<?php
session_start();
include('auth_session.php');
include('db.php');

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['performance_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

$performance_id = $data['performance_id'];
unset($data['performance_id']);

$setClauses = [];
$params = [];
foreach ($data as $key => $value) {
    $setClauses[] = "`$key` = ?";
    $params[] = $value;
}

$params[] = $performance_id;
$setClause = implode(', ', $setClauses);

$query = "UPDATE Performance SET $setClause WHERE performance_id = ?";
$stmt = $connection->prepare($query);
$success = $stmt->execute($params);

echo json_encode(['success' => $success]);
?>
