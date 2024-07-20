<?php
session_start();
include('auth_session.php');
include('db.php');

header('Content-Type: application/json');

try {
    $student_id = $_GET['student_id'];
    $stmt = $connection->prepare("SELECT DISTINCT m.metadata_id, m.category_name 
                                  FROM Goals g
                                  JOIN Metadata m ON g.metadata_id = m.metadata_id
                                  WHERE g.student_id = :student_id");
    $stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
    $stmt->execute();
    $metadataEntries = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Ensure it returns an array, even if it's empty
    echo json_encode($metadataEntries);
} catch (PDOException $e) {
    echo json_encode(["error" => "Error fetching metadata: " . $e->getMessage()]);
}
?>

