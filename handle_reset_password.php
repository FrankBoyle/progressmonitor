<?php
include('./users/db.php');

// Error Reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (isset($_POST['reset_password'])) {
    $token = $_POST['token'];
    $newPassword = $_POST['newPassword'];

    // Validate the token and reset the password
    $query = $connection->prepare("SELECT * FROM accounts WHERE reset_token=:token AND reset_token_expiry > NOW()");
    $query->bindParam("token", $token, PDO::PARAM_STR);
    $query->execute();

    if ($query->rowCount() > 0) {
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
        $query = $connection->prepare("UPDATE accounts SET password=:password, reset_token=NULL, reset_token_expiry=NULL WHERE reset_token=:token");
        $query->bindParam("password", $hashedPassword, PDO::PARAM_STR);
        $query->bindParam("token", $token, PDO::PARAM_STR);
        $query->execute();

        header("Location: login.php?reset=1");
        exit;
    } else {
        echo "<p>Invalid or expired token. Please try the password reset process again.</p>";
    }
} else {
    echo "<p>Invalid request.</p>";
}
?>
