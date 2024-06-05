<?php
session_start();
include('auth_session.php');
include('db.php');

try {
    $stmt = $connection->prepare("SELECT metadata_id, category_name FROM Metadata WHERE school_id = ?");
    $stmt->execute([$_SESSION['school_id']]);
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($categories);
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode(['error' => 'Failed to fetch categories']);
}
