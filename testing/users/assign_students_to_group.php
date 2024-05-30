<?php
session_start();
include('users/auth_session.php');
include('users/db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['group_id']) && isset($_POST['student_ids'])) {
        $groupId = $_POST['group_id'];
        $studentIds = explode(',', $_POST['student_ids']);
        
        try {
            foreach ($studentIds as $studentId) {
                // Check if the student is already in the group
                $checkStmt = $connection->prepare("SELECT * FROM StudentGroup WHERE student_id = ? AND group_id = ?");
                $checkStmt->execute([$studentId, $groupId]);

                if ($checkStmt->rowCount() == 0) {
                    // If not, insert the student into the group
                    $insertStmt = $connection->prepare("INSERT INTO StudentGroup (student_id, group_id) VALUES (?, ?)");
                    $insertStmt->execute([$studentId, $groupId]);
                }
            }
            echo "Selected students assigned to group successfully.";
        } catch (PDOException $e) {
            error_log($e->getMessage());
            echo "Error assigning students to group: " . $e->getMessage();
        }
    } else {
        echo "Group ID or Student IDs not provided.";
    }
}
?>

