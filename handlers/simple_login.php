<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/php_errors.log');

// Start session
$sessionPath = __DIR__ . '/../../tmp/sessions';
if (!file_exists($sessionPath)) {
    mkdir($sessionPath, 0777, true);
}
session_save_path($sessionPath);
session_start();

// Simple JSON response function
function sendJsonResponse($success, $message = '', $data = null, $httpCode = 200) {
    http_response_code($httpCode);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

// Load database configuration
require_once __DIR__ . '/../config.php';

try {
    // Check request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendJsonResponse(false, 'Invalid request method', null, 405);
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

    // Database connection
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASSWORD,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );

    // Find user by username or email
    $stmt = $pdo->prepare("SELECT id, username, email, password FROM users WHERE username = :username OR email = :email LIMIT 1");
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':email', $username);
    $stmt->execute();
    $user = $stmt->fetch();

    // Verify user exists and password is correct
    if (!$user || !password_verify($password, $user['password'])) {
        error_log("Failed login attempt for username/email: $username");
        sendJsonResponse(false, 'Invalid username or password', null, 401);
    }

    // Set session variables
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email'] = $user['email'];

    // Regenerate session ID to prevent session fixation
    session_regenerate_id(true);

    // Success response
    sendJsonResponse(true, 'Login successful', [
        'redirect' => '../pages/blog.php'
    ]);

} catch (PDOException $e) {
    error_log("Database error in simple login handler: " . $e->getMessage());
    sendJsonResponse(false, 'A database error occurred. Please try again later.', null, 500);
} catch (Exception $e) {
    error_log("Error in simple login handler: " . $e->getMessage());
    sendJsonResponse(false, 'An error occurred. Please try again.', null, 500);
}
?>
