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
              <i class="nav-icon"></i>
              <p>Home</p>
            </a>             
            
            <!--<span>Icon 2</span>-->

            <a href="./users/logout.php" class="nav-link">
              <i class="nav-icon"></i>
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
    const urlParams = new URLSearchParams(window.location.search);

    $(document).ready(function() {
        $('.goaltext').summernote({
        toolbar: [
            ['font', ['fontname']],
            ['style', ['bold', 'italic', 'underline']]
        ],
        fontNames: ['Arial', 'Arial Black', 'Comic Sans MS', 'Courier New', 'Merriweather']
    });

    $('#graphNotes').summernote({
        height: 300,
        toolbar: []
    });

    $('#graphNotes').summernote('disable');

    $('.goal-checkbox').change(function() {
        if ($(this).is(':checked')) {
            $('#graphNotes').summernote('enable');
        } else {
            $('#graphNotes').summernote('disable');
        }
    });

    $('#saveGraphNotes').click(function() {
        var notes = $('#graphNotes').summernote('code');
        var goalId = $('.goal-checkbox:checked').data('goal-id');
        var studentId = $('#currentStudentId').val();
        var schoolId = $('#schoolIdInput').val();
        var metadataId = new URLSearchParams(window.location.search).get('metadata_id');

        $.ajax({
            url: './users/save_graph_notes.php',
            type: 'POST',
            data: {
                notes: notes,
                goal_id: goalId,
                student_id: studentId,
                school_id: schoolId,
                metadata_id: metadataId
            },
            dataType: 'json',
            success: function(response) {
                console.log(response);
                if (response.status === 'success') {
                    alert('Notes saved successfully');
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('Error occurred while updating goal:', textStatus, '-', errorThrown);
                console.error('Response text:', jqXHR.responseText);
                alert('Error occurred while updating goal: ' + textStatus + ' - ' + errorThrown);
            }
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
                        alert(data.message);
                    }
                });
            } else {
                $('#graphNotes').summernote('code', '');
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

        $('#filterData').click(function() {
            var iepDate = $('#iep_date').val();
            var studentId = $('#currentStudentId').val();
            var metadataId = urlParams.get('metadata_id');

            if (iepDate) {
                $.post('./users/save_iep_date.php', {
                    iep_date: iepDate,
                    student_id: studentId
                }, function(response) {
                    var data = JSON.parse(response);
                    if (data.success) {
                        filterData(iepDate);
                    } else {
                        alert(data.message);
                    }
                }).fail(function(error) {
                    console.log('Error: ', error);
                });
            }
        });

        function filterData(iepDate) {
            var studentId = $('#currentStudentId').val();
            var metadataId = urlParams.get('metadata_id');

            $.get('./users/fetch_filtered_data.php', {
                student_id: studentId,
                metadata_id: metadataId,
                iep_date: iepDate
            }, function(response) {
                $('#dataTableBody').html(response);
                attachEditableHandler();
            }).fail(function(error) {
                console.error('Error: ', error);
            });
        }

        function getSelectedGoalContent() {
            var checkedCheckbox = $('.goal-checkbox:checked');
            if (checkedCheckbox.length) {
                var goalContainer = checkedCheckbox.closest('.goal-container');
                var goalTextElement = goalContainer.find('.goaltext');
                return goalTextElement ? goalTextElement.val() : '';
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
            var studentName = $('#studentName').val();
            var printWindow = window.open('', '_blank');
            printWindow.document.write('<html><head><title>Print</title></head><body>');
            printWindow.document.write('<h1>' + studentName + '</h1>');
            printWindow.document.write(content);
            printWindow.document.write('</body></html>');
            printWindow.document.close();
            printWindow.focus();
            setTimeout(() => printWindow.print(), 500);
        }
    });
</script>

    
</body>
</html>