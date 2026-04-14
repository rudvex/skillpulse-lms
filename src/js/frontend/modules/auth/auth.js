/**
 * SkillPulse LMS Authentication
 * Handles login, signup, password reset, and activation functionality
 */

class SPLMSAuth {
    constructor() {
        this.init();
    }

    init() {
        this.bindEvents();
        this.initPasswordToggles();
        this.initPasswordStrength();
        this.initRealTimeValidation();
        this.disableNativeValidation();
        this.initInputClearErrors();
        this.initCleanState();
    }

    bindEvents() {
        // Signup form submission
        jQuery(document).on('submit', '#splms-signup-form', this.handleSignup.bind(this));
        
        // Activation form submission
        jQuery(document).on('submit', '#splms-activation-form', this.handleActivation.bind(this));
        
        // Resend activation form submission
        jQuery(document).on('submit', '#splms-resend-form', this.handleResendActivation.bind(this));
        
        // Password toggle events
        jQuery(document).on('click', '.splms-password-toggle', this.togglePassword.bind(this));
    }

    initPasswordToggles() {
        // Initialize password toggle functionality
        jQuery('.splms-password-toggle').each(function() {
            const $toggle = jQuery(this);
            const target = $toggle.data('target');
            const $field = jQuery('#' + target);
            
            if ($field.length === 0) {
                console.warn('Password toggle target not found:', target);
            }
        });
    }

    initPasswordStrength() {
        // Password strength indicator
        jQuery(document).on('input', '#password', this.checkPasswordStrength.bind(this));
    }



    initRealTimeValidation() {
        // Real-time username validation
        //jQuery(document).on('blur', '#username', this.validateUsername.bind(this));
        
        // Real-time email validation
        jQuery(document).on('blur', '#email', this.validateEmail.bind(this));
        
        // Password match validation
        jQuery(document).on('blur', '#confirm_password', this.validatePasswordMatch.bind(this));
    }

    disableNativeValidation() {
        // Disable native HTML5 validation to prevent browser styling conflicts
        jQuery('#splms-signup-form').attr('novalidate', 'novalidate');
    }

    initCleanState() {
        // Ensure form starts with clean state - no errors or styling
        this.clearFieldErrors();
        this.clearMessages();
        
        // Make sure all form controls start without error styling
        jQuery('.splms-form-control').removeClass('error');
        
        // Hide all error containers
        jQuery('.splms-form-error').hide();
        
        // Clear any stuck loading states
        this.clearAllLoadingStates();
    }

    clearAllLoadingStates() {
        // Find all buttons with loading class and clear their state
        jQuery('.splms-auth-btn.loading').each((index, element) => {
            const $button = jQuery(element);
            this.setButtonLoading($button, false);
        });
    }

    initInputClearErrors() {
        // Clear errors when user starts typing
        jQuery(document).on('input focus', '.splms-form-control', function() {
            const $field = jQuery(this);
            const fieldName = $field.attr('name') || $field.attr('id');
            const $errorElement = jQuery('#' + fieldName + '_error');
            
            if ($errorElement.length) {
                $errorElement.empty().hide();
            }
            $field.removeClass('error');
        });
    }

    handleSignup(e) {
        e.preventDefault();

        const $form = jQuery(e.currentTarget);
        const $submitBtn = $form.find('button[type="submit"].splms-auth-btn-primary');

        // Add show-validation class to enable red borders on invalid fields (only after submission)
        $form.addClass('show-validation');

        // Clear previous errors
        this.clearFieldErrors();
        this.clearMessages();

        // Validate form before sending
        if (!this.validateForm($form)) {
            return; // Stop here if validation fails
        }

        // Show loading state
        this.setButtonLoading($submitBtn, true);

        // Serialize form data
        const formData = new FormData($form[0]);

        SPLMSCore.frontendAjax.signupUser(formData)
            .done((response) => {
                this.handleSignupResponse(response);
            })
            .fail((xhr, status, error) => {
                console.error('Signup AJAX Error:', status, error);
                const frontend = window.splms_frontend;
                this.showError(frontend.strings.error);
                this.setButtonLoading($submitBtn, false);
            })
            .always(() => {
                // Always stop loading state regardless of success/error
                this.setButtonLoading($submitBtn, false);
            } );
    }

    handleSignupResponse(response) {
        if (response.success) {
            this.showSuccess(response.data.message);

            // Redirect after success
            setTimeout(() => {
                if (response.data.redirect) {
                    window.location.href = response.data.redirect;
                }
            }, 5000);
        } else {
            // Show field-specific errors
            if (response.data.errors) {
                jQuery.each(response.data.errors, (field, error) => {
                    this.showFieldError(field, error);
                });
            }

            this.showError(response.data.message);
        }
    }

    handleActivation(e) {
        e.preventDefault();

        const $form = jQuery(e.currentTarget);
        const $submitBtn = $form.find('button[type="submit"].splms-auth-btn-primary');

        // Clear previous messages
        this.clearMessages();
        this.clearFieldErrors();

        // Show loading state
        this.setButtonLoading($submitBtn, true);

        // Serialize form data
        const formData = new FormData($form[0]);

        SPLMSCore.frontendAjax.activateSignup(formData)
            .done((response) => {
                this.handleActivationResponse(response);
            })
            .fail((xhr, status, error) => {
                console.error('Activation AJAX Error:', status, error);
                const frontend = window.splms_frontend;
                this.showError(frontend.strings.error);
                this.setButtonLoading($submitBtn, false);
            })
            .always(() => {
                // Always stop loading state regardless of success/error
                this.setButtonLoading($submitBtn, false);
            });
    }

    handleActivationResponse(response) {
        if (response.success) {
            this.showSuccess(response.data.message);

            // Redirect after success
            setTimeout(() => {
                if (response.data.redirect) {
                    window.location.href = response.data.redirect;
                }
            }, 2000);
        } else {
            this.showError(response.data.message);
        }
    }

    handleResendActivation(e) {
        e.preventDefault();

        const $form = jQuery(e.currentTarget);
        const $submitBtn = $form.find('button[type="submit"]');

        this.setButtonLoading($submitBtn, true);

        // Serialize form data
        const formData = new FormData($form[0]);

        SPLMSCore.frontendAjax.resendActivation(formData)
            .done((response) => {
                if (response.success) {
                    this.showAlert(response.data.message, 'success');
                    $form[0].reset();
                } else {
                    this.showAlert(response.data.message, 'error');
                }
            })
            .fail((xhr, status, error) => {
                console.error('Resend Activation AJAX Error:', status, error);
                const frontend = window.splms_frontend || {};
                this.showAlert(frontend.strings?.error || 'Something went wrong. Please try again.', 'error');
            } )
            .always(() => {
                // Always stop loading state regardless of success/error
                this.setButtonLoading($submitBtn, false);
            });
    }

    togglePassword(e) {
        e.preventDefault();

        const $toggle = jQuery(e.currentTarget);
        const target = $toggle.data('target');
        const $passwordField = jQuery('#' + target);
        const $showIcon = $toggle.find('.splms-toggle-show');
        const $hideIcon = $toggle.find('.splms-toggle-hide');

        if ($passwordField.attr('type') === 'password') {
            $passwordField.attr('type', 'text');
            $toggle.addClass('active');
            $showIcon.hide();
            $hideIcon.show().css('display', 'block');
        } else {
            $passwordField.attr('type', 'password');
            $toggle.removeClass('active');
            $showIcon.show().css('display', 'block');
            $hideIcon.hide();
        }
    }

    validateSignupForm($form) {
        let isValid = true;

        // Check required fields
        $form.find('input[required]').each(function() {
            const $field = jQuery(this);
            const value = $field.val().trim();

            if (!value) {
                isValid = false;
                $field.addClass('error');
                const fieldName = $field.attr('name');
                const $errorDiv = jQuery('#' + fieldName + '_error');
                if ($errorDiv.length) {
                    $errorDiv.text($field.data('required-message') || 'This field is required.');
                }
            } else {
                $field.removeClass('error');
            }

            // Special validation for email
            if ($field.attr('type') === 'email' && value) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(value)) {
                    isValid = false;
                    $field.addClass('error');
                    const $errorDiv = jQuery('#email_error');
                    if ($errorDiv.length) {
                        $errorDiv.text('Please enter a valid email address.');
                    }
                }
            }

            // Special validation for password confirmation
            if ($field.attr('name') === 'confirm_password' && value) {
                const password = $form.find('input[name="password"]').val();
                if (value !== password) {
                    isValid = false;
                    $field.addClass('error');
                    const $errorDiv = jQuery('#confirm_password_error');
                    if ($errorDiv.length) {
                        $errorDiv.text('Passwords do not match.');
                    }
                }
            }
        });

        // Check terms checkbox
        const $termsCheckbox = $form.find('input[name="terms_accepted"]');
        if ($termsCheckbox.length && !$termsCheckbox.is(':checked')) {
            isValid = false;
            $termsCheckbox.addClass('error');
            const $errorDiv = jQuery('#terms_accepted_error');
            if ($errorDiv.length) {
                $errorDiv.text('You must agree to the Terms and Conditions.');
            }
        }

        return isValid;
    }

    checkPasswordStrength(e) {
        const password = jQuery(e.target).val();
        const $strengthIndicator = jQuery('#password_strength');

        if (password.length === 0) {
            $strengthIndicator.hide();
            return;
        }

        let strength = 0;
        let strengthText = '';
        let strengthClass = '';

        // Check password criteria
        if (password.length >= 8) strength++;
        if (password.match(/[a-z]/)) strength++;
        if (password.match(/[A-Z]/)) strength++;
        if (password.match(/[0-9]/)) strength++;
        if (password.match(/[^a-zA-Z0-9]/)) strength++;

        switch (strength) {
            case 0:
            case 1:
                strengthText = 'Very Weak';
                strengthClass = 'very-weak';
                break;
            case 2:
                strengthText = 'Weak';
                strengthClass = 'weak';
                break;
            case 3:
                strengthText = 'Fair';
                strengthClass = 'fair';
                break;
            case 4:
                strengthText = 'Good';
                strengthClass = 'good';
                break;
            case 5:
                strengthText = 'Strong';
                strengthClass = 'strong';
                break;
        }

        $strengthIndicator.removeClass()
            .addClass('splms-password-strength ' + strengthClass)
            .html('<span>Strength: ' + strengthText + '</span>')
            .show();
    }

        validateUsername() {
        const username = jQuery('#username').val();
        const $error = jQuery('#username_error');
        
        if (username.length > 0) {
            if (!this.validateUsernameFormat(username)) {
                $error.text('Username can only contain letters, numbers, and underscores. No spaces allowed.').show();
                return false;
            } else {
                $error.empty().hide();
                return true;
            }
        }
        return true;
    }

    validateEmail() {
        const email = jQuery('#email').val();
        const $error = jQuery('#email_error');
        
        if (email.length > 0) {
            if (!this.validateEmailFormat(email)) {
                $error.text('Please enter a valid email address.').show();
                return false;
            } else {
                $error.empty().hide();
                return true;
            }
        }
        return true;
    }

    // Helper validation methods
    validateUsernameFormat(username) {
        // Username validation: only letters, numbers, underscores, no spaces, 3-20 chars
        const usernameRegex = /^[a-zA-Z0-9_]{3,20}$/;
        
        // Additional checks for common invalid usernames
        const invalidUsernames = [
            'admin', 'administrator', 'test', 'user', 'guest', 'demo',
            'test student', 'test-student', 'teststudent', 'student test',
            'null', 'undefined', 'www', 'ftp', 'mail', 'email'
        ];
        
        const lowerUsername = username.toLowerCase().replace(/[\s\-_.]/g, '');
        
        return usernameRegex.test(username) && !invalidUsernames.includes(lowerUsername);
    }

    validateEmailFormat(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

        validatePasswordMatch() {
        // Find the form that contains the confirm password field
        const $confirmPasswordField = jQuery('#confirm_password');
        const $form = $confirmPasswordField.closest('form');
        
        if (!$form.length) {
            return true; // No form found, skip validation
        }
        
        // Handle signup form (#password)
        const $passwordField = $form.find('#password');
        const $error = jQuery('#confirm_password_error');
        
        // Get password value
        const password = $passwordField.length > 0 ? $passwordField.val() : '';
        
        const confirmPassword = $confirmPasswordField.val();
        
        if (confirmPassword.length > 0 && password !== confirmPassword) {
            $error.text('Passwords do not match.').show();
            $confirmPasswordField.addClass('error');
            return false;
        } else {
            $error.empty().hide();
            $confirmPasswordField.removeClass('error');
            return true;
        }
    }



    setButtonLoading($button, isLoading) {
        // Ensure we have a valid button
        if (!$button || $button.length === 0) {
            console.warn('SkillPulse Auth: Button not found for loading state');
            return;
        }

        const $btnText = $button.find('.splms-btn-text');
        const $btnLoader = $button.find('.splms-btn-loader');

        // Check if required elements exist
        if ($btnText.length === 0 || $btnLoader.length === 0) {
            console.warn('SkillPulse Auth: Button text or loader elements not found');
            return;
        }

        if (isLoading) {
            $button.prop('disabled', true).addClass('loading');
            $btnText.hide();
            $btnLoader.show();

            // Failsafe: Clear loading after 35 seconds if not already cleared
            const buttonId = $button.attr('id') || 'unknown';
            const timeoutId = setTimeout(() => {
                if ($button.hasClass('loading')) {
                    console.warn('SkillPulse Auth: Failsafe clearing stuck loading state for button:', buttonId);
                    this.setButtonLoading($button, false);
                }
            }, 35000);

            // Store timeout ID on button for cleanup
            $button.data('loading-timeout', timeoutId);
        } else {
            // Clear any existing timeout
            const timeoutId = $button.data('loading-timeout');
            if (timeoutId) {
                clearTimeout(timeoutId);
                $button.removeData('loading-timeout');
            }

            $button.prop('disabled', false).removeClass('loading');
            $btnText.show();
            $btnLoader.hide();
        }
    }

    showSuccess(message) {
        const $successMsg = jQuery('#success_message');
        $successMsg.find('.splms-message-text').text(message);
        $successMsg.show();
    }

    showError(message) {
        const $errorMsg = jQuery('#error_message');
        $errorMsg.text(message);
        $errorMsg.show();
    }

    showFieldError(field, error) {
        const $fieldError = jQuery('#' + field + '_error');
        $fieldError.text(error).show();
    }

    clearMessages() {
        jQuery('.splms-form-message').hide();
    }

    clearFieldErrors() {
        jQuery('.splms-form-error').empty().hide();
        jQuery('.splms-form-control').removeClass('error');
    }

    showAlert(message, type = 'info') {
        // Simple alert for modals - you can enhance this with a better notification system
        if (type === 'success') {
            alert('✅ ' + message);
        } else if (type === 'error') {
            alert('❌ ' + message);
        } else {
            alert(message);
        }
    }

        // Utility method for form validation
    validatePasswordStrength(password) {
        const errors = [];
        
        if (password.length < 8) {
            errors.push('Password must be at least 8 characters long.');
        }
        if (!/[A-Z]/.test(password)) {
            errors.push('Password must contain at least one uppercase letter.');
        }
        if (!/[a-z]/.test(password)) {
            errors.push('Password must contain at least one lowercase letter.');
        }
        if (!/[0-9]/.test(password)) {
            errors.push('Password must contain at least one number.');
        }
        if (!/[^a-zA-Z0-9]/.test(password)) {
            errors.push('Password must contain at least one special character.');
        }
        
        return errors;
    }

    validateForm($form) {
        let isValid = true;
        const requiredFields = $form.find('[required]');
        
        // Clear all previous errors
        this.clearFieldErrors();
        
        requiredFields.each((index, element) => {
            const $field = jQuery(element);
            const value = $field.val().trim();
            const fieldName = $field.attr('name') || $field.attr('id');
            
            if (!value) {
                this.showFieldError(fieldName, 'This field is required.');
                this.addFieldErrorStyling($field);
                isValid = false;
            } else {
                this.removeFieldErrorStyling($field);
            }
        });
        
        // Additional signup form validation
        if ($form.attr('id') === 'splms-signup-form') {
            // Username validation
            const username = $form.find('#username').val().trim();
            if (username && !this.validateUsernameFormat(username)) {
                this.showFieldError('username', 'Username can only contain letters, numbers, and underscores. No spaces allowed.');
                this.addFieldErrorStyling($form.find('#username'));
                isValid = false;
            }
            
            // Email validation
            const email = $form.find('#email').val().trim();
            if (email && !this.validateEmailFormat(email)) {
                this.showFieldError('email', 'Please enter a valid email address.');
                this.addFieldErrorStyling($form.find('#email'));
                isValid = false;
            }
            
            // Password strength validation
            const password = $form.find('#password').val();
            if (password) {
                const passwordErrors = this.validatePasswordStrength(password);
                if (passwordErrors.length > 0) {
                    this.showFieldError('password', passwordErrors[0]); // Show first error
                    this.addFieldErrorStyling($form.find('#password'));
                    isValid = false;
                }
            }
            
            // Password match validation
            const confirmPassword = $form.find('#confirm_password').val();
            if (password && confirmPassword && password !== confirmPassword) {
                this.showFieldError('confirm_password', 'Passwords do not match.');
                this.addFieldErrorStyling($form.find('#confirm_password'));
                isValid = false;
            }
            
            // Terms validation
            const termsAccepted = $form.find('#terms_accepted').is(':checked');
            if (!termsAccepted) {
                this.showFieldError('terms_accepted', 'You must accept the terms and conditions.');
                isValid = false;
            }
        }
        
        return isValid;
    }

    // Add visual error styling to field
    addFieldErrorStyling($field) {
        $field.addClass('error');
    }

    // Remove visual error styling from field
    removeFieldErrorStyling($field) {
        $field.removeClass('error');
    }

    // Method to check if user is on mobile
    isMobile() {
        return window.innerWidth <= 768;
    }


}

// Initialize authentication when DOM is ready
jQuery(document).ready(function() {
    if (typeof window.splms_frontend !== 'undefined') {
        window.skillpulseAuth = new SPLMSAuth();
    }
});



// Export for potential external use
if (typeof module !== 'undefined' && module.exports) {
    module.exports = SPLMSAuth;
} 