<?php
session_start();
include('auth_session.php');
include('db.php');

// Function to remove a student from a group
function removeStudentFromGroup($studentId, $groupId) {
    global $connection; // Assuming you have a database connection

    // Check if the student is in the group
    $stmt = $connection->prepare("SELECT * FROM StudentGroup WHERE student_id = ? AND group_id = ?");
    $stmt->execute([$studentId, $groupId]);

    if ($stmt->rowCount() > 0) {
        // Student is in the group, proceed with removal
        $deleteStmt = $connection->prepare("DELETE FROM StudentGroup WHERE student_id = ? AND group_id = ?");
        $deleteStmt->execute([$studentId, $groupId]);

        return "Student removed from the group successfully.";
    } else {
        // Student is not in the group, provide an error message
        return "Student is not in the selected group.";
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['student_id']) && isset($_POST['group_id'])) {
    $studentId = $_POST['student_id'];
    $groupId = $_POST['group_id'];
    
    // Call the function to remove the student from the group
    removeStudentFromGroup($studentId, $groupId);
    
    // Provide a success or error message
    $message = "Student removed from the group successfully."; // You can customize this message
}

// Include HTML and form for selecting a student and group
?>
