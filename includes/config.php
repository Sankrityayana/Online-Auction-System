<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_PORT', '3307');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'online_auction_system');

// Site Configuration
define('SITE_NAME', 'Online Auction System');
define('SITE_URL', 'http://localhost');
define('UPLOAD_DIR', __DIR__ . '/../images/auctions/');
define('MAX_FILE_SIZE', 5242880); // 5MB

// Auction Settings
define('MIN_BID_INCREMENT', 5.00);
define('AUCTION_EXTENSION_TIME', 300); // 5 minutes in seconds
define('MAX_AUCTION_DURATION', 30); // days
define('AUTO_BID_ENABLED', true);
define('BID_NOTIFICATION_INTERVAL', 10); // seconds

// Session Configuration
session_start();

// Database Connection
try {
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Error Reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
