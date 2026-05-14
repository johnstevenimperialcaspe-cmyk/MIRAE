<?php
session_start();
require_once '../config/db.php';

if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    $ip = $_SERVER['REMOTE_ADDR'];
    
    // Update last logout
    $conn->query("UPDATE customers SET last_logout = CURRENT_TIMESTAMP WHERE id = $userId");
    
    // Log activity
    $stmt = $conn->prepare("INSERT INTO login_logs (user_type, user_id, action, ip_address) VALUES ('customer', ?, 'logout', ?)");
    $stmt->bind_param('is', $userId, $ip);
    $stmt->execute();
    $stmt->close();
}

session_destroy();
header('Location: login.php');
exit();
