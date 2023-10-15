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
        // Handle the database error here, e.g., log the error, return an error response, etc.
        return ['error' => 'Database error: ' . $e->getMessage()];
    }
}

function fetchMetadataCategories($schoolID) {
    global $connection;
    try {
        $stmt = $connection->prepare("SELECT metadata_id, category_name FROM Metadata WHERE SchoolID = ?");
        $stmt->execute([$schoolID]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Handle the database error here, e.g., log the error, return an error response, etc.
        return ['error' => 'Database error: ' . $e->getMessage()];
    }
}

function fetchSchoolIdForStudent($studentId) {
    global $connection;
    $stmt = $connection->prepare("SELECT SchoolID FROM Students WHERE student_id = ?");
    $stmt->execute([$studentId]);
    $result = $stmt->fetch();
    return $result ? $result['SchoolID'] : null;
}

function fetchColumnHeaders($metadataId) {
    global $connection;
    $columnHeaders = [];

    try {
        $stmt = $connection->prepare("SELECT score1_name, score2_name, score3_name, score4_name, score5_name, score6_name, score7_name, score8_name, score9_name, score10_name FROM Metadata WHERE metadata_id = ?");
        $stmt->execute([$metadataId]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            for ($i = 1; $i <= 10; $i++) {
                $columnHeaders["score" . $i] = $row["score" . $i . "_name"];
            }
        }
    } catch (PDOException $e) {
        // Handle the database error here, e.g., log the error, return an error response, etc.
        return ['error' => 'Database error: ' . $e->getMessage()];
    }

    return $columnHeaders;
}

if (isset($_GET['action'])) {
    $action = $_GET['action'];

    if ($action === 'fetchPerformanceData' && isset($_GET['student_id']) && isset($_GET['metadata_id'])) {
        $studentId = $_GET['student_id'];
        $metadataId = $_GET['metadata_id'];

        $performanceData = fetchPerformanceData($studentId, $metadataId);
        $columnHeaders = fetchColumnHeaders($metadataId);

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

        echo json_encode($responseData);
    } elseif ($action === 'fetchMetadataCategories' && isset($_GET['school_id'])) {
        $schoolID = $_GET['school_id'];
        $metadataEntries = fetchMetadataCategories($schoolID);
        echo json_encode($metadataEntries);
    } else {
        echo json_encode(['error' => 'Invalid request']);
    }
} else {
    echo json_encode(['error' => 'Action parameter is missing']);
}
?>
