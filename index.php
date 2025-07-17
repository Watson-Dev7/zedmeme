<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZedMemes</title>
    <!-- Foundation CSS -->
    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/foundation-sites@6.7.5/dist/css/foundation.min.css">

    <style>
        .top-bar-right .button {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 0.5em 1em;
            transition: all 0.3s ease;
            cursor: pointer;
            border-radius: 4px;
        }
    </style>

    
</head>

<body>
    <!-- Top Bar Navigation -->
    <div class="top-bar">
        <div class="top-bar-center">
            <ul class="menu">
                <li class="menu-text">ZedMemes</li>
                <li><a href="#" class="active">Home</a></li>
            </ul>
        </div>
        <div class="top-bar-right">
            <ul class="menu" id="authMenu">
                <li><a href="#" data-open="loginModal">Login</a></li>
                <li><a href="#" data-open="signupModal">Sign Up</a></li>
            </ul>
            <div class="menu" id="userMenu" style="display:flex; ; align-items: center; gap: 20px;">
                <div><span id="usernameDisplay"></span></div>
                <div><button class="button" id="logoutButton">Logout</button></div>
                <div><button class="button" data-open="uploadModal">Upload Meme</button></div>
            </div>
        </div>
    </div>

    <!-- Landing Screen -->
    <div id="landingScreen" class=""
        style="display: flex; flex-direction: column; align-items: center; justify-content: center; background: #111; position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 0;">
        <h1 class="zedmemes-title">
            <span class="glow-loader">ZedMemes</span>
        </h1>
        <div class="landing-actions">
            <button class="button" id="landingLoginBtn" data-open="loginModal">Login</button>
            <button class="button secondary" id="landingSignupBtn" data-open="signupModal">Sign Up</button>
        </div>
    </div>

    <!-- Main Content -->
    <div class="grid-container">
        <div class="grid-x grid-margin-x small-up-1 medium-up-2 large-up-3" id="memeGrid">
            <!-- Memes will be loaded here via AJAX -->
        </div>
        <div class="grid-x">
            <div class="cell text-center" id="paginationContainer">
                <!-- Pagination will be loaded here -->
            </div>
        </div>
    </div>

    <!-- Login Modal -->
    <div class="reveal" id="loginModal" data-reveal>
        <h3>Login</h3>
        <form id="loginForm">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" class="button">Login</button>
        </form>
        <button class="close-button" data-close aria-label="Close modal" type="button">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>

    <!-- Signup Modal -->
    <div class="reveal" id="signupModal" data-reveal>
        <h3>Sign Up</h3>
        <form id="signupForm">
            <input type="text" name="username" placeholder="Username" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" class="button secondary">Sign Up</button>
        </form>
        <button class="close-button" data-close aria-label="Close modal" type="button">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>

    <!-- Upload Modal -->
    <div class="reveal" id="uploadModal" data-reveal>
        <h3>Upload Meme</h3>
        <form id="uploadForm" enctype="multipart/form-data">
            <input type="text" name="title" placeholder="Meme Title" maxlength="100" required>
            <div id="dropzone" class="dropzone">
                <span>Drag & drop image here or click to select</span>
                <input type="file" id="memeImageInput" name="memeImage" accept="image/*" required style="display:none;">
                <div id="previewContainer"></div>
            </div>
            <button type="submit" class="button">Upload</button>
        </form>
        <button class="close-button" data-close aria-label="Close modal" type="button">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>

    <!-- Error Message Modal -->
    <div id="errorModal" class="reveal" data-reveal>
        <h4>Error</h4>
        <p id="errorMessage">...</p>
        <button class="button" data-close>Close</button>
    </div>

    <!-- Foundation JS & App Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/foundation-sites@6.7.5/dist/js/foundation.min.js"></script>
    <script src="assets/js/scripts.js"></script>

    <script>
        $('#logoutButton').click(handleLogout);

        function handleLogout(e) {
            e.preventDefault();
            console.log('Logging out...');
            // $.post('logout.php', function(response) {
            //     if (response.status === 'success') {
            //         $('#userMenu').hide();
            //         $('#authMenu').show();
            //         $('#usernameDisplay').text('');
            //         showAlert('Logged out successfully', 'success');


            //     } else {
            //         showAlert('Logout failed', 'error');
            //     }

            // }, 'json');

            location.reload(); // Reload to reset state
        }

    </script>
</body>

</html>