<?php
require 'Database.php';

$id      = $_POST['edit_id'];
$amount  = $_POST['amount'];
$date_in = $_POST['date_in'];

$stmt = $db->conn->prepare("
    UPDATE moneyin
    SET amount = :amount,
        date_in = :date_in
    WHERE id = :id
");

$stmt->execute([
    ':amount'=>$amount,
    ':date_in'=>$date_in,
    ':id'=>$id
]);

// echo "Record updated successfully";