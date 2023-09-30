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
<input type="hidden" id="currentWeekStartDate" value="" />

<div id="chartDates" style="display:none;"><?php echo json_encode($chartDates); ?></div>
<div id="chartScores" style="display:none;"><?php echo json_encode($chartScores); ?></div>

<h1>Student Performance Data</h1>
<button id="addDataRow">Add Data Row</button>

<table border="1">
    <tr>
        <th class="editable" data-field-name="week_start_date">Week Start Date</th>
        <!-- Add other headers using loop based on score names-->
        <?php foreach ($scoreNames as $key => $name): ?>
            <th class="editable" data-field-name="<?php echo $key; ?>"><?php echo $name; ?></th>
        <?php endforeach; ?>
    </tr>
    <?php if (empty($performanceData)): ?>
        <tr>
            <td colspan="11">No Data Found. Click "Add Data Row" to add new data.</td>
        </tr>
    <?php else: ?>
        <?php foreach ($performanceData as $data): ?>
            <tr data-performance-id="<?php echo $data['performance_id']; ?>">
                <td class="editable" data-field-name="week_start_date"><?php echo date("m/d/Y", strtotime($data['week_start_date'])); ?></td>
                <!-- Add scores using loop -->
                <?php for ($i = 1; $i <= 10; $i++): ?>
                    <td class="editable" data-field-name="score<?php echo $i; ?>"><?php echo $data['score'.$i]; ?></td>
                <?php endfor; ?>
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
var benchmark = null;

$(document).ready(function() {

    // Initialize the chart with empty data
    var chart = new ApexCharts(document.querySelector("#chart"), getChartOptions([], []));
    chart.render();

    // Update chart when score selection changes
    $("#scoreSelector").change(function() {
        var selectedScore = $(this).val();
        updateChart(chart, selectedScore);
    });

    // Update the chart when benchmark value changes
    $("#updateBenchmark").click(function() {
        var value = parseFloat($("#benchmarkValue").val());
        if (!isNaN(value)) {
            benchmark = value;
            var selectedScore = $("#scoreSelector").val();
            updateChart(chart, selectedScore);  // Re-render the chart with the benchmark
        } else {
            alert('Please enter a valid benchmark value.');
        }
    });

    // Automatically update chart with default score1 data on page load
    updateChart(chart, 'score1');
});

function updateChart(chart, scoreField) {
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

    // Calculate trendline
    var trendlineFunction = calculateTrendline(chartData);
    var trendlineData = chartData.map(item => {
        return {
            x: item.x,
            y: trendlineFunction(item.x)
        };
    });

    var benchmarkData = xCategories.map(date => {
        return {
            x: new Date(date).getTime(),
            y: benchmark
        };
    });

    // Update chart series data and X categories
    chart.updateOptions(getChartOptions([
        {
            name: 'Selected Score',
            data: chartData
        },
        {
            name: 'Trendline',
            data: trendlineData
        },
        {
            name: 'Benchmark',
            data: benchmarkData
        }
    ], xCategories));
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

</body>
</html>