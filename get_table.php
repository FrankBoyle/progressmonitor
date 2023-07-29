<?php
require('db.php');
include("auth_session.php");
?>

<?php

// Retrieve the JSON data from the database
$sql = "SELECT json_data FROM student1_data ORDER BY id DESC LIMIT 1";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Output data as JSON
    $data = $result->fetch_assoc()["json_data"];
    header('Content-Type: application/json');
    echo $data;
} else {
    echo "No data found.";
}

$conn->close();
?>
