<?php
// Include the database connection script
include('./users/db.php');
header('Content-Type: application/json');

// Disable error display, log errors instead
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if the required POST data is present
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['goal_description']) && isset($_POST['student_id']) && isset($_POST['metadata_id']) && isset($_POST['school_id'])) {
    $goalDescription = $_POST['goal_description'];
    $studentId = $_POST['student_id'];
    $metadataId = $_POST['metadata_id'];
    $schoolId = $_POST['school_id'];
    $goalDate = isset($_POST['goal_date']) ? $_POST['goal_date'] : date('Y-m-d'); // Use current date if not provided

    // Prepare and bind
    $stmt = $connection->prepare("INSERT INTO Goals (student_id, goal_description, goal_date, school_id, metadata_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issii", $studentId, $goalDescription, $goalDate, $schoolId, $metadataId);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'goal_id' => $connection->insert_id]); // Return the new goal ID
    } else {
        echo json_encode(['success' => false, 'message' => 'Database insertion failed: ' . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request. Missing required fields.']);
}
?>

