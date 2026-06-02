<?php
require 'Database.php';
session_start();
if($_SERVER['REQUEST_METHOD'] === 'POST') {

    $animals = $_POST['animal'];
    $amounts = $_POST['amount'];
    $market_id = $_POST['market_id'];
    $market_code = $_POST['market_code'];
    $number = $_POST['number'];


    try{
        $stmt = $db->conn->prepare("INSERT INTO market_transaction (qty, market_id, market_code, animal_id, amount, user_id, date_create, time_create, sn_number)
            VALUES ('1', :market_id, :market_code, :animal_id, :amount, :user_id,  CURDATE(), CURTIME(), :sn_number)
        ");

        for($i = 0; $i < count($animals); $i++){

            if(!empty($animals[$i]) && !empty($amounts[$i])){

                $stmt->execute([
                    'animal_id' => $animals[$i],
                    'amount'    => $amounts[$i],
                    'market_id' => $market_id[$i],
                    'market_code' => $market_code,
                    'user_id' => $_SESSION['userID'],
                    ':sn_number' => $number[$i]

                ]);

            }
        }

        echo "success";

    }catch(PDOException $e){
        echo "error";
    }

}
?>
