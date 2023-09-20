<?php
$servername = "localhost";
$username = "AndersonSchool";
$password = "SpecialEd69$";
$dbname = "bFactor-test";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_POST['performance_id'] && $_POST['field_name'] && $_POST['new_value']) {
    $performanceId = $_POST['performance_id'];
    $fieldName = 'score' . $_POST['field_name']; // Assuming you named the fields in the database like score1, score2, etc.
    $newValue = $_POST['new_value'];

    // Update the database
    $stmt = $conn->prepare("UPDATE Performance SET $fieldName = ? WHERE performance_id = ?");
    $stmt->bind_param('si', $newValue, $performanceId);
    $stmt->execute();

    if ($stmt->affected_rows < 0 || $stmt->error) {
        $response = array("success" => false, "error" => "Database error: " . $stmt->error);
    } else {
        $response = array("success" => true);
    }

    echo json_encode($response);
} else {
    $response = array("success" => false, "error" => "Invalid data provided.");
    echo json_encode($response);
}
?>

