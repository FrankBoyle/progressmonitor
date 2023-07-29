<?php
require('db.php');
include("auth_session.php");
?>

<?php
// Retrieve the existing JSON data from the database
$sql = "SELECT json_data FROM student1_data ORDER BY id DESC LIMIT 1";
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
