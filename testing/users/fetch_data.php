<?php
session_start();
// Error Reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('db.php');

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

function fetchScoreNames($school_id) {
    global $connection;
    $scoreNames = [];
    $stmt = $connection->prepare("SELECT ScoreColumn, CustomName FROM SchoolScoreNames WHERE school_id = ?");
    $stmt->execute([$school_id]);
    while ($row = $stmt->fetch()) {
        $scoreNames[$row['ScoreColumn']] = $row['CustomName'];
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

// If student_id is not set, exit early
if (!isset($_GET['student_id'])) {
    return;
}

$studentId = $_GET['student_id'];
$school_id = fetchSchoolIdForStudent($studentId);  // Fetch school_id

if (!$school_id) {
    return;  // If there's no school_id, exit early
}


if (!isset($_SESSION['teacher_id'])) {
    die("Teacher ID not set in session");
}

$teacherId = $_SESSION['teacher_id'];
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
$scoreNames = fetchScoreNames($school_id);

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
    $metadataId = $metadataEntry['metadata_id'];
    $categoryName = $metadataEntry['category_name'];
    // Generate a link to the table for this metadata entry
}

$stmt = $connection->prepare("SELECT * FROM Performance WHERE student_id = ? AND metadata_id = ? ORDER BY score_date DESC LIMIT 41");
$stmt->execute([$studentId, $metadataId]);


?>

