<?php
require 'model/Database.php';
require 'vendor/autoload.php';

use Dompdf\Dompdf;

$id = $_GET['id'];
$tid = $_GET['tid'];

// Person
$stmt = $db->conn->prepare("SELECT * FROM transportation_expenses WHERE id=?");
$stmt->execute([$id]);
$person = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt2 = $db->conn->prepare("SELECT * FROM transportation WHERE id=?");
$stmt2->execute([$tid]);
$transport = $stmt2->fetch(PDO::FETCH_ASSOC);

// Calculate cost per animal

$stmt3 = $db->conn->prepare("SELECT SUM(surviving_animal) as total FROM transportation_expenses WHERE transportation_id=?");
$stmt3->execute([$tid]);
$totalSurviving = $stmt3->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;


$costPerAnimal = $totalSurviving > 0 
    ? $transport['driver_amount'] / $totalSurviving 
    : 0;

$expected = $costPerAnimal * $person['surviving_animal'];
$balance = round($expected - $person['total']);

// HTML for PDF
$html = "
<h3 style='text-align:center;'>BASHIR MADAKI</h3>
<p style='text-align:center;'>Transportation Receipt</p>
<table border='1' width='100%' cellpadding='5' cellspacing='0'>
<tr><td>Name</td><td>{$person['fullname']}</td></tr>
<tr><td>Driver</td><td>{$transport['driver_name']}</td></tr>
<tr><td>Motor No</td><td>{$transport['bossno']}</td></tr>
<tr><td>Surviving</td><td>{$person['surviving_animal']}</td></tr>
<tr><td>Cost per Animal</td><td>₦".number_format($costPerAnimal,2)."</td></tr>
<tr><td>Expected</td><td>₦".number_format($expected)."</td></tr>
<tr><td>1st</td><td>₦".number_format($person['first_payment'])."</td></tr>
<tr><td>2nd</td><td>₦".number_format($person['second_payment'])."</td></tr>
<tr><td>3rd</td><td>₦".number_format($person['third_payment'])."</td></tr>
<tr><td>Total Paid</td><td><b>₦".number_format($person['total'])."</b></td></tr>
<tr><td>Status</td><td>".($balance==0?'Cleared':($balance>0?$balance.' Remaining':abs($balance).' Over'))."</td></tr>
</table>
<p>Date: ".date('d M Y')."</p>
";

// Generate PDF
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4');
$dompdf->render();

// Stream without forcing download
$dompdf->stream("receipt.pdf", ["Attachment" => false]);
