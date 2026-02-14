<?php
require 'Database.php';
require 'vendor/autoload.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $success = [];
  $errors = [];
  $amount = trim($_POST['amount']);
  $reason = trim($_POST['reason']);
  $userID = trim($_POST['userID']);
 
  
 

  if(empty($amount)) {
    $errors['amount'] = 'Amount is required!';
  }

  if(empty($reason)) {
    $errors['reason'] = 'Reason is required!';
  }

  if(empty($errors)){
    try{
      $db = new Database();
      $stmt = $db->conn->prepare('INSERT INTO `expenses` (`driver_id`,`amount`,`reason`, `daterecorded`, `timerecorded`, `status`) VALUES (:driver_id, :amount, :reason, CURDATE(), CURTIME(), "exp")');
      $stmt->bindParam(':driver_id', $userID, PDO::PARAM_STR);
      $stmt->bindParam(':amount', $amount, PDO::PARAM_STR);
      $stmt->bindParam(':reason', $reason, PDO::PARAM_STR);

      $result = $stmt->execute();

      if ($result) {
        $success['message'] = 'Expense added successfully.';
    }
      if($result){
        echo json_encode([
        'status' => true,
        'success' => ['message' => 'Expense added successfully.']
        ]);
        exit;
      }
    }catch(PDOException $e){
      die('Error:'. $e->getMessage());
    }
  }

  if (count($errors) > 0) {
      echo json_encode([
          'status' => false,
          'errors' => $errors,
      ]);
  } else {
      echo json_encode([
        'status' => true,
        'success' => ['message' => 'User successfully added']
      ]);
  }
}

?>