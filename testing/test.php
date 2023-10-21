<?php
include './users/fetch_data.php';

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
$defaultMetadataId = getSmallestMetadataId($school_id);
echo($defaultMetadataId);
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
        <!-- Use the default metadata ID for generating the link -->
        <a href='view_student_data.php?student_id=<?= htmlspecialchars($student['student_id']) ?>&metadata_id=<?= htmlspecialchars($defaultMetadataId) ?>'>
            <?= htmlspecialchars($student['name']) ?>
        </a><br>
    <?php endforeach; ?>
<?php else: ?>
    No students found for this teacher.
<?php endif; ?>

</body>
</html>



