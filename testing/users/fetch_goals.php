<?php
include('auth_session.php');
include('db.php');

// Error Reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Fetch goals by student ID and optionally grouped by metadata_id
if (isset($_GET['student_id'])) {
    $studentId = $_GET['student_id'];
    $goals = fetchGoals($studentId);
    echo json_encode($goals);
}

function fetchGoals($studentId) {
    global $connection;
    $stmt = $connection->prepare("
        SELECT g.goal_description, g.metadata_id
        FROM Goals g
        WHERE g.student_id = ?
    ");
    $stmt->execute([$studentId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
