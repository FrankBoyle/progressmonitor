<?php
session_start();
include('auth_session.php');
include('db.php');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

file_put_contents('post_data_debug.txt', print_r($_POST, true));

// Function to log errors server-side
function logError($error) {
    // Log error to a file (Ensure your server has write permissions for this file)
    file_put_contents('error_log.txt', $error . PHP_EOL, FILE_APPEND);
}

// Function to handle and send back errors
function handleError($errorMessage, $missingData = []) {
    echo json_encode(['success' => false, 'error' => $errorMessage, 'missing_data' => $missingData]);
    exit;
}

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    handleError("Invalid request method.");
}

// Validate and sanitize input
$studentId = isset($_POST['student_id']) ? intval($_POST['student_id']) : null;
$schoolId = isset($_POST['school_id']) ? intval($_POST['school_id']) : null;
$scoreDate = isset($_POST['score_date']) ? $_POST['score_date'] : null;
$scores = isset($_POST['scores']) ? $_POST['scores'] : [];
$metadata_id = isset($_POST['metadata_id']) ? intval($_POST['metadata_id']) : null;

if (empty($studentId)) {
    handleError("student_id is missing.");
}
if (empty($scoreDate)) {
    handleError("score_date is missing.");
}
if (empty($scores)) {
    handleError("scores are missing.");
}

// Check for duplicate date entry
$checkStmt = $connection->prepare(
    "SELECT COUNT(*) FROM Performance 
     WHERE student_id_new = ? AND score_date = ? AND metadata_id = ?"
);
$checkStmt->execute([$studentId, $scoreDate, $metadata_id]);
$duplicateCount = $checkStmt->fetchColumn();

if ($duplicateCount > 0) {
    handleError("Duplicate date entry is not allowed. A record with this date and metadata already exists for the selected student.");
}

// Prepare scores array
for ($i = 1; $i <= 10; $i++) {
    $scores["score$i"] = isset($scores["score$i"]) ? $scores["score$i"] : null;
}

// Insert data into the database
$stmt = $connection->prepare("
    INSERT INTO Performance (student_id_new, metadata_id, school_id, score_date, score1, score2, score3, score4, score5, score6, score7, score8, score9, score10) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

if ($stmt->execute([$studentId, $metadata_id, $schoolId, $scoreDate, $scores['score1'], $scores['score2'], $scores['score3'], $scores['score4'], $scores['score5'], $scores['score6'], $scores['score7'], $scores['score8'], $scores['score9'], $scores['score10']])) {
    $newPerformanceId = $connection->lastInsertId();
    $responseData = [
        'success' => true,
        'performance_id' => $newPerformanceId,
        'score_date' => $scoreDate,
        'scores' => $scores,
        'school_id' => $schoolId,
        'metadata_id' => $metadata_id,
    ];
    echo json_encode($responseData);
} else {
    handleError("Failed to insert data: " . implode(" | ", $stmt->errorInfo()));
}
?>
