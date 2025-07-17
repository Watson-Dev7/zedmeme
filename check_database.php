<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load configuration and database
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';

try {
    // Check if database connection is working
    $pdo->query('SELECT 1');
    echo "✅ Database connection successful\n";
    
    // Check if users table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Users table exists\n";
        
        // Show table structure
        $stmt = $pdo->query("DESCRIBE users");
        echo "\n📋 Users table structure:\n";
        echo str_pad('Field', 20) . str_pad('Type', 20) . str_pad('Null', 10) . str_pad('Key', 10) . str_pad('Default', 15) . "Extra\n";
        echo str_repeat('-', 75) . "\n";
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo str_pad($row['Field'], 20) . 
                 str_pad($row['Type'], 20) . 
                 str_pad($row['Null'], 10) . 
                 str_pad($row['Key'], 10) . 
                 str_pad($row['Default'] ?? 'NULL', 15) . 
                 $row['Extra'] . "\n";
        }
    } else {
        echo "❌ Users table does not exist\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    if (strpos($e->getMessage(), 'Unknown database') !== false) {
        echo "\n💡 Try creating the database first with: CREATE DATABASE `zedmemes`;\n";
    }
}
