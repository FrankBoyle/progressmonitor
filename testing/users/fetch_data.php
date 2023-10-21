<?php
session_start();
// Error Reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('db.php');

function fetchPerformanceData($student_id, $metadata_id) {
    global $connection;
    $stmt = $connection->prepare("SELECT * FROM Performance WHERE student_id = ? AND metadata_id = ? ORDER BY score_date DESC LIMIT 41");
    $stmt->execute([$student_id, $metadata_id]);
    return $stmt->fetchAll();
}

function fetchMetadataCategories($school_id) {
    global $connection;
    $stmt = $connection->prepare("SELECT metadata_id, category_name FROM Metadata WHERE school_id = ?");
    $stmt->execute([$school_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function fetchSchoolIdForStudent($student_id) {
    global $connection;
    $stmt = $connection->prepare("SELECT school_id FROM Students WHERE student_id = ?");
    $stmt->execute([$student_id]);
    $result = $stmt->fetch();
    return $result ? $result['school_id'] : null;
}

function fetchScoreNames($school_id, $metadata_id) {
    global $connection;
    $scoreNames = [];

    // Prepare the SQL statement. Make sure the names of the columns match exactly what's in your table.
    $stmt = $connection->prepare(
        "SELECT 
            category_name, 
            score1_name, 
            score2_name, 
            score3_name, 
            score4_name, 
            score5_name, 
            score6_name, 
            score7_name, 
            score8_name, 
            score9_name, 
            score10_name 
        FROM Metadata 
        WHERE school_id = ? AND metadata_id = ?"
    );

    // Bind parameters to the SQL statement and execute it, passing the school ID and metadata ID.
    $stmt->execute([$school_id, $metadata_id]);

    // Fetch the result row from the query. Since we're expecting potentially multiple rows, we'll iterate.
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // If there's a category name, use it as a key. Otherwise, you might want a default or a numerical index.
        $category = $row['category_name'] ?? 'default_category';

        // For each score column, check if it's non-empty and then add it to the array.
        // Here we're compiling all the scores into one flat array per category. If the category changes per row,
        // this structure might need to be adjusted depending on your requirements.
        for ($i = 1; $i <= 10; $i++) {
            $scoreColumnName = 'score' . $i . '_name';
            if (!empty($row[$scoreColumnName])) {
                $scoreNames[$category][] = $row[$scoreColumnName];
            }
        }
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

function getSmallestMetadataId($school_id) {
    global $connection;

    // Prepare and execute a query to fetch the smallest metadata_id
    $query = "SELECT MIN(metadata_id) AS smallest_metadata_id FROM Metadata WHERE school_id = :school_id";
    $stmt = $connection->prepare($query);
    $stmt->bindParam(':school_id', $school_id, PDO::PARAM_INT);
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
$student_id = $_GET['student_id'];
//$metadata_id = $_POST['metadata_id']; // Get metadata_id from POST
$school_id = $_SESSION['school_id'];
//$scores = $_POST['scores'];
$metadata_id = $_GET['metadata_id'];
//$scoreCategory = $_GET['scoreCategory'];
// Check if the action is set to 'fetchGroups' and handle it
if (isset($_GET['action']) && $_GET['action'] == 'fetchGroups') {
    echo json_encode(fetchGroupNames());
    exit;
}

// If student_id is not set, exit early
if (!isset($_GET['student_id'])) {
    return;
}

$school_id = fetchSchoolIdForStudent($student_id);  // Fetch school_id

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
$performanceData = fetchPerformanceData($student_id, $metadata_id);
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
$stmt->execute([$metadata_id. $school_id]);
// Check if school_id and metadata_id are provided in the GET request
if (!isset($_GET['school_id']) || !isset($_GET['metadata_id'])) {
    echo json_encode(['error' => 'school_id and metadata_id are required']);
    exit;
}

// Extract school_id and metadata_id from the GET request
$schoolId = $_GET['school_id'];
$metadataId = $_GET['metadata_id'];

// Query the Metadata table to get the category_name associated with metadata_id
$stmt = $connection->prepare("SELECT category_name FROM Metadata WHERE metadata_id = ? AND school_id = ?");
$stmt->execute([$metadataId, $schoolId]);
$categoryRow = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$categoryRow) {
    echo json_encode(['error' => 'Metadata not found']);
    exit;
}

// Extract the category_name
$categoryName = $categoryRow['category_name'];

// Query the Performance table to get performance data for the specified school_id and metadata_id
$stmt = $connection->prepare("SELECT score_date, score1, score2, score3, score4, score5, score6, score7, score8, score9, score10 FROM Performance WHERE student_id = ? AND metadata_id = ?");
$stmt->execute([$studentId, $metadataId]);
$performanceData = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Create an associative array to include both category_name and performance data
$responseData = [
    'category_name' => $categoryName,
    'performance_data' => $performanceData
];

// Return the combined data as JSON
echo json_encode($responseData);


$stmt = $connection->prepare("SELECT * FROM Performance WHERE student_id = ? AND metadata_id = ? ORDER BY score_date DESC LIMIT 41");
$stmt->execute([$student_id, $metadata_id]);

?>

