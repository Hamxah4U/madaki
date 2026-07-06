<?php
    class Database{
      public $conn;
      public function __construct()
      {
        $dsn = 'mysql:host=localhost;dbname=madakitest'; //sfgeorgn_shafabillingdb  billing_db
          $dbuser = 'root';
          $dbpass = '';
          try {
            $this->conn = new PDO($dsn, $dbuser, $dbpass);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            //echo 'connected';
          } catch (PDOException $e) {
            die("DB Connection failed:" .$e->getMessage());
          }         
      }

      public function query($sql){
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt;
      }

      public function checkExist($sql, $param = []){
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($param);
        return $stmt;
      }
    }

    $db = new Database();
    $storeName = 'Madaki Venture'. '<br/>';
    $subhead = 'GLOBAL SERVICE LTD';
    $state = 'Address: 83/19 Mango Street Jos Plateau State.';
    $phone = '07051383610';
    
?>











<?php
    function logUserActivity($action_type, $target_table, $description) {
    global $db; // Pulls the global $db instance automatically

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $userID = $_SESSION['userID'] ?? 0;
    $fullname = $_SESSION['fname'] ?? 'System/Guest';
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';

    try {
        // Use $db->conn directly here
        $stmt = $db->conn->prepare("INSERT INTO `activity_logs_tbl` (`user_id`, `fullname`, `action_type`, `target_table`, `description`, `ip_address`) 
                                    VALUES (:user_id, :fullname, :action_type, :target_table, :description, :ip_address)");
        $stmt->execute([
            'user_id'      => $userID,
            'fullname'     => $fullname,
            'action_type'  => strtoupper($action_type),
            'target_table' => $target_table,
            'description'  => $description,
            'ip_address'   => $ip_address
        ]);
    } catch (PDOException $e) {
        error_log("Failed to write activity audit log: " . $e->getMessage());
    }
  }
?>