<?php
include ('./users/fetch_data.php');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$studentId = $_GET['student_id'];
$metadataId = $_GET['metadata_id'];

foreach ($students as $student) {
    if ($student['student_id'] == $studentId) { // If the IDs match
        $studentName = $student['name']; // Get the student name
        break;
    }
}

foreach ($metadataEntries as $metadataEntry) {
    if ($metadataEntry['metadata_id'] == $metadataId) {
        $selectedCategoryName = $metadataEntry['category_name'];
        break; // We found our category, no need to continue the loop
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $studentName; ?></title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.0/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/ui/1.13.0/jquery-ui.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.css">
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/apexcharts@3.41.1/dist/apexcharts.min.css">
    <link rel="stylesheet" href="plugins/summernote/summernote-bs4.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.18/summernote-bs4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.3.2/html2canvas.min.js"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
    <link rel="stylesheet" href="dist/css/adminlte.min.css">
    <link rel="stylesheet" href="plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
    <link rel="stylesheet" href="plugins/icheck-bootstrap/icheck-bootstrap.min.css">
    <link rel="stylesheet" href="plugins/jqvmap/jqvmap.min.css">
    <link rel="stylesheet" href="dist/css/adminlte.min.css">
    <link rel="stylesheet" href="plugins/daterangepicker/daterangepicker.css">
    <script src="student_data.js" defer></script>
    <script>
        var scoreNamesFromPHP = <?php echo json_encode($scoreNames); ?>;
    </script>
</head>
<body>
    <div class="dashboard">
        <header class="dashboard-header">
            <div class="logo">Logo</div>
            <div class="header-icons">
                <span>Icon 1</span>
                <span>Icon 2</span>
                <span>Icon 3</span>
            </div>
        </header>

        <div class="content-wrapper">
            <section class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1><?php echo $studentName; ?> Performance Data - <?php echo $selectedCategoryName; ?></h1>
                            <a href="home.php" class="btn btn-primary">Home</a>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="./home.php">Home</a></li>
                                <li class="breadcrumb-item active">Performance Data</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </section>

            <section class="content">
                <div class="row">
                    <div class="col-md-4 col-sm-6 col-12">
                        <div class="card card-outline card-info">
                            <div class="card-header">
                                <h3 class="card-title">Categories</h3><br>
                                <?php foreach ($metadataEntries as $metadataEntry): ?>
                                    <a href="?student_id=<?php echo $studentId; ?>&metadata_id=<?php echo $metadataEntry['metadata_id']; ?>">
                                        <?php echo $metadataEntry['category_name']; ?>
                                    </a><br>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <div class="form-group" style="text-align: center;">
                <label for="iep_date" style="display: block;">IEP Date:</label>
                <input type="date" id="iep_date" name="iep_date" class="form-control" value="<?php echo htmlspecialchars($iep_date); ?>">
            </div>
            <button id="filterData" class="btn btn-primary">Filter Data</button>

            <section class="content">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card card-outline card-info">
                            <div class="card-header">
                                <h3 class="card-title"></h3>
                                <table border="1" id="dataTable">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <?php foreach ($scoreNames as $category => $values): ?>
                                                <?php if (is_array($values)): ?>
                                                    <?php foreach ($values as $score): ?>
                                                        <th><?php echo htmlspecialchars($score); ?></th>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <th><?php echo htmlspecialchars($values); ?></th>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="dataTableBody">
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
                                                    <td><button class="deleteRow btn btn-block btn-primary" data-performance-id="<?php echo $data['performance_id']; ?>">Delete</button></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                                <button id="addDataRow" class="btn btn-primary">Add Data Row</button>
                                <input type="text" id="newRowDate" style="display: none;">
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="content">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card card-outline card-info">
                            <div class="card-header">
                                <h3 class="card-title"></h3>
                                <div id="columnSelector">
                                    <label>Select Columns to Display:</label>
                                    <?php foreach ($scoreNames as $category => $scores): ?>
                                        <?php foreach ($scores as $index => $scoreName): ?>
                                            <label>
                                                <input type="checkbox" name="selectedColumns[]" value="<?php echo htmlspecialchars('score'.($index + 1)); ?>" data-column-name="<?php echo htmlspecialchars($scoreName); ?>">
                                                <?php echo htmlspecialchars($scoreName); ?>
                                            </label>
                                        <?php endforeach; ?>
                                    <?php endforeach; ?>
                                </div>

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
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="content">
                <div class="card card-outline card-info">
                    <div class="card-header">
                        <h3 class="card-title">Goals</h3>
                    </div>
                    <div class="card-body">
                        <div class="row" id="goalsList">
                            <?php foreach ($goals as $index => $goal): ?>
                                <div class="col-md-4 col-sm-6 col-12">
                                    <div class="info-box">
                                        <div class="info-box-content goal-container">
                                            <span class="info-box-text">Goal <?php echo $index + 1; ?></span>
                                            <label class="goal-checkbox-label">
                                                <input type="checkbox" class="goal-checkbox" data-goal-id="<?php echo $goal['goal_id']; ?>">
                                                Select
                                            </label>
                                            <textarea id="summernote<?php echo $index + 1; ?>" class="goaltext" contenteditable="true" data-goal-id="<?php echo $goal['goal_id']; ?>">
                                                <?php echo htmlspecialchars($goal['goal_description']); ?>
                                            </textarea>
                                            <button class="save-goal-btn" data-goal-id="<?php echo $goal['goal_id']; ?>">✔</button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="add-goal-form">
                            <input type="text" id="newGoalText" placeholder="Enter new goal description">
                            <button id="addNewGoalBtn">Add New Goal</button>
                        </div>
                        <div class="editable-notes-section">
                            <h3>Goal Notes</h3>
                            <textarea id="graphNotes" class="summernote"></textarea>
                            <button id="saveGraphNotes" class="btn btn-primary">Save Notes</button>
                            <button id="printButton" class="btn btn-primary">Print</button>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
    <script src="student_data.js" defer></script>
</body>
</html>
