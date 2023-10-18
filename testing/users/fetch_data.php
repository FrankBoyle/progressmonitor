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

// Checking and setting the $studentId
if (isset($_GET['student_id'])) {
    $studentId = $_GET['student_id'];
} else {
    $studentId = null; // or set a default value appropriate for your context
}

// You can create a function to fetch student IDs by school_id, e.g., fetchStudentIdsBySchool
$studentIds = fetchStudentIdsBySchool($connection, $school_id);

// Similarly, you can create a function to fetch metadata IDs by school_id, e.g., fetchMetadataIdsBySchool
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
// Check if there is performance data for the specified student
if (!$performanceData) {
    // Handle the case where no performance data is available
    echo "No performance data found for the specified student.";
    exit;
}

// Check if there are score names available
if (!$scoreNames) {
    // Handle the case where no score names are available
    echo "No score names found.";
    exit;
}

$metadataEntries = [];
$displayedColumns = [];
$performanceData = [];
$scoreNames = [];
$chartDates = [];
$defaultMetadataID = 1; // Default value in case of any issues
$metadataID = null;
$columnNames = [];

// Add more specific error messages
if (!$columnNames) {
    // Handle the case where fetching column names fails
    $columnNames = []; // Set a default value or handle the error
    echo "Failed to fetch column names.";
}

// Initialize $metadataID to null to check later if it was set

try {
    $stmt = $connection->prepare("SELECT MIN(metadata_id) AS min_metadata_id FROM Metadata WHERE school_id = ?");
    $stmt->execute([$school_id]);
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


try {
    // Check if the action is "fetchDefaultMetadataId" and return the default metadata ID
    if (isset($_GET['action']) && $_GET['action'] === 'fetchDefaultMetadataId') {
        $stmt = $connection->prepare("SELECT MIN(metadata_id) AS min_metadata_id FROM Metadata WHERE school_id = ?");
        $stmt->execute([$school_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row && $row['min_metadata_id'] !== null) {
            $metadataId = $row['min_metadata_id'];
            echo json_encode(['metadataId' => $metadataId]);
        } else {
            echo json_encode(['metadataId' => null]); // Return null for no records found
        }

        exit(); // Make sure to exit to prevent further output
    }

    // Add code to fetch column names based on the selected metadata_id
    if (isset($_GET['metadataId'])) {
        $metadataID = $_GET['metadataId'];

        // Fetch column names based on $metadataID (You need to implement this function)
        $columnNames = fetchColumnNamesByMetadataID($connection, $metadataID);

        if ($columnNames === false) {
            // Handle the case where fetching column names fails
            // You might want to set a default value or handle the error differently
            $columnNames = []; // Set a default value or handle the error
        }
    }

    // If everything is successful, return the response as JSON
    echo json_encode(['columnHeaders' => $columnNames, 'performanceData' => $performanceData]);
} catch (Exception $e) {
    // Handle exceptions and send an error response
    http_response_code(500); // Set HTTP status code to 500 Internal Server Error
    echo json_encode(['error' => 'An error occurred: ' . $e->getMessage()]);
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
    echo "Metadata entry not found.";
    exit;
}

// Fetch performance data and score names based on the metadata
$performanceData = fetchPerformanceData($connection, $studentId);
$scoreNames = fetchScoreNames($connection, $school_id);

// Preparing the data for the chart
foreach ($performanceData as $record) {
    $chartDates[] = $record['score_date'];
    // You can add more logic here if needed
}

// Fetch metadata entries from the Metadata table for the specified school_id
$stmt = $connection->prepare("SELECT metadata_id, category_name FROM Metadata WHERE school_id = ?");
$stmt->execute([$school_id]);
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $metadataEntries[] = $row;
}
?>