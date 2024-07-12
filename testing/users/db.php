<?php

    define('USER', 'ifnuhymnak_AndersonSchool');
    define('PASSWORD', 'SpecialEd69$');
    define('HOST', '127.0.0.1');
    define('DATABASE', 'ifnuhymnak_IEP_Report');

    try {
        $connection = new PDO("mysql:host=".HOST.";dbname=".DATABASE, USER, PASSWORD);
        $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        error_log("Database connection established successfully.");
    } catch (PDOException $e) {
        error_log("Database connection failed: " . $e->getMessage());
        exit("Error: " . $e->getMessage());
    }
    ?>

