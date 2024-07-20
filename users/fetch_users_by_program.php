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

if (!isset($_SESSION['program_id']) || !isset($_SESSION['school_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$programId = $_SESSION['program_id'];
$schoolId = $_SESSION['school_id'];

try {
    $query = $connection->prepare("
        SELECT t1.teacher_id, t1.fname, t1.lname, a.email 
        FROM Teachers t1 
        JOIN accounts a ON t1.account_id = a.id
        WHERE t1.program_id = :programId
        AND t1.school_id != :schoolId
        AND t1.account_id NOT IN (
            SELECT t2.account_id FROM Teachers t2 WHERE t2.school_id = :schoolId
        )
    ");
    $query->bindParam("programId", $programId, PDO::PARAM_INT);
    $query->bindParam("schoolId", $schoolId, PDO::PARAM_INT);
    $query->execute();
    
    $results = $query->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($results);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>