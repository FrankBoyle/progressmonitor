<?php
session_start();
include('users/auth_session.php');
include('users/db.php');

header('Content-Type: application/json');

// Fetch all relevant groups for a teacher
$teacherId = $_SESSION['teacher_id'];
$groups = fetchAllRelevantGroups($teacherId);

echo json_encode($groups);

function fetchAllRelevantGroups($teacherId) {
    global $connection;
    $stmt = $connection->prepare("
        SELECT g.*, (g.group_id = t.default_group_id) AS is_default 
        FROM Groups g
        LEFT JOIN Teachers t ON t.teacher_id = :teacherId
        WHERE g.teacher_id = :teacherId
        UNION
        SELECT g.*, (g.group_id = t.default_group_id) AS is_default
        FROM Groups g
        INNER JOIN SharedGroups sg ON g.group_id = sg.group_id
        LEFT JOIN Teachers t ON t.teacher_id = :teacherId
        WHERE sg.shared_teacher_id = :teacherId
    ");
    $stmt->bindParam(':teacherId', $teacherId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
