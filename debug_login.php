<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
$sessionPath = __DIR__ . '/tmp/sessions';
if (!file_exists($sessionPath)) {
    mkdir($sessionPath, 0777, true);
}
session_save_path($sessionPath);
session_start();

// Load database configuration
require_once __DIR__ . '/config.php';

// Database connection
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );

    // Test user credentials
    $testUsername = 'testuser';
    $testPassword = 'test123';

    echo "<h2>Testing Login Process</h2>";
    
    // 1. Check if test user exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username OR email = :email LIMIT 1");
    $stmt->execute([':username' => $testUsername, ':email' => $testUsername]);
    $user = $stmt->fetch();

    if (!$user) {
        echo "<p>Test user not found. Creating test user...</p>";
        // Create test user
        $hashedPassword = password_hash($testPassword, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$testUsername, 'test@example.com', $hashedPassword]);
        echo "<p>Test user created successfully.</p>";
        $userId = $pdo->lastInsertId();
    } else {
        echo "<p>Test user found in database.</p>";
        $userId = $user['id'];
    }

    // 2. Test login query
    echo "<h3>Testing login query...</h3>";
    $loginStmt = $pdo->prepare("SELECT id, username, email, password FROM users WHERE username = :username OR email = :username LIMIT 1");
    $loginStmt->execute([':username' => $testUsername]);
    $loginUser = $loginStmt->fetch();

    if ($loginUser) {
        echo "<p>Login query successful. Found user: " . htmlspecialchars($loginUser['username']) . "</p>";
        
        // Test password verification
        if (password_verify($testPassword, $loginUser['password'])) {
            echo "<p style='color:green;'>Password verification successful!</p>";
            
            // Test session
            $_SESSION['user_id'] = $loginUser['id'];
            $_SESSION['username'] = $loginUser['username'];
            $_SESSION['email'] = $loginUser['email'];
            
            echo "<p>Session variables set:</p>";
            echo "<pre>" . print_r($_SESSION, true) . "</pre>";
            
            echo "<p style='color:green; font-weight:bold;'>Login test successful! You can now try logging in through the form.</p>";
        } else {
            echo "<p style='color:red;'>Password verification failed!</p>";
            echo "<p>Stored hash: " . htmlspecialchars($loginUser['password']) . "</p>";
            echo "<p>Test password: $testPassword</p>";
        }
    } else {
        echo "<p style='color:red;'>Login query failed to find user.</p>";
    }

} catch (PDOException $e) {
    echo "<h2>Database Error</h2>";
    echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Error Code: " . $e->getCode() . "</p>";
    
    // Show the SQL query that caused the error if available
    if (isset($loginStmt)) {
        echo "<h3>SQL Query:</h3>";
        echo "<pre>" . $loginStmt->queryString . "</pre>";
    }
    
    echo "<h3>Backtrace:</h3>";
    echo "<pre>" . htmlspecialchars(print_r($e->getTraceAsString(), true)) . "</pre>";
}

// Show session info
echo "<h2>Session Information</h2>";
echo "<pre>" . print_r($_SESSION, true) . "</pre>";

echo "<p><a href='pages/login.php'>Go to login page</a></p>";
?>
