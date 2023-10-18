<?php
    session_start();
    include('db.php');

    if (isset($_POST['register'])) {
        $fname = $_POST['fname'];
        $lname = $_POST['lname'];
        $school_id = $_POST['school_id'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $password_hash = password_hash($password, PASSWORD_BCRYPT);
        $query = $connection->prepare("SELECT * FROM accounts WHERE email=:email");
        $query->bindParam("email", $email, PDO::PARAM_STR);
        $query->execute();
        
        if ($query->rowCount() > 0) {
            echo '<p class="error">The email address is already registered!</p>';
        }
        if ($query->rowCount() == 0) {
          $query = $connection->prepare("INSERT INTO accounts(fname,lname,email,password,school_id) VALUES (:fname,:lname,:email,:password_hash,:school_id)");
          $query->bindParam("fname", $fname, PDO::PARAM_STR);
            $query->bindParam("lname", $lname, PDO::PARAM_STR);
            $query->bindParam("school_id", $school_id, PDO::PARAM_INT);  // Assuming school_id is an integer
            $query->bindParam("email", $email, PDO::PARAM_STR);
            $query->bindParam("password_hash", $password_hash, PDO::PARAM_STR);
            $result = $query->execute();
            if ($result) {
              header("Location: ../login.php");
              echo '<p class="success">Your registration was successful!</p>';
            } else {
              echo '<p class="error">Something went wrong!</p>';
            }
        }
    }
?>