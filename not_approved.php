<?php
session_start();
include('./users/auth_session.php');
include('./users/db.php');

// Ensure account_id is in session
$account_id = $_SESSION['account_id'];
$school_id = $_SESSION['school_id'];

// Fetch the schools associated with the logged-in user
$query = $connection->prepare("SELECT s.school_id, s.SchoolName FROM Schools s JOIN Teachers t ON s.school_id = t.school_id WHERE t.account_id = :account_id");
$query->bindParam("account_id", $account_id, PDO::PARAM_INT);
$query->execute();
$schools = $query->fetchAll(PDO::FETCH_ASSOC);

?>


<!DOCTYPE html>
<html>
<head>
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-9YXLSJ50NV"></script>
    <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());

    gtag('config', 'G-9YXLSJ50NV');
    </script>
    <title>Not Approved</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdn.rawgit.com/balzss/luxbar/ae5835e2/build/luxbar.min.css">


    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        h1 {
            color: #333;
        }
        p {
            color: #666;
        }
        .contact-admin {
            margin-top: 20px;
            font-size: 14px;
            color: #007BFF;
        }
    </style>
</head>
<body>
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

                    <li>
                        <div class="school-selector">
                            <label for="school-select">Select School:</label>
                            <select id="school-select">
                                <?php foreach ($schools as $school): ?>
                                    <option value="<?= htmlspecialchars($school['school_id']) ?>" <?= $school['school_id'] == $_SESSION['school_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($school['SchoolName']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </li>

                    <li class="luxbar-item"><a href="mailto:dan@iepreport.com">Support</a></li>
                    <li class="luxbar-item"><a href="students.php">Home</a></li>
                    <li class="luxbar-item"><a href="./users/logout.php">Logout</a></li>

                </ul>
            </div>
        </header>
    <div class="container">
        <h1>Account Not Approved</h1>
        <p>Your account needs to be approved by an administrator before you can access this area. Please contact your administrator for more information.</p>
        <p class="contact-admin">Contact your administrator. <!--<a href="mailto:admin@example.com">admin@example.com</a>--></p>
    </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const schoolSelect = document.getElementById('school-select');
    schoolSelect.addEventListener('change', function() {
        const selectedSchoolId = this.value;
        fetch('./users/update_school_session.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `school_id=${encodeURIComponent(selectedSchoolId)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (data.approved) {
                    location.reload();
                } else {
                    alert("You are not approved for the selected school.");
                }
            } else {
                console.error('Error updating school:', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    });
});


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
