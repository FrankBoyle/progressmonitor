<?php
file_put_contents('post_log.txt', print_r($_POST, true));
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('./users/db.php');

$response = ['success' => false, 'message' => 'Unknown error.'];

if (isset($_POST['performance_id'])) {
    $performanceId = $_POST['performance_id'];

    try {
        // Prepare the DELETE statement
        $stmt = $connection->prepare("DELETE FROM Performance WHERE performance_id = :performanceId");
        
        // Bind the parameters
        $stmt->bindParam(':performanceId', $performanceId, PDO::PARAM_INT);
        
        // Execute the statement
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $response['success'] = true;
        } else {
            $response['message'] = "No rows affected. The provided performance_id might not exist in the database.";
        }
    } catch (PDOException $e) {
        $response['message'] = "Database error: " . $e->getMessage();
    }
}

echo json_encode($response);

?>

