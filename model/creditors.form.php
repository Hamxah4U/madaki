<?php
require 'Database.php';
require 'vendor/autoload.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $errors = [];

    $creditor_name  = trim($_POST['creditor_name'] ?? '');
    $creditor_phone = trim($_POST['creditor_phone'] ?? '');
    $amount         = trim($_POST['amount'] ?? '');
    $userID         = trim($_POST['userID'] ?? '');
    $mode           = $_POST['mode'] ?? 'add'; 
    $id             = $_POST['id'] ?? null;

    // ✅ VALIDATION
    if (empty($creditor_name)) {
        $errors['creditor_name'] = 'Creditor name is required!';
    }

    if (empty($creditor_phone)) {
        $errors['creditor_phone'] = 'Creditor phone is required!';
    }

    if (empty($amount)) {
        $errors['amount'] = 'Amount is required!';
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
                INSERT INTO creditors 
                (market_id, creditor_name, creditor_phone, amount, daterecorded, timerecorded, record_by) 
                VALUES (:driver_id, :creditor_name, :creditor_phone, :amount, CURDATE(), CURTIME(), :record_by)
            ');
            session_start();
            $stmt->execute([
                ':driver_id'      => $userID,
                ':creditor_name'  => $creditor_name,
                ':creditor_phone' => $creditor_phone,
                ':amount'         => $amount,
                ':record_by'      => $_SESSION['userID']
            ]);

            echo json_encode([
                'status' => true,
                'success' => ['message' => 'Creditor added successfully.']
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
                    'errors' => ['general' => 'Invalid creditor ID']
                ]);
                exit;
            }

            $stmt = $db->conn->prepare('
                UPDATE creditors 
                SET creditor_name = :creditor_name, creditor_phone = :creditor_phone, amount = :amount 
                WHERE id = :id
            ');

            $stmt->execute([
                ':creditor_name'  => $creditor_name,
                ':creditor_phone' => $creditor_phone,
                ':amount'         => $amount,
                ':id'             => $id
            ]);

            echo json_encode([
                'status' => true,
                'success' => ['message' => 'Creditor updated successfully.']
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