<?php
/**
 * ZedMemes - Core Functions
 */

// Secure session start with configuration
function secure_session_start() {
    $session_name = 'zedmemes_session';
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    $httponly = true;
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', $secure ? 1 : 0);
    session_name($session_name);
    session_start();
    session_regenerate_id(true); // Prevent session fixation
}

// Check if user is logged in with session validation
function is_logged_in() {
    if (!isset($_SESSION['user_id'], 
              $_SESSION['user_ip'], 
              $_SESSION['user_agent'])) {
        return false;
    }

    // Prevent session hijacking
    if ($_SESSION['user_ip'] != $_SERVER['REMOTE_ADDR']) {
        return false;
    }

    if ($_SESSION['user_agent'] != $_SERVER['HTTP_USER_AGENT']) {
        return false;
    }

    return true;
}

// Secure file upload validation
function validate_uploaded_file(array $file) {
    // Check for upload errors
    $upload_errors = [
        UPLOAD_ERR_OK => "No errors",
        UPLOAD_ERR_INI_SIZE => "Larger than upload_max_filesize",
        UPLOAD_ERR_FORM_SIZE => "Larger than form MAX_FILE_SIZE",
        UPLOAD_ERR_PARTIAL => "Partial upload",
        UPLOAD_ERR_NO_FILE => "No file",
        UPLOAD_ERR_NO_TMP_DIR => "No temporary directory",
        UPLOAD_ERR_CANT_WRITE => "Can't write to disk",
        UPLOAD_ERR_EXTENSION => "File upload stopped by extension"
    ];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception($upload_errors[$file['error']]);
    }

    // Verify MIME type
    $allowed_mime = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);

    if (!in_array($mime, $allowed_mime)) {
        throw new Exception("Invalid file type. Only images allowed");
    }

    // Verify extension
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
        throw new Exception("Invalid file extension");
    }

    // Check file size (max 5MB)
    if ($file['size'] > 5242880) {
        throw new Exception("File too large (max 5MB)");
    }

    return true;
}

// Generate secure filename for uploads
function generate_filename($original_name) {
    $ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
    return uniqid('meme_', true) . '.' . $ext;
}

// Sanitize output for XSS protection
function sanitize_output($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

// Redirect with flash messages
function redirect_with_message($url, $type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
    header("Location: $url");
    exit();
}

// Display flash messages
function display_flash_message() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return '<div class="callout '.$flash['type'].'">'
              .sanitize_output($flash['message'])
              .'</div>';
    }
    return '';
}

// Database query helper
function db_query($sql, $params = []) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        throw new Exception("Database operation failed");
    }
}

// Get meme with reactions count
function get_meme_with_reactions($meme_id) {
    $sql = "SELECT m.*, 
                   COUNT(DISTINCT l.id) as likes,
                   COUNT(DISTINCT u.id) as upvotes
            FROM memes m
            LEFT JOIN reactions l ON (m.id = l.meme_id AND l.type = 'like')
            LEFT JOIN reactions u ON (m.id = u.meme_id AND u.type = 'upvote')
            WHERE m.id = ?
            GROUP BY m.id";
    
    $stmt = db_query($sql, [$meme_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Check if user reacted to meme
function user_reacted_to_meme($user_id, $meme_id, $type) {
    $sql = "SELECT 1 FROM reactions 
            WHERE user_id = ? AND meme_id = ? AND type = ? 
            LIMIT 1";
    $stmt = db_query($sql, [$user_id, $meme_id, $type]);
    return (bool)$stmt->fetch();
}