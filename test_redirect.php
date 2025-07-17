<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session with the same path as test_login.php
$sessionPath = __DIR__ . '/tmp/sessions';
if (!file_exists($sessionPath)) {
    mkdir($sessionPath, 0777, true);
}
session_save_path($sessionPath);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Session Test - Redirect</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; margin: 20px; }
        .success { color: green; margin: 10px 0; padding: 10px; background: #e8f5e9; border-left: 4px solid #4caf50; }
        .error { color: #d32f2f; margin: 10px 0; padding: 10px; background: #ffebee; border-left: 4px solid #f44336; }
        .info { color: #1976d2; margin: 10px 0; padding: 10px; background: #e3f2fd; border-left: 4px solid #2196f3; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 4px; overflow-x: auto; }
    </style>
</head>
<body>
    <h2>Session Test - Redirect Page</h2>
    
    <?php
    if (isset($_SESSION['user_id'])) {
        echo "<div class='success'>✅ Session is working! You are logged in as: " . htmlspecialchars($_SESSION['username']) . "</div>";
        echo "<div class='info'>Session ID: " . session_id() . "</div>";
        echo "<div class='info'>Session Path: " . session_save_path() . "</div>";
        echo "<div class='info'>Session Data: <pre>" . print_r($_SESSION, true) . "</pre></div>";
        echo "<p><a href='test_login.php'>Back to Login Test</a></p>";
    } else {
        echo "<div class='error'>❌ No active session found. The session is not being maintained between pages.</div>";
        echo "<div class='info'>Session ID: " . session_id() . "</div>";
        echo "<div class='info'>Session Path: " . session_save_path() . "</div>";
        echo "<div class='info'>Session Data: <pre>" . print_r($_SESSION, true) . "</pre></div>";
        echo "<p><a href='test_login.php'>Go to Login Test</a></p>";
    }
    ?>
    
    <h3>Session Debug Information</h3>
    <div class='info'>
        <pre><?php 
        echo "Session Status: " . session_status() . "\n";
        echo "Session Name: " . session_name() . "\n";
        echo "Session Save Path: " . session_save_path() . "\n";
        echo "Session ID: " . session_id() . "\n";
        echo "Session Cookie Parameters: " . print_r(session_get_cookie_params(), true) . "\n";
        echo "\n";
        
        // Check session files
        $sessionFiles = glob(session_save_path() . '/sess_*');
        echo "Session Files in " . session_save_path() . ":\n";
        foreach ($sessionFiles as $file) {
            echo "- " . basename($file) . " (" . date('Y-m-d H:i:s', filemtime($file)) . ")\n";
            echo "  Content: " . file_get_contents($file) . "\n\n";
        }
        ?></pre>
    </div>
</body>
</html>
