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
                        <li>Math</li>
                        <li>Boyle's Homeroom</li>
                    </ul>
                </div>
            </section>

            <section class="box students-list">
                <h3>Students</h3>
                <ul id="student-list">
                    <li>Ryan Amole</li>
                    <li>Jayla Brazzle</li>
                    <!-- Other students here -->
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

