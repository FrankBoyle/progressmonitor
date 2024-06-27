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
    logError($errorMessage); // Log the error message
    echo json_encode(['success' => false, 'error' => $errorMessage, 'missing_data' => $missingData]);
    ob_end_flush();
    exit;
}

try {
    // Read and decode the incoming JSON request body
    $input = json_decode(file_get_contents('php://input'), true);
    file_put_contents('json_input_debug.txt', print_r($input, true));

    $studentId = isset($input['student_id']) ? $input['student_id'] : null;
    $schoolId = isset($input['school_id']) ? $input['school_id'] : null;
    $weekStartDate = isset($input['score_date']) ? $input['score_date'] : null;
    $scoreDate = isset($input['score_date']) ? $input['score_date'] : null;
    $scores = isset($input['scores']) ? $input['scores'] : null;
    $metadata_id = isset($input['metadata_id']) ? $input['metadata_id'] : null;

    file_put_contents('post_data_debug.txt', print_r(compact('studentId', 'schoolId', 'weekStartDate', 'scoreDate', 'scores', 'metadata_id'), true), FILE_APPEND);

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

    if ($stmt->execute([$studentId, $metadata_id, $schoolId, $weekStartDate, $scores['score1'], $scores['score2'], $scores['score3'], $scores['score4'], $scores['score5'], $scores['score6'], $scores['score7'], $scores['score8'], $scores['score9'], $scores['score10']])) {
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

