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
        $metadataId = $_POST['metadata_id'];
        $schoolId = $_SESSION['school_id'];

        if (empty($studentId) || empty($goalDescription) || empty($goalDate) || empty($metadataId) || empty($schoolId)) {
            throw new Exception('Missing required parameters.');
        }

        // Prepare and execute the insert statement
        $stmt = $connection->prepare("
            INSERT INTO Goals (student_id_new, goal_description, goal_date, school_id, metadata_id) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$studentId, $goalDescription, $goalDate, $schoolId, $metadataId]);

        echo json_encode(["message" => "Goal added successfully."]);
    } else {
        throw new Exception('Invalid request, missing required parameters.');
    }
} catch (Exception $e) {
    error_log("Error adding goal: " . $e->getMessage());
    echo json_encode(["error" => "Error adding goal: " . $e->getMessage()]);
}
?>
