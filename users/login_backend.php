<?php
session_start();

// Enable PHP error logging
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
//ini_set('log_errors', 1);
//ini_set('error_log', 'error_log.log');  // Ensure this file is writable by the server

include('db.php');

if (isset($_POST['login'])) {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email format!']);
        exit;
    }

    $password = $_POST['password'];

    try {
        $query = $connection->prepare("SELECT * FROM accounts WHERE email = :email");
        $query->bindParam(":email", $email, PDO::PARAM_STR);
        $query->execute();

        $result = $query->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            echo json_encode(['success' => false, 'message' => 'Username or password is incorrect!']);
            exit;
        }

        if (password_verify($password, $result['password'])) {
            session_regenerate_id(true);  // Regenerate session ID to prevent session fixation

            $_SESSION['user'] = $result['email'];
            $_SESSION['account_id'] = $result['id'];

            $teacherQuery = $connection->prepare("SELECT teacher_id, school_id, is_admin, approved, program_id FROM Teachers WHERE account_id = :account_id");
            $teacherQuery->bindParam(":account_id", $result['id'], PDO::PARAM_INT);
            $teacherQuery->execute();
            $teacherResult = $teacherQuery->fetch(PDO::FETCH_ASSOC);

            if ($teacherResult) {
                $_SESSION['teacher_id'] = $teacherResult['teacher_id'];
                $_SESSION['school_id'] = $teacherResult['school_id'];
                $_SESSION['is_admin'] = $teacherResult['is_admin'] == 1;
                $_SESSION['is_approved'] = $teacherResult['approved'] == 1;
                $_SESSION['program_id'] = $teacherResult['program_id'];

                if (!$_SESSION['is_approved']) {
                    echo json_encode(['success' => false, 'redirect_url' => '../not_approved.php']);
                    exit;
                }

                echo json_encode(['success' => true, 'redirect_url' => '../students.php']);
                exit;
            } else {
                echo json_encode(['success' => false, 'message' => 'No teacher ID associated with this account.']);
                exit;
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Username or password is incorrect!']);
            exit;
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        exit;
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Login data not received.']);
    exit;
}
?>
