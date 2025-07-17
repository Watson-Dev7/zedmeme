<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session with custom path
session_save_path(__DIR__ . '/tmp/sessions');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Simulate a logged-in user for testing
$_SESSION['user_id'] = 1;
$_SESSION['username'] = 'testuser';

// Include database connection
try {
    require_once 'includes/db.php';
    
    // Test database connection
    $pdo->query('SELECT 1');
    
    // Check if memes table exists
    $tableExists = $pdo->query("SHOW TABLES LIKE 'memes'")->rowCount() > 0;
    
    if (!$tableExists) {
        // Create memes table if it doesn't exist
        $pdo->exec("CREATE TABLE IF NOT EXISTS memes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            title VARCHAR(255),
            image_url VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )");
        
        // Insert test memes
        $testMemes = [
            [1, 'Funny Cat', 'assets/img/sample1.jpg'],
            [1, 'Doge Meme', 'assets/img/sample2.jpg'],
            [1, 'Success Kid', 'assets/img/sample3.jpg']
        ];
        
        $stmt = $pdo->prepare("INSERT INTO memes (user_id, title, image_url) VALUES (?, ?, ?)");
        foreach ($testMemes as $meme) {
            $stmt->execute($meme);
        }
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
    
    $memes = $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);
    
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
    <title>Test Blog | ZedMemes</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/foundation-sites@6.7.4/dist/css/foundation.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="assets/css/app.css">
</head>
<body>
    <header>
        <div class="topbar">
            <h2>ZedMemes</h2>
            <div class="second-bar">
                <div class="profileItems">
                    <a href="#" class="cta-button">
                        <i class="fas fa-user"></i>
                        <span>Profile</span>
                    </a>
                    <a href="#" class="cta-button">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <main>
        <?php if (isset($error)): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-triangle"></i>
                <h3>Database Error</h3>
                <p>We're having trouble loading the memes. Please try again later.</p>
                <?php if (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false): ?>
                    <div class="debug-info">
                        <p><strong>Error Details:</strong> <?= htmlspecialchars($error) ?></p>
                        <p><strong>Database:</strong> <?= htmlspecialchars(DB_NAME) ?></p>
                        <p><strong>Host:</strong> <?= htmlspecialchars(DB_HOST) ?></p>
                    </div>
                <?php endif; ?>
            </div>
        <?php elseif (empty($memes)): ?>
            <div class="no-memes">
                <i class="fas fa-images"></i>
                <h3>No memes yet!</h3>
                <p>Be the first to share a meme!</p>
                <a href="#" class="button primary">Upload a Meme</a>
            </div>
        <?php else: ?>
            <?php foreach ($memes as $meme): ?>
            <div class="meme-card">
                <div class="meme-header">
                    <img src="<?= htmlspecialchars($meme['profile_image'] ?? 'assets/img/default-profile.png') ?>" 
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
                         alt="<?= !empty($meme['title']) ? htmlspecialchars($meme['title']) : 'Meme' ?>"
                         loading="lazy">
                </div>
                
                <div class="reaction-container">
                    <button class="cta-button reaction-btn" data-action="like" data-meme-id="<?= $meme['id'] ?>">
                        <i class="fas fa-thumbs-up"></i>
                        <span id="like-count-<?= $meme['id'] ?>"><?= intval($meme['like_count'] ?? 0) ?></span>
                    </button>
                    
                    <button class="cta-button reaction-btn" data-action="download" data-meme-id="<?= $meme['id'] ?>">
                        <i class="fas fa-download"></i>
                        <span id="download-count-<?= $meme['id'] ?>"><?= intval($meme['download_count'] ?? 0) ?></span>
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
                    
                    <div class="comments-list" id="comments-<?= $meme['id'] ?>>
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
    <script src="assets/js/app.js"></script>
    <script src="assets/js/blog.js"></script>
    
    <script>
        // Initialize Foundation
        $(document).foundation();
        
        // Enable tooltips
        $(document).ready(function() {
            $('[data-tooltip]').tooltip();
            
            // Show all comment sections for testing
            $('.comment-section').addClass('expanded');
        });
    </script>
</body>
</html>
