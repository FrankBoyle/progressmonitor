<?php
session_start();
include('auth_session.php');
include('db.php');

header('Content-Type: application/json');

if (isset($_GET['group_id'])) {
    $groupId = $_GET['group_id'];

    try {
        $stmt = $connection->prepare("SELECT s.student_id, s.first_name, s.last_name, CONCAT(s.first_name, ' ', s.last_name) AS name FROM Students s INNER JOIN StudentGroup sg ON s.student_id = sg.student_id WHERE sg.group_id = ?");
        $stmt->execute([$groupId]);
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($students);
    } catch (Exception $e) {
        echo json_encode(["error" => "Error fetching students: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["error" => "Invalid request"]);
}
?>
