<?php
include('./users/db.php');

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reset_password'])) {
        $newPassword = $_POST['newPassword'];
        // Assume a function resetPassword exists to handle password reset in db.php
        $resetSuccess = resetPassword($token, $newPassword);
        if ($resetSuccess) {
            echo "<script>
                    alert('Password reset successfully. Redirecting to login page...');
                    window.location.href = 'login.php';
                  </script>";
        } else {
            echo "<p>Failed to reset password. Please try again.</p>";
        }
    } else {
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Reset Password</title>
            <link rel="icon" type="image/x-icon" href="IEPreport_logo.jpg" />
            <link rel="stylesheet" href="styles.css">
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
                                    <a href="Reset_Password_Walkthrough.jpg" class="dropdown-item" data-image="Reset_Password_Walkthrough.jpg">Reset your password.</a>
                                </div>
                            </li>

                            <li class="luxbar-item"><a href="mailto:dan@iepreport.com">Support</a></li>
                            <li class="luxbar-item"><a href="students.php">Home</a></li>
                            <li class="luxbar-item"><a href="./users/logout.php">Logout</a></li>

                        </ul>
                    </div>
                </header>

                <div class="center-content">
                    <div class="login-box">
                        <h1 class="login-box-msg">Reset Password</h1>
                        <form action="forgot_password.php?token=<?php echo $token; ?>" method="post">
                            <input type="hidden" name="token" value="<?php echo $token; ?>">
                            <div style="position: relative;">
                                <label for="newPassword">New Password:</label>
                                <input type="password" class="form-control" name="newPassword" required>
                                <span class="fas fa-lock"></span>
                            </div>
                            <button type="submit" name="reset_password" class="btn btn-primary">Reset Password</button>
                        </form>
                    </div>
                </div>
            </div>
        </body>
        </html>
        <?php
    }
} else {
    echo "<p>Invalid token or token not provided.</p>";
}
?>
