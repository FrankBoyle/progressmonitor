<?php
include('db.php');

header('Content-Type: application/json');

$groupId = $_GET['group_id'];

try {
    $stmt = $connection->prepare("
        SELECT student_id_new, first_name, last_name 
        FROM Students_new 
        WHERE student_id_new NOT IN (
            SELECT student_id_new FROM StudentGroup WHERE group_id = ?
        )
    ");
    $stmt->execute([$groupId]);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($students);
} catch (Exception $e) {
    error_log("Error fetching students not in group: " . $e->getMessage());
    echo json_encode(["error" => "Error fetching students not in group."]);
}
?>
