<?php
include './users/fetch_data.php';
session_start();

if (!isset($_SESSION['teacher_id'])) {
    die("Teacher ID not set in session");
}

$teacherId = $_SESSION['teacher_id'];
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

<script>
    // JavaScript to handle changing the link when the user selects a different metadata_id
    $('#metadataIdSelector').on('change', function () {
        var selectedLink = $(this).val();
        window.location.href = selectedLink; // Redirect to the selected link
    });
</script>
</body>
</html>


