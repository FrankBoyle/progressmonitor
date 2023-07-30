<?php
// Read the JSON data from the request body
$data = file_get_contents('php://input');

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
// Retrieve the existing JSON data from the database
$sql = "SELECT * FROM AvaK_Math ORDER BY id DESC LIMIT 1";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $existingData = json_decode($row['json_data'], true);
} else {
    $existingData = [];
}

// Decode the new JSON data from the request body
$newData = json_decode($data, true);

// Merge the new data with the existing data (if any)
$mergedData = array_merge($existingData, $newData);

// Convert the merged data back to JSON
$jsonData = json_encode($mergedData);

// Save the JSON data to the database
$sql = "INSERT INTO student1_data (json_data) VALUES ('$jsonData')";

if ($conn->query($sql) === TRUE) {
    echo "Table data saved successfully.";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();
?>
