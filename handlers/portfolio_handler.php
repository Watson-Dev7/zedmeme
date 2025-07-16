<?php
require_once '../includes/auth_check.php';
require_once '../includes/db.php';

// Get user profile data
$userId = $_SESSION['user_id'];

try {
    // Get user info
    $stmt = $pdo->prepare("SELECT id, first_name, last_name, email, username, bio, profile_image, created_at FROM users WHERE id = :id");
    $stmt->execute([':id' => $userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        die('User not found');
    }
    
    // Get user's memes
    $stmt = $pdo->prepare("SELECT id, image_url, title, created_at FROM memes WHERE user_id = :user_id ORDER BY created_at DESC");
    $stmt->execute([':user_id' => $userId]);
    $memes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Prepare data for the view
    $profileData = [
        'user' => $user,
        'memes' => $memes
    ];
    
    // Return JSON if AJAX request
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode($profileData);
        exit;
    }
    
    // For regular page load, the portfolio.php view will handle displaying the data
    
} catch (PDOException $e) {
    die('Database error: ' . $e->getMessage());
}
?>