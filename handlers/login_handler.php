<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/php_errors.log');

// Start output buffering
ob_start();

// Set JSON header
header('Content-Type: application/json');

// Set custom session path
$sessionPath = __DIR__ . '/../../tmp/sessions';
if (!file_exists($sessionPath)) {
    mkdir($sessionPath, 0777, true);
}

// Set session configuration
ini_set('session.save_path', $sessionPath);
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Function to send JSON response
function sendJsonResponse($success, $message = '', $data = null, $statusCode = 200) {
    $response = [
        'success' => $success,
        'message' => $message
    ];
    
    if ($data !== null) {
        $response['data'] = $data;
    }
    
    // Clear any previous output
    ob_clean();
    
    // Set status code
    http_response_code($statusCode);
    
    // Send JSON response
    echo json_encode($response);
    exit;
}

// Load database connection
require_once __DIR__ . '/../includes/db.php';

try {
    // Check request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendJsonResponse(false, 'Invalid request method', null, 405);
    }

    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || 
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        sendJsonResponse(false, 'Invalid or expired CSRF token. Please refresh the page and try again.', null, 403);
    }

    // Get and validate input
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username)) {
        sendJsonResponse(false, 'Username or email is required', ['field' => 'username'], 400);
    }

    if (empty($password)) {
        sendJsonResponse(false, 'Password is required', ['field' => 'password'], 400);
    }

    // Find user by username or email
    $stmt = $pdo->prepare("
        SELECT id, username, email, password
        FROM users 
        WHERE username = :username OR email = :username
        LIMIT 1
    ");
    
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verify user exists and password is correct
    if (!$user || !password_verify($password, $user['password'])) {
        // Log failed login attempt
        error_log("Failed login attempt for username/email: $username");
        sendJsonResponse(false, 'Invalid username or password', null, 401);
    }

    // Set session variables
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email'] = $user['email'];
    
    // Set optional fields if they exist
    $optionalFields = ['first_name', 'last_name', 'profile_image'];
    foreach ($optionalFields as $field) {
        if (isset($user[$field])) {
            $_SESSION[$field] = $user[$field];
        }
    }

    // Regenerate session ID to prevent session fixation
    session_regenerate_id(true);

    // Log successful login
    error_log("User {$user['username']} (ID: {$user['id']}) logged in successfully");

    // Return success response with redirect to blog.php
    sendJsonResponse(true, 'Login successful', [
        'redirect' => '../pages/blog.php'
    ]);

} catch (PDOException $e) {
    error_log("Database error in login handler: " . $e->getMessage());
    sendJsonResponse(false, 'A database error occurred. Please try again later.', null, 500);
} catch (Exception $e) {
    error_log("Error in login handler: " . $e->getMessage());
    sendJsonResponse(false, 'An unexpected error occurred. Please try again.', null, 500);
}
?>