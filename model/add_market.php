<?php
  require 'Database.php';
  if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $errors = [];
    $success = [];
    $unit = trim($_POST['unit']);

    $stmtUnit = $db->checkExist('SELECT `market_name` FROM `market` WHERE `market_name` = :department', ['department' => $unit]);
    $unitExist = $stmtUnit->rowCount();

    if($unitExist > 0){
      $errors['unitExist'] = 'Market already exist!';
    }

    if(empty($unit)){
      $errors['unit'] = 'Market name is required!';
    }

    if(empty($errors)){
      session_start();
      $user = $_SESSION['userID'];
      $date = date('Y-m-d');
      $time = date('H:i:s');
      $stmt = $db->conn->prepare('INSERT INTO `market` (`market_name`, `created_by`, `date_create`,`time_create`) VALUES (:department, :registerby, curdate(), curtime())');
      $stmt->bindParam(':department', $unit, PDO::PARAM_STR);
      $stmt->bindParam(':registerby', $user, PDO::PARAM_STR);

      $result = $stmt->execute();

      if($result){
        $success['message'] = 'Market save successfull!';
      }
    }

    if(count($errors) > 0){
      echo json_encode([
        'status' => false,
        'errors' => $errors,
      ]);
    }else{
      echo json_encode([
        'status' => true,
        'message' => $success['message'] ?? '',
      ]);
    }

  }
?>