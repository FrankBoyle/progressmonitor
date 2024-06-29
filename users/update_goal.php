<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('db.php');  

$goalId = $_POST['goal_id'];
$newText = $_POST['new_text'];

// Prepare SQL statement to update the goal
$stmt = $connection->prepare("UPDATE Goals SET goal_description = ? WHERE goal_id = ?");
$stmt->execute([$newText, $goalId]);

if ($stmt->rowCount() > 0) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
?>