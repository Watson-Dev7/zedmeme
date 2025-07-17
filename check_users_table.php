<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load database configuration
require_once __DIR__ . '/config.php';

// Database connection
try {
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

    echo "<h2>Users Table Structure</h2>";
    
    // Get table structure
    $stmt = $pdo->query("DESCRIBE users");
    $columns = $stmt->fetchAll();
    
    echo "<table border='1' cellpadding='5' cellspacing='0'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($column['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Default'] ?? 'NULL') . "</td>";
        echo "<td>" . htmlspecialchars($column['Extra']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Show sample data
    echo "<h2>Sample User Data</h2>";
    $stmt = $pdo->query("SELECT id, username, email, created_at FROM users LIMIT 5");
    $users = $stmt->fetchAll();
    
    if (count($users) > 0) {
        echo "<table border='1' cellpadding='5' cellspacing='0'>";
        echo "<tr>";
        foreach (array_keys($users[0]) as $header) {
            echo "<th>" . htmlspecialchars($header) . "</th>";
        }
        echo "</tr>";
        
        foreach ($users as $user) {
            echo "<tr>";
            foreach ($user as $value) {
                echo "<td>" . htmlspecialchars($value) . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No users found in the database.</p>";
    }
    
    // Check if password field exists and is hashed
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'password'");
    if ($stmt->rowCount() === 0) {
        echo "<div style='color:red;'>Error: 'password' column not found in users table!</div>";
    } else {
        // Check if any passwords are not hashed
        $stmt = $pdo->query("SELECT id, username, password FROM users WHERE LENGTH(password) < 60 LIMIT 5");
        $unhashed = $stmt->fetchAll();
        
        if (count($unhashed) > 0) {
            echo "<div style='color:orange;'>Warning: Found " . count($unhashed) . " users with unhashed passwords!</div>";
            echo "<p>You should update these passwords to use proper password hashing.</p>";
        } else {
            echo "<div style='color:green;'>All passwords appear to be properly hashed.</div>";
        }
    }
    
} catch (PDOException $e) {
    die("<div style='color:red;'>Database error: " . htmlspecialchars($e->getMessage()) . "</div>");
}
?>
