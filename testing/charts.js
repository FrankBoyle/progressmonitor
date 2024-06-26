let table; // Global reference to the Tabulator table
let chart; // Reference to the line chart
let barChart; // Reference to the bar chart

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

// Extract chart data based on selected checkboxes
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
        const selectedColumns = Array.from(document.querySelectorAll("#columnSelector input:checked"))
            .map(checkbox => checkbox.getAttribute("data-column-name") || '');

        console.log("Selected columns:", selectedColumns);

        // Prepare series data for each selected column
        const series = selectedColumns.map(column => ({
            name: column,
            data: data.map(row => row[column])
        }));

        console.log("Series data:", series);

        // Update the charts
        updateLineChart(categories, series);
        updateBarChart(categories, series);

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
            max: maxDataValue + 10 // Add some padding to the max value
        },
        series: seriesData,
        colors: seriesColors
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
            height: 350,
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
            labels: {
                formatter: function (val) {
                    return val.toFixed(0);
                }
            }
        },
        legend: {
            position: 'bottom',
            horizontalAlign: 'center'
        }
    };
}

function getBarChartOptions(dates, seriesData) {
    return {
        chart: {
            type: 'bar',
            height: 350,
            stacked: true
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
        }
    };
}

function createColumnCheckboxes(scoreNames) {
    const columnSelector = document.getElementById('columnSelector');
    columnSelector.innerHTML = ''; // Clear any existing checkboxes
    Object.keys(scoreNames).forEach((key, index) => {
        const label = document.createElement('label');
        label.innerHTML = `
            <input type="checkbox" data-column-name="score${index + 1}">
            ${scoreNames[key]}
        `;
        columnSelector.appendChild(label);
    });

    // Add event listener to update charts when checkboxes change
    columnSelector.addEventListener('change', function() {
        extractChartData();
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