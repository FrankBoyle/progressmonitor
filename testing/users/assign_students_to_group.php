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

if (!isset($connection)) {
    error_log("Database connection is not set.");
    die("Database connection is not set.");
}

$teacherId = $_SESSION['teacher_id'];
$groupId = $_POST['group_id'] ?? '';
$studentIds = isset($_POST['student_ids']) ? explode(',', $_POST['student_ids']) : [];

if ($groupId && !empty($studentIds)) {
    foreach ($studentIds as $studentId) {
        $checkStmt = $connection->prepare("SELECT * FROM StudentGroup WHERE student_id_new = ? AND group_id = ?");
        $checkStmt->execute([$studentId, $groupId]);

        if ($checkStmt->rowCount() == 0) {
            $insertStmt = $connection->prepare("INSERT INTO StudentGroup (student_id_new, group_id) VALUES (?, ?)");
            $insertStmt->execute([$studentId, $groupId]);
        }
    }
    echo "Selected students assigned to group successfully.";
} else {
    echo "Group ID or Student IDs not provided.";
}
?>

