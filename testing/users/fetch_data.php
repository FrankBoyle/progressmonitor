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
        $stmt->execute([$studentId, $metadataId]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        exit;
    }
}
function fetchSchoolIdForStudent($studentId) {
    global $connection;
    try {
        $stmt = $connection->prepare("SELECT SchoolID FROM Students WHERE student_id = ?");
        $stmt->execute([$studentId]);
        $result = $stmt->fetch();
        return $result ? $result['SchoolID'] : null;
    } catch (PDOException $e) {
        // Handle the database error here, e.g., log the error, return an error response, etc.
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        exit;
    }
}
function fetchMetadataCategoriesFromDatabase($schoolID) {
    global $connection;
    try {
        $stmt = $connection->prepare("SELECT metadata_id, category_name FROM Metadata WHERE SchoolID = ?");
        $stmt->execute([$schoolID]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Handle the database error here, e.g., log the error, return an error response, etc.
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        exit;
    }
}
$response = [];

if (isset($_GET['student_id'])) {
    $studentId = $_GET['student_id'];
    $schoolID = fetchSchoolIdForStudent($studentId);

    if (!$schoolID) {
        $response['error'] = 'No SchoolID found for the student';
    } else {
        if (isset($_GET['metadata_id'])) {
            $metadataId = $_GET['metadata_id'];
            $performanceData = fetchPerformanceData($studentId, $metadataId);

            if ($performanceData) {
                $columnHeaders = fetchColumnHeaders($metadataId);
                
                $responseData = [
                    'columnHeaders' => $columnHeaders,
                    'performanceData' => $performanceData,
                ];

                foreach ($responseData['columnHeaders'] as $key => $value) {
                    if ($value === null) {
                        $responseData['columnHeaders'][$key] = "N/A";
                    }
                }

                foreach ($responseData['performanceData'] as &$item) {
                    foreach ($item as $key => $value) {
                        if ($value === null) {
                            $item[$key] = "N/A";
                        }
                    }
                }

                $response = $responseData;
            } else {
                $response['error'] = 'No data found for metadata_id: ' . $metadataId;
            }
        } else {
            $response['error'] = 'metadata_id parameter is missing';
        }
    }
} else {
    $response['error'] = 'student_id parameter is missing';
}

echo json_encode($response);
?>
