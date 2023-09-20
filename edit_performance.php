<?php
$servername = "localhost";
$username = "AndersonSchool";
$password = "SpecialEd69$";
$dbname = "bFactor-test";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} else {
    echo "Connected successfully to the database";
}

if (isset($_GET['performance_id'])) {
    $performanceId = $_GET['performance_id'];

    // Retrieve the performance record based on the performance_id
    $stmt = $conn->prepare("SELECT * FROM Performance WHERE performance_id = ?");
    $stmt->bind_param('i', $performanceId);
    $stmt->execute();

    // Check for errors during performance data fetch
    if ($stmt->error) {
        die("Error during performance data fetch: " . $stmt->error);
    }

    $result = $stmt->get_result();
    $performanceData = $result->fetch_assoc();

    if (!$performanceData) {
        die("Performance data not found for the given performance_id.");
    }

    // Display an edit form for the performance data
    echo "<h1>Edit Performance Data</h1>";
    echo "<form method='POST' action='update_performance.php'>"; // Create an 'update_performance.php' page for processing updates
    echo "<input type='hidden' name='performance_id' value='" . $performanceData['performance_id'] . "'>";

    // Display input fields for score1, score2, etc. (adjust as needed)
    for ($i = 1; $i <= 10; $i++) {
        echo "Score " . $i . ": <input type='text' name='score" . $i . "' value='" . $performanceData['score' . $i] . "'><br>";
    }

    echo "<input type='submit' value='Update'>";
    echo "</form>";
}
?>
