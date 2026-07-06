<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $inactive_limit = 20 * 60; 

    if (isset($_SESSION['last_activity'])) {
        $session_life = time() - $_SESSION['last_activity'];

        if ($session_life > $inactive_limit) {
            // --- NEW: Record Timeout in Database ---
            if (isset($_SESSION['log_id'])) { 
                $stmt = $db->conn->prepare('UPDATE `user_logs_tbl` SET `logout_time` = NOW(), `logout_reason` = "Timeout" WHERE `id` = :log_id');
                $stmt->execute(['log_id' => $_SESSION['log_id']]);
            }

            session_unset();
            session_destroy();
            header("Location: /"); 
            exit();
        }
    }

    $_SESSION['last_activity'] = time();




   /*  if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $inactive_limit = 120 * 60; 

    if (isset($_SESSION['last_activity'])) {
       
        $session_life = time() - $_SESSION['last_activity'];

        if ($session_life > $inactive_limit) {
            session_unset();
            session_destroy();
            
            header("Location: /"); 
            exit();
        }
    }

    // Update last activity
    $_SESSION['last_activity'] = time(); */