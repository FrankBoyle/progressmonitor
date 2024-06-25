let table; // Global reference to the Tabulator table
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

    // Initialize charts on page load
    initializeCharts();
});

function fetchInitialData(studentId, metadataId) {
    fetch(`./users/fetch_data2.php?student_id=${studentId}&metadata_id=${metadataId}`)
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
            title: "Date", // Changed from "Score Date"
            field: "score_date",
            editor: "input",
            formatter: function(cell, formatterParams, onRendered) {
                return luxon.DateTime.fromISO(cell.getValue()).toFormat("MM/dd/yyyy");
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

function createColumnCheckboxes(scoreNames) {
    const container = document.getElementById('columnSelector');
    container.innerHTML = ''; // Clear existing options if any

    Object.keys(scoreNames).forEach(key => {
        const label = document.createElement('label');
        const checkbox = document.createElement('input');
        checkbox.type = 'checkbox';
        checkbox.checked = true; // default to checked
        checkbox.value = key;
        checkbox.onchange = extractChartData; // Attach the event to refresh the charts on change

        label.appendChild(checkbox);
        label.append(" " + scoreNames[key]); // Add a space and title
        container.appendChild(label);
    });
}

function initializeCharts() {
    initializeLineChart();
    initializeBarChart();
}

function initializeLineChart() {
    const options = getLineChartOptions([], []); // Initialize with empty data
    chart = new ApexCharts(document.querySelector("#chartContainer"), options);
    chart.render();
}

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
    var categories = data.map(row => luxon.DateTime.fromISO(row['score_date']).toFormat("MM/dd/yyyy"));
    var selectedColumns = getSelectedColumns();
    var series = prepareSeriesData(data, selectedColumns);

    updateLineChart(categories, series);
    updateBarChart(categories, series);
}

function getSelectedColumns() {
    return Array.from(document.querySelectorAll('#columnSelector input[type="checkbox"]:checked'))
                .map(input => input.value);
}

function prepareSeriesData(data, selectedColumns) {
    return selectedColumns.map(column => ({
        name: column,
        data: data.map(row => row[column])
    }));
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

function getLineChartOptions(dates, seriesData) {
    return {
        chart: {
            type: 'line',
            height: 350
        },
        series: seriesData,
        xaxis: {
            categories: dates
        }
    };
}

function getBarChartOptions(dates, seriesData) {
    return {
        chart: {
            type: 'bar',
            height: 350
        },
        series: seriesData,
        xaxis: {
            categories: dates
        }
    };
}
