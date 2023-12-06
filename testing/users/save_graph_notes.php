<?php
session_start();
include('auth_session.php');
include('db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $goalId = $_POST['goal_id'];
    $notes = $_POST['notes'];

    // SQL to insert or update notes
    $sql = "INSERT INTO goal_notes (goal_id, notes) VALUES (?, ?)
            ON DUPLICATE KEY UPDATE notes = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $goalId, $notes, $notes);

    if ($stmt->execute()) {
        echo "Notes saved successfully";
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}
?>