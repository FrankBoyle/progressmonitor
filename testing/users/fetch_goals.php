<?php
include('auth_session.php');
include('db.php');

// Error Reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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
