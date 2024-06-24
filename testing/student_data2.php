<!DOCTYPE html>
<html>
<head>
    <link href="https://unpkg.com/tabulator-tables@6.2.1/dist/css/tabulator.min.css" rel="stylesheet">
    <script type="text/javascript" src="https://unpkg.com/tabulator-tables@6.2.1/dist/js/tabulator.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/luxon/2.3.1/luxon.min.js"></script> <!-- Add Luxon -->
</head>
<body>

<div>
    <label for="function-select">Select Function:</label>
    <select id="function-select">
        <option value="mean">Mean</option>
        <option value="median">Median</option>
        <option value="mode">Mode</option>
        <option value="range">Range</option>
        <option value="slope">Slope</option>
    </select>
    <button id="apply-function">Apply Function</button>
</div>

<div id="performance-table"></div>

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

                // Function to calculate mean
                function calculateMean(data) {
                    const sum = data.reduce((acc, val) => acc + val, 0);
                    return sum / data.length;
                }

                // Function to calculate median
                function calculateMedian(data) {
                    data.sort((a, b) => a - b);
                    const mid = Math.floor(data.length / 2);
                    return data.length % 2 !== 0 ? data[mid] : (data[mid - 1] + data[mid]) / 2;
                }

                // Function to calculate mode
                function calculateMode(data) {
                    const frequency = {};
                    let maxFreq = 0;
                    let mode = [];
                    data.forEach(val => {
                        frequency[val] = (frequency[val] || 0) + 1;
                        if (frequency[val] > maxFreq) {
                            maxFreq = frequency[val];
                            mode = [val];
                        } else if (frequency[val] === maxFreq) {
                            mode.push(val);
                        }
                    });
                    return mode.length === data.length ? [] : mode;
                }

                // Function to calculate range
                function calculateRange(data) {
                    return Math.max(...data) - Math.min(...data);
                }

                // Function to calculate slope (simple linear regression)
                function calculateSlope(data) {
                    const n = data.length;
                    const sumX = data.reduce((acc, val, idx) => acc + idx, 0);
                    const sumY = data.reduce((acc, val) => acc + val, 0);
                    const sumXY = data.reduce((acc, val, idx) => acc + idx * val, 0);
                    const sumX2 = data.reduce((acc, val, idx) => acc + idx * idx, 0);

                    const slope = (n * sumXY - sumX * sumY) / (n * sumX2 - sumX * sumX);
                    return slope;
                }

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

                // Add a column for the formula result
                columns.push({
                    title: "Formula Result",
                    field: "formula_result",
                });

               // Initialize Tabulator
               const table = new Tabulator("#performance-table", {
                    height: "300px",
                    data: performanceData,
                    columns: columns,
                    layout: "fitColumns",
                    movableColumns: true,
                    resizableRows: true,
                    editTriggerEvent: "dblclick", //trigger edit on double click
                    editorEmptyValue: null,
                    clipboard: true,
                    clipboardCopyRowRange: "range",
                    clipboardPasteParser: "range",
                    clipboardPasteAction: "range",
                    clipboardCopyConfig: {
                        rowHeaders: false, //do not include row headers in clipboard output
                        columnHeaders: true, //do not include column headers in clipboard output
                    },
                    clipboardCopyStyled: false,
                    selectableRange: 1, //allow only one range at a time
                    selectableRangeColumns: false,
                    selectableRangeRows: false,
                    selectableRangeClearCells: true,
                });

                document.getElementById('apply-function').addEventListener('click', function() {
                    console.log('Apply function button clicked');
                    const selectedFunction = document.getElementById('function-select').value;
                    console.log('Selected function:', selectedFunction);

                    const selectedRows = table.getSelectedRows();
                    console.log('Selected rows:', selectedRows);

                    const selectedCells = selectedRows.map(row => row.getData());
                    console.log('Selected cells:', selectedCells);

                    let result;

                    // Extract the values of the selected cells for score2 column (for example)
                    const data = selectedCells.map(row => parseFloat(row.score2)).filter(val => !isNaN(val));
                    console.log('Extracted data:', data);

                    switch (selectedFunction) {
                        case "mean":
                            result = calculateMean(data);
                            break;
                        case "median":
                            result = calculateMedian(data);
                            break;
                        case "mode":
                            result = calculateMode(data);
                            break;
                        case "range":
                            result = calculateRange(data);
                            break;
                        case "slope":
                            result = calculateSlope(data);
                            break;
                        default:
                            result = "Invalid Function";
                    }

                    console.log('Result:', result);

                    // Display the result in the "Formula Result" column for the first selected row
                    if (selectedCells.length > 0) {
                        const firstSelectedRow = selectedCells[0];
                        const rowIndex = performanceData.findIndex(row => row.performance_id === firstSelectedRow.performance_id);
                        table.updateRow(rowIndex, { formula_result: result });
                        console.log('Updated row:', rowIndex, 'with result:', result);
                    }
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
                              console.error('Error info:', result.errorInfo); // Log detailed error info
                          }
                      })
                      .catch(error => console.error('Error:', error));
                });
            })
            .catch(error => console.error('There was a problem with the fetch operation:', error));
    });
</script>

</body>
</html>
