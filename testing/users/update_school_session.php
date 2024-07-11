<?php
session_start();

include('auth_session.php');
include('db.php');

if (isset($_POST['school_id'])) {
    $school_id = $_POST['school_id'];
    $account_id = $_SESSION['account_id']; // Assuming you have account_id in session

    try {
        // Fetch the correct teacher_id based on account_id and school_id
        $query = $connection->prepare("
            SELECT teacher_id 
            FROM Teachers 
            WHERE account_id = :account_id AND school_id = :school_id
        ");
        $query->bindParam("account_id", $account_id, PDO::PARAM_INT);
        $query->bindParam("school_id", $school_id, PDO::PARAM_INT);
        $query->execute();
        $result = $query->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $_SESSION['school_id'] = $school_id;
            $_SESSION['teacher_id'] = $result['teacher_id'];
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'No matching record found.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'School ID not set.']);
}
?>
