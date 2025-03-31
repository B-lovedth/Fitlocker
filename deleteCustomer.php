<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['customer_id'])) {
    $customer_id = $_GET['customer_id'];
    $user_id = $_SESSION['user_id'];
    
    // Verify customer belongs to user
    $stmt = $conn->prepare("DELETE FROM customers WHERE customer_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $customer_id, $user_id);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = 'Customer deleted successfully';
    } else {
        $_SESSION['error'] = 'Error deleting customer';
    }
    
    $stmt->close();
}

header("Location: search.php");
exit();
?>