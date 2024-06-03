<?php
session_start();
include('auth_session.php');
include('db.php');

// Enable PHP error logging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', 'error_log.log');  // Ensure this file is writable by the server

header('Content-Type: application/json');

if (!isset($connection)) {
    error_log("Database connection is not set.");
    echo json_encode(["error" => "Database connection is not set."]);
    die();
}

$teacherId = $_SESSION['teacher_id'];
$groupId = $_GET['group_id'] ?? '';

if ($groupId) {
    function fetchStudentsByGroup($groupId) {
        global $connection;
        $stmt = $connection->prepare("
            SELECT s.student_id_new AS student_id, s.first_name, s.last_name, CONCAT(s.first_name, ' ', s.last_name) AS name 
            FROM Students_new s
            INNER JOIN StudentGroup sg ON s.student_id_new = sg.student_id
            WHERE sg.group_id = ?
        ");
        $stmt->execute([$groupId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    try {
        $students = fetchStudentsByGroup($groupId);
        echo json_encode($students);
    } catch (Exception $e) {
        error_log("Error fetching students by group: " . $e->getMessage());
        echo json_encode(["error" => "Error fetching students by group: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["error" => "Invalid request, group_id not set"]);
}
?>

