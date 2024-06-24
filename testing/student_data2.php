<!DOCTYPE html>
<html>
<head>
    <link href="https://unpkg.com/tabulator-tables@5.0.8/dist/css/tabulator.min.css" rel="stylesheet">
    <script type="text/javascript" src="https://unpkg.com/tabulator-tables@5.0.8/dist/js/tabulator.min.js"></script>
</head>
<body>

<div id="performance-table"></div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const studentId = '1';  // replace with dynamic value
        const metadataId = '1'; // replace with dynamic value

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
                    { title: "Performance ID", field: "performance_id" },
                    { title: "School ID", field: "school_id" },
                    { title: "Score Date", field: "score_date" },
                ];

                Object.keys(scoreNames).forEach((key, index) => {
                    columns.push({ title: scoreNames[key], field: `score${index + 1}` });
                });

                // Initialize Tabulator
                new Tabulator("#performance-table", {
                    data: performanceData,
                    columns: columns,
                    layout: "fitColumns",
                    pagination: "local",
                    paginationSize: 10,
                    movableColumns: true,
                    resizableRows: true,
                    editable: true,
                    cellEdited: function(cell) {
                        // Update the cell data in the backend (make AJAX call)
                        const updatedData = cell.getData();
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
                    }
                });
            })
            .catch(error => console.error('There was a problem with the fetch operation:', error));
    });
</script>

</body>
</html>
