<?php
$servername = "localhost";
$username = "AndersonSchool";
$password = "SpecialEd69$";
$dbname = "bFactor-test";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} else {
    echo "Connected successfully to the database";
}

if ($_POST['performance_id'] && $_POST['field_name'] && $_POST['new_value']) {
    $performanceId = $_POST['performance_id'];
    $fieldName = 'score' . $_POST['field_name']; // Assuming you named the fields in the database like score1, score2, etc.
    $newValue = $_POST['new_value'];

    // Update the database
    $stmt = $conn->prepare("UPDATE Performance SET $fieldName = ? WHERE performance_id = ?");
    $stmt->bind_param('si', $newValue, $performanceId);
    $stmt->execute();

    // Handle any errors or success messages here
    // You can return a JSON response indicating success or failure
    $response = array("success" => true); // Modify as needed
    echo json_encode($response);
}
?>
