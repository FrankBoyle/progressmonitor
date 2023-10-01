<?php
// Connect to the database
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('./users/db.php');

$response = ['success' => false];

if (isset($_POST['performance_id'])) {
    $performanceId = $_POST['performance_id'];

    // Ensure you use prepared statements to prevent SQL injection
    $stmt = $connection->prepare("DELETE FROM Performance WHERE performance_id = ?");
    $stmt->bind_param("i", $performanceId);

    if ($stmt->execute()) {
        $response['success'] = true;
    }

    $stmt->close();
}

echo json_encode($response);
?>
