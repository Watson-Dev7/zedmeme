// Handle login form submission
$(document).ready(function() {
    $('#login-form').on('submit', function(e) {
        e.preventDefault();
        
        // Show loading state
        const submitBtn = $(this).find('button[type="submit"]');
        const originalBtnText = submitBtn.text();
        submitBtn.prop('disabled', true).html('<span class="show-loading">Processing...</span>');
        
        // Clear previous errors
        $('#login-error').removeClass('error').text('').hide();
        
        // Get form data
        const formData = $(this).serialize();
        const formAction = $(this).attr('action');
        
        // Submit form via AJAX
        $.ajax({
            type: 'POST',
            url: formAction,
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Redirect on success
                    window.location.href = 'blog-simple.php';
                } else {
                    // Show error message
                    $('#login-error')
                        .text(response.message || 'Login failed. Please try again.')
                        .addClass('error')
                        .show();
                }
            },
            error: function(xhr, status, error) {
                let errorMessage = 'An error occurred. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                $('#login-error')
                    .text(errorMessage)
                    .addClass('error')
                    .show();
            },
            complete: function() {
                // Reset button state
                submitBtn.prop('disabled', false).html(originalBtnText);
            }
        });
    });
});
