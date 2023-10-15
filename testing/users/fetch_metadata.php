<?php
include 'db.php';

if (isset($_GET['metadata_id'])) {
    // Fetch specific score names for the provided metadata_id
    $metadataId = $_GET['metadata_id'];

    $query = $pdo->prepare("SELECT score1_name, score2_name, score3_name, score4_name, score5_name, score6_name, score7_name, score8_name, score9_name, score10_name FROM metadata WHERE metadata_id = ?");
    $query->execute([$metadataId]);

    $result = $query->fetch(PDO::FETCH_ASSOC);

    // Return the score names as a JSON array
    echo json_encode(array_values($result));
} else {
    // Fetch all metadata entries for dropdown
    $query = $pdo->prepare("SELECT metadata_id, category_name FROM metadata");
    $query->execute();

    $metadataEntries = $query->fetchAll(PDO::FETCH_ASSOC);

    // Return as JSON
    echo json_encode($metadataEntries);
}
?>

