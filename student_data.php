<?php
session_start();
include('auth_session.php');
include('db.php');

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

// Assuming school_id is set in the session during login
$schoolId = $_SESSION['school_id']; // Default to 1 if not set

// Get the studentId from the URL and ensure it's an integer
$studentId = isset($_GET['studentId']) ? (int)$_GET['studentId'] : 0;

$studentName = "";  // Default empty name

if ($studentId > 0) {
    // Prepared statement to fetch the student's name
    if ($stmt = $db->prepare("SELECT name FROM students WHERE id = ?")) {
        $stmt->bind_param("i", $studentId);
        $stmt->execute();
        $stmt->bind_result($studentName);
        $stmt->fetch();
        $stmt->close();
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-9YXLSJ50NV"></script>
    <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());

    gtag('config', 'G-9YXLSJ50NV');
    </script>
    
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Data</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://unpkg.com/tabulator-tables@6.2.1/dist/css/tabulator.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script type="text/javascript" src="https://unpkg.com/tabulator-tables@6.2.1/dist/js/tabulator.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/luxon/2.3.1/luxon.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.3.3/html2canvas.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <link rel="stylesheet" href="https://cdn.rawgit.com/balzss/luxbar/ae5835e2/build/luxbar.min.css">

    <style>
        /* Include your custom CSS here */
    </style>
</head>
<body>

<input type="hidden" id="school-id" value="<?php echo htmlspecialchars($schoolId, ENT_QUOTES, 'UTF-8'); ?>">

    <div class="dashboard">
    <header class="dashboard-header luxbar-fixed" id="luxbar">
            <input type="checkbox" class="luxbar-checkbox" id="luxbar-checkbox"/>

            <div class="luxbar-menu luxbar-menu-right luxbar-menu-material-indigo">
                <ul class="luxbar-navigation">

                    <li class="luxbar-header">
                        <div class="logo">
                            <img src="IEPreport_logo.jpg" alt="Logo">
                        </div>
                        <label class="luxbar-hamburger luxbar-hamburger-doublespin" id="luxbar-hamburger" for="luxbar-checkbox"> <span></span> </label>
                    </li>

                    <li class="luxbar-item dropdown">
                        <a href="#" class="nav-link" id="helpDropdown" aria-haspopup="true" aria-expanded="false"><span class="question-mark">?</span></a>
                        <div class="dropdown-menu" aria-labelledby="helpDropdown">
                            <a href="IEP_Date_Walkthrough.jpg" class="dropdown-item" data-image="IEP_Date_Walkthrough.jpg">1 - Add an IEP date.</a>
                            <a href="Column_Walkthrough.jpg" class="dropdown-item" data-image="Column_Walkthrough.jpg">2 - Customize column names.</a>
                            <a href="Add_DataRow_Walkthrough.jpg" class="dropdown-item" data-image="Add_DataRow_Walkthrough.jpg">3 -  Add data rows.</a>
                            <a href="Add_Data_Walkthrough.jpg" class="dropdown-item" data-image="Add_Data_Walkthrough.jpg">4 - Add data to rubric.</a>
                            <a href="Graph_Data_Walkthrough.jpg" class="dropdown-item" data-image="Graph_Data_Walkthrough.jpg">5 - Graph your data.</a>
                            <a href="Print_Walkthrough.jpg" class="dropdown-item" data-image="Print_Walkthrough.jpg">6 - Print the IEP Report.</a>
                        </div>
                    </li>

                    <button id="printReportBtn" class="btn btn-primary">Print Report</button>

                    <li class="luxbar-item"><a href="mailto:dan@iepreport.com">Support</a></li>
                    <li class="luxbar-item"><a href="students.php">Home</a></li>
                    <li class="luxbar-item"><a href="./users/logout.php">Logout</a></li>

                </ul>
            </div>
        </header>

        <main class="content">
        <div class="print-container">

            <div class="card" id="goalsCard">
                <div class="card-header">
                    <h2>Goals</h2>
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

                <div class="print-container">
                    <div class="goal-text-container">
                        <div id="goal-text"></div>
                    </div>
                    <div class="print-table-container" id="printTableContainer"></div>
                    <div class="print-graph" id="printGraphContainer"></div>
                </div>

                <div id="statistics" class="statistics-area">
                    <h2>Statistical Summary</h2>
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
            <div id="printDialogModal" class="modal">
                <div class="modal-content">
                    
                    <span class="close" onclick="hidePrintDialogModal()">&times;</span>
                    
                    <h2>Selection to Print</h2>
                    <div>Please select a goal:</div>

                    <div id="goalSelectionContainer" class="selection-container"></div>

                    <div>Select what you want to print in the report:</div>

                    <div id="sectionSelectionContainer" class="selection-container">
                        <div class="selector-item" data-section="printTable">Performance Table</div>
                        <div class="selector-item" data-section="printLineChart">Line Chart</div>
                        <div class="selector-item" data-section="printBarChart">Bar Chart</div>
                        <div class="selector-item" data-section="printStatistics">Statistics</div>
                    </div>

                    <div id="reportingPeriodContainer" style="display:none;">
                        <label for="reporting_period"><strong>Reporting Period:</strong></label>
                        <select id="reporting_period"></select>
                    </div>

                    <div id="notes-container">
                        <label for="notes">Notes:</label>
                        <div id="notes" style="height: 200px;" placeholder="Enter notes"></div> <!-- Quill will be initialized here -->
                    </div>

                    <button onclick="saveAndPrintReport()">Print</button>
                </div>
            </div>
        </div>
        </main>
    </div>
<script src="charts.js"></script>
<script>
let schoolId = <?php echo json_encode($schoolId); ?>;

document.querySelectorAll('.dropdown-item').forEach(item => {
    let timer;
    item.addEventListener('mouseenter', function(event) {
        const imageUrl = this.getAttribute('data-image');
        timer = setTimeout(() => {
            const preview = document.createElement('img');
            preview.src = imageUrl;
            preview.className = 'image-preview';
            document.body.appendChild(preview);
            preview.style.display = 'block';
            preview.style.bottom = '20px'; // 20px from the bottom
            preview.style.left = '20px'; // 20px from the left
        }, 300); // Delay of 300 milliseconds
    });

    item.addEventListener('mouseleave', function() {
        clearTimeout(timer);
        const preview = document.querySelector('.image-preview');
        if (preview) {
            preview.remove();
        }
    });

    // Prevent the default hover action if the user is clicking
    item.addEventListener('click', function(event) {
        event.preventDefault(); // This stops the default navigation when clicking
        window.open(this.href, '_blank'); // Manually open the link in a new tab
    });
});

</script>
</body>
</html>
