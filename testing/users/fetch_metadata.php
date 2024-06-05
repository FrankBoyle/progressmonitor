<?php
include('db.php');

try {
    $stmt = $connection->prepare("SELECT metadata_id, category_name FROM Metadata");
    $stmt->execute();
    $metadataEntries = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($metadataEntries);
} catch (PDOException $e) {
    echo "Error fetching metadata: " . $e->getMessage();
}
?>
