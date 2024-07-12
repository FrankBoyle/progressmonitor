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
    
        $subject = "Password Reset Request";
        $message = "<html><body><h1>Password Reset Request</h1><p>Click on the following link to reset your password:</p><a href='https://iepreport.com/reset_password.php?token=$token'>Reset Password</a></body></html>";
    
        // Prepare to send email via Mailchimp API
        $postData = [
            'key' => 'Md-sQF74EnSZM1adKIeRSVsSw',
            'message' => [
                'html' => $message,
                'text' => strip_tags($message),
                'subject' => $subject,
                'from_email' => 'noreply@iepreport.com',
                'from_name' => 'Fran Boyle',
                'to' => [['email' => $email, 'type' => 'to']]
            ]
        ];
    
        $ch = curl_init('https://mandrillapp.com/api/1.0/messages/send.json');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
        $result = curl_exec($ch);
        $info = curl_getinfo($ch);
        if (curl_errno($ch)) {
            echo 'Curl error: ' . curl_error($ch);
        }
        curl_close($ch);
        
        if ($result) {
            $response = json_decode($result, true);
            if (isset($response[0]['status']) && $response[0]['status'] == 'sent') {
                echo '<p class="success">Password reset link has been sent to your email.</p>';
                header("Location: login.php?reset=1");
                exit;
            } else {
                echo '<p class="error">Failed to send password reset email. Response: ' . htmlspecialchars($result) . '</p>';
            }
        } else {
            echo '<p class="error">Failed to send password reset email. No response from server.</p>';
        }
        
}    
?>
