<?php require_once '../includes/auth_check.php'; ?>
<?php
require_once '../handlers/portfolio_handler.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | ZedMemes</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/foundation-sites@6.7.4/dist/css/foundation.min.css">
    <link rel="stylesheet" href="../assets/css/app.css">
</head>
<body>
    <div class="row medium-8 large-7 columns">
        <div class="off-canvas-wrapper">
            <div class="wrapper-inner" data-off-canvas-wrapper>
                <div class="off-canvas position-left reveal-for-large" id="my-info" data-off-canvas data-position="left">
                    <div class="row">
                        <br>
                        <img class="profile" src="<?= htmlspecialchars($profileData['user']['profile_image'] ?? '../assets/img/default-profile.png') ?>">
                        <h5><?= htmlspecialchars($profileData['user']['first_name'] . ' ' . $profileData['user']['last_name']) ?></h5>
                        <div class="myMenu">
                            <ul>
                                <li><a href="../handlers/logout_handler.php" class="button">Log out</a></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="off-canvas-content" data-off-canvas-content>
                    <div class="title-bar hide-for-large">
                        <div class="title-bar-left">
                            <button class="menu-icon" type="button" data-open="my-info"></button>
                            <span class="title-bar-title"><?= htmlspecialchars($profileData['user']['first_name'] . ' ' . $profileData['user']['last_name']) ?></span>
                        </div>
                    </div>
                    
                    <div class="callout">
                        <div class="row column">
                            <img class="profileImage" src="<?= htmlspecialchars($profileData['user']['profile_image'] ?? '../assets/img/default-profile.png') ?>" 
                                 alt="profile">
                            <h3><?= htmlspecialchars($profileData['user']['first_name'] . ' ' . $profileData['user']['last_name']) ?></h3>
                            
                            <div class="row column">
                                <h3>Bio</h3>
                                <p><?= htmlspecialchars($profileData['user']['bio'] ?? 'No bio yet.') ?></p>
                            </div>
                        </div>
                        
                        <span class="lead" style="width: 10px; font-size: 30px;">My Memes</span>
                        <hr>
                        
                        <div class="memes">
                            <div class="row small-up-2 medium-up-3 large-up-4">
                                <?php foreach ($profileData['memes'] as $meme): ?>
                                <div class="column">
                                    <img class="thumbnail" src="<?= htmlspecialchars($meme['image_url']) ?>" 
                                         alt="<?= htmlspecialchars($meme['title'] ?? 'Meme') ?>">
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/foundation-sites@6.7.4/dist/js/foundation.min.js"></script>
    <script src="../assets/js/app.js"></script>
    <script>
        $(document).foundation();
    </script>
</body>
</html>