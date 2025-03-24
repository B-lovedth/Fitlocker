<?php
$host = 'localhost';
$dbname = 'fitlockerdb';
$db_username = 'root'; // Changed to avoid conflict with signup.php
$db_password = '';     // Changed for consistency

try {
    $conn = new mysqli($host, $db_username, $db_password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
} catch (Exception $e) {
    die("Database connection error: " . $e->getMessage());
}
?>