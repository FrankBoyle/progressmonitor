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
    $stmt = $connection->prepare("SELECT s.* FROM Students_new s INNER JOIN Teachers t ON s.school_id = t.school_id WHERE t.teacher_id = ? AND s.archived = ?");
    $stmt->execute([$teacherId, $archivedValue]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC); // Fetch as associative array for JSON encoding
}

$allStudents = fetchStudentsByTeacher($teacherId, false);

echo json_encode($allStudents); // Output students as JSON
?>