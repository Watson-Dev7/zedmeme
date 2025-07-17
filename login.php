<?php
session_start();
require 'config/db.php';

header('Content-Type: application/json');

try {
    // Validate input
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';
    
    if (!$email || empty($password)) {
        throw new Exception('Invalid email or password');
    }

    // Check user exists
    $stmt = $pdo->prepare('SELECT id, username, password_hash FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // Verify credentials
    if (!$user || !password_verify($password, $user['password_hash'])) {
        throw new Exception('Invalid credentials');
    }

    // Regenerate session ID to prevent fixation
    session_regenerate_id(true);
    
    // Set session data
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['user_ip'] = $_SERVER['REMOTE_ADDR'];
    $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];

    echo json_encode(['status' => 'success', 'username' => $user['username']]);

} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>