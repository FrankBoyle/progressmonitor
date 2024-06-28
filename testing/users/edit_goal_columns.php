<?php
session_start();
include('auth_session.php');
include('db.php');

header('Content-Type: application/json');

try {
    if (isset($_POST['goal_id'], $_POST['custom_column_names'])) {
        $goalId = $_POST['goal_id'];
        $customColumnNames = json_encode($_POST['custom_column_names']);

        if (empty($goalId) || empty($customColumnNames)) {
            throw new Exception('Missing required parameters.');
        }

        // Prepare and execute the update statement
        $stmt = $connection->prepare("
            UPDATE Goals 
            SET custom_column_names = ? 
            WHERE goal_id = ?
        ");
        $stmt->execute([$customColumnNames, $goalId]);

        echo json_encode(["message" => "Custom column names updated successfully."]);
    } else {
        throw new Exception('Invalid request, missing required parameters.');
    }
} catch (Exception $e) {
    error_log("Error updating custom column names: " . $e->getMessage());
    echo json_encode(["error" => "Error updating custom column names: " . $e->getMessage()]);
}
?>
