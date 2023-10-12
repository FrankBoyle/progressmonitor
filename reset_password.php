<?php
// Include your script with database connection and logic here

// Check if token is set in the URL
if(isset($_GET['token'])) {
    $token = $_GET['token'];
    // You can optionally validate the token here or just let the user input the new password
    ?>
    <form action="forgot_password.php" method="post">
        <input type="hidden" name="token" value="<?php echo $token; ?>">
        <label for="newPassword">New Password:</label>
        <input type="password" name="newPassword" required>
        <input type="submit" name="reset_password" value="Reset Password">
    </form>
    <?php
} else {
    // Display the form to request the password reset email
    ?>
    <form action="forgot_password.php" method="post">
        <label for="email">Email:</label>
        <input type="email" name="email" required>
        <input type="submit" name="forgot_password" value="Request Password Reset">
    </form>
    <?php
}
