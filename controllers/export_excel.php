<?php
require 'model/Database.php';

$tid = $_GET['tid'];

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=transportation_$tid.xls");

$stmt =   $db->conn->prepare("SELECT * FROM transportation_expenses WHERE transportation_id=?");
$stmt->execute([$tid]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Name\tDeath\tSurviving\tAmount\t1st\t2nd\t3rd\tTotal\n";

foreach ($rows as $r) {
    echo "{$r['fullname']}\t{$r['death_animal']}\t{$r['surviving_animal']}\t{$r['amount']}\t{$r['first_payment']}\t{$r['second_payment']}\t{$r['third_payment']}\t{$r['total']}\n";
}
