// Defining global variables for the script.
let barChart = null;  // Declare barChart variable at the global level
let benchmark = null;
let benchmarkSeriesIndex = null; // It's null initially because the series index is not determined yet.
let selectedColumns = [];
let selectedChartType = 'bar';  // Default chart type
let xCategories = [];
let chart = null;  // This makes the chart variable accessible throughout the script.
let headerNames = [];  // Will store header names extracted from the table.
let allSeries = [];  // Will store all data series.
let dates = [];  // To store extracted dates from table rows.
let finalSeriesData = [];
let trendlineSeriesData = []; // Declare both as global variables
let scores = [];  // Declare scores globally
// Define a flag to track whether the bar chart has been initialized
let isBarChartInitialized = false;


const seriesColors = [
    '#082645',  // dark blue
    '#FF8C00',  // dark orange
    '#388E3C',  // dark green
    '#D32F2F',  // dark red
    '#7B1FA2',  // dark purple
    '#1976D2',  // dark blue
    '#C2185B',  // pink
    '#0288D1',  // light blue
    '#7C4DFF',  // deep purple
    '#C21807'   // deep red
];

const barChartSeriesColors = [
    '#082645',  // dark blue
    '#FF8C00',  // dark orange
    '#388E3C',  // dark green
    '#D32F2F',  // dark red
    '#7B1FA2',  // dark purple
    '#1976D2',  // dark blue
    '#C2185B',  // pink
    '#0288D1',  // light blue
    '#7C4DFF',  // deep purple
    '#C21807'   // deep red
];

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
    let data = [];

    tableRows.forEach((row) => {
        const dateCell = row.querySelector("td:first-child");
        const date = dateCell ? dateCell.textContent.trim() : "";

        const scoreCells = row.querySelectorAll("td:not(:first-child):not(:last-child)");
        const rowScores = Array.from(scoreCells, cell => parseInt(cell.textContent || '0', 10));

        data.push({ date, scores: rowScores });
    });

    // Sort the data by date in ascending order
    data.sort((a, b) => new Date(a.date) - new Date(b.date));

    // Extract dates and scores into separate arrays
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
    console.log('Chart rendered:', $('#chart').data('apexcharts'));

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

    //console.log("Trendline calculations - slope:", slope, "intercept:", intercept);

    // Debugging print statements
    //console.log("sumX:", sumX, "sumY:", sumY, "sumXY:", sumXY, "sumXX:", sumXX);
    //console.log("slope:", slope, "intercept:", intercept);

    return function (x) {
        return slope * x + intercept;
    };
}

function getTrendlineData(seriesData) {
    const trendlineFunction = calculateTrendline(seriesData);
    return seriesData.map((y, x) => trendlineFunction(x)); // Adjusted this line as well
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
    console.log('Chart rendered:', $('#barChart').data('apexcharts'));

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

    console.log("Selected Columns (updateBarChart):", selectedColumns);
    console.log("Header Names (updateBarChart):", headerNames);

    // Populate series data
    const newSeriesData = populateStackedBarChartSeriesData(selectedColumns, scores, headerNames);

    console.log("New Series Data (updateBarChart):", newSeriesData);

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

$(document).ready(function() {
    // Retrieve the metadata_id from the URL parameter
    const urlParams = new URLSearchParams(window.location.search);
    const metadata_id = urlParams.get('metadata_id');
    
    
    // Set the retrieved metadata_id as the value of the input field
    $('#metadataIdInput').val(metadata_id);

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
            // Event handler for adding a new data row
            $('#addDataRow').off('click').click(function() {
                // Implementation goes here
                const newRowData = {
                    // Populate the new row data here
                };

                // Send a request to the server to add the new row
                $.post('add_row.php', newRowData, function(response) {
                    if (response.success) {
                        // Create a new row element with the returned data
                        const newRow = $('<tr>').data('performance-id', response.performanceId);
                        // Populate the new row with the data from the response
                        // ...

                        // Append the new row to the table
                        $('#dataTable').append(newRow);

                        // Attach the editable handler to the new row
                        attachEditableHandler();
                    } else {
                        alert('Failed to add new row. Please try again.');
                    }
                }, 'json');
            });