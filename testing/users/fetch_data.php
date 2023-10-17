<?php
session_start();
require_once 'db.php';
require_once 'functions.php';

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

// You can create a function to fetch student IDs by SchoolID, e.g., fetchStudentIdsBySchool
$studentIds = fetchStudentIdsBySchool($connection, $schoolID); 

// Similarly, you can create a function to fetch metadata IDs by SchoolID, e.g., fetchMetadataIdsBySchool
$metadataIds = fetchMetadataIdsBySchool($connection, $schoolID); 
// Initialize empty arrays and variables
$metadataEntries = [];
$displayedColumns = [];
$performanceData = [];
$scoreNames = [];
$chartDates = [];
$defaultMetadataID = 1; // Default value in case of any issues

// If 'metadata_id' is present, use it.
if (isset($_GET['metadata_id'])) {
    $metadataId = $_GET['metadata_id'];
} else {
    // If 'metadata_id' is not required at this stage, you can comment out or remove the error message and 'exit' call.
    // Instead, set a default value or adjust the script's behavior to work without this parameter.
    // echo "Error: metadata_id parameter is missing in the URL.";
    // exit(); // Comment out or remove this line.
    // Optional: Set a default value if it makes sense for your script's logic.
    // $metadataId = 'default_value'; 
}

$schoolID = fetchSchoolIdForStudent($connection, $studentId);

echo "schoolID: $schoolID<br>";
echo "metadataID: $metadataID<br>";

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
        'score_date' => 'Date', // You can customize this label
    ];
} else {
    // Handle the case where no metadata entry is found for the specified SchoolID and metadata_id
    echo "Metadata entry not found.";
    exit;
}

// Query to find the lowest metadata_id for the specified SchoolID
$stmt = $connection->prepare("SELECT MIN(metadata_id) AS min_metadata_id FROM Metadata WHERE SchoolID = ?");
$stmt->execute([$schoolID]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$defaultMetadataID = $row['min_metadata_id'] ?? 1;

// Fetch performance data and score names based on the metadata
$performanceData = fetchPerformanceData($studentId);
$scoreNames = fetchScoreNames($schoolID);

// Preparing the data for the chart
foreach ($performanceData as $record) {
    $chartDates[] = $record['score_date'];
    // You can add more logic here if needed
}

// Handling the data POST from the dropdown functionality
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ScoreGroup'])) {
    $schoolIDIndex = $_POST['SchoolIDIndex'];
    $originalName = $_POST['ScoreColumn'];
    $customName = $_POST['CustomName'];
    $scoreGroup = $_POST['ScoreGroup'];

    // Inserting into the SchoolScoreNames table
    $stmt = $connection->prepare("INSERT INTO SchoolScoreNames (SchoolIDIndex, ScoreColumn, CustomName, group_name) VALUES (?, ?, ?, ?)");
    $stmt->execute([$schoolIDIndex, $originalName, $customName, $scoreGroup]);
    
    // Respond with the ID of the inserted row
    echo json_encode(['id' => $connection->lastInsertId()]);
    exit;
}

// Fetch metadata entries from the Metadata table for the specified SchoolID
$stmt = $connection->prepare("SELECT metadata_id, category_name FROM Metadata WHERE SchoolID = ?");
$stmt->execute([$schoolID]);
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $metadataEntries[] = $row;
}

?>
