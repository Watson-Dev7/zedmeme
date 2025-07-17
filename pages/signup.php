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
            <form id="signup-form" method="POST" action="../handlers/signup_handler.php">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                <h3>Sign Up</h3>
                
                <div id="signup-error" class="error-message"></div>
                <div id="signup-success" class="success-message"></div>
                
                <div class="form-field">
                    <label for="first_name">First Name</label>
                    <input type="text" id="first_name" name="first_name" placeholder="Enter your first name" required>
                </div>
                
                <div class="form-field">
                    <label for="last_name">Last Name</label>
                    <input type="text" id="last_name" name="last_name" placeholder="Enter your last name" required>
                </div>
                
                <div class="form-field">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="Enter your email" required>
                </div>
                
                <div class="form-field">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Choose a username" 
                           pattern="[A-Za-z0-9_]+" 
                           title="Username can only contain letters, numbers, and underscores"
                           required>
                </div>
                
                <div class="form-field">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" 
                           placeholder="Create a password (min 8 characters)" 
                           minlength="8" required>
                </div>
                
                <div class="form-field">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" 
                           placeholder="Confirm your password" required>
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
    <script>
    $(document).ready(function() {
        // Function to clear all error messages
        function clearErrors() {
            $('.error-message').removeClass('error').text('').hide();
            $('.form-field').removeClass('error');
        }

        // Function to show error for a specific field
        function showError(field, message) {
            const $field = $(`[name="${field}"]`);
            const $errorDiv = $(`<div class="error-message" id="${field}-error"></div>`);
            
            // Check if error message already exists
            let $existingError = $(`#${field}-error`);
            if ($existingError.length) {
                $existingError.text(message).addClass('error').show();
            } else {
                // Insert error message after the field
                $errorDiv.text(message).addClass('error').insertAfter($field).show();
            }
            
            // Highlight the field with error
            $field.addClass('error');
        }

        $('#signup-form').on('submit', function(e) {
            e.preventDefault();
            
            // Clear previous errors and messages
            clearErrors();
            $('#signup-error, #signup-success')
                .removeClass('error success')
                .text('')
                .hide();
            
            // Show loading state
            const submitBtn = $(this).find('button[type="submit"]');
            const originalBtnText = submitBtn.html();
            submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...');
            
            // Submit form via AJAX
            $.ajax({
                type: 'POST',
                url: $(this).attr('action'),
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Show success message
                        $('#signup-success')
                            .text(response.message || 'Registration successful!')
                            .addClass('success')
                            .show();
                        
                        // Redirect if specified
                        if (response.redirect) {
                            setTimeout(function() {
                                window.location.href = response.redirect;
                            }, 1500);
                        }
                    } else {
                        // Show field-specific errors if they exist
                        if (response.errors) {
                            // Show each error under its corresponding field
                            Object.entries(response.errors).forEach(([field, message]) => {
                                showError(field, message);
                            });
                            
                            // Also show a general error message
                            $('#signup-error')
                                .html('Please correct the errors below.')
                                .addClass('error')
                                .show();
                        } else {
                            // Show general error message
                            $('#signup-error')
                                .html(response.message || 'Registration failed. Please try again.')
                                .addClass('error')
                                .show();
                        }
                    }
                },
                error: function(xhr, status, error) {
                    // Reset button state
                    submitBtn.prop('disabled', false).html(originalBtnText);
                    
                    let errorMessage = 'An error occurred. Please try again.';
                    let response = null;
                    
                    try {
                        // Try to extract JSON from response (might have PHP warnings)
                        const responseText = xhr.responseText.trim();
                        const jsonStart = responseText.indexOf('{');
                        
                        if (jsonStart !== -1) {
                            // Extract JSON part from the response
                            const jsonString = responseText.substring(jsonStart);
                            response = JSON.parse(jsonString);
                            
                            if (response && response.message) {
                                errorMessage = response.message;
                                
                                // Show field-specific errors if they exist
                                if (response.errors) {
                                    Object.entries(response.errors).forEach(([field, message]) => {
                                        showError(field, message);
                                    });
                                    errorMessage = 'Please correct the errors below.';
                                }
                            }
                        } else {
                            // If no JSON found, show the raw response
                            errorMessage = responseText || errorMessage;
                        }
                    } catch (e) {
                        console.error('Error parsing response:', e);
                        errorMessage = 'An error occurred while processing the response.';
                    }
                    
                    // Show error message
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
    </script>
    <style>
        /* Form field styling */
        .form-field {
            margin-bottom: 1rem;
        }
        
        .form-field input[type="text"],
        .form-field input[type="email"],
        .form-field input[type="password"] {
            width: 100%;
            padding: 0.5rem;
            margin-bottom: 0.25rem;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .form-field input.error {
            border-color: #f44336;
            background-color: #ffebee;
        }
        
        /* Error message styling */
        .error-message {
            color: #f44336;
            border-radius: 4px;
            margin-bottom: 1rem;
            display: none;
            padding: 0.75rem;
        }
        
        .error-message.error {
            display: block;
        }
        
        /* Success message */
        .success-message {
            color: #4CAF50;
            background-color: #e8f5e9;
            border: 1px solid #c8e6c9;
            padding: 10px 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            display: none;
        }
        .success-message.success {
            display: block;
        }
        
        /* Loading spinner */
        .spinner-border {
            display: inline-block;
            width: 1rem;
            height: 1rem;
            vertical-align: text-bottom;
            border: 0.2em solid currentColor;
            border-right-color: transparent;
            border-radius: 50%;
            -webkit-animation: spinner-border .75s linear infinite;
            animation: spinner-border .75s linear infinite;
        }
        
        @keyframes spinner-border {
            to { transform: rotate(360deg); }
        }
    </style>
</body>
</html>