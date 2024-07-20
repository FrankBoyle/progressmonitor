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

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"));

if (isset($data->teacher_id)) {
    $teacherId = $data->teacher_id;
    $schoolId = $_SESSION['school_id'];  // Fetching the school_id from session

    try {
        $query = $connection->prepare("
            DELETE FROM Teachers 
            WHERE teacher_id = :teacher_id AND school_id = :school_id
        ");
        $query->bindParam(':teacher_id', $teacherId, PDO::PARAM_INT);
        $query->bindParam(':school_id', $schoolId, PDO::PARAM_INT);
        $query->execute();

        if ($query->rowCount() > 0) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'No matching record found']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
}
?>
