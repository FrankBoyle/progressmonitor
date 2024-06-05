<?php
include('db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $studentId = $_POST['student_id'] ?? '';
    $goalDescription = $_POST['goal_description'] ?? '';
    $goalDate = $_POST['goal_date'] ?? '';
    $metadataId = $_POST['metadata_id'] ?? '';

    // Ensure all required fields are provided
    if (empty($studentId) || empty($goalDescription) || empty($goalDate) || empty($metadataId)) {
        echo 'All fields are required.';
        exit;
    }

    try {
        $stmt = $connection->prepare("INSERT INTO Goals (student_id, goal_description, goal_date, metadata_id) VALUES (?, ?, ?, ?)");
        $stmt->execute([$studentId, $goalDescription, $goalDate, $metadataId]);

        echo "Goal added successfully.";
    } catch (PDOException $e) {
        echo "Error adding goal: " . $e->getMessage();
    }
}
?>
