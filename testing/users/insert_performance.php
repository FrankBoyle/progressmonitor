<?php
session_start();
include('auth_session.php');
include('db.php');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

// Start output buffering to catch any unexpected output
ob_start();

file_put_contents('post_data_debug.txt', print_r($_POST, true));

// Function to log errors server-side
function logError($error) {
    file_put_contents('error_log.txt', $error . PHP_EOL, FILE_APPEND);
}

// Function to handle and send back errors
function handleError($errorMessage, $missingData = []) {
    echo json_encode(['success' => false, 'error' => $errorMessage, 'missing_data' => $missingData]);
    ob_end_flush();
    exit;
}

try {
    $studentId = $_POST['student_id'];
    $schoolId = $_POST['school_id'];
    $weekStartDate = $_POST['score_date'];
    $scoreDate = $_POST['score_date'];
    $scores = $_POST['scores'];
    $metadata_id = $_POST['metadata_id'];
    $score1 = isset($_POST['score1']) ? $_POST['score1'] : null;
    $score2 = isset($_POST['score2']) ? $_POST['score2'] : null;
    $score3 = isset($_POST['score3']) ? $_POST['score3'] : null;
    $score4 = isset($_POST['score4']) ? $_POST['score4'] : null;
    $score5 = isset($_POST['score5']) ? $_POST['score5'] : null;
    $score6 = isset($_POST['score6']) ? $_POST['score6'] : null;
    $score7 = isset($_POST['score7']) ? $_POST['score7'] : null;
    $score8 = isset($_POST['score8']) ? $_POST['score8'] : null;
    $score9 = isset($_POST['score9']) ? $_POST['score9'] : null;
    $score10 = isset($_POST['score10']) ? $_POST['score10'] : null;
    $responseData = [];

    // Check if the request method is POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        handleError("Invalid request method.");
    }

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

    if ($stmt->execute([$studentId, $metadata_id, $schoolId, $weekStartDate, $score1, $score2, $score3, $score4, $score5, $score6, $score7, $score8, $score9, $score10])) {
        $newPerformanceId = $connection->lastInsertId();
        $responseData = [
            'success' => true,
            'performance_id' => $newPerformanceId,
            'score_date' => $weekStartDate,
            'scores' => $scores,
            'school_id' => $schoolId,
            'metadata_id' => $metadata_id,
        ];
        echo json_encode($responseData);
    } else {
        handleError("Failed to insert data: " . implode(" | ", $stmt->errorInfo()));
    }
} catch (Exception $e) {
    logError($e->getMessage());
    handleError("An unexpected error occurred.");
}

// Flush the output buffer
ob_end_flush();
?>
