<?php
// Include the database connection script
include('./users/db.php');

// Set the Content-Type to application/json
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
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['goal_description'])) {
    // Extract and sanitize input
    $goalDescription = htmlspecialchars($_POST['goal_description']);
    $studentId = filter_var($_POST['student_id'], FILTER_SANITIZE_NUMBER_INT);
    $metadataId = filter_var($_POST['metadata_id'], FILTER_SANITIZE_NUMBER_INT);
    $schoolId = filter_var($_POST['school_id'], FILTER_SANITIZE_NUMBER_INT);
    $goalDate = !empty($_POST['goal_date']) ? $_POST['goal_date'] : null;

    // Prepare the SQL statement
    $stmt = $connection->prepare("INSERT INTO Goals (student_id, goal_description, school_id, metadata_id, goal_date) VALUES (?, ?, ?, ?, ?)");

    // Bind the parameters
    $stmt->bindParam(1, $studentId, PDO::PARAM_INT);
    $stmt->bindParam(2, $goalDescription, PDO::PARAM_STR);
    $stmt->bindParam(3, $schoolId, PDO::PARAM_INT);
    $stmt->bindParam(4, $metadataId, PDO::PARAM_INT);
    $stmt->bindParam(5, $goalDate); // Assuming goal_date can be NULL or a string

    // Execute the statement and check for success
    if ($stmt->execute()) {
        $newGoalId = $connection->lastInsertId();
        echo json_encode([
            'success' => true, 
            'goal_id' => $newGoalId,
            'goal_description' => $goalDescription
        ]);
    } else {
        handleError('Database insertion failed: ' . $stmt->errorInfo()[2]); // [2] is the actual error message
    }

    // Close the statement
    $stmt = null;
} else {
    handleError('Invalid request. Missing required fields.');
}
?>



