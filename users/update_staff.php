<?php
session_start();
include('auth_session.php'); // Ensure the user is authenticated
include('db.php'); // Include the database connection

$data = json_decode(file_get_contents("php://input"), true);

// Validate the incoming data for required fields
if (empty($data['teacher_id']) || empty($data['fname']) || empty($data['lname']) || !isset($data['is_admin'])) {
    $missing_fields = [];
    if (empty($data['teacher_id'])) $missing_fields[] = 'teacher_id';
    if (empty($data['fname'])) $missing_fields[] = 'fname';
    if (empty($data['lname'])) $missing_fields[] = 'lname';
    if (!isset($data['is_admin'])) $missing_fields[] = 'is_admin';
    echo json_encode(['success' => false, 'message' => 'Missing or invalid data: ' . implode(', ', $missing_fields)]);
    exit;
}

$teacherId = $data['teacher_id'];
$fname = $data['fname'];
$lname = $data['lname'];
$isAdmin = $data['is_admin'];
$subjectTaught = $data['subject_taught'] ?? null; // Allow subject_taught to be null

try {
    $query = $connection->prepare("UPDATE Teachers SET fname = :fname, lname = :lname, is_admin = :is_admin, subject_taught = :subject_taught WHERE teacher_id = :teacher_id");
    $query->bindParam(':fname', $fname, PDO::PARAM_STR);
    $query->bindParam(':lname', $lname, PDO::PARAM_STR);
    $query->bindParam(':is_admin', $isAdmin, PDO::PARAM_INT);
    $query->bindParam(':subject_taught', $subjectTaught, PDO::PARAM_STR);
    $query->bindParam(':teacher_id', $teacherId, PDO::PARAM_INT);
    $query->execute();

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
