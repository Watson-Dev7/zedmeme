// Handle signup form submission
$(document).ready(function() {
    $('#signup-form').on('submit', function(e) {
        e.preventDefault();
        
        // Show loading state
        const submitBtn = $(this).find('button[type="submit"]');
        const originalBtnText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<span class="show-loading">Processing...</span>');
        
        // Clear previous messages
        $('#signup-error, #signup-success').removeClass('error success').text('').hide();
        
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
                    // Show success message
                    $('#signup-success')
                        .text(response.message || 'Registration successful!')
                        .addClass('success')
                        .show();
                    
                    // Redirect to login after 2 seconds
                    setTimeout(function() {
                        window.location.href = 'login.php';
                    }, 2000);
                } else {
                    // Show error message
                    $('#signup-error')
                        .html(response.message || 'Registration failed. Please try again.')
                        .addClass('error')
                        .show();
                }
            },
            error: function(xhr, status, error) {
                let errorMessage = 'An error occurred. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                $('#signup-error')
                    .html(errorMessage)
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
