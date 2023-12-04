<?php
session_start();
include('db.php'); // Make sure this path is correct

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

if (isset($_POST['student_id'], $_POST['group_id'])) {
    $studentId = $_POST['student_id'];
    $groupId = $_POST['group_id'];

    // Check if the student is already assigned to this group
    $stmt = $connection->prepare("SELECT * FROM StudentGroup WHERE student_id = ? AND group_id = ?");
    $stmt->execute([$studentId, $groupId]);

    if ($stmt->rowCount() > 0) {
        // Student is already in this group
        $response['message'] = 'The student is already in this group.';
    } else {
        // Student is not in this group, proceed with insertion
        $insertStmt = $connection->prepare("INSERT INTO StudentGroup (student_id, group_id) VALUES (?, ?)");
        if ($insertStmt->execute([$studentId, $groupId])) {
            $response['success'] = true;
            $response['message'] = 'Student assigned to group successfully.';
        } else {
            $response['message'] = 'Error assigning student to group.';
        }
    }
} else {
    $response['message'] = 'Invalid request.';
}

echo json_encode($response);
?>
