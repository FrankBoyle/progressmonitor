<?php
include('auth_session.php');
include('db.php');

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (isset($_GET['student_id'])) {
            $studentId = $_GET['student_id'];
            
            error_log("Fetching goals for student_id: $studentId");

            $stmt = $connection->prepare("
                SELECT g.goal_id, g.goal_description, gm.metadata_id, gm.category_name, gn.note_id, gn.report_image
                FROM Goals g
                INNER JOIN Metadata gm ON g.metadata_id = gm.metadata_id
                LEFT JOIN Goal_notes gn ON g.goal_id = gn.goal_id
                WHERE g.student_id_new = ? AND g.archived = 0
            ");
            $stmt->execute([$studentId]);
            $goals = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            error_log("Goals fetched: " . print_r($goals, true));

            echo json_encode($goals);
        } else {
            echo json_encode(["error" => "Invalid request, missing student_id"]);
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['goal_id']) && isset($_POST['goal_description'])) {
            $goalId = $_POST['goal_id'];
            $goalDescription = $_POST['goal_description'];

            $stmt = $connection->prepare("UPDATE Goals SET goal_description = ? WHERE goal_id = ?");
            $stmt->execute([$goalDescription, $goalId]);

            echo json_encode(["status" => "success", "message" => "Goal updated successfully."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Invalid request, missing goal_id or goal_description"]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid request method"]);
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode(["status" => "error", "message" => "Error processing request: " . $e->getMessage()]);
}
?>





