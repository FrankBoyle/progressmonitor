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

window.addEventListener('scroll', function(event) {
    window.clearTimeout(isScrolling);

    // Disable chart interactions during scroll
    disableChartInteractions();

    isScrolling = setTimeout(function() {
        // Enable chart interactions after scroll
        enableChartInteractions();
    }, 100);
}, false);

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
        const data = table.getData();
        const categories = data.map(row => row['score_date']);

        const selectedColumns = Array.from(document.querySelectorAll(".selector-item.selected"))
            .map(item => ({
                field: item.getAttribute("data-column-name"),
                name: item.textContent.trim()  // Use textContent of the item as the series name
            }));

        const series = selectedColumns.map(column => ({
            name: column.name,  // Using the custom name for the series
            data: data.map(row => row[column.field]),
            color: seriesColors[parseInt(column.field.replace('score', '')) - 1]  // Deduce color by score index
        }));

        const trendlineSeries = series.map(seriesData => ({
            name: `${seriesData.name} Trendline`,
            data: getTrendlineData(seriesData.data),
            type: 'line',
            dashArray: 5,
            stroke: { width: 2, curve: 'straight' },
            color: seriesData.color
        }));

        updateLineChart(categories, [...series, ...trendlineSeries]);
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

    chart.updateOptions({
        xaxis: { categories },
        yaxis: {
            labels: { formatter: val => val.toFixed(0) }  // Ensure whole numbers
        },
        series: seriesData,
        colors: seriesData.map(s => s.color),  // Apply specific colors to series
        stroke: {
            curve: 'smooth',
            width: seriesData.map(s => s.name.includes('Trendline') ? 2 : 5),
            dashArray: seriesData.map(s => s.name.includes('Trendline') ? 5 : 0)
        }
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
        colors: seriesData.map(s => s.color)  // Ensure colors are correctly applied
    });
}

// Function to get options for Line Chart
function getLineChartOptions(dates, seriesData) {
    return {
        chart: {
            type: 'line',
            height: '500',
            background: '#fff',
            toolbar: {
                show: true
            },
            dropShadow: {
                enabled: true,
                top: 1,
                left: 3,
                blur: 3,
                color: '#000',
                opacity: 0.1
            },
        },
        colors: seriesColors,
        dataLabels: {
            enabled: true,
            formatter: function(val, opts) {
                return val;  // Display actual value
            },
            style: {
                fontSize: '12px',
                fontWeight: 'bold'
            },
            background: {
                enabled: true,
                borderRadius: 2,
                borderWidth: 1,
                borderColor: '#000'
            }
        },
        stroke: {
            curve: 'smooth'
        },
        series: seriesData,
        grid: {
            borderColor: '#e7e7e7',
            row: {
                colors: ['#f3f3f3', 'transparent'],  // Alternating row background colors
                opacity: 0.5
            },
            column: {
                show: true,  // Enable vertical grid lines
                colors: ['#e7e7e7'],  // Light grey vertical lines
                width: 1
            }
        },
        markers: {
            size: 5
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
                formatter: function(val) {
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

// Function to get options for Bar Chart
function getBarChartOptions(dates, seriesData) {
    return {
        chart: {
            type: 'bar',
            height: '500',
            background: '#fff',
            toolbar: {
                show: true
            },
            stacked: true
        },
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: '80%'
            }
        },
        colors: seriesColors,
        dataLabels: {
            enabled: true,
            formatter: function (val) {
                return val;
            },
            style: {
                fontSize: '12px',
                fontWeight: 'bold'
            }
        },
        stroke: {
            show: true,
            width: 2,
            colors: ['transparent']
        },
        series: seriesData,
        grid: {
            borderColor: '#e7e7e7',
            row: {
                colors: ['#f3f3f3', 'transparent'],  // Alternating row colors
                opacity: 0.5
            },
            column: {
                show: true,  // Enable vertical grid lines
                colors: ['#e7e7e7'],  // Light grey vertical lines
                width: 1
            }
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


