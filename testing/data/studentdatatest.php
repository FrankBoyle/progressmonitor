<?php
$servername = "localhost";
$username = "AndersonSchool";
$password = "SpecialEd69$";
$dbname = "AndersonSchool";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT date, score, baseline FROM JaylaBrazzle1";
$result = $conn->query($sql);

$dataArray = array();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $dataArray[] = array(
            'col3' => $row['col3'],
            'col4' => $row['col4']
        );
    }
}

$conn->close();

echo json_encode($dataArray);
?>

