<?php
// Error Reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('db.php');

class DatabaseOperations {
    private $connection;

    public function __construct($connection) {
        $this->connection = $connection;
    }
    
function fetchPerformanceDataByMetadata($studentId, $metadataId) {
    $this->$connection;
    $stmt = $connection->prepare("
        SELECT * FROM Performance 
        WHERE student_id = ? AND metadata_id = ? 
        ORDER BY score_date DESC LIMIT 41
    ");
    $stmt->execute([$studentId, $metadataId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function fetchMetadataCategories($schoolID) {
    $this->$connection;
    $stmt = $connection->prepare("SELECT metadata_id, category_name FROM Metadata WHERE SchoolID = ?");
    $stmt->execute([$schoolID]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function fetchSchoolIdForStudent($studentId) {
    $this->$connection;
    $stmt = $connection->prepare("SELECT SchoolID FROM Students WHERE student_id = ?");
    $stmt->execute([$studentId]);
    $result = $stmt->fetch();
    return $result ? $result['SchoolID'] : null;
}

function fetchScoreNamesByMetadata($metadataId) {
    $this->$connection;
    $stmt = $connection->prepare("SELECT * FROM Metadata WHERE metadata_id = ?");
    $stmt->execute([$metadataId]);
    // Check for errors
    if ($stmt->errorCode() != '00000') {
        // Handle error here; for example, log it and/or send an error response
        error_log('PDOStatement::errorInfo(): ' . print_r($stmt->errorInfo(), true));
        die("Error executing query");
    }

    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    // Make sure $result is an array, as expected
    if (!is_array($result)) {
        // Handle the unexpected result here (e.g., the query returned false or null)
        die("No data found for provided metadata_id");
    }

    // Extract only the score names (assuming columns are like 'score1_name', 'score2_name', etc.)
    $scoreNames = [];
    foreach ($result as $key => $value) {
        if (strpos($key, 'score') === 0) { // if the key starts with 'score'
            $scoreNames[$key] = $value;
        }
    }
    return $scoreNames;
}

function fetchStudentsByTeacher($teacherId) {
    $this->$connection;
    $stmt = $connection->prepare("SELECT s.* FROM Students s INNER JOIN Teachers t ON s.SchoolID = t.SchoolID WHERE t.teacher_id = ?");
    $stmt->execute([$teacherId]);
    return $stmt->fetchAll();
}

function addNewStudent($studentName, $teacherId) {
    $this->$connection;

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

public function fetchAllMetadataEntries() {
    $query = "SELECT metadata_id, category_name FROM Metadata";  
    $stmt = $this->connection->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

public function handleError($e) {
    error_log('Database error: ' . $e->getMessage());
    die("An internal server error occurred; please try again later.");
}
}

// Initialize empty arrays and variables
$performanceData = [];
$scoreNames = [];
$chartDates = [];
$chartScores = [];
$metadataEntries = array();


// If student_id is not set, exit early
if (!isset($_GET['student_id'])) {
    return;
}

$studentId = $_GET['student_id'];
$schoolID = fetchSchoolIdForStudent($studentId);  // Fetch SchoolID

if (!$schoolID) {
    return;  // If there's no SchoolID, exit early
}

if (!isset($_GET['metadata_id'])) {
    echo "Database error: " . $e->getMessage();
    return;
}
//$metadataId = $_GET['metadata_id'];
$dbOps = new DatabaseOperations($connection);

try {
    if (isset($_GET['action']) && $_GET['action'] == 'fetchGroups') {
        echo json_encode($dbOps->fetchGroupNames());
        exit;
    }

    if (!isset($_GET['student_id'])) {
        throw new Exception("Student ID not provided");
    }

    $studentId = $_GET['student_id'];
    $schoolID = $dbOps->fetchSchoolIdForStudent($studentId);

    if (!$schoolID) {
        throw new Exception("No school ID found for provided student ID");
    }

    if (!isset($_GET['metadata_id'])) {
        throw new Exception("Metadata ID not provided");
    }

    $metadataId = $_GET['metadata_id'];
    $performanceData = $dbOps->fetchPerformanceDataByMetadata($studentId, $metadataId);
    $scoreNames = $dbOps->fetchScoreNamesByMetadata($metadataId);
    $metadataEntries = $dbOps->fetchAllMetadataEntries();
// Fetch performance data and score names
$performanceData = fetchPerformanceDataByMetadata($studentId, $metadataId);
$scoreNames = fetchScoreNamesByMetadata($metadataId);

// Preparing the data for the chart
foreach ($performanceData as $record) {
    $chartDates[] = $record['score_date'];
    // You can add more logic here if needed
}
// ... (previous code)
} catch (Exception $e) {
    $dbOps->handleError($e);
}
// Create an instance of your database operations class


?>

