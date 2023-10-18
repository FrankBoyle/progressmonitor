<?php
include './users/fetch_data.php';

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



