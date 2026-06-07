<?php
require 'Database.php';
require 'vendor/autoload.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $success = [];
    $errors = [];

    $amount  = trim($_POST['amount'] ?? '');
    $reason  = trim($_POST['reason'] ?? '');
    $userID  = trim($_POST['userID'] ?? '');
    $agent_id = trim($_POST['agent_id'] ?? null);
    $mode    = $_POST['mode'] ?? 'add'; // add or edit
    $id      = $_POST['id'] ?? null;

    // ✅ VALIDATION
    if (empty($amount)) {
        $errors['amount'] = 'Amount is required!';
    }

    if (empty($reason)) {
        $errors['reason'] = 'Reason is required!';
    }

    if (!empty($errors)) {
        echo json_encode([
            'status' => false,
            'errors' => $errors,
        ]);
        exit;
    }

    try {
        // =========================
        // ✅ ADD MODE (INSERT)
        // =========================
        if ($mode === 'add') {

            $stmt = $db->conn->prepare('
                INSERT INTO expenses 
                (driver_id, amount, reason, daterecorded, timerecorded, status, agent_id) 
                VALUES (:driver_id, :amount, :reason, CURDATE(), CURTIME(), "exp", :agent_id)
            ');

            $stmt->execute([
                ':driver_id' => $userID,
                ':amount'    => $amount,
                ':reason'    => $reason,
                ':agent_id' => $agent_id
            ]);

            echo json_encode([
                'status' => true,
                'success' => ['message' => 'Expense added successfully.']
            ]);
            exit;
        }

        // =========================
        // ✅ EDIT MODE (UPDATE)
        // =========================
        if ($mode === 'edit') {

            if (empty($id)) {
                echo json_encode([
                    'status' => false,
                    'errors' => ['general' => 'Invalid expense ID']
                ]);
                exit;
            }

            $stmt = $db->conn->prepare('
                UPDATE expenses 
                SET amount = :amount, reason = :reason 
                WHERE id = :id
            ');

            $stmt->execute([
                ':amount' => $amount,
                ':reason' => $reason,
                ':id'     => $id
            ]);

            echo json_encode([
                'status' => true,
                'success' => ['message' => 'Expense updated successfully.']
            ]);
            exit;
        }

    } catch (PDOException $e) {
        echo json_encode([
            'status' => false,
            'errors' => ['general' => $e->getMessage()]
        ]);
        exit;
    }
}
?>