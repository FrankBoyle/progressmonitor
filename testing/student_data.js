// Defining global variables for the script.
var benchmark = null;
var benchmarkSeriesIndex = null; // It's null initially because the series index is not determined yet.
var selectedChartType = 'line';  // Default chart type
var xCategories = [];
let chart = null;  // This makes the chart variable accessible throughout the script.
let headerNames = [];  // Will store header names extracted from the table.
let allSeries = [];  // Will store all data series.
let dates = [];  // To store extracted dates from table rows.
let finalSeriesData = [];
let trendlineSeriesData = []; // Declare both as global variables
// Initialize color objects for series and trendlines
let seriesColors = {};
let trendlineColors = {};

console.log("Initial global variables:", {
    benchmark,
    benchmarkSeriesIndex,
    selectedChartType,
    xCategories,
    chart,
    headerNames,
    allSeries,
    dates
});

// Initialize the accordion.
$(function() {
    $("#accordion").accordion({
        collapsible: true,
        heightStyle: "content",
        active: false, // Ensure all panels are closed initially
        activate: function(event, ui) {
            if (ui.newPanel.has('#chart').length) {
                // If the chart is not yet initialized, do so.
                // Otherwise, refresh the chart.
                if (!chart) {
                    initializeChart();
                } else {
                    chart.updateSeries(chart.w.globals.series);
                }
            }
        }
    });
});

// Extracts dates and scores data from the provided HTML table.
function extractDataFromTable() {
    const tableRows = document.querySelectorAll("table tbody tr");
    const dates = [];
    const scores = [];

    tableRows.forEach((row) => {
        const dateCell = row.querySelector("td:first-child");
        if (dateCell) {
            dates.push(dateCell.textContent.trim());
        } else {
            dates.push(""); // or some default date or error handling
        }

        const scoreCells = row.querySelectorAll("td:not(:first-child):not(:last-child)");
        const rowScores = [];

        scoreCells.forEach((cell) => {
            rowScores.push(parseInt(cell.textContent || '0', 10));
        });

        scores.push(rowScores);
    });
    console.log("Extracted dates:", dates);
    console.log("Extracted scores:", scores);

    return { dates, scores };
}

// Populates the series data based on selected columns, header map, and scores.
function populateSeriesData(selectedColumns, headerMap, scores) {
    const seriesData = [];
    for (const col of selectedColumns) {
      const headerName = headerMap[col];
      const headerIndex = headerNames.indexOf(headerName);
      if (headerIndex !== -1) {
        seriesData.push(scores.map(scoreRow => scoreRow[headerIndex]));
      }
    }
    console.log("Populated series data:", seriesData);

    return seriesData;
  }

// Modify generateSeriesData to skip dates with missing values
function generateSeriesData(scores, headerNames, customNames = []) {
    const seriesList = [];
    for (let i = 1; i < headerNames.length; i++) { // Changed the loop condition
        const scoreData = scores.map(row => row[i - 1]);
        const seriesData = scoreData.filter(value => !isNaN(value)); // Filter out NaN values
        seriesList.push({
            name: customNames[i - 1] || `score${i}`,
            data: seriesData,
            //visible: false,  // Hide the series by default
        });
    }
    console.log("Generated series list:", seriesList);
    return seriesList;
}

// This function will now return the new series list
function getUpdatedSeriesNames(seriesList, customColumnNames) {
    return seriesList.map((series, index) => {
        const customColumnName = customColumnNames[index] || headerNames[index + 1];
        console.log("Updated series list with custom column names:", seriesList);

        return {
            ...series,
            name: customColumnName,
        };
    });
}

// Updates all series names based on provided custom column names.
function updateAllSeriesNames(customColumnNames) {
    allSeries = allSeries.map((series, index) => {
        const customColumnName = customColumnNames[index] || headerNames[index + 1];
        console.log("All series after updating names:", allSeries);

        return {
            ...series,
            name: customColumnName,
        };
    });
}

function generateFinalSeriesData(data, selectedColumns) {
    const finalSeriesData = [];

    for (let i = 0; i < selectedColumns.length; i++) {
        const columnName = selectedColumns[i];
        const columnData = data[columnName]; // Assuming 'data' is an object with column data

        if (columnData) {
            finalSeriesData.push({
                name: columnName,
                data: columnData,
                // Additional properties for the series, if needed
            });
        }
    }

    return finalSeriesData;
}

// Update the chart based on selected columns.
function updateChart(selectedColumns, seriesColors, trendlineColors) { // Update function signature
    // Clear existing series data
    chart.updateSeries([]);

    // Create a new series array based on selected columns
    const newSeriesData = allSeries.filter((series, index) => selectedColumns.includes(headerNames[index + 1]));

    // For each series in newSeriesData, calculate its trendline and add it to trendlineSeriesData
    const trendlineSeriesData = [];
    newSeriesData.forEach(series => {
        const trendlineData = getTrendlineData(series.data);
        trendlineSeriesData.push({
            name: series.name + ' Trendline',
            data: trendlineData,
            type: 'line',
            ...trendlineOptions,
            // Set the trendline color to match the series color
            //color: trendlineColors[series.name.replace(' Trendline', '')], // Remove ' Trendline' when looking up the color
        });
    });

    // Add trendline data to series
    const finalSeriesData = [...newSeriesData, ...trendlineSeriesData];
    console.log("New series data based on selected columns:", newSeriesData);
    console.log("Trendline series data:", trendlineSeriesData);
    console.log("Final series data for updating the chart:", finalSeriesData);

    // Update the chart with the new series data and updated names
    chart.updateSeries(finalSeriesData);

    // Update series names in the legend
    chart.updateOptions({
        // ... (other options)
        colors: seriesColors.concat(Object.values(trendlineColors)),

        stroke: {
            width: finalSeriesData.map(series =>
                series.name.includes('Trendline') ? trendlineOptions.width : 4
            ),
            dashArray: finalSeriesData.map(series =>
                series.name.includes('Trendline') ? trendlineOptions.dashArray : 0
            ),
        },
    });
}


// Initializes the chart with default settings.
function initializeChart() {
    const headerRow = document.querySelector('#dataTable thead tr');
    headerNames = Array.from(headerRow.querySelectorAll('th')).map(th => th.innerText.trim());
    const { dates, scores } = extractDataFromTable();
    allSeries = generateSeriesData(scores, headerNames); // Add this line

    // Get the custom column names from the checkboxes
    const selectedColumns = Array.from(document.querySelectorAll("#columnSelector input:checked"))
        .map(checkbox => checkbox.getAttribute("data-column-name") || '');

    allSeries = getUpdatedSeriesNames(allSeries, selectedColumns);
    chart = new ApexCharts(document.querySelector("#chart"), getChartOptions(dates, trendlineSeriesData, seriesColors, trendlineColors));
    console.log("Extracted header names:", headerNames);
    console.log("Selected columns from checkboxes:", selectedColumns);

    chart.render();    
// Generate colors for series and trendlines
const colorsAndTrendlineColors = generateColors(finalSeriesData, trendlineSeriesData);
seriesColors = colorsAndTrendlineColors.seriesColors;
trendlineColors = colorsAndTrendlineColors.trendlineColors;
updateChart(selectedColumns, seriesColors, trendlineColors);

    // Listen for checkbox changes
    document.getElementById("columnSelector").addEventListener("change", debounce(function() {
        const selectedColumns = Array.from(document.querySelectorAll("#columnSelector input:checked"))
            .map(checkbox => checkbox.getAttribute("data-column-name") || ''); // Get custom names

        // Update all series names with custom names
        updateAllSeriesNames(selectedColumns);

        // Update the chart series with the updated names
        chart.updateSeries(allSeries);

        // Update the chart with new series data and trendlines
updateChart(selectedColumns, colorsAndTrendlineColors);
    }, 0));
};

// The debounce function
function debounce(func, wait) {
    let timeout;
    return function() {
        const context = this, args = arguments;
        clearTimeout(timeout);
        timeout = setTimeout(() => {
            func.apply(context, args);
        }, wait);
    };
}

var dataSeries = [
    //... Your series data ...
];

// Create the data labels settings
var dataLabelsSettings = {
    enabled: true,
    formatter: function(val, opts) {
        var seriesName = opts.w.config.series[opts.seriesIndex].name;

        // Hide data labels for 'Benchmark' and 'Trendline'.
        if (seriesName === 'Benchmark' || seriesName.includes('Trendline')) {
            return '';  // Return empty string to hide the label
        }

        // Format for bar charts
        if (opts.w.config.chart.type === 'bar') {
            return val.toFixed(0);
        }

        // Format for line charts
        if (opts.w.config.chart.type === 'line') {
            return val;  // or val.toFixed(2) if you want two decimal places
        }

        // Default return
        return val;
    }
};

function getChartOptions(dates, trendlineSeriesData) {
    const colorOptions = generateColors(finalSeriesData, trendlineSeriesData);
    // In the getChartOptions function:
console.log("Colors in getChartOptions:", colorOptions.seriesColors, colorOptions.trendlineColors);
    return {
        series: finalSeriesData,
        chart: {
            // ... (other chart settings)
        },
        dataLabels: dataLabelsSettings,     
        yaxis: {
            labels: {
                formatter: function(val) {
                    return parseFloat(val).toFixed(0);
                }
            }
        },
        xaxis: {
            categories: dates
        },
        colors: colorOptions.seriesColors.concat(colorOptions.trendlineColors), // Combine series and trendline colors
        curve: {
            type: 'smooth' // Set curve type to smooth for original series
        }
    };
}

const trendlineOptions = {
    dashArray: 5,             // Makes the line dashed
    width: 2                  // Line width
};

function calculateTrendline(data) {
    const nonNullData = data.filter(value => value !== null && !isNaN(value));

    if (nonNullData.length === 0) {
        // Handle the case where there are no valid data points
        return { slope: 0, intercept: 0 };
    }

    let sumX = 0;
    let sumY = 0;
    let sumXY = 0;
    let sumXX = 0;

    for (let i = 0; i < nonNullData.length; i++) {
        const x = i + 1; // X values are 1-based
        const y = nonNullData[i];

        sumX += x;
        sumY += y;
        sumXY += x * y;
        sumXX += x * x;
    }

    const n = nonNullData.length;

    const slope = (n * sumXY - sumX * sumY) / (n * sumXX - sumX * sumX);
    const intercept = (sumY - slope * sumX) / n;

    console.log("Trendline calculations - slope:", slope, "intercept:", intercept);

    // Debugging print statements
    console.log("sumX:", sumX, "sumY:", sumY, "sumXY:", sumXY, "sumXX:", sumXX);
    console.log("slope:", slope, "intercept:", intercept);

    return function (x) {
        return slope * x + intercept;
    };
}


function getTrendlineData(seriesData) {
    const trendlineFunction = calculateTrendline(seriesData);
    return seriesData.map((y, x) => trendlineFunction(x)); // Adjusted this line as well
}

function getDefaultColors() {
    return [
        '#FF5733', '#33FF57', '#5733FF', 
        '#FF33A1', '#FFD133', '#33FFE0', 
        '#A233FF', '#FF8333', '#33A1FF', 
        '#D433FF'
    ];
}

// Helper function to generate a shade of a color
function getShade(color, percent) {
    let R = parseInt(color.substring(1, 3), 16);
    let G = parseInt(color.substring(3, 5), 16);
    let B = parseInt(color.substring(5, 7), 16);

    R = parseInt(R * (100 + percent) / 100);
    G = parseInt(G * (100 + percent) / 100);
    B = parseInt(B * (100 + percent) / 100);

    R = (R < 255) ? R : 255;
    G = (G < 255) ? G : 255;
    B = (B < 255) ? B : 255;

    let RR = ((R.toString(16).length === 1) ? "0" + R.toString(16) : R.toString(16));
    let GG = ((G.toString(16).length === 1) ? "0" + G.toString(16) : G.toString(16));
    let BB = ((B.toString(16).length === 1) ? "0" + B.toString(16) : B.toString(16));

    return "#" + RR + GG + BB;
}

// Generate colors for series and corresponding trendlines
function generateColors(finalSeriesData, trendlineSeriesData) {
    const defaultColors = getDefaultColors();
    const seriesColors = [];
    const trendlineColors = {};
    
    for (let i = 0; i < finalSeriesData.length; i++) {
        const color = defaultColors[i % defaultColors.length];
        seriesColors.push(color);
        
        // Generating a darker shade for the trendline
        trendlineColors[finalSeriesData[i].name] = getShade(color, -30);
    }

    console.log("Generated colors:", seriesColors, trendlineColors);
    
    return { seriesColors, trendlineColors };
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

        $(document).on('click', '.saveRow', async function() {
            const row = $(this).closest('tr'); // Define the row variable here
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
                const scoreValue = row.find(`td[data-field-name="score${i}"]`).text().trim();
                if (scoreValue === '') {
                    scores[`score${i}`] = null; // Send null if score is empty
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
                row.attr('data-performance-id', response.performance_id);
                row.find('td[data-field-name="score_date"]').text(convertToDisplayDate(response.score_date));
                // If you have default scores or other fields returned from the server, update them here too
        
                // Clear the input fields and enable the save button for future entries
                row.find('td.editable').text('');
                row.find('.saveRow').prop('disabled', false);
        
                // Optionally, display a success message
                // alert("Data saved successfully!");
            } else {
                // Handle the error response appropriately
                if (response && response.error) {
                    // alert("Error: " + response.error);
                } else {
                    // alert("There was an error saving the data.");
                }
            }
            location.reload();
        });
        
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