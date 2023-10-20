<?php
// Include the database connection script
include('./users/db.php');
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

$responseData = [];

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    handleError("Invalid request method.");
    exit;
}

if (empty($_POST['student_id'])) {
    handleError("student_id is missing.");
    exit;
}
if (empty($_POST['score_date'])) {
    handleError("score_date is missing.");
    exit;
}
if (empty($_POST['scores'])) {
    handleError("scores are missing.");
    exit;
}

// Check if these variables are set before using them
if (isset($_POST['scores'])) {
    $scores = $_POST['scores'];
} else {
    $scores = array(); // or set it to an empty array or handle it as needed
}

if (isset($_POST['student_id'])) {
    $studentId = $_POST['student_id'];
} else {
    // Handle the case where student_id is not set
}

if (isset($_POST['score_date'])) {
    $weekStartDate = $_POST['score_date'];
} else {

foreach ($scores as $key => $score) {
    if ($score === '' || !isset($score)) {
        $scores[$key] = NULL;
    }
}
}

// Check for duplicate date entry
$checkStmt = $connection->prepare("SELECT COUNT(*) FROM Performance WHERE student_id = ? AND score_date = ? AND metadata_id = ?");
$checkStmt->execute([$studentId, $weekStartDate]);

if ($checkStmt->fetchColumn() > 0) {
    handleError("Duplicate date not allowed!");
    exit;
}

$studentId = $_POST['student_id'];
//$metadata_id = $_POST['metadata_id']; // Get metadata_id from POST
$schoolId = $_POST['school_id']; // Get school_id from POST
$weekStartDate = $_POST['score_date'];
//$scores = $_POST['scores'];
$metadata_id = $_POST['metadata_id'];
//$schoolId = isset($_POST['school_id']) ? $_POST['school_id'] : null;
// Retrieve metadataId from URL parameters

$stmt = $connection->prepare("INSERT INTO Performance (student_id, metadata_id, school_id, score_date, score1, score2, score3, score4, score5, score6, score7, score8, score9, score10) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

if ($stmt->execute([$studentId, $metadata_id, $schoolId, $weekStartDate, $score1, $score2, $score3, $score4, $score5, $score6, $score7, $score8, $score9, $score10])) {
    // Successful insertion
    $newPerformanceId = $connection->lastInsertId();
    $responseData = [
        'success' => true,
        'performance_id' => $newPerformanceId,
        'score_date' => $weekStartDate,
        'scores' => $scores,
        'school_id' => $schoolId, // Add school_id to the response
        'metadata_id' => $metadata_id, // Add metadata_id to the response
    ];
    echo json_encode($responseData);
} else {
    // Error during insertion
    handleError("Failed to insert data: " . implode(" | ", $stmt->errorInfo()));
}


?>
