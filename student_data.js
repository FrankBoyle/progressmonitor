
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

$(document).ready(function() {
    
    // Function to fetch groups and populate the dropdown
    function fetchGroups() {
        $.ajax({
            url: './users/fetch_data.php?action=fetchGroups',
            method: 'GET',
            dataType: 'json',
            success: function(groups) {
                let dropdown = $('#scoreGroupDropdown');
                dropdown.empty(); // Clear existing options
                
                groups.forEach(function(group) {
                    dropdown.append($('<option>', {
                        value: group,
                        text: group
                    }));
                });
            },
            error: function(err) {
                console.error('Failed to fetch groups', err);
            }
        });
    }

    function fetchDataForGroup(selectedGroup) {
        $.ajax({
            url: 'fetch_data.php',
            method: 'GET',
            data: { group: selectedGroup },  // This will send the selected group to the server.
            dataType: 'json',
            success: function(data) {
                // Here you handle and display the data. 
                // For instance, if the data was a list of scores, you might display them in a table or list.
                // Just as an example:
                let list = $("#dataList");
                list.empty();
                data.forEach(function(item) {
                    list.append('<li>' + item + '</li>');
                });
            },
            error: function(err) {
                console.error('Failed to fetch data for group', err);
            }
        });
    }

    // Call the fetchGroups function to populate the dropdown on page load
    fetchGroups();

    // Event handler for when an option is selected from the dropdown
    $('#scoreGroupDropdown').on('change', function() {
        let selectedValue = $(this).val();
        
        // Fetch data for the selected group
        fetchDataForGroup(selectedValue);
    });
});


function initializeChart() {
    window.chart = new ApexCharts(document.querySelector("#chart"), getChartOptions([], []));
    window.chart.render();
}

function getChartData(scoreField) {
    var chartData = [];
    var xCategories = [];

    $('tr[data-performance-id]').each(function() {
        var weekStartDate = $(this).find('td[data-field-name="week_start_date"]').text();
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
            name: 'Selected Score',
            data: chartData,
            connectNulls: true,  // Add this line here
            stroke: {
                width: 7  // Adjust this value to your desired thickness
            }
        },
        {
            name: 'Trendline',
            data: trendlineData,
            connectNulls: true  // And add it here as well, if you want to connect null values for the trendline too
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
        connectNulls: true  // Keep this if you want to connect null values for the benchmark series
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
            width: 1000,
            toolbar: {
                show: true,
                tools: {
                    download: false
                }
            },

            dropShadow: {
                enabled: true,
                color: '#000',
                top: 15,          // Adjusted the vertical offset a bit
                left: 5,          // Adjusted the horizontal offset a bit
                blur: 7,         // Increased the blur to make it more spread out
                opacity: 0.8      // Increased the opacity to make it darker
            }

        },

        dataLabels: {
            enabled: true,
            enabledOnSeries: [0],  // enable only on the first series
            offsetY: -7,
            style: {
                fontSize: '12px',
                colors: ['#333']
            }
        },

        stroke: {
            curve: 'smooth',
            width: dataSeries.map(series => series.name === 'Selected Score' ? 3 : 1)  // Set width based on series name
        },

        markers: {
            size: 5,
            colors: undefined,
            strokeColors: '#fff',
            strokeWidth: 3,
            strokeOpacity: 1.0,
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
                sizeOffset: 3
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

$(document).ready(function() {

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
            dataType: 'json',
            cache: false,
        });
        return response;
    } catch (error) {
    console.error('Error during AJAX call:', error);

    // Check if responseJSON is available
    if (error.responseJSON) {
        console.error('Server JSON response:', error.responseJSON);
        return error.responseJSON;
    } else if (error.responseText) {
        console.error('Server response text:', error.responseText);
        return { error: 'An unexpected error occurred. Please check the server logs.' };
    } else {
        console.error('Unknown error:', error);
        return { error: 'An unknown error occurred. Please check the server logs.' };
    }
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

    const response = await ajaxCall('POST', 'update_performance.php', postData);

    if (response && response.error && response.error === 'Duplicate date not allowed') {
    alert("Duplicate date not allowed!");
    cell.html(cell.data('saved-date') || '');  // Revert the cell's content back to the previously saved date or empty string if there's no saved date.
} else if (response && response.saved_date) {
    cell.data('saved-date', response.saved_date);
} else {
    //alert('An error occurred. Please try again.');
}

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
        const weekStartDate = convertToDatabaseDate(row.find('td[data-field-name="week_start_date"]').text());

        const postData = {
            performance_id: performanceId,
            field_name: fieldName,
            new_value: newValue,
            student_id: studentId,
            week_start_date: weekStartDate
        };

        ajaxCall('POST', 'update_performance.php', postData).then(response => {
    console.log(response); // <-- This is the debug line. 

    if (response && response.error && response.error === 'Duplicate date not allowed') {
        alert("Duplicate date not allowed!");
        cell.html(cell.data('saved-date') || '');  
    } else if (response && response.saved_date) {
        cell.data('saved-date', response.saved_date);
    } else {
        //alert('An error occurred. Please try again.');
    }
});
    }

    function isDateDuplicate(dateString, currentPerformanceId = null) {
    console.log("Checking for duplicate of:", dateString);
    let isDuplicate = false;
    $('table').find('td[data-field-name="week_start_date"]').each(function() {
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

            if (cell.data('field-name') === 'week_start_date') {
                input.datepicker({
                    dateFormat: 'mm/dd/yy',
                    beforeShow: function() {
                        datePickerActive = true;
                    },
                    onClose: function(selectedDate) {
    if (isValidDate(new Date(selectedDate))) {
        const currentPerformanceId = cell.closest('tr').data('performance-id');
        if (isDateDuplicate(selectedDate, currentPerformanceId)) {
            alert("This date already exists. Please choose a different date.");
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

                if (cell.data('field-name') === 'week_start_date') {
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
                    week_start_date: weekStartDate
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
                            newRow.find('td[data-field-name="week_start_date"]').text(convertToDisplayDate(response.saved_date));
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
                        alert("There was an error updating the data.");
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
    alert("An entry for this date already exists. Please choose a different date.");
    return;
}
        const newRow = $("<tr data-performance-id='new'>");
        newRow.append(`<td class="editable" data-field-name="week_start_date">${currentDate}</td>`);
        
        for (let i = 1; i <= 10; i++) {
            newRow.append(`<td class="editable" data-field-name="score${i}"></td>`);
        }
        
        newRow.append('<td><button class="saveRow">Save</button></td>');
        $("table").append(newRow);

        newRow.find('td[data-field-name="week_start_date"]').click().blur();
        attachEditableHandler();
        const dateCell = newRow.find('td[data-field-name="week_start_date"]');
        dateCell.click();
    });

    $(document).off('click', '.saveRow').on('click', '.saveRow', async function() {
        const row = $(this).closest('tr');
        const performanceId = row.data('performance-id');
    
    // If it's not a new entry, simply return and do nothing.
        if (performanceId !== 'new') {
            alert("This row is not a new entry. Please click on the cells to edit them.");
            return;
        }
   
        let scores = {};
        for (let i = 1; i <= 10; i++) {
            const scoreValue = row.find(`td[data-field-name="score${i}"]`).text();
            scores['score' + i] = scoreValue ? scoreValue : null; // Send null if score is empty
        }

        const postData = {
            student_id: CURRENT_STUDENT_ID,
            week_start_date: convertToDatabaseDate(row.find('td[data-field-name="week_start_date"]').text()),
            scores: scores
        };

        if (isDateDuplicate(postData.week_start_date)) {
        alert("An entry for this date already exists. Please choose a different date.");
        return;
    }

        const response = await ajaxCall('POST', 'insert_performance.php', postData);
        if (response && response.error && response.error === 'Duplicate date not allowed') {
            alert("Duplicate date not allowed!");
        } else if (response && response.performance_id) {
            // Update the row with the data returned from the server
            row.attr('data-performance-id', response.performance_id);
            row.find('td[data-field-name="week_start_date"]').text(convertToDisplayDate(response.week_start_date));
            // If you have any default scores or other fields returned from the server, update them here too
            } else {
                //alert("There was an error saving the data.");
            }
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

