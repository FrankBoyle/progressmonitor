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
<p>Category: <?php echo $metadataEntry['category_name']; ?></p>

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
<?php
echo '<pre>';
print_r($scoreNames);
echo '</pre>';
?>
<label>Select Score to Display: </label>
<select id="scoreSelector" name="scoreSelector"> <!-- Added 'name' attribute for form submission -->
            <?php
            // Check if there are categories and scores
            if (!empty($scoreNames)) {
                // Loop through each category and its scores
                foreach ($scoreNames as $category => $scores) {
                    foreach ($scores as $index => $scoreName) {
                        // Creating the option element
                        echo '<option value="' . htmlspecialchars('score' . ($index + 1)) . '">';
                        echo htmlspecialchars($scoreName);
                        echo '</option>';
                    }
                }
            } else {
                // In case there are no scores, an option to reflect that
                echo '<option value="">No scores available</option>';
            }
            ?>
        </select>




<label>Enter Benchmark Value: </label>
<input type="text" id="benchmarkValue">
<button id="updateBenchmark">Update Benchmark</button>

<div id="chart"></div>  <!-- Div to display the chart -->


</body>
</html>