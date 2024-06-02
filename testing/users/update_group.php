<?php
include 'db.php';

$group_id = $_POST['group_id'];
$group_name = $_POST['group_name'];

$sql = "UPDATE groups SET group_name = ? WHERE group_id = ?";
$stmt = $connection->prepare($sql);
$stmt->bind_param("si", $group_name, $group_id);

if ($stmt->execute()) {
    echo "Group updated successfully.";
} else {
    echo "Error updating group: " . $stmt->error;
}

$stmt->close();
$connection->close();
?>
