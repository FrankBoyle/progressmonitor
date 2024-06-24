<?php
session_start();
include('auth_session.php');
include('db.php');

header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data || !isset($data['performance_id'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid data']);
        exit;
    }

    $performance_id = $data['performance_id'];
    unset($data['performance_id']);

    $setClauses = [];
    $params = [];

    // Log the incoming data for debugging
    error_log("Received data: " . print_r($data, true));

    foreach ($data as $key => $value) {
        // Validate if the key is a valid column name
        if (preg_match('/^[a-zA-Z0-9_]+$/', $key)) {
            $setClauses[] = "`$key` = ?";
            $params[] = $value === null ? NULL : $value; // Handle null values
        } else {
            // Log invalid field names
            error_log("Invalid field name: $key");
        }
    }

    $params[] = $performance_id;
    $setClause = implode(', ', $setClauses);

    // Log the SQL query and parameters for debugging
    error_log("SQL query: UPDATE Performance SET $setClause WHERE performance_id = ?");
    error_log("Parameters: " . print_r($params, true));

    $query = "UPDATE Performance SET $setClause WHERE performance_id = ?";
    $stmt = $connection->prepare($query);

    $success = $stmt->execute($params);

    if (!$success) {
        // Capture SQL error info
        $errorInfo = $stmt->errorInfo();
        echo json_encode(['success' => false, 'message' => 'Database error', 'errorInfo' => $errorInfo]);
    } else {
        echo json_encode(['success' => true, 'message' => 'Data updated successfully']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
