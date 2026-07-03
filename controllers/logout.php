<?php
    // session_start();
    // session_unset();
    // session_destroy();
    // header('location:/');
    // exit();
?>

<?php
    session_start();
    require 'model/Database.php';

    if (isset($_SESSION['log_id'])) {
        
        $stmt = $db->conn->prepare("UPDATE `user_logs_tbl` SET `logout_time` = NOW(), `logout_reason` = 'Manual' WHERE `id` = :log_id");
        $stmt->execute(['log_id' => $_SESSION['log_id']]);
    }

    session_unset();
    session_destroy();

    header("Location: /");
    exit();