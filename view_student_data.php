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

$stmt = $connection->prepare("SELECT * FROM Performance WHERE student_id = ? ORDER BY week_start_date DESC LIMIT 41");
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
    // Function to attach click event handlers to editable cells
    function attachEditableHandler() {
        $('.editable').off('click').on('click', function() {
            const cell = $(this);
            const originalValue = cell.text();  // Assuming that the text holds the value

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
                cell.text(newValue);  // Update the cell content with the new value

                // Perform AJAX request to update the database with the new value
                const performanceId = cell.closest('tr').data('performance-id');
                const fieldName = cell.data('field-name');
                $.ajax({
                    type: 'POST',
                    url: 'update_performance.php', // Your backend script to handle updates
                    data: {
                        performance_id: performanceId,
                        field_name: fieldName,
                        new_value: newValue
                    },
                    success: function(response) {
                        alert('Data updated successfully');
                    },
                    error: function() {
                        alert('Error updating data. Please try again later.');
                    },
                });
            });
        });
    }

    attachEditableHandler();

    // Add a new data row when the "Add Data Row" button is clicked
    $('#addDataRow').click(function() {
        const newRow = $('<tr>');
        newRow.append($('<td>').text('New Entry'));

        for (let i = 1; i <= 10; i++) {
            newRow.append($('<td>').addClass('editable').attr('data-field-name', 'score' + i).text(''));
        }

        $('table').append(newRow);
        attachEditableHandler();
    });
});
</script>

<h1>Student Performance Data</h1>
<button id="addDataRow">Add Data Row</button>
<table border="1">
    <tr>
        <th>Week Start Date</th>
        <th>Score1</th>
        <th>Score2</th>
        <th>Score3</th>
        <th>Score4</th>
        <th>Score5</th>
        <th>Score6</th>
        <th>Score7</th>
        <th>Score8</th>
        <th>Score9</th>
        <th>Score10</th>
    </tr>

    <?php 
    if (empty($performanceData)) {
        echo "<tr><td colspan='11'>No Data. Click 'Add Data Row' to add new data.</td></tr>";
    } else {
        foreach ($performanceData as $data) : ?>
            <tr data-performance-id="<?php echo $data['performance_id']; ?>">
                <td><?php echo $data['week_start_date']; ?></td>
                <td class="editable" data-field-name="score1"><?php echo $data['score1']; ?></td>
                <td class="editable" data-field-name="score2"><?php echo $data['score2']; ?></td>
                <td class="editable" data-field-name="score3"><?php echo $data['score3']; ?></td>
                <td class="editable" data-field-name="score4"><?php echo $data['score4']; ?></td>
                <td class="editable" data-field-name="score5"><?php echo $data['score5']; ?></td>
                <td class="editable" data-field-name="score6"><?php echo $data['score6']; ?></td>
                <td class="editable" data-field-name="score7"><?php echo $data['score7']; ?></td>
                <td class="editable" data-field-name="score8"><?php echo $data['score8']; ?></td>
                <td class="editable" data-field-name="score9"><?php echo $data['score9']; ?></td>
                <td class="editable" data-field-name="score10"><?php echo $data['scor10']; ?></td>

                <!-- Add other columns here -->
            </tr>
        <?php endforeach; 
    }
    ?>
</table>

</body>
</html>



