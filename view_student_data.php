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
    <?php foreach ($scoreNames as $key => $name): ?>
        <option value="<?php echo $key; ?>"><?php echo htmlspecialchars($name); ?></option>
    <?php endforeach; ?>
</select>


<label>Enter Benchmark Value: </label>
<input type="text" id="benchmarkValue">
<button id="updateBenchmark">Update Benchmark</button>

<div id="chart"></div>  <!-- Div to display the chart -->

<script>
var benchmark = null;

$(document).ready(function() {
    initializeChart();

    benchmark = parseFloat($("#benchmarkValue").val());
    if (isNaN(benchmark)) {
        benchmark = null;  // Default benchmark value if the input is not provided
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
                x: weekStartDate,  // Directly use the date string
                y: parseFloat(scoreValue)
            });

            xCategories.push(weekStartDate);
        }
    });

    //if (benchmark === null) {
    //    benchmark = 0; // Default value if benchmark is not set
    //}
    chartData.reverse();
    xCategories.reverse();  
    return {chartData, xCategories};
}


function updateChart(scoreField) {
    var {chartData, xCategories} = getChartData(scoreField);

    // Calculate trendline
    var trendlineFunction = calculateTrendline(chartData);
    var trendlineData = chartData.map((item, index) => {
        return {
            x: item.x,
            y: trendlineFunction(index)
        };
    });

    var seriesData = [
        {
            name: 'Selected Score',
            data: chartData,
            connectNulls: true,  // Add this line here
            stroke: {
                width: 7  // Adjust this value to your desired thickness
            }
        },
        {
            name: 'Trendline',
            data: trendlineData,
            connectNulls: true  // And add it here as well, if you want to connect null values for the trendline too
        }
    ];

    if (benchmark !== null) {
    var benchmarkData = xCategories.map(date => {
        return {
            x: date,
            y: benchmark
        };
    }).reverse();
    seriesData.push({
        name: 'Benchmark',
        data: benchmarkData,
        connectNulls: true  // Keep this if you want to connect null values for the benchmark series
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

        dataLabels: {
    enabled: true,
    enabledOnSeries: [0],  // enable only on the first series
    offsetY: -5,
    style: {
        fontSize: '12px',
        colors: ['#333']
    }
},

        stroke: {
            curve: 'smooth',
            width: dataSeries.map(series => series.name === 'Selected Score' ? 3 : 1)  // Set width based on series name
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
                type: 'category', 
                categories: xCategories,
            labels: {
                hideOverlappingLabels: false,
                formatter: function(value) {
                    return value;  // Simply return the value since we're not working with timestamps anymore
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

    data.forEach(function (point, index) {
        var x = index; // Use index as the x value
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
                        //const scoreValue = row.find(`td[data-field-name="score${i}"]`).text();
                        scores['score' + i] = scoreValue ? scoreValue : null; // Send null if score is empty
                    }
                    postData.scores = scores;
                }

                $.ajax({
                    type: 'POST',
                    url: targetUrl,
                    data: postData,
                    success: function(response) {
                        if (performanceId === 'new') {
                            const newRow = $('tr[data-performance-id="new"]');
                            newRow.attr('data-performance-id', response.performance_id);
                            newRow.find('td[data-field-name="week_start_date"]').text(convertToDisplayDate(response.saved_date));
                        }
    
    // New code for updating score8 starts here
                        if (['score1', 'score2', 'score3', 'score4'].includes(fieldName)) {
                            const row = cell.closest('tr');
                            const score1 = parseFloat(row.find('td[data-field-name="score1"]').text()) || 0;
                            const score2 = parseFloat(row.find('td[data-field-name="score2"]').text()) || 0;
                            const score3 = parseFloat(row.find('td[data-field-name="score3"]').text()) || 0;
                            const score4 = parseFloat(row.find('td[data-field-name="score4"]').text()) || 0;
                            const average = (score1 + score2 + score3 + score4) / 4;
                            row.find('td[data-field-name="score8"]').text(average.toFixed(2)); // Format the result to 2 decimal places
                            // Update the score8 value in the database
                            updateScoreInDatabase(row, 'score8', average.toFixed(2));
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

    function updateScoreInDatabase(row, fieldName, newValue) {
        const performanceId = row.data('performance-id');
        const studentId = $('#currentStudentId').val();
        const weekStartDate = convertToDatabaseDate($('#currentWeekStartDate').val());

    $.ajax({
        type: 'POST',
        url: 'update_performance.php',
        data: {
            performance_id: performanceId,
            field_name: fieldName,
            new_value: newValue,
            student_id: studentId,
            week_start_date: weekStartDate
        },
        success: function(response) {
            if (response && !response.success) {
                //alert("Error updating the average score in the database.");
            }
        },
        error: function() {
            alert("There was an error updating the average score in the database.");
        }
    });
}



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

// Inside the `$('#addDataRow').click` function:
newRow.append('<td><button class="saveRow">Save</button></td>');


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

    $(document).on('click', '.deleteRow', function() {
    const row = $(this);  // Capture the button element for later use
    const performanceId = $(this).data('performance-id');
    
    // Confirm before delete
    if (!confirm('Are you sure you want to delete this row?')) {
        return;
    }
    
    // Send a request to delete the data from the server
    $.post('delete_performance.php', {
        performance_id: performanceId
    }, function(response) {
        // Handle the response, e.g., check if the deletion was successful
        if (response.success) {
            // Remove the corresponding row from the table
            row.closest('tr').remove();
        } else {
            alert('Failed to delete data. Please try again.');
        }
    }, 'json');
});

$(document).on('click', '.saveRow', function() {
    const row = $(this).closest('tr');
    const performanceId = row.data('performance-id');
    
    if (performanceId === 'new') {
        let postData = {
            performance_id: performanceId,
            student_id: $('#currentStudentId').val(),
            week_start_date: convertToDatabaseDate(row.find('td[data-field-name="week_start_date"]').text())
        };
        
        let scores = {};
        for (let i = 1; i <= 10; i++) {
            const scoreValue = row.find(`td[data-field-name="score${i}"]`).text();
            scores['score' + i] = scoreValue ? scoreValue : null; // Send null if score is empty
        }
        postData.scores = scores;

        $.ajax({
            type: 'POST',
            url: 'insert_performance.php',
            data: postData,
            success: function(response) {
                row.attr('data-performance-id', response.performance_id);
                row.find('td[data-field-name="week_start_date"]').text(convertToDisplayDate(response.saved_date));
                // New code for updating score8 starts here
                const score1 = parseFloat(row.find('td[data-field-name="score1"]').text()) || 0;
                const score2 = parseFloat(row.find('td[data-field-name="score2"]').text()) || 0;
                const score3 = parseFloat(row.find('td[data-field-name="score3"]').text()) || 0;
                const score4 = parseFloat(row.find('td[data-field-name="score4"]').text()) || 0;
                
                const average = (score1 + score2 + score3 + score4) / 4;
                row.find('td[data-field-name="score8"]').text(average.toFixed(2)); // Format the result to 2 decimal places
                // Update the score8 value in the database
                updateScoreInDatabase(row, 'score8', average.toFixed(2));
                // Once saved, you might want to disable the save button or replace it with some other control.
                row.find('.saveRow').prop('disabled', true);
            },
            error: function() {
                alert("There was an error saving the new row.");
            }
        });
    }
});


});
</script>

</body>
</html>