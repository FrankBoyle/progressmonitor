<?php 
include('./users/fetch_data.php'); 

// Define and initialize the $columnHeaders array based on your table structure
$columnHeaders = [
    'score1_name',
    'score2_name',
    'score3_name',
    'score4_name',
    'score5_name',
    'score6_name',
    'score7_name',
    'score8_name',
    'score9_name',
    'score10_name'
];

// Replace with your actual SchoolID (e.g., 1) and fetch metadata entries for that SchoolID
$schoolID = '';

// Fetch metadata entries from the Metadata table for the specified SchoolID
$metadataEntries = [];
$stmt = $connection->prepare("SELECT metadata_id, category_name FROM Metadata WHERE SchoolID = ?");
$stmt->execute([$schoolID]);

// Populate the $metadataEntries array with fetched data
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $metadataEntries[] = $row;
}

// Close the database connection if needed
// $connection = null; // Uncomment if required
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

<style>
    .dataTables_filter {
        display: none;
    }
</style>
</head>
<body>

<input type="hidden" id="currentStudentId" value="<?php echo htmlspecialchars($studentId); ?>" />

<a href="test.php" class="btn btn-primary">Student List</a>

<h1>Student Performance Data</h1>
<button id="addDataRow">Add Data Row</button>

<label for="startDateFilter">Filter by Start Date:</label>
<input type="text" id="startDateFilter">


<label>Select Metadata Group to Display: </label>
<select id="metadataIdSelector">
    <?php foreach ($metadataEntries as $entry): ?>
        <option value="<?php echo $entry['metadata_id']; ?>"><?php echo $entry['category_name']; ?></option>
    <?php endforeach; ?>
</select>

<table border="1">
    <thead>
        <tr>
            <th>Week Start Date</th>
            <!-- Dynamically generate table headers based on columnHeaders data -->
            <?php foreach ($columnHeaders as $key => $name): ?>
                <th><?php echo isset($name) ? $name : ''; ?></th>
            <?php endforeach; ?>
            <th>Action</th>
        </tr>
    </thead>

    <?php if (empty($performanceData)): ?>
        <tr>
            <td colspan="<?php echo count($columnHeaders) + 2; ?>">No Data Found. Click "Add Data Row" to add new data.</td>
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
                <!-- Dynamically generate table cells for scores based on columnHeaders data -->
                <?php foreach ($columnHeaders as $key => $name): ?>
                    <td class="editable" data-field-name="<?php echo $key; ?>">
                        <?php
                        if (isset($data[$key])) {
                            echo $data[$key];
                        }
                        ?>
                    </td>
                <?php endforeach; ?>
                <td><button class="deleteRow" data-performance-id="<?php echo $data['performance_id']; ?>">Delete</button></td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
</table>
<?php
} else {
    echo "No column headers or metadata entries available.";
}
?>


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


</body>
</html>