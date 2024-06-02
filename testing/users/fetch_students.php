<?php
include('auth_session.php');

include 'db.php';

$sql = "SELECT student_id AS student_id_new, first_name, last_name FROM students";
$result = $connection->query($sql);

$students = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
}

echo json_encode($students);

$connection->close();
?>

