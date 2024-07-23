<?php
include('./users/login_backend.php');
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet" />
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
                            <a href="Register_How_To.jpg" class="dropdown-item" data-image="Register_How_To.jpg">You have to register an account.</a>
                            <a href="Password_Reset_Walkthrough.jpg" class="dropdown-item" data-image="Password_Reset_Walkthrough_How_To.jpg">Reset your password./a>
                        </div>
                    </li>

                    <li class="luxbar-item"><a href="mailto:dan@iepreport.com">Support</a></li>
                    <li class="luxbar-item"><a href="students.php">Home</a></li>
                    <li class="luxbar-item"><a href="./users/logout.php">Logout</a></li>

                </ul>
            </div>
        </header>

        <div class="center-content">
            <div class="grid-container">
                <div class="grid-item login-box">
                    <h1 class="login-box-msg">Sign in</h1>
                    <form method="post" action="users/login_backend.php" name="login">
                        <div style="position: relative;">
                            <input type="text" class="form-control" name="email" placeholder="E-mail" required>
                            <span class="fas fa-envelope"></span>
                        </div>
                        <div style="position: relative;">
                            <input type="password" class="form-control" name="password" placeholder="Password" required>
                            <span class="fas fa-lock"></span>
                        </div>
                        <div class="remember-me">
                            <input type="checkbox" id="remember" name="remember">
                            <label for="remember">Remember Me</label>
                        </div>
                        <button type="submit" value="login" name="login" class="btn btn-primary">Sign In</button>
                    </form>
                    <div class="forgot-password">
                        <p class="mb-1">
                            <?php
                                if (isset($_GET['reset']) && $_GET['reset'] == 1) {
                                    echo '<p class="info">If this email exists in our system, a reset link has been sent. Please check your inbox (and spam folder).</p>';
                                }
                            ?>
                            <form action="forgot_password.php" method="post">
                                <label for="email">Email:</label>
                                <input type="email" class="form-control" name="email" required>
                                <input type="submit" class="btn btn-primary" name="forgot_password" value="Request Password Reset">
                            </form>
                        </p>
                    </div>
                    <p class="mb-0">
                        <a href="register.php" class="text-center"><h2>Register a new membership</h2></a>
                    </p>
                </div>
                <div class="grid-item sample-reports">
                    <h3>How do I use IEP Report?</h3>
                    <img src="Instructions.jpg" alt="1)	Create a group with +.
                        a.	Select a group.
                        2)	Add students to school and/or groups with +. 
                        a.	Select a student.
                        3)	Add Goals with +.
                        a.	Select a goal. 
                        ">
                </div>
                <div class="grid-item sample-reports">
                    <h3>Sample Reports</h3>
                    <img src="sample_report1.png" alt="Sample Report 1">
                </div>
                <div class="grid-item about-us">
                    <h3>About Us</h3>
                    <p>
                        <strong>IEP report</strong> was created by teachers, for teachers. As two Special Ed Teachers with a combined 40 years of experience, we saw a need for a better way to progress monitor. We understand the struggle to find smarter, more efficient methods. We've tried various tools, adapted hand-me-down spreadsheets, and dealt with inconsistent data, all of which were unsustainable, even for tech-savvy educators like us. Recognizing that not all teachers are tech-savvy, we created IEPreport.com to offer a user-friendly solution for reporting accurate data.
                    </p>
                    <p>
                        Our mission is to provide a tool that anyone can use to enter goals—whether in Math, ELA, Behavior, or any other area—and easily generate reports with beautiful, accurate graphs. No matter your level of tech proficiency, our tool simplifies the process while ensuring high-quality data representation. Check us out and see how our tool, while appearing simple, is powered by robust technology to meet your needs.
                    </p>
                </div>
                <div class="grid-item testimonials">
                    <h3>Testimonials</h3>
                    <p>"It makes progress monitoring so much easier. It gives me the graphs and statistics I need to help make decisions for my IEP Goals." - <strong>Joe Dattilo</strong></p>

                    <p>"I use it for all my own progress reporting for new IEPs, IEP revisions, and quarterly reporting." - <strong>Fran Boyle</strong></p>
                </div>
                <div class="grid-item sample-reports">
                    <img src="sample_report3.png" alt="Sample Report 3">
                </div>
            </div>
        </div>
    </div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
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
