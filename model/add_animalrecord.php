<?php
require 'Database.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $animals      = $_POST['animal'] ?? [];
    $amounts      = $_POST['amount'] ?? [];
    $market_id    = $_POST['market_id'] ?? [];
    $market_code  = $_POST['market_code'] ?? null;
    $number       = $_POST['number'] ?? [];
    $created_date = $_POST['created_date'] ?? [];
    $edit_id      = $_POST['edit_id'] ?? '';

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
            'amount'    => ($amounts[0] !== '' && $amounts[0] !== null) ? $amounts[0] : null,
            'sn_number' => !empty($number[0]) ? $number[0] : null,
            'id'        => $edit_id
        ]);

        echo "updated";
        exit;
    }

    // --- INSERT NEW RECORDS ---
    try {
        // We use COALESCE(:date_create, CURDATE()) so that if no date is provided, it defaults to today
        $stmt = $db->conn->prepare("
            INSERT INTO market_transaction (qty, market_id, market_code, animal_id, amount, user_id, date_create, time_create, sn_number)
            VALUES ('1', :market_id, :market_code, :animal_id, :amount, :user_id, COALESCE(:date_create, CURDATE()), CURTIME(), :sn_number)
        ");

        // Grab the batch date from the first input element since there is only one date picker in your form
        $single_batch_date = !empty($created_date[0]) ? $created_date[0] : null;

        for ($i = 0; $i < count($animals); $i++) {
            
            $animal_val = !empty($animals[$i]) ? $animals[$i] : null;
            $amount_val = ($amounts[$i] !== '' && $amounts[$i] !== null) ? $amounts[$i] : null;
            $market_val = !empty($market_id[$i]) ? $market_id[$i] : null;
            $number_val = !empty($number[$i]) ? $number[$i] : null;
            
            // Fallback: use individual row index date if it exists, otherwise use the single batch date
            $date_val   = !empty($created_date[$i]) ? $created_date[$i] : $single_batch_date;

            // Optional guard: Skip inserting if the row is completely empty
            if ($animal_val === null && $amount_val === null && $market_val === null && $number_val === null) {
                continue;
            }

            $stmt->execute([
                'market_id'   => $market_val,
                'market_code' => $market_code,
                'animal_id'   => $animal_val,
                'amount'      => $amount_val,
                'user_id'     => $_SESSION['userID'] ?? null,
                'sn_number'   => $number_val,
                'date_create' => $date_val
            ]);
        }

        echo "success";

    } catch (PDOException $e) {
        // If it fails, you can temporarily echo $e->getMessage() for debugging purposes:
        // echo "error: " . $e->getMessage();
        echo "error";
    }
}
?>