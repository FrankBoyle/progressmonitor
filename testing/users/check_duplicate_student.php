<?php
session_start();
include('auth_session.php');
include('db.php');

header('Content-Type: application/json');

$firstName = $_GET['first_name'] ?? '';
$lastName = $_GET['last_name'] ?? '';
$schoolId = $_GET['school_id'] ?? '';

if (!$firstName || !$lastName) {
    echo json_encode(['duplicate' => false]);
    exit;
}

try {
    $stmt = $connection->prepare("SELECT COUNT(*) FROM Students_new WHERE first_name = ? AND last_name = ? AND school_id = ?");
    $stmt->execute([$firstName, $lastName, $schoolId]);
    $count = $stmt->fetchColumn();

    if ($count > 0) {
        echo json_encode(['duplicate' => true]);
    } else {
        echo json_encode(['duplicate' => false]);
    }
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode(['error' => 'Database error occurred while checking for duplicates.']);
}
?>
