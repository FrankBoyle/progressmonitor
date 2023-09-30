<?php


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('./users/db.php');

// Create a new database connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the database connection
if ($conn->connect_error) {
    handleError("Connection failed: " . $conn->connect_error);
}

// Main logic
if (isset($_POST['performance_id'], $_POST['field_name'], $_POST['new_value'])) {
    updatePerformance($conn, $_POST['performance_id'], $_POST['field_name'], $_POST['new_value']);
} else {
    handleError("Invalid data provided.");
}

/**
 * Function to update the Performance data.
 */
function updatePerformance($conn, $performanceId, $fieldName, $newValue) {
    // List of allowed field names to ensure security
    $allowedFields = ['week_start_date', 'score1', 'score2', 'score3', 'score4', 'score5', 'score6', 'score7', 'score8', 'score9', 'score10'];

    if (!in_array($fieldName, $allowedFields)) {
        handleError("Invalid field specified.");
        return;
    }

    // Prepare SQL statement
    $sql = "UPDATE Performance SET $fieldName = ? WHERE performance_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('si', $newValue, $performanceId);

    // Execute and respond
    if ($stmt->execute()) {
        sendResponse(["success" => true]);
    } else {
        handleError("Database error: " . $stmt->error);
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


