<?php

    define('USER', 'AndersonSchool');
    define('PASSWORD', 'SepcialEd69$');
    define('HOST', 'localhost');
    define('DATABASE', 'AndersonSchool');

    try {
        $connection = new PDO("mysql:host=".HOST.";dbname=".DATABASE, USER, PASSWORD);
    } catch (PDOException $e) {
        exit("Error: " . $e->getMessage());

    }

?>
