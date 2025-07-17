<?php
// Start session with custom path to avoid permission issues
session_save_path('/tmp');
session_start();

// Debug output
// echo "Session ID: " . session_id() . "<br>";
// echo "Session data: ";
// print_r($_SESSION);

// Define base URL - make sure this matches your server setup
$base_url = ''; // Empty string for relative paths

// Get the current page
$current_page = $_SERVER['REQUEST_URI'];

// Get the current script name to prevent redirect loops
$current_script = basename($_SERVER['SCRIPT_NAME']);

// Prevent redirect loops
if ($current_script === 'login.php' || strpos($current_page, 'login.php') !== false) {
    // If we're already on the login page, don't redirect
    return;
} 

if (isset($_SESSION['user_id'])) {
    // If logged in, go to blog
    if ($current_script !== 'blog-simple.php') {
        header('Location: pages/blog.php');
        exit;
    }
} else {
    // If not logged in, go to login
    if ($current_script !== 'login.php') {
        header('Location: pages/login.php');
        exit;
    }
}
?>