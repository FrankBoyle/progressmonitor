<?php
session_start();
include('auth_session.php');
include('db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $date_of_birth = $_POST['date_of_birth'];
    $grade_level = $_POST['grade_level'];
    $school_id = $_SESSION['school_id']; // Assuming school_id is stored in session

    $query = "INSERT INTO Students_new (first_name, last_name, date_of_birth, grade_level, school_id) VALUES (?, ?, ?, ?, ?)";
    $stmt = $con->prepare($query);
    $stmt->bind_param("ssssi", $first_name, $last_name, $date_of_birth, $grade_level, $school_id);

    if ($stmt->execute()) {
        echo "Student added successfully.";
    } else {
        echo "Error adding student: " . $stmt->error;
    }

    $stmt->close();
}
?>
