<?php
include('./users/db.php');

// Error Reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/PHPMailer/src/Exception.php';
require 'vendor/PHPMailer/src/PHPMailer.php';
require 'vendor/PHPMailer/src/SMTP.php';

if (isset($_POST['forgot_password'])) {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo '<p class="error">Invalid email format!</p>';
        exit;
    }
    
    // Check if the email exists
    $query = $connection->prepare("SELECT * FROM accounts WHERE email=:email");
    $query->bindParam("email", $email, PDO::PARAM_STR);
    $query->execute();
    
    if ($query->rowCount() > 0) {
        // Generate a unique token
        $token = bin2hex(random_bytes(50));

        // Set token expiry
        $expiry = date("Y-m-d H:i:s", strtotime("+15 minutes"));

        // Update the user record with the token
        $query = $connection->prepare("UPDATE accounts SET reset_token=:token, reset_token_expiry=:expiry WHERE email=:email");
        $query->bindParam("token", $token, PDO::PARAM_STR);
        $query->bindParam("expiry", $expiry, PDO::PARAM_STR);
        $query->bindParam("email", $email, PDO::PARAM_STR);
        $query->execute();

        // Send the reset link to the user's email using PHPMailer
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'mail.spacemail.com'; // Set the SMTP server to send through
            $mail->SMTPAuth   = true; // Enable SMTP authentication
            $mail->Username   = 'info@iepreport.com'; // SMTP username
            $mail->Password   = '502524d9-7990-4770-8945-992fCD18761A'; // SMTP password
            $mail->SMTPSecure = 'ssl'; // Enable SSL encryption; PHPMailer has 'ssl' also
            $mail->Port       = 465; // TCP port to connect to
        
            $mail->setFrom('info@iepreport.com', 'IEP Report');
            $mail->addAddress($email); // Add a recipient
        
            $mail->isHTML(true); // Set email format to HTML
            $mail->Subject = 'Password Reset Request';
            $mail->Body    = "Click the link to reset your password: <a href='https://iepreport.com/reset_password.php?token=$token'>Reset Password</a>";
        
            $mail->send();
            echo 'Password reset email has been sent.';
        } catch (Exception $e) {
            echo 'Message could not be sent. Mailer Error: ' . $mail->ErrorInfo;
        }
        
        
        header("Location: login.php?reset=1");
        exit();
    }
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

