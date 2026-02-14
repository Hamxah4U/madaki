<?php
require 'Database.php';

if(isset($_POST['driverID'])){
  $driverID = $_POST['driverID'];
  $driver_name = $_POST['driver_name'];
  $bossno = $_POST['bossno'];
  $driver_amount = $_POST['driver_amount'];
  $amount_per_animal = $_POST['amount_per_animal'];
  $yan_waju = $_POST['yan_waju'];

  $stmt = $db->conn->prepare('UPDATE transportation SET first_payment = :first_payment, second_payment = :second_payment, third_payment = :third_payment, driver_name = :driver_name, bossno = :bossno, driver_amount = :driver_amount, amount_per_animal = :amount_per_animal, yan_waju = :yan_waju WHERE id = :id');
  $result = $stmt->execute([
    ':first_payment' => $_POST['first_payment'],
    ':second_payment' => $_POST['second_payment'],
    ':third_payment' => $_POST['third_payment'],
    ':driver_name' => $driver_name,
    ':bossno' => $bossno,
    ':driver_amount' => $driver_amount,
    ':amount_per_animal' => $amount_per_animal,
    ':yan_waju' => $yan_waju,
    ':id' => $driverID
  ]);

  if($result){
    echo json_encode([
      'status' => true,
      'message' => 'Driver info updated successfully'
    ]);
    exit;
  }
}