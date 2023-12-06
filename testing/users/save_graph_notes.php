<?php
session_start();
include('db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $goalId = $_POST['goal_id'];
    $studentId = $_POST['student_id'];
    $schoolId = $_POST['school_id'];
    $metadataId = $_POST['metadata_id'];
    $notes = $_POST['notes'];

    // SQL to insert or update notes
    $sql = "INSERT INTO goal_notes (goal_id, student_id, school_id, metadata_id, notes) 
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE student_id = ?, school_id = ?, metadata_id = ?, notes = ?";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("iiiiiiis", $goalId, $studentId, $schoolId, $metadataId, $notes, $studentId, $schoolId, $metadataId, $notes);

    if ($stmt->execute()) {
        echo "Notes saved successfully";
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}
?>