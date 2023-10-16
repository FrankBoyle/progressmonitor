<?php
session_start(); // Make sure to start the session at the beginning of each page
include './users/fetch_data.php';

if (isset($_SESSION['teacher_id'])) {
    $teacherId = $_SESSION['teacher_id'];
    // Now you can use $teacherId in your page logic
} else {
    // Handle the case where teacher_id is not set in the session
    echo "Teacher ID is missing in the session.";
}

error_log(print_r($_SESSION, true)); // Log the session variables

if ($teacherResult) {
    $_SESSION['teacher_id'] = $teacherResult['teacher_id'];
    // Assuming you have fetched the SchoolID from somewhere in your code
    $_SESSION['SchoolID'] = $schoolIdFromDatabase;

    // Redirect to the desired page
    header("Location: test.php");
    exit();
} else {
    echo '<p class="error">Username or password is incorrect!</p>';
}

$message = "";  // Initialize an empty message variable

// Define a default metadata ID (you can change this as needed)
$defaultMetadataID = 1;

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

    <?php if (!empty($students)): ?>
    <h2>Students:</h2>
    <?php foreach ($students as $student): ?>
        <?php
        // Dynamically generate the link with metadata_id as a query parameter
        $studentLink = 'view_student_data.php?student_id=' . $student['student_id'] . '&metadata_id=' . $defaultMetadataID;
        ?>
        <a href="<?= $studentLink ?>"><?= $student['name'] ?></a><br>
    <?php endforeach; ?>
<?php else: ?>
    No students found for this teacher.
<?php endif; ?>

</body>
</html>


