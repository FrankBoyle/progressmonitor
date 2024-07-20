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
    if (isset($_GET['metadata_id'])) {
        $metadataId = $_GET['metadata_id'];

        $stmt = $connection->prepare("SELECT * FROM Metadata WHERE metadata_id = ?");
        $stmt->execute([$metadataId]);
        $metadata = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$metadata) {
            throw new Exception('Metadata not found.');
        }

        echo json_encode($metadata);
    } else {
        throw new Exception('Invalid request, missing required parameters.');
    }
} catch (Exception $e) {
    error_log("Error fetching metadata details: " . $e->getMessage());
    echo json_encode(["error" => "Error fetching metadata details: " . $e->getMessage()]);
}
?>
