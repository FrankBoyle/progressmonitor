<?php
// Include the database connection script
include('./users/db.php');
header('Content-Type: application/json');

// Turn on error reporting for debugging. Remember to turn this off in production
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Function to handle and send back errors
function handleError($errorMessage) {
    echo json_encode(['success' => false, 'message' => $errorMessage]);
    exit;
}

// Check if the required POST data is present
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['goal_description']) && isset($_POST['student_id']) && isset($_POST['metadata_id']) && isset($_POST['school_id'])) {
    $goalDescription = $_POST['goal_description'];
    $studentId = $_POST['student_id'];
    $metadataId = $_POST['metadata_id'];
    $schoolId = $_POST['school_id'];
    $goalDate = isset($_POST['goal_date']) && !empty($_POST['goal_date']) ? $_POST['goal_date'] : null;

    // Prepare and bind
    $stmt = $connection->prepare("INSERT INTO Goals (student_id, goal_description, school_id, metadata_id, goal_date) VALUES (?, ?, ?, ?, ?)");
    $stmt->bindParam(1, $studentId, PDO::PARAM_INT);
    $stmt->bindParam(2, $goalDescription, PDO::PARAM_STR);
    $stmt->bindParam(3, $schoolId, PDO::PARAM_INT);
    $stmt->bindParam(4, $metadataId, PDO::PARAM_INT);
    $stmt->bindParam(5, $goalDate, PDO::PARAM_STR);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'goal_id' => $connection->lastInsertId()]);
    } else {
        handleError('Database insertion failed: ' . implode(" | ", $stmt->errorInfo()));
    }
} else {
    handleError('Invalid request. Missing required fields.');
}
?>


