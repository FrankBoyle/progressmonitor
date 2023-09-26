<?php
include('./users/db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentId = $_POST['student_id'];
    $weekStartDate = $_POST['week_start_date'];
    $scores = $_POST['scores']; // Assuming scores are passed as an array

    // Insert the new performance data into the database
    $stmt = $connection->prepare("INSERT INTO Performance (student_id, week_start_date, score1, score2, score3, score4, score5, score6, score7, score8, score9, score10) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$studentId, $weekStartDate, $scores['score1'], $scores['score2'], $scores['score3'], $scores['score4'], $scores['score5'], $scores['score6'], $scores['score7'], $scores['score8'], $scores['score9'], $scores['score10']]);

    // You can optionally return the inserted data to the client if needed
    $newPerformanceId = $connection->lastInsertId();
    $responseData = [
        'performance_id' => $newPerformanceId,
        'week_start_date' => $weekStartDate,
        'scores' => $scores,
    ];

    echo json_encode($responseData);
} else {
    // Handle invalid requests or show an error message
    echo 'Invalid request';
}
?>
