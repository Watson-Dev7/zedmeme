<?php
// Test database connection
$host = 'localhost';
$dbname = 'zedmemes';
$username = 'root';
$password = 'Nawa911?';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    echo "Database connection successful!\n";
    
    // Test query
    $stmt = $pdo->query("SELECT VERSION()");
    $version = $stmt->fetchColumn();
    echo "MySQL Version: $version\n";
    
} catch (PDOException $e) {
    echo "Database connection failed: " . $e->getMessage() . "\n";
    
    // Try to connect without database to check if it's a database-specific issue
    try {
        $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
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
