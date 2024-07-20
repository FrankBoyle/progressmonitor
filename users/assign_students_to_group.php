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
    $studentIds = isset($_POST['student_ids']) ? explode(',', $_POST['student_ids']) : [];

    if (!$groupId || empty($studentIds)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid input.']);
        exit;
    }

    try {
        $connection->beginTransaction();

        $stmt = $connection->prepare("INSERT INTO StudentGroup (student_id_new, group_id) VALUES (?, ?)");
        foreach ($studentIds as $studentId) {
            $stmt->execute([$studentId, $groupId]);
        }

        $connection->commit();
        echo json_encode(['status' => 'success', 'message' => 'Students assigned to the group successfully.']);
    } catch (PDOException $e) {
        $connection->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>


