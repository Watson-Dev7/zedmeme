<?php
session_start();
require_once '../includes/auth_check.php';
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $memeId = filter_input(INPUT_POST, 'meme_id', FILTER_VALIDATE_INT);
    $comment = filter_input(INPUT_POST, 'comment', FILTER_SANITIZE_STRING);
    $userId = $_SESSION['user_id'];
    
    if (!$memeId || empty($comment)) {
        echo json_encode(['success' => false, 'message' => 'Invalid comment']);
        exit;
    }
    
    try {
        // Check if meme exists
        $stmt = $pdo->prepare("SELECT id FROM memes WHERE id = :meme_id");
        $stmt->execute([':meme_id' => $memeId]);
        
        if (!$stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Meme not found']);
            exit;
        }
        
        // Insert comment
        $stmt = $pdo->prepare("INSERT INTO comments (meme_id, user_id, comment, created_at) 
                              VALUES (:meme_id, :user_id, :comment, NOW())");
        $stmt->execute([
            ':meme_id' => $memeId,
            ':user_id' => $userId,
            ':comment' => $comment
        ]);
        
        // Get username for response
        $stmt = $pdo->prepare("SELECT username FROM users WHERE id = :user_id");
        $stmt->execute([':user_id' => $userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'username' => $user['username'],
            'comment' => htmlspecialchars($comment, ENT_QUOTES, 'UTF-8')
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>