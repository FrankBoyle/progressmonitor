<?php
include('./users/db.php');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (isset($_POST['forgot_password'])) {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo '<p class="error">Invalid email format!</p>';
        exit;
    }

    $query = $connection->prepare("SELECT * FROM accounts WHERE email=:email");
    $query->bindParam("email", $email, PDO::PARAM_STR);
    $query->execute();
    
    if ($query->rowCount() > 0) {
        $token = bin2hex(random_bytes(50));
        $expiry = date("Y-m-d H:i:s", strtotime("+15 minutes"));
        $query = $connection->prepare("UPDATE accounts SET reset_token=:token, reset_token_expiry=:expiry WHERE email=:email");
        $query->bindParam("token", $token, PDO::PARAM_STR);
        $query->bindParam("expiry", $expiry, PDO::PARAM_STR);
        $query->bindParam("email", $email, PDO::PARAM_STR);
        $query->execute();

        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From: Your Site Name <noreply@iepreport.com>' . "\r\n";
        $headers .= 'Reply-To: noreply@iepreport.com' . "\r\n";
        $headers .= 'X-Mailer: PHP/' . phpversion();

        $subject = "Password Reset Request";
        $message = "<html><body>";
        $message .= "<h1>Password Reset Request</h1>";
        $message .= "<p>Click on the following link to reset your password:</p>";
        $message .= "<a href='https://iepreport.com/reset_password.php?token=$token'>Reset Password</a>";
        $message .= "</body></html>";

        if (mail($email, $subject, $message, $headers)) {
            echo '<p class="success">Password reset link has been sent to your email.</p>';
            header("Location: login.php?reset=1");
            exit();
        } else {
            echo '<p class="error">Failed to send password reset email.</p>';
        }
    } else {
        echo "<p class='error'>No account found with that email address.</p>";
    }
}
?>
