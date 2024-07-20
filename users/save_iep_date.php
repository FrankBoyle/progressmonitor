<?php
session_start();
include('auth_session.php');
include('db.php');

// Enable PHP error logging
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
//ini_set('log_errors', 1);
//ini_set('error_log', 'error_log.log');  // Ensure this file is writable by the server

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $iep_date = isset($data['iep_date']) ? $data['iep_date'] : null;
    $student_id = isset($data['student_id']) ? $data['student_id'] : null;

    if ($iep_date && $student_id) {
        $stmt = $connection->prepare("UPDATE Students_new SET IEP_Date = ? WHERE student_id_new = ?");
        if ($stmt->execute([$iep_date, $student_id])) {
            echo json_encode(["success" => true, "message" => "IEP date saved successfully."]);
        } else {
            echo json_encode(["success" => false, "message" => "Error saving IEP date."]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Invalid data."]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method."]);
}
?>
