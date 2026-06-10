<?php
    require 'Database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $errors = [];

    $amount = trim($_POST['amount'] ?? '');
    $reason = trim($_POST['reason'] ?? '');
    $userID = trim($_POST['userID'] ?? '');
    $agent_id = trim($_POST['agent_id'] ?? null);

    if (empty($amount)) {
        $errors['amount'] = 'Amount is required!';
    }

    if (empty($reason)) {
        $errors['reason'] = 'Reason is required!';
    }

    if (!empty($errors)) {
        echo json_encode([
            'status' => false,
            'errors' => $errors
        ]);
        exit;
    }

    try {
        $stmt = $db->conn->prepare("
            INSERT INTO expenses 
            (driver_id, amount, reason, daterecorded, timerecorded, status, agent_id)
            VALUES (:driver_id, :amount, :reason, CURDATE(), CURTIME(), 'other_exp', :agent_id)
        ");

        $stmt->bindParam(':driver_id', $userID);
        $stmt->bindParam(':amount', $amount);
        $stmt->bindParam(':reason', $reason);
        $stmt->bindParam(':agent_id', $agent_id);

        if ($stmt->execute()) {
            echo json_encode([
                'status' => true,
                'success' => ['message' => 'Other expense added successfully.']
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'errors' => ['general' => 'Insert failed']
            ]);
        }

    } catch (PDOException $e) {
        echo json_encode([
            'status' => false,
            'errors' => ['general' => $e->getMessage()]
        ]);
    }
}

?>