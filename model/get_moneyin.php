<?php
require 'Database.php';

$id = $_POST['id'];

$stmt = $db->conn->prepare("
    SELECT *
    FROM moneyin
    WHERE id = :id
");

$stmt->execute([':id'=>$id]);

echo json_encode(
    $stmt->fetch(PDO::FETCH_ASSOC)
);