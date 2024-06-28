<?php
session_start();
include('auth_session.php');
include('db.php');

header('Content-Type: application/json');

try {
    if (isset($_POST['student_id'], $_POST['goal_description'], $_POST['goal_date'], $_POST['metadata_id'])) {
        $studentId = $_POST['student_id'];
        $goalDescription = $_POST['goal_description'];
        $goalDate = $_POST['goal_date'];
        $originalMetadataId = $_POST['metadata_id'];
        $schoolId = $_SESSION['school_id'];

        if (empty($studentId) || empty($goalDescription) || empty($goalDate) || empty($originalMetadataId) || empty($schoolId)) {
            throw new Exception('Missing required parameters.');
        }

        // Copy the original metadata to create a new entry with a new metadata_id
        $stmt = $connection->prepare("SELECT * FROM Metadata WHERE metadata_id = ?");
        $stmt->execute([$originalMetadataId]);
        $metadata = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$metadata) {
            throw new Exception('Original metadata not found.');
        }

        // Prepare and execute the insert statement for the new metadata
        $stmt = $connection->prepare("
            INSERT INTO Metadata (category_name, other_columns) 
            VALUES (?, ?)
        ");
        $stmt->execute([$metadata['category_name'], $metadata['other_columns']]); // Add other columns as needed

        // Get the new metadata_id
        $newMetadataId = $connection->lastInsertId();

        // Prepare and execute the insert statement for the goal
        $stmt = $connection->prepare("
            INSERT INTO Goals (student_id_new, goal_description, goal_date, school_id, metadata_id) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$studentId, $goalDescription, $goalDate, $schoolId, $newMetadataId]);

        echo json_encode(["message" => "Goal added successfully."]);
    } else {
        throw new Exception('Invalid request, missing required parameters.');
    }
} catch (Exception $e) {
    error_log("Error adding goal: " . $e->getMessage());
    echo json_encode(["error" => "Error adding goal: " . $e->getMessage()]);
}
?>
