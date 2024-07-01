<?php
include('./users/db.php');
include('./users/auth_session.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Dashboard</title>
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
            <div id="manage-content">
                <h3>Manage Users</h3>
                <div id="approved-users-table-container"></div>
                <h3>Users Waiting for Approval</h3>
                <div id="unapproved-users-table-container"></div>
            </div>
        </section>
    </main>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
<script src="scripts.js"></script> <!-- Include the updated JavaScript for the manage page -->
</body>
</html>


