<?php
include('auth_session.php');

include ('db.php');

$sql = "SELECT group_id, group_name FROM groups";
$result = $connection->query($sql);

$groups = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $groups[] = $row;
    }
}

echo json_encode($groups);

$connection->close();
?>
