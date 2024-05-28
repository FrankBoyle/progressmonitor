<?php
include('db.php');
include('auth_session.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $iep_date = isset($_POST['iep_date']) ? $_POST['iep_date'] : null;
    $student_id = isset($_POST['student_id']) ? $_POST['student_id'] : null;

    if ($iep_date && $student_id) {
        $stmt = $connection->prepare("UPDATE Students SET IEP_Date = ? WHERE student_id = ?");
        if ($stmt->execute([$iep_date, $student_id])) {
            echo "IEP date saved successfully.";
        } else {
            echo "Error saving IEP date.";
        }
    } else {
        echo "Invalid data.";
    }
} else {
    echo "Invalid request method.";
}
?>
