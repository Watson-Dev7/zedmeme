<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
$host = 'localhost';
$dbname = 'zedmemes';
$username = 'root';
$password = 'Nawa911?';

try {
    // Create PDO instance
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    
    $pdo = new PDO($dsn, $username, $password, $options);
    echo "Database connection successful!\n\n";
    
    // Test query
    $stmt = $pdo->query('SELECT * FROM users');
    $users = $stmt->fetchAll();
    
    if (count($users) > 0) {
        echo "Users in the database:\n";
        foreach ($users as $user) {
            echo "- ID: {$user['id']}, Username: {$user['username']}, Email: {$user['email']}, Name: {$user['first_name']} {$user['last_name']}\n";
        }
    } else {
        echo "No users found in the database.\n";
    }
    
} catch (PDOException $e) {
    echo "Database connection failed: " . $e->getMessage() . "\n";
    
    // Try to connect without database to check if it's a database-specific issue
    try {
        $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password, $options);
        echo "Connection to MySQL server successful!\n";
        
        // List all databases
        $stmt = $pdo->query("SHOW DATABASES");
        echo "Available databases:\n";
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            echo "- " . $row[0] . "\n";
        }
        
    } catch (PDOException $e2) {
        echo "Connection to MySQL server failed: " . $e2->getMessage() . "\n";
    }
}
