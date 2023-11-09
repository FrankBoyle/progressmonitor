<?php
include './users/fetch_students.php';

if (!isset($_SESSION['teacher_id'])) {
    die("Teacher ID not set in session");
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

$students = fetchStudentsByTeacher($teacherId);

// Function to compare student names for sorting
function sortByName($a, $b) {
    return strcmp($a['name'], $b['name']);
}

// Sort the students array by name
usort($students, 'sortByName');
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Your Page Title</title>
</head>
<body>
    <!-- Add New Student Form -->
    <form method="post" action="">
        <label for="new_student_name">New Student Name:</label>
        <input type="text" id="new_student_name" name="new_student_name">
        <input type="submit" name="add_new_student" value="Add New Student">
    </form>

    <?php if ($message): ?>
        <p><?= $message ?></p>
    <?php endif; ?>

    <?php if (!empty($students)): ?>
        <h2>Students:</h2>
        <?php foreach ($students as $student): ?>
            <?php
                // Fetch the smallest metadata_id for the student's school_id
                $metadataId = getSmallestMetadataId($student['school_id']);
            ?>
            <a href='view_student_data.php?student_id=<?= $student['student_id'] ?>&metadata_id=<?= $metadataId ?>'><?= $student['name'] ?></a><br>
        <?php endforeach; ?>
    <?php else: ?>
        No students found for this teacher.
    <?php endif; ?>
</body>
</html>



