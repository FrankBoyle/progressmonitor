<?php
// Include the database connection script
include('./users/db.php');
header('Content-Type: application/json');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['goal_description'])) {
    $goalDescription = $_POST['goal_description'];

    // Prepare and bind
    $stmt = $connection->prepare("INSERT INTO Goals (goal_description) VALUES (?)");
    $stmt->bind_param("s", $goalDescription);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database insertion failed.']);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}
?>
