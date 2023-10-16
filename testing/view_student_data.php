<?php 
include('./users/fetch_data.php'); 
// Initialize empty arrays and variables
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

$performanceData = [];
$scoreNames = [];
$chartDates = [];
$chartScores = [];

// Check if the action is set to 'fetchGroups' and handle it
if (isset($_GET['action']) && $_GET['action'] == 'fetchGroups') {
    echo json_encode(fetchGroupNames());
    exit;
}

// If student_id is not set, exit early
if (!isset($_GET['student_id'])) {
    return;
}

$studentId = $_GET['student_id'];
$schoolID = fetchSchoolIdForStudent($studentId);  // Fetch SchoolID

if (!$schoolID) {
    return;  // If there's no SchoolID, exit early
}

// Fetch performance data and score names
$performanceData = fetchPerformanceData($studentId);
$scoreNames = fetchScoreNames($schoolID);

// Preparing the data for the chart
foreach ($performanceData as $record) {
    $chartDates[] = $record['score_date'];
    // You can add more logic here if needed
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

<style>
    .dataTables_filter {
        display: none;
    }
</style>
</head>
<body>
<input type="hidden" id="currentWeekStartDate" value="<?php echo htmlspecialchars($currentWeekStartDate); ?>">
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
<?php var_dump($scoreNames); ?>
<?php var_dump($displayedColumns); ?>
<?php var_dump($columnNames); ?>
<table border="1">
<thead>
    <tr>
        <th><?php echo isset($scoreNames['score_date']) ? $scoreNames['score_date'] : ''; ?></th>
        <!-- Dynamically generate table headers based on $displayedColumns -->
        <?php foreach ($displayedColumns as $columnName): ?>
            <?php if ($columnName !== "Week Start Date"): ?>
                <th><?php echo isset($scoreNames[$columnName]) ? $scoreNames[$columnName] : ''; ?></th>
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
                <?php foreach ($displayedColumns as $columnName): ?>
                    <?php if ($columnName !== "Week Start Date"): ?>
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
            <td colspan="<?php echo count($displayedColumns) + 2; ?>">No data available.</td>
        </tr>
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


</body>
</html>