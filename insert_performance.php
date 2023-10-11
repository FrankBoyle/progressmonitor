<?php
// Include the database connection script
include('./users/db.php');

// Function to log errors server-side
function logError($error) {
    // Log error to a file (Ensure your server has write permissions for this file)
    file_put_contents('error_log.txt', $error . PHP_EOL, FILE_APPEND);
}

// Function to handle and send back errors
function handleError($errorMessage) {
    echo json_encode(['success' => false, 'error' => $errorMessage]);
    exit;
}

$responseData = [];

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    handleError("Invalid request method.");
    exit;
}

// Validate that the necessary POST data is present
if (empty($_POST['student_id']) || empty($_POST['week_start_date']) || empty($_POST['scores'])) {
    handleError("Required data is missing.");
    exit;
}

$studentId = $_POST['student_id'];
$weekStartDate = $_POST['week_start_date'];
$scores = $_POST['scores'];

foreach ($scores as $key => $score) {
    if ($score === '' || !isset($score)) {
        $scores[$key] = NULL;
    }
}

// Check for duplicate date entry
$checkStmt = $connection->prepare("SELECT COUNT(*) FROM Performance WHERE student_id = ? AND week_start_date = ?");
$checkStmt->execute([$studentId, $weekStartDate]);

if ($checkStmt->fetchColumn() > 0) {
    handleError("Duplicate date not allowed");
    exit;
}

$stmt = $connection->prepare("INSERT INTO Performance (student_id, week_start_date, score1, score2, score3, score4, score5, score6, score7, score8, score9, score10) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

if ($stmt->execute([$studentId, $weekStartDate, $scores['score1'], $scores['score2'], $scores['score3'], $scores['score4'], $scores['score5'], $scores['score6'], $scores['score7'], $scores['score8'], $scores['score9'], $scores['score10']])) {
    $newPerformanceId = $connection->lastInsertId();
    $responseData = [
        'success' => true,
        'performance_id' => $newPerformanceId,
        'week_start_date' => $weekStartDate,
        'scores' => $scores,
    ];
    echo json_encode($responseData);
} else {
    handleError("Failed to insert data: " . implode(" | ", $stmt->errorInfo()));
}

?>
