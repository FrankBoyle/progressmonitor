<?php
include('db.php');
include('auth_session.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $groupId = $_POST['group_id'];
    $studentIds = explode(',', $_POST['student_ids']);

    try {
        $connection->beginTransaction();
        
        // Remove existing student assignments for the group
        $stmt = $connection->prepare("DELETE FROM StudentGroup WHERE group_id = ?");
        $stmt->execute([$groupId]);
        
        // Assign new students to the group
        $stmt = $connection->prepare("INSERT INTO StudentGroup (group_id, student_id) VALUES (?, ?)");
        foreach ($studentIds as $studentId) {
            $stmt->execute([$groupId, $studentId]);
        }
        
        $connection->commit();
        echo "Students assigned to group successfully.";
    } catch (PDOException $e) {
        $connection->rollBack();
        error_log($e->getMessage());
        echo "Error assigning students to group: " . $e->getMessage();
    }
}
?>
