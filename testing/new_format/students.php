<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Page</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

    <nav class="navbar">
        <!-- Navigation content goes here -->
        <div>Logo</div>
        <div>User Icon</div>
    </nav>

    <header class="header">
        <!-- Main header content like page title -->
        <h1>Dashboard</h1>
    </header>

    <div class="main-content">
        
        <aside class="sidebar">
            <!-- Sidebar content like navigation links or user info -->
            <h2>Existing Group</h2>
            <div class="existing-group">Group Name</div>
            <div class="student-list">Student List</div>
        </aside>

        <section class="group-section">
            <!-- Group content like details and buttons -->
            <h2>Create Group</h2>
            <button class="button">Create Group</button>

            <!-- More sections as needed -->
            <div class="student-list">
                <!-- Repeat for each student -->
                <div class="student-item">
                    <div>Student Avatar</div>
                    <div>Student Name</div>
                </div>
                <!-- ... other students ... -->
            </div>
        </section>
    </div>

    <script src="script.js"></script>
</body>
</html>
