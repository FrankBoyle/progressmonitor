<?php
file_put_contents('post_log.txt', print_r($_POST, true));
error_reporting(E_ALL);
ini_set('display_errors', 1);


include('./users/db.php');

if (!$connection) {
    die("Connection failed: " . $connection->errorInfo());
}

$response = ['success' => false, 'message' => 'Unknown error.'];

if (isset($_POST['performance_id'])) {
    $performanceId = $_POST['performance_id'];

    // Ensure you use prepared statements to prevent SQL injection
    $stmt = $connection->prepare("DELETE FROM Performance WHERE performance_id = ?");
    if ($stmt === false) {
        $response['message'] = "Failed to prepare the statement. Error: " . $connection->error;
    } else {
        $stmt->bind_param("i", $performanceId);

        try {
            $stmt->execute();
            $response['success'] = true;
        } catch (PDOException $e) {
            $response['message'] = $e->getMessage();
        }
        

        $stmt->close();
    }
}

echo json_encode($response);
?>

