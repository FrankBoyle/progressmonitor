<?php
include('./users/db.php');

// Check if token is set in the URL
if (isset($_GET['token'])) {
    $token = $_GET['token'];
    ?>
    <form action="reset_password.php" method="post">
        <input type="hidden" name="token" value="<?php echo $token; ?>">
        <label for="newPassword">New Password:</label>
        <input type="password" name="newPassword" required>
        <input type="submit" name="reset_password" value="Reset Password">
    </form>
    <?php
} else {
    echo "<p>Invalid token or token not provided.</p>";
}

if (isset($_POST['reset_password'])) {
    $token = $_POST['token'];
    $newPassword = $_POST['newPassword'];
    $password_hash = password_hash($newPassword, PASSWORD_BCRYPT);

    // Check token validity
    $query = $connection->prepare("SELECT * FROM accounts WHERE reset_token=:token AND reset_token_expiry > NOW()");
    $query->bindParam("token", $token, PDO::PARAM_STR);
    $query->execute();

    if ($query->rowCount() > 0) {
        // Update the password and clear the reset token
        $query = $connection->prepare("UPDATE accounts SET password=:password_hash, reset_token=NULL, reset_token_expiry=NULL WHERE reset_token=:token");
        $query->bindParam("password_hash", $password_hash, PDO::PARAM_STR);
        $query->bindParam("token", $token, PDO::PARAM_STR);
        $query->execute();

        echo '<p class="success">Password reset successful!</p>';
    } else {
        echo '<p class="error">Invalid or expired token!</p>';
    }
}
?>
