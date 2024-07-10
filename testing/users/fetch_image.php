<?php
include('auth_session.php');
include('db.php');

if (isset($_GET['goal_id'])) {
    $goalId = $_GET['goal_id'];

    $stmt = $connection->prepare("SELECT report_image FROM Goal_notes WHERE goal_id = ?");
    $stmt->execute([$goalId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result && $result['report_image']) {
        header('Content-Type: image/png'); // Adjust the content type based on your image format
        echo $result['report_image'];
    } else {
        echo 'Image not found';
    }
} else {
    echo 'Invalid request';
}
?>

