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

echo "PHP is working!";
?>

