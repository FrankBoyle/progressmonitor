<?php
// Database connection and error reporting settings
include('./users/db.php');

// Error Reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Initialize an empty array for performanceData
$performanceData = [];

// Check if `student_id` is provided in the URL
if (isset($_GET['student_id'])) {
    $studentId = $_GET['student_id'];

    $stmt = $connection->prepare("SELECT * FROM Performance WHERE student_id = ? ORDER BY week_start_date DESC LIMIT 41");
    $stmt->execute([$studentId]);

    $performanceData = $stmt->fetchAll();
}
if (isset($studentId)) {
    $stmt = $connection->prepare("SELECT school_id FROM Students WHERE student_id = ?");
    $stmt->execute([$studentId]);

    $result = $stmt->fetch();
    $schoolID = $result ? $result['school_id'] : null;
}

$scoreNames = [];
for($i = 1; $i <= 10; $i++) {
    $scoreNames['score'.$i] = 'Score'.$i;  // default names
}

if (!empty($performanceData)) {
    $stmt = $connection->prepare("SELECT original_name, custom_name FROM SchoolScoreNames WHERE SchoolID = ?");
    $stmt->execute([$schoolID]);

    while ($row = $stmt->fetch()) {
        $scoreNames[$row['original_name']] = $row['custom_name'];
    }
}

// Preparing the data for the chart
$chartDates = [];
$chartScores = [];

foreach ($performanceData as $record) {
    $chartDates[] = $record['week_start_date'];
    
    $totalScore = 0;
    for($i = 1; $i <= 10; $i++) {
        $totalScore += $record['score'.$i];
    }
    $avgScore = $totalScore / 10;
    $chartScores[] = $avgScore;
}
?>
