<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=zedmemes;charset=utf8mb4', 'root', 'Nawa911?', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    // Check if test user already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute(['test@example.com']);
    
    if ($stmt->fetch()) {
        echo "Test user already exists.\n";
    } else {
        // Create test user
        $hashedPassword = password_hash('test123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("
            INSERT INTO users (username, first_name, last_name, email, password, created_at) 
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            'testuser',
            'Test',
            'User',
            'test@example.com',
            $hashedPassword
        ]);
        
        echo "Test user created successfully!\n";
        echo "Email: test@example.com\n";
        echo "Password: test123\n";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    
    // If there's an error with the users table, show the table structure
    if ($e->getCode() == '42S02') { // Table doesn't exist
        echo "\nChecking database structure...\n";
        try {
            $pdo = new PDO('mysql:host=localhost;dbname=zedmemes;charset=utf8mb4', 'root', 'Nawa911?');
            $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
            echo "Tables in database: " . implode(', ', $tables) . "\n";
            
            if (in_array('users', $tables)) {
                echo "\nUsers table structure:\n";
                $columns = $pdo->query('DESCRIBE users')->fetchAll();
                foreach ($columns as $col) {
                    echo "- {$col['Field']} ({$col['Type']})\n";
                }
            }
        } catch (PDOException $e2) {
            echo "Could not check database structure: " . $e2->getMessage() . "\n";
        }
    }
}
