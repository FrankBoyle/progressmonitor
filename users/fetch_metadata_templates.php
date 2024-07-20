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
    // Fetch metadata IDs that have been used in the Goals table for the selected student
    $stmt = $connection->prepare("
        SELECT metadata_id 
        FROM Goals 
        WHERE school_id = :school_id 
        AND student_id_new = :student_id
    ");
    $stmt->bindParam(':school_id', $school_id, PDO::PARAM_INT);
    $stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
    $stmt->execute();
    $usedMetadata = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

    // Convert the array of used metadata IDs to a comma-separated string for the next query
    $usedMetadataIds = implode(',', array_map('intval', $usedMetadata));

    // Fetch metadata that is not used in the Goals table for the selected student
    $query = "SELECT metadata_id, category_name FROM Metadata WHERE school_id = :school_id";
    if (!empty($usedMetadataIds)) {
        $query .= " AND metadata_id NOT IN ($usedMetadataIds)";
    }

    $stmt = $connection->prepare($query);
    $stmt->bindParam(':school_id', $school_id, PDO::PARAM_INT);
    $stmt->execute();
    $templates = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($templates);
} catch (PDOException $e) {
    echo json_encode(["error" => "Error fetching metadata templates: " . $e->getMessage()]);
}
?>

