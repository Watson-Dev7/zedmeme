<?php
// Load configuration
require_once __DIR__ . '/../config.php';

try {
    // Create PDO instance
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    
} catch (PDOException $e) {
    error_log('Database connection failed: ' . $e->getMessage());
    if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
        die('Database connection failed: ' . $e->getMessage());
    } else {
        die('Database connection failed. Please try again later.');
    }
}
?>