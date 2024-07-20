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

$school_id = $_SESSION['school_id'];
$student_id = isset($_GET['student_id']) ? intval($_GET['student_id']) : 0;

try {
    // Fetch metadata IDs that are already used in the Goals table for the selected student
    $stmt = $connection->prepare("
        SELECT m.metadata_id, m.category_name 
        FROM Metadata m
        JOIN Goals g ON m.metadata_id = g.metadata_id
        WHERE g.school_id = :school_id 
        AND g.student_id_new = :student_id
    ");
    $stmt->bindParam(':school_id', $school_id, PDO::PARAM_INT);
    $stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
    $stmt->execute();
    $usedMetadataEntries = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($usedMetadataEntries);
} catch (PDOException $e) {
    echo json_encode(["error" => "Error fetching metadata: " . $e->getMessage()]);
}
?>


