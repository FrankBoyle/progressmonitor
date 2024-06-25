<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Layout</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://unpkg.com/tabulator-tables@6.2.1/dist/css/tabulator.min.css" rel="stylesheet">
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
            <div class="form-group" style="text-align: center;">
                <label for="iep_date" style="display: block;">IEP Date:</label>
                <input type="date" id="iep_date" name="iep_date" class="form-control">
            </div>
            <button id="filterData" class="btn btn-primary">Filter Data</button>
        </div>

        <div class="box box-centered-top">
            <div id="performance-table"></div>
        </div>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const studentId = urlParams.get('student_id');
    const metadataId = urlParams.get('metadata_id');

    // Function to fetch and display the filtered data
    function fetchFilteredData(iepDate) {
        fetch(`./users/fetch_filtered_data.php?student_id=${studentId}&metadata_id=${metadataId}&iep_date=${iepDate}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    table.setData(data.performanceData);
                } else {
                    console.error('Error fetching filtered data:', data.message);
                }
            })
            .catch(error => console.error('Error fetching filtered data:', error));
    }

    document.getElementById('filterData').addEventListener('click', function() {
        var iepDate = document.getElementById('iep_date').value;

        if (iepDate) {
            fetch('./users/save_iep_date.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    iep_date: iepDate,
                    student_id: studentId
                })
            })
            .then(response => response.json())
            .then(data => {
                console.log(data); // Debugging: Log response data
                if (data.success) {
                    fetchFilteredData(iepDate);
                } else {
                    alert(data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        }
    });

    // Initialize Tabulator table
    const table = new Tabulator("#performance-table", {
        height: "500px",
        layout: "fitColumns",
        tooltips: true,
        movableColumns: false,
        resizableRows: false,
        editTriggerEvent: "click",
        editorEmptyValue: null,
        clipboard: true,
        clipboardCopyRowRange: "range",
        clipboardPasteParser: "range",
        clipboardPasteAction: "range",
        clipboardCopyConfig: {
            rowHeaders: false,
            columnHeaders: true,
        },
        clipboardCopyStyled: false,
        selectable: true,
        cellFormatter: function(cell) {
            cell.getElement().classList.add("left-align-cell");
            return cell.getValue();
        },
        headerFormatter: function(header) {
            header.getElement().classList.add("center-align-header");
            return header.getLabel();
        },
    });

    // Function to initialize the table with fetched data
    function initializeTable(performanceData, scoreNames) {
        const columns = [
            {
                title: "Score Date",
                field: "score_date",
                editor: "input",
                formatter: function(cell, formatterParams, onRendered) {
                    const DateTime = luxon.DateTime;
                    let date = DateTime.fromISO(cell.getValue());
                    if (date.isValid) {
                        return date.toFormat("MM/dd/yyyy");
                    } else {
                        return "(invalid date)";
                    }
                },
                editorParams: {
                    mask: "MM/DD/YYYY",
                    format: "MM/DD/YYYY",
                },
                width: 120, // Set a fixed width for better visibility
                frozen: true // Freeze the date column
            },
        ];

        Object.keys(scoreNames).forEach((key, index) => {
            columns.push({ 
                title: scoreNames[key], 
                field: `score${index + 1}`, 
                editor: "input", 
                width: 100 // Set a fixed width for better visibility
            });
        });

        table.setColumns(columns);
        table.setData(performanceData);
    }

    // Fetch initial data to initialize the table
    fetch(`./users/fetch_data2.php?student_id=${studentId}&metadata_id=${metadataId}`)
        .then(response => response.json())
        .then(data => {
            const { performanceData, scoreNames, iepDate } = data;
            initializeTable(performanceData, scoreNames);
            // Set the IEP date if it exists
            if (iepDate) {
                document.getElementById('iep_date').value = iepDate;
            }
        })
        .catch(error => console.error('Error fetching initial data:', error));
});
</script>

</body>
</html>
