<?php
session_start();
require_once('auth_session.php');
require_once('db.php');

// Enable PHP error logging for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', 'error_log.log');

header('Content-Type: application/json');

if (!isset($_POST['school_id'])) {
    echo json_encode(['success' => false, 'message' => 'School ID not provided.']);
    exit;
}

$school_id = $_POST['school_id'];
$account_id = $_SESSION['account_id'];

// Update school_id in session
$_SESSION['school_id'] = $school_id;

// Fetch the corresponding teacher_id and approval status for the new school_id
$query = $connection->prepare("SELECT teacher_id, approved FROM Teachers WHERE account_id = :account_id AND school_id = :school_id");
$query->bindParam(":account_id", $account_id, PDO::PARAM_INT);
$query->bindParam(":school_id", $school_id, PDO::PARAM_INT);

if ($query->execute()) {
    $result = $query->fetch(PDO::FETCH_ASSOC);
    if ($result) {
        $_SESSION['teacher_id'] = $result['teacher_id'];
        $_SESSION['is_approved'] = $result['approved'];
        echo json_encode([
            'success' => true,
            'approved' => $result['approved']
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No teacher record found for the selected school.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to execute query.']);
}
?>
