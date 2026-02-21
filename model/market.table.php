<?php
  require 'Database.php';
  $stmt = $db->query('SELECT * FROM `market`');
  $units = $stmt->fetchAll(PDO::FETCH_ASSOC);
  echo json_encode($units);
									