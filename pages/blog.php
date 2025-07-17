<?php require_once '../includes/auth_check.php'; ?>
<?php
require_once '../includes/db.php';

// Get memes with user info and reaction counts
$stmt = $pdo->prepare("
    SELECT m.*, u.username, u.profile_image,
           (SELECT COUNT(*) FROM meme_reactions WHERE meme_id = m.id AND action = 'like') AS like_count,
           (SELECT COUNT(*) FROM meme_reactions WHERE meme_id = m.id AND action = 'download') AS download_count
    FROM memes m
    JOIN users u ON m.user_id = u.id
    ORDER BY m.created_at DESC
");
$stmt->execute();
$memes = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
        <?php foreach ($memes as $meme): ?>
        <div class="meme-card">
            <div class="meme-header">
                <img src="<?= htmlspecialchars($meme['profile_image'] ?? '../assets/img/default-profile.png') ?>" 
                     class="profile" alt="Profile">
                <span><?= htmlspecialchars($meme['username']) ?></span>
            </div>
            
            <img src="<?= htmlspecialchars($meme['image_url']) ?>" class="meme-image" alt="Meme">
            
            <div class="reaction-container">
                <button class="cta-button reaction-btn" data-action="like" data-meme-id="<?= $meme['id'] ?>">
                    <i class="fas fa-thumbs-up"></i>
                    <span id="like-count-<?= $meme['id'] ?>"><?= $meme['like_count'] ?></span>
                </button>
                
                <button class="cta-button reaction-btn" data-action="download" data-meme-id="<?= $meme['id'] ?>">
                    <i class="fas fa-download"></i>
                    <span id="download-count-<?= $meme['id'] ?>"><?= $meme['download_count'] ?></span>
                </button>
                
                <button class="cta-button copy-link" data-meme-id="<?= $meme['id'] ?>">
                    <i class="fas fa-link"></i> Copy Link
                </button>
            </div>
            
            <div class="comment-section">
                <form class="comment-form" data-meme-id="<?= $meme['id'] ?>">
                    <textarea name="comment" placeholder="Add a comment..." required></textarea>
                    <button type="submit" class="button small">Post</button>
                </form>
                
                <div id="comments-<?= $meme['id'] ?>">
                    <!-- Comments will be loaded here via AJAX -->
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </main>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../assets/js/app.js"></script>
</body>
</html>