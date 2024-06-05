<?php
session_start();
include('auth_session.php');
include('db.php');

// Enable PHP error logging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', 'error_log.log');  // Ensure this file is writable by the server

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $goalDescription = $_POST['goal_description'] ?? null;
    $goalDate = $_POST['goal_date'] ?? null;
    $metadataId = $_POST['metadata_id'] ?? null;
    $studentId = $_POST['student_id'] ?? null;
    $schoolId = $_POST['school_id'] ?? null;

    if (!$goalDescription || !$goalDate || !$metadataId || !$studentId || !$schoolId) {
        error_log("Invalid input data: " . print_r($_POST, true));
        echo "Invalid input data.";
        exit;
    }

    try {
        $stmt = $connection->prepare("INSERT INTO Goals (goal_description, goal_date, metadata_id, student_id, school_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$goalDescription, $goalDate, $metadataId, $studentId, $schoolId]);

        if ($stmt->rowCount() > 0) {
            echo "Goal added successfully.";
        } else {
            error_log("Failed to add goal.");
            echo "Failed to add goal.";
        }
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        echo "Database error: " . $e->getMessage();
    }
} else {
    echo "Invalid request method.";
}
