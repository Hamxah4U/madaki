<?php

require 'Database.php';

$id = $_POST['id'];

$stmt = $db->conn->prepare("
    DELETE FROM market_transaction
    WHERE id = ?
");

$stmt->execute([$id]);

echo "Deleted Successfully";