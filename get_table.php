<?php
// Database connection
$servername = "localhost"; // Replace with your MySQL server name
$username = "root"; // Replace with your MySQL username
$password = ""; // Replace with your MySQL password
$dbname = "StudentData"; // Replace with your MySQL database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

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
