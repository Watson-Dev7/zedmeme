<?php
function isAuthenticated() {
    return isset($_SESSION['user_id']);
}

function requireAuth() {
    if (!isAuthenticated()) {
        http_response_code(401);
        die(json_encode(['status' => 'error', 'message' => 'Authentication required']));
    }
}

function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}
?>