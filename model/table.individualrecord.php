<?php
require 'Database.php';

if(!isset($_GET['agent'])) {
    echo json_encode(['data' => []]);
    exit();
}

$agentId = (int) $_GET['agent'];

// Join supplytoagent with department_tbl to get animal name
$stmt = $db->conn->prepare("
    SELECT u.Fullname, s.animal_no, s.qty, s.price, d.Department AS animal_name
    FROM supplytoagent s
    JOIN department_tbl d ON s.animal_id = d.deptID
    JOIN users_tbl u ON u.userID = agent_id
    WHERE s.agent_id = :agent
    ORDER BY s.date_sent DESC, s.time_sent DESC
");
$stmt->execute(['agent' => $agentId]);
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['data' => $transactions]);
