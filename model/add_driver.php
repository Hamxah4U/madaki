<?php
require 'Database.php';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
  $driver_name = $_POST['driver_name'];
  $bossno = $_POST['bossno'];
  $driver_amount = $_POST['driver_amount'];
  $amount_per_animal = $_POST['amount_per_animal'];
  $first_payment = $_POST['first_payment'];
  $second_payment = $_POST['second_payment'];
  $third_payment = $_POST['third_payment'];
  $agent = $_POST['agent'];
  $deliverydate = $_POST['deliverydate'];
  

  $errors = [];
  $success = [];
    
  if(empty($driver_name)){
    $errors['driver_name'] = 'Driver name is required';
  }

  if(empty($bossno)){
    $errors['bossno'] = 'Boss number is required';
  }

  // if(empty($driver_amount)){
  //   $errors['driver_amount'] = 'Driver amount is required';
  // }

  // if(empty($amount_per_animal)){
  //   $errors['amount_per_animal'] = 'Amount per animal is required';
  // }


    if(empty($errors)){
      session_start();
      $user = $_SESSION['userID'];
      $stmt = $db->conn->prepare('INSERT INTO `transportation` (`driver_name`, `bossno`, `date_record`, `time_record`, `driver_amount`, `amount_per_animal`, `first_payment`, `second_payment`, `third_payment`, `yan_waju`, `created_by`,`agent`, `deliverydate`) 
      VALUES (:driver_name, :bossno, CURDATE(), CURTIME(), :driver_amount, :amount_per_animal, :first_payment, :second_payment, :third_payment, :yan_waju, :created_by, :agent, :deliverydate ) ');
      $result = $stmt->execute([
        ':driver_name' => $driver_name,
        ':bossno' => $bossno,
        ':driver_amount' => $driver_amount,
        ':amount_per_animal' => $amount_per_animal,
        ':first_payment' => $first_payment,
        ':second_payment' => $second_payment,
        ':third_payment' => $third_payment,
        ':yan_waju' => $_POST['yan_waju'],
        ':created_by' => $user,
        ':agent' => $agent,
        ':deliverydate' => $deliverydate
      ]);

      if($result){
        $success['message'] = 'Driver added successfully';
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
