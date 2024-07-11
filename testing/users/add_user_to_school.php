<?php
session_start();
include('auth_session.php');

include('db.php');

if (!isset($_SESSION['school_id']) || !isset($_POST['teacher_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$school_id = $_SESSION['school_id'];
$teacher_id = $_POST['teacher_id'];

try {
    $query = $connection->prepare("
        UPDATE Teachers 
        SET school_id = :school_id 
        WHERE teacher_id = :teacher_id
    ");
    $query->bindParam("school_id", $school_id, PDO::PARAM_INT);
    $query->bindParam("teacher_id", $teacher_id, PDO::PARAM_INT);
    $query->execute();
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
