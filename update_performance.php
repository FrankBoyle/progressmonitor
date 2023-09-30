<?php
include('./users/db.php');  // Assuming you have a separate connection script

$response = array();  // Prepare a response array

// Check if the necessary data is provided in the POST request
if (isset($_POST['performance_id'], $_POST['field_name'], $_POST['new_value'])) {

    // Define an array of allowed field names for security
    $allowedFieldNames = ["week_start_date", "score1", "score2", "score3", "score4", "score5", "score6", "score7", "score8", "score9", "score10"];

    $performanceId = $_POST['performance_id'];
    $fieldName = $_POST['field_name'];
    $newValue = $_POST['new_value'];

    // Check if the provided field name is in the list of allowed field names
    if (!in_array($fieldName, $allowedFieldNames)) {
        $response['success'] = false;
        $response['error'] = "An error occurred. Please try again later.";
        echo json_encode($response);
        exit;
    }

    // Prepare SQL string using string concatenation for the field name
    $sql = "UPDATE Performance SET " . $fieldName . " = ? WHERE performance_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('si', $newValue, $performanceId);

    if ($stmt->execute()) {
        $response['success'] = true;
    } else {
        $response['success'] = false;
        $response['error'] = "An error occurred. Please try again later.";  // Generic error message
    }

} else {
    $response['success'] = false;
    $response['error'] = "Invalid data provided.";
}

// Return the response as JSON
echo json_encode($response);

?>


