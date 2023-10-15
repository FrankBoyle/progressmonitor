<?php
// Error Reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('db.php');

function fetchPerformanceDataByMetadata($studentId, $metadataId) {
    global $connection;
    $stmt = $connection->prepare("
        SELECT * FROM Performance 
        WHERE student_id = ? AND metadata_id = ? 
        ORDER BY score_date DESC LIMIT 41
    ");
    $stmt->execute([$studentId, $metadataId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
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

function fetchScoreNamesByMetadata($metadataId) {
    global $connection;
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

$metadataEntries = array();

// Prepare the SQL query to fetch metadata entries
$query = "SELECT metadata_id, category_name FROM Metadata";  

try {
    // Prepare the SQL query
    $stmt = $connection->prepare($query);

    // Execute the query
    $stmt->execute();

    // Fetch all results into an array
    $metadataEntries = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Handle error during execution
    echo "Database error: " . $e->getMessage();
}

try {
    // Your database operations or other potential error-prone operations here
    // For example:
    $stmt = $connection->prepare($query);
    $stmt->execute();
    
    // If the above operations fail, the script will jump to the catch block below

} catch (PDOException $e) {
    // Now $e is defined, and you can access its methods
    error_log('Database error: ' . $e->getMessage());

    // Instead of echoing the error details to the user, consider showing a generic error message
    echo "An error occurred. Please try again later.";

    // Stop the script or handle the error gracefully
    exit;
}

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

if (!isset($_GET['metadata_id'])) {
    echo "Database error: " . $e->getMessage();
    return;
}
$metadataId = $_GET['metadata_id'];

// Fetch performance data and score names
$performanceData = fetchPerformanceDataByMetadata($studentId, $metadataId);
$scoreNames = fetchScoreNamesByMetadata($metadataId);

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
?>

