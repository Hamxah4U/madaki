<?php
  require 'Database.php';
  session_start();
  // $stmt = $db->query("SELECT COALESCE(e.ttexp, 0) AS ttexp, a.Fullname AS agent_name, COALESCE(mt.totalMoneyInAnimal, 0) AS totalMoneyInAnimal, COALESCE(mi.moneyOutTotal, 0) AS moneyOutTotal, m.id, m.market_name, m.status FROM market m LEFT JOIN users_tbl a ON a.userID = m.agent_id LEFT JOIN ( SELECT market_code, SUM(total) AS totalMoneyInAnimal FROM market_transaction GROUP BY market_code ) mt ON mt.market_code = m.id LEFT JOIN ( SELECT market_id, SUM(amount) AS moneyOutTotal FROM moneyin GROUP BY market_id ) mi ON mi.market_id = m.id LEFT JOIN ( SELECT driver_id, agent_id, SUM(amount) AS ttexp FROM expenses WHERE status = 'exp' GROUP BY driver_id ) e ON m.agent_id = e.agent_id WHERE m.status = 'active' ORDER BY m.id DESC");
 $stmt = $db->query("
  SELECT 
      COALESCE(oe.ttotherexp, 0) AS ttotherexp, 
      COALESCE(e.ttexp, 0) AS ttexp, 
      a.Fullname AS agent_name, 
      b.Fullname AS second_agent_name,
      COALESCE(mt.totalMoneyInAnimal, 0) AS totalMoneyInAnimal, 
      COALESCE(mi.moneyOutTotal, 0) AS moneyOutTotal, 
      m.id, 
      m.market_name, 
      m.status
  FROM market m 
  LEFT JOIN users_tbl a ON a.userID = m.agent_id 
  LEFT JOIN users_tbl b ON b.userID = m.secondagent
  LEFT JOIN ( 
      SELECT market_code, SUM(total) AS totalMoneyInAnimal 
      FROM market_transaction
      GROUP BY market_code 
  ) mt ON mt.market_code = m.id 
  LEFT JOIN ( 
      SELECT market_id, SUM(amount) AS moneyOutTotal 
      FROM moneyin
      GROUP BY market_id 
  ) mi ON mi.market_id = m.id 
  LEFT JOIN ( 
      SELECT agent_id, SUM(amount) AS ttexp 
      FROM expenses 
      WHERE status = 'exp' 
      GROUP BY agent_id 
  ) e ON m.agent_id = e.agent_id 
  LEFT JOIN (
      SELECT agent_id, SUM(amount) AS ttotherexp 
      FROM expenses  
      WHERE status = 'other_exp' 
      GROUP BY agent_id
  ) oe ON m.agent_id = oe.agent_id
  WHERE m.status = 'active' AND (m.agent_id = '".$_SESSION['userID']."' OR m.secondagent = '".$_SESSION['userID']."' OR '".$_SESSION['super_role']."'  = 'Super Admin')
  ORDER BY m.id DESC;
");
  $units = $stmt->fetchAll(PDO::FETCH_ASSOC);
  echo json_encode($units);


