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
    $schoolId = $_SESSION['school_id'];

    $stmt = $connection->prepare("SELECT metadata_id, category_name FROM Metadata WHERE school_id = ? AND metadata_template = 1");
    $stmt->execute([$schoolId]);
    $templates = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($templates);
} catch (Exception $e) {
    error_log("Error fetching templates: " . $e->getMessage());
    echo json_encode(["error" => "Error fetching templates: " . $e->getMessage()]);
}
?>
