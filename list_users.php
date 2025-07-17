<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load configuration and database
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';

try {
    // Query to get all users
    $stmt = $pdo->query("SELECT id, username, email, first_name, last_name, created_at FROM users ORDER BY created_at DESC");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($users)) {
        echo "No users found in the database.\n";
    } else {
        echo "\n📋 List of Users (Total: " . count($users) . "):\n";
        echo str_repeat('-', 120) . "\n";
        echo str_pad('ID', 5) . " | " . 
             str_pad('Username', 20) . " | " .
             str_pad('Email', 30) . " | " .
             str_pad('Name', 25) . " | " .
             "Created At\n";
        echo str_repeat('-', 120) . "\n";
        
        foreach ($users as $user) {
            $fullName = trim($user['first_name'] . ' ' . $user['last_name']);
            echo str_pad($user['id'], 5) . " | " . 
                 str_pad($user['username'], 20) . " | " .
                 str_pad($user['email'], 30) . " | " .
                 str_pad($fullName, 25) . " | " .
                 $user['created_at'] . "\n";
        }
    }
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
}
