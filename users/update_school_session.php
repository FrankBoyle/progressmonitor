<?php
session_start();
include('auth_session.php');
include('db.php');

header('Content-Type: application/json');

if (isset($_POST['school_id'])) {
    $school_id = $_POST['school_id'];
    $account_id = $_SESSION['account_id'];

    // Fetch the corresponding teacher_id, approved status, and is_admin for the new school_id
    $query = $connection->prepare("SELECT teacher_id, approved, is_admin FROM Teachers WHERE account_id = :account_id AND school_id = :school_id");
    $query->bindParam("account_id", $account_id, PDO::PARAM_INT);
    $query->bindParam("school_id", $school_id, PDO::PARAM_INT);
    $query->execute();
    $result = $query->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        $_SESSION['teacher_id'] = $result['teacher_id'];
        $_SESSION['school_id'] = $school_id; // Update session with the new school ID
        $_SESSION['is_admin'] = $result['is_admin'] == 1; // Update admin status in session
        $approved = (bool)$result['approved']; // Cast to boolean if necessary
        echo json_encode(['success' => true, 'approved' => $approved]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Teacher not found for the selected school.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'School ID not provided.']);
}
?>
