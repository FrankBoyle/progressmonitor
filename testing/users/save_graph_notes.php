<?php
session_start();
include('auth_session.php');
include('db.php');

// Function to add notes
function addNotes($goalId, $studentId, $schoolId, $metadataId, $notes) {
    global $connection; // Assuming you have a database connection

    try {
        // Prepare the SQL statement to insert notes
        $stmt = $connection->prepare("INSERT INTO Goal_notes (goal_id, student_id, school_id, metadata_id, notes) VALUES (?, ?, ?, ?, ?)
                                      ON DUPLICATE KEY UPDATE notes = ?, student_id = ?, school_id = ?, metadata_id = ?");
        $stmt->execute([$goalId, $studentId, $schoolId, $metadataId, $notes, $notes, $studentId, $schoolId, $metadataId]);

        echo json_encode(['status' => 'success', 'message' => 'Notes added successfully.']);
    } catch (PDOException $e) {
        // Handle any database errors
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

// Handling the POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['goal_id'], $_POST['student_id'], $_POST['school_id'], $_POST['metadata_id'], $_POST['notes'])) {
    $goalId = $_POST['goal_id'];
    $studentId = $_POST['student_id'];
    $schoolId = $_POST['school_id'];
    $metadataId = $_POST['metadata_id'];
    $notes = $_POST['notes'];

    // Call the function to add notes
    addNotes($goalId, $studentId, $schoolId, $metadataId, $notes);
}
?>
