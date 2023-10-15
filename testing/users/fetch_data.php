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
function fetchStudentsByTeacher($teacherId) {
    global $connection;
    $stmt = $connection->prepare("SELECT s.* FROM Students s INNER JOIN Teachers t ON s.SchoolID = t.SchoolID WHERE t.teacher_id = ?");
    $stmt->execute([$teacherId]);
    return $stmt->fetchAll();
}
function fetchColumnHeaders($metadataId) {
    global $connection;

    // Initialize an array to store the column headers
    $columnHeaders = [];

    // Prepare and execute a query to fetch score names based on metadataId
    $stmt = $connection->prepare("SELECT score1_name, score2_name, score3_name, score4_name, score5_name, score6_name, score7_name, score8_name, score9_name, score10_name FROM Metadata WHERE metadata_id = ?");
    
    if (!$stmt) {
        // Handle the query preparation error here
        die('Error preparing query: ' . $connection->error);
    }
    
    $stmt->execute([$metadataId]);

    if (!$stmt) {
        // Handle the query execution error here
        die('Error executing query: ' . $connection->error);
    }

    // Fetch the column headers and populate the $columnHeaders array
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        // Handle the case where no data is returned
        die('No data found for metadata_id: ' . $metadataId);
    }

    for ($i = 1; $i <= 10; $i++) {
        $columnHeaders["score" . $i] = $row["score" . $i . "_name"];
    }

    return $columnHeaders;
}

$response = [];
$metadataId = isset($_GET['metadata_id']) ? $_GET['metadata_id'] : null; // Initialize metadataId

if (isset($_GET['student_id']) && isset($_GET['metadata_id'])) {
    $studentId = $_GET['student_id'];
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

    $response = $responseData; // Set the response array to the constructed data
} else {
    $response['error'] = 'metadata_id parameter is missing'; // Handle the case when parameters are missing
}

echo json_encode($response); // Send the response as JSON

// Fetch the column headers based on the selected metadataId
$columnHeaders = fetchColumnHeaders($metadataId);

// Construct the data to send to the client
$responseData = [
    'columnHeaders' => $columnHeaders,
    'performanceData' => $performanceData,
];

// ...

echo json_encode($responseData); // Send the response as JSON

?>
