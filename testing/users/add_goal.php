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

if (!isset($connection)) {
    error_log("Database connection is not set.");
    die("Database connection is not set.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $studentId = $_POST['student_id'] ?? '';
    $goalDescription = $_POST['goal_description'] ?? '';
    $goalDate = $_POST['goal_date'] ?? '';
    $metadataId = $_POST['metadata_id'] ?? '';

    // Ensure all required fields are provided
    if (empty($studentId) || empty($goalDescription) || empty($goalDate) || empty($metadataId)) {
        echo 'All fields are required.';
        exit;
    }

    try {
        $stmt = $connection->prepare("INSERT INTO Goals (student_id, goal_description, goal_date, metadata_id) VALUES (?, ?, ?, ?)");
        $stmt->execute([$studentId, $goalDescription, $goalDate, $metadataId]);

        echo "Goal added successfully.";
    } catch (PDOException $e) {
        echo "Error adding goal: " . $e->getMessage();
    }
}
?>
