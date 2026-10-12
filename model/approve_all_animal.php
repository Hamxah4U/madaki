<?php
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json');

require_once 'Database.php'; 

if (isset($_GET['tid']) || isset($_POST['tid'])) {$market_id = (int)($_GET['tid'] ?? $_POST['tid']);

    try {
        $stmt =$db->conn->prepare("UPDATE `market_transaction` SET `status` = 'approved' WHERE `market_code` = :market_code AND `status` = 'pending'");
        $success = $stmt->execute(['market_code' =>$market_id]);

        if ($success) {
            echo json_encode(['status' => true]);
            exit();
        } else {
            echo json_encode(['status' => false, 'message' => 'Could not update records.']);
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