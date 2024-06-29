<?php
include('db.php');
include('auth_session.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $iep_date = isset($_POST['iep_date']) ? $_POST['iep_date'] : null;
    $student_id = isset($_POST['student_id']) ? $_POST['student_id'] : null;

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
