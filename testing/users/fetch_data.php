<?php
session_start();
// Error Reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('db.php');

// Check if the teacher is logged in
if (!isset($_SESSION['teacher_id'])) {
    die("Teacher ID not set in session");
}

$teacherId = $_SESSION['teacher_id'];
$message = "";  // Initialize an empty message variable

function getSchoolIdByTeacher($teacherId) {
    global $connection; // use the global $connection object

    $query = "SELECT school_id FROM Teachers WHERE teacher_id = ? LIMIT 1";
    $stmt = $connection->prepare($query);
    $stmt->execute([$teacherId]); // with PDO, you pass parameters in execute()

    $result = $stmt->fetch(PDO::FETCH_ASSOC); // fetch() for a single result, fetchAll() for multiple rows.
}

function fetchPerformanceData($studentId, $metadata_id) {
    global $connection;
    $stmt = $connection->prepare("SELECT * FROM Performance WHERE student_id = ? AND metadata_id = ? ORDER BY score_date DESC LIMIT 41");
    $stmt->execute([$studentId, $metadata_id]);
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

function getSmallestMetadataId($school_id) {
    global $connection;

    // Prepare and execute a query to fetch the smallest metadata_id
    $query = "SELECT MIN(metadata_id) AS smallest_metadata_id FROM Metadata WHERE school_id = :schoolId";
    $stmt = $connection->prepare($query);
    $stmt->bindParam(':schoolId', $school_id, PDO::PARAM_INT);
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
$metadataEntries = [];
$metadata_id = $_GET['metadata_id'];
$studentId = $_GET['student_id'];
$school_id = getSchoolIdByTeacher($teacherId); // Get the school ID through the teacher's ID
echo($school_id);
//$metadata_id = $_POST['metadata_id']; // Get metadata_id from POST
//$schoolId = $_POST['school_id']; // Get school_id from POST
//$scores = $_POST['scores'];

// Check if the action is set to 'fetchGroups' and handle it
if (isset($_GET['action']) && $_GET['action'] == 'fetchGroups') {
    echo json_encode(fetchGroupNames());
    exit;
}

// Handle the form submission for adding a new student, if applicable
if (isset($_POST['add_new_student']) && !empty($_POST['new_student_name'])) {
    $newStudentName = $_POST['new_student_name'];
    $message = addNewStudent($newStudentName, $teacherId);
    // Consider redirecting or providing output after this action.
}

$students = fetchStudentsByTeacher($teacherId);
// Fetch performance data and score names
$performanceData = fetchPerformanceData($studentId, $metadata_id);
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
$stmt->execute([$school_id]);
$metadataEntries = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$metadataEntries) {
    // Check for no data
    echo 'No metadata entries found for the provided school ID.';
} else {
    // Process your $metadataEntries array as needed
    foreach ($metadataEntries as $metadataEntry) {
        $metadata_id = $metadataEntry['metadata_id'];
        $categoryName = $metadataEntry['category_name'];
        // Your processing here: Generate a link or output information
    }
}

$stmt = $connection->prepare("SELECT * FROM Performance WHERE student_id = ? AND metadata_id = ? ORDER BY score_date DESC LIMIT 41");
$stmt->execute([$studentId, $metadata_id]);

?>

