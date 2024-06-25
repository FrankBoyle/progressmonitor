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

        <section class="content">
    <div class="card">
        <div class="form-group" style="text-align: center;">
            <label for="iep_date" style="display: block;">IEP Date:</label>
            <input type="date" id="iep_date" name="iep_date" class="form-control" value="<?php echo htmlspecialchars($iep_date); ?>">
        </div>
        <button id="filterData" class="btn btn-primary">Filter Data</button>
    </div>
</section>

            <main class="content">
                <section class="box box-centered-top">
                    <div id="performance-table"></div>
                </section>
            </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const studentId = urlParams.get('student_id');
    const metadataId = urlParams.get('metadata_id');

    fetch(`./users/fetch_data2.php?student_id=${studentId}&metadata_id=${metadataId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            const { performanceData, scoreNames } = data;

            // Define columns based on metadata
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
                },
            ];

            Object.keys(scoreNames).forEach((key, index) => {
                columns.push({ 
                    title: scoreNames[key], 
                    field: `score${index + 1}`, 
                    editor: "input", 
                });
            });

            // Initialize Tabulator with existing settings
            const table = new Tabulator("#performance-table", {
                height: "500px",
                data: performanceData,
                columns: columns,
                frozenRows: 0,
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

            // Add cellEdited event listener
            table.on("cellEdited", function(cell) {
                const field = cell.getField();
                let value = cell.getValue();

                if (value === "") {
                    value = null;
                }

                const updatedData = cell.getRow().getData();
                updatedData[field] = value;

                // Log the updated data for debugging
                console.log("Updated data:", updatedData);

                // Update the cell data in the backend (make AJAX call)
                fetch('./users/update_performance2.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(updatedData)
                }).then(response => response.json())
                  .then(result => {
                      if (result.success) {
                          // alert('Data updated successfully');
                      } else {
                          alert('Failed to update data: ' + result.message);
                          console.error('Error info:', result.errorInfo);
                      }
                  })
                  .catch(error => console.error('Error:', error));
            });
        })
        .catch(error => console.error('There was a problem with the fetch operation:', error));

    // Adding the filter functionality
    document.getElementById('filterData').addEventListener('click', function() {
        var iepDate = document.getElementById('iep_date').value;
        var studentId = urlParams.get('student_id');
        var metadataId = urlParams.get('metadata_id');

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
                if (data.success) {
                    filterData(iepDate);
                } else {
                    alert(data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        }
    });

    function filterData(iepDate) {
        // Assuming the Tabulator table variable is named `table`
        table.setFilter("score_date", ">=", iepDate);
    }
});

</script>

</body>
</html>