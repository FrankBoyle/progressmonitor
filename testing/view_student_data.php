<?php include('./users/fetch_data.php'); ?>
<?php $currentWeekStartDate = date('Y-m-d', strtotime('monday this week'));  // Adjust the date format as needed ?>
<input type="hidden" id="currentStudentId" value="<?php echo htmlspecialchars($studentId); ?>" />
<input type="hidden" id="currentWeekStartDate" value="<?php echo htmlspecialchars($currentWeekStartDate); ?>" />

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

<a href="test.php" class="btn btn-primary">Student List</a>

<h1>Student Performance Data</h1>
<button id="addDataRow">Add Data Row</button>

<label for="startDateFilter">Filter by Start Date:</label>
<input type="text" id="startDateFilter">

<label>Select Metadata Group to Display: </label>
<select id="metadataIdSelector">
    <?php foreach ($metadataEntries as $entry): ?>
        <!-- Continue using htmlspecialchars for escaping to prevent XSS -->
        <option value="<?php echo htmlspecialchars($entry['metadata_id'], ENT_QUOTES, 'UTF-8'); ?>">
            <?php echo htmlspecialchars($entry['category_name'], ENT_QUOTES, 'UTF-8'); ?>
        </option>
    <?php endforeach; ?>
</select>

<table border="1" id="performanceDataTable">
    <thead>
        <tr>
            <th>Date</th>
            <?php foreach ($scoreNames as $name): // Ensure $scoreNames is fetched or defined earlier ?>
                <th><?php echo htmlspecialchars($name); ?></th>
            <?php endforeach; ?>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($performanceData)): ?>
            <tr>
                <td colspan="<?php echo count($scoreNames) + 2; ?>">No Data Found. Click "Add Data Row" to add new data.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($performanceData as $data): ?>
                <tr data-performance-id="<?php echo htmlspecialchars($data['performance_id']); ?>">
                    <td class="editable" data-field-name="score_date"><?php echo date("m/d/Y", strtotime($data['score_date'])); ?></td>
                    
                    <?php
                    // Assuming your scores are in columns named score1, score2, etc.
                    for ($i = 1; $i <= 10; $i++): // Adjust if you have more scores
                        $scoreField = 'score' . $i;
                    ?>
                        <td class="editable" data-field-name="<?php echo $scoreField; ?>">
                            <?php echo isset($data[$scoreField]) ? htmlspecialchars($data[$scoreField]) : ''; ?>
                        </td>
                    <?php endfor; ?>
                    
                    <td>
                        <button class="deleteRow" data-performance-id="<?php echo htmlspecialchars($data['performance_id']); ?>">Delete</button>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
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


</body>
</html>