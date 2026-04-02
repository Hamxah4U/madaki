<?php
require 'Database.php';
require 'vendor/autoload.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $errors = [];

    // =========================
    // GET DATA SAFELY
    // =========================
    $comment = trim($_POST['comment'] ?? '');
    $userID  = trim($_POST['transport_id'] ?? '');
    $mode    = $_POST['mode'] ?? 'add'; // add | edit
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
        $errors['general'] = 'Invalid comment ID!';
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
        // ADD COMMENT
        // =========================
        if ($mode === 'add') {

            $stmt = $db->conn->prepare("
                INSERT INTO expenses 
                (driver_id, reason, daterecorded, timerecorded, status) 
                VALUES (:driver_id, :reason, CURDATE(), CURTIME(), 'comment')
            ");

            $stmt->execute([
                ':driver_id' => $userID,
                ':reason'    => $comment
            ]);

            echo json_encode([
                'status' => true,
                'success' => [
                    'message' => 'Comment added successfully.'
                ]
            ]);
            exit;
        }

        // =========================
        // UPDATE COMMENT
        // =========================
        if ($mode === 'edit') {

            // Optional: ensure record exists
            $check = $db->conn->prepare("SELECT id FROM expenses WHERE id = :id AND status = 'comment'");
            $check->execute([':id' => $id]);

            if ($check->rowCount() === 0) {
                echo json_encode([
                    'status' => false,
                    'errors' => ['general' => 'Comment not found']
                ]);
                exit;
            }

            $stmt = $db->conn->prepare("
                UPDATE expenses 
                SET reason = :reason 
                WHERE id = :id AND status = 'comment'
            ");

            $stmt->execute([
                ':reason' => $comment,
                ':id'     => $id
            ]);

            echo json_encode([
                'status' => true,
                'success' => [
                    'message' => 'Comment updated successfully.'
                ]
            ]);
            exit;
        }

        // =========================
        // INVALID MODE
        // =========================
        echo json_encode([
            'status' => false,
            'errors' => ['general' => 'Invalid request mode']
        ]);
        exit;

    } catch (PDOException $e) {
        echo json_encode([
            'status' => false,
            'errors' => [
                'general' => 'Database error: ' . $e->getMessage()
            ]
        ]);
        exit;
    }

} else {
    echo json_encode([
        'status' => false,
        'errors' => ['general' => 'Invalid request method']
    ]);
    exit;
}