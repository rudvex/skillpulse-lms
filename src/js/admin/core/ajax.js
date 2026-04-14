/**
 * SkillPulse LMS Admin AJAX Handler
 * Handles AJAX requests for admin functionality
 */
export class SPLMSAdminAjax {
	constructor() {
		this.ajaxUrl = ajaxurl || '/wp-admin/admin-ajax.php';
		this.nonce = SPLMSCore_Data?.nonce || '';
	}

	/**
     * Make AJAX request
     * @param {Object} options - AJAX options
     * @returns {Promise}
     */
	request(options) {
		const defaults = {
			url: this.ajaxUrl,
			type: 'POST',
			dataType: 'json',
			data: {
				nonce: this.nonce
			}
		};

		const settings = jQuery.extend({}, defaults, options);
        
		// Add nonce to data if not already present
		if (settings.data && !settings.data.nonce) {
			settings.data.nonce = this.nonce;
		}

		return jQuery.ajax(settings);
	}

	/**
     * Submit a form by ID using AJAX with TinyMCE support
     * @param {string} formId - The ID of the form to submit
     * @returns {Promise}
     */
	submitFormById(formId) {
		const formData = new FormData(jQuery(`#${formId}`)[0]);

		// Find the textarea connected to TinyMCE dynamically within the form
		jQuery(`#${formId} textarea.wp-editor-area`).each(function() {
			const textareaId = jQuery(this).attr('id');
			const editor = tinymce.get(textareaId);

			if (editor) {
				const content = editor.getContent();
				formData.append(textareaId, content);
			} else {
				window.console?.error('TinyMCE editor not found for textarea ID: ' + textareaId);
			}
		});

		return this.request({
			data: formData,
			processData: false,
			contentType: false
		});
	}

	/**
     * Get signup details for admin review
     * @param {number} signupId - The signup ID
     * @returns {Promise}
     */
	getSignupDetails(signupId) {
		return this.request({
			type: 'GET',
			data: {
				action: 'splms_get_signup_details',
				signup_id: signupId,
				nonce: SPLMSCore_Data.nonces?.review || this.nonce
			}
		});
	}

	/**
     * Process signup action (activate/delete/resend)
     * @param {number} signupId - The signup ID  
     * @param {string} action - The action to perform (activate/delete/resend)
     * @returns {Promise}
     */
	processSignup(signupId, action) {
		return this.request({
			data: {
				action: 'splms_process_signup',
				signup_id: signupId,
				process_action: action,
				nonce: SPLMSCore_Data.nonces?.process || this.nonce
			}
		});
	}


	/**
     * Generic form submission
     * @param {string} action - AJAX action
     * @param {FormData|Object} data - Form data or object
     * @returns {Promise}
     */
	submitForm(action, data) {
		if (data instanceof FormData) {
			data.append('action', action);
			data.append('nonce', this.nonce);
            
			return this.request({
				data: data,
				processData: false,
				contentType: false
			});
		} else {
			return this.request({
				data: {
					action: action,
					...data
				}
			});
		}
	}

	/**
     * Handle AJAX error
     * @param {Object} xhr - XMLHttpRequest object
     * @param {string} status - Status text
     * @param {string} error - Error message
     */
	handleError(xhr, status, error) {
		window.console?.error('Admin AJAX Error:', {
			status: status,
			error: error,
			response: xhr.responseText
		});
        
		// Show user-friendly error message
		window.SPLMSCore.helper.toastNotice(
			'Something went wrong. Please try again.',
			'error'
		);
	}
}

// Export is already done in class declaration above
