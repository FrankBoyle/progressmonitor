<?php
session_start();
include('auth_session.php');
include('db.php');

// Enable PHP error logging
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
//ini_set('log_errors', 1);
//ini_set('error_log', 'error_log.log');  // Ensure this file is writable by the server

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $groupId = isset($_POST['group_id']) ? intval($_POST['group_id']) : null;
    $sharedTeacherId = isset($_POST['shared_teacher_id']) ? intval($_POST['shared_teacher_id']) : null;

    if (!$groupId || !$sharedTeacherId) {
        echo json_encode(['error' => 'Invalid input.']);
        exit;
    }

    try {
        $message = shareGroupWithTeacher($connection, $groupId, $sharedTeacherId);
        echo json_encode(['message' => $message]);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'Invalid request method.']);
}

function shareGroupWithTeacher($connection, $groupId, $sharedTeacherId) {
    // Check if the group is already shared with the teacher
    $checkStmt = $connection->prepare("SELECT * FROM SharedGroups WHERE group_id = ? AND shared_teacher_id = ?");
    $checkStmt->execute([$groupId, $sharedTeacherId]);
    if ($checkStmt->fetch()) {
        return "Group is already shared with this teacher.";
    }

    // Proceed with sharing
    $stmt = $connection->prepare("INSERT INTO SharedGroups (group_id, shared_teacher_id) VALUES (?, ?)");
    $stmt->execute([$groupId, $sharedTeacherId]);
    return "Group shared successfully.";
}
?>
