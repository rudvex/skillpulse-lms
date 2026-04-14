/**
 * SkillPulse LMS Signup Validation JavaScript
 *
 * @package SkillPulse_LMS
 * @since 1.0.0
 */

// Main validation object - make it globally accessible
const SkillPulseSignupValidation = {
    // Validation strings
    strings: {
        required: 'This field is required.',
        usernameLength: 'Username must be at least 3 characters long.',
        passwordLength: 'Password must be at least 6 characters long.',
        nameLength: 'Name must be at least 2 characters long.',
        invalidEmail: 'Please enter a valid email address.',
        usernameInvalid: 'Username can only contain letters, numbers, and underscores.',
        passwordMismatch: 'Passwords do not match.',
        termsRequired: 'You must accept the terms and conditions.'
    },

    init: function() {
        this.bindEvents();
        this.initValidationRules();
    },

    bindEvents: function() {
        // Real-time validation on blur
        jQuery(document).on('blur', '#splms-signup-form input', this.handleFieldValidation.bind(this));
        
        // Clear errors on input
        jQuery(document).on('input', '#splms-signup-form input', this.handleFieldErrorClear.bind(this));
        
        // Password strength check
        jQuery(document).on('input', '#splms-signup-form #password', this.handlePasswordStrength.bind(this));
        
        // Confirm password validation - only for signup form
        jQuery(document).on('input', '#splms-signup-form #confirm_password', this.handleConfirmPassword.bind(this));
    },

    initValidationRules: function() {
        this.rules = {
            first_name: {
                required: true,
                minlength: 2
            },
            last_name: {
                required: true,
                minlength: 2
            },
            username: {
                required: true,
                minlength: 3,
                pattern: /^[a-zA-Z0-9_]+$/
            },
            email: {
                required: true,
                email: true
            },
            password: {
                required: true,
                minlength: 6
            },
            confirm_password: {
                required: true,
                equalTo: '#password'
            },
            user_type: {
                required: true
            },
            terms_accepted: {
                required: true
            },
            activation_key: {
                required: true,
                minlength: 32
            }
        };
    },

    validateForm: function($form) {
        var isValid = true;
        var errors = [];
        
        // Clear previous errors
        $form.find('.splms-form-error').empty().hide();
        $form.find('.field-error').remove();
        
        // Validate each field
        $form.find('input[required]').each(function() {
            var $field = jQuery(this);
            var fieldName = $field.attr('name');
            var fieldValue = $field.val() ? $field.val().trim() : '';
            
            if (!SkillPulseSignupValidation.validateField($field, fieldValue)) {
                isValid = false;
                $field.addClass('error');
                var errorMessage = SkillPulseSignupValidation.getFieldErrorMessage($field);
                if (errorMessage) {
                    errors.push(errorMessage);
                    SkillPulseSignupValidation.showFieldError($field, errorMessage);
                }
            } else {
                $field.removeClass('error');
            }
        });
        
        // Display form errors if any
        if (errors.length > 0) {
            var $errorMessage = $form.find('#error_message');
            var $errorText = $errorMessage.find('.splms-message-text');
            $errorText.text(errors.join(', '));
            $errorMessage.show();
        }
        
        return isValid;
    },

    validateField: function($field, value) {
        var fieldName = $field.attr('name');
        var rules = this.rules[fieldName];
        
        if (!rules) {
            return true;
        }
        
        // Required validation
        if (rules.required && !value) {
            return false;
        }
        
        // Min length validation
        if (rules.minlength && value.length < rules.minlength) {
            return false;
        }
        
        // Email validation
        if (rules.email && !this.isValidEmail(value)) {
            return false;
        }
        
        // Pattern validation
        if (rules.pattern && !rules.pattern.test(value)) {
            return false;
        }
        
        // Equal to validation
        if (rules.equalTo) {
            var $targetField = jQuery(rules.equalTo);
            if (value !== $targetField.val()) {
                return false;
            }
        }
        
        return true;
    },

    isValidEmail: function(email) {
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    },

    getFieldErrorMessage: function($field) {
        var fieldName = $field.attr('name');
        var value = $field.val() ? $field.val().trim() : '';
        var rules = this.rules[fieldName];
        
        if (!rules) {
            return '';
        }
        
        if (rules.required && !value) {
            return SkillPulseSignupValidation.strings.required;
        }
        
        if (rules.minlength && value.length < rules.minlength) {
            if (fieldName === 'username') {
                return SkillPulseSignupValidation.strings.usernameLength;
            } else if (fieldName === 'password') {
                return SkillPulseSignupValidation.strings.passwordLength;
            } else if (fieldName === 'first_name' || fieldName === 'last_name') {
                return SkillPulseSignupValidation.strings.nameLength;
            }
        }
        
        if (rules.email && !this.isValidEmail(value)) {
            return SkillPulseSignupValidation.strings.invalidEmail;
        }
        
        if (rules.pattern && !rules.pattern.test(value)) {
            if (fieldName === 'username') {
                return SkillPulseSignupValidation.strings.usernameInvalid;
            }
        }
        
        if (rules.equalTo) {
            return SkillPulseSignupValidation.strings.passwordMismatch;
        }
        
        return '';
    },

    handleFieldValidation: function(e) {
        var $field = jQuery(e.target);
        var value = $field.val() ? $field.val().trim() : '';
        
        if (!SkillPulseSignupValidation.validateField($field, value)) {
            $field.addClass('error');
            var errorMessage = SkillPulseSignupValidation.getFieldErrorMessage($field);
            if (errorMessage) {
                SkillPulseSignupValidation.showFieldError($field, errorMessage);
            }
        } else {
            $field.removeClass('error');
            SkillPulseSignupValidation.clearFieldError($field);
        }
    },

    handleFieldErrorClear: function(e) {
        var $field = jQuery(e.target);
        $field.removeClass('error');
        SkillPulseSignupValidation.clearFieldError($field);
    },

    handlePasswordStrength: function(e) {
        var $field = jQuery(e.target);
        var password = $field.val();
        var $form = $field.closest('#splms-signup-form');
        var $strengthIndicator = $form.find('#password_strength')
        
        if (!password) {
            $strengthIndicator.empty().hide();
            return;
        }
        
        var strength = this.calculatePasswordStrength(password);
        var strengthText = this.getPasswordStrengthText(strength);
        var strengthClass = this.getPasswordStrengthClass(strength);
        
        $strengthIndicator.html('<span class="' + strengthClass + '">' + strengthText + '</span>').show();
    },

    handleConfirmPassword: function(e) {
        var $field = jQuery(e.target);
        var confirmPassword = $field.val();
        var $form = $field.closest('#splms-signup-form');
        var password = $form.find('#password').val();
        
        if (confirmPassword && password !== confirmPassword) {
            $field.addClass('error');
            SkillPulseSignupValidation.showFieldError($field, SkillPulseSignupValidation.strings.passwordMismatch);
        } else {
            $field.removeClass('error');
            SkillPulseSignupValidation.clearFieldError($field);
        }
    },

    calculatePasswordStrength: function(password) {
        var score = 0;
        
        if (password.length >= 6) score++;
        if (password.length >= 8) score++;
        if (/[a-z]/.test(password)) score++;
        if (/[A-Z]/.test(password)) score++;
        if (/[0-9]/.test(password)) score++;
        if (/[^A-Za-z0-9]/.test(password)) score++;
        
        return score;
    },

    getPasswordStrengthText: function(strength) {
        if (strength <= 2) return 'Weak';
        if (strength <= 3) return 'Fair';
        if (strength <= 4) return 'Good';
        return 'Strong';
    },

    getPasswordStrengthClass: function(strength) {
        if (strength <= 2) return 'password-weak';
        if (strength <= 3) return 'password-fair';
        if (strength <= 4) return 'password-good';
        return 'password-strong';
    },

    showFieldError: function($field, message) {
        var fieldName = $field.attr('name') || $field.attr('id');
        var isPasswordField = (fieldName === 'password' || fieldName === 'confirm_password');
        
        if (isPasswordField) {
            // For password fields, find error container in the parent input-group structure
            var $formGroup = $field.closest('.splms-form-group');
            var $inputGroup = $field.closest('.splms-input-group');
            var $errorContainer = $inputGroup.find('.splms-form-error#' + fieldName + '_error');
            
            // If not found in input-group, check form-group
            if (!$errorContainer.length) {
                $errorContainer = $formGroup.find('.splms-form-error#' + fieldName + '_error');
            }
            
            // If still not found, create it in the input-group
            if (!$errorContainer.length && $inputGroup.length) {
                $errorContainer = jQuery('<div class="splms-form-error" id="' + fieldName + '_error"></div>');
                $inputGroup.append($errorContainer);
            }
        } else {
            // For other fields, use sibling or form-group error container
            var $formGroup = $field.closest('.splms-form-group');
            var $errorContainer = $field.siblings('.splms-form-error');
            
            // If not found as sibling, look for error container by ID in form-group
            if (!$errorContainer.length) {
                $errorContainer = $formGroup.find('.splms-form-error#' + fieldName + '_error');
            }
            
            // If still not found, create after the field
            if (!$errorContainer.length) {
                $errorContainer = jQuery('<div class="splms-form-error" id="' + fieldName + '_error"></div>');
                $field.after($errorContainer);
            }
        }
        
        // Set error message and show
        if ($errorContainer.length) {
            $errorContainer.text(message).show();
        }
    },

    clearFieldError: function($field) {
        var fieldName = $field.attr('name') || $field.attr('id');
        var isPasswordField = (fieldName === 'password' || fieldName === 'confirm_password');
        
        if (isPasswordField) {
            // For password fields, find error container in the parent input-group structure
            var $inputGroup = $field.closest('.splms-input-group');
            var $formGroup = $field.closest('.splms-form-group');
            var $errorContainer = $inputGroup.find('.splms-form-error#' + fieldName + '_error');
            
            // If not found in input-group, check form-group
            if (!$errorContainer.length) {
                $errorContainer = $formGroup.find('.splms-form-error#' + fieldName + '_error');
            }
        } else {
            // For other fields, use sibling or form-group error container
            var $formGroup = $field.closest('.splms-form-group');
            var $errorContainer = $field.siblings('.splms-form-error');
            
            // If not found as sibling, look for error container by ID in form-group
            if (!$errorContainer.length) {
                $errorContainer = $formGroup.find('.splms-form-error#' + fieldName + '_error');
            }
        }
        
        // Clear and hide error
        if ($errorContainer.length) {
            $errorContainer.empty().hide();
        }
    },

    // Remote validation for username and email availability
    validateRemote: function($field, callback) {
        var fieldName = $field.attr('name');
        var fieldValue = $field.val() ? $field.val().trim() : '';
        var rules = this.rules[fieldName];
        
        if (!rules || !rules.remote) {
            callback(true);
            return;
        }
        
        var data = jQuery.extend({}, rules.remote.data, {
            value: fieldValue
        });
        
        jQuery.ajax({
            url: rules.remote.url,
            type: rules.remote.type,
            data: data,
            dataType: 'json',
            success: function(response) {
                callback(response.success);
            },
            error: function() {
                callback(false);
            }
        });
    }
};

// Initialize when document is ready
jQuery(document).ready(function() {
    SkillPulseSignupValidation.init();
});

// Make available globally
window.SkillPulseSignupValidation = SkillPulseSignupValidation;

// Export for use in other modules
export { SkillPulseSignupValidation };
