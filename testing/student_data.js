

var benchmark = null;

$(document).ready(function() {
    initializeChart();

    benchmark = parseFloat($("#benchmarkValue").val());
    if (isNaN(benchmark)) {
        benchmark = null;  // Default benchmark value if the input is not provided
    }

    $("#scoreSelector").change(function() {
        var selectedScore = $(this).val();
        updateChart(selectedScore);
    });

    $("#updateBenchmark").click(function() {
        var value = parseFloat($("#benchmarkValue").val());
        if (!isNaN(value)) {
            benchmark = value;
            var selectedScore = $("#scoreSelector").val();
            updateChart(selectedScore);
        } else {
            alert('Please enter a valid benchmark value.');
        }
    });

    updateChart('score1');  // Default
});

function initializeChart() {
    window.chart = new ApexCharts(document.querySelector("#chart"), getChartOptions([], []));
    window.chart.render();
}

function getChartData(scoreField) {
    var chartData = [];
    var xCategories = [];

    $('tr[data-performance-id]').each(function() {
        var weekStartDate = $(this).find('td[data-field-name="score_date"]').text();
        var scoreValue = $(this).find(`td[data-field-name="${scoreField}"]`).text();

        if (weekStartDate !== 'New Entry' && !isNaN(parseFloat(scoreValue))) {
            chartData.push({
                x: weekStartDate,  // Directly use the date string
                y: parseFloat(scoreValue)
            });

            xCategories.push(weekStartDate);
        }
    });

    // Sorting logic starts here
    const sortedChartData = chartData.sort((a, b) => {
        return new Date(a.x) - new Date(b.x);
    });
    const sortedCategories = xCategories.sort((a, b) => {
        return new Date(a) - new Date(b);
    });

    return { chartData: sortedChartData, xCategories: sortedCategories };
}

function updateChart(scoreField) {
    var {chartData, xCategories} = getChartData(scoreField);

    // Calculate trendline
    var trendlineFunction = calculateTrendline(chartData);
    var trendlineData = chartData.map((item, index) => {
        return {
            x: item.x,
            y: trendlineFunction(index)
        };
    });

    var seriesData = [
        {
            name: 'Trendline',
            data: trendlineData,
            connectNulls: true,
            dataLabels: {
                enabled: false // Disable data labels for the Trendline series
            }
        },
        {
            name: 'Selected Score',
            data: chartData,
            connectNulls: true,
            dataLabels: {
                enabled: true // Enable data labels for the Selected Score series
            },
            stroke: {
                width: 7
            }
        }
    ];

    if (benchmark !== null) {
        var benchmarkData = xCategories.map(date => {
            return {
                x: date,
                y: benchmark
            };
        }).reverse();
        seriesData.push({
            name: 'Benchmark',
            data: benchmarkData,
            connectNulls: true,
            dataLabels: {
                enabled: false // Disable data labels for the Benchmark series
            }
        });
    }

    window.chart.updateOptions(getChartOptions(seriesData, xCategories));
}



function getChartOptions(dataSeries, xCategories) {
    return {
        series: dataSeries,
        chart: {
            type: 'line',
            stacked: false,
            width: 600,
            zoom: {
                type: 'x',
                enabled: true,   // Ensure zooming is enabled
                autoScaleYaxis: true  // This will auto-scale the Y-axis when zooming in
            },
            toolbar: {
                autoSelected: 'zoom' 
            },
            pan: {
                enabled: true,  // Enable panning
                mode: 'x',      // Enable horizontal panning
            },   
            dropShadow: {
                enabled: true,
                color: '#000',
                top: 15,          // Adjusted the vertical offset a bit
                left: 5,          // Adjusted the horizontal offset a bit
                blur: 7,         // Increased the blur to make it more spread out
                opacity: 0.5      // Increased the opacity to make it darker
            }
        },


        dataLabels: {
            enabled: true,
            enabledOnSeries: [1],  // enable only on the first series
            offsetY: -10,
            style: {
                fontSize: '12px',
                colors: ['#333']
            }
        },
        
        stroke: {
            curve: 'smooth',
            width: dataSeries.map(series => series.name === 'Selected Score' ? 3 : 1.5)  // Set width based on series name
        },

        markers: {
            size: dataSeries.map(series => {
                if (series.name === 'Selected Score') {
                    return 5;  // or whatever size you want for the "Selected Score" series
                } else {
                    return 0;  // This will make markers invisible for "Trendline" and "Benchmark" series
                }
            }),
            colors: undefined,
            strokeColors: '#fff',
            strokeWidth: 1.7,
            strokeOpacity: 1,
            strokeDashArray: 0,
            fillOpacity: 1,
            discrete: [],
            shape: "circle",
            radius: 2,
            offsetX: 0,
            offsetY: 0,
            onClick: undefined,
            onDblClick: undefined,
            showNullDataPoints: true,
            hover: {
                size: undefined,
                sizeOffset: 1.5
            }
        },

        xaxis: {
                type: 'category', 
                categories: xCategories,
            labels: {
                hideOverlappingLabels: false,
                formatter: function(value) {
                    return value;  // Simply return the value since we're not working with timestamps anymore
                }
            },
            title: {
                text: 'Date'
            }
        },


        yaxis: {
            title: {
                text: 'Value'
            },
            labels: {
                formatter: function(value) {
                    return value.toFixed(0);
                }
            }
        },
        grid: {
            xaxis: {
                lines: {
                    show: true
                }
            }
        },
        colors: ['#2196F3', '#FF5722', '#000000']
    };
}

function calculateTrendline(data) {
    var sumX = 0;
    var sumY = 0;
    var sumXY = 0;
    var sumXX = 0;
    var count = 0;

    data.forEach(function (point, index) {
        var x = index; // Use index as the x value
        var y = point.y;

        if (y !== null) {
            sumX += x;
            sumY += y;
            sumXY += x * y;
            sumXX += x * x;
            count++;
        }
    });

    var slope = (count * sumXY - sumX * sumY) / (count * sumXX - sumX * sumX);
    var intercept = (sumY - slope * sumX) / count;

    return function (x) {
        return slope * x + intercept;
    };
}

////////////////////////////////////////////////
let columnHeaders = []; // Initialize as an empty array

$(document).ready(function() {
// Listen for changes in the metadata dropdown
    // Check if metadataId is set in the URL, and if so, load the data.
    var urlParams = new URLSearchParams(window.location.search);
    var metadataId = urlParams.get('metadata_id');
    if (metadataId) {
        // If you have a function to fetch data via AJAX, call it here
        fetchDataBasedOnMetadataId(metadataId);
    }
// Update the change event for the metadata group selector
// Update the change event for the metadata group selector
$('#metadataIdSelector').on('change', function () {
    const selectedMetadataId = $(this).val();

    if (selectedMetadataId === '0') {
        // Load default headers for "Select Metadata Group" option
        loadDefaultHeaders();
    } else {
        // Make an AJAX request to fetch column names based on the selected metadata group
        $.ajax({
            type: 'GET',
            url: './users/fetch_data.php',
            data: { metadataId: selectedMetadataId },
            dataType: 'json',
            success: function (response) {
                // Update table headers with new column names from the response
                updateTableHeaders(response.columnHeaders);
            },
            error: function (xhr, status, error) {
                console.error('AJAX Error:', error);
            }
        });
    }
});


// Modify the updateTableHeaders function to handle the new column names
function updateTableHeaders(newColumnHeaders) {
    var table = $('table'); // Adjust this selector based on your HTML structure.

    // Remove existing table headers.
    table.find('thead').remove();

    // Generate new table headers based on newColumnHeaders.
    var thead = $('<thead>');
    var headerRow = $('<tr>');

    // Always include the "Date" column.
    headerRow.append($('<th>Date</th>'));

    // Add new columns based on newColumnHeaders.
    $.each(newColumnHeaders, function (index, columnName) {
        headerRow.append($('<th>' + columnName + '</th>'));
    });

    // Add the "Action" column.
    headerRow.append($('<th>Action</th>'));

    thead.append(headerRow);
    table.append(thead);
}

// Function to fetch and load default headers for the "Select Metadata Group" option
function loadDefaultHeaders() {
    // Make an initial AJAX request to fetch headers for the default option
    $.ajax({
        type: 'GET',
        url: './users/fetch_data.php',
        data: { metadataId: 0 }, // Assuming 0 represents the default option
        dataType: 'json',
        success: function (response) {
            // Update table headers with the fetched column names
            updateTableHeaders(response.columnHeaders);
        },
        error: function (xhr, status, error) {
            console.error('AJAX Error:', error);
        }
    });
}
// Function to fetch metadata categories and update the dropdown
function fetchMetadataCategories() {
    var studentId = $('#currentStudentId').val();
    var selectedMetadataId = $('#metadataIdSelector').val(); // Get the selected metadata_id

    $.ajax({
        url: './users/fetch_data.php',
        type: 'GET',
        data: { 
            action: 'fetchMetadataCategories',
            student_id: studentId,
            metadata_id: selectedMetadataId, // Pass the selected metadata_id
        },
        dataType: 'json',
        success: function (response) {
            if (response) {
                console.log(response);
                $('#metadataIdSelector').empty();
                $.each(response, function (index, item) {
                    $('#metadataIdSelector').append('<option value="' + item.metadata_id + '">' + item.category_name + '</option>');
                });
            }
        },
    });
}

// Initial table header update and metadata group fetch
updateTableHeaders();
fetchMetadata()

function fetchMetadata(metadataId) {
    var studentId = $('#currentStudentId').val();
    $.ajax({
        url: './users/fetch_data.php',
        type: 'GET',
        data: {
            action: 'fetchMetadata',
            metadata_id: metadataId, // Include metadataId as a query parameter
            student_id: studentId
        },
        dataType: 'json',
        success: function (response) {
            if (response) {
                // Check for null values and replace with appropriate text or handle them as needed
                var columnHeaders = response.columnHeaders;
                var performanceData = response.performanceData;

                // Check and handle null values in column headers
                for (var key in columnHeaders) {
                    if (columnHeaders[key] === null) {
                        columnHeaders[key] = "N/A"; // Replace with appropriate text
                    }
                }

                // Handle performance data
                $.each(performanceData, function (index, item) {
                    // Check and handle null values in performance data fields
                    for (var key in item) {
                        if (item[key] === null) {
                            item[key] = "N/A"; // Replace with appropriate text
                        }
                    }

                    // Now you can use the updated data for display or processing
                });

                // Rest of your code
            }
        },
    });
}

function getCurrentDate() {
        const currentDate = new Date();
        return `${(currentDate.getMonth() + 1).toString().padStart(2, '0')}/${currentDate.getDate().toString().padStart(2, '0')}/${currentDate.getFullYear()}`;
    }

    // Constants & Variables
    const CURRENT_STUDENT_ID = $('#currentStudentId').val();

    // Utility Functions
    function isValidDate(d) {
        return d instanceof Date && !isNaN(d);
    }

    function convertToDatabaseDate(dateString) {
        if (!dateString || dateString === "New Entry") return dateString;
        const [month, day, year] = dateString.split('/');
        return `${year}-${month}-${day}`;
    }

    function convertToDisplayDate(databaseString) {
        if (!databaseString || databaseString === "New Entry") return databaseString;
        const [year, month, day] = databaseString.split('-');
        return `${month}/${day}/${year}`;
    }

    async function ajaxCall(type, url, data) {
        try {
            const response = await $.ajax({
                type: type,
                url: url,
                data: data,
                dataType: 'json',  // Expecting server to return JSON
                cache: false,      // Don't cache results (especially important for POST requests)
            });
            return response;
        } catch (error) {
            console.error('Error during AJAX call:', error);
            return error.responseJSON;  // Return the parsed JSON error message
        }   
    }

    async function saveEditedDate(cell, newDate) {
        const performanceId = cell.closest('tr').data('performance-id');
        const fieldName = cell.data('field-name');
        const studentId = $("#currentStudentId").val();
        const postData = {
            performance_id: performanceId,
            field_name: fieldName,
            new_value: convertToDatabaseDate(newDate), // Convert to yyyy-mm-dd format before sending
            student_id: studentId

        };
        console.log(postData);
        //console.log("studentID:", student_id);


        ajaxCall('POST', 'update_performance.php', postData).then(response => {
            console.log(response); // <-- This is the debug line. 
        
            if (response && response.error && response.error === 'Duplicate date not allowed!') {
                alert("Duplicate date not allowed!");
                cell.html(cell.data('saved-date') || '');  
            } else if (response && response.saved_date) {
                cell.data('saved-date', response.saved_date);
            } else {
            }
        });  
    }

    //let dateAscending = true; // to keep track of current order

    $('#toggleDateOrder').on('click', function() {
        const table = $('table').DataTable();
        dateAscending = !dateAscending; // flip the state

        table.order([0, dateAscending ? 'asc' : 'desc']).draw();
    });

    $(document).on('click', '.deleteRow', function() {
        const row = $(this);  // Capture the button element for later use
        const performanceId = $(this).data('performance-id');
    
        // Confirm before delete
        if (!confirm('Are you sure you want to delete this row?')) {
            return;
        }
    
        // Send a request to delete the data from the server
        $.post('delete_performance.php', {
            performance_id: performanceId
        }, function(response) {
            // Handle the response, e.g., check if the deletion was successful
            if (response.success) {
                // Remove the corresponding row from the table
                row.closest('tr').remove();
            } else {
                alert('Failed to delete data. Please try again.');
            }
        }, 'json');
    });

    function updateScoreInDatabase(row, metadataFieldName, newValue) {
        const performanceId = row.data('performance-id');
        const studentId = $("#currentStudentId").val();
        const score_date = row.find('td[data-field-name="score_date"]').text();
        
        // Assuming that metadataFieldName would be something like "score1_name" and we'd need to update "score1"
        const fieldNameToUpdate = metadataFieldName.replace('_name', '');
    
        const postData = {
            performance_id: performanceId,
            field_name: fieldNameToUpdate, // Use the extracted field name to update the appropriate score column
            new_value: newValue,
            student_id: studentId,
            score_date: score_date
        };
    
        ajaxCall('POST', 'update_performance.php', postData).then(response => {
            if (response && !response.success) {
                console.error('Error updating the score in the database.');
            }
        });
    }
    

    function isDateDuplicate(dateString, currentPerformanceId = null) {
    //console.log("Checking for duplicate of:", dateString);
    let isDuplicate = false;
    $('table').find('td[data-field-name="score_date"]').each(function() {
        const cellDate = $(this).text();
        const performanceId = $(this).closest('tr').data('performance-id');
        if (cellDate === dateString && performanceId !== currentPerformanceId) {
            isDuplicate = true;
            return false; // Break out of the .each loop
        }
    });
    return isDuplicate;
}

    function attachEditableHandler() {
        $('table').on('click', '.editable:not([data-field-name="score8"])', function() {
            const cell = $(this);
            const originalValue = cell.text();
            const input = $('<input type="text">');
            input.val(originalValue);

            let datePickerActive = false;

            if (cell.data('field-name') === 'score_date') {
                input.datepicker({
                    dateFormat: 'mm/dd/yy',
                    beforeShow: function() {
                        datePickerActive = true;
                    },
                    onClose: function(selectedDate) {
    if (isValidDate(new Date(selectedDate))) {
        const currentPerformanceId = cell.closest('tr').data('performance-id');
        if (isDateDuplicate(selectedDate, currentPerformanceId)) {
            //alert("This date already exists. Please choose a different date.");
            cell.html(originalValue); // Revert to the original value
            return;
        }
        cell.text(selectedDate);  // Set the selected date
        cell.append(input.hide());  // Hide the input to show the cell text
        saveEditedDate(cell, selectedDate); // Save the edited date
    }
    datePickerActive = false;
}

                });
                cell.html(input);
                input.focus();
            } else {
                cell.html(input);
                input.focus();
            }

            input.blur(function() {
                if (datePickerActive) {
                    return;
                }
            
                let newValue = input.val();
                cell.html(newValue);
                const performanceId = cell.closest('tr').data('performance-id');
            
                // Check if it's a new row. If so, just return and don't do any AJAX call.
                if (performanceId === 'new') {
                    return;
                }
            
                if (cell.data('field-name') === 'score_date') {
                    const parts = newValue.split('/');
                    if (parts.length !== 3) {
                        cell.html(originalValue);
                        return;
                    }
                    // Save the new value for the database but display the original mm/dd/yyyy format to the user
                    cell.html(newValue);  // The selected value from datepicker is already in mm/dd/yyyy format, so just display it
                    newValue = convertToDatabaseDate(newValue);  // Convert to yyyy-mm-dd format for database use
                    saveEditedDate(cell, newValue); // Save the edited date
                } else {
                    cell.html(newValue);
                }
            
                const fieldName = cell.data('field-name');
                const targetUrl = (performanceId === 'new') ? 'insert_performance.php' : 'update_performance.php';
                const studentId = $('#currentStudentId').val();
                const weekStartDate = convertToDatabaseDate($('#currentWeekStartDate').val());
            
                let postData = {
                    performance_id: performanceId,
                    field_name: fieldName,
                    new_value: newValue,
                    student_id: studentId,
                    score_date: weekStartDate
                };
            
                if (performanceId === 'new') {
                    const row = $(this).closest('tr');
                    let scores = {};
                    for (let i = 1; i <= 10; i++) {
                        const scoreValue = row.find(`td[data-field-name="score${i}"]`).text();
                        scores['score' + i] = scoreValue ? scoreValue : null; // Send null if score is empty
                    }
                    postData.scores = scores;
                }
            
                $.ajax({
                    type: 'POST',
                    url: targetUrl,
                    data: postData,
                    dataType: 'json', // Ensure the response is treated as JSON
                    success: function(response) {
                        // Check if the response contains 'success' property
                        if (response && response.success) {
                            if (performanceId === 'new') {
                                const newRow = $('tr[data-performance-id="new"]');
                                newRow.attr('data-performance-id', response.performance_id);
                                newRow.find('td[data-field-name="score_date"]').text(convertToDisplayDate(response.saved_date));
                            }
            
                            // New code for updating score8 starts here
                            if (['score1', 'score2', 'score3', 'score4'].includes(fieldName)) {
                                const row = cell.closest('tr');
                                const score1 = parseFloat(row.find('td[data-field-name="score1"]').text()) || 0;
                                const score2 = parseFloat(row.find('td[data-field-name="score2"]').text()) || 0;
                                const score3 = parseFloat(row.find('td[data-field-name="score3"]').text()) || 0;
                                const score4 = parseFloat(row.find('td[data-field-name="score4"]').text()) || 0;
                                const average = (score1 + score2 + score3 + score4) / 4;
                                row.find('td[data-field-name="score8"]').text(average.toFixed(2)); // Format the result to 2 decimal places
                                // Update the score8 value in the database
                                updateScoreInDatabase(row, 'score8', average.toFixed(2));
                            }
                        } else {
                            // Handle errors here, e.g., show a notification to the user
                            console.error('Error in AJAX response:', response.error);
                        }
                    },
                    error: function(xhr, status, error) {
                        // Handle AJAX errors here
                        console.error('AJAX Error:', error);
                    }
                });
            });
            

            // Pressing Enter to save changes
            input.off('keypress').keypress(function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    input.blur();
                }
            });
        });
    }

$('#addDataRow').off('click').click(function() {
    // Check for an existing "new" row
    if ($('tr[data-performance-id="new"]').length) {
        alert("Please save the existing new entry before adding another one.");
        return;
    }
    
    const currentDate = getCurrentDate();
if (isDateDuplicate(currentDate)) {
    //alert("An entry for this date already exists. Please choose a different date.");
    return;
}
        const newRow = $("<tr data-performance-id='new'>");
        newRow.append(`<td class="editable" data-field-name="score_date">${currentDate}</td>`);
        
        for (let i = 1; i <= 10; i++) {
            newRow.append(`<td class="editable" data-field-name="score${i}"></td>`);
        }
        
        newRow.append('<td><button class="saveRow">Save</button></td>');
        $("table").append(newRow);

        newRow.find('td[data-field-name="score_date"]').click().blur();
        attachEditableHandler();
        const dateCell = newRow.find('td[data-field-name="score_date"]');
        dateCell.click();
    });

    $(document).on('click', '.saveRow', async function() {
        const row = $(this).closest('tr');
        const performanceId = row.data('performance-id');
    
        // Disable the save button to prevent multiple clicks
        $(this).prop('disabled', true);
    
        // If it's not a new entry, simply return and do nothing.
        if (performanceId !== 'new') {
            //alert("This row is not a new entry. Please click on the cells to edit them.");
            return;
        }
    
        let scores = {};
        for (let i = 1; i <= 10; i++) {
            const scoreValue = row.find(`td[data-field-name="score${i}"]`).text();
            scores[`score${i}`] = scoreValue ? scoreValue : null; // Send null if score is empty
        }
    
        const postData = {
            student_id: CURRENT_STUDENT_ID,
            score_date: convertToDatabaseDate(row.find('td[data-field-name="score_date"]').text()),
            scores: scores // Include the scores object in postData
        };
    
        if (isDateDuplicate(postData.score_date)) {
           // alert("An entry for this date already exists. Please choose a different date.");
            return;
        }
    
        const response = await ajaxCall('POST', 'insert_performance.php', postData);
        if (response && response.performance_id) {
            // Update the table with the newly inserted row
            const newRow = $('tr[data-performance-id="new"]');
            newRow.attr('data-performance-id', response.performance_id);
            newRow.find('td[data-field-name="score_date"]').text(convertToDisplayDate(response.score_date));
            // If you have default scores or other fields returned from the server, update them here too
        
            // Clear the input fields and enable the save button for future entries
            newRow.find('td.editable').text('');
            newRow.find('.saveRow').prop('disabled', false);
        
            // Optionally, display a success message
            // alert("Data saved successfully!");
        
        } else {
            // Handle the error response appropriately
            if (response && response.error) {
                alert("Error: " + response.error);
            } else {
                alert("There was an error saving the data.");
            }
        }
        location.reload();
 
    });
    

        // Initialize the datepicker
        $("#startDateFilter").datepicker({
            dateFormat: 'mm/dd/yy',
            onSelect: function() {
                //console.log("Table rows before draw: " + table.rows().count());
                //console.log("Date selected: " + $(this).val());
                table.draw();
                //console.log("Table rows after draw: " + table.rows().count());
            }
        });

// Custom filter for DataTables
$.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
    let selectedDate = $("#startDateFilter").datepicker("getDate");
    if (!selectedDate) {
        return true; // if no date selected, show all rows
    }

    let rowDate = $.datepicker.parseDate("mm/dd/yy", data[0]);

    // Convert both dates to time for a safer comparison
    let rowDateTime = rowDate.getTime();
    let selectedDateTime = selectedDate.getTime();

    //console.log(`Comparing rowDate ${rowDate} to selectedDate ${selectedDate}. Result: ${rowDateTime >= selectedDateTime}`);
    return rowDateTime >= selectedDateTime;
});

        $(document).on('keypress', '.saveRow', function(e) {
            if (e.which === 13) {
                e.preventDefault();
            }
        });

        // Initialization code
        $('#currentWeekStartDate').val(getCurrentDate());
        attachEditableHandler();

        $.fn.dataTable.ext.type.detect.unshift(function(value) {
            return value && value.match(/^(\d{1,2}\/\d{1,2}\/\d{4})$/) ? 'date-us' : null;
        });

        $.fn.dataTable.ext.type.order['date-us-pre'] = function(data) {
            var date = data.split('/');
            return (date[2] + date[0] + date[1]) * 1;
        };

// Declare the columns variable before the AJAX call
let columns;

// Perform an AJAX request to retrieve the data
$.ajax({
    type: 'GET', // Adjust the type as needed
    url: './users/fetch_data.php',
    dataType: 'json', // Adjust the data type as needed
    success: function(response) {
        // Define the columns based on the response data
        if (response && Array.isArray(response.columnHeaders)) {
            const columns = [
                { "type": "date-us" },
                ...response.columnHeaders.map(header => ({ title: header })),
            ];
        
            // Now you can use the 'columns' array as intended
            const table = $('table').DataTable({
                "order": [[0, "asc"]],
                "lengthChange": false,
                "paging": true,
                "searching": true,
                "info": false,
                "columns": columns
            });
        
            // Assuming you have an array of data rows in response.performanceData
            table.rows.add(response.performanceData).draw();
        } else {
            console.error("Invalid or missing 'response.columnHeaders'");
        }
    }
});

    });