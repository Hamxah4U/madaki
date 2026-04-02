<?php
require 'Database.php';
require 'vendor/autoload.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $errors = [];

    $comment = trim($_POST['comment'] ?? '');
    $userID  = trim($_POST['transport_id'] ?? '');
    $mode    = $_POST['mode'] ?? 'add';
    $id      = $_POST['id'] ?? null;

    // =========================
    // VALIDATION
    // =========================
    if (empty($comment)) {
        $errors['comment'] = 'Comment is required!';
    }

    if (empty($userID)) {
        $errors['transport_id'] = 'Transport ID is required!';
    }

    if ($mode === 'edit' && empty($id)) {
        $errors['general'] = 'Invalid ID!';
    }

    if (!empty($errors)) {
        echo json_encode([
            'status' => false,
            'errors' => $errors
        ]);
        exit;
    }

    try {
        $db = new Database();

        // =========================
        // ADD
        // =========================
        if ($mode === 'add') {

            $stmt = $db->conn->prepare("
                INSERT INTO expenses 
                (driver_id, reason, daterecorded, timerecorded, status) 
                VALUES (:driver_id, :reason, CURDATE(), CURTIME(), 'other_comment')
            ");

            $stmt->execute([
                ':driver_id' => $userID,
                ':reason'    => $comment
            ]);

            echo json_encode([
                'status' => true,
                'success' => ['message' => 'Other comment added successfully.']
            ]);
            exit;
        }

        // =========================
        // UPDATE
        // =========================
        if ($mode === 'edit') {

            // Check existence
            $check = $db->conn->prepare("
                SELECT id FROM expenses 
                WHERE id = :id AND status = 'other_comment'
            ");
            $check->execute([':id' => $id]);

            if ($check->rowCount() === 0) {
                echo json_encode([
                    'status' => false,
                    'errors' => ['general' => 'Record not found']
                ]);
                exit;
            }

            $stmt = $db->conn->prepare("
                UPDATE expenses 
                SET reason = :reason 
                WHERE id = :id AND status = 'other_comment'
            ");

            $stmt->execute([
                ':reason' => $comment,
                ':id'     => $id
            ]);

            echo json_encode([
                'status' => true,
                'success' => ['message' => 'Other comment updated successfully.']
            ]);
            exit;
        }

        echo json_encode([
            'status' => false,
            'errors' => ['general' => 'Invalid mode']
        ]);
        exit;

    } catch (PDOException $e) {
        echo json_encode([
            'status' => false,
            'errors' => ['general' => $e->getMessage()]
        ]);
        exit;
    }
}