<?php
include('./users/db.php');

$response = ['success' => false, 'message' => 'Unknown error.'];

if (isset($_POST['performance_id'])) {
    $performanceId = $_POST['performance_id'];

    // Ensure you use prepared statements to prevent SQL injection
    $stmt = $connection->prepare("DELETE FROM your_table_name WHERE performance_id = ?");
    if ($stmt === false) {
        $response['message'] = "Failed to prepare the statement. Error: " . $connection->error;
    } else {
        $stmt->bind_param("i", $performanceId);

        if ($stmt->execute()) {
            $response['success'] = true;
        } else {
            $response['message'] = "Failed to execute the statement. Error: " . $stmt->error;
        }

        $stmt->close();
    }
}

echo json_encode($response);
?>

