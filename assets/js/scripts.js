$(document).ready(function() {
    $(document).foundation();

    setupFormHandlers();
    setupDragAndDrop();

    $('#landingLoginBtn').click(function() {
        $('#loginModal').foundation('open');
    });
    $('#landingSignupBtn').click(function() {
        $('#signupModal').foundation('open');
    });
});

function hideLandingScreen() {
    $('#landingScreen').fadeOut(500);
}

// Call this after successful login or when user is authenticated
function checkAuthState() {
    $.get('includes/auth_check.php', function(response) {
        console.log('Auth check:', response);
        if (response && response.authenticated === true) {
            hideLandingScreen();
            $('#authMenu').hide();
            $('#userMenu').show();
            $('#usernameDisplay').text(response.username || 'User');
        } else {
            $('#landingScreen').show();
            $('#authMenu').show();
            $('#userMenu').hide();
        }
    }, 'json').fail(function(jqXHR, textStatus, errorThrown) {
        console.error('Auth check failed:', textStatus, errorThrown);
        $('#authMenu').show();
        $('#userMenu').hide();
    });
}

function loadMemes(page = 1) {
    $.get(`memes.php?page=${page}`, function(response) {
        if (response.status === 'success') {
            renderMemes(response.memes);
            renderPagination(response.pagination);
        } else {
            showAlert('Failed to load memes', 'alert');
        }
    }, 'json').fail(function() {
        showAlert('Failed to load memes', 'alert');
    }).always(() => {
        $('.pagination a').css('pointer-events', 'auto');
    });
}

function renderMemes(memes) {
    const $grid = $('#memeGrid').empty();

    if (memes.length === 0) {
        $grid.append('<div class="cell"><div class="callout">No memes found. Be the first to upload one!</div></div>');
        return;
    }

    memes.forEach(meme => {
        $grid.append(`
            <div class="cell">
                <div class="card">
                    <div class="card-divider">
                        <h5>${meme.title || 'Untitled Meme'}</h5>
                        <small>by ${meme.username}</small>
                    </div>
                    <img src="uploads/${meme.filename}" alt="${meme.title || 'Meme'}" loading="lazy">
                    <div class="card-section">
                        <div class="button-group">
                            <button class="button like-button ${meme.user_reacted === 'like' ? 'success' : ''}" 
                                data-meme-id="${meme.id}" data-type="like">
                                Like (${meme.likes || 0})
                            </button>
                            <button class="button upvote-button ${meme.user_reacted === 'upvote' ? 'warning' : ''}"
                                data-meme-id="${meme.id}" data-type="upvote">
                                Upvote (${meme.upvotes || 0})
                            </button>
                            <button class="button share-button" data-meme-id="${meme.id}">
                                Share
                            </button>
                            <a href="uploads/${meme.filename}" download class="button download-button">
                                Download
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        `);
    });

    setupReactionHandlers();
}

function renderPagination(pagination) {
    const $container = $('#paginationContainer').empty();

    if (pagination.total_pages <= 1) return;

    const currentPage = pagination.current_page;
    const totalPages = pagination.total_pages;

    let paginationHTML = '<ul class="pagination">';

    if (currentPage > 1) {
        paginationHTML += `<li><a href="#" data-page="${currentPage - 1}">Previous</a></li>`;
    }

    for (let i = 1; i <= totalPages; i++) {
        if (i === currentPage) {
            paginationHTML += `<li class="current">${i}</li>`;
        } else {
            paginationHTML += `<li><a href="#" data-page="${i}">${i}</a></li>`;
        }
    }

    if (currentPage < totalPages) {
        paginationHTML += `<li><a href="#" data-page="${currentPage + 1}">Next</a></li>`;
    }

    paginationHTML += '</ul>';
    $container.html(paginationHTML);

    $('.pagination a').off('click').click(function(e) {
        e.preventDefault();
        const page = $(this).data('page');
        $('.pagination a').css('pointer-events', 'none');
        loadMemes(page);
    });
}

function setupFormHandlers() {
    $('#loginForm').submit(handleLogin);
    $('#signupForm').submit(handleSignup);
    $('#logoutButton').click(handleLogout);
    $('#uploadForm').submit(handleUpload);
}

function handleLogin(e) {
    e.preventDefault();
    const $form = $(this);
    const $button = $form.find('button').prop('disabled', true).html('Logging in...');

    $.post('login.php', $form.serialize(), function(response) {
        if (response.status === 'success') {
            $('#loginModal').foundation('close');
            showAlert('Login successful!', 'success');
            checkAuthState();
        } else {
            showAlert(response.message || 'Login failed', 'alert');
        }
    }, 'json').always(() => $button.prop('disabled', false).text('Login'));
}

function handleSignup(e) {
    e.preventDefault();
    const $form = $(this); // Fix: use the submitted form
    $.post('signup.php', $form.serialize(), function(response) {
        if (response.status === 'success') {
            $('#signupModal').foundation('close');
            $('#authMenu').hide();
            $('#userMenu').show();
            $('#usernameDisplay').text(response.username || 'User');
            showAlert('Sign up successful! Welcome, ' + (response.username || 'User'), 'success');
            checkAuthState();
        } else {
            showAlert(response.message || 'Sign up failed', 'error');
        }
    }, 'json').fail(function(jqXHR) {
        showAlert(jqXHR.responseJSON?.message || 'Sign up failed', 'error');
    });
}

function handleUpload(e) {
    e.preventDefault();
    const $form = $('#uploadForm');
    const formData = new FormData($form[0]);
    const $button = $form.find('button[type="submit"]').prop('disabled', true).text('Uploading...');

    $.ajax({
        url: 'upload.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                $('#uploadModal').foundation('close');
                showAlert('Meme uploaded!', 'success');
                loadMemes(); // Refresh memes
                $form[0].reset();
                $('#previewContainer').empty();
            } else {
                showAlert(response.message || 'Upload failed', 'error');
            }
        },
        error: function(jqXHR) {
            showAlert(jqXHR.responseJSON?.message || 'Upload failed', 'error');
        },
        complete: function() {
            $button.prop('disabled', false).text('Upload');
        }
    });
}

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

function setupReactionHandlers() {
    $('.like-button, .upvote-button').off('click').click(function() {
        const $button = $(this);
        const memeId = $button.data('meme-id');
        const type = $button.data('type');

        if ($button.prop('disabled')) return;
        $button.prop('disabled', true);

        $.post('reaction.php', { meme_id: memeId, type: type }, function(response) {
            if (response.status === 'success') {
                const $card = $button.closest('.card');
                $card.find('.like-button').removeClass('success').text(`Like (${response.likes})`);
                $card.find('.upvote-button').removeClass('warning').text(`Upvote (${response.upvotes})`);

                if (type === 'like') {
                    $button.addClass('success');
                } else if (type === 'upvote') {
                    $button.addClass('warning');
                }

                showAlert('Thanks for your reaction!', 'success');
            } else {
                showAlert(response.message || 'Reaction failed', 'alert');
            }
        }, 'json').fail(function() {
            showAlert('Reaction failed', 'alert');
        }).always(function() {
            $button.prop('disabled', false);
        });
    });

    $('.share-button').off('click').click(function() {
        const memeId = $(this).data('meme-id');
        const url = `${window.location.origin}${window.location.pathname}?meme=${memeId}`;

        if (navigator.clipboard) {
            navigator.clipboard.writeText(url).then(() => {
                showAlert('Link copied to clipboard!', 'success');
            }).catch(() => {
                prompt('Copy this link:', url);
            });
        } else {
            prompt('Copy this link:', url);
        }
    });
}

function setupDragAndDrop() {
    const $dropzone = $('#dropzone');
    const $fileInput = $('#memeImageInput');
    const $previewContainer = $('#previewContainer');

    $dropzone.on('click', function() {
        $fileInput.trigger('click');
    });

    $fileInput.change(function() {
        const file = this.files[0];
        if (file) previewFile(file);
    });

    $dropzone.on('dragover', function(e) {
        e.preventDefault();
        $(this).addClass('active');
    });

    $dropzone.on('dragleave', function(e) {
        e.preventDefault();
        $(this).removeClass('active');
    });

    $dropzone.on('drop', function(e) {
        e.preventDefault();
        $(this).removeClass('active');
        const files = e.originalEvent.dataTransfer.files;
        if (files.length > 0) {
            const file = files[0];
            if (file.type.startsWith('image/')) {
                $fileInput[0].files = files;
                previewFile(file);
            } else {
                showAlert('Please select an image file', 'alert');
            }
        }
    });

    function previewFile(file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            $previewContainer.html(`
                <img src="${e.target.result}" alt="Preview" style="max-width: 100%; max-height: 200px;">
            `);
        };
        reader.readAsDataURL(file);
    }
}

function showAlert(message, type) {
    const $alert = $(`
        <div class="callout ${type}" style="position: fixed; top: 1rem; right: 1rem; z-index: 9999;">
            ${message}
        </div>
    `);
    $('body').append($alert);

    setTimeout(() => {
        $alert.fadeOut(300, function() {
            $(this).remove();
        });
    }, 3000);
}

