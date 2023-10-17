<?php
// functions.php
require_once 'db.php';
require_once 'fetch_data.php';
// Function to fetch performance data for a student
function fetchPerformanceData($connection, $studentId) {
    // No need for 'global $connection;' because it's now a parameter
    $stmt = $connection->prepare("SELECT * FROM Performance WHERE student_id = ? ORDER BY score_date DESC LIMIT 41");
    $stmt->execute([$studentId]);
    return $stmt->fetchAll();
}

// Function to fetch metadata categories for a school
function fetchMetadataCategoriesFromDatabase($connection, $schoolID) {
    // Removed the global variable
    $stmt = $connection->prepare("SELECT metadata_id, category_name FROM Metadata WHERE SchoolID = ?");
    $stmt->execute([$schoolID]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function fetchStudentIdsBySchool($connection, $schoolID) {
    // This array will hold the student IDs
    $studentIds = [];
    try {
        // Prepare your query: select student IDs from your students table where the SchoolID matches
        $stmt = $connection->prepare("SELECT student_id FROM Students WHERE SchoolID = :schoolID");
        // Execute the query with the provided SchoolID
        $stmt->execute(['schoolID' => $schoolID]);
        // Fetch all the student IDs
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $studentIds[] = $row['student_id'];  // Adjust 'student_id' if your column name is different
        }
    } catch (PDOException $e) {
        // You might want to handle errors, log them, or rethrow them, depending on your error strategy
        throw $e;
    }
    return $studentIds;
}

// Function to fetch the SchoolID for a student
function fetchSchoolIdForStudent($connection, $studentId) {
    // Adjusted to use parameter
    $stmt = $connection->prepare("SELECT SchoolID FROM Students WHERE student_id = ?");
    $stmt->execute([$studentId]);
    $result = $stmt->fetch();
    return $result ? $result['SchoolID'] : null;
}

function fetchMetadataIdsBySchool($connection, $schoolID) {
    // This array will hold the metadata IDs
    $metadataIds = [];
    try {
        // Prepare your query: select metadata IDs from your metadata table where the SchoolID matches
        $stmt = $connection->prepare("SELECT metadata_id FROM Metadata WHERE SchoolID = :schoolID");
        // Execute the query with the provided SchoolID
        $stmt->execute(['schoolID' => $schoolID]);
        // Fetch all the metadata IDs
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $metadataIds[] = $row['metadata_id'];  // Adjust 'metadata_id' if your column name is different
        }
    } catch (PDOException $e) {
        // Handle errors as per your error handling strategy
        throw $e;
    }
    return $metadataIds;
}

// Function to fetch score names for a school
function fetchScoreNames($connection, $schoolID) {
    // Code refined to use the $connection parameter
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
function fetchStudentsByTeacher($connection, $teacherId) {
    // Dependency on $connection removed from the global scope
    $stmt = $connection->prepare("SELECT s.* FROM Students s INNER JOIN Teachers t ON s.SchoolID = t.SchoolID WHERE t.teacher_id = ?");
    $stmt->execute([$teacherId]);
    return $stmt->fetchAll();
}

// Function to add a new student
function addNewStudent($connection, $studentName, $teacherId) {
    // Replaced global with function parameter
    $stmt = $connection->prepare("SELECT SchoolID FROM Teachers WHERE teacher_id = ?");
    $stmt->execute([$teacherId]);
    $teacherInfo = $stmt->fetch();
    $teacherSchoolID = $teacherInfo['SchoolID'];

    // Check for duplicates
    $stmt = $connection->prepare("SELECT student_id FROM Students WHERE name = ? AND SchoolID = ?");
    $stmt->execute([$studentName, $teacherSchoolID]);
    $duplicateStudent = $stmt->fetch();

    if ($duplicateStudent) {
        return "Student with the same name already exists.";
    } 

    // Insert the new student
    $stmt = $connection->prepare("INSERT INTO Students (name, SchoolID) VALUES (?, ?)");
    $stmt->execute([$studentName, $teacherSchoolID]);
    return "New student added successfully.";
}

// Function to fetch column names based on metadataId
function fetchColumnNamesByMetadataID($connection, $metadataID) {
    $stmt = $connection->prepare("SELECT score1_name, score2_name, score3_name, score4_name, score5_name, score6_name, score7_name, score8_name, score9_name, score10_name FROM Metadata WHERE metadata_id = ?");
    $stmt->execute([$metadataID]);
    
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $columnNames = [
        'score1' => $row['score1_name'],
        'score2' => $row['score2_name'],
        'score3' => $row['score3_name'],
        'score4' => $row['score4_name'],
        'score5' => $row['score5_name'],
        'score6' => $row['score6_name'],
        'score7' => $row['score7_name'],
        'score8' => $row['score8_name'],
        'score9' => $row['score9_name'],
        'score10' => $row['score10_name'],
    ];
    
    return $columnNames;
}


?>
