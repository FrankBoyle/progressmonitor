<?php
// Include database connection and error reporting settings
include('./users/db.php');

// Error Reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if `student_id` is provided in the URL
if (!isset($_GET['student_id'])) {
    die('Student ID is not provided.');
}

$studentId = $_GET['student_id'];

$stmt = $conn->prepare("SELECT * FROM Performance WHERE student_id = ? ORDER BY week_start_date DESC LIMIT 41");
$stmt->execute([$studentId]);

$performanceData = $stmt->fetchAll();

// Check Data Retrieval
if (empty($performanceData)) {
    die('No performance data found for the given student ID.');
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Performance Data</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

<script>
$(document).ready(function() {
    // Add click event handler to editable cells
    $('.editable').click(function() {
        const cell = $(this);
        const originalValue = cell.data('value');

        // Create an input field for editing
        const input = $('<input type="text">');
        input.val(originalValue);

        // Replace the cell content with the input field
        cell.html(input);

        // Focus on the input field
        input.focus();

        // Add blur event handler to save changes
        input.blur(function() {
            const newValue = input.val();
            cell.data('value', newValue);

            // Update the cell content with the new value
            cell.text(newValue);

            // Perform AJAX request to update the database with the new value
            const performanceId = cell.closest('tr').data('performance-id');
            const fieldName = cell.data('field-name');

            $.ajax({
                type: 'POST',
                url: 'update_performance.php',
                data: {
                    performance_id: performanceId,
                    field_name: fieldName,
                    new_value: newValue,
                },
                success: function(response) {
                    if (response.success) {
                        alert('Data updated successfully.');
                    } else {
                        alert('Error updating data: ' + response.error);
                    }
                },
                error: function() {
                    alert('Error updating data. Please try again later.');
                },
            });
        });

        // Pressing Enter key while editing should save changes
        input.keypress(function(e) {
            if (e.which === 13) {
                input.blur();
            }
        });
    });
});
</script>

<h1>Student Performance Data</h1>

<table border="1">
    <tr>
        <th>Week Start Date</th>
        <th class="editable" data-field-name="score1">Score1</th>
        <th class="editable" data-field-name="score2">Score2</th>
        <th class="editable" data-field-name="score3">Score3</th>
        <th class="editable" data-field-name="score4">Score4</th>
        <th class="editable" data-field-name="score5">Score5</th>
        <th class="editable" data-field-name="score6">Score6</th>
        <th class="editable" data-field-name="score7">Score7</th>
        <th class="editable" data-field-name="score8">Score8</th>
        <th class="editable" data-field-name="score9">Score9</th>
        <th class="editable" data-field-name="score10">Score10</th>
    </tr>

    <?php foreach ($performanceData as $data) : ?>
        <tr data-performance-id="<?php echo $data['performance_id']; ?>">
            <td><?php echo $data['week_start_date']; ?></td>
            <td class="editable" data-value="<?php echo $data['score1']; ?>" data-field-name="score1"><?php echo $data['score1']; ?></td>
            <td class="editable" data-value="<?php echo $data['score2']; ?>" data-field-name="score2"><?php echo $data['score2']; ?></td>
            <td class="editable" data-value="<?php echo $data['score3']; ?>" data-field-name="score3"><?php echo $data['score3']; ?></td>
            <td class="editable" data-value="<?php echo $data['score4']; ?>" data-field-name="score4"><?php echo $data['score4']; ?></td>
            <td class="editable" data-value="<?php echo $data['score5']; ?>" data-field-name="score5"><?php echo $data['score5']; ?></td>
            <td class="editable" data-value="<?php echo $data['score6']; ?>" data-field-name="score6"><?php echo $data['score6']; ?></td>
            <td class="editable" data-value="<?php echo $data['score7']; ?>" data-field-name="score7"><?php echo $data['score7']; ?></td>
            <td class="editable" data-value="<?php echo $data['score8']; ?>" data-field-name="score8"><?php echo $data['score8']; ?></td>
            <td class="editable" data-value="<?php echo $data['score9']; ?>" data-field-name="score9"><?php echo $data['score9']; ?></td>
            <td class="editable" data-value="<?php echo $data['score10']; ?>" data-field-name="score10"><?php echo $data['score10']; ?></td>
        </tr>
    <?php endforeach; ?>

</table>

</body>
</html>


