<?php
session_start();
require 'config/db.php';
header('Content-Type: application/json');

try {
    if (!isset($_SESSION['user_id'])) throw new Exception('Login required.');

    $meme_id = intval($_POST['meme_id'] ?? 0);
    $type = $_POST['type'] ?? '';

    if (!$meme_id || !in_array($type, ['like', 'upvote'])) throw new Exception('Invalid request.');

    // Remove previous reaction
    $pdo->prepare('DELETE FROM reactions WHERE user_id = ? AND meme_id = ?')->execute([$_SESSION['user_id'], $meme_id]);
    // Add new reaction
    $pdo->prepare('INSERT INTO reactions (user_id, meme_id, type) VALUES (?, ?, ?)')->execute([$_SESSION['user_id'], $meme_id, $type]);

    // Get updated counts
    $likes = $pdo->prepare('SELECT COUNT(*) FROM reactions WHERE meme_id = ? AND type = "like"');
    $likes->execute([$meme_id]);
    $upvotes = $pdo->prepare('SELECT COUNT(*) FROM reactions WHERE meme_id = ? AND type = "upvote"');
    $upvotes->execute([$meme_id]);

    echo json_encode([
        'status' => 'success',
        'likes' => $likes->fetchColumn(),
        'upvotes' => $upvotes->fetchColumn()
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>