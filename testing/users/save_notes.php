<?php
session_start();
include('auth_session.php');
include('db.php');

// Function to add notes
function addNotes($goalId, $studentIdNew, $schoolId, $metadataId, $reportingPeriod, $notes) {
    global $connection; // Assuming you have a database connection

    try {
        // Check if the notes already exist for the given goal
        $stmt = $connection->prepare("SELECT COUNT(*) FROM Goal_notes WHERE goal_id = ?");
        $stmt->execute([$goalId]);
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            // Update the existing notes
            $stmt = $connection->prepare("UPDATE Goal_notes SET reporting_period = ?, notes = ? WHERE goal_id = ?");
            $stmt->execute([$reportingPeriod, $notes, $goalId]);
        } else {
            // Insert new notes
            $stmt = $connection->prepare("INSERT INTO Goal_notes (goal_id, student_id_new, school_id, metadata_id, reporting_period, notes) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$goalId, $studentIdNew, $schoolId, $metadataId, $reportingPeriod, $notes]);
        }

        // Ensure no additional output is sent
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'message' => 'Notes added successfully.']);
    } catch (PDOException $e) {
        // Handle any database errors
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

// Ensure the request method is POST and required parameters are set
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['goal_id'], $_POST['student_id_new'], $_POST['school_id'], $_POST['metadata_id'], $_POST['reporting_period'], $_POST['notes'])) {
    $goalId = $_POST['goal_id'];
    $studentIdNew = $_POST['student_id_new'];
    $schoolId = $_POST['school_id'];
    $metadataId = $_POST['metadata_id'];
    $reportingPeriod = $_POST['reporting_period'];
    $notes = $_POST['notes'];

    // Call the function to add notes
    addNotes($goalId, $studentIdNew, $schoolId, $metadataId, $reportingPeriod, $notes);
} else {
    header('Content-Type: application/json');
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid request or missing parameters.']);
}

// Ensure no additional output is sent
exit;
?>


