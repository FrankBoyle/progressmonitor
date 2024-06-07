// Defining global variables for the script.
let barChart = null;
let benchmark = null;
let benchmarkSeriesIndex = null;
let selectedColumns = [];
let selectedChartType = 'bar';
let xCategories = [];
let chart = null;
let headerNames = [];
let allSeries = [];
let dates = [];
let finalSeriesData = [];
let trendlineSeriesData = [];
let scores = [];
let isBarChartInitialized = false;
let metadataId;  // Ensure this is declared at the global level

const seriesColors = [
    '#082645', '#FF8C00', '#388E3C', '#D32F2F', '#7B1FA2', '#1976D2', '#C2185B', '#0288D1', '#7C4DFF', '#C21807'
];

const barChartSeriesColors = seriesColors.slice();  // Use the same colors for bar chart

// Initialization
$(document).ready(function() {
    initializeChart();  // Initialize line chart
    initializeBarChart();  // Initialize bar chart
});

// Inside your accordion activation function
$("#accordion").accordion({
    collapsible: true,
    heightStyle: "content",
    active: false,
    activate: function(event, ui) {
        if (ui.newPanel.has('#chart').length) {
            selectedChartType = 'line';
            //console.log("Line Graph activated");

            // Update the selected columns based on the current state of the checkboxes
            selectedColumns = Array.from(document.querySelectorAll("#columnSelector input:checked"))
                .map(checkbox => checkbox.getAttribute("data-column-name") || '');

            if (!chart) {
                initializeChart();
            } else {
                // Update the line chart with the selected columns
                updateChart(selectedColumns); // Assuming updateChart is the function to update the line chart
            }
        } else if (ui.newPanel.has('#barChart').length) {
            selectedChartType = 'bar';
            //console.log("Bar Graph activated");

            if (barChart === null) {
                initializeBarChart(); // Initialize the bar chart
            } else {
                // Update the bar chart with the selected columns
                selectedColumns = Array.from(document.querySelectorAll("#columnSelector input:checked"))
                    .map(checkbox => checkbox.getAttribute("data-column-name") || '');
                updateBarChart(selectedColumns);
            }
        }
    }
});

// Extracts dates and scores data from the provided HTML table.
function extractDataFromTable() {
    const tableRows = document.querySelectorAll("table tbody tr");
    const data = [];

    tableRows.forEach((row) => {
        const excludeCheckbox = row.querySelector(".exclude-row-checkbox");
        if (excludeCheckbox && excludeCheckbox.checked) {
            return; // Skip rows where the checkbox is checked
        }

        const dateCell = row.querySelector("td:first-child");
        const date = dateCell ? dateCell.textContent.trim() : "";

        const scoreCells = row.querySelectorAll("td:not(:first-child):not(:last-child)");
        const rowScores = Array.from(scoreCells, cell => parseFloat(cell.textContent) || 0);  // Use parseFloat to retain decimal values

        data.push({ date, scores: rowScores });
    });

    data.sort((a, b) => new Date(a.date) - new Date(b.date));
    const dates = data.map(item => item.date);
    const scores = data.map(item => item.scores);

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
    //console.log("Populated series data:", seriesData);

    return seriesData;
  }

// Modify generateSeriesData to skip dates with missing values
function generateSeriesData(scores, headerNames, customNames = []) {
    const seriesList = [];
    for (let i = 1; i < headerNames.length; i++) { // Loop through headers, skipping the 'Date' column
        const scoreData = scores.map(row => {
            const score = row[i - 1]; // Adjust for zero-based index
            // Check if the value is numeric, otherwise return null for missing data
            return score !== '' && !isNaN(score) ? score : null;
        });
        seriesList.push({
            name: customNames[i - 1] || headerNames[i], // Use the header name if custom name is not provided
            data: scoreData,
            color: seriesColors[i - 1] || undefined, // Fallback to a default color if necessary
        });
    }
    //console.log("Generated series list:", seriesList);
    return seriesList;
}

// This function will now return the new series list
function getUpdatedSeriesNames(seriesList, customColumnNames) {
    return seriesList.map((series, index) => {
        const customColumnName = customColumnNames[index] || headerNames[index + 1];
        //console.log("Updated series list with custom column names:", seriesList);

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
        //console.log("All series after updating names:", allSeries);

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
function updateChart(selectedColumns) { // Update function signature
    // Clear existing series data
    chart.updateSeries([]);

    // Create a new series array based on selected columns
    const newSeriesData = allSeries.filter((series, index) => selectedColumns.includes(headerNames[index + 1]));

    // For each series in newSeriesData, calculate its trendline and add it to trendlineSeriesData
    const trendlineSeriesData = [];
    newSeriesData.forEach((series, index) => {
        const trendlineData = getTrendlineData(series.data);
        trendlineSeriesData.push({
            name: series.name + ' Trendline',
            data: trendlineData,
            type: 'line',
            width: '85%', // Set the width to 1000 pixels
            color: series.color,  // Ensure trendline has same color as series
            ...trendlineOptions,
        });
    });
    
    // Add trendline data to series
    const finalSeriesData = [...newSeriesData, ...trendlineSeriesData];
    //console.log("New series data based on selected columns:", newSeriesData);
    //console.log("Trendline series data:", trendlineSeriesData);
    //console.log("Final series data for updating the chart:", finalSeriesData);

    // Update the chart with the new series data and updated names
    chart.updateSeries(finalSeriesData);

    // Update series names in the legend
    chart.updateOptions({
        stroke: {
            width: finalSeriesData.map(series =>
                series.name.includes('Trendline') ? trendlineOptions.width : 5
            ),
            dashArray: finalSeriesData.map(series =>
                series.name.includes('Trendline') ? trendlineOptions.dashArray : 0
            ),
            curve: finalSeriesData.map(series =>
                series.name.includes('Trendline') ? 'straight' : 'smooth'
            ),
        },
    });
}

// Initializes the chart with default settings.
function initializeChart() {
    // Extract headers and data
    const headerRow = document.querySelector('#dataTable thead tr');
    headerNames = Array.from(headerRow.querySelectorAll('th')).map(th => th.innerText.trim());
    const { dates, scores } = extractDataFromTable();
    allSeries = generateSeriesData(scores, headerNames);

    // Get selected columns
    selectedColumns = Array.from(document.querySelectorAll("#columnSelector input:checked"))
        .map(checkbox => checkbox.getAttribute("data-column-name") || '');

    // Update series names
    allSeries = getUpdatedSeriesNames(allSeries, selectedColumns);

    // Initialize the chart
    chart = new ApexCharts(document.querySelector("#chart"), getChartOptions(dates));
    chart.render();    
    //console.log('Chart rendered:', $('#chart').data('apexcharts'));

    // Update the chart on checkbox changes
    document.getElementById("columnSelector").addEventListener("change", debounce(function() {
        selectedColumns = Array.from(document.querySelectorAll("#columnSelector input:checked"))
            .map(checkbox => checkbox.getAttribute("data-column-name") || '');

        updateChart(selectedColumns);
    }, 250));
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
        // If the value is null, return an empty string so no label is shown
        if (val === null) {
            return '';
        }

        // Hide data labels for 'Benchmark' and 'Trendline'.
        if (seriesName.includes('Trendline')) {
            return '';  // Return empty string to hide the label
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
    return {
        series: finalSeriesData.map(series => {
            return series;  // Return the series as-is for trendlines
        }),
        chart: {
            type: 'line',
            width: '85%', // Set the width to 1000 pixels
            dropShadow: {
                enabled: true,
                color: seriesColors,
                top: 7,  // Change to 0 to see if positioning is the issue
                left: 6,  // Change to 0 for same reason
                blur: 6,  // Increase blur for visibility
                opacity: 0.15  // Increase opacity for visibility
            },
        },
        colors: seriesColors,
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

        stroke: {
            width: finalSeriesData.map(series =>
                series.name.includes('Trendline') ? trendlineOptions.width : 6
            ),
            curve: 'smooth'
        },

        markers: {
            size: 4
        },
    };
}

const trendlineOptions = {
    dashArray: 5,             // Makes the line dashed
    width: 2                  // Line width
};

function calculateTrendline(data) {
    const validDataPoints = data.map((val, idx) => ({ x: idx + 1, y: val })).filter(point => point.y !== null && !isNaN(point.y));
    
    if (validDataPoints.length === 0) {
        // Return a constant function that always returns 0 if there are no valid data points
        return function(x) {
            return 0;
        };
    }

    const n = validDataPoints.length;
    const sumX = validDataPoints.reduce((acc, point) => acc + point.x, 0);
    const sumY = validDataPoints.reduce((acc, point) => acc + point.y, 0);
    const sumXY = validDataPoints.reduce((acc, point) => acc + point.x * point.y, 0);
    const sumXX = validDataPoints.reduce((acc, point) => acc + point.x * point.x, 0);
    
    const slope = (n * sumXY - sumX * sumY) / (n * sumXX - sumX * sumX);
    const intercept = (sumY - slope * sumX) / n;
    
    return function (x) {
        return slope * x + intercept;
    };
}

function getTrendlineData(data) {
    let trendlineFunction = calculateTrendline(data);
    return data.map((_, idx) => {
        // Call the trendline function with the sequential index starting at 1
        const x = idx + 1;
        const y = trendlineFunction(x);
        return y !== null && !isNaN(y) ? y : null;
    });
}

////////////////////////////////////////////////
function generateStackedBarChartData(scores, headerNames, customNames = []) {
    const seriesList = [];

    // Assuming scores is an array of arrays representing columns of data

    for (let i = 1; i < headerNames.length; i++) {
        const scoreData = scores.map(row => row[i]);
        const seriesData = scoreData.map(value => isNaN(value) ? 0 : value); // Replace NaN with 0
        seriesList.push({
            name: customNames[i - 1] || `Column${i}`, // Modify the naming convention if needed
            data: seriesData,
            // You can set other properties here as needed
        });
    }
    return seriesList;
}

// Modify the extractDataForBarChart function to extract data.
function extractDataForBarChart() {
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
    //console.log("Extracted dates:", dates);
    //console.log("Extracted scores:", scores);

    return { dates, scores };
}

// Populate the stacked bar chart series data.
function populateStackedBarChartSeriesData(selectedColumns, scores, headerNames) {
    const seriesData = [];
    selectedColumns.forEach(columnName => {
        const columnIndex = headerNames.indexOf(columnName);
        if (columnIndex !== -1) {
            const data = scores.map(row => row[columnIndex - 1] || 0);
            seriesData.push({ 
                name: columnName, // This sets the series name used in the legend
                data: data 
            });
        } else {
            console.error(`Column ${columnName} not found in header names`);
        }
    });

    return seriesData;
}

// Initialize the bar chart
function initializeBarChart() {
    // Ensure headerNames is populated correctly before calling getBarChartOptions
    const { dates, scores } = extractDataForBarChart();
    selectedColumns = Array.from(document.querySelectorAll("#columnSelector input:checked"))
        .map(checkbox => checkbox.getAttribute("data-column-name") || '');

    // Pass headerNames explicitly to getBarChartOptions
    const seriesData = populateStackedBarChartSeriesData(selectedColumns, scores, headerNames);

    // Pass headerNames to getBarChartOptions function
    barChart = new ApexCharts(document.querySelector("#barChart"), getBarChartOptions(dates, seriesData, headerNames));
    barChart.render();
    //console.log('Chart rendered:', $('#barChart').data('apexcharts'));

    // Add an event listener to update the bar chart when checkboxes change
    document.getElementById("columnSelector").addEventListener("change", debounce(function () {
        selectedColumns = Array.from(document.querySelectorAll("#columnSelector input:checked"))
            .map(checkbox => checkbox.getAttribute("data-column-name") || '');
        updateBarChart(selectedColumns);
    }, 250));
}

// Update the bar chart with new data based on selected columns
function updateBarChart(selectedColumns) {
    // Re-extract the data
    const { dates, scores } = extractDataForBarChart();
    headerNames = Array.from(document.querySelector('#dataTable thead tr').querySelectorAll('th'))
                         .map(th => th.innerText.trim());

    //console.log("Selected Columns (updateBarChart):", selectedColumns);
    //console.log("Header Names (updateBarChart):", headerNames);

    // Populate series data
    const newSeriesData = populateStackedBarChartSeriesData(selectedColumns, scores, headerNames);

    //console.log("New Series Data (updateBarChart):", newSeriesData);

    // Update bar chart
    barChart.updateOptions(getBarChartOptions(dates, newSeriesData, headerNames));
    
}

function getBarChartOptions(dates, seriesData, headerNames) {
    const totalValues = new Array(dates.length).fill(0);

    // Calculate running totals for each category
    seriesData.forEach((series) => {
        series.data.forEach((value, index) => {
            totalValues[index] += value;
        });
    });

    const annotations = totalValues.map((total, index) => ({
        x: dates[index], // Use the date instead of index
        y: total + 5, // You may need to adjust this for exact positioning
        orientation: 'horizontal',
        label: {
            text: `Total: ${total}`,
            borderColor: 'black',
            style: {
                background: '#f2f2f2',
                color: '#333',
                fontSize: '14px',
                fontWeight: 'bold',
                padding: {
                    left: 10,
                    right: 10,
                    top: 4,
                    bottom: 5,
                },
            },
        },
    }));    

    // Adjust the Y position of annotations based on bar heights
    annotations.forEach((annotation, index) => {
        const maxBarHeight = Math.max(...seriesData.map((series) => series.data[index]));
        annotation.y = totalValues[index] + maxBarHeight / 2; // Adjust as needed
    });

    return {
        chart: {
            type: 'bar',
            width: '85%',
            stacked: true,
        },
        xaxis: {
            categories: dates,
        },
        legend: {
            show: true,  // Ensure the legend is always shown
            showForSingleSeries: true, // Important for single series
        },
        series: seriesData.map((series, index) => ({
            ...series,
            color: barChartSeriesColors[index],
        })),
        colors: barChartSeriesColors,
        dataLabels: {
            enabled: true,
            formatter: function (val) {
                if (val === 0) {
                    return ''; // Hide labels for zero values
                }
                return val;
            },
            style: {
                fontSize: '16px',
            },
        },
        annotations: {
            xaxis: annotations,
            orientation: 'horizontal',
        },
    };
}

////////////////////////////////////////////////
////////////////////////////////////////////////
////////////////////////////////////////////////
////////////////////////////////////////////////
////////////////////////////////////////////////
////////////////////////////////////////////////
////////////////////////////////////////////////

function initializeDatepicker() {
    $(".datepicker").datepicker({
        dateFormat: 'mm/dd/yy',
        onSelect: function(dateText, inst) {
            const input = $(this);
            const cell = input.closest('td');
            const performanceId = cell.closest('tr').data('performance-id');
            const studentId = CURRENT_STUDENT_ID;
            console.log("metadataId is: " + metadataId); // This should output the metadata ID

            const dbDate = convertToDatabaseDate(dateText);
            if (isDateDuplicate(dbDate, performanceId, studentId, metadataId)) {
                alert("Duplicate date not allowed!");
                input.datepicker('setDate', cell.data('original-value')); // Reset to the original value
            } else {
                saveCellValue(cell, input);
                cell.data('original-value', dbDate); // Update the original value to the new date
            }
        }
    });
}

function attachEditableHandler() {
    console.log('attachEditableHandler called'); // Add this line for debugging

    $('table').off('dblclick', '.editable').on('dblclick', '.editable', function() {
        const cell = $(this);
        if (cell.hasClass('editing')) return;

        let originalValue = cell.text().trim(); // Get the original value to revert back if needed
        const input = $('<input type="text" class="cell-input">').val(originalValue);

        // Store the original value in case we need to revert back to it
        cell.data('original-value', originalValue);

        if (cell.data('field-name') === 'score_date') {
            input.addClass('datepicker');
            cell.addClass('editing').empty().append(input);
            input.datepicker({
                dateFormat: 'mm/dd/yy',
                onSelect: function(dateText, inst) {
                    // Perform the duplicate check when a date is selected
                    const performanceId = cell.closest('tr').data('performance-id');
                    const studentId = CURRENT_STUDENT_ID;
                    const dbDate = convertToDatabaseDate(dateText);
                    if (isDateDuplicate(dbDate, performanceId, studentId, metadataId)) {
                        alert("Duplicate date not allowed!");
                        input.datepicker('setDate', originalValue); // Reset to the original value
                    } else {
                        cell.data('original-value', dbDate); // Update original value with new date
                        saveCellValue(cell, input);
                    }
                }
            });
            input.focus();
        } else {
            input.on('blur', function() {
                saveCellValue(cell, input);
            }).on('keydown', function(e) {
                if (e.keyCode === 13) { // Enter key pressed
                    saveCellValue(cell, input);
                }
            });
            cell.addClass('editing').empty().append(input).find('input').focus();
        }
    });
}

function saveCellValue(cell, inputElement) {
    const newValue = inputElement.val().trim();
    const originalValue = cell.data('original-value') || cell.text().trim();
    const performanceId = cell.closest('tr').data('performance-id');
    const fieldName = cell.data('field-name');
    let dbDate; // Declare dbDate variable

    // If the field being edited is 'score_date', convert it to database format and check for duplicates.
    if (fieldName === 'score_date') {
        dbDate = convertToDatabaseDate(newValue); // Define dbDate here
        // Check for duplicates in the entire table, not just the current row being edited.
        if (isDateDuplicate(dbDate, performanceId, CURRENT_STUDENT_ID, metadataId)) {
            alert("Duplicate date not allowed!");
            inputElement.datepicker('setDate', originalValue); // Reset to the original value
            cell.removeClass('editing').html(originalValue); // Ensure the display is also reset
            return; // Exit the function to prevent the AJAX call
        }
    }

    let postData = {
        performance_id: performanceId,
        field_name: fieldName,
        new_value: fieldName === 'score_date' ? dbDate : newValue,
        student_id: CURRENT_STUDENT_ID,
        metadata_id: metadataId, // Ensure this line correctly retrieves the metadata_id
        school_id: $('#schoolIdInput').val()
    };

    // AJAX call to update the cell value on the server
    $.ajax({
        type: 'POST',
        url: './users/update_performance.php',
        data: postData,
        success: function(response) {
            if (fieldName === 'score_date' && response.saved_date) {
                cell.html(convertToDisplayDate(response.saved_date));
            } else {
                cell.html(newValue);
            }
            cell.removeClass('editing');
        },
        error: function(xhr, status, error) {
            console.error('Error status:', status);
            console.error('Error message:', error);
            console.error('Response text:', xhr.responseText);
            cell.html(originalValue);
            cell.removeClass('editing');
            alert('Error occurred while updating data.');
        }
    });
}


$('#addDataRow').off('click').click(function() {
    // Create a temporary input to attach datepicker
    const tempInput = $("<input type='text'>").appendTo('body');
    
    // Position the temporary input to the top right of the button
    const buttonPosition = $(this).offset();
    const buttonWidth = $(this).outerWidth();
    const inputWidth = 120; // Adjust as needed, this is the width of the datepicker input
    const inputHeight = 30; // Adjust as needed

    const tempInputLeft = buttonPosition.left + buttonWidth - inputWidth;
    const tempInputTop = buttonPosition.top - inputHeight;

    tempInput.css({
        position: 'absolute',
        left: tempInputLeft + 'px',
        top: tempInputTop + 'px',
        zIndex: 1000 // To ensure it's above other elements
    });

    tempInput.datepicker({
        dateFormat: 'mm/dd/yy',
        onSelect: function(dateText) {
            if (isDateDuplicate(dateText)) {
                alert("An entry for this date already exists. Please choose a different date.");
                return;
            }

            // Create the new row after date is selected
            const newRow = $("<tr data-performance-id='new'>");
            newRow.append(`<td class="editable" data-field-name="score_date">${dateText}</td>`);

            for (let i = 1; i <= 10; i++) {
                newRow.append(`<td class="editable" data-field-name="score${i}"></td>`);
            }

            newRow.append('<td><button class="saveRow">Save</button></td>');
            $("table").append(newRow);

            // Cleanup temporary input
            tempInput.remove();

            // Force a save immediately upon selecting a date
            saveRowData(newRow);
        }
    });

    // Show the datepicker immediately
    tempInput.datepicker('show');
});

function convertToDatabaseDate(dateString) {
    if (!dateString || dateString === "New Entry") return dateString;
    
    const components = dateString.split('/');
    if (components.length === 3) {
        const [month, day, year] = components;
        return `${year}-${month.padStart(2, '0')}-${day.padStart(2, '0')}`;
    }
    
    return dateString;
}

function getCurrentDate() {
    const currentDate = new Date();
    return `${(currentDate.getMonth() + 1).toString().padStart(2, '0')}/${currentDate.getDate().toString().padStart(2, '0')}/${currentDate.getFullYear()}`;
}

// Constants & Variables
const CURRENT_STUDENT_ID = $('#currentStudentId').val();

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
        console.log('Response:', response);

        return response;
    } catch (error) {
        console.error('Error during AJAX call:', error);

        // Debugging: Log the error response (if available)
        if (error.responseJSON) {
            console.error('Error response:', error.responseJSON);
            return error.responseJSON;  // Return the parsed JSON error message
        } else if (error.responseText) {
            console.error('Error responseText:', error.responseText);
            return { error: error.responseText };  // Provide the response text as error message
        } else {
            return { error: 'Unknown error occurred.' };  // Provide a generic error message
        }
    }   
}


function isDateDuplicate(dateString, currentPerformanceId = null, currentStudentId = null, currentMetadataId = null) {
    console.log("Checking for duplicate of:", dateString);
    let isDuplicate = false;

    // Validate the input date format (YYYY-MM-DD)
    const datePattern = /^\d{4}-\d{2}-\d{2}$/;
    if (!datePattern.test(dateString)) {
        console.log("Invalid date format:", dateString);
        return false;
    }

    // Convert the input dateString to a JavaScript Date object in Eastern Standard Time (EST)
    const inputDate = new Date(dateString + 'T00:00:00-05:00'); // Adjust timezone offset for EST

    $('table').find('td[data-field-name="score_date"]').each(function() {
        const cellDateText = $(this).text();
        
        // Check if cellDateText is a valid date string before creating cellDate
        if (Date.parse(cellDateText)) {
            const cellDate = new Date(cellDateText);
            const $currentRow = $(this).closest('tr');
            const performanceId = $currentRow.data('performance-id');
            const studentId = CURRENT_STUDENT_ID;
            const urlParams = new URLSearchParams(window.location.search);
            const metadata_id = urlParams.get('metadata_id');    

            console.log("Comparing:", cellDate, "to", inputDate);
            console.log("performanceId:", performanceId);
            console.log("studentId:", studentId);
            console.log("metadata_id:", metadata_id);
            
            // Check if dates, student_id, and metadata_id are the same, but not the same performance entry
            if (cellDate.getTime() === inputDate.getTime() 
                && performanceId !== currentPerformanceId 
                && studentId === currentStudentId 
                && metadata_id === currentMetadataId) {
                console.log("Duplicate found!");
                isDuplicate = true;
                return false; // Break out of the .each loop
            }
        } else {
            console.log("Invalid date:", cellDateText);
        }
    });

    console.log("isDuplicate:", isDuplicate);
    return isDuplicate;
}

// Function to update goal text
function updateGoalText(goalId, newText) {
    const postData = {
        goal_id: goalId,
        new_text: newText
    };

    $.ajax({
        type: 'POST',
        url: './users/update_goal.php', // Ensure this is the correct endpoint
        data: postData,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                //alert('Goal updated successfully.');
            } else {
                alert(response.message || 'Failed to update goal.');
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            alert('Error occurred while updating goal: ' + textStatus + ' - ' + errorThrown);
        }
    });
}

async function saveRowData(row) {
    const performanceId = row.data('performance-id');
    const school_id = $('#schoolIdInput').val();
    const metadata_id = new URLSearchParams(window.location.search).get('metadata_id');

    console.log('Saving row data:', row);
    console.log('Performance ID:', performanceId);
    console.log('School ID:', school_id);
    console.log('Metadata ID:', metadata_id);

    // Disable the save button to prevent multiple clicks
    row.find('.saveRow').prop('disabled', true);

    if (performanceId !== 'new') {
        return;
    }

    let scores = {};
    for (let i = 1; i <= 10; i++) {
        const scoreValue = row.find(`td[data-field-name="score${i}"]`).text().trim();
        scores[`score${i}`] = scoreValue === '' ? null : scoreValue;
    }

    const postData = {
        student_id: CURRENT_STUDENT_ID,
        score_date: convertToDatabaseDate(row.find('td[data-field-name="score_date"]').text()),
        scores: scores,
        metadata_id: metadata_id,
        school_id: school_id,
    };

    console.log('Post data:', postData);

    const response = await ajaxCall('POST', './users/insert_performance.php', postData);
    if (response && response.performance_id) {
        row.attr('data-performance-id', response.performance_id);
        row.find('td[data-field-name="score_date"]').text(convertToDisplayDate(response.score_date));
        row.find('.saveRow').prop('disabled', false);
    } else {
        if (response && response.error) {
            alert("Error: " + response.error);
        } else {
            alert("There was an error saving the data.");
        }
    }

    // Reload the page to show the new row with a delete button
    location.reload();
}


$(document).ready(function() {
    // Retrieve the metadata_id from the URL parameter
    metadataId = new URLSearchParams(window.location.search).get('metadata_id');
    //console.log('metadataId (global):', metadataId);

    // Set the retrieved metadata_id as the value of the input field
    $('#metadataIdInput').val(metadataId);

    initializeDatepicker();
    
    $(document).on('click', '.deleteRow', function() {
        const row = $(this);  // Capture the button element for later use
        const performanceId = $(this).data('performance-id');

        // Confirm before delete
        if (!confirm('Are you sure you want to delete this row?')) {
            return;
        }

        // Send a request to delete the data from the server
        $.post('./users/delete_performance.php', {
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

    $('#addNewGoalBtn').on('click', function() {
        const newGoalText = $('#newGoalText').val().trim();
        const studentId = CURRENT_STUDENT_ID; // Assuming you've already retrieved this
        const schoolId = $('#schoolIdInput').val(); // Make sure this input exists and holds the school_id
        const metadataId = new URLSearchParams(window.location.search).get('metadata_id'); // Retrieved from URL as in your example

        if (newGoalText === '') {
            alert('Please enter a goal description.');
            return;
        }

        const postData = {
            goal_description: newGoalText,
            student_id: studentId,
            metadata_id: metadataId,
            school_id: schoolId,
        };

        // Perform the AJAX request
        $.ajax({
            type: 'POST',
            url: './users/add_new_goal.php', // Adjust if necessary
            data: postData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Refresh the page after a short delay to allow the alert to be read by the user
                    setTimeout(function() {
                        window.location.reload();
                    }, 1000); // Adjust the delay as needed
                } else {
                    alert(response.message || 'Failed to add new goal.');
                }
            },
        });
    });

    // Event listener for goal checkbox change
    $(document).on('change', '.goal-checkbox', function() {
        // Uncheck and remove 'selected' class from all other goals
        $('.goal-checkbox').not(this).prop('checked', false).closest('.goal-container').removeClass('selected');
        
        // Toggle 'selected' class on the current goal container based on the checkbox state
        $(this).closest('.goal-container').toggleClass('selected', this.checked);
    });

    // Event handler for the save button
    $(document).on('click', '.save-goal-btn', function() {
        const goalId = $(this).data('goal-id'); // This should now correctly get the goal ID
        const newText = $(this).siblings('.goaltext').val(); // Retrieves the text of the corresponding textarea
        // Show an alert to the user
        alert('Goal Updated.');
        updateGoalText(goalId, newText);
    });

    $(document).on('keypress', '.saveRow', function(e) {
        if (e.which === 13) {
            e.preventDefault();
        }
    });

    // Initialization code
    attachEditableHandler();

    $.fn.dataTable.ext.type.detect.unshift(function(value) {
        return value && value.match(/^(\d{1,2}\/\d{1,2}\/\d{4})$/) ? 'date-us' : null;
    });

    $.fn.dataTable.ext.type.order['date-us-pre'] = function(data) {
        var date = data.split('/');
        return (date[2] + date[0] + date[1]) * 1;
    };

    // Define the DataTable and apply custom date filter
    let table = $('#dataTable').DataTable({
        "order": [[0, "asc"]],
        "lengthChange": false,
        "searching": false,
        "paging": false,
        "info": false,
        "sorting": false,
        "columns": [
            { "type": "date-us" },
            null, null, null, null, null, null, null, null, null, null, null
        ],
        "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"],
        "columnDefs": [
            {
                "targets": [0], // Apply the date filter to the first column (date)
                "type": "date-us",
                "render": function (data) {
                    return data ? $.datepicker.formatDate('mm/dd/yy', new Date(data)) : '';
                }
            }
        ]
    });
});
