<?php
include './users/fetch_data.php';

session_start(); // Start the session at the beginning of the page

// Check if teacher_id is set in the session
if (!isset($_SESSION['teacher_id'])) {
    // Handle the case where teacher_id is not set in the session
    echo "Teacher ID is missing in the session.";
    exit;
}

// Now you can safely use $_SESSION['teacher_id'] in your page logic.
$teacherId = $_SESSION['teacher_id'];
// Now you can safely use $_SESSION['teacher_id'] in your page logic.
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
            <a href='view_student_data.php?student_id=<?= $student['student_id'] ?>'><?= $student['name'] ?></a><br>
        <?php endforeach; ?>
    <?php else: ?>
        No students found for this teacher.
    <?php endif; ?>
</body>
</html>