<?php
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json');

require_once 'Database.php'; 

if (isset($_GET['id']) && isset($_GET['tid'])) {
    $id = (int)$_GET['id'];
    $market_id = (int)$_GET['tid'];

    try {
        $stmt =$db->conn->prepare("UPDATE `market_transaction` SET `status` = 'approved' WHERE `id` = :id");
        $success = $stmt->execute(['id' =>$id]);

        if ($success) {
            echo json_encode(['status' => true]);
            exit();
        } else {
            echo json_encode(['status' => false, 'message' => 'Could not update record.']);
            exit();
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => false, 'message' => $e->getMessage()]);
        exit();
    }
} else {
    echo json_encode(['status' => false, 'message' => 'Missing parameters.']);
    exit();
}
?>