<?php
session_start();
include('auth_session.php');
// Error Reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('db.php');

function fetchStudentsByTeacher($teacherId, $archived = false) {
    global $connection;
    $archivedValue = $archived ? 1 : 0; // Use 1 for TRUE and 0 for FALSE
    $stmt = $connection->prepare("SELECT s.* FROM Students s INNER JOIN Teachers t ON s.school_id = t.school_id WHERE t.teacher_id = ? AND s.archived = ?");
    $stmt->execute([$teacherId, $archivedValue]);
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

if (!isset($_SESSION['teacher_id'])) {
    die("Teacher ID not set in session");
} else {
    $teacherId = $_SESSION['teacher_id'];
}

function archiveStudent($studentId) {
        global $connection;
        if ($_SESSION['is_admin']) { // Directly using the session variable
            $stmt = $connection->prepare("UPDATE Students SET archived = TRUE WHERE student_id = ?");
        $stmt->execute([$studentId]);
        return "Student archived successfully.";
    } else {
              // Admin privileges required
              die("Unauthorized access.");  
    }
}

function unarchiveStudent($studentId) {
    global $connection;

    $stmt = $connection->prepare("UPDATE Students SET archived = FALSE WHERE student_id = ?");
    $stmt->execute([$studentId]);

    return "Student unarchived successfully.";
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

// Toggle the view based on the form submission
if (isset($_POST['toggle_view'])) {
    $_SESSION['show_archived'] = $_POST['show_archived'] == '1';
}

// Use the session variable to determine the current view
$showArchived = $_SESSION['show_archived'] ?? false;

// Fetch students based on the current view
$students = fetchStudentsByTeacher($teacherId, $showArchived);

function getSmallestMetadataId($schoolId) {
    global $connection;

    // Prepare and execute a query to fetch the smallest metadata_id
    $query = "SELECT MIN(metadata_id) AS smallest_metadata_id FROM Metadata WHERE school_id = :schoolId";
    $stmt = $connection->prepare($query);
    $stmt->bindParam(':schoolId', $schoolId, PDO::PARAM_INT);
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

if (isset($_POST['create_group'])) {
    $groupName = $_POST['group_name'];
    $schoolId = $_SESSION['school_id']; // Retrieved from session
    $teacherId = $_SESSION['teacher_id']; // Assuming teacher_id is stored in session

    // SQL to insert a new group
    $stmt = $connection->prepare("INSERT INTO Groups (group_name, school_id, teacher_id) VALUES (?, ?, ?)");
    $stmt->execute([$groupName, $schoolId, $teacherId]);

    $message = "New group created successfully.";
}

if (isset($_POST['edit_group'])) {
    $groupId = $_POST['group_id'];
    $editedGroupName = $_POST['edited_group_name'];

    // SQL to update the group name
    $stmt = $connection->prepare("UPDATE Groups SET group_name = ? WHERE group_id = ?");
    $stmt->execute([$editedGroupName, $groupId]);

    $message = "Group name updated successfully.";

    // Refresh the group list
    $stmt = $connection->prepare("SELECT group_id, group_name FROM Groups WHERE teacher_id = ?");
    $stmt->execute([$teacherId]);
    $groups = $stmt->fetchAll();
}

$isGroupFilterActive = isset($_POST['selected_group_id']) && $_POST['selected_group_id'] != "all_students";

if (isset($_POST['selected_group_id'])) {
    $selectedGroupId = $_POST['selected_group_id'];

    if ($selectedGroupId != "all_students") {
        // Fetch students assigned to the selected group
        $students = fetchStudentsByGroup($teacherId, $selectedGroupId);
    } else {
        // Fetch all students
        $students = fetchStudentsByTeacher($teacherId, $showArchived);
    }
} else {
    // Fetch all students by default
    $students = fetchStudentsByTeacher($teacherId, $showArchived);
}

// Function to fetch students by group
function fetchStudentsByGroup($teacherId, $groupId) {
    global $connection;
    $stmt = $connection->prepare("SELECT s.* FROM Students s 
                                   INNER JOIN StudentGroup sg ON s.student_id = sg.student_id 
                                   WHERE sg.group_id = ? AND s.school_id IN 
                                   (SELECT school_id FROM Teachers WHERE teacher_id = ?)");
    $stmt->execute([$groupId, $teacherId]);
    return $stmt->fetchAll();
}

// Fetch groups for the specific teacher
$teacherId = $_SESSION['teacher_id'];
$stmt = $connection->prepare("SELECT group_id, group_name FROM Groups WHERE teacher_id = ?");
$stmt->execute([$teacherId]);
$groups = $stmt->fetchAll();
$isAdmin = false;

// Fetch the admin status from the database
$stmt = $connection->prepare("SELECT is_admin FROM Teachers WHERE teacher_id = ?");
$stmt->execute([$teacherId]);
$teacherData = $stmt->fetch(PDO::FETCH_ASSOC);

if ($teacherData && $teacherData['is_admin'] == 1) {
    $isAdmin = true;
}

$_SESSION['is_admin'] = $isAdmin; // Storing isAdmin status in session for easy access

if (isset($_POST['assign_to_group'])) {
    if (isset($_POST['student_ids']) && is_array($_POST['student_ids']) && !empty($_POST['student_ids'])) {
        $studentIds = $_POST['student_ids'];
        $groupId = $_POST['group_id'];

        foreach ($studentIds as $studentId) {
            // Check if the student is already in this group
            $checkStmt = $connection->prepare("SELECT * FROM StudentGroup WHERE student_id = ? AND group_id = ?");
            $checkStmt->execute([$studentId, $groupId]);

            if ($checkStmt->rowCount() == 0) {
                // Student is not in this group, proceed with insertion
                $insertStmt = $connection->prepare("INSERT INTO StudentGroup (student_id, group_id) VALUES (?, ?)");
                $insertStmt->execute([$studentId, $groupId]);
            }
        }
        $message = "Selected students assigned to group successfully.";
    } else {
        $message = "No students selected.";
    }
}

$teacherId = $_SESSION['teacher_id'];
$message = "";  // Initialize an empty message variable

// Handle form submission for adding new student
if (isset($_POST['add_new_student'])) {
    $newStudentName = $_POST['new_student_name'];
    if (!empty($newStudentName)) {
        $message = addNewStudent($newStudentName, $teacherId);
    }
}

//$students = fetchStudentsByTeacher($teacherId);

?>

