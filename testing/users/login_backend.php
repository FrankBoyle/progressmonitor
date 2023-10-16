<?php
    session_start();

    // Error reporting for development
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    include('db.php');

    if (isset($_POST['login'])) {
        $email = $_POST['email'];
        $password = $_POST['password'];
        
        try {
            $query = $connection->prepare("SELECT * FROM accounts WHERE email=:email");
            $query->bindParam("email", $email, PDO::PARAM_STR);
            $query->execute();
            
            $result = $query->fetch(PDO::FETCH_ASSOC);  
            
            if (!$result) {
                echo '<p class="error">Username or password is incorrect!</p>';
            } else {
              if (password_verify($password, $result['password'])) {
                $_SESSION['user'] = $result['email'];
                
                // Fetching additional information now that the user is verified
                $accountId = $result['id'];
                
                $teacherQuery = $connection->prepare("SELECT teacher_id, SchoolID FROM Teachers WHERE account_id = :accountId");
                $teacherQuery->bindParam("accountId", $accountId, PDO::PARAM_INT);
                $teacherQuery->execute();
                
                $teacherResult = $teacherQuery->fetch(PDO::FETCH_ASSOC);
            
                if ($teacherResult) {
                    $_SESSION['teacher_id'] = $teacherResult['teacher_id'];

                    // Assuming 'SchoolID' is the correct column name for the school identifier
                    $_SESSION['SchoolID'] = $teacherResult['SchoolID']; // Store SchoolID in the session
                } else {
                    echo '<p class="error">No teacher ID associated with this account.</p>';
                    exit(); 
                }
                
                // Redirect to the desired page after successful login
                header("Location: ../test.php");
                exit(); 
            } else {
                echo '<p class="error">Username or password is incorrect!</p>';
            }}
        } catch (PDOException $e) {
            echo "Database Error: " . $e->getMessage(); // Show the exception error message
        }
    }
?>
