<?php
include './users/fetch_data.php';
session_start();

if (!isset($_SESSION['teacher_id'])) {
    header("Location: login.php"); // Redirect to the login page if teacher_id is not set in the session
    exit;
}

if (!isset($_SESSION['teacher_id'])) {
    die("Teacher ID not set in session");
}

// Now you can safely use $_SESSION['teacher_id'] in your page logic.
//$teacherId = $_SESSION['teacher_id'];
//$teacherId = $_SESSION['teacher_id'];
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



