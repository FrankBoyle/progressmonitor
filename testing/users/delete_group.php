<?php
session_start();
include('db.php'); // Make sure this path is correct

if (isset($_POST['group_id'])) {
    $groupId = $_POST['group_id'];

    // Check if the group has any students
    $checkStmt = $connection->prepare("SELECT COUNT(*) AS student_count FROM StudentGroup WHERE group_id = ?");
    $checkStmt->execute([$groupId]);
    $result = $checkStmt->fetch();

    if ($result['student_count'] > 0) {
        // Group has students, ask for confirmation
        $confirmation = confirm("This group has students. Are you sure you want to delete this group and remove all students from it?");
        if (!$confirmation) {
            echo "Deletion canceled by user.";
            exit;
        }
        
        // Remove all students from the group
        $removeStudentsStmt = $connection->prepare("DELETE FROM StudentGroup WHERE group_id = ?");
        $removeStudentsStmt->execute([$groupId]);
    }

    // Delete the group from the database
    $deleteStmt = $connection->prepare("DELETE FROM Groups WHERE group_id = ?");
    $deleteStmt->execute([$groupId]);

    // Send a success response back to the JavaScript
    echo "Group deleted successfully";
} else {
    echo "Invalid request";
}
?>

