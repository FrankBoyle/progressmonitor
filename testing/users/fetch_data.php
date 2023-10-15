<?php
// Error Reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('./users/db.php');

function fetchPerformanceData($studentId) {
    global $connection;
    $stmt = $connection->prepare("SELECT * FROM Performance WHERE student_id = ? ORDER BY score_date DESC LIMIT 41");
    $stmt->execute([$studentId]);
    return $stmt->fetchAll();
}

function fetchMetadataCategories($schoolID) {
    global $connection;
    $stmt = $connection->prepare("SELECT metadata_id, category_name FROM Metadata WHERE SchoolID = ?");
    $stmt->execute([$schoolID]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function fetchSchoolIdForStudent($studentId) {
    global $connection;
    $stmt = $connection->prepare("SELECT SchoolID FROM Students WHERE student_id = ?");
    $stmt->execute([$studentId]);
    $result = $stmt->fetch();
    return $result ? $result['SchoolID'] : null;
}

function fetchStudentsByTeacher($teacherId) {
    global $connection;
    $stmt = $connection->prepare("SELECT s.* FROM Students s INNER JOIN Teachers t ON s.SchoolID = t.SchoolID WHERE t.teacher_id = ?");
    $stmt->execute([$teacherId]);
    return $stmt->fetchAll();
}

function addNewStudent($studentName, $teacherId) {
    global $connection;

    // Fetch the SchoolID of the current teacher
    $stmt = $connection->prepare("SELECT SchoolID FROM Teachers WHERE teacher_id = ?");
    $stmt->execute([$teacherId]);
    $teacherInfo = $stmt->fetch();
    $teacherSchoolID = $teacherInfo['SchoolID'];

    // Check if the student with the same name and SchoolID already exists
    $stmt = $connection->prepare("SELECT student_id FROM Students WHERE name = ? AND SchoolID = ?");
    $stmt->execute([$studentName, $teacherSchoolID]);
    $duplicateStudent = $stmt->fetch();

    if ($duplicateStudent) {
        return "Student with the same name already exists.";
    } 

    // Insert the new student with the same SchoolID
    $stmt = $connection->prepare("INSERT INTO Students (name, SchoolID) VALUES (?, ?)");
    $stmt->execute([$studentName, $teacherSchoolID]);
    return "New student added successfully.";
}

function fetchScoreNamesBasedOnMetadata($metadataId) {
    global $connection;
    
    // Initialize an array to store the score names
    $scoreNames = [];

    // Prepare and execute a query to fetch score names based on metadata_id
    $stmt = $connection->prepare("SELECT score1_name, score2_name, score3_name, score4_name, score5_name, score6_name, score7_name, score8_name, score9_name, score10_name FROM Metadata WHERE metadata_id = ?");
    $stmt->execute([$metadataId]);

    // Fetch the score names and populate the $scoreNames array
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    for ($i = 1; $i <= 10; $i++) {
        $scoreNames["score" . $i] = $row["score" . $i . "_name"];
    }

    return $scoreNames;
}

// Initialize empty arrays and variables
$performanceData = [];
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
$schoolID = fetchSchoolIdForStudent($studentId);  // Fetch SchoolID

if (!$schoolID) {
    return;  // If there's no SchoolID, exit early
}

// Fetch performance data and score names
$performanceData = fetchPerformanceData($studentId);

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

if (isset($_GET['metadata_id'])) {
    $metadataId = $_GET['metadata_id'];

    // Fetch score names based on the selected metadata_id from the Metadata table
    $stmt = $connection->prepare("SELECT score1_name, score2_name, score3_name, score4_name, score5_name, score6_name, score7_name, score8_name, score9_name, score10_name FROM Metadata WHERE metadata_id = ?");
    $stmt->execute([$metadataId]);

    // Fetch the score names and return them as JSON
    $scoreNames = [];
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    // Add each score name to the $scoreNames array
    for ($i = 1; $i <= 10; $i++) {
        $scoreNames["score" . $i] = $row["score" . $i . "_name"];
    }

    echo json_encode($scoreNames);
} else {
    // Handle the case where metadata_id is not provided
    echo json_encode(['error' => 'metadata_id parameter is missing']);
}

?>


