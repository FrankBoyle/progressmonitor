let table; // Global reference to the Tabulator table
let chart; // Reference to the line chart
let barChart; // Reference to the bar chart
let isScrolling;

// Define series colors
const seriesColors = [
    '#082645', '#FF8C00', '#388E3C', '#D32F2F', '#7B1FA2', '#1976D2', '#C2185B', '#0288D1', '#7C4DFF', '#C21807'
];

document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const studentId = urlParams.get('student_id');
    const metadataId = urlParams.get('metadata_id');

    // Fetch initial data and setup the table
    fetchInitialData(studentId, metadataId);

    // Setup event listener for the filter button
    document.getElementById('filterData').addEventListener('click', function() {
        const iepDate = document.getElementById('iep_date').value;
        console.log('Filter data button clicked, IEP Date:', iepDate);
        if (iepDate) {
            saveIEPDate(iepDate, studentId);
        }
    });

    // Initialize charts on page load
    initializeCharts();
});

function fetchInitialData(studentId, metadataId) {
    fetch(`./users/fetch_data.php?student_id=${studentId}&metadata_id=${metadataId}`)
        .then(response => response.json())
        .then(data => {
            console.log('Initial data fetched:', data);
            if (data && data.performanceData && data.scoreNames) {
                createColumnCheckboxes(data.scoreNames);
                initializeTable(data.performanceData, data.scoreNames);
                if (data.iepDate) {
                    document.getElementById('iep_date').value = data.iepDate;
                }
            } else {
                console.error('Invalid or incomplete initial data:', data);
            }
        })
        .catch(error => {
            console.error('Error fetching initial data:', error);
        });
}

function saveIEPDate(iepDate, studentId) {
    console.log(`Saving IEP Date: ${iepDate} for Student ID: ${studentId}`);
    fetch('./users/save_iep_date.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            iep_date: iepDate,
            student_id: studentId
        })
    })
    .then(response => response.json())
    .then(data => {
        console.log('IEP date saved:', data);
        if (data.success) {
            fetchFilteredData(iepDate, studentId, metadataId);
        } else {
            alert(data.message);
        }
    })
    .catch(error => console.error('Error saving IEP date:', error));
}

function fetchFilteredData(iepDate, studentId, metadataId) {
    console.log(`Fetching filtered data for IEP Date: ${iepDate}, Student ID: ${studentId}, Metadata ID: ${metadataId}`);
    fetch(`./users/fetch_filtered_data.php?student_id=${studentId}&metadata_id=${metadataId}&iep_date=${iepDate}`)
        .then(response => response.json())
        .then(data => {
            console.log('Filtered data fetched:', data);
            if (data && data.performanceData && data.scoreNames) {
                createColumnCheckboxes(data.scoreNames);
                initializeTable(data.performanceData, data.scoreNames);
                extractChartData(); // Update charts based on the new data
            } else {
                console.error('Invalid or incomplete data received:', data);
            }
        })
        .catch(error => console.error('Error fetching filtered data:', error));
}

function initializeCharts() {
    initializeLineChart();
    initializeBarChart();
}

function initializeLineChart() {
    const chartOptions = getLineChartOptions([], []); // Empty data initially
    chart = new ApexCharts(document.querySelector("#chartContainer"), chartOptions);
    chart.render();
}

function initializeBarChart() {
    const barChartOptions = getBarChartOptions([], []); // Empty data initially
    barChart = new ApexCharts(document.querySelector("#barChartContainer"), barChartOptions);
    barChart.render();
}

// Extract chart data based on selected columns
function extractChartData() {
    try {
        console.log("Extracting chart data...");

        // Assuming 'table' is your Tabulator table variable
        const data = table.getData();
        console.log("Table data:", data);

        // Extract 'score_date' as categories
        const categories = data.map(row => row['score_date']);
        console.log("Categories (Dates):", categories);

        // Get selected columns
        const selectedColumns = Array.from(document.querySelectorAll(".selector-item.selected"))
            .map(item => item.getAttribute("data-column-name"));

        console.log("Selected columns:", selectedColumns);

        // Prepare series data for each selected column
        const series = selectedColumns.map((column, index) => ({
            name: column,
            data: data.map(row => row[column]),
            color: seriesColors[index % seriesColors.length] // Assign color from seriesColors
        }));

        // Calculate trendline data for each series
        const trendlineSeries = series.map(seriesData => ({
            name: seriesData.name + ' Trendline',
            data: getTrendlineData(seriesData.data),
            type: 'line',
            dashArray: 5,
            color: seriesData.color,  // Use the same color as the original series
            stroke: {
                width: 2,
                curve: 'straight'
            },
        }));

        // Combine original series with trendline series
        const finalSeries = [...series, ...trendlineSeries];

        console.log("Final series data:", finalSeries);

        // Update the charts
        updateLineChart(categories, finalSeries);
        updateBarChart(categories, series); // Bar chart does not include trendlines

        console.log("Charts updated successfully.");
    } catch (error) {
        console.error("Error extracting chart data:", error);
    }
}

// Update Line Chart
function updateLineChart(categories, seriesData) {
    if (!chart) {
        console.error('Line chart is not initialized');
        return;
    }

    if (seriesData.length === 0) {
        seriesData.push({ name: "No Data", data: [] });
    }

    const maxDataValue = Math.max(...seriesData.flatMap(s => s.data));

    chart.updateOptions({
        xaxis: {
            categories: categories
        },
        yaxis: {
            max: Math.ceil(maxDataValue + 10), // Add some padding to the max value and round up
            labels: {
                formatter: function(val) {
                    return val.toFixed(0); // Ensure y-axis labels are whole numbers
                }
            }
        },
        series: seriesData,
        colors: seriesColors,
        stroke: {
            curve: 'smooth',
            width: seriesData.map(series =>
                series.name.includes('Trendline') ? 2 : 5
            ),
            dashArray: seriesData.map(series =>
                series.name.includes('Trendline') ? 5 : 0
            ),
        },
    });
}

// Data preparation for line and bar charts
function prepareChartData(rawData) {
    const dates = rawData.map(data => data.date);
    const seriesData = rawData.map(data => ({ name: data.name, data: data.values }));
    return { dates, seriesData };
}

// Update Bar Chart
function updateBarChart(categories, seriesData) {
    if (!barChart) {
        console.error('Bar chart is not initialized');
        return;
    }

    if (seriesData.length === 0) {
        seriesData.push({ name: "No Data", data: [] });
    }

    // Calculate the maximum stack height for each category
    const maxStackHeight = categories.map((_, i) => {
        return seriesData.reduce((acc, series) => acc + (series.data[i] || 0), 0);
    });
    const maxDataValue = Math.max(...maxStackHeight);

    barChart.updateOptions({
        xaxis: {
            categories: categories
        },
        yaxis: {
            min: 0, // Ensure the minimum value is 0
            max: maxDataValue + 10 // Add some padding to the max value
        },
        series: seriesData,
        colors: seriesColors
    });
}

// Function to get options for Line Chart
function getLineChartOptions(dates, seriesData) {
    return {
        chart: {
            type: 'line',
            height: '100%',
            background: '#fff',
            toolbar: {
                show: true
            },
            dropShadow: {
                enabled: true,
                top: 1,
                left: 3,
                blur: 3,
                color: seriesColors,
                opacity: 0.1
            },
        },
        colors: seriesColors,
        dataLabels: {
            enabled: true,
            formatter: function(val, opts) {
                var seriesName = opts.w.config.series[opts.seriesIndex].name;
                if (val === null) {
                    return '';
                }
                if (seriesName.includes('Trendline')) {
                    return '';
                }
                return val; // Return the value as it is, respecting the table's decimal places
            },
            style: {
                fontSize: '12px',
                fontWeight: 'bold'
            },
            background: {
                enabled: true,
                borderRadius: 2,
                borderWidth: 1,
                borderColor: '#000',
                dropShadow: {
                    enabled: false
                }
            }
        },
        stroke: {
            curve: 'smooth',
            width: seriesData.map(series =>
                series.name.includes('Trendline') ? 2 : 5
            ),
            dashArray: seriesData.map(series =>
                series.name.includes('Trendline') ? 5 : 0
            ),
            colors: seriesColors
        },
        series: seriesData,
        grid: {
            borderColor: '#e7e7e7',
            row: {
                colors: ['#f3f3f3', 'transparent'],
                opacity: 0.5
            },
        },
        markers: {
            size: 0
        },
        xaxis: {
            categories: dates,
            title: {
                text: 'Date',
                offsetY: -20
            }
        },
        yaxis: {
            title: {
                text: 'Value'
            },
            labels: {
                formatter: function(val) {
                    return val.toFixed(0); // Ensure y-axis labels are whole numbers
                }
            }
        },
        legend: {
            position: 'bottom',
            horizontalAlign: 'center',
            showForSingleSeries: true
        }
    };
}

// Function to get options for Bar Chart
function getBarChartOptions(dates, seriesData) {
    return {
        chart: {
            type: 'bar',
            height: '100%',
            background: '#fff',
            toolbar: {
                show: true
            },
            stacked: true
        },
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: '80%', // Increase the bar width
            },
        },
        colors: seriesColors,
        dataLabels: {
            enabled: true,
            enabledOnSeries: undefined, // Show dataLabels on all series
            formatter: function (val, opts) {
                return val; // Keep the label text the same as the data value
            },
            textAnchor: 'middle',
            distributed: false, // Do not distribute labels individually
            offsetX: 0,
            offsetY: 0,
            style: {
                fontSize: '12px',
                fontFamily: 'Helvetica, Arial, sans-serif',
                fontWeight: 'bold',
                colors: undefined // Colors will be overridden by background.foreColor
            },
            background: {
                enabled: true,
                foreColor: '#fff', // Text color
                padding: 1,
                borderRadius: 0,
                borderWidth: 0, // Thin border
                borderColor: '#000', // Black outline
                opacity: 0.9,
                dropShadow: {
                    enabled: false // Disable background shadow
                }
            },
            dropShadow: {
                enabled: false, // Disable text shadow
                top: 1,
                left: 1,
                blur: 1,
                color: '#000',
                opacity: 0.45
            }
        },
        stroke: {
            show: true,
            width: 2,
            colors: ['transparent']
        },
        series: seriesData,
        xaxis: {
            categories: dates,
            title: {
                text: 'Date',
                offsetY: -10 // Move the axis title closer to the dates
            }
        },
        yaxis: {
            title: {
                text: 'Value'
            },
            labels: {
                formatter: function (val) {
                    return val.toFixed(0);
                }
            }
        },
        fill: {
            opacity: 1
        },
        tooltip: {
            y: {
                formatter: function (val) {
                    return val + " units";
                }
            }
        },
        legend: {
            position: 'bottom',
            horizontalAlign: 'center',
            showForSingleSeries: true // Always show the legend, even for a single series
        }
    };
}

function createColumnCheckboxes(scoreNames) {
    const columnSelector = document.getElementById('columnSelector');
    columnSelector.innerHTML = ''; // Clear any existing checkboxes
    Object.keys(scoreNames).forEach((key, index) => {
        const item = document.createElement('div');
        item.classList.add('selector-item');
        item.setAttribute("data-column-name", `score${index + 1}`);
        item.textContent = scoreNames[key];
        item.addEventListener('click', function() {
            item.classList.toggle('selected');
            extractChartData();
        });
        columnSelector.appendChild(item);
    });
}

function initializeTable(performanceData, scoreNames) {
    if (table) {
        table.destroy();
    }

    const columns = [
        {
            title: "Date",
            field: "score_date",
            editor: "input",
            formatter: function(cell, formatterParams, onRendered) {
                const DateTime = luxon.DateTime;
                let date = DateTime.fromISO(cell.getValue());
                return date.isValid ? date.toFormat("MM/dd/yyyy") : "(invalid date)";
            },
            editorParams: {
                mask: "MM/DD/YYYY",
                format: "MM/DD/YYYY",
            },
            width: 120,
            frozen: false,
        },
    ];

    Object.keys(scoreNames).forEach((key, index) => {
        columns.push({
            title: scoreNames[key],
            field: `score${index + 1}`,
            editor: "input",
            width: 100
        });
    });

    table = new Tabulator("#performance-table", {
        height: "500px",
        data: performanceData,
        columns: columns,
        layout: "fitDataStretch",
        tooltips: true,
        movableColumns: false,
        resizableRows: false,
        editTriggerEvent: "dblclick",
        editorEmptyValue: null,
        clipboard: true,
        clipboardCopyRowRange: "range",
        clipboardPasteParser: "range",
        clipboardPasteAction: "range",
        clipboardCopyConfig: {
            rowHeaders: false,
            columnHeaders: true,
        },
        clipboardCopyStyled: false,
        selectableRange: 1, // allow only one range at a time
        selectableRangeColumns: false,
        selectableRangeRows: false,
        selectableRangeClearCells: false,
    });

    // Add cellEdited event listener inside initializeTable after declaring table
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
        fetch('./users/update_performance.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(updatedData)
        }).then(response => response.json())
          .then(result => {
              if (result.success) {
                  // Data updated successfully
              } else {
                  alert('Failed to update data: ' + result.message);
                  console.error('Error info:', result.errorInfo); // Log detailed error info
              }
          })
          .catch(error => console.error('Error:', error));
    });
}

window.addEventListener('scroll', function(event) {
    window.clearTimeout(isScrolling);

    // Disable chart interactions during scroll
    disableChartInteractions();

    isScrolling = setTimeout(function() {
        // Enable chart interactions after scroll
        enableChartInteractions();
    }, 100);
}, false);

function disableChartInteractions() {
    if (chart) {
        chart.updateOptions({
            chart: {
                animations: {
                    enabled: false
                }
            }
        });
    }
    if (barChart) {
        barChart.updateOptions({
            chart: {
                animations: {
                    enabled: false
                }
            }
        });
    }
}

function enableChartInteractions() {
    if (chart) {
        chart.updateOptions({
            chart: {
                animations: {
                    enabled: true
                }
            }
        });
    }
    if (barChart) {
        barChart.updateOptions({
            chart: {
                animations: {
                    enabled: true
                }
            }
        });
    }
}

function calculateTrendline(data) {
    const validDataPoints = data.map((val, idx) => ({ x: idx + 1, y: val })).filter(point => point.y !== null && !isNaN(point.y));

    if (validDataPoints.length === 0) {
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
        return parseFloat((slope * x + intercept).toFixed(2)); // Round to 2 decimal places
    };
}

function getTrendlineData(data) {
    let trendlineFunction = calculateTrendline(data);
    return data.map((_, idx) => {
        const x = idx + 1;
        const y = trendlineFunction(x);
        return y !== null && !isNaN(y) ? y : null;
    });
}

