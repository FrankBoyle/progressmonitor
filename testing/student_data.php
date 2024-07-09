<?php
session_start();
include('auth_session.php');
include('db.php');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Assuming school_id is set in the session during login
$schoolId = $_SESSION['school_id']; // Default to 1 if not set
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
    <title>Student Data</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://unpkg.com/tabulator-tables@6.2.1/dist/css/tabulator.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script type="text/javascript" src="https://unpkg.com/tabulator-tables@6.2.1/dist/js/tabulator.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/luxon/2.3.1/luxon.min.js"></script> <!-- Add Luxon -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.3.3/html2canvas.min.js"></script>
    <style>
        .print-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            width: 100%;
            max-width: 600px;
            margin: 0 auto; /* Center the container */
        }

        .print-table-container,
        .print-graph {
            flex: 1;
            min-width: 45%; /* Ensure both elements have a minimum width */
            margin: 10px; /* Adjust as needed for spacing */
            overflow: hidden;
        }

        .print-table th, .print-table td, .print-graph th, .print-graph td {
            padding: 8px;
            border: 1px solid #ccc;
            text-align: center;
            word-wrap: break-word; /* Ensure long words are wrapped */
        }

        .statistics-table {
            width: 48%;
            box-sizing: border-box;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .statistics-table th, .statistics-table td {
            padding: 8px;
            border: 1px solid #ccc;
            text-align: center;
        }

        .report-details {
            width: 100%;
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
            padding: 0 10px;
            box-sizing: border-box;
        }

        .report-details div {
            flex: 1;
            text-align: center;
        }
    </style>
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
            <div class="card column-select-card">
                <div class="selector-area">
                    <div id="columnSelectorTitle" class="selector-title">Click columns to include in graph:</div>
                    <div id="columnSelector" class="checkbox-container"></div>
                </div>
                <div id="reportContainer" class="print-container">
                    <div class="print-table-container" id="printTableContainer"></div>
                    <div class="print-graph" id="printGraphContainer"></div>
                </div>
                <div id="statistics" class="statistics-area">
                    <h3>Statistical Summary</h3>
                    <table id="statsTable" class="statistics-table">
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
                <div class="chart-wrapper">
                    <div id="chartContainer" class="chart"></div>
                </div>
            </div>
            <div class="card chart-card">
                <div class="chart-wrapper">
                    <div id="barChartContainer" class="chart"></div>
                </div>
            </div>

            <!-- Print Report Modal -->
            <div id="printDialogModal" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="hidePrintDialogModal()">&times;</span>
                    <h2>Select Sections to Print</h2>
                    <div>Please select a goal:</div>
                    <div id="goalSelectionContainer" class="selection-container"></div>
                    <div>Select what you want to print in the report:</div>
                    <div id="sectionSelectionContainer" class="selection-container">
                        <div class="selector-item" data-section="printTable">Performance Table</div>
                        <div class="selector-item" data-section="printLineChart">Line Chart</div>
                        <div class="selector-item" data-section="printBarChart">Bar Chart</div>
                        <div class="selector-item" data-section="printStatistics">Statistics</div>
                    </div>
                    <div>
                        <label for="reporting_period">Reporting Period:</label>
                        <input type="text" id="reporting_period" placeholder="Enter reporting period">
                    </div>
                    <div>
                        <label for="notes">Notes:</label>
                        <textarea id="notes" placeholder="Enter notes"></textarea>
                    </div>
                    <button onclick="saveAndPrintReport()">Print</button>
                </div>
            </div>

        </main>
    </div>
    <script src="charts.js"></script> <!-- Link to your external JS file that handles chart logic -->
    <script>
        let schoolId = <?php echo json_encode($schoolId); ?>;
    </script>
</body>
</html>
