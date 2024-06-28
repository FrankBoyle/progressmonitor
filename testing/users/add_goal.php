<?php
session_start();
include('auth_session.php');
include('db.php');

header('Content-Type: application/json');

try {
    if (isset($_POST['student_id'], $_POST['goal_description'], $_POST['goal_date'], $_POST['metadata_option'])) {
        $studentId = $_POST['student_id'];
        $goalDescription = $_POST['goal_description'];
        $goalDate = $_POST['goal_date'];
        $metadataOption = $_POST['metadata_option'];
        $schoolId = $_SESSION['school_id'];
        $newMetadataId = null;

        if (empty($studentId) || empty($goalDescription) || empty($goalDate) || empty($metadataOption) || empty($schoolId)) {
            throw new Exception('Missing required parameters.');
        }

        if ($metadataOption === 'existing') {
            if (!isset($_POST['existing_metadata_id'])) {
                throw new Exception('Existing metadata ID is required.');
            }
            $newMetadataId = $_POST['existing_metadata_id'];
        } else if ($metadataOption === 'new') {
            if (!isset($_POST['category_name'])) {
                throw new Exception('New metadata details are required.');
            }

            $categoryName = $_POST['category_name'];
            $stmt = $connection->prepare("
                INSERT INTO Metadata (school_id, metadata_name, category_name, score1_name, score2_name, score3_name, score4_name, score5_name, score6_name, score7_name, score8_name, score9_name, score10_name) 
                VALUES (?, 'Custom Metadata', ?, 'Score 1', 'Score 2', 'Score 3', 'Score 4', 'Score 5', 'Score 6', 'Score 7', 'Score 8', 'Score 9', 'Score 10')
            ");
            $stmt->execute([$schoolId, $categoryName]);
            $newMetadataId = $connection->lastInsertId();
        } else {
            throw new Exception('Invalid metadata option.');
        }

        $stmt = $connection->prepare("
            INSERT INTO Goals (student_id_new, goal_description, goal_date, school_id, metadata_id) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$studentId, $goalDescription, $goalDate, $schoolId, $newMetadataId]);

        echo json_encode(["message" => "Goal added successfully."]);
    } else {
        throw new Exception('Invalid request, missing required parameters.');
    }
} catch (Exception $e) {
    error_log("Error adding goal: " . $e->getMessage());
    echo json_encode(["error" => "Error adding goal: " . $e->getMessage()]);
}
?>
