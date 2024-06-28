<?php
session_start();
include('auth_session.php');
include('db.php');

header('Content-Type: application/json');

try {
    if (isset($_POST['metadata_id'], $_POST['custom_column_names'])) {
        $metadataId = $_POST['metadata_id'];
        $customColumnNames = json_decode($_POST['custom_column_names'], true);

        if (empty($metadataId) || empty($customColumnNames)) {
            throw new Exception('Missing required parameters.');
        }

        $connection->beginTransaction();
        foreach ($customColumnNames as $column) {
            if (!in_array($column['field'], ['score1_name', 'score2_name', 'score3_name'])) {
                throw new Exception("Invalid field name: {$column['field']}");
            }
            $stmt = $connection->prepare("UPDATE Metadata SET {$column['field']} = ? WHERE metadata_id = ?");
            $stmt->execute([$column['title'], $metadataId]);
        }
        $connection->commit();
        echo json_encode(["message" => "Custom column names updated successfully."]);
    } else {
        throw new Exception('Invalid request, missing required parameters.');
    }
} catch (Exception $e) {
    $connection->rollBack();
    error_log("Error updating custom column names: " . $e->getMessage());
    echo json_encode(["error" => "Error updating custom column names: " . $e->getMessage()]);
    http_response_code(500); // Ensure the correct HTTP status code is returned
}
?>
