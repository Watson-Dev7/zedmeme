// Main application JavaScript
$(document).foundation();

$(document).ready(function() {
    // Handle login form submission
    $('#login-form').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            type: 'POST',
            url: 'handlers/login_handler.php',
            data: $(this).serialize(),
            success: function(response) {
                if(response.success) {
                    window.location.href = 'pages/blog-simple.php';
                } else {
                    $('#login-error').text(response.message).show();
                }
            },
            dataType: 'json'
        });
    });

    // Handle signup form submission
    $('#signup-form').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            type: 'POST',
            url: 'handlers/signup_handler.php',
            data: $(this).serialize(),
            success: function(response) {
                if(response.success) {
                    $('#signup-success').text(response.message).show();
                    setTimeout(() => {
                        window.location.href = 'pages/login.php';
                    }, 2000);
                } else {
                    $('#signup-error').text(response.message).show();
                }
            },
            dataType: 'json'
        });
    });

    // Handle meme reactions
    $('.reaction-btn').on('click', function() {
        const memeId = $(this).data('meme-id');
        const action = $(this).data('action');
        
        $.ajax({
            type: 'POST',
            url: 'handlers/reaction_handler.php',
            data: { meme_id: memeId, action: action },
            success: function(response) {
                if(response.success) {
                    const counter = $(`#${action}-count-${memeId}`);
                    counter.text(response.count);
                }
            },
            dataType: 'json'
        });
    });

    // Handle comments
    $('.comment-form').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const memeId = form.data('meme-id');
        
        $.ajax({
            type: 'POST',
            url: 'handlers/comment_handler.php',
            data: form.serialize(),
            success: function(response) {
                if(response.success) {
                    form.find('textarea').val('');
                    $(`#comments-${memeId}`).prepend(`
                        <div class="comment">
                            <strong>${response.username}</strong>
                            <p>${response.comment}</p>
                            <small>Just now</small>
                        </div>
                    `);
                }
            },
            dataType: 'json'
        });
    });

    // Copy meme link
    $('.copy-link').on('click', function() {
        const memeId = $(this).data('meme-id');
        const url = `${window.location.origin}/meme.php?id=${memeId}`;
        
        navigator.clipboard.writeText(url).then(() => {
            $(this).text('Copied!');
            setTimeout(() => {
                $(this).text('Copy Link');
            }, 2000);
        });
    });
});