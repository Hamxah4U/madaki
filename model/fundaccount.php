<?php
  require 'Database.php';

  if($_SERVER['REQUEST_METHOD'] == 'POST'){
    session_start();
    $errors = [];
    $success = [];
    $agent_id = htmlspecialchars($_POST['cid']);
    $narration = $_POST['narration'];
    $amount = $_POST['amount'];
    $fname = $_POST['fname'];
    $phone = $_POST['phone'];

    if(empty(trim($narration))){
      $errors['narration'] = 'Required!';
    }

    if(empty(trim($amount))){
      $errors['amount'] = 'Required!';
    }

    if(empty($errors)){
        $stmt = $db->conn->prepare('INSERT INTO `supplytoagent` (`agent_id`, `amount`,`narration`, `date_sent`, time_sent, funded_by) 
        VALUES (:agent_id, :amount, :narration, CURDATE(), CURRENT_TIME(), :recordbt) ');
        $result = $stmt->execute([
          ':agent_id' => $agent_id,
          ':amount' => $amount,
          ':narration' => $narration,
          ':recordbt' => $_SESSION['userID'],      
        ]);

        if($result){
          $success['message'] = 'Amount funded successfully!';
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
        'success' => $success,
      ]);
    }
  }
?>