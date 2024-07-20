<?php
session_start();
include('auth_session.php');
include('db.php');

// Enable PHP error logging
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
//ini_set('log_errors', 1);
//ini_set('error_log', 'error_log.log');  // Ensure this file is writable by the server

header('Content-Type: application/json');

if (isset($_POST['school_id'])) {
    $school_id = $_POST['school_id'];
    $account_id = $_SESSION['account_id'];

    // Update school_id in session
    $_SESSION['school_id'] = $school_id;

    // Fetch the corresponding teacher_id for the new school_id
    $query = $connection->prepare("SELECT teacher_id FROM Teachers WHERE account_id = :account_id AND school_id = :school_id");
    $query->bindParam("account_id", $account_id, PDO::PARAM_INT);
    $query->bindParam("school_id", $school_id, PDO::PARAM_INT);
    $query->execute();
    $result = $query->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        $_SESSION['teacher_id'] = $result['teacher_id'];
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Teacher not found for the selected school.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'School ID not provided.']);
}
?>