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

Sure! To label the goals by category_name instead of metadata_id, we'll need to update both the backend to fetch and include category_name in the response and the frontend to use this label when displaying the goals.

Backend: Update fetch_goals.php
First, modify the PHP script to include category_name in the response.

php
Copy code
<?php
include('auth_session.php');
include('db.php');

// Error Reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Fetch goals by student ID and optionally grouped by metadata_id
if (isset($_GET['student_id'])) {
    $studentId = $_GET['student_id'];
    $goals = fetchGoals($studentId);
    echo json_encode($goals);
}

function fetchGoals($studentId) {
    global $connection;
    $stmt = $connection->prepare("
        SELECT g.goal_description, g.metadata_id, m.category_name
        FROM Goals g
        JOIN Metadata m ON g.metadata_id = m.metadata_id
        WHERE g.student_id = ?
    ");
    $stmt->execute([$studentId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
Frontend: Update JavaScript
Modify the JavaScript to use category_name for labeling the goal sections.

html
Copy code
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Layout</title>
    <link rel="stylesheet" href="styles.css">
    <!-- Include Quill CSS -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <style>
        /* Basic Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif';
            background-color: #E5E5E5;
            margin: 0;
        }

        .dashboard {
            display: flex;
            flex-direction: column;
            height: 100vh;
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            background-color: #333;
            color: white;
        }

        .logo {
            font-weight: bold;
        }

        .header-icons span {
            margin-right: 10px;
            cursor: pointer;
        }

        .content {
            display: flex;
            flex-grow: 1;
            padding: 1rem;
        }

        .box {
            background-color: white;
            padding: 1rem;
            margin: 0.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            flex: 1;
        }

        .create-group,
        .existing-groups {
            grid-row: span 2;
        }

        .selected-group {
            font-weight: bold;
        }

        .options-btn {
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            margin-left: 10px;
        }

        .group-options {
            position: absolute;
            background: white;
            border: 1px solid #ccc;
            padding: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            display: none;
            z-index: 1000;
        }

        .group-options button {
            display: block;
            width: 100%;
            border: none;
            background: none;
            padding: 10px;
            text-align: left;
            cursor: pointer;
            font-size: 14px;
            color: #333;
        }

        .group-options button:hover {
            background-color: #f0f0f0;
        }

        button {
            background-color: #555;
            color: white;
            padding: 8px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background-color: #333;
        }

        ul {
            list-style-type: none;
            padding: 0;
        }

        ul li {
            background-color: #e0e0e0;
            margin: 0.5rem 0;
            padding: 0.5rem;
            border-radius: 5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
        }

        ul li:hover {
            background-color: #ccc;
        }

        .add-group-btn {
            background-color: transparent;
            border: none;
            color: inherit;
            cursor: pointer;
            font-size: 1.2rem;
            margin-left: 5px;
        }

        h2 {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }

        h3 {
            margin-bottom: 0.5rem;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgb(0,0,0);
            background-color: rgba(0,0,0,0.4);
            padding-top: 60px;
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 400px;
            border-radius: 8px;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        @media (max-width: 768px) {
            .content {
                grid-template-columns: 1fr;
            }

            .create-group,
            .existing-groups {
                grid-row: auto;
            }
        }
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