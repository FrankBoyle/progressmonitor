<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('./users/db.php');  // Include the database connection
header('Content-Type: application/json');

// Main logic
if (isset($_POST['performance_id'], $_POST['field_name'], $_POST['new_value'])) {
    $performanceId = $_POST['performance_id'];
    $fieldName = $_POST['field_name'];
    $newValue = $_POST['new_value'];
    $student_id = $_POST['student_id'] ?? null;

    // If the field being updated is one of the score fields and the value is empty, set it to NULL.
    if (in_array($fieldName, ['score1', 'score2', 'score3', 'score4', 'score5', 'score6', 'score7', 'score8', 'score9', 'score10'])) {
        if ($newValue === '' || !isset($newValue)) {
            $newValue = NULL;
        }
    }

    // Validate and sanitize the date input (assuming it's for the 'score_date' field)
    if ($fieldName === 'score_date') {
        $checkStmt = $connection->prepare("SELECT COUNT(*) FROM Performance WHERE student_id = ? AND score_date = ? AND performance_id != ?");
        $checkStmt->execute([$student_id, $newValue, $performanceId]); // Ensure to grab the $student_id in this script too.
        $count = $checkStmt->fetchColumn();
    
        if ($count > 0) {
            handleError("Duplicate date not allowed!");
            return;
        }
        
        // Inside the `if ($fieldName === 'score_date') { ... }` block:
        $newDate = date_create_from_format('Y-m-d', $newValue);
        if (!$newDate) {
            handleError("Invalid date format received. Expected 'Y-m-d' format but received: " . $newValue);
            return;
        }
        $newValue = date_format($newDate, 'Y-m-d');
    }

    updatePerformance($connection, $performanceId, $fieldName, $newValue);  
} else {
    handleError("Invalid data provided.");
}

/**
 * Function to update the Performance data.
 */
function updatePerformance($connection, $performanceId, $fieldName, $newValue) {
    // List of allowed field names to ensure security
    $allowedFields = ['score_date', 'score1', 'score2', 'score3', 'score4', 'score5', 'score6', 'score7', 'score8', 'score9', 'score10'];

    if (!in_array($fieldName, $allowedFields)) {
        handleError("Invalid field specified.");
        return;
    }

    // Prepare SQL statement
    $sql = "UPDATE `Performance` SET `$fieldName` = ? WHERE `performance_id` = ?";
    $stmt = $connection->prepare($sql);
    $stmt->bindParam(1, $newValue);
    $stmt->bindParam(2, $performanceId);

    // Execute and respond
    if ($stmt->execute()) {
        sendResponse(["success" => true]);
    } else {
        handleError("Database error: " . $stmt->errorInfo()[2]);
    }
}

/**
 * Function to handle errors.
 */
function handleError($errorMessage) {
    sendResponse(["success" => false, "error" => $errorMessage]);
}

/**
 * Function to send a JSON response.
 */
function sendResponse($response) {
    echo json_encode($response);
    exit;
}
?>





