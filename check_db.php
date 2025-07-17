<?php
try {
    $pdo = new PDO('mysql:host=localhost;charset=utf8mb4', 'root', 'Nawa911?', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // Check if database exists
    $databases = $pdo->query('SHOW DATABASES')->fetchAll(PDO::FETCH_COLUMN);
    if (in_array('zedmemes', $databases)) {
        echo "Database 'zedmemes' exists.\n";
        
        // Check if users table exists
        $pdo->exec('USE zedmemes');
        $tables = $pdo->query('SHOW TABLES LIKE "users"')->fetchAll();
        if (count($tables) > 0) {
            echo "Users table exists.\n";
            
            // Count users
            $stmt = $pdo->query('SELECT COUNT(*) FROM users');
            $count = $stmt->fetchColumn();
            echo "Number of users: $count\n";
            
            // Show first user if exists
            if ($count > 0) {
                $user = $pdo->query('SELECT * FROM users LIMIT 1')->fetch(PDO::FETCH_ASSOC);
                echo "First user email: " . ($user['email'] ?? 'N/A') . "\n";
            }
        } else {
            echo "Users table does not exist.\n";
        }
    } else {
        echo "Database 'zedmemes' does not exist.\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
