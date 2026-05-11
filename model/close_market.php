<?php
require 'Database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $id = intval($_POST['id'] ?? 0);

    if ($id <= 0) {
        echo "error";
        exit;
    }

    $stmt = $db->conn->prepare("
        UPDATE market 
        SET status = 'closed'
        WHERE id = :id
    ");

    $update = $stmt->execute([
        ':id' => $id
    ]);

    if ($update) {
        echo "success";
    } else {
        echo "error";
    }
}
?>