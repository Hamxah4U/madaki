<?php
  require 'Database.php';

  if($_SERVER['REQUEST_METHOD'] == 'POST'){
    session_start();
    $errors = [];
    $success = [];

    $dpt = htmlentities($_POST['dpt']);
    $price = htmlspecialchars($_POST['cprice']);   
    $tcode = htmlspecialchars($_POST['tcode']);    
    $qty = htmlspecialchars($_POST['issuedqty']);    
    $user = $_SESSION['userID'];
    $issuedqty = htmlentities($_POST['issuedqty']);
    $agent = htmlentities($_POST['agent']);



    if(!empty($issuedqty) && intval($issuedqty) <= 0){
      $errors['issuedqty_'] = 'Issued quantity cannot be less than or equal to 0';
    }

    if(empty(trim($issuedqty))){
      $errors['issuedqty'] = 'Issued quantity is required';
    }

    if($dpt == '--choose--'){
      $errors['unit'] = 'Animal is required!';
    }

    if(empty(trim($price))){
      $errors['price'] = 'Price is required!';
    }

    if(empty($errors)){
      $stmt = $db->conn->prepare("INSERT INTO supplytoagent (agent_id, animal_id, animal_no, qty, price, date_sent, time_sent, sent_by)
      VALUES (:agent_id, :animal_id, :animal_no, :qty, :price, CURDATE(), CURRENT_TIME(), :sent_by) ");
      $stmt->bindParam(':agent_id', $agent, PDO::PARAM_INT); 
      $stmt->bindParam(':animal_id', $dpt, PDO::PARAM_INT);
      $stmt->bindParam(':animal_no', $tcode, PDO::PARAM_STR);
      $stmt->bindParam(':qty', $issuedqty, PDO::PARAM_INT);
      $stmt->bindParam(':price', $price, PDO::PARAM_INT);
      $stmt->bindParam(':sent_by', $user, PDO::PARAM_INT);
      $result = $stmt->execute();
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