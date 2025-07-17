<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load database configuration
require_once __DIR__ . '/config.php';

echo "<h1>Database Connection Test</h1>";

try {
    // Test database connection
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
    
    echo "<p style='color:green;'>✓ Successfully connected to the database.</p>";
    
    // Get MySQL version
    $version = $pdo->query('SELECT VERSION()')->fetchColumn();
    echo "<p>MySQL Version: " . htmlspecialchars($version) . "</p>";
    
    // Check if users table exists
    $tables = $pdo->query("SHOW TABLES LIKE 'users'")->fetchAll();
    
    if (count($tables) > 0) {
        echo "<p style='color:green;'>✓ Users table exists.</p>";
        
        // Show users table structure
        echo "<h3>Users Table Structure:</h3>";
        $stmt = $pdo->query("DESCRIBE users");
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        while ($row = $stmt->fetch()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Default'] ?? 'NULL') . "</td>";
            echo "<td>" . htmlspecialchars($row['Extra']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Show sample data (first 5 users)
        echo "<h3>Sample User Data (first 5 users):</h3>";
        $users = $pdo->query("SELECT * FROM users LIMIT 5")->fetchAll();
        if (count($users) > 0) {
            echo "<table border='1' cellpadding='5'>";
            // Table header
            echo "<tr>";
            foreach (array_keys($users[0]) as $column) {
                echo "<th>" . htmlspecialchars($column) . "</th>";
            }
            echo "</tr>";
            // Table rows
            foreach ($users as $user) {
                echo "<tr>";
                foreach ($user as $value) {
                    echo "<td>" . htmlspecialchars(substr($value, 0, 50)) . (strlen($value) > 50 ? '...' : '') . "</td>";
                }
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No users found in the database.</p>";
        }
    } else {
        echo "<p style='color:red;'>✗ Users table does not exist.</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color:red;'>✗ Database connection failed: " . htmlspecialchars($e->getMessage()) . "</p>";
    
    // Show connection details (except password)
    echo "<h3>Connection Details:</h3>";
    echo "<ul>";
    echo "<li>Host: " . htmlspecialchars(DB_HOST) . "</li>";
    echo "<li>Database: " . htmlspecialchars(DB_NAME) . "</li>";
    echo "<li>User: " . htmlspecialchars(DB_USER) . "</li>";
    echo "<li>Password: " . (defined('DB_PASS') && DB_PASS ? '***' : 'Not set') . "</li>";
    echo "</ul>";
    
    // Show common solutions
    echo "<h3>Common Solutions:</h3>";
    echo "<ol>";
    echo "<li>Check if MySQL server is running</li>";
    echo "<li>Verify the database credentials in config.php</li>";
    echo "<li>Check if the database exists: <code>CREATE DATABASE IF NOT EXISTS `" . htmlspecialchars(DB_NAME) . "`</code></li>";
    echo "<li>Check if the database user has proper permissions</li>";
    echo "</ol>";
}
?>
