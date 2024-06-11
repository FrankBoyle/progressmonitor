<?php

    define('USER', 'AndersonSchool');
    define('PASSWORD', 'SpecialEd69$');
    define('HOST', '127.0.0.1');
    define('DATABASE', 'bFactor-test');

    try {
        $connection = new PDO("mysql:host=".HOST.";dbname=".DATABASE, USER, PASSWORD);
        $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        error_log("Database connection established successfully.");
    } catch (PDOException $e) {
        error_log("Database connection failed: " . $e->getMessage());
        exit("Error: " . $e->getMessage());
    }

        /*



    *////////
    ?>


