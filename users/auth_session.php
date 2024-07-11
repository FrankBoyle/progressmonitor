<?php
session_start();
include('db.php');

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Check if the user is a teacher and is approved
$user_id = $_SESSION['user']['id'];
$query = $connection->prepare("SELECT approved FROM Teachers WHERE account_id = :account_id");
$query->bindParam(":account_id", $user_id, PDO::PARAM_INT);
$query->execute();
$teacher = $query->fetch(PDO::FETCH_ASSOC);

if (!$teacher || $teacher['approved'] == 0) {
    $_SESSION['is_approved'] = false;
} else {
    $_SESSION['is_approved'] = true;
}
?>
