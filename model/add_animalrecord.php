<?php
require 'Database.php';
session_start();
if($_SERVER['REQUEST_METHOD'] === 'POST') {

    $animals = $_POST['animal'];
    $amounts = $_POST['amount'];
    $market_id = $_POST['market_id'];

    try{
        $stmt = $db->conn->prepare("INSERT INTO market_transaction (qty, market_id, animal_id, amount, user_id, date_create, time_create)
            VALUES ('1', :market_id, :animal_id, :amount, :user_id,  CURDATE(), CURTIME())
        ");

        for($i = 0; $i < count($animals); $i++){

            if(!empty($animals[$i]) && !empty($amounts[$i])){

                $stmt->execute([
                    'animal_id' => $animals[$i],
                    'amount'    => $amounts[$i],
                    'market_id' => $market_id,
                    'user_id' => $_SESSION['userID']
                ]);

            }
        }

        echo "success";

    }catch(PDOException $e){
        echo "error";
    }

}
?>
