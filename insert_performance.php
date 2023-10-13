<?php
// Include the database connection script
include('./users/db.php');
header('Content-Type: application/json');

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

$responseData = [];

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    handleError("Invalid request method.");
    exit;
}

if (empty($_POST['student_id']) || empty($_POST['score_date']) || empty($_POST['scores'])) {
    $missingData = [];
    if (empty($_POST['student_id'])) {
        $missingData[] = 'student_id';
    }
    if (empty($_POST['score_date'])) {
        $missingData[] = 'score_date';
    }
    if (empty($_POST['scores'])) {
        $missingData[] = 'scores';
    }
    
    handleError("Required data is missing.", $missingData);
    exit;
}

// Validate that the necessary POST data is present
if (empty($_POST['student_id']) || empty($_POST['score_date']) || empty($_POST['scores'])) {
    handleError("Required data is missing.");
    exit;
}

$studentId = $_POST['student_id'];
$weekStartDate = $_POST['score_date'];
$scores = $_POST['scores'];

foreach ($scores as $key => $score) {
    if ($score === '' || !isset($score)) {
        $scores[$key] = NULL;
    }
}

// Check for duplicate date entry
$checkStmt = $connection->prepare("SELECT COUNT(*) FROM Performance WHERE student_id = ? AND score_date = ?");
$checkStmt->execute([$studentId, $weekStartDate]);

if ($checkStmt->fetchColumn() > 0) {
    handleError("Duplicate date not allowed!");
    exit;
}

$stmt = $connection->prepare("INSERT INTO Performance (student_id, score_date, score1, score2, score3, score4, score5, score6, score7, score8, score9, score10) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

if ($stmt->execute([$studentId, $weekStartDate, $scores['score1'], $scores['score2'], $scores['score3'], $scores['score4'], $scores['score5'], $scores['score6'], $scores['score7'], $scores['score8'], $scores['score9'], $scores['score10']])) {
    $newPerformanceId = $connection->lastInsertId();
    $responseData = [
        'success' => true,
        'performance_id' => $newPerformanceId,
        'score_date' => $weekStartDate,
        'scores' => $scores,
    ];
    echo json_encode($responseData);
} else {
    handleError("Failed to insert data: " . implode(" | ", $stmt->errorInfo()));
}

?>

