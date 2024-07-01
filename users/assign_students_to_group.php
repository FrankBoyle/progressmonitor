<?php
include('auth_session.php');
include('db.php');

header('Content-Type: application/json');

try {
    if (isset($_POST['group_id']) && isset($_POST['student_ids'])) {
        $groupId = $_POST['group_id'];
        $studentIds = explode(',', $_POST['student_ids']); // Assuming student_ids is a comma-separated string

        // Prepare statement to insert each student into the group
        $stmt = $connection->prepare("INSERT INTO StudentGroup (group_id, student_id_new) VALUES (?, ?)");

        foreach ($studentIds as $studentId) {
            // Verify the student_id_new exists in Students_new
            $checkStmt = $connection->prepare("SELECT COUNT(*) FROM Students_new WHERE student_id_new = ?");
            $checkStmt->execute([$studentId]);
            if ($checkStmt->fetchColumn() == 0) {
                throw new Exception("Student ID $studentId does not exist in Students_new.");
            }

            // Insert the student into the group
            $stmt->execute([$groupId, $studentId]);
        }

        echo json_encode(["status" => "success", "message" => "Students assigned to group successfully."]);
    } else {
        echo json_encode(["error" => "Invalid request, group_id or student_ids not set"]);
    }
} catch (Exception $e) {
    error_log("Error assigning students to group: " . $e->getMessage());
    echo json_encode(["error" => "Error assigning students to group: " . $e->getMessage()]);
}
?>

