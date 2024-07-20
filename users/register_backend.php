<?php
session_start();
include('db.php');

 Enable PHP error logging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', 'error_log.log');  // Ensure this file is writable by the server

function log_message($message) {
    file_put_contents('register_debug.log', $message . PHP_EOL, FILE_APPEND);
}

if (isset($_POST['register'])) {
    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $school_uuid = $_POST['school_uuid'];
    $school_name = $_POST['school_name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $password_hash = password_hash($password, PASSWORD_BCRYPT);

    log_message("Starting registration process for $email");

    if (empty($school_uuid)) {
        if (empty($school_name)) {
            log_message("No UUID or school name provided");
            echo '<p class="error">Please provide a school name if you do not have a UUID!</p>';
            exit;
        }

        $school_uuid = uuid_generate();
        $query = $connection->prepare("INSERT INTO Schools (school_uuid, SchoolName) VALUES (:school_uuid, :school_name)");
        $query->bindParam(":school_uuid", $school_uuid, PDO::PARAM_STR);
        $query->bindParam(":school_name", $school_name, PDO::PARAM_STR);
        $query->execute();
        $school_id = $connection->lastInsertId();
        log_message("New school created with ID $school_id");
    } else {
        $query = $connection->prepare("SELECT school_id FROM Schools WHERE school_uuid = :school_uuid");
        $query->bindParam(":school_uuid", $school_uuid, PDO::PARAM_STR);
        $query->execute();

        if ($query->rowCount() == 0) {
            log_message("Invalid School UUID: $school_uuid");
            echo '<p class="error">Invalid School UUID!</p>';
            exit;
        }

        $school = $query->fetch(PDO::FETCH_ASSOC);
        $school_id = $school['school_id'];
        log_message("School ID found: $school_id");
    }

    $query = $connection->prepare("SELECT * FROM accounts WHERE email = :email");
    $query->bindParam(":email", $email, PDO::PARAM_STR);
    $query->execute();

    if ($query->rowCount() > 0) {
        log_message("Email already registered: $email");
        echo '<p class="error">The email address is already registered!</p>';
        exit;
    }

    $query = $connection->prepare("INSERT INTO accounts (school_id, fname, lname, email, password) VALUES (:school_id, :fname, :lname, :email, :password_hash)");
    $query->bindParam(":school_id", $school_id, PDO::PARAM_INT);
    $query->bindParam(":fname", $fname, PDO::PARAM_STR);
    $query->bindParam(":lname", $lname, PDO::PARAM_STR);
    $query->bindParam(":email", $email, PDO::PARAM_STR);
    $query->bindParam(":password_hash", $password_hash, PDO::PARAM_STR);
    $result = $query->execute();

    if ($result) {
        log_message("Registration successful for $email");
        $_SESSION['registration_success'] = true; // Set session variable
        header("Location: ../login.php");
        exit; // Ensure script stops executing after redirect
    } else {
        log_message("Registration failed for $email");
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

