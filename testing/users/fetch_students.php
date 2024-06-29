<?php
include('db.php');

$query = "SELECT student_id_new, first_name, last_name, group_id FROM Students_new LEFT JOIN StudentGroup ON Students_new.student_id_new = StudentGroup.student_id_new";
$result = $connection->query($query);

$students = [];

while ($row = $result->fetch_assoc()) {
    if (!isset($students[$row['student_id_new']])) {
        $students[$row['student_id_new']] = [
            'student_id_new' => $row['student_id_new'],
            'first_name' => $row['first_name'],
            'last_name' => $row['last_name'],
            'groups' => []
        ];
    }
    if ($row['group_id'] !== null) {
        $students[$row['student_id_new']]['groups'][] = $row['group_id'];
    }
}

// Log the final students array
error_log(print_r($students, true));

echo json_encode(array_values($students));
?>
