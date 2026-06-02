<?php
header('Content-Type: application/json');

require 'Database.php';

try {

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    $unitId = trim($_POST['unitId'] ?? '');
    $amount = trim($_POST['unit'] ?? '');
    $date = $_POST['date'];

    if (empty($unitId)) {
        throw new Exception('Market ID is required');
    }

    if (empty($amount)) {
        throw new Exception('Amount is required');
    }

    if (!is_numeric($amount)) {
        throw new Exception('Amount must be numeric');
    }

    // Example insert query
    // $stmt = $db->conn->prepare("INSERT INTO moneyin (market_id, amount) VALUES (?, ?)");
    // $stmt->execute([$unitId, $amount]);
  session_start();
    $stmt = $db->conn->prepare("
        INSERT INTO moneyin (
            amount,
            market_id,
            user_id,
            datercorded,
            timerecorded,
            date_in
        ) VALUES (
            :amount,
            :market_id,
            :user_id,
            :datercorded,
            :timerecorded,
            :date_in
        )
    ");

    $insert = $stmt->execute([
        ':amount'        => $amount,
        ':market_id'     => $unitId,
        ':user_id'       => $_SESSION['userID'] ?? 0,
        ':datercorded'   => date('Y-m-d'),
        ':timerecorded'  => date('H:i:s'),
        ':date_in' => $date
    ]);

    echo json_encode([
        'status' => 'success',
        'message' => 'Money added successfully'
    ]);

} catch (Exception $e) {

    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>