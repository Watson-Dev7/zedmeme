<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session with custom path
$sessionPath = __DIR__ . '/tmp/sessions';
if (!file_exists($sessionPath)) {
    mkdir($sessionPath, 0777, true);
}
session_save_path($sessionPath);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once __DIR__ . '/includes/db.php';

// Test user credentials
$testUsername = 'testuser';
$testPassword = 'test123';

// Output HTML with some styling
echo "<html><head><style>
    body { font-family: Arial, sans-serif; line-height: 1.6; margin: 20px; }
    .success { color: green; margin: 10px 0; padding: 10px; background: #e8f5e9; border-left: 4px solid #4caf50; }
    .error { color: #d32f2f; margin: 10px 0; padding: 10px; background: #ffebee; border-left: 4px solid #f44336; }
    .info { color: #1976d2; margin: 10px 0; padding: 10px; background: #e3f2fd; border-left: 4px solid #2196f3; }
    pre { background: #f5f5f5; padding: 10px; border-radius: 4px; overflow-x: auto; }
</style></head><body>";

echo "<h2>Login Test</h2>";

try {
    // Test database connection
    $pdo->query('SELECT 1');
    echo "<div class='success'>✅ Database connection successful!</div>";
    
    // Check if users table exists
    $usersTable = $pdo->query("SHOW TABLES LIKE 'users'")->rowCount();
    if ($usersTable === 0) {
        throw new Exception("Users table does not exist");
    }
    echo "<div class='success'>✅ Users table exists</div>";
    
    // Check if test user exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$testUsername]);
    $user = $stmt->fetch();
    
    if (!$user) {
        // Create test user if doesn't exist
        $hashedPassword = password_hash($testPassword, PASSWORD_DEFAULT);
        $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)")
           ->execute([$testUsername, 'test@example.com', $hashedPassword]);
        echo "<div class='info'>ℹ️ Created test user: $testUsername / $testPassword</div>";
        $user = ['id' => $pdo->lastInsertId(), 'username' => $testUsername, 'password' => $hashedPassword];
    } else {
        echo "<div class='success'>✅ Test user found: $testUsername</div>";
    }
    
    // Test login
    if (password_verify($testPassword, $user['password'])) {
        echo "<div class='success'>✅ Password verification successful!</div>";
        
        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        
        // Test session
        if (isset($_SESSION['user_id'])) {
            echo "<div class='success'>✅ Session set successfully!</div>";
            echo "<div class='info'>User ID: " . $_SESSION['user_id'] . "</div>";
            echo "<div class='info'>Username: " . $_SESSION['username'] . "</div>";
            
            // Test redirect
            echo "<div class='info'><a href='test_redirect.php'>Test redirect to another page</a></div>";
        } else {
            echo "<div class='error'>❌ Session not set correctly</div>";
        }
    } else {
        echo "<div class='error'>❌ Password verification failed</div>";
    }
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</div>";
    
    // Show more detailed error information for debugging
    echo "<div class='info'><pre>" . print_r([
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ], true) . "</pre></div>";
    
    // Show database connection details (without password)
    if (isset($pdo)) {
        $errorInfo = $pdo->errorInfo();
        echo "<div class='info'><pre>" . print_r([
            'database' => DB_NAME,
            'host' => DB_HOST,
            'user' => DB_USER,
            'error_info' => $errorInfo
        ], true) . "</pre></div>";
    }
}

echo "</body></html>";
