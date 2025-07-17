<?php
// Load configuration
require_once 'config.php';

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

    echo "Database connection successful!\n\n";

    // Check required tables
    $requiredTables = ['users', 'memes', 'meme_reactions'];
    $missingTables = [];

    foreach ($requiredTables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() === 0) {
            $missingTables[] = $table;
            echo "❌ Table '$table' is missing\n";
        } else {
            echo "✅ Table '$table' exists\n";
        }
    }

    if (!empty($missingTables)) {
        echo "\nMissing tables: " . implode(', ', $missingTables) . "\n";
        echo "You may need to run the database migration script.\n";
    } else {
        echo "\nAll required tables exist!\n";
    }

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage() . "\n");
}
