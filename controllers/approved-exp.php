<?php
require_once 'model/Database.php'; 
require 'views/partials/security.php'; 

// Ensure only Admins can approve
// if ($_SESSION['role'] !== 'Admin') {
//     die("Unauthorized access.");
// }

if (isset($_GET['id']) && isset($_GET['tid'])) {
    $expense_id = (int)$_GET['id'];
    $market_id = (int)$_GET['tid'];

    // Update pstatus to 'approved'
    $stmt = $db->conn->prepare("UPDATE `expenses` SET `pstatus` = 'approved' WHERE `id` = :id");
    $success = $stmt->execute(['id' => $expense_id]);

    if ($success) {
        // Redirect back to your transportation expense page with the market ID
        header("Location: /view-market?marketId=" . $market_id);
        exit();
    } else {
        echo "Error: Could not approve the expense.";
    }
} else {
    echo "Error: Missing parameters.";
}