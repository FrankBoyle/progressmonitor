<?php
session_start();
include('./users/auth_session.php');
include('./users/db.php');

// Enable PHP error logging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', 'error_log.log');  // Ensure this file is writable by the server

// Check if the connection is properly set
if (!$connection) {
    error_log("Database connection is not set.");
    die("Database connection is not set.");
}

error_log("Database connection is set.");

// Fetch necessary data
$schoolId = $_SESSION['school_id'];
$teacherId = $_SESSION['teacher_id'];

function fetchStudentsByTeacher($teacherId, $archived = false) {
    global $connection;
    $archivedValue = $archived ? 1 : 0;
    $stmt = $connection->prepare("SELECT * FROM Students_new WHERE school_id = (SELECT school_id FROM Teachers WHERE teacher_id = ?) AND archived = ?");
    $stmt->execute([$teacherId, $archivedValue]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function fetchAllRelevantGroups($teacherId) {
    global $connection;
    $stmt = $connection->prepare("
        SELECT g.*, (g.group_id = t.default_group_id) AS is_default 
        FROM Groups g
        LEFT JOIN Teachers t ON t.teacher_id = :teacherId
        WHERE g.teacher_id = :teacherId
        UNION
        SELECT g.*, (g.group_id = t.default_group_id) AS is_default
        FROM Groups g
        INNER JOIN SharedGroups sg ON g.group_id = sg.group_id
        LEFT JOIN Teachers t ON t.teacher_id = :teacherId
        WHERE sg.shared_teacher_id = :teacherId
    ");
    $stmt->bindParam(':teacherId', $teacherId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$students = fetchStudentsByTeacher($teacherId);
$groups = fetchAllRelevantGroups($teacherId);

// Handle group creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_group'])) {
    $groupName = $_POST['group_name'];
    try {
        $stmt = $connection->prepare("INSERT INTO Groups (group_name, school_id, teacher_id) VALUES (?, ?, ?)");
        $stmt->execute([$groupName, $schoolId, $teacherId]);
        echo "Group created successfully.";
    } catch (PDOException $e) {
        error_log($e->getMessage());
        echo "Error creating group: " . $e->getMessage();
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Layout</title>
    <link rel="stylesheet" href="styles.css">
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
                        <?php foreach ($groups as $group): ?>
                            <li><?php echo htmlspecialchars($group['group_name']); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </section>

            <section class="box students-list">
                <h3>Students</h3>
                <ul id="student-list">
                    <?php foreach ($students as $student): ?>
                        <li><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></li>
                    <?php endforeach; ?>
                </ul>
            </section>

            <!-- Second Row -->
            <section class="box existing-groups">
                <h3>Goals</h3>
                <ul>
                    <li>Goal 1</li>
                    <li>Goal 2</li>
                </ul>
            </section>

            <section class="box details">
                <h3>Details</h3>
                <ul>
                    <li>Detail 1</li>
                    <li>Detail 2</li>
                </ul>
            </section>
        </main>
    </div>

    <!-- Add Group Modal -->
    <div id="add-group-modal" style="display:none;">
        <div>
            <h2>Add New Group</h2>
            <form id="add-group-form" onsubmit="addGroup(event)">
                <label for="group-name">Group Name:</label>
                <input type="text" id="group-name" name="group_name" required>
                <button type="submit">Add Group</button>
                <button type="button" onclick="hideAddGroupModal()">Cancel</button>
            </form>
        </div>
    </div>

    <script>
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
                const groupList = document.getElementById('group-list').querySelector('ul');
                const newGroupItem = document.createElement('li');
                newGroupItem.textContent = groupName;
                groupList.appendChild(newGroupItem);
                hideAddGroupModal();
            })
            .catch(error => {
                console.error('Error:', error);
                alert('There was an error adding the group. Please try again.');
            });
        }
    </script>

</body>
</html>

