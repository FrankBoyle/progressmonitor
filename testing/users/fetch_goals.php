<?php
include('auth_session.php');
include('db.php');

// Error Reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update goal
    $goalId = $_POST['goal_id'];
    $goalDescription = $_POST['goal_description'];

    $stmt = $connection->prepare("UPDATE Goals SET goal_description = ? WHERE goal_id = ?");
    $stmt->execute([$goalDescription, $goalId]);

    echo json_encode(['status' => 'success']);
    exit;
}

if (isset($_GET['student_id'])) {
    $studentId = $_GET['student_id'];
    $goals = fetchGoals($studentId);
    echo json_encode($goals);
}

function fetchGoals($studentId) {
    global $connection;
    $stmt = $connection->prepare("
        SELECT g.goal_id, g.goal_description, g.metadata_id, m.category_name
        FROM Goals g
        JOIN Metadata m ON g.metadata_id = m.metadata_id
        WHERE g.student_id = ?
    ");
    $stmt->execute([$studentId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

