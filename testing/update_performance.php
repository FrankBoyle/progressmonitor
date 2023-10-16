<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('./users/db.php');  // Include the database connection
header('Content-Type: application/json');

// Main logic
if (isset($_POST['student_id'], $_POST['metadata_id'])) {
    $studentId = $_POST['student_id'];
    $metadataId = $_POST['metadata_id'];

    // Fetch data based on student_id and metadata_id
    $data = fetchData($studentId, $metadataId);
    
    // Return the fetched data as a JSON response
    echo json_encode($data);
} else {
    handleError("Invalid data provided.");
}

/**
 * Function to fetch performance data for the specified student_id and metadata_id.
 */
function fetchData($studentId, $metadataId) {
    global $connection;

    // Prepare SQL statement to fetch data based on student_id and metadata_id
    $sql = "SELECT * FROM `Performance` WHERE `student_id` = ? AND `metadata_id` = ?";
    $stmt = $connection->prepare($sql);
    $stmt->bindParam(1, $studentId);
    $stmt->bindParam(2, $metadataId);

    // Execute the query
    if ($stmt->execute()) {
        // Fetch all rows and return as an array
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
