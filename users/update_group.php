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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $groupId = $_POST['group_id'];
    $groupName = $_POST['group_name'];

    try {
        $stmt = $connection->prepare("UPDATE Groups SET group_name = ? WHERE group_id = ?");
        $stmt->execute([$groupName, $groupId]);
        echo "Group updated successfully.";
    } catch (PDOException $e) {
        error_log($e->getMessage());
        echo "Error updating group: " . $e->getMessage();
    }
}
?>
