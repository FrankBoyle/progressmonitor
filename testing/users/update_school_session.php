<?php
session_start();

include('auth_session.php');
include('db.php');

header('Content-Type: application/json');

if (isset($_POST['school_id'])) {
    $schoolId = $_POST['school_id'];
    $_SESSION['school_id'] = $schoolId;
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'School ID not provided']);
}
?>