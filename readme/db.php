<?php
// ============================================================
// MIRAE Admin Dashboard - Database Configuration
// File: config/db.php
// ============================================================

define('DB_HOST',     'localhost');
define('DB_NAME',     'mirae_db');
define('DB_USER',     'root');
define('DB_PASS',     '');       // Default XAMPP has no password
define('DB_CHARSET',  'utf8mb4');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Set charset
$conn->set_charset(DB_CHARSET);

// Check connection
if ($conn->connect_error) {
    die(json_encode([
        'error' => true,
        'message' => 'Database connection failed: ' . $conn->connect_error
    ]));
}
?>
