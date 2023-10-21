<?php
$servername = "localhost";
$username = "AndersonSchool";
$password = "SpecialEd69$";
$dbname = "AndersonSchool";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get user preferences from the form
$columnTitle = $_POST['column_title'];
$visibleColumns = $_POST['visible_columns'];

// Update user preferences in the database (replace with proper user authentication)
$userID = 1; // Change this to the actual user ID
$sql = "INSERT INTO JaylaBrazzle1 (Date, Score, Baseline) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("???", $Date , $columnTitle, implode(",", $visibleColumns));
$stmt->execute();
$stmt->close();

// Fetch data based on user preferences
$sql = "SELECT " . implode(",", $visibleColumns) . " FROM user_data"; // user_data should be your data table
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Display fetched data
    echo "<table>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        foreach ($visibleColumns as $col) {
            echo "<td>" . $row[$col] . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "No data available.";
}

$conn->close();
?>
