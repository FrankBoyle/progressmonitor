<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Performance Data</title>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.0/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.0/jquery-ui.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
</head>
<body>

<?php include('./users/fetch_data.php'); ?>

<input type="hidden" id="currentStudentId" value="<?php echo htmlspecialchars($studentId); ?>" />
<input type="hidden" id="currentWeekStartDate" value="<?php echo htmlspecialchars($currentWeekStartDate); ?>" />

<div id="chartDates" style="display:none;"><?php echo json_encode($chartDates); ?></div>
<div id="chartScores" style="display:none;"><?php echo json_encode($chartScores); ?></div>

<h1>Student Performance Data</h1>
<button id="addDataRow">Add Data Row</button>

<table border="1">
    <thead>
        <tr>
            <th>Week Start Date</th>
            <?php foreach ($scoreNames as $key => $name): ?>
                <th><?php echo $name; ?></th>
            <?php endforeach; ?>
            <th>Action</th>
        </tr>
    </thead>

    <?php if (empty($performanceData)): ?>
        <tr>
            <td colspan="11">No Data Found. Click "Add Data Row" to add new data.</td>
        </tr>
    <?php else: ?>
        <?php foreach ($performanceData as $data): ?>
            <tr data-performance-id="<?php echo $data['performance_id']; ?>">
                <td class="editable" data-field-name="week_start_date">
                    <?php
                    if (isset($data['week_start_date'])) {
                        echo date("m/d/Y", strtotime($data['week_start_date']));
                    }
                    ?>
                </td>
                <!-- Add scores using loop -->
                <?php for ($i = 1; $i <= 10; $i++): ?>
                    <td class="editable" data-field-name="score<?php echo $i; ?>">
                        <?php
                        if (isset($data['score'.$i])) {
                            echo $data['score'.$i];
                        }
                        ?>
                    </td>
                <?php endfor; ?>
                <td><button class="deleteRow" data-performance-id="<?php echo $data['performance_id']; ?>">Delete</button></td> <!-- New delete button for each row -->
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
</table>

<label>Select Score to Display: </label>
<select id="scoreSelector">
    <?php for ($i = 1; $i <= 10; $i++): ?>
        <option value="score<?php echo $i; ?>">Score <?php echo $i; ?></option>
    <?php endfor; ?>
</select>

<label>Enter Benchmark Value: </label>
<input type="text" id="benchmarkValue">
<button id="updateBenchmark">Update Benchmark</button>

<div id="chart"></div>  <!-- Div to display the chart -->

<script>
//var benchmark = null;

$(document).ready(function() {
    initializeChart();

    benchmark = parseFloat($("#benchmarkValue").val());
    if (isNaN(benchmark)) {
        benchmark = 0;  // Default benchmark value if the input is not provided
    }

    $("#scoreSelector").change(function() {
        var selectedScore = $(this).val();
        updateChart(selectedScore);
    });

    $("#updateBenchmark").click(function() {
        var value = parseFloat($("#benchmarkValue").val());
        if (!isNaN(value)) {
            benchmark = value;
            var selectedScore = $("#scoreSelector").val();
            updateChart(selectedScore);
        } else {
            alert('Please enter a valid benchmark value.');
        }
    });

    updateChart('score1');  // Default
});


function initializeChart() {
    window.chart = new ApexCharts(document.querySelector("#chart"), getChartOptions([], []));
    window.chart.render();
}

function getChartData(scoreField) {
    var chartData = [];
    var xCategories = [];

    $('tr[data-performance-id]').each(function() {
        var weekStartDate = $(this).find('td[data-field-name="week_start_date"]').text();
        var scoreValue = $(this).find(`td[data-field-name="${scoreField}"]`).text();

        if (weekStartDate !== 'New Entry' && !isNaN(parseFloat(scoreValue))) {
            chartData.push({
                x: new Date(weekStartDate).getTime(),
                y: parseFloat(scoreValue)
            });
            xCategories.push(weekStartDate);
        }
    });

    if (benchmark === null) {
        benchmark = 0; // Default value if benchmark is not set
    }
    
    return {chartData, xCategories};
}


function updateChart(scoreField) {
    var {chartData, xCategories} = getChartData(scoreField);

    // Calculate trendline
    var trendlineFunction = calculateTrendline(chartData);
    var trendlineData = chartData.map(item => {
        return {
            x: item.x,
            y: trendlineFunction(item.x)
        };
    });

    var seriesData = [
        {
            name: 'Selected Score',
            data: chartData
        },
        {
            name: 'Trendline',
            data: trendlineData
        }
    ];

    if (benchmark !== null) {
        var benchmarkData = xCategories.map(date => {
            return {
                x: new Date(date).getTime(),
                y: benchmark
            };
        });
        seriesData.push({
            name: 'Benchmark',
            data: benchmarkData
        });
    }

    window.chart.updateOptions(getChartOptions(seriesData, xCategories));
}


function getChartOptions(dataSeries, xCategories) {
    return {
        series: dataSeries,
        chart: {
            type: 'line',
            stacked: false,
            width: 1000,
            toolbar: {
                show: true,
                tools: {
                    download: false
                }
            },
            dropShadow: {
                enabled: true,
                color: '#000',
                top: 18,
                left: 7,
                blur: 10,
                opacity: 0.2
            }
        },
        stroke: {
            curve: 'smooth',
            width: [1, 1, 1]
        },
        markers: {
            size: 5,
            colors: undefined,
            strokeColors: '#fff',
            strokeWidth: 2,
            strokeOpacity: 0.9,
            strokeDashArray: 0,
            fillOpacity: 1,
            discrete: [],
            shape: "circle",
            radius: 2,
            offsetX: 0,
            offsetY: 0,
            onClick: undefined,
            onDblClick: undefined,
            showNullDataPoints: true,
            hover: {
                size: undefined,
                sizeOffset: 3
            }
        },
        xaxis: {
            categories: xCategories,
            type: 'datetime',
            tickAmount: xCategories.length,
            labels: {
                hideOverlappingLabels: false,
                formatter: function(value, timestamp, opts) {
                    return new Date(value).toLocaleDateString();
                }
            },
            title: {
                text: 'Date'
            }
        },
        yaxis: {
            title: {
                text: 'Value'
            },
            labels: {
                formatter: function(value) {
                    return value.toFixed(0);
                }
            }
        },
        grid: {
            xaxis: {
                lines: {
                    show: true
                }
            }
        },
        colors: ['#2196F3', '#FF5722', '#000000']
    };
}

function calculateTrendline(data) {
    var sumX = 0;
    var sumY = 0;
    var sumXY = 0;
    var sumXX = 0;
    var count = 0;

    data.forEach(function (point) {
        var x = point.x;
        var y = point.y;

        if (y !== null) {
            sumX += x;
            sumY += y;
            sumXY += x * y;
            sumXX += x * x;
            count++;
        }
    });

    var slope = (count * sumXY - sumX * sumY) / (count * sumXX - sumX * sumX);
    var intercept = (sumY - slope * sumX) / count;

    return function (x) {
        return slope * x + intercept;
    };
}
</script>


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

    function convertToDisplayDate(databaseString) {
        if (!databaseString || databaseString === "New Entry") {
            return databaseString;
        }
        const parts = databaseString.split('-');
        if (parts.length !== 3) {
            return databaseString;
        }
        return `${parts[1]}/${parts[2]}/${parts[0]}`;  // Convert to mm/dd/yyyy format
    }

    function attachEditableHandler() {
        $('.editable').off('click').on('click', function() {
            const cell = $(this);
            const originalValue = cell.text();
            const input = $('<input type="text">');
            input.val(originalValue);

            let datePickerActive = false;

            if (cell.data('field-name') === 'week_start_date') {
                input.datepicker({
                    dateFormat: 'mm/dd/yy',
                    beforeShow: function() {
                        datePickerActive = true;
                    },
                    onClose: function(selectedDate) {
                        if (isValidDate(new Date(selectedDate))) {
                            cell.text(selectedDate);  // Set the selected date
                            cell.append(input.hide());  // Hide the input to show the cell text
                            saveEditedDate(cell, selectedDate); // Save the edited date
                        }
                        datePickerActive = false;
                    }
                });
                cell.html(input);
                input.focus();
            } else {
                cell.html(input);
                input.focus();
            }

            input.blur(function() {
                if (datePickerActive) {
                    return;
                }

                let newValue = input.val();
                if (cell.data('field-name') === 'week_start_date') {
                    const parts = newValue.split('/');
                    if (parts.length !== 3) {
                        cell.html(originalValue);
                        return;
                    }
                    // Save the new value for the database but display the original mm/dd/yyyy format to the user
                    cell.html(newValue);  // The selected value from datepicker is already in mm/dd/yyyy format, so just display it
                    newValue = convertToDatabaseDate(newValue);  // Convert to yyyy-mm-dd format for database use
                    saveEditedDate(cell, newValue); // Save the edited date
                } else {
                    cell.html(newValue);
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

                            // Assuming your server response contains the saved date under the key 'saved_date'
                            // This updates the displayed date for the new row to the date that was saved in the database.
                            newRow.find('td[data-field-name="week_start_date"]').text(convertToDisplayDate(response.saved_date));
                            newRow.find('td[data-field-name="week_start_date"]').data('saved-date', response.saved_date);
                        }
                    },
                    error: function() {
                        // Handle any error here, e.g., show a notification to the user
                        alert("There was an error updating the data.");
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

    function saveEditedDate(cell, newDate) {
        const performanceId = cell.closest('tr').data('performance-id');
        const fieldName = cell.data('field-name');
        const targetUrl = 'update_performance.php';

        const studentId = $('#currentStudentId').val();

        let postData = {
            performance_id: performanceId,
            field_name: fieldName,
            new_value: convertToDatabaseDate(newDate), // Convert to yyyy-mm-dd format before sending
            student_id: studentId
        };

        $.ajax({
            type: 'POST',
            url: targetUrl,
            data: postData,
            success: function(response) {
                // Assuming your server response contains the saved date under the key 'saved_date'
                cell.data('saved-date', response.saved_date);
            },
            error: function() {
                // Handle any error here, e.g., show a notification to the user
                alert("There was an error saving the edited date.");
            }
        });
    }

    attachEditableHandler();

    $('#addDataRow').click(function() {
        // Check if there's already a "new" row
        if ($('tr[data-performance-id="new"]').length > 0) {
            alert("Please save the existing new entry before adding another one.");
            return;
        }

        // Your code to add a new row
        const currentDate = new Date();
        const formattedDate = (currentDate.getMonth() + 1).toString().padStart(2, '0') + '/' +
            currentDate.getDate().toString().padStart(2, '0') + '/' +
            currentDate.getFullYear();
        var newRow = $("<tr data-performance-id='new'>");
        newRow.append('<td class="editable" data-field-name="week_start_date">' + formattedDate + '</td>');  // Set the current date as default
        for (let i = 1; i <= 10; i++) {
            newRow.append('<td class="editable" data-field-name="score' + i + '"></td>');
        }
        $("table").append(newRow);

        // Automatically trigger saving for the new row's "Week Start Date"
        newRow.find('td[data-field-name="week_start_date"]').click().blur();
        saveEditedDate(newRow.find('td[data-field-name="week_start_date"]'), formattedDate); // Save the edited date

        attachEditableHandler();
    });

    const currentDate = new Date();
    const formattedDate = (currentDate.getMonth() + 1).toString().padStart(2, '0') + '/' +
        currentDate.getDate().toString().padStart(2, '0') + '/' +
        currentDate.getFullYear();
    $('#currentWeekStartDate').val(formattedDate);

    $.ajaxSetup({
        complete: function(xhr, status) {
            if (status !== 'success') {
                const response = xhr.responseJSON || {};
                const errorMsg = response.error || 'Unknown error';
                //alert(`There was an issue saving the data: ${errorMsg}`);
                console.error(`Error response from server:`, response);
            }
        }
    });

    $(document).on('click', '.delete-row', function() {
    var row = $(this).closest('tr');
    var performanceId = row.data('performance-id');
    if (confirm('Are you sure you want to delete this row?')) {
        $.ajax({
            type: 'POST',
            url: 'delete_performance.php',
            data: { performance_id: performanceId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    row.remove();
                    alert("Data deleted successfully!");
                } else {
                    alert("There was an error deleting the data: " + response.message);
                }
            },
            error: function() {
                alert("Error while sending request to server.");
            }
        });
    }
});

});
</script>


</body>
</html>
