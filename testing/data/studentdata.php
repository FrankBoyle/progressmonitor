<?php
include("./users/auth_session.php");
session_start(); // Start the session

$servername = "localhost";
$username = "AndersonSchool";
$password = "SpecialEd69$";
$dbname = "AndersonSchool";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$selectedTable = $_POST['selected_table'] ?? $_SESSION['selected_table'] ?? 'JaylaBrazzle1'; // Set a default table name

//echo "Updating records in table: $selectedTable<br>";

// Handle updates for ID, date, score, and baseline
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    foreach ($_POST['id'] as $key => $id) {
        $date = $_POST["date"][$key];
        $score = $_POST["score"][$key];
        $baseline = $_POST["baseline"][$key];

        $update_sql = "UPDATE $selectedTable SET date='$date', score='$score', baseline='$baseline' WHERE id=$id";
       
        if ($conn->query($update_sql) !== TRUE) {
            echo "Error updating record: " . $conn->error;
        }
    }
}

// Handle goal update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_goal'])) {
    $newGoal = $_POST["edit_goal"];
    
    // Update the goal in the database
    $updateGoalSql = "UPDATE $selectedTable SET goal='$newGoal' WHERE 1";
    if ($conn->query($updateGoalSql) !== TRUE) {
        echo "Error updating goal: " . $conn->error;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['select_table'])) {
    // Handle student selection
    $selectedTable = $_POST['selected_table'];
    $_SESSION['selected_table'] = $selectedTable; // Store the selected table value in a session variable
}

$sql = "SELECT id, date, score, baseline, goal FROM $selectedTable";
$result = $conn->query($sql);
?>

