<?php
session_start();
include('auth_session.php'); // Ensure the user is authenticated
include('db.php'); // Include the database connection

$data = json_decode(file_get_contents("php://input"), true);

// Validate the incoming data for required fields
if (empty($data['teacher_id']) || empty($data['name']) || !isset($data['is_admin'])) {
    $missing_fields = [];
    if (empty($data['teacher_id'])) $missing_fields[] = 'teacher_id';
    if (empty($data['name'])) $missing_fields[] = 'name';
    if (!isset($data['is_admin'])) $missing_fields[] = 'is_admin';
    echo json_encode(['success' => false, 'message' => 'Missing or invalid data: ' . implode(', ', $missing_fields)]);
    exit;
}

$teacherId = $data['teacher_id'];
$name = $data['name'];
$isAdmin = $data['is_admin'];
$subjectTaught = $data['subject_taught'] ?? null; // Use null coalescing operator to handle optional data

try {
    $query = $connection->prepare("UPDATE Teachers SET name = :name, is_admin = :is_admin, subject_taught = :subject_taught WHERE teacher_id = :teacher_id");
    $query->bindParam(':name', $name, PDO::PARAM_STR);
    $query->bindParam(':is_admin', $isAdmin, PDO::PARAM_INT);
    $query->bindParam(':subject_taught', $subjectTaught, PDO::PARAM_STR);
    $query->bindParam(':teacher_id', $teacherId, PDO::PARAM_INT);
    $query->execute();

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>


