<?php
session_start();

include('auth_session.php');
include('db.php');

header('Content-Type: application/json');

if (isset($_POST['school_id'])) {
    $newSchoolId = intval($_POST['school_id']);
    $_SESSION['school_id'] = $newSchoolId;
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'No school ID provided']);
}
?>