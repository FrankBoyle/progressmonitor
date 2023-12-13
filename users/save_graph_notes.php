<?php
session_start();
include('auth_session.php');
include('db.php');

// Function to add notes
function addNotes($goalId, $studentId, $schoolId, $metadataId, $notes) {
    global $connection; // Assuming you have a database connection

    try {
        // Check if the notes already exist for the given goal
        $stmt = $connection->prepare("SELECT COUNT(*) FROM Goal_notes WHERE goal_id = ?");
        $stmt->execute([$goalId]);
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            // Update the existing notes
            $stmt = $connection->prepare("UPDATE Goal_notes SET notes = ? WHERE goal_id = ?");
            $stmt->execute([$notes, $goalId]);
        } else {
            // Insert new notes
            $stmt = $connection->prepare("INSERT INTO Goal_notes (goal_id, student_id, school_id, metadata_id, notes) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$goalId, $studentId, $schoolId, $metadataId, $notes]);
        }

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
