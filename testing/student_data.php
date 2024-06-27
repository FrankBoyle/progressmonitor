<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Layout</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://unpkg.com/tabulator-tables@6.2.1/dist/css/tabulator.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script type="text/javascript" src="https://unpkg.com/tabulator-tables@6.2.1/dist/js/tabulator.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/luxon/2.3.1/luxon.min.js"></script> <!-- Add Luxon -->
</head>
<body>
<div class="dashboard">
    <header class="dashboard-header">
        <div class="logo">
            <img src="bFactor_logo.png" alt="Logo">
        </div>
        <div class="header-icons">
            <a href="students.php" class="nav-link"><i class="nav-icon"></i>Home</a>
            <a href="./users/logout.php" class="nav-link"><i class="nav-icon"></i>Sign Out</a>
        </div>
    </header>
    <main class="content">
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

    </main>
</div>
<script src="charts.js"></script> <!-- Link to your external JS file that handles chart logic -->

</body>
</html>
