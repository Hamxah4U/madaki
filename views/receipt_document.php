<?php
/** @var array $r  @var array $biz  @var bool $pdf */
$e = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
$colors = ['paid' => ['#0f7b4b', '#e6f4ec'], 'due' => ['#b45309', '#fdf1e1'], 'over' => ['#1d4ed8', '#e8effd']];
[$sc, $sbg] = $colors[$r['status_key']];
$ord = ['1st Payment', '2nd Payment', '3rd Payment'];
$t = $r['transport'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Receipt <?= $e($r['receipt_no']) ?> – <?= $e($biz['name']) ?></title>
<style>
  @page { margin: 0; }
  * { box-sizing: border-box; }
  body { margin: 0; background: <?= $pdf ? '#ffffff' : '#eef1f5' ?>; color: #1f2937;
         font-family: <?= $pdf ? "'DejaVu Sans', sans-serif" : "'Segoe UI', Roboto, Helvetica, Arial, sans-serif" ?>; font-size: 13px; }
  .wrap { <?= $pdf ? '' : 'max-width: 560px; margin: 24px auto; padding: 0 12px;' ?> }
  .card { background: #fff; <?= $pdf ? '' : 'border-radius: 10px; box-shadow: 0 4px 24px rgba(15,23,42,.10); overflow: hidden;' ?> }
  table { width: 100%; border-collapse: collapse; }
  .head { background: #0b3d2e; color: #fff; padding: 22px 26px; }
  .head .name { font-size: 20px; font-weight: bold; letter-spacing: 1px; }
  .head .tag  { font-size: 11px; color: #b7d9c9; margin-top: 3px; }
  .head .rcpt { text-align: right; font-size: 11px; color: #b7d9c9; }
  .head .rcpt b { display: block; font-size: 15px; color: #fff; letter-spacing: .5px; }
  .gold { height: 4px; background: #c9a227; }
  .body { padding: 22px 26px; }
  .title { font-size: 11px; letter-spacing: 2px; color: #6b7280; text-transform: uppercase; margin-bottom: 10px; }
  .meta td { padding: 3px 0; vertical-align: top; }
  .meta .l { color: #6b7280; width: 36%; }
  .meta .v { font-weight: 600; }
  .sec { margin-top: 18px; }
  .lines th { text-align: left; font-size: 10px; letter-spacing: 1px; text-transform: uppercase; color: #6b7280;
              border-bottom: 2px solid #0b3d2e; padding: 6px 0; }
  .lines th.r, .lines td.r { text-align: right; }
  .lines td { padding: 7px 0; border-bottom: 1px solid #e5e7eb; }
  .sum td { padding: 6px 0; }
  .sum .tot td { border-top: 2px solid #0b3d2e; font-size: 15px; font-weight: bold; padding-top: 9px; }
  .badge { display: inline-block; padding: 6px 16px; border-radius: 20px; font-weight: bold; letter-spacing: 1px; font-size: 12px;
           color: <?= $sc ?>; background: <?= $sbg ?>; border: 1px solid <?= $sc ?>; }
  .balbox { background: <?= $sbg ?>; border-left: 4px solid <?= $sc ?>; padding: 12px 14px; margin-top: 16px; }
  .foot { padding: 16px 26px 22px; text-align: center; color: #6b7280; font-size: 11px; border-top: 1px dashed #d1d5db; }
  .foot b { color: #0b3d2e; }
  .actions { text-align: center; margin-top: 16px; }
  .btn { display: inline-block; padding: 10px 20px; margin: 0 4px; border-radius: 6px; text-decoration: none; font-size: 13px;
         background: #0b3d2e; color: #fff; border: 0; cursor: pointer; }
  .btn.alt { background: #fff; color: #0b3d2e; border: 1px solid #0b3d2e; }
<?php if ($pdf): ?>
  body { font-size: 11px; }
  .head { padding: 14px 22px; } .head .name { font-size: 17px; }
  .body { padding: 14px 22px; } .sec { margin-top: 10px; } .title { margin-bottom: 5px; }
  .meta td { padding: 2px 0; } .lines td { padding: 4px 0; } .lines th { padding: 4px 0; }
  .balbox { margin-top: 10px; padding: 8px 12px; } .foot { padding: 10px 22px 14px; font-size: 10px; }
<?php endif; ?>
  @media print { .actions { display: none; } body { background: #fff; } .wrap { margin: 0; max-width: none; } .card { box-shadow: none; } }
</style>
</head>
<body>
<div class="wrap">
<div class="card">
  <div class="head">
    <table><tr>
      <td>
        <div class="name"><?= $e($biz['name']) ?></div>
        <div class="tag"><?= $e($biz['tagline']) ?></div>
      </td>
      <td class="rcpt">PAYMENT RECEIPT<b><?= $e($r['receipt_no']) ?></b></td>
    </tr></table>
  </div>
  <div class="gold"></div>

  <div class="body">
    <div class="title">Receipt details</div>
    <table class="meta">
      <tr><td class="l">Received from</td><td class="v"><?= $e($r['customer']) ?></td></tr>
      <tr><td class="l">Date issued</td><td class="v"><?= $e($r['issued']) ?></td></tr>
      <?php if ($r['trip_date']): ?><tr><td class="l">Trip date</td><td class="v"><?= $e($r['trip_date']) ?></td></tr><?php endif; ?>
      <tr><td class="l">Driver</td><td class="v"><?= $e($t['driver_name'] ?: 'N/A') ?></td></tr>
      <tr><td class="l">Motor No.</td><td class="v"><?= $e($t['bossno'] ?: 'N/A') ?></td></tr>
      <?php if (!empty($t['yan_waju'])): ?><tr><td class="l">Yan Waju</td><td class="v"><?= $e($t['yan_waju']) ?></td></tr><?php endif; ?>
    </table>

    <div class="sec">
      <div class="title">Animals &amp; charges</div>
      <table class="lines">
        <tr><th>Description</th><th class="r">Qty</th><th class="r">Amount</th></tr>
        <tr><td>Animals loaded</td><td class="r"><?= $r['total_animal'] ?></td><td class="r">–</td></tr>
        <?php if ($r['death_animal'] > 0): ?>
        <tr><td>Deaths</td><td class="r"><?= $r['death_animal'] ?></td><td class="r">–</td></tr>
        <?php endif; ?>
        <tr><td>Surviving animals @ <?= naira($r['cost_per']) ?> each</td><td class="r"><?= $r['surviving'] ?></td><td class="r"><?= naira($r['expected']) ?></td></tr>
      </table>
    </div>

    <div class="sec">
      <div class="title">Payments received</div>
      <table class="lines">
        <?php foreach ($r['payments'] as $i => $p): if ($p <= 0) continue; ?>
        <tr><td><?= $ord[$i] ?></td><td class="r"><?= naira($p) ?></td></tr>
        <?php endforeach; ?>
        <?php if ($r['paid'] <= 0): ?><tr><td colspan="2" style="color:#6b7280">No payment recorded yet</td></tr><?php endif; ?>
      </table>
    </div>

    <table class="sum" style="margin-top:12px">
      <tr><td>Amount due</td><td style="text-align:right"><?= naira($r['expected']) ?></td></tr>
      <tr class="tot"><td>Total paid</td><td style="text-align:right"><?= naira($r['paid']) ?></td></tr>
    </table>

    <div class="balbox">
      <table><tr>
        <td><span class="badge"><?= $e($r['status']) ?></span></td>
        <td style="text-align:right; font-size:15px; font-weight:bold; color:<?= $sc ?>">
          <?php if ($r['status_key'] === 'due'): ?>Balance: <?= naira($r['balance']) ?>
          <?php elseif ($r['status_key'] === 'over'): ?>Overpaid: <?= naira(abs($r['balance'])) ?>
          <?php else: ?>Balance: <?= naira(0) ?><?php endif; ?>
        </td>
      </tr></table>
    </div>
  </div>

  <div class="foot">
    <b>Thank you for your patronage.</b><br>
    <?php if ($biz['phone'] || $biz['address']): ?><?= $e(trim($biz['address'] . ' ' . ($biz['phone'] ? '· ' . $biz['phone'] : ''))) ?><br><?php endif; ?>
    This is a computer-generated receipt and is valid without a signature.
  </div>
</div>

<?php if (!$pdf): ?>
<div class="actions">
  <a class="btn" href="<?= $e(($_SERVER['REQUEST_URI'] ?? '/receipt') . (strpos($_SERVER['REQUEST_URI'] ?? '', '?') !== false ? '&' : '?') . 'format=pdf') ?>">Download PDF</a>
  <button class="btn alt" onclick="window.print()">Print</button>
</div>
<?php endif; ?>
</div>
</body>
</html>
