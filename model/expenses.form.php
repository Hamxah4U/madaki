<?php
require 'Database.php';
require 'vendor/autoload.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $errors = [];

    $amount   = trim($_POST['amount'] ?? '');
    $reason   = trim($_POST['reason'] ?? '');
    $userID   = trim($_POST['userID'] ?? '');
    $agent_id = trim($_POST['agent_id'] ?? null);
    $mode     = $_POST['mode'] ?? 'add'; 
    $id       = $_POST['id'] ?? null;
    $fstatus  = $_POST['fstatus'] ?? null;

    $receiptPath = null;

    // ✅ VALIDATION
    if (empty($amount)) {
        $errors['amount'] = 'Amount is required!';
    }

    if (empty($reason)) {
        $errors['reason'] = 'Reason is required!';
    }

    // Handle File Upload
    if (isset($_FILES['receipt']) && $_FILES['receipt']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath   = $_FILES['receipt']['tmp_name'];
        $fileName      = $_FILES['receipt']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        $allowedExtensions = ['jpg', 'jpeg', 'png'];

        if (in_array($fileExtension, $allowedExtensions)) {
            $uploadDir = 'uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $newFileName = uniqid('receipt_', true) . '.' . $fileExtension;
            $destPath    = $uploadDir . $newFileName;

            if (move_uploaded_file($fileTmpPath, $destPath)) {
                $receiptPath = $destPath;
            } else {
                $errors['receipt'] = 'Error moving uploaded file.';
            }
        } else {
            $errors['receipt'] = 'Invalid file type. Only JPG, JPEG, and PNG allowed.';
        }
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
                (driver_id, amount, reason, receipt, daterecorded, timerecorded, status, agent_id, fstatus) 
                VALUES (:driver_id, :amount, :reason, :receipt, CURDATE(), CURTIME(), "exp", :agent_id, :fstatus)
            ');

            $stmt->execute([
                ':driver_id' => $userID,
                ':amount'    => $amount,
                ':reason'    => $reason,
                ':receipt'   => $receiptPath,
                ':agent_id'  => $agent_id,
                ':fstatus'   => $fstatus
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

            // Update with optional new receipt image
            if ($receiptPath) {
                $stmt = $db->conn->prepare('
                    UPDATE expenses 
                    SET amount = :amount, reason = :reason, receipt = :receipt 
                    WHERE id = :id
                ');
                $stmt->execute([
                    ':amount'  => $amount,
                    ':reason'  => $reason,
                    ':receipt' => $receiptPath,
                    ':id'      => $id
                ]);
            } else {
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
            }

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