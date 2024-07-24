<?php
session_start();
include('auth_session.php');
include('db.php');

// Enable error reporting for debugging (remove or disable in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

// Read JSON input
$input = json_decode(file_get_contents('php://input'), true);

$uuid = $input['uuid'] ?? '';
if (!$uuid) {
    echo json_encode(['success' => false, 'message' => 'UUID is required.']);
    exit;
}

// Ensure the user is logged in and has a valid session
if (!isset($_SESSION['account_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in or session expired.']);
    exit;
}

$account_id = $_SESSION['account_id'];  // Using the session variable set at login

// Find the school ID based on the UUID
$stmt = $connection->prepare("SELECT school_id FROM Schools WHERE school_uuid = :uuid");
$stmt->bindParam(':uuid', $uuid);
$stmt->execute();

if ($stmt->rowCount() == 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid UUID.']);
    exit;
}

$school = $stmt->fetch(PDO::FETCH_ASSOC);
$school_id = $school['school_id'];

// Check if user is already part of this school
$check = $connection->prepare("SELECT * FROM Teachers WHERE account_id = :account_id AND school_id = :school_id");
$check->bindParam(':account_id', $account_id);
$check->bindParam(':school_id', $school_id);
$check->execute();

if ($check->rowCount() > 0) {
    echo json_encode(['success' => false, 'message' => 'You are already a member of this school.']);
    exit;
}

// Insert the user as a teacher in the new school
$insert = $connection->prepare("INSERT INTO Teachers (account_id, school_id, is_admin, fname, lname, approved) SELECT account_id, :school_id, 0, fname, lname, 0 FROM Teachers WHERE account_id = :account_id");
$insert->bindParam(':school_id', $school_id);
$insert->bindParam(':account_id', $account_id);
$insert->execute();

echo json_encode(['success' => true]);
?>

