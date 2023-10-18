<?php
session_start();
include('db.php');
include('functions.php');

// Error Reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if 'school_id' is set in the session before using it.
if (isset($_SESSION['school_id'])) {
    // 'school_id' is different from 'school_id' or 'schoolId'.
    $school_id = $_SESSION['school_id'];
} else {
    // Handle the case where 'school_id' is not set in the session.
    echo "Error: school_id is not set in the session.";
    exit(); 
}

// Checking and setting the $student_id
if (isset($_GET['student_id'])) {
    $student_id = $_GET['student_id'];
} else {
    $student_id = null; // or set a default value appropriate for your context
}

$student_ids = fetchStudentIdsBySchool($connection, $school_id);
$metadataIds = fetchMetadataIdsBySchool($connection, $school_id);
$columnHeaders = [
    'score1_name',
    'score2_name',
    'score3_name',
    'score4_name',
    'score5_name',
    'score6_name',
    'score7_name',
    'score8_name',
    'score9_name',
    'score10_name'
];

$metadataEntries = [];
$displayedColumns = [];
$performanceData = []; // Initialize as an empty array
$scoreNames = [];
$chartDates = [];
$defaultMetadataID = 1; // Default value in case of any issues
$metadataID = null;
$columnNames = [];

try {
    $stmt = $connection->prepare("SELECT MIN(metadata_id) AS min_metadata_id FROM Metadata WHERE school_id = ?");
    $stmt->execute([$school_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row && $row['min_metadata_id'] !== null) {
        $metadataID = $row['min_metadata_id'];
    } else {
        echo json_encode(['error' => 'No metadata records found for the specified school']);
        exit();
    }
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    exit();
} catch (Exception $e) {
    echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
    exit();
}

// Fetch metadata entries from the Metadata table for the specified school_id
$stmt = $connection->prepare("SELECT metadata_id, category_name FROM Metadata WHERE school_id = ?");
$stmt->execute([$school_id]);
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $metadataEntries[] = $row;
}

if (isset($_GET['action']) && $_GET['action'] === 'fetchDefaultMetadataId') {
    $defaultMetadataId = fetchDefaultMetadataId($connection, $school_id);
    echo json_encode(['metadataId' => $defaultMetadataId]);
    exit();
}

// Check if the action is "fetchDefaultMetadataId" and return the default metadata ID
if (isset($_GET['action']) && $_GET['action'] === 'fetchDefaultMetadataId') {
    $defaultMetadataId = fetchDefaultMetadataId($connection, $school_id);
    echo json_encode(['metadataId' => $defaultMetadataId]);
    exit(); // Make sure to exit to prevent further output
}

// Fetch metadata entries from the Metadata table for the specified school_id and metadata_id
$stmt = $connection->prepare("SELECT * FROM Metadata WHERE school_id = ? AND metadata_id = ?");
$stmt->execute([$school_id, $metadataID]);

if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    // Populate the $displayedColumns array with column names from the metadata entry
    $displayedColumns = [
        'score1' => $row['score1_name'],
        'score2' => $row['score2_name'],
        'score3' => $row['score3_name'],
        'score4' => $row['score4_name'],
        'score5' => $row['score5_name'],
        'score6' => $row['score6_name'],
        'score7' => $row['score7_name'],
        'score8' => $row['score8_name'],
        'score9' => $row['score9_name'],
        'score10' => $row['score10_name'],
        //'score_date' => 'Date', // You can customize this label
    ];
} else {
    // Handle the case where no metadata entry is found for the specified school_id and metadata_id
    echo json_encode(['error' => 'Metadata entry not found']);
    exit();
}

// Fetch performance data and score names based on the metadata
$performanceData = fetchPerformanceData($connection, $student_id);
$scoreNames = fetchScoreNames($connection, $school_id);

// Preparing the data for the chart
foreach ($performanceData as $record) {
    $chartDates[] = $record['score_date'];
    // You can add more logic here if needed
}

$responseData = [
    'metadataId' => $metadataID,
    'displayedColumns' => $displayedColumns,
    'columnHeaders' => $columnHeaders, // Include columnHeaders in the response
    // Add other data you want to send to the client
];

// Fetch metadata entries from the Metadata table for the specified school_id
$stmt = $connection->prepare("SELECT metadata_id, category_name FROM Metadata WHERE school_id = ?");
$stmt->execute([$school_id]);
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $metadataEntries[] = $row;
}
//echo json_encode($responseData);

?>