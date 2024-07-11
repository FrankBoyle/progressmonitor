<?php
session_start();
include('auth_session.php');
include('db.php');

if (!isset($_SESSION['program_id']) || !isset($_SESSION['school_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$programId = $_SESSION['program_id'];
$schoolId = $_SESSION['school_id'];

try {
    $stmt = $connection->prepare("
        SELECT t.teacher_id, t.fname, t.lname, a.email 
        FROM Teachers t 
        JOIN accounts a ON t.account_id = a.id 
        WHERE t.program_id = :programId AND t.school_id != :schoolId
    ");
    $stmt->bindParam('programId', $programId, PDO::PARAM_INT);
    $stmt->bindParam('schoolId', $schoolId, PDO::PARAM_INT);
    $stmt->execute();

    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($result);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>