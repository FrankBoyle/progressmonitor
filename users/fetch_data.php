<?php
include('auth_session.php');
// Error Reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('db.php');

function fetchPerformanceData($studentId, $metadata_id, $iep_date = null) {
    global $connection;
    if ($iep_date) {
        $stmt = $connection->prepare("SELECT * FROM Performance WHERE student_id = ? AND metadata_id = ? AND score_date >= ? ORDER BY score_date ASC LIMIT 41");
        $stmt->execute([$studentId, $metadata_id, $iep_date]);
    } else {
        $stmt = $connection->prepare("SELECT * FROM Performance WHERE student_id = ? AND metadata_id = ? ORDER BY score_date ASC LIMIT 41");
        $stmt->execute([$studentId, $metadata_id]);
    }
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function fetchStudentsByTeacher($teacherId) {
    global $connection;
    $stmt = $connection->prepare("SELECT s.* FROM Students s INNER JOIN Teachers t ON s.school_id = t.school_id WHERE t.teacher_id = ?");
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
    $stmt = $connection->prepare("SELECT school_id FROM Students WHERE student_id = ?");
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
    $stmt = $connection->prepare("SELECT IEP_Date FROM Students WHERE student_id = ?");
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
    $stmt = $connection->prepare("SELECT * FROM Goals WHERE student_id = ? AND metadata_id = ? AND school_id = ? ORDER BY goal_date DESC");
    $stmt->execute([$studentId, $metadataId, $schoolId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Initialize empty arrays and variables
$performanceData = [];
$scoreNames = [];
$chartDates = [];
$chartScores = [];

if (!isset($_GET['student_id']) || !isset($_GET['metadata_id'])) {
    return;
}

$studentId = $_GET['student_id'];
$metadataId = $_GET['metadata_id'];

// Fetch the stored IEP date
$iep_date = fetchIepDate($studentId);

$schoolId = fetchSchoolIdForStudent($studentId);

if (!$schoolId) {
    return;
}

if (!isset($_SESSION['teacher_id'])) {
    die("Teacher ID not set in session");
}

$teacherId = $_SESSION['teacher_id'];
$message = "";  // Initialize an empty message variable

$students = fetchStudentsByTeacher($teacherId);
$performanceData = fetchPerformanceData($studentId, $metadataId, $iep_date);
$scoreNames = fetchScoreNames($schoolId, $metadataId);

// Preparing the data for the chart
foreach ($performanceData as $record) {
    $chartDates[] = $record['score_date'];
}

$metadataEntries = fetchMetadataCategories($schoolId);

// Output the links to tables for each metadata entry
foreach ($metadataEntries as $metadataEntry) {
    $metadata_id = $metadataEntry['metadata_id'];
    $categoryName = $metadataEntry['category_name'];
    // Generate a link to the table for this metadata entry
}
?>