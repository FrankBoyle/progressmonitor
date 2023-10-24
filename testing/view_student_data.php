<?php
include ('./users/fetch_data.php');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//$metadata_id = !empty($_GET['metadata_id']) ? $_GET['metadata_id'] : null;
//echo '<pre>';
//print_r($_GET);
//echo '</pre>';

foreach ($students as $student) {
    if ($student['student_id'] == $studentId) { // If the IDs match
        $studentName = $student['name']; // Get the student name
        break;
    }
}

if (isset($_GET['metadata_id'])) {
    $selectedMetadataId = $_GET['metadata_id'];

    // Now fetch the corresponding category name based on this metadata_id
    foreach ($metadataEntries as $metadataEntry) {
        if ($metadataEntry['metadata_id'] == $selectedMetadataId) {
            $selectedCategoryName = $metadataEntry['category_name'];
            break; // We found our category, no need to continue the loop
        }
    }
} else {
    // Optional: Handle cases where no metadata_id is specified, if needed
    // $selectedCategoryName = "Default Category or message"; // for example
}
?>

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
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.css">
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="student_data.js"></script>
    
    <script>
    // Get the metadata_id from the URL parameter
    const urlParams = new URLSearchParams(window.location.search);
    const metadata_id = urlParams.get('metadata_id');
    </script>

<style>
    .dataTables_filter {
        display: none;
    }
</style>
</head>
<body>
<h1>Student Overview</h1>
<p>Name: <?php echo $studentName; ?></p>
<p>Category: <?php echo $selectedCategoryName; ?></p>

<a href="test.php" class="btn btn-primary">Student List</a>
<input type="hidden" id="schoolIdInput" name="school_id" value="<?php echo htmlspecialchars($school_id); ?>">
<input type="hidden" id="currentStudentId" value="<?php echo htmlspecialchars($studentId); ?>" />
<input type="hidden" id="currentWeekStartDate" value="<?php echo htmlspecialchars($currentWeekStartDate); ?>" />

<h1>Student Performance Data</h1>
<button id="addDataRow">Add Data Row</button>

<label for="startDateFilter">Filter by Start Date:</label>
<input type="text" id="startDateFilter">

<!-- Add the generated links here -->
<?php foreach ($metadataEntries as $metadataEntry): ?>
    <a href="?student_id=<?php echo $student_id; ?>&metadata_id=<?php echo $metadataEntry['metadata_id']; ?>">
        <?php echo $metadataEntry['category_name']; ?>
    </a><br>
<?php endforeach; ?>

<table border="1">
<thead>
    <tr>
        <th>Date</th>
        <?php 
        // Iterate through all key-value pairs in $scoreNames.
        foreach ($scoreNames as $category => $values) {
            // Check if the current category's values are an array (assuming you only want arrays).
            if (is_array($values)) {
                // Iterate through each item in the current category's array.
                foreach ($values as $score) {
                    // Print the score as a table header. Apply any necessary formatting or escaping here.
                    echo "<th>" . htmlspecialchars($score) . "</th>";
                }
            } else {
                // If it's not an array, it might be a standalone category name. You can decide how to handle these cases.
                // For example, you might want to print it as a header, too.
                echo "<th>" . htmlspecialchars($values) . "</th>";
            }
        }
        ?>
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
                <td class="editable" data-field-name="score_date">
                    <?php
                    if (isset($data['score_date'])) {
                        echo date("m/d/Y", strtotime($data['score_date']));
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

<div>
    <label>Show Trendlines:</label>
    <input type="checkbox" id="toggleTrendlines" checked> <!-- Checked by default -->
</div>

<!-- Existing checkboxes for column selection -->
<div>
    <label>Select Columns to Display:</label>
    <!-- Existing PHP code to generate checkboxes -->
    <?php
    foreach ($scoreNames as $category => $scores) {
        foreach ($scores as $index => $scoreName) {
            $scoreColumnName = 'score' . ($index + 1);
            echo '<label>';
            echo '<input type="checkbox" name="selectedColumns[]" value="' . htmlspecialchars($scoreColumnName) . '">';
            echo htmlspecialchars($scoreName);
            echo '</label>';
        }
    }
    ?>
</div>

<label>Enter Benchmark Value:</label>
<input type="text" id="benchmarkValue">
<button type ="button" id="updateBenchmark">Update Benchmark</button>
<div>
    <label>Select Chart Type:</label>
    <label>
        <input type="radio" name="chartType" value="line" checked>
        Line Chart
    </label>
    <label>
        <input type="radio" name="chartType" value="bar">
        Bar Chart
    </label>
</div>
<div id="chart"></div> <!-- Div to display the chart -->

<!-- Radio buttons to select chart type -->

<div id="chart-container">
        <canvas id="myChart"></canvas>
    </div>
    <script>
        var chart;
        var xCategories = [];
        var benchmark = null;

        // Sample data (you should replace this with your actual data)
        var chartData = {
            labels: [], // x-axis labels
            datasets: [] // Data series
        };

        // Initialize the chart
        initializeChart();

        // Update the chart when the "Update Benchmark" button is clicked
        document.getElementById('updateBenchmark').addEventListener('click', function () {
            var newBenchmarkValue = parseFloat(document.getElementById('benchmarkValue').value.trim());

            if (isNaN(newBenchmarkValue)) {
                alert("Invalid benchmark value. Please enter a number.");
            } else {
                benchmark = newBenchmarkValue;
                updateChart();
            }
        });

        // Function to initialize the chart
        function initializeChart() {
            if (chart) {
                chart.destroy(); // Destroy the existing chart if it exists
            }

            var ctx = document.getElementById('myChart').getContext('2d');

            chart = new Chart(ctx, {
                type: 'line', // Default chart type
                data: chartData,
                options: getChartOptions()
            });

            // Initialize the chart with the current selections
            updateChart();
        }

        // Function to update the chart
        function updateChart() {
            // Update the chart data and options here based on your selectedColumns and benchmark values
            // Sample code to update chart data:
            chartData.labels = xCategories;
            
            // Sample selected columns
            var selectedColumns = ['Series 1', 'Series 2'];

            updateChartData(selectedColumns);

            // Sample code to update chart options:
            chart.options = getChartOptions();

            // Update the chart
            chart.update();
        }

        // Function to update the chart data based on selected columns
        function updateChartData(selectedColumns) {
            var updatedDataSets = [];

            selectedColumns.forEach(function (selectedColumn, index) {
                var chartDataSet = {
                    label: selectedColumn,
                    data: [], // Fill this array with your data values
                    borderColor: getRandomColor(),
                    fill: false
                };

                // Sample data values (replace with your data)
                if (selectedColumn === 'Series 1') {
                    chartDataSet.data = [1, 2, 3, 4, 5];
                } else if (selectedColumn === 'Series 2') {
                    chartDataSet.data = [5, 4, 3, 2, 1];
                }

                updatedDataSets.push(chartDataSet);
            });

            chartData.datasets = updatedDataSets;
        }

        // Function to generate random colors (you can modify this)
        function getRandomColor() {
            var letters = '0123456789ABCDEF';
            var color = '#';
            for (var i = 0; i < 6; i++) {
                color += letters[Math.floor(Math.random() * 16)];
            }
            return color;
        }

        // Function to get chart options
        function getChartOptions() {
            return {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        type: 'category',
                        labels: xCategories,
                        title: {
                            display: true,
                            text: 'Date'
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Value'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                }
                // Add more chart options as needed
            };
        }
    </script>
</body>
</html>