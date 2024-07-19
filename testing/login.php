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
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">

    <style>
        /* Combined CSS */
        .dashboard {
            display: flex;
            flex-direction: column;
            height: 100vh;
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 1rem; /* Adjusted padding to reduce height */
            background-color: #1A3E55; /* Very dark blue for more prominent navbar */
            color: white;
            position: fixed; /* Make the navbar fixed */
            width: 100%;
            top: 0;
            z-index: 1000; /* Ensure it stays above other content */
        }

        .center-content {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f5f5f5;
            padding-top: 4rem; /* Adjust for fixed header */
        }

        .login-box {
            background-color: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            width: 100%;
        }

        .login-box-msg {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: #333;
            text-align: center;
        }

        /* Additional Styles for Testimonials and Sample Reports */
        .landing-page {
            display: grid;
            grid-template-columns: 1fr 1fr;
            grid-template-rows: auto 1fr;
            height: calc(100vh - 4rem); /* Adjust for fixed header */
            padding-top: 4rem; /* Adjust for fixed header */
        }

        .additional-content {
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 20px;
            background-color: #e9ecef;
        }

        .testimonials, .sample-reports {
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <header class="dashboard-header">
            <div class="logo">
                <img src="IEPreport_logo.jpg" alt="Logo">
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

        <div class="landing-page">
            <div class="center-content">
                <div class="login-box">
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
                        <a href="register.php" class="text-center">Register a new membership</a>
                    </p>
                </div>
            </div>

            <div class="additional-content">
                <div class="testimonials">
                    <h2>Testimonials</h2>
                    <p>"This service has transformed our IEP reporting process!" - Educator A</p>
                    <p>"An invaluable tool for special education teachers." - Educator B</p>
                </div>
                <div class="sample-reports">
                    <h2>Sample Reports</h2>
                    <p>Check out some sample reports to see what our system can do.</p>
                    <ul>
                        <li><a href="sample_report1.pdf">Sample Report 1</a></li>
                        <li><a href="sample_report2.pdf">Sample Report 2</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
