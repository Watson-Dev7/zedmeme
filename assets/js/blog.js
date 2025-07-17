/**
 * Blog Page Functionality
 * Handles interactions for the blog/meme feed
 */

$(document).ready(function() {
    // Initialize tooltips
    $('[data-tooltip]').tooltip();
    
    // Toggle comment section
    $('.comment-toggle').on('click', function() {
        const memeId = $(this).data('meme-id');
        $(`#comment-section-${memeId}`).toggleClass('expanded');
        loadComments(memeId);
    });
    
    // Handle comment submission
    $('.comment-form').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const memeId = form.data('meme-id');
        const commentInput = form.find('input[name="comment"]');
        const commentText = commentInput.val().trim();
        
        if (!commentText) return;
        
        // Show loading state
        const submitBtn = form.find('button[type="submit"]');
        const originalText = submitBtn.text();
        submitBtn.html('<i class="fas fa-spinner fa-spin"></i>');
        submitBtn.prop('disabled', true);
        
        // Simulate API call (replace with actual API call)
        setTimeout(() => {
            // Create new comment element
            const commentHtml = `
                <div class="comment">
                    <img src="${$('.meme-header .profile').attr('src')}" class="comment-avatar" alt="User">
                    <div class="comment-content">
                        <span class="comment-username">${$('.meme-header .username').text()}</span>
                        <p class="comment-text">${commentText}</p>
                        <span class="comment-time">Just now</span>
                    </div>
                </div>
            `;
            
            // Prepend new comment to the list
            $(`#comments-${memeId}`).prepend(commentHtml);
            
            // Clear input and reset button
            commentInput.val('');
            submitBtn.html(originalText);
            submitBtn.prop('disabled', false);
            
            // Hide the submit button until new input
            submitBtn.css('opacity', '0');
            submitBtn.css('visibility', 'hidden');
            
        }, 800);
    });
    
    // Handle reaction buttons (like, download)
    $('.reaction-btn').on('click', function() {
        const button = $(this);
        const action = button.data('action');
        const memeId = button.data('meme-id');
        const counter = button.find('span');
        
        // Toggle active state
        const isActive = button.hasClass('active');
        
        // Update UI immediately for better UX
        if (isActive) {
            button.removeClass('active');
            counter.text(parseInt(counter.text()) - 1);
        } else {
            button.addClass('active');
            counter.text(parseInt(counter.text()) + 1);
        }
        
        // Simulate API call (replace with actual API call)
        // Example: updateReaction(memeId, action, !isActive);
    });
    
    // Handle copy link button
    $('.copy-link').on('click', function() {
        const button = $(this);
        const memeId = button.data('meme-id');
        const url = `${window.location.origin}${window.location.pathname}?meme=${memeId}`;
        
        // Copy to clipboard
        navigator.clipboard.writeText(url).then(() => {
            // Update button state
            button.attr('data-copied', 'true');
            button.find('.button-text').text('Copied!');
            
            // Reset button after 2 seconds
            setTimeout(() => {
                button.attr('data-copied', 'false');
                button.find('.button-text').text('Copy Link');
            }, 2000);
        }).catch(err => {
            console.error('Failed to copy URL: ', err);
            alert('Failed to copy link. Please try again.');
        });
    });
    
    // Show comment form when clicking on comment icon
    $('.fa-comment').on('click', function(e) {
        e.stopPropagation();
        const memeId = $(this).closest('.reaction-btn').data('meme-id');
        $(`#comment-section-${memeId}`).addClass('expanded').find('input[type="text"]').focus();
    });
    
    // Load comments when comment section is expanded
    $('.comment-section').on('click', '.comment-toggle', function() {
        const memeId = $(this).data('meme-id');
        loadComments(memeId);
    });
});

/**
 * Load comments for a specific meme
 * @param {string} memeId - The ID of the meme to load comments for
 */
function loadComments(memeId) {
    const $commentsContainer = $(`#comments-${memeId}`);
    
    // Only load if not already loaded
    if ($commentsContainer.data('loaded') !== true) {
        $commentsContainer.html('<div class="loading-comments"><i class="fas fa-spinner fa-spin"></i> Loading comments...</div>');
        
        // Simulate API call (replace with actual API call)
        setTimeout(() => {
            // Example response from server
            const mockComments = [
                {
                    id: 1,
                    username: 'user123',
                    avatar: '../assets/img/default-profile.png',
                    text: 'This is a sample comment!',
                    time: '2 hours ago'
                },
                {
                    id: 2,
                    username: 'meme_lover',
                    avatar: '../assets/img/default-profile.png',
                    text: 'Hilarious! 😂',
                    time: '5 hours ago'
                }
            ];
            
            if (mockComments.length > 0) {
                let commentsHtml = '';
                mockComments.forEach(comment => {
                    commentsHtml += `
                        <div class="comment">
                            <img src="${comment.avatar}" class="comment-avatar" alt="${comment.username}">
                            <div class="comment-content">
                                <span class="comment-username">${comment.username}</span>
                                <p class="comment-text">${comment.text}</p>
                                <span class="comment-time">${comment.time}</span>
                            </div>
                        </div>
                    `;
                });
                $commentsContainer.html(commentsHtml);
            } else {
                $commentsContainer.html('<div class="no-comments">No comments yet. Be the first to comment!</div>');
            }
            
            // Mark as loaded
            $commentsContainer.data('loaded', true);
        }, 800);
    }
}

/**
 * Update reaction count for a meme
 * @param {string} memeId - The ID of the meme
 * @param {string} action - The action (like/download)
 * @param {boolean} isActive - Whether the action is being added or removed
 */
function updateReaction(memeId, action, isActive) {
    // Example API call (replace with your actual API endpoint)
    /*
    $.ajax({
        url: `/api/memes/${memeId}/reactions`,
        method: isActive ? 'POST' : 'DELETE',
        data: { action: action },
        success: function(response) {
            // Update UI with new counts
            $(`#${action}-count-${memeId}`).text(response.newCount);
        },
        error: function(xhr, status, error) {
            console.error(`Failed to ${action} meme:`, error);
            // Revert UI on error
            const button = $(`[data-action="${action}"][data-meme-id="${memeId}"]`);
            const counter = button.find('span');
            button.toggleClass('active');
            counter.text(parseInt(counter.text()) + (isActive ? -1 : 1));
        }
    });
    */
}
