<?php
require 'Database.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $animals     = $_POST['animal'] ?? [];
    $amounts     = $_POST['amount'] ?? [];
    $market_id   = $_POST['market_id'] ?? [];
    $market_code = $_POST['market_code'] ?? null;
    $number      = $_POST['number'] ?? [];
    $edit_id     = $_POST['edit_id'] ?? '';

    // --- UPDATE EXISTING RECORD ---
    if (!empty($edit_id)) {
        $stmt = $db->conn->prepare("
            UPDATE market_transaction
            SET
                market_id = :market_id,
                animal_id = :animal_id,
                amount = :amount,
                sn_number = :sn_number
            WHERE id = :id
        ");

        $stmt->execute([
            'market_id' => !empty($market_id[0]) ? $market_id[0] : null,
            'animal_id' => !empty($animals[0]) ? $animals[0] : null,
            // Check for '0' string or 0 integer so zero amounts don't accidentally become null
            'amount'    => ($amounts[0] !== '' && $amounts[0] !== null) ? $amounts[0] : null,
            'sn_number' => !empty($number[0]) ? $number[0] : null,
            'id'        => $edit_id
        ]);

        echo "updated";
        exit;
    }

    // --- INSERT NEW RECORDS ---
    try {
        $stmt = $db->conn->prepare("
            INSERT INTO market_transaction (qty, market_id, market_code, animal_id, amount, user_id, date_create, time_create, sn_number)
            VALUES ('1', :market_id, :market_code, :animal_id, :amount, :user_id, CURDATE(), CURTIME(), :sn_number)
        ");

        for ($i = 0; $i < count($animals); $i++) {
            
            // Format individual variables into values or null
            $animal_val = !empty($animals[$i]) ? $animals[$i] : null;
            $amount_val = ($amounts[$i] !== '' && $amounts[$i] !== null) ? $amounts[$i] : null;
            $market_val = !empty($market_id[$i]) ? $market_id[$i] : null;
            $number_val = !empty($number[$i]) ? $number[$i] : null;

            // Optional guard: Skip inserting if the row is completely empty/blank
            if ($animal_val === null && $amount_val === null && $market_val === null && $number_val === null) {
                continue;
            }

            $stmt->execute([
                'market_id'   => $market_val,
                'market_code' => $market_code,
                'animal_id'   => $animal_val,
                'amount'      => $amount_val,
                'user_id'     => $_SESSION['userID'] ?? null,
                'sn_number'   => $number_val
            ]);
        }

        echo "success";

    } catch (PDOException $e) {
        echo "error";
    }
}
?>

<?php
/* 
    use PDOException;
        require 'Database.php';
        session_start();
        if($_SERVER['REQUEST_METHOD'] === 'POST') {

            $animals = $_POST['animal'];
            $amounts = $_POST['amount'];
            $market_id = $_POST['market_id'];
            $market_code = $_POST['market_code'];
            $number = $_POST['number'];
            $edit_id = $_POST['edit_id'] ?? '';

            if(!empty($edit_id)){

            $stmt = $db->conn->prepare("
                UPDATE market_transaction
                SET
                    market_id = :market_id,
                    animal_id = :animal_id,
                    amount = :amount,
                    sn_number = :sn_number
                WHERE id = :id
            ");

            $stmt->execute([
                'market_id' => $market_id[0],
                'animal_id' => $animals[0],
                'amount' => $amounts[0],
                'sn_number' => $number[0],
                'id' => $edit_id
            ]);

            echo "updated";
            exit;
        }

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

        } */
?>
