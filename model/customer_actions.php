<?php
header('Content-Type: application/json');
require 'Database.php';
session_start();

if (isset($_POST['action'])) {
    $action =$_POST['action'];

    // --- FETCH CUSTOMERS (For DataTable) ---
    if ($action === 'fetch') {
        try {
            $stmt =$db->conn->prepare("SELECT * FROM `customers_tbl` ORDER BY `id` DESC");
            $stmt->execute();
            $customers =$stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['status' => 'success', 'data' => $customers]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }

    // --- ADD CUSTOMER ---
    if ($action === 'add') {$fullname = trim($_POST['fname']);$email    = trim($_POST['email'] ?? '');$phone    = trim($_POST['phone']);$address  = trim($_POST['address']);$password = password_hash('password123', PASSWORD_DEFAULT); 
        $created_by =$_SESSION['userID'] ?? 'Admin';

        if (empty($fullname) || empty($phone)) {
            echo json_encode(['status' => 'error', 'message' => 'Please fill in all required fields.']);
            exit;
        }

        try {
            // 1. Check if email already exists ONLY IF email is not empty
            if (!empty($email)) {
                $checkEmail =$db->conn->prepare("SELECT * FROM `customers_tbl` WHERE `email` = ?");
                $checkEmail->execute([$email]);
                if ($checkEmail->rowCount() > 0) {
                    echo json_encode(['status' => 'error', 'field' => 'email', 'message' => 'Email address already exists!']);
                    exit;
                }
            }

            // 2. Check if phone already exists
            $checkPhone =$db->conn->prepare("SELECT * FROM `customers_tbl` WHERE `phone` = ?");
            $checkPhone->execute([$phone]);
            if ($checkPhone->rowCount() > 0) {
                echo json_encode(['status' => 'error', 'field' => 'phone', 'message' => 'Phone number already exists!']);
                exit;
            }

            // Insert record (if email is empty, store as NULL or empty string based on your preference)
            $emailToStore = !empty($email) ?$email : null;

            $stmt =$db->conn->prepare("INSERT INTO `customers_tbl` (`fullname`, `phone`, `email`, `password`, `address`, `created_by`, `status`) VALUES (?, ?, ?, ?, ?, ?, 'active')");
            $stmt->execute([$fullname,$phone, $emailToStore,$password, $address,$created_by]);

            echo json_encode(['status' => 'success', 'message' => 'Customer added successfully!']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        }
        exit;
    }

    // --- EDIT CUSTOMER ---
    if ($action === 'edit') {
        $id       = intval($_POST['userID']);
        $fullname = trim($_POST['fname']);
        $email    = trim($_POST['email'] ?? '');
        $phone    = trim($_POST['phone']);
        $address  = trim($_POST['address']);

        if ($id <= 0 || empty($fullname) || empty($phone)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid data provided.']);
            exit;
        }

        try {
            // 1. Check if email exists for other customers ONLY IF email is not empty
            if (!empty($email)) {
                $checkEmail =$db->conn->prepare("SELECT * FROM `customers_tbl` WHERE `email` = ? AND `id` != ?");
                $checkEmail->execute([$email,$id]);
                if ($checkEmail->rowCount() > 0) {
                    echo json_encode(['status' => 'error', 'field' => 'email', 'message' => 'Email is already used by another customer!']);
                    exit;
                }
            }

            // 2. Check if phone exists for other customers
            $checkPhone =$db->conn->prepare("SELECT * FROM `customers_tbl` WHERE `phone` = ? AND `id` != ?");
            $checkPhone->execute([$phone,$id]);
            if ($checkPhone->rowCount() > 0) {
                echo json_encode(['status' => 'error', 'field' => 'phone', 'message' => 'Phone number is already used by another customer!']);
                exit;
            }

            $emailToStore = !empty($email) ?$email : null;

            // Update record
            $stmt =$db->conn->prepare("UPDATE `customers_tbl` SET `fullname` = ?, `phone` = ?, `email` = ?, `address` = ? WHERE `id` = ?");
            $stmt->execute([$fullname, $phone,$emailToStore, $address,$id]);

            echo json_encode(['status' => 'success', 'message' => 'Customer updated successfully!']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        }
        exit;
    }
}