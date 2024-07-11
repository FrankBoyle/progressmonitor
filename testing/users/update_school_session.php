<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['school_id'])) {
    $school_id = $_POST['school_id'];

    // Update the session variable
    $_SESSION['school_id'] = $school_id;

    // Return a JSON response
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>
