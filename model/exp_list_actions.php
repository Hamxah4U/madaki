<?php
// Save as: model/exp_list_actions.php
require 'Database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function back(string $type, string $msg): void
{
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
    $to = $_SERVER['HTTP_REFERER'] ?? '/manageexpenses';
    header('Location: ' . $to);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    back('error', 'Invalid request.');
}

$action = $_POST['action'] ?? '';
$id     = (int)($_POST['id'] ?? 0);
$name   = trim($_POST['name'] ?? '');

try {
    // ---------- ADD ----------
    if ($action === 'add') {
        if ($name === '') {
            back('error', 'Expense name is required.');
        }
        if (mb_strlen($name) > 150) {
            back('error', 'Expense name is too long (max 150 characters).');
        }

        $check = $db->conn->prepare('SELECT COUNT(*) FROM exp_list WHERE LOWER(name) = LOWER(:name)');
        $check->execute([':name' => $name]);
        if ($check->fetchColumn() > 0) {
            back('error', 'This expense already exists.');
        }

        $stmt = $db->conn->prepare('INSERT INTO exp_list (name, status) VALUES (:name, :status)');
        $stmt->execute([':name' => $name, ':status' => 'active']);

        back('success', 'Expense added successfully.');
    }

    // ---------- EDIT ----------
    if ($action === 'edit') {
        if ($id <= 0) {
            back('error', 'Invalid expense ID.');
        }
        if ($name === '') {
            back('error', 'Expense name is required.');
        }
        if (mb_strlen($name) > 150) {
            back('error', 'Expense name is too long (max 150 characters).');
        }

        $check = $db->conn->prepare('SELECT COUNT(*) FROM exp_list WHERE LOWER(name) = LOWER(:name) AND id != :id');
        $check->execute([':name' => $name, ':id' => $id]);
        if ($check->fetchColumn() > 0) {
            back('error', 'Another expense already uses this name.');
        }

        $stmt = $db->conn->prepare('UPDATE exp_list SET name = :name WHERE id = :id');
        $stmt->execute([':name' => $name, ':id' => $id]);

        back('success', 'Expense updated successfully.');
    }

    // ---------- ACTIVATE / DEACTIVATE ----------
    if ($action === 'toggle') {
        if ($id <= 0) {
            back('error', 'Invalid expense ID.');
        }

        $stmt = $db->conn->prepare(
            "UPDATE exp_list
             SET status = IF(status = 'active', 'inactive', 'active')
             WHERE id = :id"
        );
        $stmt->execute([':id' => $id]);

        back('success', 'Status changed.');
    }

    back('error', 'Unknown action.');

} catch (PDOException $e) {
    back('error', 'Database error: ' . $e->getMessage());
}