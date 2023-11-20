<?php
file_put_contents('post_log.txt', print_r($_POST, true));
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('./users/db.php');

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
