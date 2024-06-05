<?php
include('db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $goalDescription = $_POST['goal_description'] ?? '';
    $goalDate = $_POST['goal_date'] ?? '';
    $metadataId = $_POST['metadata_id'] ?? '';

    // Ensure all required fields are provided
    if (empty($goalDescription) || empty($goalDate) || empty($metadataId)) {
        echo 'All fields are required.';
        exit;
    }

    try {
        $stmt = $connection->prepare("INSERT INTO Goals (goal_description, goal_date, metadata_id) VALUES (?, ?, ?)");
        $stmt->execute([$goalDescription, $goalDate, $metadataId]);

        echo "Goal added successfully.";
    } catch (PDOException $e) {
        echo "Error adding goal: " . $e->getMessage();
    }
}
?>

