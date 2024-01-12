<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('auth_session.php');
include('db.php');

function fetchStudentsByTeacher($teacherId, $archived = false) {
    global $connection;
    $archivedValue = $archived ? 1 : 0;
    $stmt = $connection->prepare("SELECT s.* FROM Students s INNER JOIN Teachers t ON s.school_id = t.school_id WHERE t.teacher_id = ? AND s.archived = ?");
    $stmt->execute([$teacherId, $archivedValue]);
    return $stmt->fetchAll();
}

function addNewStudent($studentName, $teacherId) {
    global $connection;

    $stmt = $connection->prepare("SELECT school_id FROM Teachers WHERE teacher_id = ?");
    $stmt->execute([$teacherId]);
    $teacherInfo = $stmt->fetch();
    $teacherSchoolId = $teacherInfo['school_id'];

    $stmt = $connection->prepare("SELECT student_id FROM Students WHERE name = ? AND school_id = ?");
    $stmt->execute([$studentName, $teacherSchoolId]);
    $duplicateStudent = $stmt->fetch();

    if ($duplicateStudent) {
        return "Student with the same name already exists.";
    } 

    $stmt = $connection->prepare("INSERT INTO Students (name, school_id) VALUES (?, ?)");
    $stmt->execute([$studentName, $teacherSchoolId]);
    return "New student added successfully.";
}

function archiveStudent($studentId) {
    global $connection;
    if ($_SESSION['is_admin']) {
        $stmt = $connection->prepare("UPDATE Students SET archived = TRUE WHERE student_id = ?");
        $stmt->execute([$studentId]);
        return "Student archived successfully.";
    } else {
        die("Unauthorized access.");  
    }
}

function fetchTeachersBySchool($schoolId) {
    global $connection;
    $stmt = $connection->prepare("SELECT teacher_id, name FROM Teachers WHERE school_id = ?");
    $stmt->execute([$schoolId]);
    return $stmt->fetchAll();
}

function unarchiveStudent($studentId) {
    global $connection;

    $stmt = $connection->prepare("UPDATE Students SET archived = FALSE WHERE student_id = ?");
    $stmt->execute([$studentId]);

    return "Student unarchived successfully.";
}

function fetchStudentsByGroup($teacherId, $groupId) {
    global $connection;
    $stmt = $connection->prepare("SELECT s.* FROM Students s 
                                   INNER JOIN StudentGroup sg ON s.student_id = sg.student_id 
                                   WHERE sg.group_id = ? AND s.school_id IN 
                                   (SELECT school_id FROM Teachers WHERE teacher_id = ?)");
    $stmt->execute([$groupId, $teacherId]);
    return $stmt->fetchAll();
}

function getSmallestMetadataId($schoolId) {
    global $connection;

    $query = "SELECT MIN(metadata_id) AS smallest_metadata_id FROM Metadata WHERE school_id = :schoolId";
    $stmt = $connection->prepare($query);
    $stmt->bindParam(':schoolId', $schoolId, PDO::PARAM_INT);
    $stmt->execute();

    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result && isset($result['smallest_metadata_id'])) {
        return $result['smallest_metadata_id'];
    } else {
        return null;
    }
}

if (!isset($_SESSION['teacher_id'])) {
    die("Teacher ID not set in session");
}

$teacherId = $_SESSION['teacher_id'];

if (isset($_POST['archive_student'])) {
    if (isset($_POST['student_id_to_toggle'])) {
        $studentIdToArchive = $_POST['student_id_to_toggle'];
        $message = archiveStudent($studentIdToArchive);
    } else {
        $message = "Student ID not provided for archiving.";
    }
}

if (isset($_POST['unarchive_student'])) {
    if (isset($_POST['student_id_to_toggle'])) {
        $studentIdToUnarchive = $_POST['student_id_to_toggle'];
        $message = unarchiveStudent($studentIdToUnarchive);
    } else {
        $message = "Student ID not provided for unarchiving.";
    }
}

if (isset($_POST['toggle_view'])) {
    $_SESSION['show_archived'] = $_POST['show_archived'] == '1';
}

$showArchived = $_SESSION['show_archived'] ?? false;

$students = fetchStudentsByTeacher($teacherId, $showArchived);

$teacherId = $_SESSION['teacher_id'];

if (isset($_POST['create_group'])) {
    $groupName = $_POST['group_name'];
    $schoolId = $_SESSION['school_id'];
    $teacherId = $_SESSION['teacher_id'];

    $stmt = $connection->prepare("INSERT INTO Groups (group_name, school_id, teacher_id) VALUES (?, ?, ?)");
    $stmt->execute([$groupName, $schoolId, $teacherId]);

    $message = "New group created successfully.";
}

if (isset($_POST['edit_group'])) {
    $groupId = $_POST['group_id'];
    $editedGroupName = $_POST['edited_group_name'];

    $stmt = $connection->prepare("UPDATE Groups SET group_name = ? WHERE group_id = ?");
    $stmt->execute([$editedGroupName, $groupId]);

    $message = "Group name updated successfully.";

    $stmt = $connection->prepare("SELECT group_id, group_name FROM Groups WHERE teacher_id = ?");
    $stmt->execute([$teacherId]);
    $groups = $stmt->fetchAll();
}

$isGroupFilterActive = isset($_POST['selected_group_id']) && $_POST['selected_group_id'] != "all_students";

if (isset($_POST['selected_group_id'])) {
    $selectedGroupId = $_POST['selected_group_id'];

    if ($selectedGroupId != "all_students") {
        $students = fetchStudentsByGroup($teacherId, $selectedGroupId);
    } else {
        $students = fetchStudentsByTeacher($teacherId, $showArchived);
    }
} else {
    $students = fetchStudentsByTeacher($teacherId, $showArchived);
}

$teacherId = $_SESSION['teacher_id'];
$stmt = $connection->prepare("SELECT group_id, group_name FROM Groups WHERE teacher_id = ?");
$stmt->execute([$teacherId]);
$groups = $stmt->fetchAll();
$isAdmin = false;

$stmt = $connection->prepare("SELECT is_admin FROM Teachers WHERE teacher_id = ?");
$stmt->execute([$teacherId]);
$teacherData = $stmt->fetch(PDO::FETCH_ASSOC);

if ($teacherData && $teacherData['is_admin'] == 1) {
    $isAdmin = true;
}

$_SESSION['is_admin'] = $isAdmin;

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
        $message = "Selected students assigned to group successfully.";
    } else {
        $message = "No students selected.";
    }
}

$message = "";
if (isset($_POST['add_new_student'])) {
    $newStudentName = $_POST['new_student_name'];
    if (!empty($newStudentName)) {
        $message = addNewStudent($newStudentName, $teacherId);
    }
}

?>
