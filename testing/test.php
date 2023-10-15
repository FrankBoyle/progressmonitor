<?php
session_start();
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

// Fetch metadata categories for the dropdown
$metadataCategories = fetchMetadataCategoriesFromDatabase($teacherId);
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
            <label><?= $student['name'] ?>:</label>
            <select class="metadata-selector">
                <?php foreach ($metadataCategories as $category): ?>
                    <option value="<?= $category['metadata_id'] ?>"><?= $category['category_name'] ?></option>
                <?php endforeach; ?>
            </select>
            <a href='view_student_data.php?student_id=<?= $student['student_id'] ?>' class="view-data-link">View Data</a><br>
        <?php endforeach; ?>
    <?php else: ?>
        No students found for this teacher.
    <?php endif; ?>

    <script>
        // JavaScript to handle metadata selection and view data links
        const metadataSelectors = document.querySelectorAll(".metadata-selector");
        const viewDataLinks = document.querySelectorAll(".view-data-link");

        metadataSelectors.forEach((selector, index) => {
            selector.addEventListener("change", () => {
                const selectedMetadataId = selector.value;
                viewDataLinks[index].href += `&metadata_id=${selectedMetadataId}`;
            });
        });
    </script>
</body>
</html>
