<?php
session_start();
include('db.php');
include('functions.php');

// Check if 'school_id' is set in the session before using it.
if (isset($_SESSION['school_id'])) {
    $school_id = $_SESSION['school_id'];
} else {
    echo json_encode(['error' => 'school_id is not set in the session']);
    exit();
}

// Checking and setting the $student_id
if (isset($_GET['student_id'])) {
    $student_id = $_GET['student_id'];
} else {
    $student_id = null;
}

// Get the metadata_id from the URL parameter
if (isset($_GET['metadata_id'])) {
    $metadataID = $_GET['metadata_id'];
} else {
    echo json_encode(['error' => 'metadata_id is missing']);
    exit();
}

// Fetch performance data based on school_id, student_id, and metadata_id
$performanceData = fetchPerformanceData($connection, $student_id, $metadataID);

// Return the data as JSON
echo json_encode(['performanceData' => $performanceData]);
?>