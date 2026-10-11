<?php
  require 'Database.php';
  require 'vendor/autoload.php';

  if ($_SERVER['REQUEST_METHOD'] == 'POST') {
      $creditor_id = trim($_POST['creditor_id'] ?? null);
      $amount      = trim($_POST['amount'] ?? null);
      $receiver_id = trim($_POST['receiver_id'] ?? null);
      $market_id = trim($_POST['market_id'] ?? null);

      if (empty($creditor_id) || empty($amount) || empty($receiver_id)) {
          echo json_encode(['status' => false, 'error' => 'All fields are required.']);
          exit;
      }

      try {
          $stmt = $db->conn->prepare('
              INSERT INTO creditor_payments 
              (creditor_id, amount, receiver_id, daterecorded, timerecorded, market_id) 
              VALUES (:creditor_id, :amount, :receiver_id, CURDATE(), CURTIME(), :market_id)
          ');

          $stmt->execute([
              ':creditor_id' => $creditor_id,
              ':amount'      => $amount,
              ':receiver_id' => $receiver_id,
              ':market_id' => $market_id
          ]);

          echo json_encode(['status' => true, 'message' => 'Payment recorded successfully.']);
      } catch (PDOException $e) {
          echo json_encode(['status' => false, 'error' => $e->getMessage()]);
      }
  }
?>