<?php
// Error Reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('db.php');

function fetchPerformanceData($studentId, $metadataId) {
    global $connection;
    try {
        $stmt = $connection->prepare("SELECT * FROM Performance WHERE student_id = ? AND metadata_id = ? ORDER BY score_date DESC LIMIT 41");
        $stmt->execute([$studentId, $metadataId]); // Pass both studentId and metadataId as parameters
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        // Handle the database error here, e.g., log the error, return an error response, etc.
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        exit;
    }
}

// Initialize the response array
$response = [];

if (isset($_GET['student_id'])) {
    $studentId = $_GET['student_id'];
    $schoolID = fetchSchoolIdForStudent($studentId);

    if (!$schoolID) {
        $response['error'] = 'No SchoolID found for the student';
    } else {
        // Check if the metadata_id parameter is provided
        if (isset($_GET['metadata_id'])) {
            $metadataId = $_GET['metadata_id'];

            // Fetch performance data based on studentId and metadataId
            $performanceData = fetchPerformanceData($studentId, $metadataId);

            // Fetch the column headers based on the selected metadataId
            $columnHeaders = fetchColumnHeaders($metadataId);

            // Construct the data to send to the client
            $responseData = [
                'columnHeaders' => $columnHeaders,
                'performanceData' => $performanceData,
            ];

            // Handle null values in columnHeaders
            foreach ($responseData['columnHeaders'] as $key => $value) {
                if ($value === null) {
                    $responseData['columnHeaders'][$key] = "N/A";
                }
            }

            // Handle null values in performanceData
            foreach ($responseData['performanceData'] as &$item) {
                foreach ($item as $key => $value) {
                    if ($value === null) {
                        $item[$key] = "N/A";
                    }
                }
            }

            // Set the response array to the constructed data
            $response = $responseData;
        } else {
            $response['error'] = 'metadata_id parameter is missing';
        }
    }
} else {
    $response['error'] = 'student_id parameter is missing';
}

// Send the response as JSON
echo json_encode($response);

?>
