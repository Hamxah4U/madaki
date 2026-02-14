<?php
require 'model/Database.php';

$id = $_GET['id'];
$tid = $_GET['tid'];

$stmt = $db->conn->prepare("SELECT * FROM transportation_expenses WHERE id=?");
$stmt->execute([$id]);
$person = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt2 = $db->conn->prepare("SELECT * FROM transportation WHERE id=?");
$stmt2->execute([$tid]);
$transport = $stmt2->fetch(PDO::FETCH_ASSOC);

// Calculate cost per animal

$stmt3 = $db->conn->prepare("SELECT SUM(surviving_animal) as total_animal FROM transportation_expenses WHERE transportation_id=?");
$stmt3->execute([$tid]);
$totalSurviving = $stmt3->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

$costPerAnimal = $totalSurviving > 0 
    ? $transport['driver_amount'] / $totalSurviving 
    : 0;

$expected = $costPerAnimal; //* $person['surviving_animal'];
$balance = round($expected - $person['total']);

require 'views/receipt_modal.view.php';
