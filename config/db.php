<?php
// ============================================================
// MIRAE Admin Dashboard - Database Configuration
// File: config/db.php
// ============================================================

// Use Railway environment variables, fallback to local defaults for XAMPP
define('DB_HOST', getenv('MYSQLHOST') ?: 'localhost');
define('DB_NAME', getenv('MYSQLDATABASE') ?: 'mirae_db');
define('DB_USER', getenv('MYSQLUSER') ?: 'root');
define('DB_PASS', getenv('MYSQLPASSWORD') ?: ''); 
define('DB_PORT', getenv('MYSQLPORT') ?: '3306');
define('DB_CHARSET', 'utf8mb4');

// Create connection
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
    if ($conn->connect_error) {
        throw new Exception($conn->connect_error);
    }
    $conn->set_charset(DB_CHARSET);
} catch (Exception $e) {
    // If it's an API request, return JSON
    if (strpos($_SERVER['REQUEST_URI'], '-api.php') !== false) {
        header('Content-Type: application/json');
        die(json_encode([
            'error' => true,
            'message' => 'Database connection failed: ' . $e->getMessage()
        ]));
    }
    // Otherwise show a clean error
    die("Database Connection Error. Please check your hosting environment variables.");
}
?>
