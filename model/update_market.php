<?php
  require 'Database.php';
  if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['unitId'];
    $department = $_POST['unit'];

    $agent = (!empty($_POST['agent']) && trim($_POST['agent']) !== '') ? trim($_POST['agent']) : null;
    $secondagent = (!empty($_POST['secondagent']) && trim($_POST['secondagent']) !== '') ? trim($_POST['secondagent']) : null;
   
    $errors = [];
    $success = [];

    if(empty(trim($department))){
      $errors['unitUpdate'] = 'Market cannot be empty!';
    }

    if(empty($errors)){
      $stmt = $db->conn->prepare('UPDATE `market` SET `agent_id` = :agent, `secondagent` = :secondagent, `market_name` = :unit WHERE `market`.`id` = :DID');
      $stmt->bindParam(':unit', $department, PDO::PARAM_STR);
      $stmt->bindParam(':agent', $agent, PDO::PARAM_STR);
      $stmt->bindParam(':secondagent', $secondagent, PDO::PARAM_STR);
      $stmt->bindParam(':DID', $id, PDO::PARAM_INT);
      $result = $stmt->execute();
      if($result){
        $success['message'] = 'Market updated successfully!';
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
        'success' => $success['message']
      ]);
    }
  }
?>