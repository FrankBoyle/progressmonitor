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

echo "PHP is working!";

// Start session
session_start();

// Error reporting for development
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Ensure teacher_id is in the session
if (!isset($_SESSION['teacher_id'])) {
    die("Teacher ID not set in session");
}

$teacherId = $_SESSION['teacher_id'];
echo "Teacher ID from session: " . $teacherId;

$stmt = $conn->prepare("SELECT s.* FROM Students s INNER JOIN Teacher_Student_Assignment tsa ON s.student_id = tsa.student_id WHERE tsa.teacher_id = ?");
$stmt->bind_param('i', $teacherId);
$stmt->execute();

// Check for errors during student fetch
if ($stmt->error) {
    die("Error during student fetch: " . $stmt->error);
}

$result = $stmt->get_result();
$students = $result->fetch_all(MYSQLI_ASSOC);

// Check if any students are retrieved
if (empty($students)) {
    die("No students found for this teacher.");
}

foreach ($students as $student) {
    echo "<a href='view_student_data.php?student_id=" . $student['student_id'] . "'>" . $student['name'] . "</a><br>";
}

if (isset($_GET['student_id'])) {
    $studentId = $_GET['student_id'];

    $stmt = $conn->prepare("SELECT * FROM Performance WHERE student_id = ? ORDER BY week_start_date DESC LIMIT 41");  // you can change the LIMIT as needed
    $stmt->bind_param('i', $studentId);
    $stmt->execute();
    
    // Check for errors during performance data fetch
    if ($stmt->error) {
        die("Error during performance data fetch: " . $stmt->error);
    }

    $result = $stmt->get_result();
    $performanceData = $result->fetch_all(MYSQLI_ASSOC);
    
    echo "<table border='1'>";
    echo "<tr><th>Week Start Date</th><th>Score1</th><th>Score2</th>...<th>Score10</th></tr>";  // Add more headers if needed
    
    foreach ($performanceData as $data) {
        echo "<tr>";
        for ($i = 1; $i <= 10; $i++) {
            echo "<td data-value='" . $data['score' . $i] . "' class='editable'>" . $data['score' . $i] . "</td>";
        }
        echo "</tr>";
    }
    
    echo "</table>";
}

?>

