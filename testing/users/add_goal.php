<?php
session_start();
include('auth_session.php');
include('db.php');

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $studentId = $_POST['student_id'];
        $goalDescription = $_POST['goal_description'];
        $goalDate = $_POST['goal_date'];
        $metadataId = $_POST['metadata_id'];
        $schoolId = $_POST['school_id'];

        // Insert the new goal into the Goals table
        $stmt = $connection->prepare("
            INSERT INTO Goals (student_id, goal_description, goal_date, school_id, metadata_id)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$studentId, $goalDescription, $goalDate, $schoolId, $metadataId]);

        echo json_encode(["message" => "Goal added successfully."]);
    } else {
        echo json_encode(["error" => "Invalid request method."]);
    }
} catch (Exception $e) {
    error_log("Error adding goal: " . $e->getMessage());
    echo json_encode(["error" => "Error adding goal: " . $e->getMessage()]);
}
?>
