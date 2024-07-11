<?php
session_start();
include('auth_session.php'); // Ensure the user is authenticated
include('db.php'); // Include the database connection

$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['studentId'])) { // Make sure to use the same key as used in the fetch request
    $studentId = $data['studentId'];
    $schoolId = $_SESSION['school_id'];

    try {
        // Set the 'archived' field to 0 to activate the student
        $query = $connection->prepare("UPDATE Students_new SET archived = 0 WHERE student_id_new = :student_id AND school_id = :school_id");
        $query->bindParam(':student_id', $studentId, PDO::PARAM_INT);
        $query->bindParam(':school_id', $schoolId, PDO::PARAM_INT);
        $query->execute();

        // Check if the update was successful
        if ($query->rowCount() > 0) {
            // Fetch the full student data to return to the client
            $studentQuery = $connection->prepare("SELECT * FROM Students_new WHERE student_id_new = :student_id AND school_id = :school_id");
            $studentQuery->bindParam(':student_id', $studentId, PDO::PARAM_INT);
            $studentQuery->bindParam(':school_id', $schoolId, PDO::PARAM_INT);
            $studentQuery->execute();
            $studentData = $studentQuery->fetch(PDO::FETCH_ASSOC);

            if ($studentData) {
                echo json_encode(['success' => true, 'student' => $studentData]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Student data could not be retrieved.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'No changes made, student may already be active or does not exist.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid or missing data']);
}
?>

