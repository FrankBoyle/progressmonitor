<?php
session_start();

include('auth_session.php');
include('db.php');

// Function to get reporting periods and notes for a specific goal
function getReportingPeriods($goalId, $studentIdNew, $schoolId, $metadataId) {
    global $connection;

    try {
        $stmt = $connection->prepare("SELECT reporting_period, notes FROM Goal_notes WHERE goal_id = ? AND student_id_new = ? AND school_id = ? AND metadata_id = ?");
        $stmt->execute([$goalId, $studentIdNew, $schoolId, $metadataId]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $results;
    } catch (PDOException $e) {
        throw new Exception('Database error: ' . $e->getMessage());
    }
}

// Ensure the request method is POST and required parameters are set
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (isset($data['goal_id'], $data['student_id_new'], $data['school_id'], $data['metadata_id'])) {
        $goalId = $data['goal_id'];
        $studentIdNew = $data['student_id_new'];
        $schoolId = $data['school_id'];
        $metadataId = $data['metadata_id'];

        try {
            $reportingPeriods = getReportingPeriods($goalId, $studentIdNew, $schoolId, $metadataId);
            header('Content-Type: application/json');
            echo json_encode(['status' => 'success', 'data' => $reportingPeriods]);
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
