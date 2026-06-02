<?php
  require 'Database.php';
  
  // $stmt = $db->query("SELECT COALESCE(SUM(mt.total)) AS totalMoneyInAnimal, m.id, COALESCE(SUM(mi.amount), 0) AS moneyOutTotal, m.market_name, m.status
  //   FROM `market` m
  //   LEFT JOIN moneyin mi ON m.id = mi.market_id 
  //   LEFT JOIN market_transaction mt ON mt.market_id = m.id 
  //   WHERE m.status = 'Active' AND mi.market_id = mt.market_id AND mt.user_id = mi.user_id 
  //   GROUP BY m.market_name, mt.market_id, mt.user_id
  //   ORDER BY m.id DESC");
  $stmt = $db->query("SELECT a.Fullname AS agent_name, COALESCE(SUM(mt.total)) AS totalMoneyInAnimal, m.id, COALESCE(SUM(mi.amount), 0) AS moneyOutTotal, m.market_name, m.status
    FROM `market` m
    LEFT JOIN market_transaction mt ON mt.market_id = m.id
    LEFT JOIN moneyin mi ON m.id = mi.market_id
    LEFT JOIN users_tbl a ON a.userID = m.agent_id
    WHERE m.status = 'Active' 
    GROUP BY m.market_name 
    ORDER BY m.id DESC");
  $units = $stmt->fetchAll(PDO::FETCH_ASSOC);
  echo json_encode($units);


