<?php
require 'Database.php';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
  $agent_name = $_POST['agent_name'];
  $amount = $_POST['amount'];
  $phone = $_POST['phone'];
  $email = $_POST['email'];

  $errors = [];
  $success = [];
    
  if(empty($agent_name)){
    $errors['driver_name'] = 'Agent name is required';
  }

  if(empty($amount)){
    $errors['bossno'] = 'Boss number is required';
  }

  if(empty($phone)){
    $errors['phone'] = 'Phone number is required';
  }

  // if(empty($amount_per_animal)){
  //   $errors['amount_per_animal'] = 'Amount per animal is required';
  // }


    if(empty($errors)){
      session_start();
      $user = $_SESSION['userID'];
      $stmt = $db->conn->prepare('INSERT INTO `users_tbl` (`Fullname`, `Email`, `Phone`, `DateRegister`, `TimeRegister`, `amount`, `Role`, `UserPassword`) 
      VALUES (:Fullname, :Email, :phone, CURDATE(), CURTIME(), :amount, :Role, :UserPassword ) ');
      $result = $stmt->execute([
        ':Fullname' => $agent_name,
        ':Email' => $email,
        ':phone' => $phone,
        ':amount' => $amount,
        ':Role' => 'Agent II',
        ':UserPassword' => password_hash('12345', PASSWORD_DEFAULT)
      ]);

      if($result){
        $success['message'] = 'Agent added successfully';
      }
    }

    if(count($errors) > 0){
        echo json_encode([
            'status' => false,
            'errors' => $errors
        ]);
    }else{
        echo json_encode([
            'status' => true,
            'success' => $success
        ]);
    }
}
