<?php
include('./users/db.php');

// Error Reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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

        // Send the reset link to the user's email
        $resetLink = "https://iepreport.com/reset_password.php?token=$token";
        mail($email, "Password Reset Request", "Click the link to reset your password: $resetLink");

        header("Location: login.php?reset=1");
        exit();
    } else {
        echo "<p class='error'>No account found with that email address.</p>";
    }
}
?>

