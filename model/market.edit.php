<?php
  require 'Database.php';
  $id = $_GET['deptId'];

  $stmt = $db->conn->prepare('SELECT * FROM `market` WHERE id = :id');
  $stmt->bindParam(':id', $id);
  $stmt->execute();
  $dept = $stmt->fetch(PDO::FETCH_ASSOC);
  echo json_encode($dept);