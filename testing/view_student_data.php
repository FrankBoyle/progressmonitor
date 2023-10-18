<?php 
include('./users/fetch_data.php'); 
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
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="student_data.js"></script>

<style>
    .dataTables_filter {
        display: none;
    }
</style>
</head>
<body>
<input type="hidden" id="currentStudentId" value="<?php echo htmlspecialchars($studentid); ?>" />

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

<!-- Add the generated links here -->
<?php
// Output the links to tables for each metadata entry
foreach ($metadataEntries as $metadataEntry) {
    $metadataId = $metadataEntry['metadata_id'];
    $categoryName = $metadataEntry['category_name'];

    // Generate a link to the table for this metadata entry
    echo "<a href='table.php?student_id=$student_id&metadata_id=$metadataId'>$categoryName</a><br>";
}
?>


<table id="myDataTable" border="1">
<thead>
    <tr>
        <th>Date</th>
        <!-- Dynamically generate table headers based on $displayedColumns -->
        <?php foreach ($displayedColumns as $columnName => $displayName): ?>
            <?php if ($columnName !== "Date"): ?>
                <th><?php echo isset($scoreNames[$columnName]) ? $scoreNames[$columnName] : $displayName; ?></th>
            <?php endif; ?>
        <?php endforeach; ?>
        <th>Action</th>
    </tr>
</thead>
<?php if (!empty($performanceData)): ?>
    <?php foreach ($performanceData as $data): ?>
        <tr data-performance-id="<?php echo $data['performance_id']; ?>">
            <td class="editable" data-field-name="score_date">
                <?php echo isset($data['score_date']) ? date("m/d/Y", strtotime($data['score_date'])) : ''; ?>
            </td>
            <!-- Dynamically generate table cells for scores based on $displayedColumns -->
            <?php foreach ($displayedColumns as $columnName => $columnLabel): ?>
                <?php if ($columnName !== "Date"): ?>
                    <td class="editable" data-field-name="<?php echo $columnName; ?>">
                        <?php echo isset($data[$columnName]) ? $data[$columnName] : ''; ?>
                    </td>
                <?php endif; ?>
            <?php endforeach; ?>
            <td><button class="deleteRow" data-performance-id="<?php echo $data['performance_id']; ?>">Delete</button></td>
        </tr>
    <?php endforeach; ?>
<?php else: ?>
    <tr>
        <td colspan="<?php echo count($displayedColumns) + 1; ?>">No data available.</td>
    </tr>
<?php endif; ?>
</table>


<label>Select Score to Display: </label>
<select id="scoreSelector">
    <?php foreach ($displayedColumns as $columnName => $columnLabel): ?>
        <?php if ($columnName !== "Date"): ?>
            <option value="<?php echo $columnName; ?>"><?php echo htmlspecialchars($columnLabel); ?></option>
        <?php endif; ?>
    <?php endforeach; ?>
</select>

<label>Enter Benchmark Value: </label>
<input type="text" id="benchmarkValue">
<button id="updateBenchmark">Update Benchmark</button>

<div id="chart"></div>  <!-- Div to display the chart -->


</body>
</html>