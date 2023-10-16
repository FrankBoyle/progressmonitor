<?php
// Error Reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('db.php');

if (!isset($_GET['teacher_id'])) {
    echo "Teacher ID is missing in the URL.";
    exit;
}

$teacherId = $_GET['teacher_id'];
// Continue with the rest of your script...


// Function to fetch performance data for a student
function fetchPerformanceData($studentId) {
    global $connection;
    $stmt = $connection->prepare("SELECT * FROM Performance WHERE student_id = ? ORDER BY score_date DESC LIMIT 41");
    $stmt->execute([$studentId]);
    return $stmt->fetchAll();
}

// Function to fetch score names for a school
function fetchScoreNames($schoolID) {
    global $connection;
    $scoreNames = [];
    $stmt = $connection->prepare("SELECT score1_name, score2_name, score3_name, score4_name, score5_name, score6_name, score7_name, score8_name, score9_name, score10_name FROM Metadata WHERE SchoolID = ?");
    $stmt->execute([$schoolID]);
    if ($row = $stmt->fetch()) {
        for ($i = 1; $i <= 10; $i++) {
            $scoreName = $row["score{$i}_name"];
            $scoreNames["score{$i}"] = $scoreName;
        }
    }
    return $scoreNames;
}

// Function to fetch students by teacher
function fetchStudentsByTeacher($teacherId) {
    global $connection;
    $stmt = $connection->prepare("SELECT s.* FROM Students s INNER JOIN Teachers t ON s.SchoolID = t.SchoolID WHERE t.teacher_id = ?");
    $stmt->execute([$teacherId]);
    return $stmt->fetchAll();
}

// Function to fetch group names
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


// Check if the action is set to 'fetchGroups' and handle it
if (isset($_GET['action']) && $_GET['action'] == 'fetchGroups') {
    echo json_encode(fetchGroupNames());
    exit;
}

// Check if the teacher_id is provided in the URL
if (!isset($_GET['teacher_id'])) {
    echo "Teacher ID is missing in the URL.";
    exit;
}

$teacherId = $_GET['teacher_id'];
$schoolID = fetchSchoolIdForTeacher($teacherId); // Fetch SchoolID for the teacher

// Function to fetch the SchoolID for a teacher
function fetchSchoolIdForTeacher($teacherId) {
    global $connection;
    $stmt = $connection->prepare("SELECT SchoolID FROM Teachers WHERE teacher_id = ?");
    $stmt->execute([$teacherId]);
    $result = $stmt->fetch();
    return $result ? $result['SchoolID'] : null;
}

// ...

$teacherId = $_GET['teacher_id']; // Assuming you have a way to get the teacher's ID

// Fetch the SchoolID associated with the teacher
$teacherSchoolID = fetchSchoolIdForTeacher($teacherId);

if (!$teacherSchoolID) {
    // Handle the case where the teacher is not associated with any school
    echo "Teacher is not associated with any school.";
    exit;
}

// Fetch students associated with the teacher
$students = fetchStudentsByTeacher($teacherId);

// Optionally, you can fetch performance data for each student here
// and organize it based on the SchoolID and student ID.

// ...
?>