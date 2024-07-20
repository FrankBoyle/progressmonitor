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

if (isset($_GET['student_id']) && isset($_GET['metadata_id'])) {
    $student_id = $_GET['student_id'];
    $metadata_id = $_GET['metadata_id'];

    try {
        $stmt = $connection->prepare("SELECT reporting_period FROM Goal_notes WHERE student_id_new = ? AND metadata_id = ? ORDER BY reporting_period ASC");
        $stmt->execute([$student_id, $metadata_id]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        header('Content-Type: application/json');
        echo json_encode($result);
    } catch (PDOException $e) {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    header('Content-Type: application/json');
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid request or missing parameters.']);
}
?>
