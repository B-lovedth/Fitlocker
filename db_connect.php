<?php
$host = 'localhost';
$dbname = 'fitlockerdb';
$db_username = 'root';
$db_password = '';  

try {
    $conn = new mysqli($host, $db_username, $db_password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
} catch (Exception $e) {
    die("Database connection error: " . $e->getMessage());
}
?>