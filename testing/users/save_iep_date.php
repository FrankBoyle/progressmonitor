<?php
include('db.php');
include('auth_session.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $iep_date = isset($input['iep_date']) ? $input['iep_date'] : null;
    $student_id = isset($input['student_id']) ? $input['student_id'] : null;

    if ($iep_date && $student_id) {
        $stmt = $connection->prepare("UPDATE Students_new SET IEP_Date = ? WHERE student_id_new = ?");
        if ($stmt->execute([$iep_date, $student_id])) {
            echo json_encode(["success" => true, "message" => "IEP date saved successfully."]);
        } else {
            echo json_encode(["success" => false, "message" => "Error saving IEP date."]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Invalid data."]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method."]);
}
?>
