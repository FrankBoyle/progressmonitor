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
            <h2>Create Group <button class="add-group-btn">+</button></h2>
            </section>

            <section class="box students-list">
                <h2>Student</h2>
                <ul>
                    <li>Student 1</li>
                    <li>Student 2</li>
                </ul>
            </section>

            <!-- Second Row -->
            <section class="box existing-groups">
                <h2>Existing Group</h2>
                <ul>
                    <li>Group 1</li>
                    <li>Group 2</li>
                </ul>
            </section>

            <section class="box details">
                <h2>Details</h2>
                <ul>
                    <li>Detail 1</li>
                    <li>Detail 2</li>
                </ul>
            </section>
        </main>
    </div>

</body>
</html>
