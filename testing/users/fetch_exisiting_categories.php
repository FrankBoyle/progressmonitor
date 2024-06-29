<?php
session_start();
include('auth_session.php');
include('db.php');

header('Content-Type: application/json');

try {
    if (isset($_GET['student_id'], $_GET['school_id'])) {
        $studentId = $_GET['student_id'];
        $schoolId = $_GET['school_id'];

        $stmt = $connection->prepare("
            SELECT DISTINCT Metadata.metadata_id, Metadata.category_name 
            FROM Metadata
            JOIN Goals ON Metadata.metadata_id = Goals.metadata_id
            WHERE Goals.student_id_new = ? AND Goals.school_id = ?
        ");
        $stmt->execute([$studentId, $schoolId]);
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($categories);
    } else {
        throw new Exception('Missing required parameters.');
    }
} catch (Exception $e) {
    error_log("Error fetching existing categories: " . $e->getMessage());
    echo json_encode(["error" => "Error fetching existing categories: " . $e->getMessage()]);
}
?>
