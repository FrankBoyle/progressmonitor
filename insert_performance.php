<?php
// Include your database connection settings
include('./users/db.php');

// Fetch data from POST request
$studentId = $_POST['student_id'];
$weekStartDate = $_POST['week_start_date'];
$scores = $_POST['scores'];

// Prepare SQL statement
$stmt = $connection->prepare("INSERT INTO Performance (student_id, week_start_date, score1, score2, score3, score4, score5, score6, score7, score8, score9, score10/* ... other score fields */) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?/* ... other score values */)");

// Bind the parameters
$stmt->bindParam(1, $studentId);
$stmt->bindParam(2, $weekStartDate);
$stmt->bindParam(3, $scores['score1']);
$stmt->bindParam(4, $scores['score2']);
$stmt->bindParam(4, $scores['score3']);
$stmt->bindParam(4, $scores['score4']);
$stmt->bindParam(4, $scores['score5']);
$stmt->bindParam(4, $scores['score6']);
$stmt->bindParam(4, $scores['score7']);
$stmt->bindParam(4, $scores['score8']);
$stmt->bindParam(4, $scores['score9']);
$stmt->bindParam(4, $scores['score10']);
// ... continue for other score fields

// Execute the statement
$stmt->execute();
?>