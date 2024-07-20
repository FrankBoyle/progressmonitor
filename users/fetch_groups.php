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

$teacherId = $_SESSION['teacher_id'];

function fetchAllRelevantGroups($teacherId) {
    global $connection;
    $stmt = $connection->prepare("
        SELECT g.*
        FROM Groups g
        WHERE g.teacher_id = :teacherId
        UNION
        SELECT g.*
        FROM Groups g
        INNER JOIN SharedGroups sg ON g.group_id = sg.group_id
        WHERE sg.shared_teacher_id = :teacherId
    ");
    $stmt->bindParam(':teacherId', $teacherId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$groups = fetchAllRelevantGroups($teacherId);

echo json_encode($groups);
?>
