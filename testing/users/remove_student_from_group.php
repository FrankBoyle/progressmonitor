<?php
session_start();
include('auth_session.php');
include('db.php');

// Function to remove a student from a group
function removeStudentFromGroup($studentId, $groupId) {
    global $connection; // Assuming you have a database connection

    try {
        // Check if the student is in the group
        $stmt = $connection->prepare("SELECT * FROM StudentGroup WHERE student_id = ? AND group_id = ?");
        $stmt->execute([$studentId, $groupId]);

        if ($stmt->rowCount() > 0) {
            // Student is in the group, proceed with removal
            $deleteStmt = $connection->prepare("DELETE FROM StudentGroup WHERE student_id = ? AND group_id = ?");
            $deleteStmt->execute([$studentId, $groupId]);

            echo json_encode(['status' => 'success', 'message' => 'Student removed from the group successfully.']);
        } else {
            // Student is not in the group, provide an error message
            echo json_encode(['status' => 'error', 'message' => 'Student is not in the selected group.']);
        }
    } catch (PDOException $e) {
        // Handle any database errors
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

// Handle AJAX request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['student_id'], $_POST['group_id'])) {
    $studentId = $_POST['student_id'];
    $groupId = $_POST['group_id'];
    
    // Call the function to remove the student from the group
    removeStudentFromGroup($studentId, $groupId);
}
?>
