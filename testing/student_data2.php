<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Layout</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://unpkg.com/tabulator-ttables@6.2.1/dist/css/tabulator.min.css" rel="stylesheet">
    <script type="text/javascript" src="https://unpkg.com/tabulator-tables@6.2.1/dist/js/tabulator.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/luxon/2.3.1/luxon.min.js"></script>
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
            </div>
            <div id="performance-table"></div>
        </div>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const studentId = urlParams.get('student_id');
    const metadataId = urlParams.get('metadata_id');

    let table;

    // Function to fetch and display the filtered data
    function fetchFilteredData(iepDate) {
        console.log('Fetching filtered data with IEP date:', iepDate); // Debugging output
        fetch(`./users/fetch_filtered_data.php?student_id=${studentId}&metadata_id=${metadataId}&iep_date=${iepDate}`)
            .then(response => response.json())
            .then(data => {
                console.log('Filtered data received:', data); // Debugging output
                table.setData(data);
            })
            .catch(error => console.error('Error fetching filtered data:', error));
    }

    document.getElementById('filterData').addEventListener('click', function() {
        var iepDate = document.getElementById('iep_date').value;

        console.log('Filter Data button clicked, IEP date:', iepDate); // Debugging output

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
                console.log('Save IEP date response:', data); // Debugging output
                if (data.success) {
                    fetchFilteredData(iepDate);
                } else {
                    alert(data.message);
                }
            })
            .catch(error => console.error('Error saving IEP date:', error));
        }
    });

    table = new Tabulator("#performance-table", {
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

    function initializeTable(performanceData, scoreNames) {
        const columns = [
            {
                title: "Score Date",
                field: "score_date",
                editor: "input",
                formatter: function(cell, formatterParams, onRendered) {
                    const DateTime = luxon.DateTime;
                    let date = DateTime.fromISO(cell.getValue());
                    return date.isValid ? date.toFormat("MM/dd/yyyy") : "(invalid date)";
                },
                editorParams: {
                    mask: "MM/DD/YYYY",
                    format: "MM/DD/YYYY",
                },
                width: 120,
                frozen: true
            },
        ];

        Object.keys(scoreNames).forEach((key, index) => {
            columns.push({ 
                title: scoreNames[key], 
                field: `score${index + 1}`, 
                editor: "input", 
                width: 100 
            });
        });

        table.setColumns(columns);
        table.setData(performanceData);
    }

    fetch(`./users/fetch_data2.php?student_id=${studentId}&metadata_id=${metadataId}`)
        .then(response => response.json())
        .then(data => {
            const { performanceData, scoreNames, iepDate } = data;
            console.log('Initial data received:', data); // Debugging output
            initializeTable(performanceData, scoreNames);
            if (iepDate) {
                document.getElementById('iep_date').value = iepDate;
            }
        })
        .catch(error => console.error('Error fetching initial data:', error));
});
</script>

</body>
</html>


