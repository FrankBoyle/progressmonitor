<?php
session_start();
include('auth_session.php');
include('db.php');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Assuming school_id is set in the session during login
$schoolId = $_SESSION['school_id']; // Default to 1 if not set
//$teacher_id = $SESSION['teacher_id'];
//$schoolId = $SESSION['school_id'];
//$admin = $SESSION['is_admin'] == 1; // Assuming 'is_admin' is the column name

// Other necessary PHP code...
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-LKFCCN4XXS"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-LKFCCN4XXS');
</script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Layout</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://unpkg.com/tabulator-tables@6.2.1/dist/css/tabulator.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script type="text/javascript" src="https://unpkg.com/tabulator-tables@6.2.1/dist/js/tabulator.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/luxon/2.3.1/luxon.min.js"></script> <!-- Add Luxon -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
</head>
<body>
<div class="dashboard">
    <header class="dashboard-header">
        <div class="logo">
            <img src="bFactor_logo.png" alt="Logo">
        </div>
        <div class="header-icons">
            <button id="printReportBtn" class="btn btn-primary">Print Report</button>
            <a href="students.php" class="nav-link"><i class="nav-icon"></i>Home</a>
            <a href="./users/logout.php" class="nav-link"><i class="nav-icon"></i>Sign Out</a>
        </div>
    </header>
    <main class="content">

        <!-- New Goals Card -->
        <div class="card" id="goalsCard">
            <div class="card-header">
                <h3>Goals</h3>
            </div>
            <div id="goals-container"></div>

        </div>
        
        <div class="card">
            <div class="filter-section">
                <div class="form-group">
                    <label for="iep_date">IEP Date:</label>
                    <input type="date" id="iep_date" name="iep_date" class="form-control">
                </div>
                <button id="filterData" class="btn btn-primary">Filter Data</button>
                <button id="addDataRow" class="btn btn-primary">Add Data Row</button>
                <input type="date" id="newRowDate" style="display: none;">
            </div>

            <button id="editColumnsBtn" class="btn btn-primary">Edit Column Names</button>

            <!-- Modal for Editing Column Names -->
            <div id="editColumnNamesModal" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="hideEditColumnNamesModal()">&times;</span>
                    <h2>Edit Column Names</h2>
                    <form id="editColumnNamesForm" onsubmit="submitColumnNames(event)">
                        <!-- Dynamic input fields will be added here based on the existing column names -->
                    </form>
                </div>
            </div>

            <div id="performance-table"></div>

        </div>

        <div class="card chart-card">
            <div class="selector-area">
                <div id="columnSelectorTitle" class="selector-title">Click columns to include in graph:</div>
                <div id="columnSelector" class="checkbox-container"></div>
            </div>
            <div id="statistics" class="statistics-area">
                <h3>Statistical Summary</h3>
                <table id="statsTable">
                    <thead>
                        <tr>
                            <th>Variable</th>
                            <th>Mean</th>
                            <th>Median</th>
                            <th>Standard Deviation</th>
                            <th>Trendline Equation</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Empty initially -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Chart Containers -->
        <div class="card chart-card">
            <div id="chartContainer" class="chart"></div>
        </div>
        <div class="card chart-card">
            <div id="barChartContainer" class="chart"></div>
        </div>

<!-- Print Dialog Modal -->
<div id="printDialogModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="hidePrintDialogModal()">&times;</span>
        <h2>Select Sections to Print</h2>
        <form id="printOptionsForm">
            <div class="form-group">
                <input type="checkbox" id="printTable" name="printTable" checked>
                <label for="printTable">Performance Table</label>
            </div>
            <div class="form-group">
                <input type="checkbox" id="printLineChart" name="printLineChart" checked>
                <label for="printLineChart">Line Chart</label>
            </div>
            <div class="form-group">
                <input type="checkbox" id="printBarChart" name="printBarChart" checked>
                <label for="printBarChart">Bar Chart</label>
            </div>
            <div class="form-group">
                <input type="checkbox" id="printStatistics" name="printStatistics" checked>
                <label for="printStatistics">Statistics</label>
            </div>
            <button type="button" class="btn btn-primary" onclick="printReport()">Print</button>
        </form>
    </div>
</div>


        <!-- Goal Selection Modal -->
<div id="goalSelectionModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="hideGoalSelectionModal()">&times;</span>
        <h2>Select a Goal</h2>
        <div id="goalSelectionContainer">
            <!-- Dynamic goal selection options will be inserted here -->
        </div>
        <button type="button" class="btn btn-primary" onclick="confirmGoalSelection()">Confirm</button>
    </div>
</div>


    </main>
</div>
<script src="charts.js"></script> <!-- Link to your external JS file that handles chart logic -->
<script>
const schoolId = <?php echo json_encode($schoolId); ?>;
</script>
</body>
</html>

