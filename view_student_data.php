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
    <title><?php echo $studentName; ?></title>
    <!-- Bootstrap 4 -->
    <script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.0/jquery-ui.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.css">
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/apexcharts@3.41.1/dist/apexcharts.min.css">
    <link rel="stylesheet" href="plugins/summernote/summernote-bs4.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.18/summernote-bs4.min.js"></script>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.0/themes/base/jquery-ui.css">

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
    <!-- overlayScrollbars -->
    <link rel="stylesheet" href="plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="dist/css/adminlte.min.css">
    <!-- Ionicons -->
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <!-- Tempusdominus Bootstrap 4 -->
    <link rel="stylesheet" href="plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
    <!-- iCheck -->
    <link rel="stylesheet" href="plugins/icheck-bootstrap/icheck-bootstrap.min.css">
    <!-- JQVMap -->
    <link rel="stylesheet" href="plugins/jqvmap/jqvmap.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="dist/css/adminlte.min.css">
    <!-- Daterange picker -->
    <link rel="stylesheet" href="plugins/daterangepicker/daterangepicker.css">
    <!-- summernote -->
    <script src="student_data.js"  defer></script>
    
    <script>
    // Get the metadata_id from the URL parameter
    const urlParams = new URLSearchParams(window.location.search);
    const metadata_id = urlParams.get('metadata_id');
    var scoreNamesFromPHP = <?php echo json_encode($scoreNames); ?>;
    </script>

<style>
#dataTable_wrapper .col-md-6:eq(0) {
    position: relative;
    z-index: 1000;
}

    .editable {
        cursor: pointer;
    }

    .editable.editing {
        background-color: #f4f4f4;
    }

    .editable input {
        border: none;
        padding: 0;
        margin: 0;
        box-sizing: border-box;
        background-color: transparent;
        outline: none;
    }
</style>

</head>
<body>
<h1>Student Performance Data</h1>

<h2>Student Overview</h2>
<p>Name: <?php echo $studentName; ?></p>
<p>Category: <?php echo $selectedCategoryName; ?></p>
<div>
<a href="test.php" class="btn btn-primary">Student List</a>
<input type="hidden" id="schoolIdInput" name="school_id" value="<?php echo htmlspecialchars($school_id); ?>">
<input type="hidden" id="currentStudentId" value="<?php echo htmlspecialchars($student_id); ?>" />
</div>

<div>
<label for="startDateFilter">Filter by Start Date:</label>
<input type="text" id="startDateFilter">
</div>

<div>
<!-- Add the generated links here -->
<?php foreach ($metadataEntries as $metadataEntry): ?>
    <a href="?student_id=<?php echo $student_id; ?>&metadata_id=<?php echo $metadataEntry['metadata_id']; ?>">
        <?php echo $metadataEntry['category_name']; ?>
    </a><br>
<?php endforeach; ?>
</div>

<!-- Snippet of the modified code -->
<table border="1" id="dataTable">
    <thead>
        <tr>
            <th>Date</th>
            <?php 
            foreach ($scoreNames as $category => $values) {
                if (is_array($values)) {
                    foreach ($values as $score) {
                        echo "<th>" . htmlspecialchars($score) . "</th>";
                    }
                } else {
                    echo "<th>" . htmlspecialchars($values) . "</th>";
                }
            }
            ?>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($performanceData)): ?>
            <tr>
                <td colspan="11">No Data Found. Click "Add Data Row" to add new data.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($performanceData as $data): ?>
                <tr data-performance-id="<?php echo $data['performance_id']; ?>">
                    <td class="editable" data-field-name="score_date">
                        <?php echo isset($data['score_date']) ? date("m/d/Y", strtotime($data['score_date'])) : ""; ?>
                    </td>
                    <?php for ($i = 1; $i <= 10; $i++): ?>
                        <td class="editable" data-field-name="score<?php echo $i; ?>">
                            <?php echo isset($data['score'.$i]) ? $data['score'.$i] : ""; ?>
                        </td>
                    <?php endfor; ?>
                    <td><button class="deleteRow" data-performance-id="<?php echo $data['performance_id']; ?>">Delete</button></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<button id="addDataRow">Add Data Row</button>
<input type="text" id="newRowDate" style="display: none;">

<!-- Existing checkboxes for column selection -->
<div id="columnSelector">
    <label>Select Columns to Display:</label>
    <?php
    foreach ($scoreNames as $category => $scores) {
        foreach ($scores as $index => $scoreName) {
            $scoreColumnName = 'score' . ($index + 1);
            $customColumnName = htmlspecialchars($scoreName); // Custom column name
            echo '<label>';
            echo '<input type="checkbox" name="selectedColumns[]" value="' . htmlspecialchars($scoreColumnName) . '"';
            echo ' data-column-name="' . $customColumnName . '">'; // Include custom name as data attribute
            echo htmlspecialchars($scoreName);
            echo '</label>';
        }
    }
    ?>
</div>


<label>Enter Benchmark Value:</label>
<input type="text" id="benchmarkValue">
<button type ="button" id="updateBenchmark">Update Benchmark</button>

<div id="accordion">
    <h3>Line Graph</h3>
    <div>
    <div id="chart" style="width: 1000px;"></div>
    </div>
    <h3>Bar Graph</h3>
    <div>
    <div id="barChart" style="width: 1000px;"></div>
    </div>
</div>

<script>

</script>
</body>
</html>