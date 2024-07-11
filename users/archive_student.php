<?php
session_start();
include('auth_session.php');
include('db.php');

$input = json_decode(file_get_contents('php://input'), true);
$student_id_new = $input['student_id_new'] ?? null;

if ($student_id_new === null) {
    echo json_encode(['success' => false, 'message' => 'Invalid student ID']);
    exit();
}

try {
    $stmt = $connection->prepare("UPDATE Students_new SET archived = 1 WHERE student_id_new = :student_id_new");
    $stmt->bindParam(':student_id_new', $student_id_new, PDO::PARAM_INT);
    $stmt->execute();

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
