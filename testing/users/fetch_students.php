<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('auth_session.php');
include('db.php');

$schoolId = $_SESSION['school_id'];
$teacherId = $_SESSION['teacher_id'];

function fetchStudentsByTeacher($teacherId, $archived = false) {
    global $connection;
    $archivedValue = $archived ? 1 : 0;
    $stmt = $connection->prepare("SELECT s.* FROM Students s INNER JOIN Teachers t ON s.school_id = t.school_id WHERE t.teacher_id = ? AND s.archived = ?");
    $stmt->execute([$teacherId, $archivedValue]);
    return $stmt->fetchAll();
}

$allStudents = fetchStudentsByTeacher($teacherId, false);

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

$schoolId = $_SESSION['school_id'];
$teachers = fetchTeachersBySchool($schoolId);

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

function shareGroupWithTeacher($connection, $groupId, $sharedTeacherId) {
    // Check if the group is already shared with the teacher
    $checkStmt = $connection->prepare("SELECT * FROM SharedGroups WHERE group_id = ? AND shared_teacher_id = ?");
    $checkStmt->execute([$groupId, $sharedTeacherId]);
    if ($checkStmt->fetch()) {
        return "Group is already shared with this teacher.";
    }
    // Proceed with sharing
    $stmt = $connection->prepare("INSERT INTO SharedGroups (group_id, shared_teacher_id) VALUES (?, ?)");
    $stmt->execute([$groupId, $sharedTeacherId]);
    return "Group shared successfully.";
}

function fetchAllRelevantGroups($teacherId) {
    global $connection;
    // This query selects both groups owned by the teacher and groups shared with the teacher
    // and also checks if the group is the default group for the teacher
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

$groups = fetchAllRelevantGroups($teacherId);
$defaultGroupStmt = $connection->prepare("SELECT default_group_id FROM Teachers WHERE teacher_id = ?");
$defaultGroupStmt->execute([$teacherId]);
$defaultGroupResult = $defaultGroupStmt->fetch(PDO::FETCH_ASSOC);
$defaultGroupId = $defaultGroupResult ? $defaultGroupResult['default_group_id'] : null;

if (isset($_POST['share_group'])) {
    if (isset($_POST['group_id']) && isset($_POST['shared_teacher_id'])) {
        $groupId = $_POST['group_id'];
        $sharedTeacherId = $_POST['shared_teacher_id'];
        $message = shareGroupWithTeacher($connection, $groupId, $sharedTeacherId);
    } else {
        $message = "Group ID or Teacher ID not provided for sharing.";
    }
}

if (!isset($_SESSION['teacher_id'])) {
    die("Teacher ID not set in session");
}

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

if (isset($_POST['create_group'])) {
    $groupName = $_POST['group_name'];
    $schoolId = $_SESSION['school_id'];
    $teacherId = $_SESSION['teacher_id'];

    // Check if a group with the same name already exists for this teacher
    $checkStmt = $connection->prepare("SELECT group_id FROM Groups WHERE group_name = ? AND teacher_id = ?");
    $checkStmt->execute([$groupName, $teacherId]);
    if ($checkStmt->fetch()) {
        $message = "A group with this name already exists.";
    } else {
        // Group with the same name does not exist, proceed with creation
        $stmt = $connection->prepare("INSERT INTO Groups (group_name, school_id, teacher_id) VALUES (?, ?, ?)");
        $stmt->execute([$groupName, $schoolId, $teacherId]);
        $message = "New group created successfully.";
    }
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

// Initialize $selectedGroupId with the default group ID or "all_students" if no POST data
$selectedGroupId = $_POST['selected_group_id'] ?? $defaultGroupId ?? "all_students";

$isGroupFilterActive = $selectedGroupId != "all_students";

if ($isGroupFilterActive) {
    $students = fetchStudentsByGroup($teacherId, $selectedGroupId);
} else {
    $students = fetchStudentsByTeacher($teacherId, $showArchived);
}

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

if (isset($_POST['add_new_student'])) {
    $newStudentName = $_POST['new_student_name'];
    if (!empty($newStudentName)) {
        $message = addNewStudent($newStudentName, $teacherId);
    }
}

?>
