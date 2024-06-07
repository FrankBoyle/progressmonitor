<?php
session_start();
include('auth_session.php');
// Error Reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('db.php');

function fetchPerformanceData($studentId, $metadata_id, $iep_date = null) {
    global $connection;
    $query = "SELECT * FROM Performance WHERE student_id_new = ? AND metadata_id = ? ";
    $query .= $iep_date ? "AND score_date >= ? " : "";
    $query .= "ORDER BY score_date ASC LIMIT 41";
    
    $stmt = $connection->prepare($query);
    $params = $iep_date ? [$studentId, $metadata_id, $iep_date] : [$studentId, $metadata_id];
    $stmt->execute($params);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function fetchStudentsByTeacher($teacherId) {
    global $connection;
    $stmt = $connection->prepare("SELECT s.* FROM Students_new s INNER JOIN Teachers t ON s.school_id = t.school_id WHERE t.teacher_id = ?");
    $stmt->execute([$teacherId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function fetchMetadataCategories($school_id) {
    global $connection;
    $stmt = $connection->prepare("SELECT metadata_id, category_name FROM Metadata WHERE school_id = ?");
    $stmt->execute([$school_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function fetchSchoolIdForStudent($studentId) {
    global $connection;
    $stmt = $connection->prepare("SELECT school_id FROM Students_new WHERE student_id_new = ?");
    $stmt->execute([$studentId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ? $result['school_id'] : null;
}

function fetchScoreNames($school_id, $metadata_id) {
    global $connection;
    $scoreNames = [];

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
    $stmt->execute([$school_id, $metadata_id]);

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $category = $row['category_name'] ?? 'default_category';
        for ($i = 1; $i <= 10; $i++) {
            $scoreColumnName = 'score' . $i . '_name';
            if (!empty($row[$scoreColumnName])) {
                $scoreNames[$category][] = $row[$scoreColumnName];
            }
        }
    }

    return $scoreNames;
}

function fetchIepDate($studentId) {
    global $connection;
    $stmt = $connection->prepare("SELECT IEP_Date FROM Students_new WHERE student_id_new = ?");
    $stmt->execute([$studentId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ? $result['IEP_Date'] : null;
}

function getSmallestMetadataId($schoolId) {
    global $connection;

    $stmt = $connection->prepare("SELECT MIN(metadata_id) AS smallest_metadata_id FROM Metadata WHERE school_id = :schoolId");
    $stmt->bindParam(':schoolId', $schoolId, PDO::PARAM_INT);
    $stmt->execute();

    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ? $result['smallest_metadata_id'] : null;
}

function fetchGoals($studentId, $metadataId, $schoolId) {
    global $connection;
    $stmt = $connection->prepare("
        SELECT g.goal_id, g.goal_description, gm.metadata_id, gm.category_name
        FROM Goals g
        INNER JOIN Metadata gm ON g.metadata_id = gm.metadata_id
        WHERE g.student_id_new = ? AND g.metadata_id = ? AND g.school_id = ? AND g.archived = 0
    ");
    $stmt->execute([$studentId, $metadataId, $schoolId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Initialize empty arrays and variables
$performanceData = [];
$scoreNames = [];
$chartDates = [];
$chartScores = [];
$goals = [];
$metadataEntries = [];
$message = "";  // Initialize an empty message variable

// Check if student_id and metadata_id are set
if (!isset($_GET['student_id']) || !isset($_GET['metadata_id'])) {
    die("Student ID and Metadata ID are required.");
}

/* Ensure that $student_id is defined
if (!isset($studentId)) {
    die("Error: Student ID is not set");
}
*/

// Debugging information
echo "Student ID: " . $studentId;

$studentId = $_GET['student_id'];
$metadata_id = $_GET['metadata_id'];

// Fetch the stored IEP date
$iep_date = fetchIepDate($studentId);

// Fetch school_id for the student
$school_id = fetchSchoolIdForStudent($studentId);  

if (!$school_id) {
    die("School ID not found for the student.");
}

// Ensure teacher ID is set in session
if (!isset($_SESSION['teacher_id'])) {
    die("Teacher ID not set in session");
}

$teacherId = $_SESSION['teacher_id'];

// Fetch students, performance data, and score names
$students = fetchStudentsByTeacher($teacherId);
$performanceData = fetchPerformanceData($studentId, $metadata_id, $iep_date);
$scoreNames = fetchScoreNames($school_id, $metadata_id);

// Preparing the data for the chart
foreach ($performanceData as $record) {
    $chartDates[] = $record['score_date'];
}

// Fetch metadata entries from the Metadata table for the specified school_id
$metadataEntries = fetchMetadataCategories($school_id);

// Fetch the goals
$goals = fetchGoals($studentId, $metadata_id, $school_id);

// Checking and setting the $studentName
$studentName = null;
foreach ($students as $student) {
    if ($student['student_id_new'] == $studentId) {
        $studentName = $student['first_name'] . ' ' . $student['last_name'];
        break;
    }
}

if ($studentName === null) {
    die("Student not found.");
}


?>
