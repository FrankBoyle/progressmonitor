<?php
include('auth_session.php');
// Error Reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('db.php');

function fetchPerformanceData($studentId, $metadata_id) {
    global $connection;
    $stmt = $connection->prepare("SELECT * FROM Performance WHERE student_id = ? AND metadata_id = ? ORDER BY score_date DESC LIMIT 41");
    $stmt->execute([$studentId, $metadata_id]);
    return $stmt->fetchAll();
}

function fetchStudentsByTeacher($teacherId) {
    global $connection;
    $stmt = $connection->prepare("SELECT s.* FROM Students s INNER JOIN Teachers t ON s.school_id = t.school_id WHERE t.teacher_id = ?");
    $stmt->execute([$teacherId]);
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
$studentId = $_GET['student_id'];
//$metadata_id = $_POST['metadata_id']; // Get metadata_id from POST
//$schoolId = $_POST['school_id']; // Get school_id from POST
//$scores = $_POST['scores'];
$metadata_id = $_GET['metadata_id'];
// Check if the action is set to 'fetchGroups' and handle it
if (isset($_GET['action']) && $_GET['action'] == 'fetchGroups') {
    echo json_encode(fetchGroupNames());
    exit;
}

// If student_id is not set, exit early
if (!isset($_GET['student_id'])) {
    return;
}

if (isset($_GET['student_id'], $_GET['metadata_id'])) {
    $studentId = $_GET['student_id'];
    $metadataId = $_GET['metadata_id'];
    $schoolId = fetchSchoolIdForStudent($studentId); // Assuming you have this function as shown in your script

    // Fetch the goals
    $goals = fetchGoals($studentId, $metadataId, $schoolId);
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

$students = fetchStudentsByTeacher($teacherId);
// Fetch performance data and score names
$performanceData = fetchPerformanceData($studentId, $metadata_id);
$scoreNames = fetchScoreNames($school_id, $metadata_id);

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

