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
    const studentIdNew = urlParams.get('student_id');
    const metadataId = urlParams.get('metadata_id');

    if (!studentIdNew || !metadataId) {
        console.error('Student ID or Metadata ID is missing in the URL parameters.');
        alert('Student ID or Metadata ID is missing. Please check the URL parameters.');
        return;
    }

    // Fetch initial data and setup the table
    fetchInitialData(studentIdNew, metadataId);

    // Setup event listener for the filter button
    document.getElementById('filterData').addEventListener('click', function() {
        const iepDate = document.getElementById('iep_date').value;
        console.log('Filter data button clicked, IEP Date:', iepDate);
        if (iepDate) {
            saveIEPDate(iepDate, studentIdNew);
        }
    });

    // Initialize charts on page load
    initializeCharts();

    // Event listener for adding a new data row
    document.getElementById("addDataRow").addEventListener("click", function() {
        const newRowDateInput = document.getElementById("newRowDate");
        newRowDateInput.style.display = "block";
        newRowDateInput.focus();

        newRowDateInput.addEventListener("change", function() {
            const newDate = newRowDateInput.value;

            if (newDate === "") {
                alert("Please select a date.");
                return;
            }

            if (isDateDuplicate(newDate)) {
                alert("An entry for this date already exists. Please choose a different date.");
                return;
            }

            const newData = {
                student_id_new: studentIdNew,
                school_id: schoolId, // Use the schoolId from the session variable
                metadata_id: metadataId,
                score_date: newDate,
                scores: {}
            };

            for (let i = 1; i <= 10; i++) {
                newData.scores[`score${i}`] = null;
            }

            console.log('Sending new data:', newData); // Add debugging statement

            fetch('./users/insert_performance.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(newData)
            }).then(response => {
                console.log('Fetch response:', response);
                return response.text(); // Get response as text
            }).then(text => {
                console.log('Response text:', text);
                let result;
                try {
                    result = JSON.parse(text); // Parse text as JSON
                } catch (error) {
                    throw new Error('Response is not valid JSON');
                }
                console.log('Parsed result:', result);
                if (result.success) {
                    newData.performance_id = result.performance_id;
                    table.addRow(newData);
                    newRowDateInput.value = "";
                    newRowDateInput.style.display = "none";
                } else {
                    alert('Failed to add new data: ' + result.error);
                    console.error('Error info:', result.missing_data);
                }
            }).catch(error => {
                console.error('Error:', error);
                alert('An error occurred while adding new data.');
            });
        }, { once: true });
    });
});

document.addEventListener('DOMContentLoaded', function() {
    const editBtn = document.getElementById('editColumnsBtn');
    if (editBtn) {
        editBtn.addEventListener('click', showEditColumnNamesModal);
        console.log("Button listener attached.");
    } else {
        console.log("Edit button not found.");
    }
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

function fetchInitialData(studentIdNew, metadataId) {
    fetch(`./users/fetch_data.php?student_id=${studentIdNew}&metadata_id=${metadataId}`)
        .then(response => response.json())
        .then(data => {
            console.log('Initial data fetched:', data);
            if (data && data.performanceData && data.scoreNames) {
                createColumnCheckboxes(data.scoreNames);
                initializeTable(data.performanceData, data.scoreNames, studentIdNew, metadataId);
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

// Function to initialize the table
function initializeTable(performanceData, scoreNames, studentIdNew, metadataId) {
    if (table) {
        table.destroy();
    }

    const columns = [
        // Add Actions column with delete button
        {
            title: "Actions",
            field: "actions",
            formatter: function(cell, formatterParams, onRendered) {
                return '<button class="delete-row-btn" data-performance-id="' + cell.getRow().getData().performance_id + '">Delete</button>';
            },
            width: 100,
            hozAlign: "center", // Correct option for horizontal alignment
            cellClick: function(e, cell) {
                const performanceId = cell.getRow().getData().performance_id;

                // Confirm before delete
                if (confirm('Are you sure you want to delete this row?')) {
                    // Send a request to delete the data from the server
                    fetch('./users/delete_performance.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ performance_id: performanceId })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Remove the row from the table
                            cell.getRow().delete();
                        } else {
                            alert('Failed to delete data. Please try again.');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while deleting the data.');
                    });
                }
            }
        },
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
        }
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
        height: "500px", // Limit table height to 500px
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
        virtualDomBuffer: 300, // Increase virtual DOM buffer size
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
        updatedData.student_id_new = studentIdNew;  // Ensure student_id_new is included
        updatedData.metadata_id = metadataId;  // Ensure metadata_id is included

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
                  console.log('Data updated successfully');
              } else {
                  alert('Failed to update data: ' + result.message);
                  console.error('Error info:', result.errorInfo); // Log detailed error info
              }
          })
          .catch(error => console.error('Error:', error));
    });

    // Ensure the table is fully initialized and rendered before allowing selection
    table.on("tableBuilt", function() {
        console.log("Table fully built and ready for interaction.");
    });
}

function isDateDuplicate(date) {
    const data = table.getData();
    return data.some(row => row['score_date'] === date);
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

        const series = selectedColumns.map(column => {
            let rawData = data.map(row => row[column.field]);
            let interpolatedData = interpolateData(rawData); // Interpolate missing values
            return {
                name: column.name,  // Using the custom name for the series
                data: interpolatedData,
                color: seriesColors[parseInt(column.field.replace('score', '')) - 1]  // Deduce color by score index
            };
        });

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

function interpolateData(data) {
    let interpolatedData = [...data];
    for (let i = 1; i < interpolatedData.length - 1; i++) {
        if (interpolatedData[i] === null) {
            let prev = i - 1;
            let next = i + 1;
            while (next < interpolatedData.length && interpolatedData[next] === null) {
                next++;
            }
            if (next < interpolatedData.length) {
                let interpolatedValue = interpolatedData[prev] + (interpolatedData[next] - interpolatedData[prev]) * (i - prev) / (next - prev);
                interpolatedData[i] = parseFloat(interpolatedValue.toFixed(2)); // Round to 2 decimal places
            }
        }
    }
    return interpolatedData;
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
            labels: { formatter: val => val.toFixed(0) } // Ensure whole numbers
        },
        series: seriesData,
        colors: seriesData.map(s => s.color), // Apply specific colors to series
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
        colors: seriesData.map(s => s.color) // Ensure colors are correctly applied
    });
}

// Function to get options for Line Chart
function getLineChartOptions(dates, seriesData) {
    return {
        chart: {
            type: 'line',
            height: '500',  // Make sure this is just a number or a string like '500px'
            background: '#fff',
            toolbar: {
                show: true
            },
            dropShadow: {
                enabled: true,
                top: 1,
                left: 3,
                blur: 3,
                color: '#000',  // Ensure this is a valid color or an array of colors
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
                    return '';  // No labels on trendlines
                }
                return val.toFixed(0);  // Formatting to zero decimal places
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
            width: seriesData.map(series => series.name.includes('Trendline') ? 2 : 5),
            dashArray: seriesData.map(series => series.name.includes('Trendline') ? 5 : 0),
            colors: seriesColors
        },
        series: seriesData,
        grid: {
            borderColor: '#a8a8a8',
            strokeDashArray: 0, // Solid lines
            position: 'back',  // Grid lines behind the data points
            xaxis: {
                lines: {
                    show: true  // Show vertical grid lines
                }
            },
            yaxis: {
                lines: {
                    show: true  // Show horizontal grid lines
                }
            }
        },
        markers: {
            size: 5
        },
        xaxis: {
            categories: dates,
            title: {
                text: 'Date',
                offsetY: -20
            },
            axisTicks: {
                show: true
            },
            axisBorder: {
                show: true
            }
        },
        yaxis: {
            title: {
                text: 'Value'
            },
            labels: {
                formatter: function(val) {
                    return val.toFixed(0); // Ensuring labels are whole numbers
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
                    return val;
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
            refreshStatisticsDisplay();  // Update to call refresh on any click
        });
        columnSelector.appendChild(item);
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

function refreshStatisticsDisplay() {
    const selectedColumns = Array.from(document.querySelectorAll(".selector-item.selected"));
    const tbody = document.getElementById('statsTable').getElementsByTagName('tbody')[0];
    tbody.innerHTML = ''; // Clear existing rows

    if (selectedColumns.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5">No columns selected</td></tr>'; // Handle no selection
        return;
    }

    selectedColumns.forEach(item => {
        const columnField = item.getAttribute("data-column-name");
        const columnName = item.textContent.trim();
        updateStatisticsDisplay(columnField, columnName, tbody);
    });
}

function calculateStatistics(data) {
    let mean = data.reduce((acc, val) => acc + val, 0) / data.length;
    let median = calculateMedian(data);
    let stdDev = calculateStandardDeviation(data, mean);

    return {
        mean: mean.toFixed(2),
        median: median,
        stdDev: stdDev.toFixed(2)
    };
}

function calculateStandardDeviation(data, mean) {
    let squareDiffs = data.map(value => {
        let diff = value - mean;
        return diff * diff;
    });
    let avgSquareDiff = squareDiffs.reduce((sum, value) => sum + value, 0) / data.length;
    return Math.sqrt(avgSquareDiff);
}

function calculateMedian(data) {
    data.sort((a, b) => a - b);
    let mid = Math.floor(data.length / 2);
    return data.length % 2 !== 0 ? data[mid] : (data[mid - 1] + data[mid]) / 2;
}

function calculateTrendlineEquation(data) {
    const n = data.length;
    let sumX = 0, sumY = 0, sumXY = 0, sumXX = 0;
    data.forEach((y, x) => {
        sumX += x;
        sumY += y;
        sumXY += x * y;
        sumXX += x * x;
    });
    let slope = (n * sumXY - sumX * sumY) / (n * sumXX - sumX * sumX);
    let intercept = (sumY - slope * sumX) / n;
    return `y = ${slope.toFixed(2)}x + ${intercept.toFixed(2)}`;
}

function updateStatisticsDisplay(columnField, columnName, tbody) {
    const data = table.getData().map(row => parseFloat(row[columnField])).filter(val => !isNaN(val));

    if (data.length > 0) {
        const stats = calculateStatistics(data);
        const trendlineEquation = calculateTrendlineEquation(data);
        const row = tbody.insertRow();
        row.innerHTML = `
            <td>${columnName}</td>
            <td>${stats.mean}</td>
            <td>${stats.median}</td>
            <td>${stats.stdDev}</td>
            <td>${trendlineEquation}</td>
        `;
    } else {
        const row = tbody.insertRow();
        row.innerHTML = `<td colspan="5">No data available for ${columnName}</td>`;
    }
}

function showEditColumnNamesModal() {
    const modal = document.getElementById('editColumnNamesModal');
    modal.style.display = 'block';
}

function hideEditColumnNamesModal() {
    const modal = document.getElementById('editColumnNamesModal');
    modal.style.display = 'none';
}

function submitColumnNames(event) {
    event.preventDefault();
    const inputs = event.target.querySelectorAll('input[type="text"]');
    let newColumnNames = Array.from(inputs).map(input => input.value);

    // Send these new column names to server or use them to update the local table
    updateColumnNamesOnServer(newColumnNames);
}

function updateColumnNamesOnServer(newColumnNames) {
    // Prepare the data to be sent to the PHP backend
    const formData = new FormData();
    formData.append('goal_id', '123'); // Assuming you have a way to get the current goal_id
    formData.append('custom_column_names', JSON.stringify(newColumnNames));

    // Make an AJAX call to the PHP script
    fetch('edit_goal_columns.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.message) {
            console.log(data.message);
            alert('Column names updated successfully!');
        } else if (data.error) {
            console.error('Error updating column names:', data.error);
            alert('Failed to update column names: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Network or server error occurred.');
    });
}

