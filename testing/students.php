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
        <button onclick="assignStudentsToGroupModal()">Assign to Group</button>
    </div>

    <div id="edit-group-modal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="hideEditGroupModal()">&times;</span>
            <h2>Edit Group</h2>
                <form id="edit-group-form" onsubmit="updateGroup(event)">
                    <input type="hidden" id="edit-group-id">
                    <label for="edit-group-name">Group Name:</label>
                    <input type="text" id="edit-group-name" name="group_name" required>
                    <button type="submit">Save Changes</button>
                </form>
            <button onclick="deleteGroup()">Delete Group</button>
            <h3>Assign Students to Group</h3>
            <form id="assign-students-form" onsubmit="assignStudentsToGroup(event)">
                <div style="display: flex; align-items: center;">
                    <div style="margin-right: 10px;">
                        <select name="student_ids[]" multiple class="select2" style="width: 200px; height: 100px;" data-placeholder="Student name here">
                            <option></option>
                            <?php foreach ($allStudents as $student): ?>
                                <option value="<?= htmlspecialchars($student['student_id']) ?>"><?= htmlspecialchars($student['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <button type="submit" name="assign_to_group">Assign to Group</button>
            </div>
        </form>

        <h3>Share Group</h3>
        <form id="share-group-form" onsubmit="shareGroup(event)">
            <input type="hidden" id="share-group-id">
            <select id="share-teacher-id" name="shared_teacher_id">
                <option value="">Select staff here</option>
                <?php foreach ($teachers as $teacher): ?>
                    <option value="<?= htmlspecialchars($teacher['teacher_id']) ?>"><?= htmlspecialchars($teacher['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Share</button>
        </form>
    </div>
</div>


    <!-- Include Quill JavaScript -->
    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
    <script>
let quillInstances = {}; // Initialize quillInstances globally

document.addEventListener('DOMContentLoaded', function() {
    loadGroups();
    document.addEventListener('click', function(event) {
        const optionsMenu = document.getElementById('group-options');
        if (optionsMenu && !optionsMenu.contains(event.target)) {
            optionsMenu.style.display = 'none';
        }
    });

    document.querySelectorAll('.delete-group').forEach(button => {
        button.addEventListener('click', function() {
            const groupId = this.getAttribute('data-group-id');
            if (confirm('Are you sure you want to delete this group?')) {
                deleteGroup(groupId);
            }
        });
    });

    document.querySelectorAll('.edit-group-btn').forEach(button => {
        button.addEventListener('click', function() {
            const groupId = this.getAttribute('data-group-id');
            const groupName = this.getAttribute('data-group-name');
            showEditGroupModal(groupId, groupName);
        });
    });

    $('.select2').select2();

    // Set up the MutationObserver to watch for added nodes
    const goalList = document.getElementById('goal-list');
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.addedNodes.length) {
                mutation.addedNodes.forEach(function(node) {
                    if (node.classList && node.classList.contains('goal-item')) {
                        const quillEditor = node.querySelector('.quill-editor');
                        if (quillEditor) {
                            const goalId = quillEditor.getAttribute('data-goal-id');
                            quillInstances[goalId] = new Quill(quillEditor, {
                                theme: 'snow',
                                readOnly: true,
                                modules: {
                                    toolbar: false
                                }
                            });
                        }
                    }
                });
            }
        });
    });

    observer.observe(goalList, { childList: true, subtree: true });
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

function showEditGroupModal(groupId, groupName) {
    document.getElementById('edit-group-id').value = groupId;
    document.getElementById('edit-group-name').value = groupName;
    document.getElementById('share-group-id').value = groupId;
    document.getElementById('edit-group-modal').style.display = 'block';
    document.querySelector('[name="student_ids[]"]').selectedIndex = -1;
}

function hideEditGroupModal() {
    document.getElementById('edit-group-modal').style.display = 'none';
}

function updateGroup(event) {
    event.preventDefault();
    const groupId = document.getElementById('edit-group-id').value;
    const groupName = document.getElementById('edit-group-name').value;

    fetch('users/update_group.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `group_id=${encodeURIComponent(groupId)}&group_name=${encodeURIComponent(groupName)}`
    })
    .then(response => response.text())
    .then(data => {
        alert(data);
        hideEditGroupModal();
        loadGroups();
    })
    .catch(error => {
        console.error('Error:', error);
        alert('There was an error updating the group. Please try again.');
    });
}

function deleteGroup(groupId) {
    fetch('users/delete_group.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `group_id=${encodeURIComponent(groupId)}`
    })
    .then(response => response.text())
    .then(data => {
        alert(data);
        loadGroups();
    })
    .catch(error => {
        console.error('Error:', error);
        alert('There was an error deleting the group. Please try again.');
    });
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

        const goalsByMetadata = data.reduce((acc, goal) => {
            if (!acc[goal.metadata_id]) {
                acc[goal.metadata_id] = { category_name: goal.category_name, goals: [] };
            }
            acc[goal.metadata_id].goals.push(goal);
            return acc;
        }, {});

        for (const metadataId in goalsByMetadata) {
            const metadataGoals = goalsByMetadata[metadataId];

            const metadataContainer = document.createElement('div');
            const metadataLink = document.createElement('a');
            metadataLink.href = `student_data.php?student_id=${studentId}&metadata_id=${metadataId}`;
            metadataLink.innerHTML = `<h4 class="goal-category">${metadataGoals.category_name}</h4>`;
            metadataContainer.appendChild(metadataLink);

            metadataGoals.goals.forEach(goal => {
                const listItem = document.createElement('div');
                listItem.classList.add('goal-item');
                listItem.innerHTML = `<div class="quill-editor" data-goal-id="${goal.goal_id}">${goal.goal_description}</div>`;
                listItem.innerHTML += `<button class="edit-btn" onclick="editGoal(${goal.goal_id})">✏️</button>`;
                metadataContainer.appendChild(listItem);
            });

            goalList.appendChild(metadataContainer);
        }

        const studentItems = document.getElementById('student-list').querySelectorAll('li');
        studentItems.forEach(student => student.classList.remove('selected-student'));
        element.classList.add('selected-student');
    })
    .catch(error => {
        console.error('Error:', error);
        alert('There was an error fetching goals. Please try again.');
    });

}

function editGoal(goalId) {
    const editor = document.querySelector(`.quill-editor[data-goal-id="${goalId}"]`);
    const quill = quillInstances[goalId];
    quill.enable(true);
    quill.root.setAttribute('contenteditable', true);
    
    // Remove any existing save buttons
    document.querySelectorAll('.save-btn').forEach(btn => btn.remove());

    const saveBtn = document.createElement('button');
    saveBtn.textContent = 'Save';
    saveBtn.className = 'save-btn';
    saveBtn.onclick = function() {
        saveGoal(goalId, quill.root.innerHTML);
    };
    editor.parentNode.appendChild(saveBtn);
}

function saveGoal(goalId, goalDescription) {
    fetch('users/fetch_goals.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `goal_id=${encodeURIComponent(goalId)}&goal_description=${encodeURIComponent(goalDescription)}`
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok ' + response.statusText);
            }
            return response.json();
        })
        .then(data => {
            if (data.status === 'success') {
                const quill = quillInstances[goalId];
                quill.enable(false);
                quill.root.setAttribute('contenteditable', false);
                document.querySelector(`.quill-editor[data-goal-id="${goalId}"]`).parentNode.querySelector('.save-btn').remove();
                alert('Goal updated successfully.');
            } else {
                alert('There was an error updating the goal. Please try again.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('There was an error updating the goal. Please try again.');
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

function shareGroup(event) {
    event.preventDefault();
    const groupId = document.getElementById('share-group-id').value;
    const teacherId = document.getElementById('share-teacher-id').value;

    fetch('users/share_group.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `group_id=${encodeURIComponent(groupId)}&shared_teacher_id=${encodeURIComponent(teacherId)}`
    })
    .then(response => response.text())
    .then(data => {
        alert(data);
        hideEditGroupModal();
        loadGroups();
    })
    .catch(error => {
        console.error('Error:', error);
        alert('There was an error sharing the group. Please try again.');
    });
}

function assignStudentsToGroup(event) {
    event.preventDefault();
    const groupId = document.getElementById('edit-group-id').value;
    const studentIds = Array.from(document.querySelector('[name="student_ids[]"]').selectedOptions).map(option => option.value);

    fetch('users/assign_students_to_group.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `group_id=${encodeURIComponent(groupId)}&student_ids=${encodeURIComponent(studentIds.join(','))}`
    })
    .then(response => response.text())
    .then(data => {
        alert(data);
        hideEditGroupModal();
        loadGroups();
    })
    .catch(error => {
        console.error('Error:', error);
        alert('There was an error assigning students to the group. Please try again.');
    });
}

function showGroupOptions(event, groupId, groupName) {
    event.stopPropagation();
    const optionsMenu = document.getElementById('group-options');
    optionsMenu.style.display = 'block';
    optionsMenu.style.left = event.pageX + 'px';
    optionsMenu.style.top = event.pageY + 'px';
    optionsMenu.setAttribute('data-group-id', groupId);
    optionsMenu.setAttribute('data-group-name', groupName);
}

function editGroup() {
    const groupId = document.getElementById('group-options').getAttribute('data-group-id');
    const groupName = document.getElementById('group-options').getAttribute('data-group-name');
    showEditGroupModal(groupId, groupName);
}

function showEditGroupModal(groupId, groupName) {
    document.getElementById('edit-group-id').value = groupId;
    document.getElementById('edit-group-name').value = groupName;
    document.getElementById('edit-group-modal').style.display = 'block';
    // Clear previous selections in the assign students form
    document.querySelector('[name="student_ids[]"]').selectedIndex = -1;
}

function hideEditGroupModal() {
    document.getElementById('edit-group-modal').style.display = 'none';
}

    </script>

</body>
</html>