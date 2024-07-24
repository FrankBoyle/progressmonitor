<?php
session_start();
include('auth_session.php');
include('db.php');

header('Content-Type: application/json');

$inputData = file_get_contents("php://input");
$data = json_decode($inputData);

if (empty($data->teacher_id) || !isset($data->approved)) {
    echo json_encode(['success' => false, 'message' => 'Invalid data', 'received' => $inputData]);
    exit;
}

$teacherId = $data->teacher_id;
$approved = $data->approved;
$schoolId = $_SESSION['school_id'];

try {
    $query = $connection->prepare("
        UPDATE Teachers 
        SET approved = :approved 
        WHERE teacher_id = :teacher_id AND school_id = :school_id
    ");
    $query->bindParam(':approved', $approved, PDO::PARAM_INT);
    $query->bindParam(':teacher_id', $teacherId, PDO::PARAM_INT);
    $query->bindParam(':school_id', $schoolId, PDO::PARAM_INT);
    $query->execute();

    if ($query->rowCount() > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No matching record found or no changes made']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
