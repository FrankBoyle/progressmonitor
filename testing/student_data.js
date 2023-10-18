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
        var weekStartDate = $(this).find('td[data-field-name="score_date"]').text().trim(); // Trim spaces
        var scoreValue = $(this).find(`td[data-field-name="${scoreField}"]`).text().trim(); // Trim spaces

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

$(document).ready(function () {
    const CURRENT_STUDENT_ID = $('#currentStudentId').val();
    const columnNames = Object.values(columnHeaders);
    let dateAscending = true;

    // Initialize the page
    initializePage();

    // Event listeners
    $('#metadataIdSelector').on('change', handleMetadataChange);
    $('#metadataIdSelector').on('change', function() {
        const selectedMetadataId = $(this).val();
        
        // Update the URL with the selected metadata_id
        const currentUrl = window.location.href;
        const updatedUrl = updateUrlParameter(currentUrl, 'metadata_id', selectedMetadataId);
        window.location.href = updatedUrl;
        
        // Update the chart headers based on the selected metadata_id
        updateChartHeaders(selectedMetadataId);
    });
    $('#toggleDateOrder').on('click', toggleDateOrder);
    $('#addDataRow').on('click', addNewDataRow);
    $(document).on('click', '.deleteRow', deleteDataRow);
    $(document).on('click', '.saveRow', saveDataRow);
    $("#startDateFilter").datepicker({
        dateFormat: 'mm/dd/yy',
        onSelect: filterTableByDate
    });

    function initializePage() {
        initializeDatePicker();
        fetchDefaultHeaders();
        attachEditableHandler();
    }

    function updateUrlParameter(url, paramKey, paramValue) {
        const urlParams = new URLSearchParams(window.location.search);
        urlParams.set(paramKey, paramValue);
        return `${url.split('?')[0]}?${urlParams.toString()}`;
    }
    
    function initializeDatePicker() {
        $("#startDateFilter").datepicker({
            dateFormat: 'mm/dd/yy',
            onSelect: filterTableByDate
        });
    }

    function fetchColumnHeaders(metadataId) {
        // Send an AJAX request to fetch the JSON data
        $.ajax({
            url: './users/fetch_data.php', // Replace with the actual URL to your JSON data
            type: 'GET',
            dataType: 'json',
            success: function (jsonData) {
                // Use the displayedColumns object directly
                const columnHeaders = jsonData.displayedColumns;
    
                // Now you can use the 'columnHeaders' object as needed
                updateTable(columnNames, performanceData);            },
            error: function (xhr, status, error) {
                console.error('AJAX Error:', status, error);
            }
        });
    }
    
    function updateChartHeaders(selectedMetadataId) {
        // Make an AJAX request to your server to fetch the updated chart headers
        $.ajax({
            url: './users/fetch_data.php', // Replace with the actual URL to fetch chart headers
            type: 'GET',
            data: { metadataId: selectedMetadataId }, // Send the selected metadata_id as a parameter
            dataType: 'json', // Assuming the response will be in JSON format
            success: function(response) {
                // Assuming response.data contains the updated chart headers as an array
                const updatedChartHeaders = response.data;
                
                // Update the chart headers in your HTML
                const chartTable = $('#chartTable'); // Replace with the actual ID or selector of your chart table
                
                // Clear existing headers
                chartTable.find('thead').empty();
                
                // Generate new headers based on updatedChartHeaders
                const thead = $('<thead>');
                const headerRow = $('<tr>');
                headerRow.append($('<th>Date</th>'));
                
                // Iterate through updatedChartHeaders and add them as table headers
                updatedChartHeaders.forEach(function(columnName) {
                    headerRow.append($('<th>' + columnName + '</th>'));
                });
                
                headerRow.append($('<th>Action</th>'));
                thead.append(headerRow);
                chartTable.append(thead);
            },
            error: function(error) {
                // Handle any errors that occur during the AJAX request
                console.error('Error fetching updated chart headers:', error);
            }
        });
    }
    
    function handleMetadataChange() {
        const selectedMetadataId = $(this).val();
        if (selectedMetadataId === '0') {
            fetchDefaultHeaders();
        } else {
            fetchColumnHeaders(selectedMetadataId);
        }
    }

    function fetchDefaultHeaders() {
        $.ajax({
            url: './users/fetch_data.php',
            method: 'GET',
            data: {
                student_id: CURRENT_STUDENT_ID, // Pass your student ID here
                action: 'fetchDefaultMetadataId'
            },
            dataType: 'json',
            success: function (response) {
                console.log('Response:', response);
                // Check for errors in the response
                if (response.hasOwnProperty('error')) {
                    console.error('Server Error:', response.error);
                    // Handle the error as needed, e.g., display an error message to the user
                } else {
                    // Handle the successful response here
                    var metadataId = response.metadataId;
                    var displayedColumns = response.displayedColumns;
                    // Process and use the data as needed
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX Error:', error);
                console.log('HTTP Status Code:', xhr.status);
                console.log('Status Text:', xhr.statusText);
                console.log('Response Text:', xhr.responseText);
                // Handle the AJAX error as needed
            }
        });
    }   
    
    function fetchTableData(metadataId) {
        const data = {
            action: 'fetchPerformanceData',
            student_id: CURRENT_STUDENT_ID,
            metadata_id: metadataId,
        };
    
        $.ajax({
            type: 'GET',
            url: './users/fetch_data.php',
            data: data,
            dataType: 'json',
            success: function (response) {
                console.log('Response:', response);
                
                if (!response || $.isEmptyObject(response)) {
                    console.error('Empty or invalid response from the server.');
                    return;
                }
    
                if (response && response.columnHeaders && response.performanceData) {
                    updateTable(response.columnHeaders, response.performanceData);
                } else {
                    console.error('Invalid response:', response);
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX Error:', error);
                
                // Log the HTTP status code and status text
                console.log('HTTP Status Code:', xhr.status);
                console.log('Status Text:', xhr.statusText);
    
                // Check if the response text is available
                if (xhr.responseText) {
                    console.log('Response Text:', xhr.responseText);
                } else {
                    console.error('Empty Response Text');
                }
            }
        });
    }     
     
    function updateTable(columnHeaders, performanceData) {
        // Debugging: Log data to console
        console.log('performanceData:', performanceData);
        console.log('columnHeaders:', columnHeaders);
    
        // Update table headers with new column names
        const table = $('table');
        table.find('thead').remove();
    
        // Generate new table headers based on columnHeaders
        const thead = $('<thead>');
        const headerRow = $('<tr>');
        headerRow.append($('<th>Date</th>'));
    
        // Convert columnHeaders object to an array of column names
        const columnNames = Object.values(columnHeaders);
    
        // Debugging: Log columnNames to console
        console.log('columnNames:', columnNames);
    
        $.each(columnNames, function (index, columnName) {
            headerRow.append($('<th>' + columnName + '</th>'));
        });
    
        headerRow.append($('<th>Action</th>'));
        thead.append(headerRow);
        table.append(thead);
    
        // Use DataTables API to populate the table with performanceData
        const dataTable = table.DataTable({
            "order": [[0, "asc"]],
            "lengthChange": false,
            "paging": true,
            "searching": true,
            "info": false,
            "columns": [
                { "type": "date-us" },
                ...columnNames.map(header => ({ title: header })),
                { "orderable": false }
            ]
        });
    
        // Check for the existence of performanceData before updating
        if (performanceData && performanceData.length > 0) {
            // Clear existing data
            dataTable.clear();
    
            // Add new data
            dataTable.rows.add(performanceData);
    
            // Redraw the table
            dataTable.draw();
        } else {
            // Display a message when there's no data
            table.append('<tbody><tr><td colspan="' + (columnNames.length + 2) + '">No data available</td></tr></tbody>');
        }
    }     

// Assuming you have a table element with the ID "myDataTable" in your HTML
const table = $('#myDataTable');

// Extract columnNames from columnHeaders
const columnNames = Object.values(columnHeaders);

// Check if DataTables is already initialized for the table
if (!$.fn.DataTable.isDataTable('#myDataTable')) {
    const dataTable = table.DataTable({
        "order": [[0, "asc"]],
        "lengthChange": false,
        "paging": true,
        "searching": true,
        "info": false,
        "columns": [
            { "type": "date-us" },
            ...columnNames.map(header => ({ title: header })),
            { "orderable": false }
        ]
    });

    // Check for the existence of performanceData before updating
    if (performanceData && performanceData.length > 0) {
        // Clear existing data
        dataTable.clear();

        // Add new data
        dataTable.rows.add(performanceData);

        // Redraw the table
        dataTable.draw();
    } else {
        // Display a message when there's no data
        table.append('<tbody><tr><td colspan="' + (columnNames.length + 2) + '">No data available</td></tr></tbody>');
    }
} else {
    // DataTables is already initialized, so update the data
    const dataTable = table.DataTable();
    
    // Check for the existence of performanceData before updating
    if (performanceData && performanceData.length > 0) {
        // Clear existing data
        dataTable.clear();

        // Add new data
        dataTable.rows.add(performanceData);

        // Redraw the table
        dataTable.draw();
    } else {
        // Display a message when there's no data
        table.append('<tbody><tr><td colspan="' + (columnNames.length + 2) + '">No data available</td></tr></tbody>');
    }
}



    function toggleDateOrder() {
        const table = $('table').DataTable();
        dateAscending = !dateAscending;
        table.order([0, dateAscending ? 'asc' : 'desc']).draw();
    }

    function addNewDataRow() {
        if ($('tr[data-performance-id="new"]').length) {
            alert("Please save the existing new entry before adding another one.");
            return;
        }

        const currentDate = getCurrentDate();
        if (isDateDuplicate(currentDate)) {
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
    }

function deleteDataRow(row) {
    const performanceId = row.data('performance-id');

    if (!confirm('Are you sure you want to delete this row?')) {
        return;
    }

    // Send a request to delete the data from the server
    $.post('delete_performance.php', {
        performance_id: performanceId
    }, function (response) {
        // Handle the response, e.g., check if the deletion was successful
        if (response.success) {
            // Remove the corresponding row from the table
            row.remove();
        } else {
            alert('Failed to delete data. Please try again.');
        }
    }, 'json');
}

function saveDataRow(row) {
    const performanceId = row.data('performance-id');

    // Disable the save button to prevent multiple clicks
    row.find('.saveRow').prop('disabled', true);

    // If it's not a new entry, simply return and do nothing.
    if (performanceId !== 'new') {
        return;
    }

    const scores = {};
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
        alert("An entry for this date already exists. Please choose a different date.");
        row.find('.saveRow').prop('disabled', false);
        return;
    }

    $.ajax({
        type: 'POST',
        url: 'insert_performance.php',
        data: postData,
        dataType: 'json',
        success: function (response) {
            console.log('Response:', response);
            if (response && response.performance_id) {
                // Update the table with the newly inserted row
                row.attr('data-performance-id', response.performance_id);
                row.find('td[data-field-name="score_date"]').text(convertToDisplayDate(response.score_date));
                // If you have default scores or other fields returned from the server, update them here too

                // Clear the input fields and enable the save button for future entries
                row.find('td.editable').text('');
                row.find('.saveRow').prop('disabled', false);

                // Optionally, display a success message
                alert("Data saved successfully!");
            } else {
                // Handle the error response appropriately
                if (response && response.error) {
                    alert("Error: " + response.error);
                } else {
                    alert("There was an error saving the data.");
                }
            }
        },
        error: function (xhr, status, error) {
            // Handle AJAX errors here
            console.error('AJAX Error:', error);
            alert("There was an error saving the data.");
            row.find('.saveRow').prop('disabled', false);
        }
    });
}

function filterTableByDate() {
    const selectedDate = $("#startDateFilter").datepicker("getDate");
    if (!selectedDate) {
        // If no date selected, show all rows
        $('table').DataTable().search('').draw();
        return;
    }

    // Format selected date as MM/DD/YYYY
    const formattedDate = $.datepicker.formatDate('mm/dd/yy', selectedDate);

    // Apply date filter to the table
    $('table').DataTable().search(formattedDate).draw();
}

function getCurrentDate() {
    const currentDate = new Date();
    const month = (currentDate.getMonth() + 1).toString().padStart(2, '0');
    const day = currentDate.getDate().toString().padStart(2, '0');
    const year = currentDate.getFullYear();
    return `${month}/${day}/${year}`;
}

function isDateDuplicate(dateString) {
    let isDuplicate = false;
    $('table').find('td[data-field-name="score_date"]').each(function () {
        const cellDate = $(this).text();
        if (cellDate === dateString) {
            isDuplicate = true;
            return false; // Break out of the .each loop
        }
    });
    return isDuplicate;
}

function attachEditableHandler() {
    $('table').on('click', '.editable', function () {
        const cell = $(this);
        const originalValue = cell.text();
        const input = $('<input type="text">');
        input.val(originalValue);

        let datePickerActive = false;

        if (cell.data('field-name') === 'score_date') {
            input.datepicker({
                dateFormat: 'mm/dd/yy',
                beforeShow: function () {
                    datePickerActive = true;
                },
                onClose: function (selectedDate) {
                    if (isValidDate(new Date(selectedDate))) {
                        const currentPerformanceId = cell.closest('tr').data('performance-id');
                        if (isDateDuplicate(selectedDate, currentPerformanceId)) {
                            alert("This date already exists. Please choose a different date.");
                            cell.html(originalValue); // Revert to the original value
                            return;
                        }
                        cell.text(selectedDate); // Set the selected date
                        cell.append(input.hide()); // Hide the input to show the cell text
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

        input.blur(function () {
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
                cell.html(newValue); // The selected value from datepicker is already in mm/dd/yyyy format, so just display it
                newValue = convertToDatabaseDate(newValue); // Convert to yyyy-mm-dd format for database use
                saveEditedDate(cell, newValue); // Save the edited date
            } else {
                cell.html(newValue);
            }

            const fieldName = cell.data('field-name');
            const targetUrl = (performanceId === 'new') ? 'insert_performance.php' : 'update_performance.php';
            const student_id = $('#currentStudentId').val();
            const weekStartDate = convertToDatabaseDate($('#currentWeekStartDate').val());

            let postData = {
                performance_id: performanceId,
                field_name: fieldName,
                new_value: newValue,
                student_id: student_id,
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
                success: function (response) {
                    // Check if the response contains 'success' property
                    if (response && response.success) {
                        console.log('Response:', response);
                        if (performanceId === 'new') {
                            const newRow = $('tr[data-performance-id="new"]');
                            newRow.attr('data-performance-id', response.performance_id);
                            newRow.find('td[data-field-name="score_date"]').text(convertToDisplayDate(response.score_date));
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
                error: function (xhr, status, error) {
                    // Handle AJAX errors here
                    console.error('AJAX Error:', error);
                }
            });
        });

        // Pressing Enter to save changes
        input.off('keypress').keypress(function (e) {
            if (e.which === 13) {
                e.preventDefault();
                input.blur();
            }
        });
    });
}
});