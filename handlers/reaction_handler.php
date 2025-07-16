<?php
session_start();
require_once '../includes/auth_check.php';
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $memeId = filter_input(INPUT_POST, 'meme_id', FILTER_VALIDATE_INT);
    $action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_STRING);
    $userId = $_SESSION['user_id'];
    
    if (!$memeId || !in_array($action, ['like', 'download'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid request']);
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
        
        // Check if user already reacted
        $stmt = $pdo->prepare("SELECT id FROM meme_reactions WHERE meme_id = :meme_id AND user_id = :user_id AND action = :action");
        $stmt->execute([
            ':meme_id' => $memeId,
            ':user_id' => $userId,
            ':action' => $action
        ]);
        
        if ($stmt->fetch()) {
            // User already reacted, remove reaction
            $stmt = $pdo->prepare("DELETE FROM meme_reactions WHERE meme_id = :meme_id AND user_id = :user_id AND action = :action");
            $stmt->execute([
                ':meme_id' => $memeId,
                ':user_id' => $userId,
                ':action' => $action
            ]);
            
            $increment = -1;
        } else {
            // Add new reaction
            $stmt = $pdo->prepare("INSERT INTO meme_reactions (meme_id, user_id, action, created_at) 
                                  VALUES (:meme_id, :user_id, :action, NOW())");
            $stmt->execute([
                ':meme_id' => $memeId,
                ':user_id' => $userId,
                ':action' => $action
            ]);
            
            $increment = 1;
        }
        
        // Get updated count
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM meme_reactions WHERE meme_id = :meme_id AND action = :action");
        $stmt->execute([
            ':meme_id' => $memeId,
            ':action' => $action
        ]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'count' => $result['count'],
            'increment' => $increment
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>