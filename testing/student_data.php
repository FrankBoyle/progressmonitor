<?php
include ('./users/fetch_data.php');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

foreach ($students as $student) {
    if ($student['student_id'] == $studentId) { // If the IDs match
        $studentName = $student['name']; // Get the student name
        break;
    }
}

if (isset($_GET['metadata_id'])) {
    $selectedMetadataId = $_GET['metadata_id'];

    // Now fetch the corresponding category name based on this metadata_id
    foreach ($metadataEntries as $metadataEntry) {
        if ($metadataEntry['metadata_id'] == $selectedMetadataId) {
            $selectedCategoryName = $metadataEntry['category_name'];
            break; // We found our category, no need to continue the loop
        }
    }
} else {
    // Optional: Handle cases where no metadata_id is specified, if needed
    // $selectedCategoryName = "Default Category or message"; // for example
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $studentName; ?></title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.0/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/ui/1.13.0/jquery-ui.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.css">
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/apexcharts@3.41.1/dist/apexcharts.min.css">
    <link rel="stylesheet" href="../plugins/summernote/summernote-bs4.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.18/summernote-bs4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.3.2/html2canvas.min.js"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="../plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="../plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
    <link rel="stylesheet" href="../dist/css/adminlte.min.css">
    <link rel="stylesheet" href="../plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
    <link rel="stylesheet" href="../plugins/icheck-bootstrap/icheck-bootstrap.min.css">
    <link rel="stylesheet" href="../plugins/jqvmap/jqvmap.min.css">
    <link rel="stylesheet" href="../plugins/daterangepicker/daterangepicker.css">
    <link rel="stylesheet" href="styles.css">
    <script src="student_data.js" defer></script>
    <script>
        var scoreNamesFromPHP = <?php echo json_encode($scoreNames); ?>;
    </script>
    <style>

    </style>
    <div>
<input type="hidden" id="schoolIdInput" name="school_id" value="<?php echo htmlspecialchars($school_id); ?>">
<input type="hidden" id="currentStudentId" value="<?php echo htmlspecialchars($student_id); ?>" />
<input type="hidden" id="currentWeekStartDate" value="<?php echo htmlspecialchars($currentWeekStartDate); ?>" />
<input type="hidden" id="studentName" name="studentName" value="<?php echo htmlspecialchars($studentName); ?>">
</div>   
</head>
<body>
    <div class="dashboard">
      
       <header class="dashboard-header">
          <div class="logo">
            <img src="bFactor_logo.png" alt="Logo">
          </div>

          <div class="header-icons">
            <a href="students.php" class="nav-link">
              <i class="nav-icon fas fa-sign-out-alt"></i>
              <p>Home</p>
            </a>             
            
            <!--<span>Icon 2</span>-->

            <a href="./users/logout.php" class="nav-link">
              <i class="nav-icon fas fa-sign-out-alt"></i>
              <p>Sign Out</p>
            </a> 

          </div>
        </header>

        <div class="content-wrapper">
            <section class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1><?php echo $studentName; ?> Performance Data - <?php echo $selectedCategoryName; ?></h1>
                            <a href="students.php" class="btn btn-primary">Home</a>
                        </div>
                        <div class="col-sm-6">

                        </div>
                    </div>
                </div>
            </section>

            <section class="content">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Categories</h3><br>
                        <?php foreach ($metadataEntries as $metadataEntry): ?>
                            <a href="?student_id=<?php echo $studentId; ?>&metadata_id=<?php echo $metadataEntry['metadata_id']; ?>">
                                <?php echo $metadataEntry['category_name']; ?>
                            </a><br>
                        <?php endforeach; ?>
                    </div>
                    <div class="form-group" style="text-align: center;">
                <label for="iep_date" style="display: block;">IEP Date:</label>
                <input type="date" id="iep_date" name="iep_date" class="form-control" value="<?php echo htmlspecialchars($iep_date); ?>">
            </div>
            <button id="filterData" class="btn btn-primary">Filter Data</button>
                </div>
            </section>

            <section class="content">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"></h3>
                        <table border="1" id="dataTable">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <?php foreach ($scoreNames as $category => $values): ?>
                                        <?php if (is_array($values)): ?>
                                            <?php foreach ($values as $score): ?>
                                                <th><?php echo htmlspecialchars($score); ?></th>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <th><?php echo htmlspecialchars($values); ?></th>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="dataTableBody">
                                <?php if (empty($performanceData)): ?>
                                    <tr>
                                        <td colspan="11">No Data Found. Click "Add Data Row" to add new data.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($performanceData as $data): ?>
                                        <tr data-performance-id="<?php echo $data['performance_id']; ?>">
                                            <td class="editable" data-field-name="score_date">
                                                <?php echo isset($data['score_date']) ? date("m/d/Y", strtotime($data['score_date'])) : ""; ?>
                                            </td>
                                            <?php for ($i = 1; $i <= 10; $i++): ?>
                                                <td class="editable" data-field-name="score<?php echo $i; ?>">
                                                    <?php echo isset($data['score'.$i]) ? $data['score'.$i] : ""; ?>
                                                </td>
                                            <?php endfor; ?>
                                            <td><button class="deleteRow btn btn-block btn-primary" data-performance-id="<?php echo $data['performance_id']; ?>">Delete</button></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                        <button id="addDataRow" class="btn btn-primary">Add Data Row</button>
                        <input type="text" id="newRowDate" style="display: none;">
                    </div>
                </div>
            </section>

            <section class="content">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"></h3>
                        <div id="columnSelector">
                            <label>Select Columns to Display:</label>
                            <?php foreach ($scoreNames as $category => $scores): ?>
                                <?php foreach ($scores as $index => $scoreName): ?>
                                    <label>
                                        <input type="checkbox" name="selectedColumns[]" value="<?php echo htmlspecialchars('score'.($index + 1)); ?>" data-column-name="<?php echo htmlspecialchars($scoreName); ?>">
                                        <?php echo htmlspecialchars($scoreName); ?>
                                    </label>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        </div>

                        <div id="accordion">
                            <h3>Line Graph</h3>
                            <div>
                                <div id="chart" style="width: 1000px;"></div>
                            </div>
                            <h3>Bar Graph</h3>
                            <div>
                                <div id="barChart" style="width: 1000px;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="content">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Goals</h3>
                    </div>
                    <div class="card-body">
                        <div class="row" id="goalsList">
                            <?php foreach ($goals as $index => $goal): ?>
                                <div class="col-md-4 col-sm-6 col-12">
                                    <div class="info-box">
                                        <div class="info-box-content goal-container">
                                            <span class="info-box-text">Goal <?php echo $index + 1; ?></span>
                                            <label class="goal-checkbox-label">
                                                <input type="checkbox" class="goal-checkbox" data-goal-id="<?php echo $goal['goal_id']; ?>">
                                                Select
                                            </label>
                                            <textarea id="summernote<?php echo $index + 1; ?>" class="goaltext" contenteditable="true" data-goal-id="<?php echo $goal['goal_id']; ?>">
                                                <?php echo htmlspecialchars($goal['goal_description']); ?>
                                            </textarea>
                                            <button class="save-goal-btn" data-goal-id="<?php echo $goal['goal_id']; ?>">✔</button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="add-goal-form">
                            <input type="text" id="newGoalText" placeholder="Enter new goal description">
                            <button id="addNewGoalBtn">Add New Goal</button>
                        </div>
                        <div class="editable-notes-section">
                            <h3>Goal Notes</h3>
                            <textarea id="graphNotes" class="summernote"></textarea>
                            <button id="saveGraphNotes" class="btn btn-primary">Save Notes</button>
                            <button id="printButton" class="btn btn-primary">Print</button>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
    <script>
            $(document).ready(function() {
      $('.goaltext').summernote({
        toolbar: [
          // Only include buttons for font type and basic styling
          ['font', ['fontname']], // Font type
          ['style', ['bold', 'italic', 'underline']] // Bold, italic, underline
        ],
        fontNames: ['Arial', 'Arial Black', 'Comic Sans MS', 'Courier New', 'Merriweather'] // Add custom font types if needed
      });

    // Initialize Summernote
    $('#graphNotes').summernote({
        height: 300,
        toolbar: [
            // Add your toolbar options here
        ]
    });

    // Disable the textbox initially
    $('#graphNotes').summernote('disable');

    // Enable/Disable the textbox based on goal selection
    $('.goal-checkbox').change(function() {
        if ($(this).is(':checked')) {
            $('#graphNotes').summernote('enable');
        } else {
            $('#graphNotes').summernote('disable');
        }
    });

    // Handle save button click
    $('#saveGraphNotes').click(function() {
    var notes = $('#graphNotes').summernote('code');
    var goalId = $('.goal-checkbox:checked').data('goal-id');
    var studentId = $('#currentStudentId').val(); // Assuming this is the correct way to get the student ID
    var schoolId = $('#schoolIdInput').val();     // Assuming this is the correct way to get the school ID
    var metadataId = urlParams.get('metadata_id'); // Assuming this is the correct way to get the metadata ID

    // AJAX call to save the notes
    $.post('./users/save_graph_notes.php', {
        notes: notes,
        goal_id: goalId,
        student_id: studentId,
        school_id: schoolId,
        metadata_id: metadataId
    }, function(response) {
        // Handle response
        console.log(response);
    }).fail(function(error) {
        console.log('Error: ', error);
    });
});
    
$('.goal-checkbox').change(function() {
    var goalId = $(this).data('goal-id');
    if (this.checked) {
        $.get('./users/get_goal_notes.php', { goal_id: goalId }, function(response) {
            var data = JSON.parse(response);
            if (data.status === 'success') {
                $('#graphNotes').summernote('code', data.notes);
            } else {
                $('#graphNotes').summernote('code', '');
                // Optionally alert the user if no notes were found
                alert(data.message);
            }
        });
    } else {
        $('#graphNotes').summernote('code', ''); // Clear the notes when no goal is selected
    }
});

$('#printButton').click(function() {
    var currentChart = selectedChartType === 'bar' ? barChart : chart;
    getGraphContentAsImage(currentChart, function(graphImage) {
        if (graphImage) {
            var notesContent = $('#graphNotes').summernote('code');
            var selectedGoalContent = getSelectedGoalContent();
            var contentToPrint = '<div><strong>Selected Goal:</strong><br>' + selectedGoalContent + '</div>';
            contentToPrint += '<div><img src="' + graphImage + '"></div>';
            contentToPrint += '<div>' + notesContent + '</div>';
            printContent(contentToPrint);
        } else {
            console.error('Failed to receive graph image');
        }
    });
});

document.getElementById('filterData').addEventListener('click', function() {
    var iepDate = document.getElementById('iep_date').value;
    var studentId = <?php echo json_encode($studentId); ?>; // Pass the studentId from PHP to JavaScript
    
    if (iepDate) {
        // Send the IEP date to the server to save in the database
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "./users/save_iep_date.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                var response = JSON.parse(xhr.responseText);
                if (response.success) {
                    // If successful, proceed to filter the data
                    filterData(iepDate);
                } else {
                    alert(response.message);
                }
            }
        };
        xhr.send("iep_date=" + encodeURIComponent(iepDate) + "&student_id=" + encodeURIComponent(studentId));
    }
});

function filterData(iepDate) {
    var studentId = <?php echo json_encode($studentId); ?>; // Pass the studentId from PHP to JavaScript
    var metadataId = <?php echo json_encode($metadataId); ?>; // Pass the metadataId from PHP to JavaScript

    var xhr = new XMLHttpRequest();
    xhr.open("GET", "./users/fetch_filtered_data.php?student_id=" + encodeURIComponent(studentId) + "&metadata_id=" + encodeURIComponent(metadataId) + "&iep_date=" + encodeURIComponent(iepDate), true);
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            var dataTableBody = document.getElementById('dataTableBody');
            if (dataTableBody) {
                console.log("Element with ID 'dataTableBody' found.");
                dataTableBody.innerHTML = xhr.responseText;
                attachEditableHandler(); // Reattach editable handler to new table rows
            } else {
                console.error("Element with ID 'dataTableBody' not found.");
            }
        }
    };
    xhr.send();
}

function getSelectedGoalContent() {
    var checkedCheckbox = document.querySelector('.goal-checkbox:checked');
    if (checkedCheckbox) {
        var goalContainer = checkedCheckbox.closest('.goal-container');
        if (goalContainer) {
            // Extract and return only the goal text
            var goalTextElement = goalContainer.querySelector('.goaltext');
            return goalTextElement ? goalTextElement.value : ''; // Using value to get the text content
        }
    }
    return 'No goal selected';
}

function getGraphContentAsImage(chartVar, callback) {
    if (chartVar) {
        chartVar.dataURI().then(({ imgURI }) => {
            callback(imgURI);
        }).catch(error => {
            console.error('Error in converting chart to image:', error);
            callback(null);
        });
    } else {
        console.error('Chart variable is null or undefined');
        callback(null);
    }
}

function printContent(content) {
    var studentName = document.getElementById('studentName').value; // Fetch the student's name

    var printWindow = window.open('', '_blank');
    var image = new Image();
    image.onload = function() {
        printWindow.document.write('<html><head><title>Print</title></head><body>');
        printWindow.document.write('<h1>' + studentName + '</h1>'); // Include the student's name
        printWindow.document.write(content);
        printWindow.document.write('</body></html>');
        printWindow.document.close();
        printWindow.focus();
        setTimeout(() => printWindow.print(), 500);
    };
    image.onerror = function() {
        console.error('Error loading the image');
    };
    image.src = content.match(/src="([^"]+)"/)[1];
}
    });
    </script>
    
</body>
</html>