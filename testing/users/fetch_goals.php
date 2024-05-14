<?php
include('auth_session.php');
include('db.php');

// Error Reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Function to fetch the school ID for a student
function fetchSchoolIdForStudent($studentId) {
    global $connection;
    $stmt = $connection->prepare("SELECT school_id FROM Students_new WHERE student_id_new = ?");
    $stmt->execute([$studentId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ? $result['school_id'] : null;
}

// Fetch goals for a specific student
function fetchGoals($studentId, $metadataId, $schoolId) {
    global $connection;
    $stmt = $connection->prepare("SELECT * FROM Goals WHERE student_id = ? AND metadata_id = ? AND school_id = ? ORDER BY goal_date DESC");
    $stmt->execute([$studentId, $metadataId, $schoolId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// If student_id and metadata_id are not set, exit early
if (!isset($_GET['student_id']) || !isset($_GET['metadata_id'])) {
    echo json_encode([]);
    exit;
}

$studentId = $_GET['student_id'];
$metadataId = $_GET['metadata_id'];
$schoolId = fetchSchoolIdForStudent($studentId);

if (!$schoolId) {
    echo json_encode([]);
    exit;
}

$goals = fetchGoals($studentId, $metadataId, $schoolId);

echo json_encode($goals);
?>
