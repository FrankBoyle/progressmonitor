<?php
include('./users/db.php');
include('./users/auth_session.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-LKFCCN4XXS"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'G-LKFCCN4XXS');
    </script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Layout</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
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
            <a href="./users/logout.php" class="nav-link">
                <i class="nav-icon"></i>
                <p>Sign Out</p>
            </a>
        </div>
    </header>

    <main class="content-students">
        <section class="box manage-section">
            <h2>Manage Dashboard</h2>
            <div class="card">
                <h3>Manage Users</h3>
                <div id="users-table-container">
                    <!-- Table will be dynamically populated here -->
                </div>
            </div>
            <div class="card">
                <h3>Other Management Features</h3>
                <p>Content for other management features goes here.</p>
            </div>
        </section>
    </main>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
<script src="scripts.js"></script> <!-- Include any additional JavaScript for the manage page -->
</body>
</html>

