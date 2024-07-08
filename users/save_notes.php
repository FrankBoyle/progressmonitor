<?php
session_start();

include('auth_session.php');
include('db.php');

// Function to add notes
function addNotes($goalId, $studentIdNew, $schoolId, $metadataId, $reportingPeriod, $notes) {
    global $connection; // Assuming you have a database connection

    try {
        // Insert new notes
        $stmt = $connection->prepare("INSERT INTO Goal_notes (goal_id, student_id_new, school_id, metadata_id, reporting_period, notes) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$goalId, $studentIdNew, $schoolId, $metadataId, $reportingPeriod, $notes]);

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
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (isset($data['goal_id'], $data['student_id_new'], $data['school_id'], $data['metadata_id'], $data['reporting_period'], $data['notes']) && !empty($data['reporting_period'])) {
        $goalId = $data['goal_id'];
        $studentIdNew = $data['student_id_new'];
        $schoolId = $data['school_id'];
        $metadataId = $data['metadata_id'];
        $reportingPeriod = $data['reporting_period'];
        $notes = $data['notes'];

        // Call the function to add notes
        addNotes($goalId, $studentIdNew, $schoolId, $metadataId, $reportingPeriod, $notes);
    } else {
        header('Content-Type: application/json');
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid request or missing parameters.']);
    }
} else {
    header('Content-Type: application/json');
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}

// Ensure no additional output is sent
exit;
?>


