<?php
// Enable error reporting but don't display them (we'll log them instead)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/php_errors.log');

// Buffer output to prevent any accidental output
ob_start();

// Set JSON header before any output
header('Content-Type: application/json');

// Load configuration and database
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_save_path('/tmp');
    session_start();
}

// Function to send JSON response and exit
function sendResponse($success, $message, $errors = [], $statusCode = 200) {
    $response = [
        'success' => $success,
        'message' => $message
    ];
    
    if (!empty($errors)) {
        $response['errors'] = $errors;
    }
    
    // Clear any previous output
    ob_clean();
    
    // Set the HTTP status code
    http_response_code($statusCode);
    
    // Output the JSON response
    echo json_encode($response);
    exit;
}

// Log the incoming request (for debugging)
error_log('Signup request data: ' . print_r($_POST, true));

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'Invalid request method.', [], 405);
}

// Verify CSRF token
if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || 
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    sendResponse(false, 'Security validation failed. Please refresh the page and try again.', [], 403);
}

// Get and sanitize input
$inputData = [
    'first_name' => isset($_POST['first_name']) ? htmlspecialchars(trim($_POST['first_name']), ENT_QUOTES, 'UTF-8') : '',
    'last_name' => isset($_POST['last_name']) ? htmlspecialchars(trim($_POST['last_name']), ENT_QUOTES, 'UTF-8') : '',
    'email' => filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL),
    'username' => isset($_POST['username']) ? htmlspecialchars(trim($_POST['username']), ENT_QUOTES, 'UTF-8') : '',
    'password' => $_POST['password'] ?? '',
    'confirm_password' => $_POST['confirm_password'] ?? '',
    'agree' => isset($_POST['Agree'])
];

extract($inputData);

// Log received input
error_log('Processed input data: ' . print_r($inputData, true));

// Validation
$errors = [];

// First name validation
if (empty($first_name)) {
    $errors['first_name'] = 'First name is required';
} elseif (strlen($first_name) > 50) {
    $errors['first_name'] = 'First name is too long';
}

// Last name validation
if (empty($last_name)) {
    $errors['last_name'] = 'Last name is required';
} elseif (strlen($last_name) > 50) {
    $errors['last_name'] = 'Last name is too long';
}

// Email validation
if (empty($email)) {
    $errors['email'] = 'Email is required';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Please enter a valid email address';
} elseif (strlen($email) > 100) {
    $errors['email'] = 'Email is too long';
}

// Username validation
if (empty($username)) {
    $errors['username'] = 'Username is required';
} elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
    $errors['username'] = 'Username can only contain letters, numbers, and underscores';
} elseif (strlen($username) > 30) {
    $errors['username'] = 'Username is too long (max 30 characters)';
}

// Password validation
if (empty($password)) {
    $errors['password'] = 'Password is required';
} elseif (strlen($password) < 8) {
    $errors['password'] = 'Password must be at least 8 characters';
}

// Confirm password validation
if (empty($confirm_password)) {
    $errors['confirm_password'] = 'Please confirm your password';
} elseif ($password !== $confirm_password) {
    $errors['confirm_password'] = 'Passwords do not match';
}

// Agreement checkbox validation
if (!$agree) {
    $errors['agree'] = 'You must agree to the privacy policy';
}

// Log validation errors if any
error_log('Validation errors: ' . print_r($errors, true));

// If there are validation errors, return them
if (!empty($errors)) {
    sendResponse(false, 'Please fix the following errors', $errors, 400);
}

try {
    // Check if email already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
    $stmt->execute([':email' => $email]);
    if ($stmt->fetch()) {
        sendResponse(false, 'Email already registered', ['email' => 'This email is already registered'], 400);
    }
    
    // Check if username already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username");
    $stmt->execute([':username' => $username]);
    if ($stmt->fetch()) {
        sendResponse(false, 'Username already taken', ['username' => 'This username is already taken'], 400);
    }
    
    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert new user
    $stmt = $pdo->prepare("INSERT INTO users (username, first_name, last_name, email, password, created_at) 
                          VALUES (:username, :first_name, :last_name, :email, :password, NOW())");
    
    $result = $stmt->execute([
        ':username' => $username,
        ':first_name' => $first_name,
        ':last_name' => $last_name,
        ':email' => $email,
        ':password' => $hashedPassword
    ]);
    
    if ($result) {
        // Get the new user's ID
        $userId = $pdo->lastInsertId();
        
        // Set session variables
        $_SESSION['user_id'] = $userId;
        $_SESSION['email'] = $email;
        $_SESSION['first_name'] = $first_name;
        
        // Return success response
        sendResponse(true, 'Registration successful!', [], 200, 'blog-simple.php');
    } else {
        throw new Exception('Failed to insert user into database');
    }
    
} catch (PDOException $e) {
    error_log('Database error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
    sendResponse(false, 'A database error occurred. Please try again.', 
        (defined('ENVIRONMENT') && ENVIRONMENT === 'development') 
            ? ['debug' => $e->getMessage()] 
            : [], 
        500);
    
} catch (Exception $e) {
    error_log('Error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
    sendResponse(false, 'An error occurred. Please try again.', 
        (defined('ENVIRONMENT') && ENVIRONMENT === 'development') 
            ? ['debug' => $e->getMessage()] 
            : [], 
        500);
}
