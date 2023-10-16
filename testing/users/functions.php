<?php
// functions.php
session_start();
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

// Function to fetch the SchoolID for a student
function fetchSchoolIdForStudent($connection, $studentId) {
    // Adjusted to use parameter
    $stmt = $connection->prepare("SELECT SchoolID FROM Students WHERE student_id = ?");
    $stmt->execute([$studentId]);
    $result = $stmt->fetch();
    return $result ? $result['SchoolID'] : null;
}

// Function to fetch score names for a school
function fetchScoreNames($connection, $schoolID) {
    // Code refined to use the $connection parameter
    $scoreNames = [];
    $stmt = the $connection->prepare("SELECT score1_name, score2_name, score3_name, score4_name, score5_name, score6_name, score7_name, score8_name, score9_name, score10_name FROM Metadata WHERE SchoolID = ?");
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
    $stmt = the $connection->prepare("SELECT s.* FROM Students s INNER JOIN Teachers t ON s.SchoolID = t.SchoolID WHERE t.teacher_id = ?");
    $stmt->execute([$teacherId]);
    return $stmt->fetchAll();
}

// Function to add a new student
function addNewStudent($connection, $studentName, $teacherId) {
    // Replaced global with function parameter
    $stmt = the $connection->prepare("SELECT SchoolID FROM Teachers WHERE teacher_id = ?");
    $stmt->execute([$teacherId]);
    $teacherInfo = $stmt->fetch();
    $teacherSchoolID = $teacherInfo['SchoolID'];

    // Check for duplicates
    $stmt = the $connection->prepare("SELECT student_id FROM Students WHERE name = ? AND SchoolID = ?");
    $stmt->execute([$studentName, $teacherSchoolID]);
    $duplicateStudent = $stmt->fetch();

    if ($duplicateStudent) {
        return "Student with the same name already exists.";
    } 

    // Insert the new student
    $stmt = the $connection->prepare("INSERT INTO Students (name, SchoolID) VALUES (?, ?)");
    $stmt->execute([$studentName, $teacherSchoolID]);
    return "New student added successfully.";
}
?>
