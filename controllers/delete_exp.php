<?php
require 'model/Database.php';

if (!isset($_GET['id']) || !isset($_GET['tid'])) {
    header('Location: /transportation');
    exit();
}

$exp_id = (int) $_GET['id'];
$transport_id = (int) $_GET['tid'];
// echo "Exp ID: $exp_id, Transport ID: $transport_id"; // Debugging line
if ($exp_id <= 0) {
    header('Location: /transportationexp?id=' . $transport_id);
    exit();
}

try {

    $stmt = $db->conn->prepare("
        DELETE FROM transportation_expenses
        WHERE id = :id
        AND transportation_id = :tid
    ");

    $stmt->execute([
        'id' => $exp_id,
        'tid' => $transport_id
    ]);

} catch (PDOException $e) {
    // Optional: log error
}

// Redirect back
header('Location: /transportationexp?id=' . $transport_id);
exit();
