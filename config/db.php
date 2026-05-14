<?php
// ============================================================
// MIRAE Admin Dashboard - Database Configuration
// File: config/db.php
// ============================================================

// Flexible Database Configuration
$is_production = (strpos($_SERVER['HTTP_HOST'], 'infinityfreeapp.com') !== false);

if ($is_production) {
    define('DB_HOST', 'sqlXXX.infinityfree.com'); // REPLACE XXX with your actual host from dashboard (e.g. sql205)
    define('DB_NAME', 'if0_41920022_mirae_db');   // Your DB name
    define('DB_USER', 'if0_41920022');            // Your Username
    define('DB_PASS', 'iINHtgjGvajF');            // Your Password
    define('DB_PORT', '3306');
} else {
    // Local XAMPP or Railway Fallback
    define('DB_HOST', getenv('MYSQLHOST') ?: getenv('DB_HOST') ?: 'localhost');
    define('DB_NAME', getenv('MYSQLDATABASE') ?: getenv('DB_NAME') ?: 'mirae_db');
    define('DB_USER', getenv('MYSQLUSER') ?: getenv('DB_USER') ?: 'root');
    define('DB_PASS', getenv('MYSQLPASSWORD') ?: getenv('DB_PASS') ?: ''); 
    define('DB_PORT', getenv('MYSQLPORT') ?: getenv('DB_PORT') ?: '3306');
}
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
