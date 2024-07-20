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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['goal_id'])) {
        $goalId = $_POST['goal_id'];

        try {
            $stmt = $connection->prepare("UPDATE Goals SET archived = 1 WHERE goal_id = ?");
            $stmt->execute([$goalId]);

            echo json_encode(["status" => "success", "message" => "Goal archived successfully."]);
        } catch (Exception $e) {
            error_log("Error archiving goal: " . $e->getMessage());
            echo json_encode(["status" => "error", "message" => "Error archiving goal: " . $e->getMessage()]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid request, goal_id not set"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
}
?>
