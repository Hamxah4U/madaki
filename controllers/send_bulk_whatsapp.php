<?php
/**
 * POST /send-bulk-whatsapp   (login required – see index.php)
 *   transport_id = motor/trip id
 *   dry          = 1  -> only check who can be messaged, send nothing
 *   ids[]        = optional list of transportation_expenses ids (to retry only some)
 * Returns JSON with one result row per customer.
 */
header('Content-Type: application/json');
require_once 'model/Database.php';
require_once 'model/receipt_helper.php';

function out(array $a, int $code = 200) { http_response_code($code); echo json_encode($a, JSON_UNESCAPED_UNICODE); exit(); }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') out(['status' => false, 'message' => 'Invalid request.'], 405);

$tid = (int)($_POST['transport_id'] ?? 0);
if ($tid <= 0) out(['status' => false, 'message' => 'Invalid transport ID.'], 400);
$dry = !empty($_POST['dry']);
$only = array_map('intval', (array)($_POST['ids'] ?? []));

$cfg = receipt_config();
$tokenOk = $cfg['access_token'] !== '' && strpos($cfg['access_token'], 'PUT_YOUR') === false;
if (!$dry && !$tokenOk) {
    out(['status' => false, 'message' => 'WhatsApp access token is not set in config/whatsapp.php.'], 500);
}

$st = $db->conn->prepare("SELECT id FROM transportation_expenses WHERE transportation_id = ? ORDER BY id");
$st->execute([$tid]);
$ids = $st->fetchAll(PDO::FETCH_COLUMN);
if (!$ids) out(['status' => false, 'message' => 'No customer records found for this motor.']);
if ($only) $ids = array_values(array_intersect($ids, $only));

@set_time_limit(0);
ignore_user_abort(true);

$api = "https://graph.facebook.com/{$cfg['api_version']}/{$cfg['phone_number_id']}";

function wa_call(string $url, array $cfg, $body, bool $json = true): array {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $json ? json_encode($body) : $body,
        CURLOPT_HTTPHEADER     => array_filter([
            'Authorization: Bearer ' . $cfg['access_token'],
            $json ? 'Content-Type: application/json' : null,
        ]),
        CURLOPT_TIMEOUT        => 60,
    ]);
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);
    $data = json_decode((string)$resp, true) ?: [];
    $msg  = $data['error']['message'] ?? ($err ?: null);
    return ['ok' => $code >= 200 && $code < 300, 'data' => $data, 'error' => $msg];
}

function clean_param(string $s): string {          // WhatsApp template params cannot contain newlines/tabs
    return trim(preg_replace('/\s+/', ' ', $s)) ?: '-';
}

$results = []; $sent = 0; $failed = 0; $skipped = 0;

foreach ($ids as $id) {
    $r = get_receipt_data($db->conn, (int)$id, $tid);
    if (!$r) continue;

    $row = ['id' => (int)$id, 'name' => $r['customer'], 'phone' => $r['phone'], 'status' => '', 'detail' => ''];
    $to = normalize_phone($r['phone']);

    if (!$to) {
        $row['status'] = 'skipped';
        $row['detail'] = trim((string)$r['phone']) === '' ? 'No phone number' : 'Invalid phone number';
        $skipped++; $results[] = $row; continue;
    }
    $row['phone'] = $to;
    if ($dry) { $row['status'] = 'ready'; $results[] = $row; continue; }

    // 1) Build PDF and upload it to WhatsApp
    $pdf  = render_receipt_pdf($r);
    $tmp  = tempnam(sys_get_temp_dir(), 'rcpt');
    file_put_contents($tmp, $pdf);
    $fname = 'Receipt-' . $r['receipt_no'] . '.pdf';
    $up = wa_call("$api/media", $cfg, [
        'messaging_product' => 'whatsapp',
        'type' => 'application/pdf',
        'file' => new CURLFile($tmp, 'application/pdf', $fname),
    ], false);
    @unlink($tmp);

    if (!$up['ok'] || empty($up['data']['id'])) {
        $row['status'] = 'failed'; $row['detail'] = 'Upload failed: ' . ($up['error'] ?? 'unknown');
        $failed++; $results[] = $row; continue;
    }
    $mediaId = $up['data']['id'];

    // 2) Send message
    $motor = $r['transport']['bossno'] ?: ($r['transport']['driver_name'] ?: '-');
    if ($cfg['template_name'] !== '') {
        $payload = [
            'messaging_product' => 'whatsapp', 'to' => $to, 'type' => 'template',
            'template' => [
                'name' => $cfg['template_name'], 'language' => ['code' => $cfg['template_lang']],
                'components' => [
                    ['type' => 'header', 'parameters' => [['type' => 'document', 'document' => ['id' => $mediaId, 'filename' => $fname]]]],
                    ['type' => 'body', 'parameters' => [
                        ['type' => 'text', 'text' => clean_param($r['customer'])],
                        ['type' => 'text', 'text' => clean_param($motor)],
                        ['type' => 'text', 'text' => clean_param($r['status'] === 'BALANCE DUE' ? 'Balance ' . naira($r['balance']) : ($r['status'] === 'OVERPAID' ? 'Overpaid ' . naira(abs($r['balance'])) : 'Paid in full'))],
                    ]],
                ],
            ],
        ];
    } else {
        $payload = [
            'messaging_product' => 'whatsapp', 'to' => $to, 'type' => 'document',
            'document' => ['id' => $mediaId, 'filename' => $fname,
                           'caption' => "Hello {$r['customer']}, here is your payment receipt from {$cfg['business']['name']}. Thank you!"],
        ];
    }
    $send = wa_call("$api/messages", $cfg, $payload);

    if ($send['ok']) { $row['status'] = 'sent'; $sent++; }
    else {
        $row['status'] = 'failed'; $row['detail'] = $send['error'] ?? 'Unknown error'; $failed++;
        $msg = "Hello *{$r['customer']}*,\n\nYour payment receipt from *{$cfg['business']['name']}* is ready:\n" . receipt_url((int)$id, $tid, true) . "\n\nThank you for your patronage!";
        $row['wa_link'] = 'https://wa.me/' . $to . '?text=' . rawurlencode($msg);   // manual fallback
    }
    $results[] = $row;
    usleep((int)$cfg['delay_us']);
}

$summary = $dry
    ? count(array_filter($results, fn($x) => $x['status'] === 'ready')) . " ready, $skipped without a usable phone number."
    : "Sent $sent, failed $failed, skipped $skipped (no/invalid phone).";

out(['status' => true, 'dry' => $dry, 'message' => $summary, 'sent' => $sent, 'failed' => $failed, 'skipped' => $skipped, 'results' => $results]);
