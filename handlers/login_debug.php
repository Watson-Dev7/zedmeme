<?php
// Enable all error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/php_errors.log');

// Start output buffering
ob_start();

// Set JSON header
header('Content-Type: application/json');

// Simple error handler
function handleError($errno, $errstr, $errfile, $errline) {
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
}
set_error_handler('handleError');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_save_path('/tmp');
    session_start();
}

// Simple response function
function sendResponse($success, $message = '', $data = null, $statusCode = 200) {
    $response = ['success' => $success];
    if ($message) $response['message'] = $message;
    if ($data !== null) $response['data'] = $data;
    
    ob_clean();
    http_response_code($statusCode);
    echo json_encode($response);
    exit;
}

try {
    // Check request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendResponse(false, 'Invalid request method', null, 405);
    }

    // Load database connection
    require_once __DIR__ . '/../includes/db.php';
    
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || 
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        sendResponse(false, 'Invalid or expired CSRF token', null, 403);
    }

    // Get and validate input
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username)) {
        sendResponse(false, 'Username or email is required', ['field' => 'username'], 400);
    }

    if (empty($password)) {
        sendResponse(false, 'Password is required', ['field' => 'password'], 400);
    }

    // Find user by username or email
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username OR email = :username LIMIT 1");
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verify user exists and password is correct
    if (!$user) {
        sendResponse(false, 'User not found', null, 401);
    }

    if (!password_verify($password, $user['password'])) {
        sendResponse(false, 'Invalid password', null, 401);
    }

    // Set session variables
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email'] = $user['email'];
    
    if (isset($user['first_name'])) $_SESSION['first_name'] = $user['first_name'];
    if (isset($user['last_name'])) $_SESSION['last_name'] = $user['last_name'];

    // Regenerate session ID to prevent session fixation
    session_regenerate_id(true);

    // Return success response
    sendResponse(true, 'Login successful', [
        'redirect' => 'blog.php'
    ]);

} catch (Throwable $e) {
    // Log the error
    error_log("Login Error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
    
    // Return detailed error in development, generic in production
    $errorMessage = (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false || $_SERVER['SERVER_NAME'] === 'localhost')
        ? $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine()
        : 'An error occurred during login';
        
    sendResponse(false, $errorMessage, null, 500);
}
