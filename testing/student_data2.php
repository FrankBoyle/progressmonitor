<!DOCTYPE html>
<html>
<head>
    <link href="https://unpkg.com/tabulator-tables@6.2.1/dist/css/tabulator.min.css" rel="stylesheet">
    <script type="text/javascript" src="https://unpkg.com/tabulator-tables@6.2.1/dist/js/tabulator.min.js"></script>
</head>
<body>

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

                // Define columns based on metadata
                const columns = [
                    { title: "Score Date", field: "score_date", editor: "input" },
                ];

                Object.keys(scoreNames).forEach((key, index) => {
                    columns.push({ title: scoreNames[key], field: `score${index + 1}`, editor: "input" });
                });

                // Initialize Tabulator
                const table = new Tabulator("#performance-table", {
                    data: performanceData,
                    columns: columns,
                    layout: "fitColumns",
                    pagination: "local",
                    paginationSize: 10,
                    movableColumns: true,
                    resizableRows: true,
                });

                // Add cellEdited event listener
                table.on("cellEdited", function(e, cell) {
                    // Check if the value is empty and set to null if it is
                    const field = cell.getField();
                    let value = cell.getValue();

                    if (value === "") {
                        cell.setValue(null, true); // update cell display to null
                        value = null;
                    }

                    const updatedData = cell.getData();
                    updatedData[field] = value;

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
                              alert('Data updated successfully');
                          } else {
                              alert('Failed to update data');
                          }
                      });
                });
            })
            .catch(error => console.error('There was a problem with the fetch operation:', error));
    });
</script>

</body>
</html>
