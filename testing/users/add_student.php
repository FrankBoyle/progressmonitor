<?php
session_start();
include('auth_session.php');
include('db.php');

// Ensure the request method is POST and required parameters are set
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['first_name'], $_POST['last_name'], $_POST['date_of_birth'], $_POST['grade_level'])) {
    $firstName = $_POST['first_name'];
    $lastName = $_POST['last_name'];
    $dateOfBirth = $_POST['date_of_birth'];
    $gradeLevel = $_POST['grade_level'];
    $groupId = isset($_POST['group_id']) ? $_POST['group_id'] : null;

    try {
        // Insert new student
        $stmt = $connection->prepare("INSERT INTO students (first_name, last_name, date_of_birth, grade_level) VALUES (?, ?, ?, ?)");
        $stmt->execute([$firstName, $lastName, $dateOfBirth, $gradeLevel]);
        $studentId = $connection->lastInsertId();

        // Assign student to the selected group if a group was selected
        if ($groupId) {
            $stmt = $connection->prepare("INSERT INTO group_students (group_id, student_id) VALUES (?, ?)");
            $stmt->execute([$groupId, $studentId]);
        }

        // Ensure no additional output is sent
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'message' => 'Student added successfully.']);
    } catch (PDOException $e) {
        // Handle any database errors
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    header('Content-Type: application/json');
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid request or missing parameters.']);
}

// Ensure no additional output is sent
exit;
?>
