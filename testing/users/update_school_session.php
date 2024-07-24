<?php
session_start();
include('auth_Session.php');
include('db.php');  

$response = ['success' => false];

if (isset($_POST['school_id'])) {
    $_SESSION['school_id'] = $_POST['school_id'];

    // Check if the user is approved for this school
    $stmt = $connection->prepare("SELECT approved FROM Teachers WHERE account_id = ? AND school_id = ?");
    $stmt->execute([$_SESSION['account_id'], $_POST['school_id']]);
    $approved = $stmt->fetchColumn();

    if ($approved) {
        $response = ['success' => true, 'approved' => true];
    } else {
        $response = ['success' => true, 'approved' => false];
    }
} else {
    $response = ['success' => false, 'message' => 'No school ID provided.'];
}

echo json_encode($response);
?>
