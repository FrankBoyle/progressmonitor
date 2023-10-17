<?php
session_start();
include('db.php');
include('functions.php');

// Error Reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if 'SchoolID' is set in the session before using it.
if (isset($_SESSION['SchoolID'])) {
    // It's important to use the same case as you used when you set the session variable.
    // 'SchoolID' is different from 'schoolID' or 'schoolId'.
    $schoolID = $_SESSION['SchoolID'];
} else {
    // Handle the case where 'SchoolID' is not set in the session.
    // Depending on your application's logic, this might involve redirecting the user,
    // showing an error message, or setting a default value for testing.
    echo "Error: SchoolID is not set in the session.";
    exit(); // Stop the script, or handle this situation differently as per your requirements.
}

// Checking and setting the $studentId
if (isset($_GET['student_id'])) {
    $studentId = $_GET['student_id'];
} else {
    $studentId = null; // or set a default value appropriate for your context
}

// You can create a function to fetch student IDs by SchoolID, e.g., fetchStudentIdsBySchool
$studentIds = fetchStudentIdsBySchool($connection, $schoolID);

// Similarly, you can create a function to fetch metadata IDs by SchoolID, e.g., fetchMetadataIdsBySchool
$metadataIds = fetchMetadataIdsBySchool($connection, $schoolID);
// Initialize empty arrays and variables

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
$performanceData = [];
$scoreNames = [];
$chartDates = [];
$defaultMetadataID = 1; // Default value in case of any issues
$metadataID = null;

// Initialize $metadataID to null to check later if it was set

try {
    $stmt = $connection->prepare("SELECT MIN(metadata_id) AS min_metadata_id FROM Metadata WHERE SchoolID = ?");
    $stmt->execute([$schoolID]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row && $row['min_metadata_id'] !== null) {
        $metadataID = $row['min_metadata_id'];
    } else {
        throw new Exception("No metadata records found for the specified school.");
    }
} catch (PDOException $e) {
    // Catch and handle any PDO-specific exceptions
    echo "Database error: " . $e->getMessage();
    exit();
} catch (Exception $e) {
    // Catch and handle general exceptions
    echo "Error: " . $e->getMessage();
    exit();
}

// Add code to fetch column names based on the selected metadata_id
if (isset($_GET['metadataId'])) {
    $metadataID = $_GET['metadataId'];

    // Fetch column names based on $metadataID (You need to implement this function)
    $columnNames = fetchColumnNamesByMetadataID($connection, $metadataID);

    if ($columnNames !== false) {
        // Return the column names as JSON
        echo json_encode(['columnHeaders' => $columnNames]);
        exit;
    }
}



// Fetch metadata entries from the Metadata table for the specified SchoolID and metadata_id
$stmt = $connection->prepare("SELECT * FROM Metadata WHERE SchoolID = ? AND metadata_id = ?");
$stmt->execute([$schoolID, $metadataID]);

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
    // Handle the case where no metadata entry is found for the specified SchoolID and metadata_id
    echo "Metadata entry not found.";
    exit;
}

// Fetch performance data and score names based on the metadata
$performanceData = fetchPerformanceData($connection, $studentId);
$scoreNames = fetchScoreNames($connection, $schoolID);

// Preparing the data for the chart
foreach ($performanceData as $record) {
    $chartDates[] = $record['score_date'];
    // You can add more logic here if needed
}

// Fetch metadata entries from the Metadata table for the specified SchoolID
$stmt = $connection->prepare("SELECT metadata_id, category_name FROM Metadata WHERE SchoolID = ?");
$stmt->execute([$schoolID]);
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $metadataEntries[] = $row;
}
?>