<?php
include('db.php');

header('Content-Type: application/json');

$uuid = $_POST['uuid'] ?? '';
if (!$uuid) {
    echo json_encode(['success' => false, 'message' => 'UUID is required.']);
    exit;
}

// Find the school ID based on the UUID
$stmt = $connection->prepare("SELECT school_id FROM Schools WHERE school_uuid = ?");
$stmt->bindParam(1, $uuid);
$stmt->execute();

if ($stmt->rowCount() == 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid UUID.']);
    exit;
}

$school = $stmt->fetch();
$school_id = $school['school_id'];

// Assume $user_id is obtained from the session or another secure source
$user_id = $_SESSION['user_id'];

// Check if user is already part of this school
$check = $connection->prepare("SELECT * FROM Teachers WHERE account_id = ? AND school_id = ?");
$check->execute([$user_id, $school_id]);

if ($check->rowCount() > 0) {
    echo json_encode(['success' => false, 'message' => 'You are already a member of this school.']);
    exit;
}

// Insert the user as a teacher in the new school
$insert = $connection->prepare("INSERT INTO Teachers (account_id, school_id, is_admin, fname, lname, approved) SELECT account_id, ?, 0, fname, lname, 0 FROM Teachers WHERE account_id = ?");
$insert->bindParam(1, $school_id);
$insert->bindParam(2, $user_id);
$insert->execute();

echo json_encode(['success' => true]);
?>
