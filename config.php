<?php
    define('USER', 'fboyle');
    define('PASSWORD', 'Fjb08084$&');
    define('HOST', '54.144.214.46');
    define('DATABASE', 'LoginSystem');
    try {
        $connection = new PDO("mysql:host=".HOST.";dbname=".DATABASE, USER, PASSWORD);
    } catch (PDOException $e) {
        exit("Error: " . $e->getMessage());
    }
?>