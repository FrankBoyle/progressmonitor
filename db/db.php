<?php
    // Enter your host name, database username, password, and database name.
    // If you have not set database password on localhost then set empty.
    $con = mysqli_connect("localhost:3306","AndersonSchool","SpecialEd69$","AndersonSchool");
    // Check connection
    if (mysqli_connect_errno()){
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }

$query = mysql_query("SELECT * FROM account WHERE email  = '". $email ."'"); 
$emailduplicate = null;
    if (mysql_num_rows($query) > 0) 
    { 
    $emailduplicate = 'Email Address is Already in Use.  Please log-in or reset your password.'; 
    }          


?>


