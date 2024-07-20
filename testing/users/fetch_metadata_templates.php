<?php
session_start();
include('auth_session.php');
include('db.php');

header('Content-Type: application/json');

try {
    if (!isset($_GET['metadata_id'])) {
        throw new Exception('Missing required parameter: metadata_id.');
    }

    $metadataId = $_GET['metadata_id'];

    error_log("Fetching metadata details for metadata_id: " . $metadataId); // Log metadata_id

    $stmt = $connection->prepare("SELECT * FROM Metadata WHERE metadata_id = :metadata_id");
    $stmt->bindParam(':metadata_id', $metadataId, PDO::PARAM_INT);
    $stmt->execute();
    $metadata = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$metadata) {
        throw new Exception('Metadata not found.');
    }

    error_log("Metadata Details: " . json_encode($metadata)); // Log data
    echo json_encode($metadata);
} catch (Exception $e) {
    error_log("Error fetching metadata details: " . $e->getMessage());
    echo json_encode(["error" => "Error fetching metadata details: " . $e->getMessage()]);
}

?>

