<?php
include('db.php');

$data = json_decode(file_get_contents("php://input"));

if (isset($data->teacher_id)) {
    $teacherId = $data->teacher_id;

    try {
        $query = $connection->prepare("DELETE FROM Teachers WHERE teacher_id = :teacher_id");
        $query->bindParam(':teacher_id', $teacherId, PDO::PARAM_INT);
        $query->execute();

        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
}
?>
