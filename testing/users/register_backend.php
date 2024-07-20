<?php
include('db.php');

// Enable error logging and set the error log file path
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/my_php_errors.log'); // __DIR__ ensures the log file is created in the same directory as the script
error_reporting(E_ALL);

function log_message($message) {
    file_put_contents(__DIR__ . '/register_debug.log', $message . PHP_EOL, FILE_APPEND); // Ensure log file is created in the script directory
}

header('Content-Type: application/json');

if (isset($_POST['register'])) {
    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $school_uuid = $_POST['school_uuid'];
    $school_name = $_POST['school_name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $password_hash = password_hash($password, PASSWORD_BCRYPT);

    log_message("Starting registration process for $email");

    try {
        $connection->beginTransaction();

        if (empty($school_uuid) && !empty($school_name)) {
            $school_uuid = uuid_generate();
            $query = $connection->prepare("INSERT INTO Schools (school_uuid, SchoolName) VALUES (:school_uuid, :school_name)");
            $query->bindParam(":school_uuid", $school_uuid, PDO::PARAM_STR);
            $query->bindParam(":school_name", $school_name, PDO::PARAM_STR);
            $query->execute();
            $school_id = $connection->lastInsertId();
            log_message("New school created with ID $school_id");

            // Copy metadata templates
            copyGoalTemplates(8, $school_id, $connection);
        } elseif (!empty($school_uuid)) {
            $query = $connection->prepare("SELECT school_id FROM Schools WHERE school_uuid = :school_uuid");
            $query->bindParam(":school_uuid", $school_uuid, PDO::PARAM_STR);
            $query->execute();
            $school = $query->fetch(PDO::FETCH_ASSOC);

            if (!$school) {
                throw new Exception("Invalid School UUID: $school_uuid");
            }

            $school_id = $school['school_id'];
        } else {
            throw new Exception("No school information provided.");
        }

        $query = $connection->prepare("INSERT INTO accounts (school_id, fname, lname, email, password) VALUES (:school_id, :fname, :lname, :email, :password_hash)");
        $query->bindParam(":school_id", $school_id, PDO::PARAM_INT);
        $query->bindParam(":fname", $fname, PDO::PARAM_STR);
        $query->bindParam(":lname", $lname, PDO::PARAM_STR);
        $query->bindParam(":email", $email, PDO::PARAM_STR);
        $query->bindParam(":password_hash", $password_hash, PDO::PARAM_STR);
        $query->execute();

        $connection->commit();
        echo json_encode(['success' => true, 'message' => 'Your registration was successful!']);
    } catch (Exception $e) {
        $connection->rollBack();
        log_message("Error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Registration failed: ' . $e->getMessage()]);
    }
    exit;
}

function copyGoalTemplates($templateSchoolId, $newSchoolId, $connection) {
    $query = $connection->prepare("SELECT * FROM Metadata WHERE school_id = :templateSchoolId");
    $query->bindParam(':templateSchoolId', $templateSchoolId, PDO::PARAM_INT);
    $query->execute();
    $templates = $query->fetchAll(PDO::FETCH_ASSOC);

    $insertQuery = $connection->prepare("INSERT INTO Metadata (school_id, metadata_template, category_name, score1_name, score2_name, score3_name, score4_name, score5_name, score6_name, score7_name, score8_name, score9_name, score10_name) VALUES (:newSchoolId, :metadata_template, :category_name, :score1_name, :score2_name, :score3_name, :score4_name, :score5_name, :score6_name, :score7_name, :score8_name, :score9_name, :score10_name)");

    foreach ($templates as $template) {
        $insertQuery->bindParam(':newSchoolId', $newSchoolId, PDO::PARAM_INT);
        $insertQuery->bindParam(':metadata_template', $template['metadata_template'], PDO::PARAM_INT);
        $insertQuery->bindParam(':category_name', $template['category_name'], PDO::PARAM_STR);
        $insertQuery->bindParam(':score1_name', $template['score1_name'], PDO::PARAM_STR);
        $insertQuery->bindParam(':score2_name', $template['score2_name'], PDO::PARAM_STR);
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
