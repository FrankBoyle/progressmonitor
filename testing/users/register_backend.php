<?php
include('db.php');

error_reporting(E_ALL);
ini_set('display_errors', 1);

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
        // Assuming template school_id is '8'
        copyGoalTemplates(8, $school_id, $connection);
        log_message("Goal templates copied to new school with ID: $school_id");
    
        header("Location: ../login.php");
        echo '<p class="success">Your registration was successful!</p>';
    } else {
        log_message("Registration failed for $email");
        echo '<p class="error">Something went wrong!</p>';
    }
    
}

function copyGoalTemplates($templateSchoolId, $newSchoolId, $connection) {
    try {
        // Prepare the query to select template metadata entries from the template school
        $selectQuery = $connection->prepare("SELECT metadata_template, category_name, score1_name, score2_name, score3_name, score4_name, score5_name, score6_name, score7_name, score8_name, score9_name, score10_name FROM Metadata WHERE school_id = :templateSchoolId");
        $selectQuery->bindParam(':templateSchoolId', $templateSchoolId, PDO::PARAM_INT);
        $selectQuery->execute();
        $templates = $selectQuery->fetchAll(PDO::FETCH_ASSOC);

        // Prepare the query to insert copied metadata entries for the new school
        $insertQuery = $connection->prepare("INSERT INTO Metadata (school_id, metadata_template, category_name, score1_name, score2_name, score3_name, score4_name, score5_name, score6_name, score7_name, score8_name, score9_name, score10_name) VALUES (:newSchoolId, :metadata_template, :category_name, :score1_name, :score2_name, :score3_name, :score4_name, :score5_name, :score6_name, :score7_name, :score8_name, :score9_name, :score10_name)");

        // Execute insert for each template
        foreach ($templates as $template) {
            $insertQuery->bindParam(':newSchoolId', $newSchoolId, PDO::PARAM_INT);
            $insertQuery->bindParam(':metadata_template', $template['metadata_template'], PDO::PARAM_INT);
            $insertQuery->bindParam(':category_name', $template['category_name'], PDO::PARAM_STR);
            // Repeat for all score fields
            $insertQuery->bindParam(':score1_name', $template['score1_name'], PDO::PARAM_STR);
            $insertQuery->bindParam(':score2_name', $template['score2_name'], PDO::PARAM_STR);
            // Bind all other parameters similarly
            $insertQuery->bindParam(':score3_name', $template['score3_name'], PDO::PARAM_STR);
            $insertQuery->bindParam(':score4_name', $template['score4_name'], PDO::PARAM_STR);
            $insertQuery->bindParam(':score5_name', $template['score5_name'], PDO::PARAM_STR);
            $insertQuery->bindParam(':score6_name', $template['score6_name'], PDO::PARAM_STR);
            $insertQuery->bindParam(':score7_name', $template['score7_name'], PDO::PARAM_STR);
            $insertQuery->bindParam(':score8_name', $template['score8_name'], PDO::PARAM_STR);
            $insertQuery->bindParam(':score9_name', $template['score9_name'], PDO::PARAM_STR);
            $insertQuery->bindParam(':score10_name', $template['score10_name'], PDO::PARAM_STR);
            $insertQuery->execute();
        }
    } catch (PDOException $e) {
        echo 'Database error: ' . $e->getMessage();
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

