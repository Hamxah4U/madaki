<?php
  require 'Database.php';
  $stmt = $db->query("SELECT a.Fullname AS agent_name, COALESCE(SUM(mt.total), 0) AS totalMoneyInAnimal, COALESCE(SUM(mi.amount), 0) AS moneyOutTotal, m.id, m.market_name, m.status FROM market m LEFT JOIN market_transaction mt ON mt.market_code = m.id LEFT JOIN moneyin mi ON mi.market_id = m.id LEFT JOIN users_tbl a ON a.userID = m.agent_id WHERE m.status = 'active' GROUP BY m.id ORDER BY m.id DESC");
  $units = $stmt->fetchAll(PDO::FETCH_ASSOC);
  echo json_encode($units);


