<?php
session_start();
require 'config/db.php';

header('Content-Type: application/json');

try {
    // Pagination settings
    $page = max(1, intval($_GET['page'] ?? 1));
    $limit = 9;
    $offset = ($page - 1) * $limit;

    // Get current user ID if authenticated
    $currentUserId = $_SESSION['user_id'] ?? null;

    // Base query to get memes with user reactions
    $sql = "
        SELECT 
            m.id,
            m.filename,
            m.title,
            m.created_at,
            u.username,
            " . ($currentUserId ? "
            (SELECT r.type FROM reactions r 
             WHERE r.meme_id = m.id AND r.user_id = ? 
             LIMIT 1) as user_reacted
            " : "NULL as user_reacted") . "
        FROM memes m
        JOIN users u ON m.user_id = u.id
        ORDER BY m.created_at DESC
        LIMIT ? OFFSET ?
    ";

    // Prepare and execute query
    $stmt = $pdo->prepare($sql);
    $params = $currentUserId ? [$currentUserId, $limit, $offset] : [$limit, $offset];
    $stmt->execute($params);
    $memes = $stmt->fetchAll();

    // Get total count for pagination
    $countStmt = $pdo->query("SELECT COUNT(*) FROM memes");
    $totalMemes = $countStmt->fetchColumn();
    $totalPages = ceil($totalMemes / $limit);

    // Format response
    $response = [
        'status' => 'success',
        'memes' => $memes,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_memes' => $totalMemes,
            'per_page' => $limit
        ],
        'username' => $_SESSION['username'] ?? null
    ];

    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>