<?php
include('./users/db.php');

// Error Reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function fetchPerformanceData($studentId) {
    global $connection;

    $stmt = $connection->prepare("SELECT * FROM Performance WHERE student_id = ? ORDER BY week_start_date DESC LIMIT 41");
    $stmt->execute([$studentId]);

    return $stmt->fetchAll();
}

function fetchSchoolIdForStudent($studentId) {
    global $connection;

    $stmt = $connection->prepare("SELECT SchoolID FROM Students WHERE student_id = ?");
    $stmt->execute([$studentId]);

    $result = $stmt->fetch();
    return $result ? $result['SchoolID'] : null;
}

function fetchScoreNames($schoolID) {
    global $connection;

    $scoreNames = [];
    $stmt = $connection->prepare("SELECT original_name, custom_name FROM SchoolScoreNames WHERE SchoolID = ?");
    $stmt->execute([$schoolID]);

    while ($row = $stmt->fetch()) {
        $scoreNames[$row['original_name']] = $row['custom_name'];
    }

    return $scoreNames;
}

// This section remains mostly unchanged but uses the functions above
$performanceData = [];
$scoreNames = [];

if (isset($_GET['student_id'])) {
    $studentId = $_GET['student_id'];
    $performanceData = fetchPerformanceData($studentId);
    $schoolID = fetchSchoolIdForStudent($studentId);
    $scoreNames = fetchScoreNames($schoolID);
}
if (isset($studentId)) {
    // Update the column name to SchoolID
    $stmt = $connection->prepare("SELECT SchoolID FROM Students WHERE student_id = ?");
    $stmt->execute([$studentId]);

    $result = $stmt->fetch();
    $schoolID = $result ? $result['SchoolID'] : null;  // Update the column name to SchoolID
}

if (!empty($performanceData)) {
    // Update the column name in the WHERE condition to SchoolID
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
    
    //$totalScore = 0;
    //for($i = 1; $i <= 10; $i++) {
        //$totalScore += $record['score'.$i];
    //}
    //$avgScore = $totalScore / 10;
    //$chartScores[] = $avgScore;
}
?>
