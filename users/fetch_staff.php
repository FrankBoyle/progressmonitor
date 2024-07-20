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

$schoolId = $_SESSION['school_id'];

function fetchAllRelevantStaff($schoolId) {
    global $connection;
    $stmt = $connection->prepare("
        SELECT teacher_id, fname, lname, subject_taught, is_admin, approved 
        FROM Teachers
        WHERE school_id = :schoolId
    ");
    $stmt->bindParam(':schoolId', $schoolId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$staff = fetchAllRelevantStaff($schoolId);

echo json_encode($staff);
?>
