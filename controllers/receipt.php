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

require 'views/receipt.view.php';
