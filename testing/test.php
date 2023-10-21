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
                // Check if 'school_id' is set for the student before attempting to fetch metadata
                if (isset($student['school_id'])) {
                    // Fetch the smallest metadata_id for the student's school_id
                    $metadata_id = getSmallestMetadataId($student['school_id']);
                    
                    // Check if a valid metadataId was returned before using it
                    if ($metadata_id) {
                        echo "<a href='view_student_data.php?student_id=" . htmlspecialchars($student['student_id']) . "&metadata_id=" . htmlspecialchars($metadata_id) . "'>" . htmlspecialchars($student['name']) . "</a><br>";
                    } else {
                        echo "No metadata found for " . htmlspecialchars($student['name']) . "<br>";
                    }
                } else {
                    echo "No school information available for " . htmlspecialchars($student['name']) . "<br>";
                }
            ?>
        <?php endforeach; ?>
    <?php else: ?>
        No students found for this teacher.
    <?php endif; ?>

</body>
</html>



