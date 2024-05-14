<?php
session_start();
include('users/auth_session.php');
include('users/db.php');

// Enable PHP error logging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', 'error_log.log');  // Ensure this file is writable by the server

// Check if the connection is properly set
if (!isset($connection)) {
    error_log("Database connection is not set.");
    die("Database connection is not set.");
}

error_log("Database connection is set.");

// Handle group creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_group'])) {
    $groupName = $_POST['group_name'];
    try {
        $stmt = $connection->prepare("INSERT INTO Groups (group_name, school_id, teacher_id) VALUES (?, ?, ?)");
        $stmt->execute([$groupName, $_SESSION['school_id'], $_SESSION['teacher_id']]);
        echo "Group created successfully.";
    } catch (PDOException $e) {
        error_log($e->getMessage());
        echo "Error creating group: " . $e->getMessage();
    }
    exit;
}

// Function to fetch students by group ID
function fetchStudentsByGroup($groupId) {
    global $connection;
    $stmt = $connection->prepare("
        SELECT s.* FROM Students_new s
        INNER JOIN StudentGroup sg ON s.student_id_new = sg.student_id
        WHERE sg.group_id = ?
    ");
    $stmt->execute([$groupId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Layout</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.18/summernote-lite.min.css" rel="stylesheet">
    <!-- Include Summernote JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.18/summernote-lite.min.js"></script>
    <style>
        .selected-group {
            background-color: #D3D3D3; /* Light gray background */
            color: #000; /* Black text color */
            font-weight: bold; /* Bold text */
        }
        .group-options {
            position: absolute;
            background: white;
            border: 1px solid #ccc;
            padding: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            display: none;
        }
        .group-options button {
            display: block;
            margin: 5px 0;
        }
    </style>
</head>
<body>

    <div class="dashboard">
        <!-- Top Bar -->
        <header class="dashboard-header">
            <div class="logo">Logo</div>
            <div class="header-icons">
                <span>Icon 1</span>
                <span>Icon 2</span>
                <span>Icon 3</span>
            </div>
        </header>

        <!-- Main Content -->
        <main class="content">
            <!-- First Row -->
            <section class="box create-group">
                <h2>Groups <button class="add-group-btn" onclick="showAddGroupModal()">+</button></h2>
                <div id="group-list">
                    <ul>
                        <!-- Groups will be loaded here -->
                    </ul>
                </div>
            </section>

            <section class="box students-list">
                <h3>Students</h3>
                <ul id="student-list">
                    <!-- Students will be loaded here -->
                </ul>
            </section>

            <!-- Second Row -->
            <section class="box existing-groups">
                <h3>Goals</h3>
                <ul id="goal-list">
                    <!-- Goals will be loaded here -->
                </ul>
            </section>
        </main>
    </div>

    <!-- Add Group Modal -->
    <div id="add-group-modal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="hideAddGroupModal()">&times;</span>
            <h2>Add New Group</h2>
            <form id="add-group-form" onsubmit="addGroup(event)">
                <label for="group-name">Group Name:</label>
                <input type="text" id="group-name" name="group_name" required>
                <button type="submit">Add Group</button>
            </form>
        </div>
    </div>

    <!-- Group Options Menu -->
    <div id="group-options" class="group-options">
        <button onclick="editGroup()">Edit Group</button>
        <button onclick="shareGroup()">Share Group</button>
    </div>


    <script>
        $(document).ready(function() {
            loadGroups();
            $(document).click(function(event) {
                const optionsMenu = $('#group-options');
                if (!optionsMenu.is(event.target) && optionsMenu.has(event.target).length === 0) {
                    optionsMenu.hide();
                }
            });
        });

        function loadGroups() {
            fetch('users/fetch_groups.php')
            .then(response => response.json())
            .then(data => {
                const groupList = $('#group-list ul');
                groupList.empty();
                data.forEach(group => {
                    const listItem = $('<li></li>').text(group.group_name).attr('data-group-id', group.group_id).click(function() {
                        selectGroup($(this));
                    });

                    const optionsBtn = $('<button></button>').text('⋮').addClass('options-btn').click(function(event) {
                        showGroupOptions(event, group.group_id);
                    });

                    listItem.append(optionsBtn);
                    groupList.append(listItem);
                });
            })
            .catch(error => {
                console.error('Error:', error);
                alert('There was an error loading groups. Please try again.');
            });
        }

        function showAddGroupModal() {
            $('#add-group-modal').show();
        }

        function hideAddGroupModal() {
            $('#add-group-modal').hide();
        }

        function addGroup(event) {
            event.preventDefault();
            const groupName = $('#group-name').val();

            fetch('students.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'create_group=1&group_name=' + encodeURIComponent(groupName)
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok ' + response.statusText);
                }
                return response.text();
            })
            .then(data => {
                console.log('Group added successfully:', data);
                loadGroups(); // Reload groups after adding
                hideAddGroupModal();
            })
            .catch(error => {
                console.error('Error:', error);
                alert('There was an error adding the group. Please try again.');
            });
        }

        function selectGroup(element) {
            const groupId = element.attr('data-group-id');

            // Fetch students by group
            fetch('users/fetch_students_by_group.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'group_id=' + encodeURIComponent(groupId)
            })
            .then(response => response.json())
            .then(data => {
                // Update the student list
                const studentList = $('#student-list');
                studentList.empty();
                data.forEach(student => {
                    const listItem = $('<li></li>').text(student.first_name + ' ' + student.last_name).attr('data-student-id', student.student_id_new).click(function() {
                        selectStudent($(this));
                    });
                    studentList.append(listItem);
                });

                // Update the selected group
                $('#group-list li').removeClass('selected-group');
                element.addClass('selected-group');
            })
            .catch(error => {
                console.error('Error:', error);
                alert('There was an error fetching students. Please try again.');
            });
        }

        function selectStudent(element) {
            const studentId = element.attr('data-student-id');
            const selectedGroup = $('.selected-group');

            if (selectedGroup.length) {
                const metadataId = selectedGroup.attr('data-group-id');

                // Fetch goals by student and metadata
                fetch(`users/fetch_goals.php?student_id=${encodeURIComponent(studentId)}&metadata_id=${encodeURIComponent(metadataId)}`)
                .then(response => response.json())
                .then(data => {
                    // Update the goals list
                    const goalList = $('#goal-list');
                    goalList.empty();
                    data.forEach(goal => {
                        const listItem = $('<li></li>').html(goal.goal_description); // Use .html() to display rich text
                        goalList.append(listItem);
                    });

                    // Initialize Summernote on goal items
                    $('#goal-list li').summernote({
                        toolbar: false, // Disable toolbar
                        airMode: true, // Enable air mode for inline editing
                        disableDragAndDrop: true, // Disable drag and drop functionality
                        popover: false, // Disable popover
                        height: null, // Adjust height as needed
                        minHeight: null, // Adjust min height as needed
                        maxHeight: null, // Adjust max height as needed
                    });
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('There was an error fetching goals. Please try again.');
                });
            }
        }

        function showGroupOptions(event, groupId) {
            event.stopPropagation();
            const optionsMenu = $('#group-options');
            optionsMenu.show();
            optionsMenu.css({ left: event.pageX + 'px', top: event.pageY + 'px' });
            optionsMenu.attr('data-group-id', groupId);
        }

        function editGroup() {
            const groupId = $('#group-options').attr('data-group-id');
            alert('Edit group: ' + groupId);
            // Implement edit group functionality
        }

        function shareGroup() {
            const groupId = $('#group-options').attr('data-group-id');
            alert('Share group: ' + groupId);
            // Implement share group functionality
        }
    </script>

</body>
</html>