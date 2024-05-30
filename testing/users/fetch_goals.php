<?php
session_start();
include('auth_session.php');
include('db.php');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$studentId = $_GET['student_id'] ?? '';

if ($studentId) {
    function fetchGoalsByStudent($studentId) {
        global $connection;
        $stmt = $connection->prepare("
            SELECT g.goal_id, g.goal_description, g.metadata_id, m.category_name 
            FROM Goals g
            INNER JOIN Metadata m ON g.metadata_id = m.metadata_id
            WHERE g.student_id = ?
        ");
        $stmt->execute([$studentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    $goals = fetchGoalsByStudent($studentId);
    echo json_encode($goals);
} else {
    echo json_encode([]);
}
?>

