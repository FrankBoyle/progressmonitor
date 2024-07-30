<?php
session_start();

// Enable PHP error logging (for debugging purposes)
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
// ini_set('log_errors', 1);
// ini_set('error_log', 'error_log.log');  // Ensure this file is writable by the server

include('db.php'); // Ensure db.php correctly sets up the $connection variable

header('Content-Type: application/json'); // Set this at the top of your PHP script

if (isset($_POST['login'])) {
    // Sanitize and validate email
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email format!']);
        exit;
    }

    // Sanitize password
    $password = trim($_POST['password']);
    
    if (empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Password cannot be empty!']);
        exit;
    }

    try {
        // Prepare and execute query to get account details
        $query = $connection->prepare("SELECT * FROM accounts WHERE email = :email");
        $query->bindParam(":email", $email, PDO::PARAM_STR);
        $query->execute();
        $result = $query->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            echo json_encode(['success' => false, 'message' => 'Username or password is incorrect!']);
            exit;
        }

        // Verify password
        if (password_verify($password, $result['password'])) {
            // Regenerate session ID
            session_regenerate_id(true);

            // Store session data
            $_SESSION['user'] = $result['email'];
            $_SESSION['account_id'] = $result['id'];

            // Prepare and execute query to get teacher details
            $teacherQuery = $connection->prepare("SELECT teacher_id, school_id, is_admin, approved, program_id FROM Teachers WHERE account_id = :account_id");
            $teacherQuery->bindParam(":account_id", $result['id'], PDO::PARAM_INT);
            $teacherQuery->execute();
            $teacherResult = $teacherQuery->fetch(PDO::FETCH_ASSOC);

            if ($teacherResult) {
                $_SESSION['teacher_id'] = $teacherResult['teacher_id'];
                $_SESSION['school_id'] = $teacherResult['school_id'];
                $_SESSION['is_admin'] = (bool) $teacherResult['is_admin'];
                $_SESSION['is_approved'] = (bool) $teacherResult['approved'];
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
