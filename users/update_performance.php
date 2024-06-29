<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('db.php');  
header('Content-Type: application/json');

// Main logic
if (isset($_POST['performance_id'], $_POST['field_name'], $_POST['new_value'])) {
    $performanceId = $_POST['performance_id'];
    $fieldName = $_POST['field_name'];
    $newValue = $_POST['new_value'];
    $studentId = $_POST['student_id'] ?? null;
    $metadata_id = $_POST['metadata_id'];
    if (in_array($fieldName, ['score1', 'score2', 'score3', 'score4', 'score5', 'score6', 'score7', 'score8', 'score9', 'score10'])) {
        if ($newValue === '' || !isset($newValue)) {
            $newValue = NULL;
        }
    }

    if ($fieldName === 'score_date') {
        $checkStmt = $connection->prepare("
        SELECT COUNT(*) 
        FROM Performance 
        WHERE 
            student_id_new = ? AND 
            score_date = ? AND 
            metadata_id = ? AND 
            performance_id != ?
    ");

        if($metadata_id !== null) {
            $checkStmt->execute([$studentId, $newValue, $metadata_id, $performanceId]);  
        } else {
            handleError("Metadata ID is missing!");  
            return;
        }
        
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

function updatePerformance($connection, $performanceId, $fieldName, $newValue) {
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

function handleError($errorMessage) {
    sendResponse(["success" => false, "error" => $errorMessage]);
}


function sendResponse($response) {
    echo json_encode($response);
    exit;
}
?>






