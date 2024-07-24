<?php
session_start();
include('db.php');

if (!isset($_SESSION['account_id'])) {
    header('Location: login.php');
    exit;
}

$account_id = $_SESSION['account_id'];
$school_id = $_SESSION['school_id'] ?? 0;

// Check if the user is approved for the current school
$stmt = $connection->prepare("SELECT approved FROM Teachers WHERE account_id = :account_id AND school_id = :school_id");
$stmt->bindParam(':account_id', $account_id);
$stmt->bindParam(':school_id', $school_id);
$stmt->execute();
$approval = $stmt->fetchColumn();

if ($approval === false || $approval == 0) {
    header("Location: not_approved.php");
    exit;
}
?>

