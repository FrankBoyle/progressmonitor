<!DOCTYPE html>
<html>
<head>
    <link href="https://unpkg.com/tabulator-tables@6.2.1/dist/css/tabulator.min.css" rel="stylesheet">
    <script type="text/javascript" src="https://unpkg.com/tabulator-tables@6.2.1/dist/js/tabulator.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/luxon/2.3.1/luxon.min.js"></script> <!-- Add Luxon -->

    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/w2ui/1.5.2/w2ui.min.css" />
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/w2ui/1.5.2/w2ui.min.js"></script>
</head>
<body>


<div id="performance-table"></div>
<div id="w2ui-performance-table" style="height: 300px;"></div>

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

                // Define columns for Tabulator
                const tabulatorColumns = [
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
                    tabulatorColumns.push({ 
                        title: scoreNames[key], 
                        field: `score${index + 1}`, 
                        editor: "input", 
                    });
                });

                // Initialize Tabulator table
                const table = new Tabulator("#performance-table", {
                    height: "300px",
                    data: performanceData,
                    columns: tabulatorColumns,
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
                        columnHeaders: true, //include column headers in clipboard output
                    },
                    clipboardCopyStyled: false,
                    selectableRange: 1, //allow only one range at a time
                    selectableRangeColumns: false,
                    selectableRangeRows: false,
                    selectableRangeClearCells: true,
                });

                // Add cellEdited event listener for Tabulator
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

                // Define columns for w2ui
                const w2uiColumns = [
                    { field: 'recid', caption: 'ID', size: '50px', sortable: true, attr: 'align=center' },
                    { field: 'score_date', caption: 'Score Date', size: '120px', sortable: true, editable: { type: 'date' } },
                ];

                Object.keys(scoreNames).forEach((key, index) => {
                    w2uiColumns.push({ 
                        field: `score${index + 1}`, 
                        caption: scoreNames[key], 
                        size: '120px', 
                        sortable: true, 
                        editable: { type: 'text' },
                    });
                });

                // Initialize w2ui grid
                $('#w2ui-performance-table').w2grid({
                    name: 'performanceGrid',
                    show: { 
                        toolbar: true,
                        footer: true,
                    },
                    columns: w2uiColumns,
                    records: performanceData.map((item, index) => ({ recid: index + 1, ...item })),
                    onEditField: function (event) {
                        console.log('Edit field', event);
                    },
                    onChange: function(event) {
                        const record = this.get(event.recid);
                        const updatedData = { ...record, [event.column]: event.value_new };
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
                    }
                });

            })
            .catch(error => console.error('There was a problem with the fetch operation:', error));
    });
</script>

</body>
</html>



