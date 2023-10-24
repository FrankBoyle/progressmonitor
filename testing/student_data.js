var benchmark = null;
var selectedChartType = 'line'; // Default chart type
var xCategories = [];

$(document).ready(function() {
    initializeChart();

    // Instead of using .click() we're using the more verbose .on('click' ...) which is more explicit
    $(document).on('click', '#updateBenchmark', function() {
        console.log("Update benchmark button clicked"); // This should log when you click the button

        var newBenchmarkValue = $("#benchmarkValue").val().trim();
        console.log("Benchmark value entered:", newBenchmarkValue); // This should log the value you entered

        benchmark = parseFloat(newBenchmarkValue);

        if (isNaN(benchmark)) {
            console.log("Invalid number entered"); // This will confirm if your input was not a number
            alert("Invalid benchmark value. Please enter a number.");
        } else {
            console.log("Valid number entered, updating chart."); // This confirms that the number was valid and the function should be executed
            updateChartWithCurrentSelections(benchmark); // Make sure this function is defined and works correctly
        }
    });

    // For checkboxes, instead of .click(), we use .on('click', ...) and provide the callback function
    $("input[name='selectedColumns[]']").on('click', function() {
        updateChartWithCurrentSelections();
    });

    // Explicit event handling for radio buttons
    $("input[name='chartType']").on('change', function() {
        updateChartWithCurrentSelections();
    });

    // Explicit event handling for toggle switch
    $("#toggleTrendlines").on('change', function() {
        updateChartWithCurrentSelections();
    });

    // Initialize the chart with the current selections
    updateChartWithCurrentSelections();
});

function updateChartWithCurrentSelections() {
    var selectedColumns = [];
    $("input[name='selectedColumns[]']:checked").each(function() {
        selectedColumns.push($(this).val());
    });

    var selectedChartType = $("input[name='chartType']:checked").val();
    
    updateChart(selectedColumns, selectedChartType, xCategories, benchmark); // Update chart with current selections
}

function initializeChart() {
    window.chart = new ApexCharts(document.querySelector("#chart"), getChartOptions([], []));
    window.chart.render();
}

function getChartData(scoreField) {
    var chartData = [];
    xCategories = [];

    $('tr[data-performance-id]').each(function() {
        var weekStartDate = $(this).find('td[data-field-name="score_date"]').text();
        var scoreValue = $(this).find('td[data-field-name="' + scoreField + '"]').text();

        if (weekStartDate !== 'New Entry' && !isNaN(parseFloat(scoreValue))) {
            chartData.push({
                x: weekStartDate,  // Directly use the date string
                y: parseFloat(scoreValue)
            });

            xCategories.push(weekStartDate);
        }
    });

    // Sorting logic should be outside the loop
    const sortedChartData = chartData.sort((a, b) => {
        return new Date(a.x) - new Date(b.x);
    });

    const sortedCategories = xCategories.sort((a, b) => {
        return new Date(a) - new Date(b);
    });

    xCategories = sortedCategories;

    return { chartData: sortedChartData, xCategories: sortedCategories };
}

function updateChart(selectedColumns, selectedChartType, xCategories, benchmark) {
    var seriesData = [];
    // Define colors for scores and their trendlines
    const colors = ['#2196F3', '#FF5722', '#4CAF50', '#FFC107', '#9C27B0', '#607D8B']; // Add more colors as needed
    var scoreNamesMap = getScoreNamesMap();
    var actualScoreName = '';
    var showTrendlines = $("#toggleTrendlines").is(':checked'); // Check if the trendlines should be displayed

    if (!xCategories || !Array.isArray(xCategories)) {
        xCategories = [];  // Make sure xCategories is an array
    }
    // Check if selectedColumns is an array
    if (!Array.isArray(selectedColumns)) {
        selectedColumns = [selectedColumns]; // Ensure it's an array
    }

    selectedColumns.forEach(function(selectedColumn, index) {
        var { chartData, xCategories: columnCategories } = getChartData(selectedColumn);
        actualScoreName = scoreNamesMap[selectedColumn];

        // Assign colors to data series and trendlines based on index
        var scoreColor = colors[index % colors.length];

        seriesData.push(
            {
                name: actualScoreName,
                data: chartData,
                color: scoreColor,  // Set color property here for the series
                connectNulls: true,
                dataLabels: {
                    enabled: true // Enable data labels for the Selected Score series
                },
            })
                    // Calculate trendline and add to seriesData only if showTrendlines is true
        if (showTrendlines) {
            var trendlineFunction = calculateTrendline(chartData);
            var trendlineData = chartData.map((item, index) => {
                return {
                    x: item.x,
                    y: trendlineFunction(index) // calculate y based on trendline function
                };
            });
            seriesData.push(
                {
                    name: 'Trendline ' + actualScoreName,
                    data: trendlineData,
                    color: scoreColor,  // Set color property here for the series
                    stroke: {
                        dashArray: 3, // This makes the line dashed; the number controls the dash length
                    },
                    connectNulls: true,
                    dataLabels: {
                        enabled: false // Disable data labels for the Trendline series
                    }
                });  
            }  
        });

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
    
    // Pass seriesData to getChartOptions
    window.chart.updateOptions(getChartOptions(seriesData, xCategories, selectedChartType, actualScoreName));
};

function getChartOptions(dataSeries, xCategories, selectedChartType, actualScoreName) {
    //console.log(selectedChartType);
    var chartType = selectedChartType; // Get the selected chart type
    //console.log(chartType);
    let colors;
    if (dataSeries && dataSeries.length > 0) {
        colors = dataSeries.map(series => {
            if (series.stroke && series.stroke.colors && series.stroke.colors[0]) {
                return series.stroke.colors[0];
            }
            return '#000000'; // default color if no color is defined for a series
        });
    } else {
        colors = ['#000000']; // default color array if dataSeries is invalid
    }

    var dataLabelsSettings = {
        enabled: true, // Globally enabling labels, to be selectively displayed.
        formatter: function (val, opts) {
            // This function decides when and where to display a label.
    
            // Information about the data point and series.
            var seriesIndex = opts.seriesIndex;
            var seriesName = opts.w.globals.seriesNames[seriesIndex]; // Getting the series name.
    
            // Check if it's the specific series you want to label.
            // Please adjust "YourTargetSeriesName" to match your actual series name or condition.
            //if (seriesName === 'actualScoreName') {
            //    return val; // Returning the value to be displayed as the label for the line series points.
            //}
    
            // If not the series we're interested in, don't show a label.
            //return "";
        },
        offsetY: -10, // You can adjust this value as needed.
        style: {
            fontSize: '12px',
            colors: ['#333']
        }
    };
    
    if (chartType === 'line') {
        dataLabelsSettings.enabled = true;
        dataLabelsSettings.enabledOnSeries = [0, 2, 4, 6, 8, 10]; // Or specify the exact series indexes of line charts.
    }
    
    return {
        series: dataSeries,
        chart: {
            type: chartType,
            stacked: false,
            width: 600,
            colors: colors,
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

        dataLabels: dataLabelsSettings,
        
        stroke: {
            show: true,
            curve: 'smooth',
            width: dataSeries.map(series => {
                // Check if 'name' exists before calling 'startsWith'
                if (series.name && series.name.startsWith('Trendline ')) {
                    return 1;  // or whatever dash length you prefer
                } else {
                    return 3;  // solid line for others
                }
            }),
        },

        markers: {
            size: dataSeries.map(series => {
                if (series.name === 'actualScoreName') {
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
        //colors: ['#2196F3', '#FF5722', '#000000']
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

function getScoreNamesMap() {
    var scoreNamesMap = {};
    // Assume each checkbox is immediately contained within a label element as per your HTML
    $('input[name="selectedColumns[]"]').each(function() {
        var $checkbox = $(this);
        // The parent label's text is the score name
        var scoreName = $checkbox.parent().text().trim();
        var checkboxValue = $checkbox.val();
        scoreNamesMap[checkboxValue] = scoreName;
    });
    return scoreNamesMap;
}

////////////////////////////////////////////////

$(document).ready(function() {
    // Retrieve the metadata_id from the URL parameter
    const urlParams = new URLSearchParams(window.location.search);
    const metadata_id = urlParams.get('metadata_id');

    // Set the retrieved metadata_id as the value of the input field
    $('#metadataIdInput').val(metadata_id);

    // Now the input field should have the correct metadata_id value
    //console.log(metadata_id);

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
    
            // Debugging: Log the response
            //console.log('Response:', response);
    
            return response;
        } catch (error) {
            console.error('Error during AJAX call:', error);
    
            // Debugging: Log the error response (if available)
            if (error.responseJSON) {
                console.error('Error response:', error.responseJSON);
                return error.responseJSON;  // Return the parsed JSON error message
            } else {
                //return { error: 'Unknown error occurred.' };  // Provide a generic error message
            }
        }   
    }
    

    async function saveEditedDate(cell, newDate) {
        const performanceId = cell.closest('tr').data('performance-id');
        const fieldName = cell.data('field-name');
        const studentId = CURRENT_STUDENT_ID;
        const weekStartDate = convertToDatabaseDate($('#currentWeekStartDate').val());
        const school_id = $('#schoolIdInput').val();

        const postData = {
            performance_id: performanceId,
            field_name: fieldName,
            new_value: convertToDatabaseDate(newDate), // Convert to yyyy-mm-dd format before sending
            score_date: weekStartDate,
            student_id: studentId,
            metadata_id: metadata_id,
            school_id: school_id

        };
        //console.log(postData);
        //console.log("studentID:", student_id);


        ajaxCall('POST', 'update_performance.php', postData).then(response => {
            //console.log(response); // <-- This is the debug line. 
        
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
        const studentId = CURRENT_STUDENT_ID;
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
    

    function isDateDuplicate(dateString, currentPerformanceId = null, currentStudentId = null, currentMetadataId = null) {
        //console.log("Checking for duplicate of:", dateString);
        let isDuplicate = false;
    
        $('table').find('td[data-field-name="score_date"]').each(function() {
            const cellDate = $(this).text();
            const $currentRow = $(this).closest('tr');
            const performanceId = $currentRow.data('performance-id');
            const studentId = $currentRow.data('student-id'); // Retrieve the student_id
            const urlParams = new URLSearchParams(window.location.search);
            const metadata_id = urlParams.get('metadata_id');    
            // Check if date, student_id, and metadata_id are the same, but not the same performance entry
            if (cellDate === dateString 
                && performanceId !== currentPerformanceId 
                && studentId === currentStudentId 
                && metadata_id === currentMetadataId) {
                isDuplicate = true;
                return false; // Break out of the .each loop
            }
        });
    
        return isDuplicate;
    }
    

    function attachEditableHandler() {
        //$('table').on('click', '.editable:not([data-field-name="score8"])', function() {
        $('table').on('click', '.editable', function() {
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

                //const performanceId = cell.closest('tr').data('performance-id');
                const fieldName = cell.data('field-name');
                const targetUrl = (performanceId === 'new') ? 'insert_performance.php' : 'update_performance.php';
                const studentId = $('#currentStudentId').val();
                const weekStartDate = convertToDatabaseDate($('#currentWeekStartDate').val());
                const school_id = $('#schoolIdInput').val();

                let postData = {
                    performance_id: performanceId,
                    field_name: fieldName,
                    new_value: newValue,
                    student_id: studentId,
                    score_date: weekStartDate,
                    metadata_id: metadata_id,
                    school_id: school_id,
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
                    success: function(response) {
                        if (performanceId === 'new') {
                            const newRow = $('tr[data-performance-id="new"]');
                            newRow.attr('data-performance-id', response.performance_id);
                            newRow.find('td[data-field-name="score_date"]').text(convertToDisplayDate(response.saved_date));
                        }
    
                        /* New code for updating score8 starts here
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
                        */
                    },
                    error: function() {
                        // Handle any error here, e.g., show a notification to the user
                        //alert("There was an error updating the data.");
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
        const school_id = $('#schoolIdInput').val();
        const urlParams = new URLSearchParams(window.location.search);
        const metadata_id = urlParams.get('metadata_id');
        console.log(metadata_id);

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
            if (scoreValue.trim() === '') {
                scores[`score${i}`] = null; // Send null if score is empty or only contains whitespace
            } else {
                // Handle non-empty score values, e.g., validate or parse them if necessary
                scores[`score${i}`] = scoreValue;
            }
        }
        
    
        const postData = {
            student_id: CURRENT_STUDENT_ID,
            score_date: convertToDatabaseDate(row.find('td[data-field-name="score_date"]').text()),
            scores: scores, // Include the scores object in postData
            metadata_id: metadata_id,
            school_id: school_id,
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
                //alert("Error: " + response.error);
            } else {
                //alert("There was an error saving the data.");
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

        let table = $('table').DataTable({
            "order": [[0, "asc"]],
            "lengthChange": false,
            //"searching": false,
            "paging": false,
            "info": false,
            "columns": [
                { "type": "date-us" },
                null, null, null, null, null, null, null, null, null, null, null
            ]
        });
});