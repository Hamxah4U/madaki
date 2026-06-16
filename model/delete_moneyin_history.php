<?php

require 'Database.php';

$id = $_POST['id'];

$stmt = $db->conn->prepare("
    DELETE FROM moneyin
    WHERE id = ?
");

if($stmt->execute([$id])){
    echo "Deleted Successfully";
}else{
    print_r($stmt->errorInfo());
}