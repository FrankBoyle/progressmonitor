<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('auth_session.php');
include('db.php');

$schoolId = $_SESSION['school_id'];
$teacherId = $_SESSION['teacher_id'];

function fetchStudentsByTeacher($teacherId, $archived = false) {
    global $connection;
    $archivedValue = $archived ? 1 : 0;
    $stmt = $connection->prepare("
        SELECT s.student_id_new, s.first_name, s.last_name, sg.group_id 
        FROM Students_new s 
        LEFT JOIN StudentGroup sg ON s.student_id_new = sg.student_id_new 
        INNER JOIN Teachers t ON s.school_id = t.school_id 
        WHERE t.teacher_id = ? AND s.archived = ?
    ");
    $stmt->execute([$teacherId, $archivedValue]);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $students = [];
    foreach ($result as $row) {
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
    return array_values($students);
}

$allStudents = fetchStudentsByTeacher($teacherId, false);

echo json_encode($allStudents);
?>
