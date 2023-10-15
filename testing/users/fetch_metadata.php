<?php
include ('db.php');

// Error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (isset($_GET['metadata_id'])) {
    // Fetch specific score names for the provided metadata_id
    $metadataId = $_GET['metadata_id'];

    $query = $connection->prepare("SELECT score1_name, score2_name, score3_name, score4_name, score5_name, score6_name, score7_name, score8_name, score9_name, score10_name FROM Metadata WHERE metadata_id = ?");
    $query->execute([$metadataId]);

    $result = $query->fetch(PDO::FETCH_ASSOC);

    // Return the score names as a JSON array
    echo json_encode(array_values($result));
} else {
    // Fetch all metadata entries for dropdown
    $query = $connection->prepare("SELECT metadata_id, category_name FROM Metadata");
    $query->execute();

    $metadataEntries = $query->fetchAll(PDO::FETCH_ASSOC);

    // Return as JSON
    echo json_encode($metadataEntries);
}
?>


