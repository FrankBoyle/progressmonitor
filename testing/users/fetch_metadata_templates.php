<?php
session_start();
include('auth_session.php');
include('db.php');

header('Content-Type: application/json');

try {
    $schoolId = $_SESSION['school_id'];
    $stmt = $connection->prepare("SELECT metadata_id, category_name FROM Metadata WHERE school_id = ?");
    $stmt->execute([$schoolId]);
    $metadata = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($metadata);
} catch (Exception $e) {
    error_log("Error fetching metadata: " . $e->getMessage());
    echo json_encode(["error" => "Error fetching metadata: " . $e->getMessage()]);
}
?>
