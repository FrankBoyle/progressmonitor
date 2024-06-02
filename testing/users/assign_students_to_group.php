<?php
include('auth_session.php');

include 'db.php';

$group_id = $_POST['group_id'];
$student_ids = explode(',', $_POST['student_ids']);

foreach ($student_ids as $student_id) {
    $sql = "INSERT INTO group_students (group_id, student_id) VALUES (?, ?)";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("ii", $group_id, $student_id);
    $stmt->execute();
}

echo "Students assigned successfully.";

$stmt->close();
$connection->close();
?>


