<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_save_path('/tmp');
    session_start();
}

// Generate CSRF token if it doesn't exist
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Get the current page and script name
$current_page = $_SERVER['REQUEST_URI'] ?? '';
$current_script = basename($_SERVER['SCRIPT_NAME'] ?? '');

// Only redirect if we're not already on the login or signup page
if (!isset($_SESSION['user_id'])) {
    if ($current_script !== 'login.php' && $current_script !== 'signup.php') {
        header('Location: login.php');
        exit;
    }
}
