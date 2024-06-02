<?php
include 'db.php';

$group_id = $_POST['group_id'];

$sql = "DELETE FROM groups WHERE group_id = ?";
$stmt = $connection->prepare($sql);
$stmt->bind_param("i", $group_id);

if ($stmt->execute()) {
    echo "Group deleted successfully.";
} else {
    echo "Error deleting group: " . $stmt->error;
}

$stmt->close();
$connection->close();
?>


