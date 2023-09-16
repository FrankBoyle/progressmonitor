<?php

    define('USER', 'AndersonSchool');
    define('PASSWORD', 'SpecialEd69$');
    define('HOST', 'localhost');
    define('DATABASE', 'bfactor-test');

    try {
        $connection = new PDO("mysql:host=".HOST.";dbname=".DATABASE, USER, PASSWORD);
    } catch (PDOException $e) {
        exit("Error: " . $e->getMessage());

    }

?>
