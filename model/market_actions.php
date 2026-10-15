<?php
header('Content-Type: application/json');
require 'Database.php';

if (isset($_POST['action'])) {
    $action =$_POST['action'];

    // Handle Add Market
    if ($action === 'add') {
        $name = trim($_POST['market_name']);
        if (!empty($name)) {
            try {
                // Check if market name already exists
                $checkStmt =$db->conn->prepare("SELECT COUNT(*) FROM `market_2` WHERE `name` = ?");
                $checkStmt->execute([$name]);
                if ($checkStmt->fetchColumn() > 0) {
                    echo json_encode(['status' => 'error', 'message' => 'Market name already exists!']);
                    exit;
                }

                // Insert new market
                $stmt =$db->conn->prepare("INSERT INTO `market_2` (`name`, `status`) VALUES (?, 'active')");
                $stmt->execute([$name]);
                echo json_encode(['status' => 'success', 'message' => 'Market added successfully!']);
            } catch (PDOException $e) {
                echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Market name cannot be empty.']);
        }
        exit;
    }

    // Handle Edit Market
    if ($action === 'edit') {
        $id = intval($_POST['market_id']);
        $name = trim($_POST['market_name']);
        if ($id > 0 && !empty($name)) {
            try {
                // Check if market name already exists for a different ID
                $checkStmt =$db->conn->prepare("SELECT COUNT(*) FROM `market_2` WHERE `name` = ? AND `id` != ?");
                $checkStmt->execute([$name,$id]);
                if ($checkStmt->fetchColumn() > 0) {
                    echo json_encode(['status' => 'error', 'message' => 'Market name already exists for another record!']);
                    exit;
                }

                // Update market
                $stmt =$db->conn->prepare("UPDATE `market_2` SET `name` = ? WHERE `id` = ?");
                $stmt->execute([$name,$id]);
                echo json_encode(['status' => 'success', 'message' => 'Market updated successfully!']);
            } catch (PDOException $e) {
                echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid data provided.']);
        }
        exit;
    }

    // Handle Status Change (Deactivate)
    if ($action === 'status') {
        $id = intval($_POST['id']);
        $status = trim($_POST['status']); // expected 'inactive'
        if ($id > 0 &&$status === 'inactive') {
            try {
                $stmt =$db->conn->prepare("UPDATE `market_2` SET `status` = ? WHERE `id` = ?");
                $stmt->execute([$status,$id]);
                echo json_encode(['status' => 'success', 'message' => 'Market deactivated successfully!']);
            } catch (PDOException $e) {
                echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
        }
        exit;
    }
}