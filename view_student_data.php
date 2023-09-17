<?php
    include('./users/db.php');

// 1. Error Reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Assuming your PDO connection code goes somewhere here
// For the sake of this example, I'll use a placeholder.

// 3. PDO Error Mode
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// 2. Check if `student_id` is Set
if (!isset($_GET['student_id'])) {
    die('Student ID is not provided.');
}
$studentId = $_GET['student_id'];

$stmt = $pdo->prepare("SELECT * FROM Performance WHERE student_id = ? ORDER BY week_start_date DESC LIMIT 41");
$stmt->execute([$studentId]);

$performanceData = $stmt->fetchAll();

// 4. Check Data Retrieval
if (empty($performanceData)) {
    die('No performance data found for the given student ID.');
}

echo "<table border='1'>";
// 5. Handle the `...` in Table Header: For now, I'll just remove the ... for clarity
echo "<tr><th>Week Start Date</th><th>Score1</th><th>Score2</th><th>Score3</th><th>Score4</th><th>Score5</th><th>Score6</th><th>Score7</th><th>Score8</th><th>Score9</th><th>Score10</th></tr>";

foreach ($performanceData as $data) {
    echo "<tr>";
    echo "<td>" . $data['week_start_date'] . "</td>";
    echo "<td>" . $data['score1'] . "</td>";
    echo "<td>" . $data['score2'] . "</td>";
    echo "<td>" . $data['score3'] . "</td>";
    echo "<td>" . $data['score4'] . "</td>";
    echo "<td>" . $data['score5'] . "</td>";
    echo "<td>" . $data['score6'] . "</td>";
    echo "<td>" . $data['score7'] . "</td>";
    echo "<td>" . $data['score8'] . "</td>";
    echo "<td>" . $data['score9'] . "</td>";
    echo "<td>" . $data['score10'] . "</td>";
    echo "</tr>";
}

echo "</table>";
?>
