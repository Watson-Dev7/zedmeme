<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=zedmemes;charset=utf8mb4', 'root', 'Nawa911?', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    $stmt = $pdo->query('SELECT id, username, email, first_name, last_name FROM users');
    $users = $stmt->fetchAll();

    echo "Users in the database:\n";
    foreach ($users as $user) {
        echo "- ID: {$user['id']}, Username: {$user['username']}, Email: {$user['email']}, Name: {$user['first_name']} {$user['last_name']}\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
