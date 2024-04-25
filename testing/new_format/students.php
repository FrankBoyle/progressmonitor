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
            <nav class="header-icons">
                <span>Icon 1</span>
                <span>Icon 2</span>
                <span>Icon 3</span>
            </nav>
        </header>

        <!-- Main Content -->
        <main class="content">
            <!-- Create Group Box -->

            <!-- Existing Groups Section -->
            <section class="box existing-groups">
                <div class="section-header">
                    <h2>Existing Groups</h2>
                    <button class="add-group-btn">+</button>
                </div>
                <ul>
                    <li>Group 1</li>
                    <li>Group 2</li>
                    <!-- More groups... -->
                </ul>
            </section>

            <!-- Existing Groups List -->
            <section class="box existing-groups">
                <h2>Existing Group</h2>
                <ul>
                    <li>Group 1</li>
                    <li>Group 2</li>
                    <!-- ... more groups ... -->
                </ul>
            </section>

            <!-- Students List -->
            <section class="box students-list">
                <h2>Student</h2>
                <ul>
                    <li>Student 1</li>
                    <li>Student 2</li>
                    <!-- ... more students ... -->
                </ul>
            </section>

            <!-- Details Section -->
            <section class="box details">
                <h2>Details</h2>
                <ul>
                    <li>Detail 1</li>
                    <li>Detail 2</li>
                    <!-- ... more details ... -->
                </ul>
            </section>
        </main>
    </div>

</body>
</html>
