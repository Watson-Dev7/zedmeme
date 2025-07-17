<?php
// Load configuration
require_once __DIR__ . '/../config.php';

// Create database connection
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );

    // Create database if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `" . DB_NAME . "`");

    // Create users table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `users` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `username` VARCHAR(50) NOT NULL UNIQUE,
            `email` VARCHAR(100) NOT NULL UNIQUE,
            `password` VARCHAR(255) NOT NULL,
            `profile_image` VARCHAR(255) DEFAULT 'assets/img/default-profile.png',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // Create memes table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `memes` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `user_id` INT NOT NULL,
            `title` VARCHAR(255),
            `image_url` VARCHAR(255) NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // Create meme_reactions table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `meme_reactions` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `meme_id` INT NOT NULL,
            `user_id` INT NOT NULL,
            `action` ENUM('like', 'download') NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY `unique_reaction` (`meme_id`, `user_id`, `action`),
            FOREIGN KEY (`meme_id`) REFERENCES `memes`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // Create comments table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `comments` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `meme_id` INT NOT NULL,
            `user_id` INT NOT NULL,
            `content` TEXT NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (`meme_id`) REFERENCES `memes`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    echo "✅ Database migration completed successfully!\n";

    // Add a test user if none exists
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM `users`");
    $userCount = $stmt->fetch()['count'];

    if ($userCount === 0) {
        $password = password_hash('test123', PASSWORD_DEFAULT);
        $pdo->exec("INSERT INTO `users` (`username`, `email`, `password`) VALUES ('testuser', 'test@example.com', '$password')");
        echo "✅ Added test user (username: testuser, password: test123)\n";
    }

    // Add test memes if none exist
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM `memes`");
    $memeCount = $stmt->fetch()['count'];

    if ($memeCount === 0) {
        $pdo->exec("
            INSERT INTO `memes` (`user_id`, `title`, `image_url`) VALUES 
            (1, 'Funny Cat', 'assets/img/placeholder.php?text=Cat+Meme'),
            (1, 'Doge', 'assets/img/placeholder.php?text=Doge+Meme'),
            (1, 'Success Kid', 'assets/img/placeholder.php?text=Success+Kid')
        ");
        echo "✅ Added test memes\n";
    }

} catch (PDOException $e) {
    die("❌ Database migration failed: " . $e->getMessage() . "\n");
}

echo "\nSetup complete!\n";
