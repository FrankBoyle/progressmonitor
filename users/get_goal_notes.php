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

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['goal_id'])) {
    $goalId = $_GET['goal_id'];

    try {
        $stmt = $connection->prepare("SELECT notes FROM Goal_notes WHERE goal_id = ?");
        $stmt->execute([$goalId]);

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode(['status' => 'success', 'notes' => $row['notes']]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No notes found for this goal yet.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
?>
