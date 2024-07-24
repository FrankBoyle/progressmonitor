<?php
session_start();
include('./users/auth_session.php');
include('./users/db.php');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$school_id = $_SESSION['school_id'];

if ($school_id) {
    $stmt = $connection->prepare("SELECT school_uuid FROM Schools WHERE school_id = :school_id");
    if (!$stmt) {
        die("PDO prepare failed: " . $connection->errorInfo());
    }

    $stmt->bindParam(':school_id', $school_id, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        echo "Fetched UUID: " . $result['school_uuid'];  // Debug: Output fetched UUID
    } else {
        echo "No data found for the given school ID.";
    }
    $stmt->close();
} else {
    echo "School ID is not set or invalid.";
}

// Debugging: Output session variables
//echo '<pre>';
//echo 'Session Variables:';
//print_r($_SESSION);
//echo '</pre>';
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
</script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Dashboard</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://unpkg.com/tabulator-tables@6.2.1/dist/css/tabulator.min.css" rel="stylesheet">
    <script type="text/javascript" src="https://unpkg.com/tabulator-tables@6.2.1/dist/js/tabulator.min.js"></script>
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
        <!-- Existing Users Management Section -->
        <section class="box manage-section">
            <h2>Manage Users <button class="toggle-btn" onclick="toggleSection('users-section')">+</button></h2>
            <h2>Your School ID - People can register with this ID to join your school.</h2>
            <input type="text" value="<?php echo htmlspecialchars($school_uuid); ?>" readonly>
            <div id="users-section" class="collapsible-content">
                <div id="approved-users-table-container"></div>
                <div id="waiting-approval-table-container"></div>
            </div>
        </section>

        <!-- New Students Management Section -->
        <section class="box manage-section">
            <h2>Manage Students <button class="toggle-btn" onclick="toggleSection('students-section')">+</button></h2>
            <div id="students-section" class="collapsible-content">
                <div id="active-students-table-container"></div>
                <div id="archived-students-table-container"></div>
            </div>
        </section>

        <section class="box manage-section">
            <h2>Assign Users to School <button class="toggle-btn" onclick="toggleSection('assign-users-section')">+</button></h2>
            <div id="assign-users-section" class="collapsible-content">
                <div>
                    <label for="program-users-search">Search Users by Program:</label>
                    <input type="text" id="program-users-search" placeholder="Enter user's name or email">
                    <button onclick="searchProgramUsers()">Search</button>
                </div>
                <div id="program-users-table-container"></div>
            </div>
        </section>

    </main>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
<script src="https://unpkg.com/tabulator-tables@5.2.7/dist/js/tabulator.min.js"></script>
<script src="scripts.js"></script>
<script>
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

</script>
</body>
</html>

