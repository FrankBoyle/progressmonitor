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

try {
    if (isset($_GET['group_id'])) {
        $groupId = $_GET['group_id'];

        $stmt = $connection->prepare("
            SELECT s.student_id_new AS student_id, s.first_name, s.last_name, CONCAT(s.first_name, ' ', s.last_name) AS name 
            FROM Students_new s 
            INNER JOIN StudentGroup sg ON s.student_id_new = sg.student_id_new 
            WHERE sg.group_id = ?
        ");
        $stmt->execute([$groupId]);
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($students);
    } else {
        echo json_encode(["error" => "Invalid request, group_id not set"]);
    }
} catch (Exception $e) {
    error_log("Error fetching group students: " . $e->getMessage());
    echo json_encode(["error" => "Error fetching group students: " . $e->getMessage()]);
}
?>
