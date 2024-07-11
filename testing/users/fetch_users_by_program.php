<?php
session_start();
include('auth_session.php');
include('db.php');

if (!isset($_SESSION['school_id']) || !isset($_SESSION['program_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$school_id = $_SESSION['school_id'];
$program_id = $_SESSION['program_id'];

try {
    $query = $connection->prepare("
        SELECT teacher_id, fname, lname, email 
        FROM Teachers 
        WHERE program_id = :program_id AND school_id != :school_id AND approved = 1
    ");
    $query->bindParam("program_id", $program_id, PDO::PARAM_INT);
    $query->bindParam("school_id", $school_id, PDO::PARAM_INT);
    $query->execute();
    $results = $query->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($results);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
