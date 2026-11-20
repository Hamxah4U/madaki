<?php
/**
 * Customer receipt – opened from the WhatsApp message.
 * No login needed: access is controlled by the signed token in ?t=
 * Put this file in your web root (next to transportationexp_view.php's router).
 */
require_once 'model/receipt_helpers.php';

header('X-Robots-Tag: noindex, nofollow');
header('Referrer-Policy: no-referrer');
header('Cache-Control: private, no-store');

function e($v): string { return htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

$cfg  = receipt_config();
$ref  = receipt_parse_token($_GET['t'] ?? '');
$data = $ref ? receipt_load(receipt_db(), $ref['tid'], $ref['cid']) : null;

if (!$data) {
    http_response_code(404);
    ?><!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex"><title>Receipt not found</title>
<style>body{font:16px/1.5 system-ui,-apple-system,"Segoe UI",Roboto,sans-serif;background:#e9eeeb;color:#17211d;display:grid;place-items:center;min-height:100vh;margin:0;padding:24px}
.box{background:#fff;border:1px solid #dde3df;border-radius:8px;padding:28px;max-width:420px}h1{font-size:20px;margin:0 0 8px}p{margin:0;color:#5f6b66}</style></head>
<body><div class="box"><h1>We could not open this receipt</h1>
<p>The link may be incomplete. Please ask <?= e($cfg['company_name']) ?> to send it again.</p></div></body></html>
<?php
    exit;
}

$c = $data['customer'];
$t = $data['totals'];
$receiptNo = 'BMT-' . $data['tid'] . '-' . $data['cid'];
$issued    = date('d M Y, h:i A');

switch ($t['status']) {
    case 'paid': $stampText = 'Paid in full';  $stampClass = 'is-paid'; break;
    case 'over': $stampText = 'Overpaid';      $stampClass = 'is-over'; break;
    default:     $stampText = 'Balance due';   $stampClass = 'is-due';
}
$balanceLabel = $t['status'] === 'over' ? 'Credit' : 'Balance';
$balanceValue = naira(abs($t['balance']));
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Receipt <?= e($receiptNo) ?> – <?= e($c['name']) ?></title>
<style>
  :root{
    --ink:#17211d; --muted:#5f6b66; --line:#dde3df; --paper:#ffffff; --bg:#e9eeeb;
    --brand:#0e5a3c; --brand-soft:#e6f1ec; --amber:#a65b00; --amber-soft:#fbf0e0; --blue:#1d4f91; --blue-soft:#e6eef8;
    --serif:Georgia,"Times New Roman",serif;
    --sans:system-ui,-apple-system,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif;
  }
  *{box-sizing:border-box}
  html{-webkit-text-size-adjust:100%}
  body{margin:0;background:var(--bg);color:var(--ink);font:15px/1.5 var(--sans);padding:24px 16px 48px}
  .sheet{max-width:760px;margin:0 auto}
  .paper{background:var(--paper);border:1px solid var(--line);border-radius:6px;position:relative}

  /* masthead */
  .mast{display:flex;justify-content:space-between;gap:24px;padding:28px 32px 24px;border-top:6px solid var(--brand);border-radius:6px 6px 0 0}
  .brand h1{font:700 24px/1.2 var(--serif);margin:0;letter-spacing:.2px}
  .brand p{margin:4px 0 0;color:var(--muted);font-size:13px}
  .meta{text-align:right;font-size:13px;color:var(--muted)}
  .meta .title{font:700 20px/1.2 var(--serif);color:var(--ink);margin-bottom:6px}
  .meta b{color:var(--ink);font-weight:600}

  /* ticket perforation */
  .perf{height:0;border-top:2px dashed var(--line);margin:0 22px;position:relative}
  .perf::before,.perf::after{content:"";position:absolute;top:-11px;width:20px;height:20px;border-radius:50%;background:var(--bg);border:1px solid var(--line)}
  .perf::before{left:-33px}.perf::after{right:-33px}

  .body{padding:26px 32px 8px}
  .parties{display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:26px}
  .label{font-size:12.5px;color:var(--muted);margin-bottom:2px}
  .value{font-weight:600}
  .big{font:700 19px/1.3 var(--serif)}
  dl{margin:0}dl div{display:flex;justify-content:space-between;gap:12px;padding:3px 0;font-size:14px}
  dt{color:var(--muted)}dd{margin:0;font-weight:600;text-align:right}

  table{width:100%;border-collapse:collapse;font-variant-numeric:tabular-nums}
  th{font-size:12.5px;font-weight:600;color:var(--muted);text-align:right;padding:8px 10px;border-bottom:2px solid var(--ink)}
  td{padding:10px;text-align:right;border-bottom:1px solid var(--line)}
  th:first-child,td:first-child{text-align:left;padding-left:0}
  th:last-child,td:last-child{padding-right:0}
  tfoot td{border-bottom:0;font-weight:700;padding-top:12px}
  .table-wrap{overflow-x:auto}

  /* payments + summary */
  .lower{display:grid;grid-template-columns:1.1fr 1fr;gap:28px;margin:26px 0 8px;align-items:start}
  .pay h2,.sum h2{font:600 13px/1.3 var(--sans);color:var(--muted);margin:0 0 8px}
  .sum dl div{font-size:15px;padding:5px 0}
  .sum .rule{border-top:1px solid var(--ink);margin-top:6px;padding-top:10px}
  .sum .bal dt{color:var(--ink);font-weight:600}
  .sum .bal dd{font:700 24px/1.2 var(--serif)}
  .sum .bal.is-due dd{color:var(--amber)}.sum .bal.is-paid dd{color:var(--brand)}.sum .bal.is-over dd{color:var(--blue)}

  .stamp{display:inline-block;transform:rotate(-4deg);border:3px double currentColor;border-radius:6px;padding:6px 16px;margin:6px 0 14px;font:700 18px/1.2 var(--serif);letter-spacing:.5px}
  .stamp.is-paid{color:var(--brand);background:var(--brand-soft)}
  .stamp.is-due{color:var(--amber);background:var(--amber-soft)}
  .stamp.is-over{color:var(--blue);background:var(--blue-soft)}

  .foot{padding:18px 32px 26px;margin-top:10px;border-top:1px solid var(--line);display:flex;justify-content:space-between;gap:20px;flex-wrap:wrap;font-size:13px;color:var(--muted)}
  .foot b{color:var(--ink);font-weight:600}
  .actions{max-width:760px;margin:18px auto 0;display:flex;gap:10px;justify-content:center}
  .btn{appearance:none;border:1px solid var(--brand);background:var(--brand);color:#fff;font:600 15px/1 var(--sans);padding:13px 22px;border-radius:6px;cursor:pointer}
  .btn:hover{filter:brightness(1.08)}
  .btn:focus-visible{outline:3px solid #7bbf9f;outline-offset:2px}
  .fine{max-width:760px;margin:14px auto 0;text-align:center;font-size:12.5px;color:var(--muted)}

  @media (max-width:560px){
    body{padding:12px 8px 40px}
    .mast{flex-direction:column;gap:14px;padding:22px 18px 18px}
    .meta{text-align:left}
    .body{padding:20px 18px 4px}.foot{padding:16px 18px 22px}
    .parties,.lower{grid-template-columns:1fr;gap:20px}
    .perf::before{left:-9px}.perf::after{right:-9px}.perf{margin:0 10px}
    th,td{padding:8px 4px;font-size:13px}
    th{font-size:12px}
    .body{padding-left:14px;padding-right:14px}
  }

  @page{size:A4;margin:12mm}
  @media print{
    body{background:#fff;padding:0}
    .paper{border:0;border-radius:0}
    .perf::before,.perf::after{display:none}
    .actions,.fine{display:none}
    .stamp{-webkit-print-color-adjust:exact;print-color-adjust:exact}
  }
</style>
</head>
<body>
<div class="sheet">
  <article class="paper">

    <header class="mast">
      <div class="brand">
        <h1><?= e($cfg['company_name']) ?></h1>
        <?php if ($cfg['company_tagline']): ?><p><?= e($cfg['company_tagline']) ?></p><?php endif; ?>
        <?php if ($cfg['company_address'] || $cfg['company_phone']): ?>
          <p><?= e(trim($cfg['company_address'] . ' ' . $cfg['company_phone'])) ?></p>
        <?php endif; ?>
      </div>
      <div class="meta">
        <div class="title">Payment receipt</div>
        <div>Receipt no. <b><?= e($receiptNo) ?></b></div>
        <div>Issued <b><?= e($issued) ?></b></div>
      </div>
    </header>

    <div class="perf" aria-hidden="true"></div>

    <section class="body">
      <div class="parties">
        <div>
          <div class="label">Received from</div>
          <div class="big"><?= e($c['name'] ?: 'Customer') ?></div>
        </div>
        <dl>
          <?php if ($data['trip']['motor']): ?><div><dt>Motor no.</dt><dd><?= e($data['trip']['motor']) ?></dd></div><?php endif; ?>
          <?php if ($data['trip']['driver']): ?><div><dt>Driver</dt><dd><?= e($data['trip']['driver']) ?></dd></div><?php endif; ?>
          <?php if ($data['trip']['delivery']): ?><div><dt>Delivery date</dt><dd><?= e($data['trip']['delivery']) ?></dd></div><?php endif; ?>
        </dl>
      </div>

      <div class="table-wrap">
        <table>
          <thead>
            <tr><th>Market</th><th>Loaded</th><th>Died</th><th>Delivered</th><th>Amount</th></tr>
          </thead>
          <tbody>
            <?php foreach ($data['rows'] as $r): ?>
            <tr>
              <td><?= e($r['market']) ?></td>
              <td><?= (int)$r['loaded'] ?></td>
              <td><?= (int)$r['died'] ?></td>
              <td><?= (int)$r['delivered'] ?></td>
              <td><?= e(naira($r['expected'])) ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
          <?php if (count($data['rows']) > 1): ?>
          <tfoot>
            <tr>
              <td>Total</td>
              <td><?= (int)$t['loaded'] ?></td>
              <td><?= (int)$t['died'] ?></td>
              <td><?= (int)$t['delivered'] ?></td>
              <td><?= e(naira($t['expected'])) ?></td>
            </tr>
          </tfoot>
          <?php endif; ?>
        </table>
      </div>

      <div class="lower">
        <div class="pay">
          <div class="stamp <?= e($stampClass) ?>"><?= e($stampText) ?></div>
          <h2>Payments received</h2>
          <dl>
            <div><dt>1st payment</dt><dd><?= $t['first']  > 0 ? e(naira($t['first']))  : '–' ?></dd></div>
            <div><dt>2nd payment</dt><dd><?= $t['second'] > 0 ? e(naira($t['second'])) : '–' ?></dd></div>
            <div><dt>3rd payment</dt><dd><?= $t['third']  > 0 ? e(naira($t['third']))  : '–' ?></dd></div>
          </dl>
        </div>

        <div class="sum">
          <h2>Summary</h2>
          <dl>
            <div><dt><?= (int)$t['delivered'] ?> delivered × <?= e(naira($data['rate'])) ?></dt><dd><?= e(naira($t['expected'])) ?></dd></div>
            <div><dt>Total paid</dt><dd><?= e(naira($t['paid'])) ?></dd></div>
            <div class="rule bal <?= e($stampClass) ?>"><dt><?= e($balanceLabel) ?></dt><dd><?= e($balanceValue) ?></dd></div>
          </dl>
        </div>
      </div>
    </section>

    <footer class="foot">
      <div>
        <?php if (!empty($data['agent']['name'])): ?>
          Your agent: <b><?= e($data['agent']['name']) ?></b><?php if (!empty($data['agent']['phone'])): ?> · <?= e($data['agent']['phone']) ?><?php endif; ?>
        <?php else: ?>
          Thank you for your business.
        <?php endif; ?>
      </div>
      <div>Thank you for choosing <?= e($cfg['company_name']) ?>.</div>
    </footer>
  </article>

  <div class="actions">
    <button class="btn" type="button" onclick="window.print()">Save as PDF or print</button>
  </div>
  <p class="fine">Computer-generated receipt. Figures reflect payments recorded up to <?= e($issued) ?>.</p>
</div>
</body>
</html>
