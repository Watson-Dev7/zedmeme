<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load configuration and database
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';

try {
    // Test user credentials
    $testUser = [
        'username' => 'testlogin',
        'email' => 'testlogin@example.com',
        'first_name' => 'Test',
        'last_name' => 'Login',
        'password' => 'Test@1234' // This will be hashed
    ];
    
    // Check if user already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username OR email = :email");
    $stmt->execute([
        ':username' => $testUser['username'],
        ':email' => $testUser['email']
    ]);
    
    if ($stmt->fetch()) {
        echo "Test user already exists.\n";
    } else {
        // Hash the password
        $hashedPassword = password_hash($testUser['password'], PASSWORD_DEFAULT);
        
        // Insert the test user
        $stmt = $pdo->prepare("INSERT INTO users (username, email, first_name, last_name, password, created_at) 
                              VALUES (:username, :email, :first_name, :last_name, :password, NOW())");
        
        $result = $stmt->execute([
            ':username' => $testUser['username'],
            ':email' => $testUser['email'],
            ':first_name' => $testUser['first_name'],
            ':last_name' => $testUser['last_name'],
            ':password' => $hashedPassword
        ]);
        
        if ($result) {
            echo "✅ Test user created successfully!\n";
            echo "Username: " . $testUser['username'] . "\n";
            echo "Password: " . $testUser['password'] . "\n";
            echo "Email: " . $testUser['email'] . "\n";
        } else {
            echo "❌ Failed to create test user\n";
        }
    }
    
    // List all users
    echo "\n📋 Current Users:\n";
    $stmt = $pdo->query("SELECT id, username, email, first_name, last_name, created_at FROM users ORDER BY id");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "ID: " . $row['id'] . ", ";
        echo "Username: " . $row['username'] . ", ";
        echo "Name: " . $row['first_name'] . " " . $row['last_name'] . ", ";
        echo "Email: " . $row['email'] . "\n";
    }
    
} catch (PDOException $e) {
    die("❌ Database error: " . $e->getMessage() . "\n");
}
