<?php
session_start();
include('./users/auth_session.php');
include('./users/db.php');

// Ensure account_id is in session
$account_id = $_SESSION['account_id'];
$school_id = $_SESSION['school_id'];

// Fetch the schools associated with the logged-in user
$query = $connection->prepare("SELECT s.school_id, s.SchoolName FROM Schools s JOIN Teachers t ON s.school_id = t.school_id WHERE t.account_id = :account_id");
$query->bindParam("account_id", $account_id, PDO::PARAM_INT);
$query->execute();
$schools = $query->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-9YXLSJ50NV"></script>
    <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());

    gtag('config', 'G-9YXLSJ50NV');
    </script>
    
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Group Management</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/css/lightbox.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.rawgit.com/balzss/luxbar/ae5835e2/build/luxbar.min.css">

</head>
<body>

<div class="dashboard">

        <header class="dashboard-header luxbar-fixed" id="luxbar">
            <input type="checkbox" class="luxbar-checkbox" id="luxbar-checkbox"/>

            <div class="luxbar-menu luxbar-menu-right luxbar-menu-material-indigo">
                <ul class="luxbar-navigation">

                    <li class="luxbar-header">
                        <div class="logo">
                            <img src="IEPreport_logo.jpg" alt="Logo">
                        </div>
                        <label class="luxbar-hamburger luxbar-hamburger-doublespin" id="luxbar-hamburger" for="luxbar-checkbox"> <span></span> </label>
                    </li>

                    <li class="luxbar-item dropdown">
                        <a href="#" class="nav-link" id="helpDropdown" aria-haspopup="true" aria-expanded="false"><span class="question-mark">?</span></a>
                        <div class="dropdown-menu" aria-labelledby="helpDropdown">
                            <a href="Groups_Walkthrough.jpg" class="dropdown-item" data-image="Groups_Walkthrough.jpg">1 - Create or add a group with +.</a>
                            <a href="Group_Select.jpg" class="dropdown-item sub-item" data-image="Group_Select.jpg">a - Select a group.</a>
                            <a href="Students_Walkthrough.jpg" class="dropdown-item" data-image="Students_Walkthrough.jpg">2 - Create or add students with +. </a>
                            <a href="Students_Select.jpg" class="dropdown-item sub-item" data-image="Students_Select.jpg">a - Select a student.</a>
                            <a href="Goal_Create_Walkthrough.jpg" class="dropdown-item" data-image="Goal_Create_Walkthrough.jpg">3 - Create or add goals with +.</a>
                            <a href="Rubric_Select.jpg" class="dropdown-item sub-item" data-image="Rubric_Select.jpg">a - Select a rubric.</a>
                        </div>
                    </li>

                    <li>
                        <div class="school-selector">
                            <label for="school-select">Select School:</label>
                            <select id="school-select">
                                <?php foreach ($schools as $school): ?>
                                    <option value="<?= htmlspecialchars($school['school_id']) ?>" <?= $school['school_id'] == $_SESSION['school_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($school['SchoolName']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </li>

                    <li class="luxbar-item">
                        <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                            <a href="manage.php" class="nav-link">Manage</a>
                        <?php endif; ?>
                    </li>
                    <li class="luxbar-item"><a href="mailto:dan@iepreport.com">Support</a></li>
                    <li class="luxbar-item"><a href="students.php">Home</a></li>
                    <li class="luxbar-item"><a href="./users/logout.php">Logout</a></li>

                </ul>
            </div>
        </header>

        <main class="content-students">
            <input type="hidden" id="selected-student-id" value="">

            <section class="box create-group">
                <h2>Groups <button class="add-group-btn" onclick="showAddGroupModal()">+</button></h2>
                <div id="group-list">
                    <ul>
                        <?php foreach ($groups as $group): ?>
                            <li data-group-id="<?= htmlspecialchars($group['group_id']) ?>" data-group-name="<?= htmlspecialchars($group['group_name']) ?>">
                                <?= htmlspecialchars($group['group_name']) ?>
                                <button class="options-btn" onclick="showGroupOptions(event, '<?= htmlspecialchars($group['group_id']) ?>', '<?= htmlspecialchars(addslashes($group['group_name'])) ?>')">Options</button>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </section>

            <section class="box students-list">
                <h2>Students <button class="add-student-btn">+</button></h2>
                <div class="message" id="students-message">Please use groups to see students.</div>
                <ul id="student-list" style="display: none;">
                    <?php foreach ($allStudents as $student): ?>
                        <li data-student-id="<?= htmlspecialchars($student['student_id']) ?>"><?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?></li>
                    <?php endforeach; ?>
                </ul>
            </section>

            <section class="box existing-groups">
                <h2>Goals <button class="add-goal-btn" onclick="showAddGoalModal()">+</button></h2>
                <div class="message" id="goals-message">Click a student to see their goals.</div>
                <div id="goal-list" style="display: none;">
                    <!-- Goals will be loaded here and grouped by metadata_id -->
                </div>
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

   <!-- Place the Edit Group button here, outside the modal -->
   <!-- <button class="edit-group-btn" onclick="showEditGroupModal()">Edit Group</button>-->

<!-- Add Student Modal -->
<div id="add-student-modal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="hideAddStudentModal()">&times;</span>
        <h2>Assign Students to Group</h2>
        <div style="margin-top: 20px;">
            <form id="assign-students-form" onsubmit="assignStudentsToGroup(event)">
                <div style="display: flex; align-items: center;">
                    <div style="margin-right: 10px;">
                        <select name="student_id" class="select2" style="width: 200px;" data-placeholder="Student name here" multiple>
                            <option></option>
                            <!-- Options will be dynamically populated -->
                        </select>
                    </div>
                    <button type="submit" name="assign_to_group">Assign to Group</button>
                </div>
            </form>
        </div>
        <h2>Remove Students from Group</h2>
        <div id="group-students-list-add">
            <!-- Students will be loaded here dynamically -->
        </div>
        <h2>Add New Student</h2>
        <form id="add-student-form" onsubmit="addStudent(event)">
            <div class="form-group">
                <label for="first-name">First Name:</label>
                <input type="text" id="first-name" name="first_name" required>
            </div>
            <div class="form-group">
                <label for="last-name">Last Name:</label>
                <input type="text" id="last-name" name="last_name" required>
            </div>
            <div class="form-group">
                <label for="date-of-birth">Date of Birth:</label>
                <input type="date" id="date-of-birth" name="date_of_birth" required>
            </div>
            <div class="form-group">
                <label for="grade-level">Grade Level:</label>
                <input type="text" id="grade-level" name="grade_level" required>
            </div>
            <button type="submit">Add Student</button>
                    <!-- Message area for notifications -->
            <div id="student-add-message" class="alert" style="display: none;"></div>
        </form>
    </div>
</div>

<!-- Add Goal Modal -->
<div id="add-goal-modal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="hideAddGoalModal()">&times;</span>
        <h2>Add New Goal</h2>
        <form id="add-goal-form" onsubmit="addGoal(event)">
            <div class="selector-area">
                <div id="columnSelectorTitle" class="selector-title">Goal Rubric Options:</div>
                <div id="metadataOptionSelector" class="checkbox-container-students">
                    <div class="selector-item" data-option="template" onclick="selectOption('template')">Template Rubric</div>
                    <div class="selector-item" data-option="existing" onclick="selectOption('existing')">Previously Used Rubric</div>
                </div>
            </div>

            <div id="templateDropdown" class="form-group" style="display: none;">
                <label for="template-metadata-select"><strong>Choose a template rubric:</strong></label>
                <select id="template-metadata-select" name="template_id" onchange="showColumnNames('template')">
                    <option value="" disabled selected>Select one</option>
                </select>
            </div>

            <div id="existingDropdown" class="form-group">
                <label for="existing-metadata-select"><strong>Choose a template rubric:</strong></label>
                <select id="existing-metadata-select" name="existing_category_id" onchange="showColumnNames('existing')">
                    <option value="" disabled selected>Select one</option>
                </select>
            </div>

            <div id="columnNamesDisplay" style="display: none; margin-top: 10px;">
                <h3>Starting column names for rubric:</h3>
                <ul id="columnNamesList"></ul>
            </div>

            <div class="form-group">
                <label for="goal-description">Goal Description:</label>
                <div id="goal-description" style="height: 200px;"></div>
            </div>

            <div class="form-group">
                <label for="goal-date">Goal Date:</label>
                <input type="date" id="goal-date" name="goal_date" required>
            </div>
            <button type="submit">Add Goal</button>
        </form>
    </div>
</div>

<!-- Group Options -->
<div id="group-options" class="group-options">
    <button onclick="editGroup()">Edit Group</button>
</div>

<!-- Edit Group Modal -->
<div id="edit-group-modal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="hideEditGroupModal()">&times;</span>
        <h2>Edit Group</h2>
        <form id="edit-group-form" onsubmit="updateGroup(event)">
            <input type="hidden" id="edit-group-id">
            <div class="form-group">
                <label for="edit-group-name">Group Name:</label>
                <input type="text" id="edit-group-name" name="group_name" required>
            </div>
            <button class="save-btn" type="submit">Save Changes</button>
        </form>
        <button class="delete-btn" onclick="deleteGroup()">Delete Group</button>
        <div id="group-students-list-edit">
            <!-- Students will be loaded here dynamically -->
        </div>
        <h2>Share Group</h2>
        <form id="share-group-form" onsubmit="shareGroup(event)">
            <input type="hidden" id="share-group-id">
            <div class="form-group">
                <select id="share-teacher-id" name="shared_teacher_id">
                    <option value="">Select staff here</option>
                    <?php foreach ($teachers as $teacher): ?>
                        <option value="<?= htmlspecialchars($teacher['teacher_id']) ?>">
                            <?= htmlspecialchars($teacher['fname'] . ' ' . $teacher['lname']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit">Share</button>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js"></script>

<script>

let quillInstances = {}; // Initialize variables globally

document.addEventListener('DOMContentLoaded', function() {
    loadGroups();
    loadStaff();
    lightbox.init();

    window.showAddGoalModal = showAddGoalModal;
    window.hideAddGoalModal = hideAddGoalModal;

    document.querySelector('.add-goal-btn').addEventListener('click', showAddGoalModal);
    document.querySelector('.add-group-btn').addEventListener('click', showAddGroupModal);

    window.hideAddGroupModal = hideAddGroupModal;
    window.hideAddStudentModal = hideAddStudentModal;

    document.addEventListener('click', function(event) {
        const optionsMenu = document.getElementById('group-options');
        if (optionsMenu && !optionsMenu.contains(event.target)) {
            optionsMenu.style.display = 'none';
        }
    });

    $('.select2').select2();

    const goalList = document.getElementById('goal-list');
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.addedNodes.length) {
                populateStudentsAndGoals();
            }
        });
    });
    observer.observe(goalList, { childList: true, subtree: true });

    populateStudentsAndGoals();

    const metadataOptionSelector = document.getElementById('metadataOptionSelector');
    metadataOptionSelector.addEventListener('click', function(event) {
        if (event.target.classList.contains('selector-item')) {
            const items = metadataOptionSelector.querySelectorAll('.selector-item');
            items.forEach(item => item.classList.remove('selected'));
            event.target.classList.add('selected');

            const selectedOption = event.target.getAttribute('data-option');
            selectOption(selectedOption);
        }
    });

    const schoolSelect = document.getElementById('school-select');
    let previousSchoolId = schoolSelect.value;

    schoolSelect.addEventListener('change', function() {
        const selectedSchoolId = this.value;
        fetch('./users/update_school_session.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `school_id=${encodeURIComponent(selectedSchoolId)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (!data.approved) {
                    alert("You are not approved for the selected school.");
                    schoolSelect.value = previousSchoolId;
                } else {
                    previousSchoolId = selectedSchoolId;
                    location.reload(); // Reload the page to reflect changes
                }
            } else {
                console.error('Error updating school:', data.message);
                schoolSelect.value = previousSchoolId;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            schoolSelect.value = previousSchoolId;
        });
    });
});

document.querySelector('.add-student-btn').addEventListener('click', function() {
    const selectedGroup = document.querySelector('.selected-group');
    if (selectedGroup) {
        const groupId = selectedGroup.getAttribute('data-group-id');
        if (groupId) {
            loadStudentsForGroupAssignment(groupId);
            showAddStudentModal(groupId);
        } else {
            console.error('Group ID is not defined.');
        }
    } else {
        console.error('No group is selected.');
    }
});

document.querySelectorAll('.dropdown-item').forEach(item => {
    let timer;
    item.addEventListener('mouseenter', function(event) {
        const imageUrl = this.getAttribute('data-image');
        timer = setTimeout(() => {
            const preview = document.createElement('img');
            preview.src = imageUrl;
            preview.className = 'image-preview';
            document.body.appendChild(preview);
            preview.style.display = 'block';
            preview.style.bottom = '20px'; // 20px from the bottom
            preview.style.left = '20px'; // 20px from the left
        }, 300); // Delay of 300 milliseconds
    });

    item.addEventListener('mouseleave', function() {
        clearTimeout(timer);
        const preview = document.querySelector('.image-preview');
        if (preview) {
            preview.remove();
        }
    });

    // Prevent the default hover action if the user is clicking
    item.addEventListener('click', function(event) {
        event.preventDefault(); // This stops the default navigation when clicking
        window.open(this.href, '_blank'); // Manually open the link in a new tab
    });
});

function populateStudentsAndGoals() {
    const studentList = document.getElementById('student-list');
    const studentsMessage = document.getElementById('students-message');
    if (studentList.children.length > 0) {
        studentsMessage.style.display = 'none';
        studentList.style.display = 'block';
    } else {
        studentsMessage.style.display = 'block';
        studentList.style.display = 'none';
    }

    const goalList = document.getElementById('goal-list');
    const goalsMessage = document.getElementById('goals-message');
    if (goalList.children.length > 0) {
        goalsMessage.style.display = 'none';
        goalList.style.display = 'block';
    } else {
        goalsMessage.style.display = 'block';
        goalList.style.display = 'none';
    }
}

function addGroup(event) {
    event.preventDefault();
    const groupName = document.getElementById('group-name').value;

    fetch('./users/add_group.php', {
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
            //console.log('Group added successfully:', data);
            // Google Analytics event tracking for successful goal addition
            gtag('event', 'add_group', {
                'event_category': 'Group Management',
                'event_label': 'Success',
                'value': 1
            });

            loadGroups();
            hideAddGroupModal();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('There was an error adding the group. Please try again.');

            // Google Analytics event tracking for failure goal addition
            gtag('event', 'add_group', {
                'event_category': 'Group Management',
                'event_label': 'Failure',
                'value': 0
            });
        });
}

function addStudent(event, groupId) {
    event.preventDefault();
    const firstName = document.getElementById('first-name').value.trim();
    const lastName = document.getElementById('last-name').value.trim();
    const dateOfBirth = document.getElementById('date-of-birth').value;
    const gradeLevel = document.getElementById('grade-level').value;
    const schoolId = <?= json_encode($_SESSION['school_id']); ?>;

    const messageDiv = document.getElementById('student-add-message'); // Reference the message div

    fetch(`./users/check_duplicate_student.php?first_name=${encodeURIComponent(firstName)}&last_name=${encodeURIComponent(lastName)}&school_id=${encodeURIComponent(schoolId)}`)
    .then(response => response.json())
    .then(data => {
        if (data.duplicate) {
            if (!confirm("A student with the same name already exists. Are you sure you want to add another?")) {
                return;
            }
        }
        fetch('./users/add_student.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `first_name=${encodeURIComponent(firstName)}&last_name=${encodeURIComponent(lastName)}&date_of_birth=${encodeURIComponent(dateOfBirth)}&grade_level=${encodeURIComponent(gradeLevel)}&school_id=${encodeURIComponent(schoolId)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Google Analytics event tracking for successful student addition
                gtag('event', 'add_new_student', {
                    'event_category': 'Student Management',
                    'event_label': 'Success',
                    'value': 1
                });

                const studentSelect = document.querySelector('[name="student_id"]');
                const option = document.createElement('option');
                option.value = data.student_id;
                option.textContent = `${firstName} ${lastName}`;
                studentSelect.appendChild(option);

                $('.select2').select2(); // Reinitialize select2
                $('.select2').trigger('change'); // Update UI

                messageDiv.textContent = 'Student added successfully!';
                messageDiv.className = 'alert success';
                messageDiv.style.display = 'block';

                loadStudentsForGroupAssignment(groupId);
            } else {
                // Google Analytics event tracking for failed student addition
                gtag('event', 'add_new_student', {
                    'event_category': 'Student Management',
                    'event_label': 'Failure',
                    'value': 0
                });

                messageDiv.textContent = 'Error adding student: ' + data.message;
                messageDiv.className = 'alert error';
                messageDiv.style.display = 'block';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            messageDiv.textContent = 'An error occurred while adding the student.';
            messageDiv.className = 'alert error';
            messageDiv.style.display = 'block';
        });
    })
    .catch(error => {
        console.error('Error checking for duplicates:', error);
        messageDiv.textContent = 'Failed to check for duplicate students.';
        messageDiv.className = 'alert error';
        messageDiv.style.display = 'block';
    });
}

function loadStaff() {
    fetch('users/fetch_staff.php')
        .then(response => response.json())
        .then(data => {
            const staffSelect = document.getElementById('share-teacher-id');
            staffSelect.innerHTML = '<option value="">Select staff here</option>'; // Clear previous options

            data.forEach(staff => {
                // Populate select options
                const option = document.createElement('option');
                option.value = staff.teacher_id;
                option.textContent = `${staff.fname} ${staff.lname}`; // Correctly concatenate first name and last name
                staffSelect.appendChild(option);
            });

            // Reinitialize the select2 element if needed
            if ($.fn.select2) {
                $('.select2').select2();
            } else {
                console.warn("Select2 is not defined, ensure Select2 library is correctly included.");
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('There was an error loading staff. Please try again.');
        });
}

// Function to show the modal
function showAddGroupModal() {
        const modal = document.getElementById('add-group-modal');
        if (modal) {
            modal.style.display = 'block';
        } else {
            console.error("Modal element not found");
        }
}

// Function to hide the modal
function hideAddGroupModal() {
    const modal = document.getElementById('add-group-modal');
        if (modal) {
            modal.style.display = 'none';
        }
}

function showAddStudentModal(groupId) {
    //console.log('showAddStudentModal called with groupId:', groupId); // Debug log
    document.getElementById('add-student-modal').style.display = 'block';

    // Load students for the selected group
    loadGroupStudents(groupId, 'group-students-list-add');
}

function hideAddStudentModal() {
    const modal = document.getElementById('add-student-modal');
    if (modal) {
        modal.style.display = 'none';
    }
}

function loadStudentsByGroup(groupId) {
    //console.log('Loading students for group by ID:', groupId); // Debug log

    fetch(`users/fetch_group_students.php?group_id=${encodeURIComponent(groupId)}`)
        .then(response => response.json())
        .then(data => {
            //console.log('Fetched group students:', data); // Debug log

            const studentList = document.getElementById('student-list');
            const studentsMessage = document.getElementById('students-message');
            
            if (data.error) {
                alert(data.error);
                return;
            }

            studentList.innerHTML = ''; // Clear the existing list

            if (data.length === 0) {
                studentsMessage.style.display = 'block';
                studentList.style.display = 'none';
                studentsMessage.innerHTML = 'No students in this group.';
                return;
            }

            // Sort students by last name within the group
            data.sort((a, b) => a.last_name.localeCompare(b.last_name));

            data.forEach(student => {
                const listItem = document.createElement('li');
                listItem.textContent = student.first_name + ' ' + student.last_name;
                listItem.setAttribute('data-student-id', student.student_id);
                listItem.addEventListener('click', () => selectStudent(listItem)); // Add event listener
                studentList.appendChild(listItem);
            });

            studentsMessage.style.display = 'none';
            studentList.style.display = 'block';
        })
        .catch(error => {
            console.error('Error fetching group students:', error);
            alert('There was an error loading the students for this group. Please try again.');
        });
}

function loadStudentsForGroupAssignment(groupId) {
    //console.log('Loading students for group assignment:', groupId); // Debug log

    fetch(`users/fetch_students.php?group_id=${encodeURIComponent(groupId)}`)
        .then(response => response.json())
        .then(data => {
            //console.log('Fetched students:', data); // Debug log

            if (Array.isArray(data)) {
                const studentSelect = document.querySelector('[name="student_id"]');
                studentSelect.innerHTML = '<option></option>'; // Clear previous options

                // Sort students by last name
                data.sort((a, b) => a.last_name.localeCompare(b.last_name));

                data.forEach(student => {
                    const option = document.createElement('option');
                    option.value = student.student_id_new;
                    option.textContent = student.first_name + ' ' + student.last_name;
                    studentSelect.appendChild(option);
                });

                // Reinitialize the select2 element
                $('.select2').select2();
            } else {
                console.error('Expected an array but received:', data);
                //alert('There was an error loading students. Please try again.');
            }
        })
        .catch(error => {
            console.error('Error fetching students for assignment:', error);
            //alert('There was an error loading students. Please try again.');
        });
}

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
                listItem.setAttribute('data-group-name', group.group_name); // Ensure this is set
                listItem.addEventListener('click', function() {
                    selectGroup(this);
                });

                const optionsBtn = document.createElement('button');
                optionsBtn.className = 'options-btn';
                optionsBtn.addEventListener('click', function(event) {
                    showGroupOptions(event, group.group_id, group.group_name); // Pass group name here
                });

                listItem.appendChild(optionsBtn);
                groupList.appendChild(listItem);
            });

            // Call populateStudentsAndGoals after updating the group list
            populateStudentsAndGoals();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('There was an error loading groups. Please try again.');
        });
}

function loadStudents() {
    fetch('users/fetch_students.php') // Adjust the endpoint if necessary
        .then(response => response.json())
        .then(data => {
            const studentList = document.getElementById('student-list');
            const studentsMessage = document.getElementById('students-message');
            const studentSelect = document.querySelector('[name="student_ids[]"]');
            studentList.innerHTML = '';
            studentSelect.innerHTML = '<option></option>'; // Clear previous options

            // Sort students by last name
            data.sort((a, b) => a.last_name.localeCompare(b.last_name));

            data.forEach(student => {
                // Populate student list
                const listItem = document.createElement('li');
                listItem.textContent = student.first_name + ' ' + student.last_name;
                listItem.setAttribute('data-student-id', student.student_id_new);
                listItem.addEventListener('click', function() {
                    selectStudent(this);
                });
                studentList.appendChild(listItem);

                // Populate select options
                const option = document.createElement('option');
                option.value = student.student_id_new;
                option.textContent = student.first_name + ' ' + student.last_name;
                studentSelect.appendChild(option);
            });

            // Reinitialize the select2 element
            $('.select2').select2();

            // Show or hide the student list and message based on content
            if (studentList.children.length > 0) {
                studentsMessage.style.display = 'none';
                studentList.style.display = 'block';
            } else {
                studentsMessage.style.display = 'block';
                studentList.style.display = 'none';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            //alert('There was an error loading students. Please try again.');
        });
}

function selectGroup(element) {
    const groupId = element.getAttribute('data-group-id');
    
    // Update the hidden input with the selected groupId
    document.getElementById('edit-group-id').value = groupId;

    // Load students for the selected group
    loadStudentsByGroup(groupId);

    const groupItems = document.getElementById('group-list').querySelectorAll('li');
    groupItems.forEach(group => group.classList.remove('selected-group'));
    element.classList.add('selected-group');
}

function selectStudent(element) {
    const studentId = element.getAttribute('data-student-id');
    document.getElementById('selected-student-id').value = studentId;

    loadGoals(studentId);

    const studentItems = document.getElementById('student-list').querySelectorAll('li');
    studentItems.forEach(student => student.classList.remove('selected-student'));
    element.classList.add('selected-student');
}

function showGroupOptions(event, groupId, groupName) {
    event.stopPropagation();
    const optionsMenu = document.getElementById('group-options');
    optionsMenu.style.display = 'block';
    optionsMenu.style.left = event.pageX + 'px';
    optionsMenu.style.top = event.pageY + 'px';
    optionsMenu.setAttribute('data-group-id', groupId);
    optionsMenu.setAttribute('data-group-name', groupName);

    // Set the group_id in the edit-group-id and share-group-id input fields
    document.getElementById('edit-group-id').value = groupId;
    document.getElementById('share-group-id').value = groupId;
}

function editGroup() {
    const groupId = document.getElementById('group-options').getAttribute('data-group-id');
    const groupName = document.getElementById('group-options').getAttribute('data-group-name');
    showEditGroupModal(groupId, groupName);
}

function updateGroup(event) {
    event.preventDefault();

    const groupId = document.getElementById('edit-group-id').value;
    const groupName = document.getElementById('edit-group-name').value;

    if (!groupId || !groupName) {
        alert("Group ID and name are required.");
        return;
    }

    fetch('./users/update_group.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `group_id=${encodeURIComponent(groupId)}&group_name=${encodeURIComponent(groupName)}`
    })
    .then(response => response.text())
    .then(data => {
        //console.log('Response:', data); // Debug log

        if (data.includes('Group updated successfully')) {
            alert('Group updated successfully.');
            hideEditGroupModal();
            loadGroups(); // Reload the groups to reflect the updated name
        } else {
            alert('Error updating group: ' + data);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('There was an error updating the group. Please try again.');
    });
}

function assignStudentsToGroup(event) {
    event.preventDefault();

    const groupId = document.getElementById('edit-group-id').value;
    const studentIds = Array.from(document.querySelector('[name="student_id"]').selectedOptions).map(option => option.value);

    if (!groupId || studentIds.length === 0) {
        alert("Please select a student and a group.");
        return;
    }

    fetch('./users/assign_students_to_group.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `group_id=${encodeURIComponent(groupId)}&student_ids=${encodeURIComponent(studentIds.join(','))}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === "success") {
            // Google Analytics event tracking for successful student assignment
            gtag('event', 'add_student_group', {
                'event_category': 'Student Management',
                'event_label': 'Success',
                'value': 1
            });

            loadGroupStudents(groupId, 'group-students-list-add'); // Refresh the student list in the modal
            loadStudentsByGroup(groupId); // Refresh the student list on the main page
            alert('Students successfully assigned to the group.');
        } else {
            // Google Analytics event tracking for failed student assignment
            gtag('event', 'add_student_group', {
                'event_category': 'Student Management',
                'event_label': 'Failure',
                'value': 0
            });

            alert(data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('There was an error assigning students to the group. Please try again.');

        // Google Analytics event tracking for errors during student assignment
        gtag('event', 'add_student_group', {
            'event_category': 'Student Management',
            'event_label': 'Error',
            'value': 0
        });
    });
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
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            // Google Analytics event tracking for failed group sharing
            gtag('event', 'share_group', {
                'event_category': 'Group Management',
                'event_label': 'Failure',
                'value': 0
            });

            alert(data.error);
        } else {
            // Google Analytics event tracking for successful group sharing
            gtag('event', 'share_group', {
                'event_category': 'Group Management',
                'event_label': 'Success',
                'value': 1
            });

            alert(data.message);
        }
        hideEditGroupModal();
        loadGroups();
    })
    .catch(error => {
        console.error('Error:', error);
        alert('There was an error sharing the group. Please try again.');

        // Google Analytics event tracking for errors not related to the data conditions (network errors, etc.)
        gtag('event', 'share_group', {
            'event_category': 'Group Management',
            'event_label': 'Error',
            'value': 0
        });
    });
}

function addGoal(event) {
    event.preventDefault();

    const studentId = document.getElementById('selected-student-id').value;
    const goalDescription = window.quillInstances['goal-description'].root.innerHTML;
    const goalDate = document.getElementById('goal-date').value;
    const metadataOptionElement = document.querySelector('#metadataOptionSelector .selector-item.selected');
    const metadataOption = metadataOptionElement ? metadataOptionElement.getAttribute('data-option') : null;
    const schoolId = <?= json_encode($_SESSION['school_id']); ?>;
    let metadataId = null;

    if (!studentId || !goalDescription || !goalDate || !metadataOption || !schoolId) {
        alert('Missing required parameters.');
        return;
    }

    if (metadataOption === 'existing') {
        metadataId = document.getElementById('existing-metadata-select').value;
        if (!metadataId) {
            alert('Please select an existing category.');
            return;
        }
    } else if (metadataOption === 'template') {
        metadataId = document.getElementById('template-metadata-select').value;
        if (!metadataId) {
            alert('Please select a category template.');
            return;
        }

        // If using a template, copy the template to create a new metadata entry
        fetch(`users/fetch_metadata_details.php?metadata_id=${metadataId}`)
            .then(response => response.json())
            .then(template => {
                if (template.error) {
                    throw new Error(template.error);
                }

                return fetch('./users/add_goal.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({
                        student_id: studentId,
                        goal_description: goalDescription,
                        goal_date: goalDate,
                        metadata_option: metadataOption,
                        template_id: metadataId,
                        school_id: schoolId
                    })
                });
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    throw new Error(data.error);
                }

                // Google Analytics event tracking for successful goal addition
                gtag('event', 'add_goal', {
                    'event_category': 'Goal Management',
                    'event_label': 'Success',
                    'value': 1
                });

                alert('Goal added successfully!');
                hideAddGoalModal();
                loadGoals(studentId); // Refresh the goals list
            })
            .catch(error => {
                console.error('Error adding goal:', error);
                alert('Error adding goal: ' + error.message);

                // Google Analytics event tracking for failed goal addition
                gtag('event', 'add_goal', {
                    'event_category': 'Goal Management',
                    'event_label': 'Failure',
                    'value': 0
                });
            });

        return;
    } else {
        alert('Invalid metadata option.');
        return;
    }

    fetch('./users/add_goal.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: new URLSearchParams({
            student_id: studentId,
            goal_description: goalDescription,
            goal_date: goalDate,
            metadata_option: metadataOption,
            existing_category_id: metadataId,
            school_id: schoolId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            throw new Error(data.error);
        }

        // Google Analytics event tracking for successful goal addition
        gtag('event', 'add_goal', {
            'event_category': 'Goal Management',
            'event_label': 'Success',
            'value': 1
        });

        alert('Goal added successfully!');
        hideAddGoalModal();
        loadGoals(studentId); // Refresh the goals list
    })
    .catch(error => {
        console.error('Error adding goal:', error);
        alert('Error adding goal: ' + error.message);

        // Google Analytics event tracking for failed goal addition
        gtag('event', 'add_goal', {
            'event_category': 'Goal Management',
            'event_label': 'Failure',
            'value': 0
        });
    });
}

function showEditGroupModal(groupId, groupName) {
    //console.log('showEditGroupModal called with groupId:', groupId, 'and groupName:', groupName); // Debug log
    document.getElementById('edit-group-id').value = groupId;
    document.getElementById('edit-group-name').value = groupName || '';
    document.getElementById('edit-group-modal').style.display = 'block';

    // Load students for the selected group
    //loadGroupStudents(groupId, 'group-students-list-edit');
}

function hideEditGroupModal() {
    document.getElementById('edit-group-modal').style.display = 'none';
    resetStudentList();

    // Show the Edit Group button
    const editGroupButton = document.querySelector('.edit-group-btn');
    if (editGroupButton) {
        editGroupButton.style.display = 'inline-block';
    }
}

function loadGroupStudents(groupId, targetElementId = 'group-students-list-add') {
    //console.log('Loading students for group:', groupId); // Debug log

    fetch(`./users/fetch_group_students.php?group_id=${encodeURIComponent(groupId)}`)
        .then(response => response.json())
        .then(data => {
            //console.log('Fetched group students:', data); // Debug log

            const groupStudentsList = document.getElementById(targetElementId);
            if (!groupStudentsList) {
                console.error('Target element not found:', targetElementId);
                return;
            }

            groupStudentsList.innerHTML = '';

            if (data.error) {
                alert(data.error);
                return;
            }

            if (data.length === 0) {
                groupStudentsList.innerHTML = '<p>No students in this group.</p>';
                return;
            }

            // Sort students by last name
            data.sort((a, b) => a.last_name.localeCompare(b.last_name));

            data.forEach(student => {
                const studentItem = document.createElement('div');
                studentItem.style.display = 'flex';
                studentItem.style.alignItems = 'center';
                studentItem.style.marginBottom = '10px';

                const studentName = document.createElement('span');
                studentName.style.marginRight = '10px';
                studentName.textContent = student.name;

                const removeButton = document.createElement('button');
                removeButton.style.color = 'red';
                removeButton.style.background = 'none';
                removeButton.style.border = 'none';
                removeButton.style.cursor = 'pointer';
                removeButton.style.fontSize = '16px';
                removeButton.style.lineHeight = '1';
                removeButton.textContent = '×';
                removeButton.onclick = () => removeStudentFromGroup(student.student_id, groupId);

                studentItem.appendChild(studentName);
                studentItem.appendChild(removeButton);
                groupStudentsList.appendChild(studentItem);
            });

            //console.log('Updated DOM with new student list.');
        })
        .catch(error => {
            console.error('Error fetching group students:', error);
            alert('There was an error loading the students for this group. Please try again.');
        });
}

function removeStudentFromGroup(studentId, groupId) {
    if (!confirm('Are you sure you want to remove this student from the group?')) {
        return;
    }

    fetch('users/remove_student_from_group.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `student_id=${encodeURIComponent(studentId)}&group_id=${encodeURIComponent(groupId)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            // Google Analytics event tracking for successful student removal
            gtag('event', 'remove_student_group', {
                'event_category': 'Student Management',
                'event_label': 'Success',
                'value': 1
            });

            // Refresh student lists both in modal and main page
            loadGroupStudents(groupId, 'group-students-list-add'); // Refresh modal list
            loadStudentsByGroup(groupId); // Refresh main page list if applicable
        } else {
            alert('There was an error removing the student from the group. Please try again.');
            
            // Google Analytics event tracking for failed student removal
            gtag('event', 'remove_student_group', {
                'event_category': 'Student Management',
                'event_label': 'Failure',
                'value': 0
            });
        }
    })
    .catch(error => {
        console.error('Error removing student from group:', error);
        alert('There was an error removing the student from the group. Please try again.');

        // Google Analytics event tracking for network or processing errors
        gtag('event', 'remove_student_group', {
            'event_category': 'Student Management',
            'event_label': 'Error',
            'value': 0
        });
    });
}

function loadAllStudentsForAssignment(groupId) {
    fetch('users/fetch_students.php') // Adjust the endpoint if necessary
        .then(response => response.json())
        .then(data => {
            const studentSelect = document.querySelector('[name="student_id"]');
            studentSelect.innerHTML = '<option></option>'; // Clear previous options

            // Filter students who are not in the selected group
            const filteredStudents = data.filter(student => !student.groups.includes(groupId));

            filteredStudents.forEach(student => {
                const option = document.createElement('option');
                option.value = student.student_id_new;
                option.textContent = student.first_name + ' ' + student.last_name;
                studentSelect.appendChild(option);
            });

            // Reinitialize the select2 element
            $('.select2').select2();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('There was an error loading students. Please try again.');
        });
}

function deleteGroup() {
    const confirmDeletion = confirm('Are you sure you want to delete this group?');
    if (!confirmDeletion) {
        return;
    }

    const groupId = document.getElementById('edit-group-id').value;

    if (!groupId) {
        alert('Group ID is not defined.');
        return;
    }

    fetch('users/delete_group.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `group_id=${encodeURIComponent(groupId)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert(data.message);
            hideEditGroupModal();
            loadGroups();
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('There was an error deleting the group. Please try again.');
    });
}

function resetStudentList() {
    const studentList = document.getElementById('student-list');
    const selectedGroup = document.querySelector('.selected-group');

    if (selectedGroup) {
        const groupId = selectedGroup.getAttribute('data-group-id');
        loadStudentsByGroup(groupId);
    } else {
        studentList.innerHTML = '<p>Please select a group to view students.</p>';
    }
}

function loadMetadata() {
    //console.log('Loading existing categories...');
    const studentId = document.getElementById('selected-student-id').value;
    fetch(`users/fetch_metadata.php?student_id=${studentId}`)
        .then(response => response.json())
        .then(data => {
            //console.log('Fetched used metadata:', data);
            const metadataSelect = document.getElementById('existing-metadata-select');
            if (metadataSelect) {
                metadataSelect.innerHTML = '<option value="" disabled selected>Choose one</option>';

                data.forEach(metadata => {
                    //console.log(`Adding metadata to existing: ${metadata.category_name}`);
                    const option = document.createElement('option');
                    option.value = metadata.metadata_id;
                    option.textContent = metadata.category_name;
                    metadataSelect.appendChild(option);
                });
            } else {
                console.error('Metadata select element not found.');
            }
        })
        .catch(error => {
            console.error('Error loading metadata:', error);
            alert('There was an error loading metadata. Please try again.');
        });
}

// Add the loadGoals function definition somewhere in your script
function loadGoals(studentId) {
    fetch(`users/fetch_goals.php?student_id=${encodeURIComponent(studentId)}`)
        .then(response => response.text())
        .then(data => {
            try {
                const jsonData = JSON.parse(data.trim());
                if (jsonData.error) {
                    alert(jsonData.message);
                    return;
                }

                const goalList = document.getElementById('goal-list');
                goalList.innerHTML = '';

                const goalsByMetadata = jsonData.reduce((acc, goal) => {
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
                        if (!goal.archived) {
                            const listItem = document.createElement('div');
                            listItem.classList.add('goal-item');
                            listItem.innerHTML = `
                                <div class="goal-content" id="goal-content-${goal.goal_id}" ondblclick="editGoal(${goal.goal_id})">
                                    <div class="goal-text-container">
                                        <div class="goal-text" data-goal-id="${goal.goal_id}">${goal.goal_description}</div>
                                    </div>
                                    <button class="archive-btn" onclick="archiveGoal(${goal.goal_id})">Archive</button>
                                </div>
                                <div class="goal-edit" id="goal-edit-${goal.goal_id}" style="display: none;">
                                    <div id="editor-${goal.goal_id}" class="quill-editor" data-goal-id="${goal.goal_id}"></div>
                                    <button class="btn btn-primary save-btn" onclick="saveGoal(${goal.goal_id}, this)">Save</button>
                                    <button class="btn btn-secondary cancel-btn" onclick="cancelEdit(${goal.goal_id}, '${goal.goal_description}')">Cancel</button>
                                </div>
                                <div class="progress-reports">
                                    <strong>Progress Reports:</strong>
                                    <div class="thumbnails">
                                        ${goal.notes.map((note, index) => note.report_image ? `
                                            <div class="thumbnail-container">
                                                <a href="${note.report_image}" data-lightbox="goal-${goal.goal_id}" data-title="Report Image">
                                                    <img src="${note.report_image}" alt="Report Available" class="thumbnail">
                                                    <div class="thumbnail-overlay">${index + 1}</div>
                                                </a>
                                            </div>
                                        ` : '').join('')}
                                    </div>
                                </div>
                            `;

                            metadataContainer.appendChild(listItem);
                        }
                    });

                    goalList.appendChild(metadataContainer);
                }

                document.querySelectorAll('.quill-editor').forEach(editor => {
                    const goalId = editor.getAttribute('data-goal-id');
                    if (!window.quillInstances) {
                        window.quillInstances = {};
                    }
                    if (!window.quillInstances[goalId]) {
                        const quill = new Quill(editor, {
                            theme: 'snow',
                            readOnly: true,
                            modules: {
                                toolbar: [
                                    [{ 'header': '1'}, {'header': '2'}, { 'font': [] }],
                                    [{size: []}],
                                    ['bold', 'italic', 'underline', 'strike'],
                                    [{ 'color': [] }, { 'background': [] }],
                                    [{ 'script': 'sub'}, { 'script': 'super' }],
                                    ['blockquote', 'code-block'],
                                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                                    [{ 'indent': '-1'}, { 'indent': '+1' }, { 'align': [] }],
                                    ['link', 'image', 'video'],
                                    ['clean']  
                                ]
                            }
                        });
                        quill.root.innerHTML = document.querySelector(`.goal-text[data-goal-id="${goalId}"]`).innerHTML;
                        window.quillInstances[goalId] = quill;
                    }
                });
            } catch (error) {
                console.error('Error parsing JSON:', error);
                alert('Error processing the goals. Please try again.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('There was an error fetching goals. Please try again.');
        });
}

function showAddGoalModal() {
    const selectedStudent = document.querySelector('.selected-student');
    if (!selectedStudent) {
        alert('Please select a student first.');
        return;
    }
    const modal = document.getElementById('add-goal-modal');
    modal.style.display = 'block';

    // Reset dropdowns
    document.getElementById('templateDropdown').style.display = 'none';
    document.getElementById('existingDropdown').style.display = 'none';
    document.getElementById('columnNamesDisplay').style.display = 'none';

    // Ensure Quill is initialized for the 'goal-description' after the modal is displayed
    setTimeout(function() {
        if (!window.quillInstances) {
            window.quillInstances = {}; // Ensure the global object for Quill instances exists
        }
        if (!window.quillInstances['goal-description']) {
            window.quillInstances['goal-description'] = new Quill('#goal-description', {
                theme: 'snow',
                modules: {
                    toolbar: [
                        [{ 'header': '1'}, {'header': '2'}, { 'font': [] }],
                        [{size: []}],
                        ['bold', 'italic', 'underline', 'strike'],
                        [{ 'color': [] }, { 'background': [] }],
                        [{ 'script': 'sub'}, { 'script': 'super' }],
                        ['blockquote', 'code-block'],
                        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                        [{ 'indent': '-1'}, { 'indent': '+1' }, { 'align': [] }],
                        ['link', 'image', 'video'],
                        ['clean']  
                    ]
                }
            });
        } else {
            // If already initialized, just update the editor's content to empty or default
            window.quillInstances['goal-description'].setText('');
        }
    }, 0); // A minimal timeout to ensure the modal and its contents are fully visible
}

function hideAddGoalModal() {
    const modal = document.getElementById('add-goal-modal');
    modal.style.display = 'none';
}

function editGoal(goalId) {
    const quill = window.quillInstances[goalId];
    if (!quill) {
        console.error('Quill editor instance not found for goal ID:', goalId);
        return;
    }
    quill.enable(true);
    quill.root.innerHTML = document.querySelector(`.goal-text[data-goal-id="${goalId}"]`).innerHTML;
    document.getElementById(`goal-content-${goalId}`).style.display = 'none';
    document.getElementById(`goal-edit-${goalId}`).style.display = 'block';
}

function cancelEdit(goalId, originalContent) {
    const quill = window.quillInstances[goalId];
    quill.root.innerHTML = originalContent;
    quill.enable(false);
    document.getElementById(`goal-content-${goalId}`).style.display = 'block';
    document.getElementById(`goal-edit-${goalId}`).style.display = 'none';
}

function saveGoal(goalId, saveButton) {
    const quill = window.quillInstances[goalId];
    const updatedContent = quill.root.innerHTML;
    
    fetch('./users/update_goal.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            goal_id: goalId,
            new_text: updatedContent
        })
    }).then(response => response.json())
      .then(data => {
          if (data.success) {
              const goalItem = saveButton.closest('.goal-item');
              goalItem.querySelector('.goal-text').innerHTML = updatedContent;
              quill.enable(false);
              document.getElementById(`goal-content-${goalId}`).style.display = 'block';
              document.getElementById(`goal-edit-${goalId}`).style.display = 'none';
          } else {
              alert('Failed to save goal. Please try again.');
          }
      }).catch(error => {
          console.error('Error:', error);
          alert('An error occurred while saving the goal.');
      });
}

function archiveGoal(goalId) {
    if (!confirm('Are you sure you want to archive this goal?')) {
        return;
    }

    fetch('./users/archive_goal.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `goal_id=${encodeURIComponent(goalId)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert('Goal archived successfully.');
            loadGoals(document.getElementById('selected-student-id').value);
        } else {
            alert('Error archiving goal: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error archiving goal:', error);
        alert('Error archiving goal: ' + error.message);
    });
}

function selectOption(option) {
    const templateDropdown = document.getElementById('templateDropdown');
    const existingDropdown = document.getElementById('existingDropdown');

    if (!templateDropdown || !existingDropdown) {
        console.error('Dropdown elements not found.');
        return;
    }

    //console.log(`Option selected: ${option}`);
    if (option === 'template') {
        templateDropdown.style.display = 'block';
        existingDropdown.style.display = 'none';
        loadTemplates();
    } else if (option === 'existing') {
        templateDropdown.style.display = 'none';
        existingDropdown.style.display = 'block';
        loadMetadata();
    }
}

// Function to load metadata templates
function loadTemplates() {
    //console.log('Loading templates...');
    const studentId = document.getElementById('selected-student-id').value;
    fetch(`users/fetch_metadata_templates.php?student_id=${studentId}`)
        .then(response => response.json())
        .then(data => {
            //console.log('Fetched unused templates:', data);
            if (data.error) {
                throw new Error(data.error);
            }

            const templateSelect = document.getElementById('template-metadata-select');
            if (!templateSelect) {
                console.error('Template metadata select element not found.');
                return;
            }
            templateSelect.innerHTML = '<option value="" disabled selected>Choose one</option>';

            data.forEach(template => {
                if (template.category_name.includes('Template')) {
                    //console.log(`Adding template: ${template.category_name}`);
                    const option = document.createElement('option');
                    option.value = template.metadata_id;
                    option.textContent = template.category_name;
                    templateSelect.appendChild(option);
                }
            });
        })
        .catch(error => {
            console.error('Error loading metadata templates:', error);
        });
}

function loadExistingCategories() {
    const studentId = document.getElementById('selected-student-id').value;
    const schoolId = <?= json_encode($_SESSION['school_id']); ?>;

    fetch(`users/fetch_existing_categories.php?student_id=${studentId}&school_id=${schoolId}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                throw new Error(data.error);
            }

            const existingSelect = document.getElementById('existing-metadata-select');
            if (!existingSelect) {
                console.error('Existing metadata select element not found.');
                return;
            }
            existingSelect.innerHTML = '<option value="">Select a category to see column options</option>';

            data.forEach(category => {
                const option = document.createElement('option');
                option.value = category.metadata_id;
                option.textContent = category.category_name;
                existingSelect.appendChild(option);
            });
        })
        .catch(error => {
            console.error('Error loading existing categories:', error);
        });
}

function showColumnNames(type) {
    let selectedId;
    if (type === 'template') {
        selectedId = document.getElementById('template-metadata-select').value;
    } else if (type === 'existing') {
        selectedId = document.getElementById('existing-metadata-select').value;
    }

    if (!selectedId) {
        document.getElementById('columnNamesDisplay').style.display = 'none';
        return;
    }

    //console.log(`Showing column names for ${type} with ID: ${selectedId}`);
    fetch(`users/fetch_metadata_details.php?metadata_id=${selectedId}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                throw new Error(data.error);
            }

            const columnNamesList = document.getElementById('columnNamesList');
            if (!columnNamesList) {
                console.error('Column names list element not found.');
                return;
            }
            columnNamesList.innerHTML = '';

            for (let i = 1; i <= 10; i++) {
                const scoreName = data[`score${i}_name`];
                if (scoreName) {
                    //console.log(`Adding column name: ${scoreName}`);
                    const listItem = document.createElement('li');
                    listItem.textContent = scoreName;
                    columnNamesList.appendChild(listItem);
                }
            }

            document.getElementById('columnNamesDisplay').style.display = 'block';
        })
        .catch(error => {
            console.error('Error loading column names:', error);
        });
}
</script>
</body>
</html>
