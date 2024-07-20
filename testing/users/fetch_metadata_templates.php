<?php
session_start();
include('auth_session.php');
include('db.php');

header('Content-Type: application/json');

try {
    $school_id = $_SESSION['school_id'];

    $stmt = $connection->prepare("SELECT metadata_id, category_name FROM Metadata WHERE school_id = :school_id AND metadata_template = 1");
    $stmt->bindParam(':school_id', $school_id, PDO::PARAM_INT);
    $stmt->execute();
    $templates = $stmt->fetchAll(PDO::FETCH_ASSOC);

    error_log("Templates: " . json_encode($templates)); // Log data

    echo json_encode($templates);
} catch (PDOException $e) {
    echo json_encode(["error" => "Error fetching metadata templates: " . $e->getMessage()]);
}
?>

