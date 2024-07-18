<?php
include('db.php');

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (isset($_POST['register'])) {
    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $school_uuid = $_POST['school_uuid'];
    $school_name = $_POST['school_name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $password_hash = password_hash($password, PASSWORD_BCRYPT);

    if (empty($school_uuid)) {
        if (empty($school_name)) {
            echo '<p class="error">Please provide a school name if you do not have a UUID!</p>';
            exit;
        }

        // Insert the new school into the Schools table
        $school_uuid = uuid_generate();
        $query = $connection->prepare("INSERT INTO Schools (school_uuid, school_name) VALUES (:school_uuid, :school_name)");
        $query->bindParam(":school_uuid", $school_uuid, PDO::PARAM_STR);
        $query->bindParam(":school_name", $school_name, PDO::PARAM_STR);
        $query->execute();

        // Get the school ID of the newly inserted school
        $school_id = $connection->lastInsertId();
    } else {
        // Lookup the school ID based on the UUID
        $query = $connection->prepare("SELECT school_id FROM Schools WHERE school_uuid = :school_uuid");
        $query->bindParam(":school_uuid", $school_uuid, PDO::PARAM_STR);
        $query->execute();

        if ($query->rowCount() == 0) {
            echo '<p class="error">Invalid School UUID!</p>';
            exit;
        }

        $school = $query->fetch(PDO::FETCH_ASSOC);
        $school_id = $school['school_id'];
    }

    // Check if the email is already registered
    $query = $connection->prepare("SELECT * FROM accounts WHERE email = :email");
    $query->bindParam(":email", $email, PDO::PARAM_STR);
    $query->execute();

    if ($query->rowCount() > 0) {
        echo '<p class="error">The email address is already registered!</p>';
        exit;
    }

    // Insert the new user into the accounts table
    $query = $connection->prepare("INSERT INTO accounts (school_id, fname, lname, email, password) VALUES (:school_id, :fname, :lname, :email, :password_hash)");
    $query->bindParam(":school_id", $school_id, PDO::PARAM_INT);
    $query->bindParam(":fname", $fname, PDO::PARAM_STR);
    $query->bindParam(":lname", $lname, PDO::PARAM_STR);
    $query->bindParam(":email", $email, PDO::PARAM_STR);
    $query->bindParam(":password_hash", $password_hash, PDO::PARAM_STR);
    $result = $query->execute();

    if ($result) {
        header("Location: ../login.php");
        echo '<p class="success">Your registration was successful!</p>';
    } else {
        echo '<p class="error">Something went wrong!</p>';
    }
}

function uuid_generate() {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}
?>
