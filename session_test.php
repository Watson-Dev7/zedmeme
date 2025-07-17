<?php
// filepath: c:\xampp\htdocs\zedmemes\includes\functions.php
function secure_session_start() {
    $session_name = 'zedmemes_session';
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    $httponly = true;
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', $secure ? 1 : 0);
    session_name($session_name);
    session_start();
    session_regenerate_id(true);
}
secure_session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Session Test</title>
</head>
<body>
    <h2>Session Data</h2>
    <pre><?php var_dump($_SESSION); ?></pre>
    <h2>Cookie Data</h2>
    <pre><?php var_dump($_COOKIE); ?></pre>
</body>
</html>
<?php
// In signup.php catch block
echo json_encode([
    'status' => 'error',
    'message' => $e->getMessage()
]);
?>

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL
);