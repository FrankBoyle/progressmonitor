<?php
// Error Reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('db.php');
header('Content-Type: application/json');

class DatabaseOperations {
    private $connection;

    public function __construct($connection) {
        $this->connection = $connection;
    }
    
    public function fetchPerformanceDataByMetadata($studentId, $metadataId) {
        $stmt = $this->connection->prepare("
            SELECT * FROM Performance 
            WHERE student_id = ? AND metadata_id = ? 
            ORDER BY score_date DESC LIMIT 41
        ");
        $stmt->execute([$studentId, $metadataId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function fetchMetadataCategories($schoolID) {
        $stmt = $this->connection->prepare("SELECT metadata_id, category_name FROM Metadata WHERE SchoolID = ?");
        $stmt->execute([$schoolID]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function fetchSchoolIdForStudent($studentId) {
        $stmt = $this->connection->prepare("SELECT SchoolID FROM Students WHERE student_id = ?");
        $stmt->execute([$studentId]);
        $result = $stmt->fetch();
        return $result ? $result['SchoolID'] : null;
    }

function fetchScoreNamesByMetadata($metadataId) {
    $stmt = $this->connection->prepare("SELECT * FROM Metadata WHERE metadata_id = ?");
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
    $stmt = $this->connection->prepare("SELECT s.* FROM Students s INNER JOIN Teachers t ON s.SchoolID = t.SchoolID WHERE t.teacher_id = ?");
    $stmt->execute([$teacherId]);
    return $stmt->fetchAll();
}

function addNewStudent($studentName, $teacherId) {
    // Fetch the SchoolID of the current teacher
    $stmt = $this->connection->prepare("SELECT SchoolID FROM Teachers WHERE teacher_id = ?");
    $stmt->execute([$teacherId]);
    $teacherInfo = $stmt->fetch();
    $teacherSchoolID = $teacherInfo['SchoolID'];

    // Check if the student with the same name and SchoolID already exists
    $stmt = $this->connection->prepare("SELECT student_id FROM Students WHERE name = ? AND SchoolID = ?");
    $stmt->execute([$studentName, $teacherSchoolID]);
    $duplicateStudent = $stmt->fetch();

    if ($duplicateStudent) {
        return "Student with the same name already exists.";
    } 

    // Insert the new student with the same SchoolID
    $stmt = $this->connection->prepare("INSERT INTO Students (name, SchoolID) VALUES (?, ?)");
    $stmt->execute([$studentName, $teacherSchoolID]);
    return "New student added successfully.";
}

public function fetchAllMetadataEntries() {
    $query = "SELECT metadata_id, category_name FROM Metadata";  
    $stmt = this->connection->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

public function handleError($e) {
    error_log('Database error: ' . $e->getMessage());
    die("An internal server error occurred; please try again later.");
}
}

$dbOps = new DatabaseOperations($connection);

try {
    // Check if action is set to 'fetchGroups'
    if (isset($_GET['action']) && $_GET['action'] == 'fetchGroups') {
        echo json_encode($dbOps->fetchGroupNames());
        exit;
    }

    // Check if the student ID is provided
    if (!isset($_GET['student_id'])) {
        throw new Exception("Student ID not provided");
    }

    $studentId = $_GET['student_id'];
    
    // Fetch the school ID for the student
    $schoolID = $dbOps->fetchSchoolIdForStudent($studentId);
    if (!$schoolID) {
        throw new Exception("No school ID found for provided student ID");
    }

    // Check if the metadata ID is provided
    if (!isset($_GET['metadata_id'])) {
        throw new Exception("Metadata ID not provided");
    }

    $metadataId = $_GET['metadata_id'];

    // Fetch the required data using the appropriate class methods
    $performanceData = $dbOps->fetchPerformanceDataByMetadata($studentId, $metadataId);
    $scoreNames = $dbOps->fetchScoreNamesByMetadata($metadataId);
    $metadataEntries = $dbOps->fetchAllMetadataEntries();

    // Initialize chart data
    $chartDates = [];
    $chartScores = []; // Assuming you want to process scores too

    // Preparing the data for the chart
    foreach ($performanceData as $record) {
        $chartDates[] = $record['score_date'];
        // Assuming the score is also stored in the record, add it to the chart scores
        // $chartScores[] = $record['score']; // Uncomment and adjust if necessary
    }

    // ... (Any additional logic for processing or output)

} catch (Exception $e) {
    // Handle exceptions by calling the error handling method of your class
    $dbOps->handleError($e);
}

// ... (Any final logic or closing tags, if necessary)

?>