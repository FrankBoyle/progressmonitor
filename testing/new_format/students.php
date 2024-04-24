<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

    <div class="dashboard-container">

        <nav class="navbar">
            <!-- Add your icons here -->
            <div class="nav-item">🏠 Home</div>
            <div class="nav-item right">8:00</div>
        </nav>

        <div class="main-content">

            <section class="create-group-section">
                <div class="create-group-header">
                    <div class="header-content">
                        <h2>Create Group</h2>
                        <p>Some description if needed</p>
                        <button class="button-create-group">CREATE GROUP</button>
                    </div>
                </div>
            </section>

            <section class="existing-group-section">
                <h2>Existing Group</h2>
                <div class="group-name">Group 1</div>
                <div class="student-list-title">Student List</div>
            </section>

            <section class="student-section">
                <h2>Students</h2>
                <!-- This would be repeated for each student, potentially generated via JS -->
                <div class="student-card">
                    <div class="student-avatar">👤</div>
                    <div class="student-info">
                        <div class="student-name">Student Name</div>
                        <div class="student-details">More info</div>
                    </div>
                </div>
            </section>

        </div>

    </div>

</body>
</html>


