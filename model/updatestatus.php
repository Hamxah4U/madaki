<?php
require 'Database.php';

if($_SERVER['REQUEST_METHOD'] == 'POST'){

    $errors = [];

    $driverID = $_POST['driverID'];
    $status   = $_POST['status'];

    if(empty($status)){
        $errors['status'] = 'Please select status!';
    }

    if(empty($driverID)){
        $errors['driver'] = 'Please select driver!';
    }

    // if validation errors
    if(count($errors) > 0){

        echo json_encode([
            'status' => false,
            'errors' => $errors
        ]);

        exit;
    }

    $stmt = $db->conn->prepare("
        UPDATE transportation 
        SET status_id = :status_id 
        WHERE id = :id
    ");

    $update = $stmt->execute([
        'status_id' => $status,
        'id' => $driverID
    ]);

    if($update){

        echo json_encode([
            'status' => true,
            'success' => [
                'message' => 'Status updated successfully!'
            ]
        ]);

    }else{

        echo json_encode([
            'status' => false,
            'errors' => [
                'database' => 'Failed to update status'
            ]
        ]);

    }

}
?>