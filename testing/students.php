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
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">

    <style>
    </style>
</head>

<body>

    <div class="dashboard">
        <header class="dashboard-header">
            <div class="logo">Logo</div>
            <div class="header-icons">
                <span>Icon 1</span>
                <span>Icon 2</span>
                <span>Icon 3</span>
            </div>
        </header>

        <main class="content">
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

            <section class="box existing-groups">
                <h3>Goals</h3>
                <div id="goal-list">
                    <!-- Goals will be loaded here and grouped by metadata_id -->
                </div>
            </section>

        </main>
    </div>

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

    <div id="group-options" class="group-options">
        <button onclick="editGroup()">Edit Group</button>
        <button onclick="shareGroup()">Share Group</button>
    </div>

    <!-- Include Quill JavaScript -->
    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            loadGroups();
            document.addEventListener('click', function(event) {
                const optionsMenu = document.getElementById('group-options');
                if (!optionsMenu.contains(event.target)) {
                    optionsMenu.style.display = 'none';
                }
            });
        });

        function loadGroups() {
            fetch('users/fetch_groups.php')
                .then(response => response.json())
                .then(data => {
                    const groupList = document.getElementById('group-list').querySelector('ul');
                    groupList.innerHTML = '';
                    data.forEach(group => {
                        const listItem = document.createElement('li');
                        listItem.textContent = group.group_name;
                        listItem.setAttribute('data-group-id', group.group_id);
                        listItem.addEventListener('click', function() {
                            selectGroup(this);
                        });

                        const optionsBtn = document.createElement('button');
                        optionsBtn.textContent = '⋮';
                        optionsBtn.className = 'options-btn';
                        optionsBtn.addEventListener('click', function(event) {
                            showGroupOptions(event, group.group_id);
                        });

                        listItem.appendChild(optionsBtn);
                        groupList.appendChild(listItem);
                    });
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('There was an error loading groups. Please try again.');
                });
        }

        function showAddGroupModal() {
            document.getElementById('add-group-modal').style.display = 'block';
        }

        function hideAddGroupModal() {
            document.getElementById('add-group-modal').style.display = 'none';
        }

        function addGroup(event) {
            event.preventDefault();
            const groupName = document.getElementById('group-name').value;

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
                    loadGroups();
                    hideAddGroupModal();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('There was an error adding the group. Please try again.');
                });
        }

        function selectGroup(element) {
            const groupId = element.getAttribute('data-group-id');

            fetch('users/fetch_students_by_group.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'group_id=' + encodeURIComponent(groupId)
            })
                .then(response => response.json())
                .then(data => {
                    const studentList = document.getElementById('student-list');
                    studentList.innerHTML = '';
                    data.forEach(student => {
                        const listItem = document.createElement('li');
                        listItem.textContent = student.first_name + ' ' + student.last_name;
                        listItem.setAttribute('data-student-id', student.student_id_new);
                        listItem.addEventListener('click', function() {
                            selectStudent(this);
                        });
                        studentList.appendChild(listItem);
                    });

                    const groupItems = document.getElementById('group-list').querySelectorAll('li');
                    groupItems.forEach(group => group.classList.remove('selected-group'));
                    element.classList.add('selected-group');
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('There was an error fetching students. Please try again.');
                });
        }

        function selectStudent(element) {
            const studentId = element.getAttribute('data-student-id');

            fetch(`users/fetch_goals.php?student_id=${encodeURIComponent(studentId)}`)
                .then(response => response.json())
                .then(data => {
                    const goalList = document.getElementById('goal-list');
                    goalList.innerHTML = '';

                    // Group goals by metadata_id and category_name
                    const goalsByMetadata = data.reduce((acc, goal) => {
                        if (!acc[goal.metadata_id]) {
                            acc[goal.metadata_id] = { category_name: goal.category_name, goals: [] };
                        }
                        acc[goal.metadata_id].goals.push(goal);
                        return acc;
                    }, {});

                    // Render goals by group
                    for (const metadataId in goalsByMetadata) {
                        const metadataGoals = goalsByMetadata[metadataId];

                        const metadataContainer = document.createElement('div');
                        metadataContainer.innerHTML = `<h4 class="goal-category">${metadataGoals.category_name}</h4>`;

                        metadataGoals.goals.forEach(goal => {
                            const listItem = document.createElement('div');
                            listItem.classList.add('goal-item', 'quill-editor');
                            listItem.innerHTML = goal.goal_description;
                            metadataContainer.appendChild(listItem);
                        });

                        goalList.appendChild(metadataContainer);
                    }

                    // Use MutationObserver to detect when the goal list items are added to the DOM
                    const goalListObserver = new MutationObserver((mutations) => {
                        mutations.forEach((mutation) => {
                            mutation.addedNodes.forEach((node) => {
                                if (node.nodeType === 1 && node.classList.contains('quill-editor')) {
                                    new Quill(node, {
                                        theme: 'snow',
                                        readOnly: true,
                                        modules: {
                                            toolbar: false
                                        }
                                    });
                                }
                            });
                        });
                    });

                    // Observe the goal list for child nodes being added
                    goalListObserver.observe(goalList, { childList: true });
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('There was an error fetching goals. Please try again.');
                });
        }

        function showGroupOptions(event, groupId) {
            event.stopPropagation();
            const optionsMenu = document.getElementById('group-options');
            optionsMenu.style.display = 'block';
            optionsMenu.style.left = event.pageX + 'px';
            optionsMenu.style.top = event.pageY + 'px';
            optionsMenu.setAttribute('data-group-id', groupId);
        }

        function editGroup() {
            const groupId = document.getElementById('group-options').getAttribute('data-group-id');
            alert('Edit group: ' + groupId);
            // Implement edit group functionality
        }

        function shareGroup() {
            const groupId = document.getElementById('group-options').getAttribute('data-group-id');
            alert('Share group: ' + groupId);
            // Implement share group functionality
        }
    </script>

</body>
</html>