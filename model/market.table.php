<?php
  require 'Database.php';
  // $stmt = $db->query('SELECT * FROM `market`');
  $stmt = $db->query("SELECT m.id, COALESCE(SUM(mi.amount), 0) AS moneyinTotal, m.market_name, m.status FROM `market` m LEFT JOIN moneyin mi ON m.id = mi.market_id WHERE m.status = 'Active' GROUP BY m.market_name ORDER BY m.id DESC");
  $units = $stmt->fetchAll(PDO::FETCH_ASSOC);
  echo json_encode($units);
									