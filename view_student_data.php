<?php
// Include database connection and error reporting settings
include('./users/db.php');

// Error Reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Initialize an empty array for performanceData
$performanceData = array();

// Check if `student_id` is provided in the URL
if (isset($_GET['student_id'])) {
    $studentId = $_GET['student_id'];

    $stmt = $connection->prepare("SELECT * FROM Performance WHERE student_id = ? ORDER BY week_start_date DESC LIMIT 41");
    $stmt->execute([$studentId]);

    $performanceData = $stmt->fetchAll();
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

<input type="hidden" id="currentStudentId" value="<?php echo htmlspecialchars($studentId); ?>" />
<input type="hidden" id="currentWeekStartDate" value="" />

<script>
$(document).ready(function() {

function attachEditableHandler() {
    $('.editable').off('click').on('click', function() {
        const cell = $(this);
        const originalValue = cell.text();
        const input = $('<input type="text">');
        input.val(originalValue);
        cell.html(input);
        input.focus();

        input.blur(function() {
            const newValue = input.val();
            cell.text(newValue);

            const performanceId = cell.closest('tr').data('performance-id');
            const fieldName = cell.data('field-name');
            const targetUrl = (performanceId === 'new') ? 'insert_performance.php' : 'update_performance.php';

            const studentId = $('#currentStudentId').val();  
            const weekStartDate = $('#currentWeekStartDate').val();
            
let postData = {
    performance_id: performanceId,
    field_name: fieldName,
    new_value: newValue,
    student_id: studentId,
    week_start_date: weekStartDate
};

if (performanceId === 'new') {
    let scores = {};
    for (let i = 1; i <= 10; i++) {
        scores['score' + i] = $('tr[data-performance-id="new"]').find(`td[data-field-name="score${i}"]`).text();
    }
    postData.scores = scores;
}

            // Fetch student ID and week start date dynamically
            const studentId = $('#currentStudentId').val();  
            const weekStartDate = $('#currentWeekStartDate').val();

            $.ajax({ // <-- This is the replacement!
                    type: 'POST',
                    url: targetUrl,
                    data: postData,
                success: function(response) {
                    if (performanceId === 'new') {
                        // Optionally update the new row's performance-id with the ID returned from the server
                    }
                    alert('Data updated successfully');
                },
                error: function() {
                    alert('Error updating data. Please try again later.');
                }
            });
        });

        // Pressing Enter to save changes
        input.keypress(function(e) {
            if (e.which === 13) {
                input.blur();
            }
        });
    });
}

attachEditableHandler();

$('#addDataRow').click(function() {
    const newRow = $('<tr data-performance-id="new">');
    newRow.append('<td class="editable" data-field-name="week_start_date">New Entry</td>');
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
        <th class="editable" data-field-name="week_start_date">Week Start Date</th>
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

        <!-- more headers here -->
    </tr>
    <?php if (empty($performanceData)) : ?>
        <tr><td colspan="11">No Data Found. Click "Add Data Row" to add new data.</td></tr>
    <?php else : ?>
        <?php foreach ($performanceData as $data) : ?>
            <tr data-performance-id="<?php echo $data['performance_id']; ?>">
                <td class="editable" data-field-name="week_start_date"><?php echo $data['week_start_date']; ?></td>
                <td class="editable" data-field-name="score1"><?php echo $data['score1']; ?></td>
                <td class="editable" data-field-name="score2"><?php echo $data['score2']; ?></td>
                <td class="editable" data-field-name="score3"><?php echo $data['score3']; ?></td>
                <td class="editable" data-field-name="score4"><?php echo $data['score4']; ?></td>
                <td class="editable" data-field-name="score5"><?php echo $data['score5']; ?></td>
                <td class="editable" data-field-name="score6"><?php echo $data['score6']; ?></td>
                <td class="editable" data-field-name="score7"><?php echo $data['score7']; ?></td>
                <td class="editable" data-field-name="score8"><?php echo $data['score8']; ?></td>
                <td class="editable" data-field-name="score9"><?php echo $data['score9']; ?></td>
                <td class="editable" data-field-name="score10"><?php echo $data['score10']; ?></td>

                <!-- Add other columns here -->
            </tr>

        <?php endforeach; ?>
    <?php endif; ?>
</table>

</body>
</html>



