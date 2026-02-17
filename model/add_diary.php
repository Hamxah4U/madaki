<?php
require 'Database.php';
require 'vendor/autoload.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $success = [];
  $errors = [];
  $comment = trim($_POST['comment']);
  $userID = trim($_POST['transport_id']);

  if(empty($comment)) {
    $errors['comment'] = 'Comment is required!';
  }

  if(empty($userID)) {
    $errors['transport_id'] = 'Transport ID is required!';
  }

  if(empty($errors)){
    try{
      $db = new Database();
      $stmt = $db->conn->prepare('INSERT INTO `expenses` (`driver_id`,`reason`, `daterecorded`, `timerecorded`, `status`) VALUES (:driver_id, :reason, CURDATE(), CURTIME(), "diary")');
      $stmt->bindParam(':driver_id', $userID, PDO::PARAM_STR);
      $stmt->bindParam(':reason', $comment, PDO::PARAM_STR);

      $result = $stmt->execute();

      if ($result) {
        $success['message'] = 'Comment added successfully.';
    }
      if($result){
        echo json_encode([
        'status' => true,
        'success' => ['message' => 'Diary added successfully.']
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