let table; // Declare `table` globally

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
