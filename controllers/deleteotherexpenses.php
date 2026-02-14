<?php
require 'model/Database.php';

if (isset($_GET['id']) && isset($_GET['tid'])) {

    $id  = (int) $_GET['id'];     
    $tid = (int) $_GET['tid'];     

    try {
        $db = new Database();

        // Delete only the selected record
        $stmt = $db->conn->prepare("DELETE FROM expenses WHERE id = :id");
        $stmt->execute(['id' => $id]);

        // Optional: check if deleted
        if ($stmt->rowCount() > 0) {
            header("Location: /transportationexp?id=" . $tid . "&msg=deleted");
            exit;
        } else {
            header("Location: /transportationexp?id=" . $tid . "&msg=notfound");
            exit;
        }

    } catch (PDOException $e) {
        echo "Delete Error: " . $e->getMessage();
    }
} else {
    echo "Invalid request.";
}
?>
