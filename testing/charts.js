
let table; // Declare `table` in a higher scope

document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const studentId = urlParams.get('student_id');
    const metadataId = urlParams.get('metadata_id');

    function initializeTable(performanceData, scoreNames) {
    // Check if table already exists and destroy it if it does
    if (table) {
        table.destroy();
    }

    // Check if scoreNames is valid
    if (!scoreNames || typeof scoreNames !== 'object') {
        console.error('scoreNames is invalid or not an object:', scoreNames);
        return; // Prevent further execution if scoreNames is invalid
    }

    const columns = [
        {
            title: "Score Date",
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
            fetch('./users/update_performance2.php', {
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

    function fetchFilteredData(iepDate) {
        fetch(`./users/fetch_filtered_data.php?student_id=${studentId}&metadata_id=${metadataId}&iep_date=${iepDate}`)
            .then(response => response.json())
            .then(data => {
                console.log('Filtered data fetched:', data);
                if (data && data.performanceData && data.scoreNames) {
                    initializeTable(data.performanceData, data.scoreNames);
                } else {
                    console.error('Invalid or incomplete data received:', data);
                }
            })
            .catch(error => console.error('Error fetching filtered data:', error));
    }

    document.getElementById('filterData').addEventListener('click', function() {
        const iepDate = document.getElementById('iep_date').value;
        if (iepDate) {
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
                    fetchFilteredData(iepDate);
                } else {
                    alert(data.message);
                }
            })
            .catch(error => console.error('Error saving IEP date:', error));
        }
    });

    fetch(`./users/fetch_data2.php?student_id=${studentId}&metadata_id=${metadataId}`)
        .then(response => response.json())
        .then(data => {
            console.log('Initial data fetched:', data);
            if (data && data.performanceData && data.scoreNames) {
                initializeTable(data.performanceData, data.scoreNames);
                if (data.iepDate) {
                    document.getElementById('iep_date').value = data.iepDate;
                }
            } else {
                console.error('Invalid or incomplete initial data:', data);
            }
        })
        .catch(error => console.error('Error fetching initial data:', error));
});

// Constants for the colors and other settings
const seriesColors = ['#082645', '#FF8C00', '#388E3C', '#D32F2F', '#7B1FA2', '#1976D2', '#C2185B', '#0288D1', '#7C4DFF', '#C21807'];
const trendlineOptions = {
    dashArray: 5,
    width: 2,
    color: '#555' // Custom color for trendline
};

document.addEventListener('DOMContentLoaded', function() {
    initializeCharts();  // Assuming you have a function to initialize charts
    extractChartData();  // Initial data extraction and chart population
});

document.getElementById('filterData').addEventListener('click', function() {
    extractChartData();  // Refresh charts on filter change
});


// Initialize Charts
function initializeCharts() {
    initializeLineChart();
    initializeBarChart();
}

// Initialize Line Chart with dummy data
function initializeLineChart() {
    const chartOptions = getLineChartOptions([], []); // Empty data initially
    chart = new ApexCharts(document.querySelector("#chartContainer"), chartOptions);
    chart.render();
}

// Update Line Chart
function updateLineChart(data) {
    const chartData = prepareChartData(data);
    chart.updateOptions(getLineChartOptions(chartData.dates, chartData.seriesData));
}

// Initialize Bar Chart with dummy data
function initializeBarChart() {
    const barChartOptions = getBarChartOptions([], []); // Empty data initially
    barChart = new ApexCharts(document.querySelector("#barChartContainer"), barChartOptions);
    barChart.render();
}

// Update Bar Chart
function updateBarChart(data) {
    const chartData = prepareBarChartData(data);
    barChart.updateOptions(getBarChartOptions(chartData.dates, chartData.seriesData));
}

function extractChartData() {
    var data = table.getData(); // Assuming 'table' is your Tabulator table variable
    var categories = data.map(row => row['Score Date']); // Extract 'Score Date' as categories

    // Dynamically determine the columns (excluding 'Score Date')
    var columnHeaders = table.getColumns().map(column => column.getField()).filter(field => field !== 'Score Date');

    // Prepare series data for each column
    var series = columnHeaders.map(column => {
        return {
            name: column,
            data: data.map(row => row[column])
        };
    });

    // Update the charts
    updateLineChart(categories, series);
    updateBarChart(categories, series);
}

function updateLineChart(categories, seriesData) {
    chart.updateOptions({
        xaxis: {
            categories: categories
        },
        series: seriesData
    });
}

function updateBarChart(categories, seriesData) {
    barChart.updateOptions({
        xaxis: {
            categories: categories
        },
        series: seriesData
    });
}


// Generate options for line chart
function getLineChartOptions(dates, seriesData) {
    return {
        chart: {
            type: 'line',
            height: 350,
            dropShadow: {
                enabled: true,
                color: '#000',
                top: 18,
                left: 7,
                blur: 10,
                opacity: 0.2
            },
            toolbar: {
                show: false
            }
        },
        colors: seriesColors,
        dataLabels: {
            enabled: true
        },
        stroke: {
            curve: 'smooth'
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
            size: 1
        },
        xaxis: {
            categories: dates,
            title: {
                text: 'Date'
            }
        },
        yaxis: {
            title: {
                text: 'Value'
            },
            min: 0,
            max: (Math.max(...seriesData.map(s => Math.max(...s.data))) + 10)
        },
        legend: {
            position: 'top',
            horizontalAlign: 'right',
            floating: true,
            offsetY: -25,
            offsetX: -5
        }
    };
}

// Generate options for bar chart
function getBarChartOptions(dates, seriesData) {
    return {
        chart: {
            type: 'bar',
            height: 350
        },
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: '55%',
                endingShape: 'rounded'
            },
        },
        dataLabels: {
            enabled: false
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
                text: 'Date'
            }
        },
        yaxis: {
            title: {
                text: 'Value'
            }
        },
        fill: {
            opacity: 1
        },
        tooltip: {
            y: {
                formatter: function (val) {
                    return val + " units"
                }
            }
        }
    };
}

// Calculate and format trendline data for a given series
function calculateTrendline(seriesData) {
    const n = seriesData.length;
    const xSum = seriesData.reduce((acc, curr, idx) => acc + idx, 0);
    const ySum = seriesData.reduce((acc, curr) => acc + curr, 0);
    const xySum = seriesData.reduce((acc, curr, idx) => acc + curr * idx, 0);
    const xxSum = seriesData.reduce((acc, curr, idx) => acc + idx * idx, 0);
    const slope = (n * xySum - xSum * ySum) / (n * xxSum - xSum * xSum);
    const intercept = (ySum - slope * xSum) / n;

    return seriesData.map((_, idx) => slope * idx + intercept);
}

// Data preparation for line and bar charts
function prepareChartData(rawData) {
    const dates = rawData.map(data => data.date);
    const seriesData = rawData.map(data => ({ name: data.name, data: data.values }));
    return { dates, seriesData };
}
