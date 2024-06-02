<?php
include('auth_session.php');
include 'db.php';

$group_id = $_POST['group_id'];

$sql = "SELECT students.student_id AS student_id_new, first_name, last_name 
        FROM students 
        JOIN group_students ON students.student_id = group_students.student_id 
        WHERE group_students.group_id = ?";
$stmt = $connection->prepare($sql);
$stmt->bind_param("i", $group_id);
$stmt->execute();
$result = $stmt->get_result();

$students = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
}

echo json_encode($students);

$stmt->close();
$connection->close();
?>

