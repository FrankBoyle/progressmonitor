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

// Preparing the data for the chart
$chartDates = [];
$chartScores = [];

foreach ($performanceData as $record) {
    $chartDates[] = $record['week_start_date'];
    
    // Computing average score for simplicity, adjust as needed
    $totalScore = 0;
    for($i = 1; $i <= 10; $i++) {
        $totalScore += $record['score'.$i];
    }
    $avgScore = $totalScore / 10;
    $chartScores[] = $avgScore;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Performance Data</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.0/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/ui/1.13.0/jquery-ui.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>

    <!-- ApexCharts integration -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
</head>

<body>

<input type="hidden" id="currentStudentId" value="<?php echo htmlspecialchars($studentId); ?>" />
<input type="hidden" id="currentWeekStartDate" value="" />

<!-- Adding hidden divs for chart data -->
<div id="chartDates" style="display:none;"><?php echo json_encode($chartDates); ?></div>
<div id="chartScores" style="display:none;"><?php echo json_encode($chartScores); ?></div>

<script>
$(document).ready(function() {

function isValidDate(d) {
    return d instanceof Date && !isNaN(d);
}

function convertToDatabaseDate(dateString) {
    if (!dateString || dateString === "New Entry") {
        return dateString;
    }
    const parts = dateString.split('/');
    if (parts.length !== 3) {
        return dateString;
    }
    return `${parts[2]}-${parts[0]}-${parts[1]}`;
}

function attachEditableHandler() {
    $('.editable').off('click').on('click', function() {
        const cell = $(this);
        const originalValue = cell.text();
        let input = $('<input type="text">');
        
        input.val(originalValue);
        
        if (cell.data('field-name') === 'week_start_date') {
            input.mask('00/00/0000');
        }
        
        cell.html(input);
        input.focus();

        input.blur(function() {
    let newValue = input.val();
    if (cell.data('field-name') === 'week_start_date') {
        const parts = newValue.split('/');
        const constructedDate = new Date(parts[2], parts[0] - 1, parts[1]);
        if (isValidDate(constructedDate)) {
            cell.text(newValue);
            newValue = convertToDatabaseDate(newValue); // Convert to the correct format for database
        } else {
            alert('Invalid date. Please ensure the date is in MM/DD/YYYY format.');
            cell.text(originalValue);
            return;
        }
    } else {
        cell.text(newValue);
    }

            const performanceId = cell.closest('tr').data('performance-id');
            const fieldName = cell.data('field-name');
            const targetUrl = (performanceId === 'new') ? 'insert_performance.php' : 'update_performance.php';

            const studentId = $('#currentStudentId').val();  
            const weekStartDate = convertToDatabaseDate($('#currentWeekStartDate').val());

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

            $.ajax({
                type: 'POST',
                url: targetUrl,
                data: postData,
                success: function(response) {
                    if (performanceId === 'new') {
                        // Update the new row's performance-id with the ID returned from the server
                        const newRow = $('tr[data-performance-id="new"]');
                        newRow.attr('data-performance-id', response.performance_id);
                    }
                    alert('Data added successfully');
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

    // Set the current week start date for the new row, if needed
    const currentDate = new Date();
    const formattedDate = (currentDate.getMonth() + 1).toString().padStart(2, '0') + '/' +
                          currentDate.getDate().toString().padStart(2, '0') + '/' + 
                          currentDate.getFullYear();
    $('#currentWeekStartDate').val(formattedDate);
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
            <td class="editable" data-field-name="week_start_date"><?php echo date("m/d/Y", strtotime($data['week_start_date'])); ?></td>
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
<script src="apexcharts-render.js"></script>

<div id="chart"></div>
<script>
// Including ApexCharts
document.write('<script src="https://cdn.jsdelivr.net/npm/apexcharts"><\/script>');

document.addEventListener('DOMContentLoaded', function() {
    // Fetch data from PHP and convert to Javascript arrays
    var chartDates = JSON.parse(document.getElementById('chartDates').textContent);
    var chartScores = JSON.parse(document.getElementById('chartScores').textContent);

    // Setting up the options for the chart
    var options = {
        series: [{
            name: 'Average Score',
            data: chartScores
        }],
        chart: {
            height: 350,
            type: 'line',
            zoom: {
                enabled: false
            }
        },
        dataLabels: {
            enabled: false
        },
        stroke: {
            curve: 'smooth'
        },
        xaxis: {
            type: 'datetime',
            categories: chartDates.map(date => new Date(date).getTime())
        },
        tooltip: {
            x: {
                format: 'dd/MM/yy HH:mm'
            },
        },
    };

    // Render the chart
    var chart = new ApexCharts(document.querySelector("#chart"), options);
    chart.render();
});
</script>
</body>
</html>


