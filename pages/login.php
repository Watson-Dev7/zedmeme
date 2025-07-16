<?php require_once '../includes/auth_check.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | ZedMemes</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/foundation-sites@6.7.4/dist/css/foundation.min.css">
    <link rel="stylesheet" href="../assets/css/app.css">
</head>
<body>
    <div class="formCard">
        <div class="translucent-form-overlay">
            <form id="login-form">
                <h3>Log in</h3>
                
                <div id="login-error" class="error-message"></div>
                
                <div class="row columns">
                    <label>
                        <input type="text" name="username" placeholder="Username/Email" required>
                    </label>
                </div>
                <div class="row columns">
                    <label>
                        <input type="password" name="password" placeholder="Password" required>
                    </label>
                </div>
                <button type="submit" id="button" class="primary button expanded search-button">
                    Log in
                </button>
                <p class="text-center">Don't have an account? <a href="signup.php">Sign up</a></p>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../assets/js/app.js"></script>
</body>
</html>