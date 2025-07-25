<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set custom error handler
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    error_log("PHP Error [$errno] $errstr in $errfile on line $errline");
    if (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false) {
        echo "<div style='background:#ffebee;padding:15px;border-left:4px solid #f44336;margin:10px;'>";
        echo "<strong>Error [$errno]:</strong> $errstr<br>";
        echo "<small>in $errfile on line $errline</small>";
        echo "</div>";
    }
    return true;
});

// Start session with custom path
$sessionPath = __DIR__ . '/../tmp/sessions';
if (!file_exists($sessionPath)) {
    mkdir($sessionPath, 0777, true);
}
session_save_path($sessionPath);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Include database connection
try {
    // Load database configuration
    $configFile = __DIR__ . '/../includes/db.php';
    if (!file_exists($configFile)) {
        throw new Exception("Database configuration file not found at: $configFile");
    }
    
    require_once $configFile;
    
    // Check if PDO object was created
    if (!isset($pdo) || !($pdo instanceof PDO)) {
        throw new Exception("Failed to initialize database connection");
    }
    
    // Test database connection
    $pdo->query('SELECT 1');
    
    // Check if tables exist
    $tables = $pdo->query("SHOW TABLES LIKE 'memes'")->rowCount();
    $usersTable = $pdo->query("SHOW TABLES LIKE 'users'")->rowCount();
    
    if ($tables === 0 || $usersTable === 0) {
        throw new Exception("Required database tables are missing. Please run the installation script.");
    }
    
    // Get memes with user info and reaction counts
    $query = "
        SELECT m.*, u.username, u.profile_image,
               (SELECT COUNT(*) FROM meme_reactions WHERE meme_id = m.id AND action = 'like') AS like_count,
               (SELECT COUNT(*) FROM meme_reactions WHERE meme_id = m.id AND action = 'download') AS download_count
        FROM memes m
        JOIN users u ON m.user_id = u.id
        ORDER BY m.created_at DESC
        LIMIT 20
    ";
    
    $stmt = $pdo->query($query);
    
    if ($stmt === false) {
        throw new Exception("Failed to execute query: " . implode(" ", $pdo->errorInfo()));
    }
    
    $memes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Check if we got any results
    if (empty($memes)) {
        $memes = [];
        $no_memes = true;
    }
    
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
    error_log($error);
} catch (Exception $e) {
    $error = "Error: " . $e->getMessage();
    error_log($error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meme Feed | ZedMemes</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/foundation-sites@6.7.4/dist/css/foundation.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/app.css">
</head>
<body>
    <header>
        <div class="topbar">
            <h2>Zedmemes</h2>
            <div class="second-bar">
                <div class="profileItems" style="">
                    <a href="portfolio.php" class="cta-button">
                        <i class="fas fa-user"></i>
                    </a>
                    <a href="../handlers/logout_handler.php" class="cta-button">
                        <i class="fas fa-sign-out-alt"></i>
                    </a>
                </div>
            </div>
        </div>
    </header>

   

    <main>
        <?php if (isset($error)): ?>
            <div class="error-message">
                <p>We're having trouble loading the memes. Please try again later.</p>
                <?php if (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false): ?>
                    <p class="debug-info">Debug: <?= htmlspecialchars($error) ?></p>
                <?php endif; ?>
            </div>
        <?php elseif (isset($no_memes)): ?>
            <div class="no-memes">
                <i class="fas fa-images"></i>
                <h3>No memes yet!</h3>
                <p>Be the first to share a meme!</p>
                <a href="upload.php" class="button primary">Upload a Meme</a>
            </div>
        <?php else: ?>
            <?php foreach ($memes as $meme): ?>
            <div class="meme-card">
                <div class="meme-header">
                    <img src="<?= htmlspecialchars($meme['profile_image'] ?? '../assets/img/default-profile.png') ?>" 
                         class="profile" alt="<?= htmlspecialchars($meme['username']) ?>'s profile">
                    <span class="username"><?= htmlspecialchars($meme['username']) ?></span>
                    <span class="post-time"><?= !empty($meme['created_at']) ? date('M j, Y', strtotime($meme['created_at'])) : 'Recently' ?></span>
                </div>
                
                <?php if (!empty($meme['title'])): ?>
                    <h3 class="meme-title"><?= htmlspecialchars($meme['title']) ?></h3>
                <?php endif; ?>
                
                <div class="meme-image-container">
                    <img src="<?= htmlspecialchars($meme['image_url']) ?>" 
                         class="meme-image" 
                         alt="Meme by <?= htmlspecialchars($meme['username']) ?>"
                         loading="lazy">
                </div>
                
                <div class="reaction-container">
                    <button class="cta-button reaction-btn" data-action="like" data-meme-id="<?= $meme['id'] ?>">
                        <i class="fas fa-thumbs-up"></i>
                        <span id="like-count-<?= $meme['id'] ?>"><?= intval($meme['like_count']) ?></span>
                    </button>
                    
                    <button class="cta-button reaction-btn" data-action="download" data-meme-id="<?= $meme['id'] ?>">
                        <i class="fas fa-download"></i>
                        <span id="download-count-<?= $meme['id'] ?>"><?= intval($meme['download_count']) ?></span>
                    </button>
                    
                    <button class="cta-button copy-link" data-meme-id="<?= $meme['id'] ?>" data-copied="false">
                        <i class="fas fa-link"></i>
                        <span class="button-text">Copy Link</span>
                    </button>
                    
                    <button class="cta-button comment-toggle" data-meme-id="<?= $meme['id'] ?>">
                        <i class="far fa-comment"></i>
                        <span>Comment</span>
                    </button>
                </div>
                
                <div class="comment-section" id="comment-section-<?= $meme['id'] ?>">
                    <form class="comment-form" data-meme-id="<?= $meme['id'] ?>">
                        <div class="form-group">
                            <input type="text" name="comment" placeholder="Add a comment..." required>
                            <button type="submit" class="button small primary">Post</button>
                        </div>
                    </form>
                    
                    <div class="comments-list" id="comments-<?= $meme['id'] ?>">
                        <!-- Comments will be loaded here via AJAX -->
                        <div class="loading-comments">
                            <i class="fas fa-spinner fa-spin"></i> Loading comments...
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/foundation-sites@6.7.4/dist/js/foundation.min.js"></script>
    
    <!-- Custom Scripts -->
    <script src="../assets/js/app.js"></script>
    <script src="../assets/js/blog.js"></script>
    
    <!-- Initialize Foundation -->
    <script>
        $(document).foundation();
        
        // Enable tooltips
        $(document).ready(function() {
            $('[data-tooltip]').tooltip();
        });
    </script>
</body>
</html>