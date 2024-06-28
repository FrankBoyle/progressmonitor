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

        $query = "
            UPDATE Metadata 
            SET score1_name = ?, score2_name = ?, score3_name = ?, score4_name = ?, score5_name = ?, 
                score6_name = ?, score7_name = ?, score8_name = ?, score9_name = ?, score10_name = ?
            WHERE metadata_id = ?
        ";

        $stmt = $connection->prepare($query);
        $stmt->bind_param("ssssssssssi", 
            $customColumnNames['score1'], $customColumnNames['score2'], $customColumnNames['score3'],
            $customColumnNames['score4'], $customColumnNames['score5'], $customColumnNames['score6'],
            $customColumnNames['score7'], $customColumnNames['score8'], $customColumnNames['score9'],
            $customColumnNames['score10'], $metadataId);

        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            echo json_encode(["message" => "Custom column names updated successfully."]);
        } else {
            throw new Exception("No rows updated - it's possible the metadata_id does not exist or the new names are the same as the old.");
        }
    } else {
        throw new Exception('Invalid request, missing required parameters.');
    }
} catch (Exception $e) {
    error_log("Error updating custom column names: " . $e->getMessage());
    echo json_encode(["error" => "Error updating custom column names: " . $e->getMessage()]);
}
?>
