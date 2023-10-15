var benchmark = null;

$(document).ready(function() {
    initializeChart();

    benchmark = parseFloat($("#benchmarkValue").val());
    if (isNaN(benchmark)) {
        benchmark = null;  // Default benchmark value if the input is not provided
    }

    $("#scoreSelector").change(function() {
        var selectedScore = $(this).val();
        updateChart(selectedScore);
    });

    $("#updateBenchmark").click(function() {
        var value = parseFloat($("#benchmarkValue").val());
        if (!isNaN(value)) {
            benchmark = value;
            var selectedScore = $("#scoreSelector").val();
            updateChart(selectedScore);
        } else {
            alert('Please enter a valid benchmark value.');
        }
    });

    updateChart('score1');  // Default
});

function initializeChart() {
    window.chart = new ApexCharts(document.querySelector("#chart"), getChartOptions([], []));
    window.chart.render();
}

function getChartData(scoreField) {
    var chartData = [];
    var xCategories = [];

    $('tr[data-performance-id]').each(function() {
        var weekStartDate = $(this).find('td[data-field-name="score_date"]').text();
        var scoreValue = $(this).find(`td[data-field-name="${scoreField}"]`).text();

        if (weekStartDate !== 'New Entry' && !isNaN(parseFloat(scoreValue))) {
            chartData.push({
                x: weekStartDate,  // Directly use the date string
                y: parseFloat(scoreValue)
            });

            xCategories.push(weekStartDate);
        }
    });

    // Sorting logic starts here
    const sortedChartData = chartData.sort((a, b) => {
        return new Date(a.x) - new Date(b.x);
    });
    const sortedCategories = xCategories.sort((a, b) => {
        return new Date(a) - new Date(b);
    });

    return { chartData: sortedChartData, xCategories: sortedCategories };
}

function updateChart(scoreField) {
    var {chartData, xCategories} = getChartData(scoreField);

    // Calculate trendline
    var trendlineFunction = calculateTrendline(chartData);
    var trendlineData = chartData.map((item, index) => {
        return {
            x: item.x,
            y: trendlineFunction(index)
        };
    });

    var seriesData = [
        {
            name: 'Trendline',
            data: trendlineData,
            connectNulls: true,
            dataLabels: {
                enabled: false // Disable data labels for the Trendline series
            }
        },
        {
            name: 'Selected Score',
            data: chartData,
            connectNulls: true,
            dataLabels: {
                enabled: true // Enable data labels for the Selected Score series
            },
            stroke: {
                width: 7
            }
        }
    ];

    if (benchmark !== null) {
        var benchmarkData = xCategories.map(date => {
            return {
                x: date,
                y: benchmark
            };
        }).reverse();
        seriesData.push({
            name: 'Benchmark',
            data: benchmarkData,
            connectNulls: true,
            dataLabels: {
                enabled: false // Disable data labels for the Benchmark series
            }
        });
    }

    window.chart.updateOptions(getChartOptions(seriesData, xCategories));
}



function getChartOptions(dataSeries, xCategories) {
    return {
        series: dataSeries,
        chart: {
            type: 'line',
            stacked: false,
            width: 600,
            zoom: {
                type: 'x',
                enabled: true,   // Ensure zooming is enabled
                autoScaleYaxis: true  // This will auto-scale the Y-axis when zooming in
            },
            toolbar: {
                autoSelected: 'zoom' 
            },
            pan: {
                enabled: true,  // Enable panning
                mode: 'x',      // Enable horizontal panning
            },   
            dropShadow: {
                enabled: true,
                color: '#000',
                top: 15,          // Adjusted the vertical offset a bit
                left: 5,          // Adjusted the horizontal offset a bit
                blur: 7,         // Increased the blur to make it more spread out
                opacity: 0.5      // Increased the opacity to make it darker
            }
        },


        dataLabels: {
            enabled: true,
            enabledOnSeries: [1],  // enable only on the first series
            offsetY: -10,
            style: {
                fontSize: '12px',
                colors: ['#333']
            }
        },
        
        stroke: {
            curve: 'smooth',
            width: dataSeries.map(series => series.name === 'Selected Score' ? 3 : 1.5)  // Set width based on series name
        },

        markers: {
            size: dataSeries.map(series => {
                if (series.name === 'Selected Score') {
                    return 5;  // or whatever size you want for the "Selected Score" series
                } else {
                    return 0;  // This will make markers invisible for "Trendline" and "Benchmark" series
                }
            }),
            colors: undefined,
            strokeColors: '#fff',
            strokeWidth: 1.7,
            strokeOpacity: 1,
            strokeDashArray: 0,
            fillOpacity: 1,
            discrete: [],
            shape: "circle",
            radius: 2,
            offsetX: 0,
            offsetY: 0,
            onClick: undefined,
            onDblClick: undefined,
            showNullDataPoints: true,
            hover: {
                size: undefined,
                sizeOffset: 1.5
            }
        },

        xaxis: {
                type: 'category', 
                categories: xCategories,
            labels: {
                hideOverlappingLabels: false,
                formatter: function(value) {
                    return value;  // Simply return the value since we're not working with timestamps anymore
                }
            },
            title: {
                text: 'Date'
            }
        },


        yaxis: {
            title: {
                text: 'Value'
            },
            labels: {
                formatter: function(value) {
                    return value.toFixed(0);
                }
            }
        },
        grid: {
            xaxis: {
                lines: {
                    show: true
                }
            }
        },
        colors: ['#2196F3', '#FF5722', '#000000']
    };
}

function calculateTrendline(data) {
    var sumX = 0;
    var sumY = 0;
    var sumXY = 0;
    var sumXX = 0;
    var count = 0;

    data.forEach(function (point, index) {
        var x = index; // Use index as the x value
        var y = point.y;

        if (y !== null) {
            sumX += x;
            sumY += y;
            sumXY += x * y;
            sumXX += x * x;
            count++;
        }
    });

    var slope = (count * sumXY - sumX * sumY) / (count * sumXX - sumX * sumX);
    var intercept = (sumY - slope * sumX) / count;

    return function (x) {
        return slope * x + intercept;
    };
}

////////////////////////////////////////////////

$(document).ready(function() {

    function getCurrentDate() {
        const currentDate = new Date();
        return `${(currentDate.getMonth() + 1).toString().padStart(2, '0')}/${currentDate.getDate().toString().padStart(2, '0')}/${currentDate.getFullYear()}`;
    }

    // Constants & Variables
    const CURRENT_STUDENT_ID = $('#currentStudentId').val();

    fetch("/testing/users/fetch_metadata.php")
    .then(response => response.text())  // First get the response as text
    .then(text => {
        if (!text) {
            throw new Error("Empty response from server");
        }
        return JSON.parse(text);  // Parse the text as JSON
    })
    .then(data => {
        // Handle the parsed data here
    })
    .catch(error => {
        console.error("Error fetching data:", error);
    });


    fetch("./users/fetch_data.php?student_id=" + CURRENT_STUDENT_ID)
    .then(response => {
        if (!response.ok) {
            throw new Error("Network response was not ok");
        }
        return response.json();
    })
    .then(data => {
        if (data.error) {
            console.error("Server error:", data.error);
            return; // Exit out of the success callback if there's an error.
        }

        const dates = data.dates;
        const scores = data.scores;

        const seriesData = [];

        for (const [label, scoreData] of Object.entries(scores)) {
            seriesData.push({
                name: label, // This will use the custom name
                data: scoreData
            });
        }

        // Assuming you have already initialized your chart elsewhere and it's stored in a variable named "chart".
        // You can update the series and x-axis categories as:
        chart.updateOptions({
            xaxis: {
                categories: dates
            },
            series: seriesData
        });

    })
    .catch(error => {
        console.error("Fetch error:", error);
    });


    fetch('./users/fetch_metadata.php')
.then(response => response.json())
.then(data => {
    let dropdown = document.getElementById('metadataIdSelector');
    dropdown.addEventListener('change', function() {
        let selectedMetadataId = dropdown.value;
        fetch(`/testing/users/fetch_metadata.php?metadata_id=${selectedMetadataId}`)
            .then(response => response.json())
            .then(data => {
                // handle the data here. Update the page, chart, etc.
            })
            .catch(error => {
                console.error('There was an error fetching the metadata:', error);
            });
    });
    
});

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
            return response;
        } catch (error) {
            console.error('Error during AJAX call:', error);
            return error.responseJSON;  // Return the parsed JSON error message
        }   
    }

    async function saveEditedDate(cell, newDate) {
        const performanceId = cell.closest('tr').data('performance-id');
        const fieldName = cell.data('field-name');
        const studentId = CURRENT_STUDENT_ID;
        const postData = {
            performance_id: performanceId,
            field_name: fieldName,
            new_value: convertToDatabaseDate(newDate), // Convert to yyyy-mm-dd format before sending
            student_id: studentId

        };
        console.log(postData);
        //console.log("studentID:", student_id);


        ajaxCall('POST', 'update_performance.php', postData).then(response => {
            console.log(response); // <-- This is the debug line. 
        
            if (response && response.error && response.error === 'Duplicate date not allowed!') {
                alert("Duplicate date not allowed!");
                cell.html(cell.data('saved-date') || '');  
            } else if (response && response.saved_date) {
                cell.data('saved-date', response.saved_date);
            } else {
            }
        });  
    }

    let dateAscending = true; // to keep track of current order

    $('#toggleDateOrder').on('click', function() {
        const table = $('table').DataTable();
        dateAscending = !dateAscending; // flip the state

        table.order([0, dateAscending ? 'asc' : 'desc']).draw();
    });

    $(document).on('click', '.deleteRow', function() {
        const row = $(this);  // Capture the button element for later use
        const performanceId = $(this).data('performance-id');
    
        // Confirm before delete
        if (!confirm('Are you sure you want to delete this row?')) {
            return;
        }
    
        // Send a request to delete the data from the server
        $.post('delete_performance.php', {
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

    function updateScoreInDatabase(row, fieldName, newValue) {
        const performanceId = row.data('performance-id');
        const studentId = CURRENT_STUDENT_ID;
        const weekStartDate = convertToDatabaseDate(row.find('td[data-field-name="score_date"]').text());

        const postData = {
            performance_id: performanceId,
            field_name: fieldName,
            new_value: newValue,
            student_id: studentId,
            score_date: weekStartDate
        };

        ajaxCall('POST', 'update_performance.php', postData).then(response => {
            if (response && !response.success) {
                //alert('Error updating the average score in the database.');
            }
        });
    }

    function populateMetadataDropdown(data) {
        const dropdown = document.getElementById("metadataIdSelector"); // Assuming you have a dropdown with this ID
    
        // Clear any existing options
        dropdown.innerHTML = '';
    
        // Add new options
        data.forEach(item => {
            const option = document.createElement('option');
            option.value = item.metadata_id;
            option.textContent = item.category_name;
            dropdown.appendChild(option);
        });
    }
    

    // This function fetches metadata when the page loads and populates the dropdown.
function fetchMetadataOnLoad() {
    fetch("./users/fetch_metadata.php")
    .then(response => {
        if (!response.ok) {
            throw new Error("Network response was not ok");
        }
        return response.json(); 
    })
    .then(data => {
        populateMetadataDropdown(data);
    })
    .catch(error => {
        console.error("Error:", error);
    });
}

// This function updates the chart based on the selected metadata ID.
function fetchScoresOnMetadataChange(metadataId) {
    fetch(`./users/fetch_metadata.php?metadata_id=${metadataId}`)
    .then(response => {
            if (!response.ok) {
                throw new Error("Network response was not ok");
            }
            return response.text();
        })
        .then(data => {
            if (data.trim().length > 0) {
                const parsedData = JSON.parse(data);
                updateChartWithScores(parsedData);
            } else {
                throw new Error("Empty response from server");
            }
        })
        .catch(error => {
            console.error("Error:", error);
        });
}

// This is your event listener for when a new metadata is selected.
document.getElementById("metadataIdSelector").addEventListener("change", function(event) {
    const selectedMetadataId = event.target.value;
    fetchScoresOnMetadataChange(selectedMetadataId);
});

// Call the initial metadata fetch when the page loads.
fetchMetadataOnLoad();


    function isDateDuplicate(dateString, currentPerformanceId = null) {
    //console.log("Checking for duplicate of:", dateString);
    let isDuplicate = false;
    $('table').find('td[data-field-name="score_date"]').each(function() {
        const cellDate = $(this).text();
        const performanceId = $(this).closest('tr').data('performance-id');
        if (cellDate === dateString && performanceId !== currentPerformanceId) {
            isDuplicate = true;
            return false; // Break out of the .each loop
        }
    });
    return isDuplicate;
}

    function attachEditableHandler() {
        $('table').on('click', '.editable:not([data-field-name="score8"])', function() {
            const cell = $(this);
            const originalValue = cell.text();
            const input = $('<input type="text">');
            input.val(originalValue);

            let datePickerActive = false;

            if (cell.data('field-name') === 'score_date') {
                input.datepicker({
                    dateFormat: 'mm/dd/yy',
                    beforeShow: function() {
                        datePickerActive = true;
                    },
                    onClose: function(selectedDate) {
    if (isValidDate(new Date(selectedDate))) {
        const currentPerformanceId = cell.closest('tr').data('performance-id');
        if (isDateDuplicate(selectedDate, currentPerformanceId)) {
            //alert("This date already exists. Please choose a different date.");
            cell.html(originalValue); // Revert to the original value
            return;
        }
        cell.text(selectedDate);  // Set the selected date
        cell.append(input.hide());  // Hide the input to show the cell text
        saveEditedDate(cell, selectedDate); // Save the edited date
    }
    datePickerActive = false;
}

                });
                cell.html(input);
                input.focus();
            } else {
                cell.html(input);
                input.focus();
            }

            input.blur(function() {
                if (datePickerActive) {
                    return;
                }

                let newValue = input.val();
                cell.html(newValue);
                const performanceId = cell.closest('tr').data('performance-id');
    
                // Check if it's a new row. If so, just return and don't do any AJAX call. 
                if (performanceId === 'new') {
                    return;
                }

                if (cell.data('field-name') === 'score_date') {
                    const parts = newValue.split('/');
                    if (parts.length !== 3) {
                        cell.html(originalValue);
                        return;
                    }
                    // Save the new value for the database but display the original mm/dd/yyyy format to the user
                    cell.html(newValue);  // The selected value from datepicker is already in mm/dd/yyyy format, so just display it
                    newValue = convertToDatabaseDate(newValue);  // Convert to yyyy-mm-dd format for database use
                    saveEditedDate(cell, newValue); // Save the edited date
                } else {
                    cell.html(newValue);
                }

                //const performanceId = cell.closest('tr').data('performance-id');
                const fieldName = cell.data('field-name');
                const targetUrl = (performanceId === 'new') ? 'insert_performance.php' : 'update_performance.php';
                const studentId = $('#currentStudentId').val();
                const weekStartDate = convertToDatabaseDate($('#currentWeekStartDate').val());

                let postData = {
                    performance_id: performanceId,
                    field_name: fieldName,
                    new_value: newValue,
                    student_id: studentId,
                    score_date: weekStartDate
                };

                if (performanceId === 'new') {
                    const row = $(this).closest('tr');
                    let scores = {};
                    for (let i = 1; i <= 10; i++) {
                        const scoreValue = row.find(`td[data-field-name="score${i}"]`).text();
                        scores['score' + i] = scoreValue ? scoreValue : null; // Send null if score is empty
                    }
                    postData.scores = scores;
                }

                $.ajax({
                    type: 'POST',
                    url: targetUrl,
                    data: postData,
                    success: function(response) {
                        if (performanceId === 'new') {
                            const newRow = $('tr[data-performance-id="new"]');
                            newRow.attr('data-performance-id', response.performance_id);
                            newRow.find('td[data-field-name="score_date"]').text(convertToDisplayDate(response.saved_date));
                        }
    
    // New code for updating score8 starts here
                        if (['score1', 'score2', 'score3', 'score4'].includes(fieldName)) {
                            const row = cell.closest('tr');
                            const score1 = parseFloat(row.find('td[data-field-name="score1"]').text()) || 0;
                            const score2 = parseFloat(row.find('td[data-field-name="score2"]').text()) || 0;
                            const score3 = parseFloat(row.find('td[data-field-name="score3"]').text()) || 0;
                            const score4 = parseFloat(row.find('td[data-field-name="score4"]').text()) || 0;
                            const average = (score1 + score2 + score3 + score4) / 4;
                            row.find('td[data-field-name="score8"]').text(average.toFixed(2)); // Format the result to 2 decimal places
                            // Update the score8 value in the database
                            updateScoreInDatabase(row, 'score8', average.toFixed(2));
                        }
                    },
                    error: function() {
                        // Handle any error here, e.g., show a notification to the user
                        //alert("There was an error updating the data.");
                    }
                });
            });

            // Pressing Enter to save changes
            input.off('keypress').keypress(function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    input.blur();
                }
            });
        });
    }

$('#addDataRow').off('click').click(function() {
    // Check for an existing "new" row
    if ($('tr[data-performance-id="new"]').length) {
        alert("Please save the existing new entry before adding another one.");
        return;
    }
    
    const currentDate = getCurrentDate();
if (isDateDuplicate(currentDate)) {
    //alert("An entry for this date already exists. Please choose a different date.");
    return;
}
        const newRow = $("<tr data-performance-id='new'>");
        newRow.append(`<td class="editable" data-field-name="score_date">${currentDate}</td>`);
        
        for (let i = 1; i <= 10; i++) {
            newRow.append(`<td class="editable" data-field-name="score${i}"></td>`);
        }
        
        newRow.append('<td><button class="saveRow">Save</button></td>');
        $("table").append(newRow);

        newRow.find('td[data-field-name="score_date"]').click().blur();
        attachEditableHandler();
        const dateCell = newRow.find('td[data-field-name="score_date"]');
        dateCell.click();
    });

    $(document).on('click', '.saveRow', async function() {
        const row = $(this).closest('tr');
        const performanceId = row.data('performance-id');
    
        // Disable the save button to prevent multiple clicks
        $(this).prop('disabled', true);
    
        // If it's not a new entry, simply return and do nothing.
        if (performanceId !== 'new') {
            //alert("This row is not a new entry. Please click on the cells to edit them.");
            return;
        }
    
        let scores = {};
        for (let i = 1; i <= 10; i++) {
            const scoreValue = row.find(`td[data-field-name="score${i}"]`).text();
            scores[`score${i}`] = scoreValue ? scoreValue : null; // Send null if score is empty
        }
    
        const postData = {
            student_id: CURRENT_STUDENT_ID,
            score_date: convertToDatabaseDate(row.find('td[data-field-name="score_date"]').text()),
            scores: scores // Include the scores object in postData
        };
    
        if (isDateDuplicate(postData.score_date)) {
           // alert("An entry for this date already exists. Please choose a different date.");
            return;
        }
    
        const response = await ajaxCall('POST', 'insert_performance.php', postData);
        if (response && response.performance_id) {
            // Update the table with the newly inserted row
            const newRow = $('tr[data-performance-id="new"]');
            newRow.attr('data-performance-id', response.performance_id);
            newRow.find('td[data-field-name="score_date"]').text(convertToDisplayDate(response.score_date));
            // If you have default scores or other fields returned from the server, update them here too
        
            // Clear the input fields and enable the save button for future entries
            newRow.find('td.editable').text('');
            newRow.find('.saveRow').prop('disabled', false);
        
            // Optionally, display a success message
            // alert("Data saved successfully!");
        
        } else {
            // Handle the error response appropriately
            if (response && response.error) {
                alert("Error: " + response.error);
            } else {
                alert("There was an error saving the data.");
            }
        }
        location.reload();
 
    });
    
    document.getElementById('metadataIdSelector').addEventListener('change', function(e) {
        const selectedMetadataId = e.target.value;
    
        fetch(`./users/fetch_metadata.php?metadata_id=${selectedMetadataId}`)
            .then(response => response.json())
            .then(data => {
                // Here, assuming the server returns an array of score names in the order
                // [score1_name, score2_name, ...]
    
                data.forEach((scoreName, index) => {
                    const scoreElement = document.getElementById(`score${index + 1}Name`);
                    if(scoreElement) {
                        scoreElement.textContent = scoreName;
                    }
                });
            })
            .catch(error => {
                console.error("Failed to fetch metadata:", error);
            });
    });
    
        // Initialize the datepicker
        $("#startDateFilter").datepicker({
            dateFormat: 'mm/dd/yy',
            onSelect: function() {
                //console.log("Table rows before draw: " + table.rows().count());
                //console.log("Date selected: " + $(this).val());
                table.draw();
                //console.log("Table rows after draw: " + table.rows().count());
            }
        });

// Custom filter for DataTables
$.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
    let selectedDate = $("#startDateFilter").datepicker("getDate");
    if (!selectedDate) {
        return true; // if no date selected, show all rows
    }

    let rowDate = $.datepicker.parseDate("mm/dd/yy", data[0]);

    // Convert both dates to time for a safer comparison
    let rowDateTime = rowDate.getTime();
    let selectedDateTime = selectedDate.getTime();

    //console.log(`Comparing rowDate ${rowDate} to selectedDate ${selectedDate}. Result: ${rowDateTime >= selectedDateTime}`);
    return rowDateTime >= selectedDateTime;
});

        $(document).on('keypress', '.saveRow', function(e) {
            if (e.which === 13) {
                e.preventDefault();
            }
        });

        // Initialization code
        $('#currentWeekStartDate').val(getCurrentDate());
        attachEditableHandler();

        $.fn.dataTable.ext.type.detect.unshift(function(value) {
            return value && value.match(/^(\d{1,2}\/\d{1,2}\/\d{4})$/) ? 'date-us' : null;
        });

        $.fn.dataTable.ext.type.order['date-us-pre'] = function(data) {
            var date = data.split('/');
            return (date[2] + date[0] + date[1]) * 1;
        };

        let table = $('table').DataTable({
            "order": [[0, "asc"]],
            "lengthChange": false,
            //"searching": false,
            "paging": false,
            "info": false,
            "columns": [
                { "type": "date-us" },
                null, null, null, null, null, null, null, null, null, null, null
            ]
        });
});