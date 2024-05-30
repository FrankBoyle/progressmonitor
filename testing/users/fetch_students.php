<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('auth_session.php');
include('db.php');

$schoolId = $_SESSION['school_id'];
$teacherId = $_SESSION['teacher_id'];

// Fetch students assigned to the logged-in teacher
function fetchStudentsByTeacher($teacherId, $archived = false) {
    global $connection;
    $archivedValue = $archived ? 1 : 0;
    $stmt = $connection->prepare("SELECT s.* FROM Students_new s INNER JOIN Teachers t ON s.school_id = t.school_id WHERE t.teacher_id = ? AND s.archived = ?");
    $stmt->execute([$teacherId, $archivedValue]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch teachers in the same school
function fetchTeachersBySchool($schoolId) {
    global $connection;
    $stmt = $connection->prepare("SELECT teacher_id, name FROM Teachers WHERE school_id = ?");
    $stmt->execute([$schoolId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Add new student
function addNewStudent($firstName, $lastName, $teacherId) {
    global $connection;
    $schoolId = fetchTeacherSchoolId($teacherId);
    if (checkDuplicateStudent($firstName, $lastName, $schoolId)) {
        return "Student with the same name already exists.";
    } 

    $stmt = $connection->prepare("INSERT INTO Students_new (first_name, last_name, school_id) VALUES (?, ?, ?)");
    $stmt->execute([$firstName, $lastName, $schoolId]);
    return "New student added successfully.";
}

// Fetch the school ID of the teacher
function fetchTeacherSchoolId($teacherId) {
    global $connection;
    $stmt = $connection->prepare("SELECT school_id FROM Teachers WHERE teacher_id = ?");
    $stmt->execute([$teacherId]);
    return $stmt->fetchColumn();
}

// Check for duplicate student
function checkDuplicateStudent($firstName, $lastName, $schoolId) {
    global $connection;
    $stmt = $connection->prepare("SELECT student_id_new FROM Students_new WHERE first_name = ? AND last_name = ? AND school_id = ?");
    $stmt->execute([$firstName, $lastName, $schoolId]);
    return $stmt->fetch() ? true : false;
}

// Archive a student
function archiveStudent($studentId) {
    global $connection;
    if ($_SESSION['is_admin']) {
        $stmt = $connection->prepare("UPDATE Students_new SET archived = TRUE WHERE student_id_new = ?");
        $stmt->execute([$studentId]);
        return "Student archived successfully.";
    } else {
        die("Unauthorized access.");  
    }
}

// Unarchive a student
function unarchiveStudent($studentId) {
    global $connection;
    $stmt = $connection->prepare("UPDATE Students_new SET archived = FALSE WHERE student_id_new = ?");
    $stmt->execute([$studentId]);
    return "Student unarchived successfully.";
}

// Fetch students in a specific group
function fetchStudentsByGroup($teacherId, $groupId) {
    global $connection;
    $stmt = $connection->prepare("SELECT s.* FROM Students_new s 
                                   INNER JOIN StudentGroup sg ON s.student_id_new = sg.student_id 
                                   WHERE sg.group_id = ? AND s.school_id IN 
                                   (SELECT school_id FROM Teachers WHERE teacher_id = ?)");
    $stmt->execute([$groupId, $teacherId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Share a group with another teacher
function shareGroupWithTeacher($groupId, $sharedTeacherId) {
    global $connection;
    $checkStmt = $connection->prepare("SELECT * FROM SharedGroups WHERE group_id = ? AND shared_teacher_id = ?");
    $checkStmt->execute([$groupId, $sharedTeacherId]);
    if ($checkStmt->fetch()) {
        return "Group is already shared with this teacher.";
    }
    $stmt = $connection->prepare("INSERT INTO SharedGroups (group_id, shared_teacher_id) VALUES (?, ?)");
    $stmt->execute([$groupId, $sharedTeacherId]);
    return "Group shared successfully.";
}

// Fetch all groups relevant to the teacher
function fetchAllRelevantGroups($teacherId) {
    global $connection;
    $stmt = $connection->prepare("
        SELECT g.*, (g.group_id = t.default_group_id) AS is_default 
        FROM Groups g
        LEFT JOIN Teachers t ON t.teacher_id = :teacherId
        WHERE g.teacher_id = :teacherId
        UNION
        SELECT g.*, (g.group_id = t.default_group_id) AS is_default
        FROM Groups g
        INNER JOIN SharedGroups sg ON g.group_id = sg.group_id
        LEFT JOIN Teachers t ON t.teacher_id = :teacherId
        WHERE sg.shared_teacher_id = :teacherId
    ");
    $stmt->bindParam(':teacherId', $teacherId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$allStudents = fetchStudentsByTeacher($teacherId, false);
$teachers = fetchTeachersBySchool($schoolId);
$groups = fetchAllRelevantGroups($teacherId);
$defaultGroupStmt = $connection->prepare("SELECT default_group_id FROM Teachers WHERE teacher_id = ?");
$defaultGroupStmt->execute([$teacherId]);
$defaultGroupResult = $defaultGroupStmt->fetch(PDO::FETCH_ASSOC);
$defaultGroupId = $defaultGroupResult ? $defaultGroupResult['default_group_id'] : null;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_new_student'])) {
        $firstName = $_POST['new_first_name'];
        $lastName = $_POST['new_last_name'];
        echo addNewStudent($firstName, $lastName, $teacherId);
    }

    if (isset($_POST['archive_student'])) {
        $studentId = $_POST['student_id_to_archive'];
        echo archiveStudent($studentId);
    }

    if (isset($_POST['unarchive_student'])) {
        $studentId = $_POST['student_id_to_unarchive'];
        echo unarchiveStudent($studentId);
    }

    if (isset($_POST['share_group'])) {
        $groupId = $_POST['group_id'];
        $sharedTeacherId = $_POST['shared_teacher_id'];
        echo shareGroupWithTeacher($groupId, $sharedTeacherId);
    }

    if (isset($_POST['create_group'])) {
        $groupName = $_POST['group_name'];
        $stmt = $connection->prepare("INSERT INTO Groups (group_name, school_id, teacher_id) VALUES (?, ?, ?)");
        $stmt->execute([$groupName, $schoolId, $teacherId]);
        echo "New group created successfully.";
    }

    if (isset($_POST['edit_group'])) {
        $groupId = $_POST['group_id'];
        $editedGroupName = $_POST['edited_group_name'];
        $stmt = $connection->prepare("UPDATE Groups SET group_name = ? WHERE group_id = ?");
        $stmt->execute([$editedGroupName, $groupId]);
        echo "Group name updated successfully.";
    }

    if (isset($_POST['assign_to_group'])) {
        if (isset($_POST['student_ids']) && is_array($_POST['student_ids']) && !empty($_POST['student_ids'])) {
            $studentIds = $_POST['student_ids'];
            $groupId = $_POST['group_id'];

            foreach ($studentIds as $studentId) {
                $checkStmt = $connection->prepare("SELECT * FROM StudentGroup WHERE student_id = ? AND group_id = ?");
                $checkStmt->execute([$studentId, $groupId]);

                if ($checkStmt->rowCount() == 0) {
                    $insertStmt = $connection->prepare("INSERT INTO StudentGroup (student_id, group_id) VALUES (?, ?)");
                    $insertStmt->execute([$studentId, $groupId]);
                }
            }
            echo "Selected students assigned to group successfully.";
        } else {
            echo "No students selected.";
        }
    }

    if (isset($_POST['toggle_view'])) {
        $_SESSION['show_archived'] = $_POST['show_archived'] == '1';
    }
}

$showArchived = $_SESSION['show_archived'] ?? false;
$students = $isGroupFilterActive ? fetchStudentsByGroup($teacherId, $selectedGroupId) : fetchStudentsByTeacher($teacherId, $showArchived);

$isAdmin = false;
$stmt = $connection->prepare("SELECT is_admin FROM Teachers WHERE teacher_id = ?");
$stmt->execute([$teacherId]);
$teacherData = $stmt->fetch(PDO::FETCH_ASSOC);

if ($teacherData && $teacherData['is_admin'] == 1) {
    $isAdmin = true;
}

$_SESSION['is_admin'] = $isAdmin;
?>