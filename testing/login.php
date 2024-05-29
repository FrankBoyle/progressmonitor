<?php
    include('./users/login_backend.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Layout</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">

    <style>
    </style>
</head>
<body>
    <div class="dashboard">
       <header class="dashboard-header">
          <div class="logo">
            <img src="bFactor_logo.png" alt="Logo">
          </div>
          <div class="header-icons">
            <span>Icon 1</span>
            <span>Icon 2</span>
            <span>Icon 3</span>
          </div>
        </header>

        <div class="center-content">
            <div class="login-box">
                <h1 class="login-box-msg">Sign in</h1>
                <form method="post" action="./users/login_backend.php" name="login">
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
    </div>
</body>
</html>