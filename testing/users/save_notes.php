<?php
session_start();

include('auth_session.php');
include('db.php');

// Function to get the next reporting period for a specific goal
function getNextReportingPeriod($goalId, $studentIdNew, $schoolId, $metadataId) {
    global $connection;

    try {
        $stmt = $connection->prepare("SELECT MAX(reporting_period) AS max_reporting_period FROM Goal_notes WHERE goal_id = ? AND student_id_new = ? AND school_id = ? AND metadata_id = ?");
        $stmt->execute([$goalId, $studentIdNew, $schoolId, $metadataId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $maxReportingPeriod = $row['max_reporting_period'];
        return $maxReportingPeriod ? $maxReportingPeriod + 1 : 1;
    } catch (PDOException $e) {
        throw new Exception('Database error: ' . $e->getMessage());
    }
}

// Function to add or update notes
function addOrUpdateNotes($goalId, $studentIdNew, $schoolId, $metadataId, $reportingPeriod, $notes, $reportImage) {
    global $connection;

    try {
        // Check if the record already exists
        $stmt = $connection->prepare("SELECT COUNT(*) FROM Goal_notes WHERE goal_id = ? AND student_id_new = ? AND school_id = ? AND metadata_id = ? AND reporting_period = ?");
        $stmt->execute([$goalId, $studentIdNew, $schoolId, $metadataId, $reportingPeriod]);
        $exists = $stmt->fetchColumn() > 0;

        if ($exists) {
            // Update existing record
            $stmt = $connection->prepare("UPDATE Goal_notes SET notes = ?, report_image = ? WHERE goal_id = ? AND student_id_new = ? AND school_id = ? AND metadata_id = ? AND reporting_period = ?");
            $stmt->execute([$notes, $reportImage, $goalId, $studentIdNew, $schoolId, $metadataId, $reportingPeriod]);
        } else {
            // Insert new record
            $stmt = $connection->prepare("INSERT INTO Goal_notes (goal_id, student_id_new, school_id, metadata_id, reporting_period, notes, report_image) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$goalId, $studentIdNew, $schoolId, $metadataId, $reportingPeriod, $notes, $reportImage]);
        }

        // Ensure no additional output is sent
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'message' => 'Notes saved successfully.']);
    } catch (PDOException $e) {
        // Handle any database errors
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

// Ensure the request method is POST and required parameters are set
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (isset($data['goal_id'], $data['student_id_new'], $data['school_id'], $data['metadata_id'], $data['reporting_period'], $data['notes'], $data['report_image'])) {
        $goalId = $data['goal_id'];
        $studentIdNew = $data['student_id_new'];
        $schoolId = $data['school_id'];
        $metadataId = $data['metadata_id'];
        $reportingPeriod = $data['reporting_period'];
        $notes = $data['notes'];
        $reportImage = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $data['report_image'])); // Decode base64 image

        try {
            // Call the function to add or update notes
            addOrUpdateNotes($goalId, $studentIdNew, $schoolId, $metadataId, $reportingPeriod, $notes, $reportImage);
        } catch (Exception $e) {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    } else {
        header('Content-Type: application/json');
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid request or missing parameters.']);
    }
} else {
    header('Content-Type: application/json');
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}

// Ensure no additional output is sent
exit;
?>

