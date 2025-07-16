<?php require_once '../includes/auth_check.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up | ZedMemes</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/foundation-sites@6.7.4/dist/css/foundation.min.css">
    <link rel="stylesheet" href="../assets/css/app.css">
</head>
<body>
    <div class="formCard">
        <div class="translucent-form-overlay">
            <form id="signup-form">
                <h3>Sign Up</h3>
                
                <div id="signup-error" class="error-message"></div>
                <div id="signup-success" class="success-message"></div>
                
                <div class="row columns" id="name">
                    <label>
                        <input type="text" name="first_name" placeholder="First name" required>
                    </label>
                    <label>
                        <input type="text" name="last_name" placeholder="Last name" required>
                    </label>
                </div>
                <div class="row columns">
                    <label>
                        <input type="email" name="email" placeholder="Email" required>
                    </label>
                </div>
                <div class="row columns">
                    <label>
                        <input type="password" name="password" placeholder="Password" required minlength="8">
                    </label>
                </div>
                <div class="row columns">
                    <label>
                        <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                    </label>
                </div>
                <div class="row columns">
                    <input type="checkbox" id="Agree" name="Agree" required>
                    <label for="Agree">I Agree with <span>privacy</span> and <span>policy</span></label>
                </div>
                <button type="submit" id="button" class="primary button expanded search-button">
                    Sign Up
                </button>
                <p class="text-center">Already have an account? <a href="login.php">Log in</a></p>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../assets/js/app.js"></script>
</body>
</html>