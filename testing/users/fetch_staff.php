<?php
include('auth_session.php'); // Ensure the user is authenticated
include('db.php'); // Include the database connection

header('Content-Type: application/json');

// Get the school ID from the session
$schoolId = $_SESSION['school_id'];

function fetchAllRelevantStaff($schoolId) {
    global $connection;
    $stmt = $connection->prepare("
        SELECT teacher_id, name, subject_taught, is_admin, approved
        FROM Teachers
        WHERE school_id = :schoolId
    ");
    $stmt->bindParam(':schoolId', $schoolId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch staff and output as JSON
$staff = fetchAllRelevantStaff($schoolId);
echo json_encode($staff);
?>
