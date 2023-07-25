<?php

$sname= "54.144.214.46";
$unmae= "root";
$password = "";
$db_name = "LoginSystem";

$conn = mysqli_connect($sname, $unmae, $password, $db_name);

if (!$conn) {
	echo "Connection failed!";
}