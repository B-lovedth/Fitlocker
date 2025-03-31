<?php
session_start();
require_once 'db_connect.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo 'error';
    exit();
}

// Get the logged-in user's ID
$user_id = $_SESSION['user_id'];

// Prepare and execute the deletion query
$stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
if ($stmt->execute()) {
    // Destroy the session after successful deletion
    session_destroy();
    echo 'success';
} else {
    echo 'error';
}
$stmt->close();
?>