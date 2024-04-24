<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Page</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

    <div class="dashboard-container">

        <nav class="navbar">
            <div class="logo">Logo</div>
            <div class="user-icon">User Icon</div>
        </nav>

        <div class="dashboard-header">
            <h1>Dashboard</h1>
        </div>

        <div class="main-content">

            <aside class="sidebar">
                <h2>Existing Group</h2>
                <div class="group-name">Group Name</div>
                <div class="student-list-title">Student List</div>
            </aside>

            <section class="group-section">
                <div class="create-group-header">
                    <h2>Create Group</h2>
                    <button class="button-create-group">Create Group</button>
                </div>
                <div class="student-list">
                    <!-- Repeat this div for each student -->
                    <div class="student-item">
                        <img src="student-avatar.png" alt="Student Avatar">
                        <div class="student-name">Student Name</div>
                    </div>
                </div>
            </section>

        </div>

    </div>

</body>
</html>

