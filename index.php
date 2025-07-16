<?php
session_start();

// Define base URL
$base_url = 'http://' . $_SERVER['HTTP_HOST'] . '/zedmemes/';

// Redirect logic
if (isset($_SESSION['user_id'])) {
    header('Location: ' . $base_url . 'pages/blog-simple.php');
} else {
    header('Location: ' . $base_url . 'pages/login.php');
}
exit;
?>