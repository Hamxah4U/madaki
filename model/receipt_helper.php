<?php
/**
 * Shared receipt logic: one place for the numbers so the screen receipt,
 * the PDF and the WhatsApp message always agree.
 */

function receipt_config(): array {
    static $cfg = null;
    if ($cfg === null) $cfg = require __DIR__ . '/../config/whatsapp.php';
    return $cfg;
}

function receipt_token(int $id, int $tid): string {
    return substr(hash_hmac('sha256', "$id|$tid", receipt_config()['link_secret']), 0, 24);
}

function receipt_token_valid(int $id, int $tid, ?string $k): bool {
    return is_string($k) && hash_equals(receipt_token($id, $tid), $k);
}

/** Absolute public link to a receipt (page, or PDF when $pdf = true). */
function receipt_url(int $id, int $tid, bool $pdf = false): string {
    $https  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    $base   = ($https ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
    return $base . "/receipt?id=$id&tid=$tid&k=" . receipt_token($id, $tid) . ($pdf ? '&format=pdf' : '');
}

/** Turn 0803..., 803..., +234803... into 234803... (digits only). Returns null if invalid. */
function normalize_phone(?string $raw): ?string {
    $d = preg_replace('/\D+/', '', (string)$raw);
    if ($d === '') return null;
    $cc = receipt_config()['country_code'];
    if (strpos($d, '00') === 0)              $d = substr($d, 2);
    elseif (strpos($d, '0') === 0)           $d = $cc . substr($d, 1);
    elseif (strlen($d) === 10)               $d = $cc . $d;
    $len = strlen($d);
    if ($len < 11 || $len > 15) return null;
    if (strpos($d, $cc) === 0 && $len !== strlen($cc) + 10) return null; // Nigerian numbers: 234 + 10 digits
    return $d;
}

/** Everything a receipt needs, or null if the record does not exist. */
function get_receipt_data(PDO $conn, int $id, int $tid): ?array {
    $st = $conn->prepare("SELECT te.*, c.fullname AS customer_name, c.phone AS customer_phone
                          FROM transportation_expenses te
                          LEFT JOIN customers_tbl c ON c.id = te.customer_id
                          WHERE te.id = ? AND te.transportation_id = ?");
    $st->execute([$id, $tid]);
    $person = $st->fetch(PDO::FETCH_ASSOC);
    if (!$person) return null;

    $st = $conn->prepare("SELECT * FROM transportation WHERE id = ?");
    $st->execute([$tid]);
    $transport = $st->fetch(PDO::FETCH_ASSOC);
    if (!$transport) return null;

    $st = $conn->prepare("SELECT SUM(surviving_animal) FROM transportation_expenses WHERE transportation_id = ?");
    $st->execute([$tid]);
    $totalSurviving = (int)$st->fetchColumn();

    $costPerAnimal = $totalSurviving > 0 ? ((float)$transport['driver_amount']) / $totalSurviving : 0.0;
    $surviving     = (int)$person['surviving_animal'];
    $expected      = round($costPerAnimal * $surviving, 2);
    $paid          = round((float)$person['total'], 2);
    $balance       = round($expected - $paid, 2);

    $name  = trim($person['customer_name'] ?: $person['fullname'] ?: 'Valued Customer');
    $phone = $person['customer_phone'] ?: $person['phone'];

    if ($balance > 0.5)       { $status = 'BALANCE DUE'; $statusKey = 'due'; }
    elseif ($balance < -0.5)  { $status = 'OVERPAID';    $statusKey = 'over'; }
    else                      { $status = 'PAID IN FULL'; $statusKey = 'paid'; }

    return [
        'id' => $id, 'tid' => $tid,
        'receipt_no'   => sprintf('BMT-%04d-%05d', $tid, $id),
        'issued'       => date('d M Y'),
        'trip_date'    => !empty($transport['date_record']) ? date('d M Y', strtotime($transport['date_record'])) : '',
        'customer'     => $name,
        'phone'        => $phone,
        'transport'    => $transport,
        'total_animal' => (int)$person['total_animal'],
        'death_animal' => (int)$person['death_animal'],
        'surviving'    => $surviving,
        'cost_per'     => round($costPerAnimal, 2),
        'expected'     => $expected,
        'payments'     => [
            (float)$person['first_payment'],
            (float)$person['second_payment'],
            (float)$person['third_payment'],
        ],
        'paid'         => $paid,
        'balance'      => $balance,
        'status'       => $status,
        'status_key'   => $statusKey,
    ];
}

function naira(float $n): string { return '₦' . number_format($n, 2); }

/** Render receipt HTML. $pdf = true gives the dompdf-safe variant (DejaVu font for the ₦ sign). */
function render_receipt_html(array $r, bool $pdf = false): string {
    $cfg = receipt_config();
    $biz = $cfg['business'];
    ob_start();
    require __DIR__ . '/../views/receipt_document.php';
    return ob_get_clean();
}

/** Generate the receipt as PDF bytes. */
function render_receipt_pdf(array $r): string {
    require_once __DIR__ . '/../vendor/autoload.php';
    $opts = new \Dompdf\Options();
    $opts->set('defaultFont', 'DejaVu Sans');
    $opts->set('isRemoteEnabled', false);
    $dompdf = new \Dompdf\Dompdf($opts);
    $dompdf->loadHtml(render_receipt_html($r, true), 'UTF-8');
    $dompdf->setPaper('A5', 'portrait');
    $dompdf->render();
    return $dompdf->output();
}
