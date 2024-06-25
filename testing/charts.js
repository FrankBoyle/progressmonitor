let table; // Declare `table` in a higher scope
let chart; // Reference to the line chart
let barChart; // Reference to the bar chart

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

    extractChartData();  // Initial data extraction and chart population
    // Initialize charts on page load
    initializeCharts();
});

// Initialize and update charts functions
function initializeCharts() {
    initializeLineChart();
    initializeBarChart();
}

function fetchInitialData(studentId, metadataId) {
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
                initializeTable(data.performanceData, data.scoreNames);
            } else {
                console.error('Invalid or incomplete data received:', data);
            }
        })
        .catch(error => console.error('Error fetching filtered data:', error));
}

function initializeTable(performanceData, scoreNames) {
    console.log('Initializing table with data and score names:', { performanceData, scoreNames });
    // Ensure previous instance is cleaned up before initializing a new one
    if (table) {
        table.destroy();
    }

    // Create columns dynamically based on scoreNames
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

    // Initialize Tabulator
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
        selectableRange: 1,
        selectableRangeColumns: false,
        selectableRangeRows: false,
        selectableRangeClearCells: false,
    });

    // Add cellEdited event listener
    table.on("cellEdited", function(cell) {
        console.log('Cell edited:', cell);
        const field = cell.getField();
        let value = cell.getValue();

        if (value === "") {
            value = null;
        }

        const updatedData = cell.getRow().getData();
        updatedData[field] = value;

        console.log("Updated data post-edit:", updatedData);

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
                    console.log('Data updated successfully:', result);
                } else {
                    alert('Failed to update data: ' + result.message);
                    console.error('Error info:', result.errorInfo);
                }
            })
            .catch(error => {
                console.error('Error updating data:', error);
            });
    });
}


// Constants for the colors and other settings
const seriesColors = ['#082645', '#FF8C00', '#388E3C', '#D32F2F', '#7B1FA2', '#1976D2', '#C2185B', '#0288D1', '#7C4DFF', '#C21807'];
const trendlineOptions = {
    dashArray: 5,
    width: 2,
    color: '#555' // Custom color for trendline
};

document.addEventListener('DOMContentLoaded', function() {
    //initializeCharts();  // Assuming you have a function to initialize charts
});

document.getElementById('filterData').addEventListener('click', function() {
    extractChartData();  // Refresh charts on filter change
});

// Initialize Line Chart with dummy data
function initializeLineChart() {
    const options = getLineChartOptions([], []); // Initialize with empty data
    chart = new ApexCharts(document.querySelector("#chartContainer"), options);
    chart.render();
}

// Update Line Chart
function updateLineChart(data) {
    const chartData = prepareChartData(data);
    chart.updateOptions(getLineChartOptions(chartData.dates, chartData.seriesData));
}

// Initialize Bar Chart with dummy data
function initializeBarChart() {
    const options = getBarChartOptions([], []); // Initialize with empty data
    barChart = new ApexCharts(document.querySelector("#barChartContainer"), options);
    barChart.render();
}

function extractChartData() {
    if (!table) {
        console.log("Table is not initialized.");
        return;
    }
    var data = table.getData();
    var categories = data.map(row => row['Score Date']);
    var series = prepareSeriesData(data);

    updateLineChart(categories, series);
    updateBarChart(categories, series);
}

function updateLineChart(categories, seriesData) {
    chart.updateOptions({
        xaxis: { categories: categories },
        series: seriesData
    });
}

function updateBarChart(categories, seriesData) {
    barChart.updateOptions({
        xaxis: { categories: categories },
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
