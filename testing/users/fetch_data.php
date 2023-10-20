<?php
session_start();
// Error Reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('db.php');

// Example of initializing variables with default values
$teacherId = null;
$studentId = null;
$school_id = null;

// Rest of your code...

function fetchPerformanceData($studentId) {
    global $connection;
    $stmt = $connection->prepare("SELECT * FROM Performance WHERE student_id = ? ORDER BY score_date DESC LIMIT 41");
    $stmt->execute([$studentId]);
    return $stmt->fetchAll();
}

function fetchMetadataCategories($school_id) {
    global $connection;
    $stmt = $connection->prepare("SELECT metadata_id, category_name FROM Metadata WHERE school_id = ?");
    $stmt->execute([$school_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function fetchSchoolIdForStudent($studentId) {
    global $connection;
    $stmt = $connection->prepare("SELECT school_id FROM Students WHERE student_id = ?");
    $stmt->execute([$studentId]);
    $result = $stmt->fetch();
    return $result ? $result['school_id'] : null;
}

function fetchScoreNames($school_id, $metadata_id) {
    global $connection;
    $scoreNames = [];

    // Fetch column names based on school_id and metadata_id
    $stmt = $connection->prepare("
        SELECT 
            score1_name, score2_name, score3_name, score4_name, 
            score5_name, score6_name, score7_name, score8_name, 
            score9_name, score10_name
        FROM 
            Metadata
        WHERE 
            school_id = ? AND metadata_id = ?
    ");

    $stmt->execute([$school_id, $metadata_id]);

    if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Populate the scoreNames array with column names
        $scoreNames = [
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
        ];
    }

    return $scoreNames;
}


function fetchStudentsByTeacher($teacherId) {
    global $connection;
    $stmt = $connection->prepare("SELECT s.* FROM Students s INNER JOIN Teachers t ON s.school_id = t.school_id WHERE t.teacher_id = ?");
    $stmt->execute([$teacherId]);
    return $stmt->fetchAll();
}

function addNewStudent($studentName, $teacherId) {
    global $connection;

    // Fetch the school_id of the current teacher
    $stmt = $connection->prepare("SELECT school_id FROM Teachers WHERE teacher_id = ?");
    $stmt->execute([$teacherId]);
    $teacherInfo = $stmt->fetch();
    $teacherschool_id = $teacherInfo['school_id'];

    // Check if the student with the same name and school_id already exists
    $stmt = $connection->prepare("SELECT student_id FROM Students WHERE name = ? AND school_id = ?");
    $stmt->execute([$studentName, $teacherschool_id]);
    $duplicateStudent = $stmt->fetch();

    if ($duplicateStudent) {
        return "Student with the same name already exists.";
    } 

    // Insert the new student with the same school_id
    $stmt = $connection->prepare("INSERT INTO Students (name, school_id) VALUES (?, ?)");
    $stmt->execute([$studentName, $teacherschool_id]);
    return "New student added successfully.";
}

function fetchGroupNames() {
    global $connection;
    $stmt = $connection->prepare("SELECT group_name FROM ScoreGroups");
    $stmt->execute();
    $stmt->bindColumn(1, $groupName);
    
    $groups = [];
    while ($stmt->fetch(PDO::FETCH_BOUND)) {
        $groups[] = $groupName;
    }
    
    return $groups;
}

function getSmallestMetadataId($schoolId) {
    global $connection;

    // Prepare and execute a query to fetch the smallest metadata_id
    $query = "SELECT MIN(metadata_id) AS smallest_metadata_id FROM Metadata WHERE school_id = :schoolId";
    $stmt = $connection->prepare($query);
    $stmt->bindParam(':schoolId', $schoolId, PDO::PARAM_INT);
    $stmt->execute();

    // Fetch the result
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if a result was found
    if ($result && isset($result['smallest_metadata_id'])) {
        return $result['smallest_metadata_id'];
    } else {
        return null; // No matching records found
    }
}
// Initialize empty arrays and variables
$performanceData = [];
$scoreNames = [];
$chartDates = [];
$chartScores = [];

// Check if the action is set to 'fetchGroups' and handle it
if (isset($_GET['action']) && $_GET['action'] == 'fetchGroups') {
    echo json_encode(fetchGroupNames());
    exit;
}

$students = fetchStudentsByTeacher($teacherId);

if (isset($_GET['metadata_id'])) {
    $metadata_id = $_GET['metadata_id']; // Assign a value if it's set
} else {
    // Handle the case where metadata_id is not set
}

// Fetch performance data and score names using the modified function
$performanceData = fetchPerformanceData($studentId);
$scoreNames = fetchScoreNames($school_id, $metadata_id);

// If student_id is not set, exit early
if (!isset($_GET['student_id'])) {
    return;
}

$studentId = $_GET['student_id']; // Initialize $studentId
//$metadata_id = $_POST['metadata_id']; // Initialize $metadata_id
$schoolId = $_SESSION['school_id']; // Initialize $schoolId

if (!$school_id) {
    return;  // If there's no school_id, exit early
}

if (isset($_SESSION['teacher_id'])) {
    $teacherId = $_SESSION['teacher_id']; // Assign a value to $teacherId
}

$message = "";  // Initialize an empty message variable

// Handle form submission for adding new student
if (isset($_POST['add_new_student'])) {
    $newStudentName = $_POST['new_student_name'];
    if (!empty($newStudentName)) {
        $message = addNewStudent($newStudentName, $teacherId);
    }
}

$students = fetchStudentsByTeacher($teacherId);
// Fetch performance data and score names
$performanceData = fetchPerformanceData($studentId);
$scoreNames = fetchScoreNames($school_id, $metadata_id);

// Preparing the data for the chart
foreach ($performanceData as $record) {
    $chartDates[] = $record['score_date'];
    // You can add more logic here if needed
}

// Handling the data POST from the dropdown functionality
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ScoreGroup'])) {
    $school_idIndex = $_POST['school_idIndex'];
    $originalName = $_POST['ScoreColumn'];
    $customName = $_POST['CustomName'];
    $scoreGroup = $_POST['ScoreGroup'];

    // Inserting into the SchoolScoreNames table
    $stmt = $connection->prepare("INSERT INTO SchoolScoreNames (school_idIndex, ScoreColumn, CustomName, group_name) VALUES (?, ?, ?, ?)");
    $stmt->execute([$school_idIndex, $originalName, $customName, $scoreGroup]);
    
    // Respond with the ID of the inserted row
    echo json_encode(['id' => $connection->lastInsertId()]);
    exit;
}

// Fetch metadata entries from the Metadata table for the specified school_id
$stmt = $connection->prepare("SELECT metadata_id, category_name FROM Metadata WHERE school_id = ?");
$stmt->execute([$school_id]);
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $metadataEntries[] = $row;
}

// Checking and setting the $student_id
if (isset($_GET['student_id'])) {
    $student_id = $_GET['student_id'];
} else {
    $student_id = null; // or set a default value appropriate for your context
}

// Output the links to tables for each metadata entry
foreach ($metadataEntries as $metadataEntry) {
    $metadata_id = $metadataEntry['metadata_id'];
    $categoryName = $metadataEntry['category_name'];
    // Generate a link to the table for this metadata entry
}

$stmt = $connection->prepare("SELECT * FROM Performance WHERE student_id = ? AND metadata_id = ? ORDER BY score_date DESC LIMIT 41");
$stmt->execute([$studentId, $metadata_id]);

?>

