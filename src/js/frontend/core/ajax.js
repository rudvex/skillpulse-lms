/**
 * SkillPulse LMS Frontend AJAX Handler
 * Handles AJAX requests for frontend functionality
 */

export class SPLMSFrontendAjax {
	constructor() {
		const frontend = window.splms_frontend || {};
		this.ajaxUrl = frontend.ajax_url;
		this.nonce = frontend.nonces?.splms_nonce || '';
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
     * Toggle bookmark (for lessons and quizzes)
     * @param {number} itemId - Lesson or Quiz ID
     * @param {string} type - 'lesson' or 'quiz'
     * @returns {Promise}
     */
	toggleBookmark(itemId, type = 'lesson') {
		const dataKey = type === 'quiz' ? 'quiz_id' : 'lesson_id';
		return this.request({
			data: {
				action: 'splms_toggle_bookmark',
				[dataKey]: itemId
			}
		});
	}

	/**
     * Enroll in course
     * @param {number} courseId - Course ID
     * @returns {Promise}
     */
	enrollCourse(courseId) {
		return this.request({
			data: {
				action: 'splms_enroll_course',
				course_id: courseId
			}
		});
	}

	/**
     * Unenroll from course
     * @param {number} courseId - Course ID
     * @returns {Promise}
     */
	unenrollCourse(courseId) {
		return this.request({
			data: {
				action: 'splms_unenroll_course',
				course_id: courseId
			}
		});
	}

	/**
     * Toggle course wishlist
     * @param {number} courseId - Course ID
     * @returns {Promise}
     */
	toggleWishlist(courseId) {
		return this.request({
			data: {
				action: 'splms_toggle_wishlist',
				course_id: courseId
			}
		});
	}

	/**
     * Get course purchase URL
     * @param {number} courseId - Course ID
     * @returns {Promise}
     */
	getCoursePurchaseUrl(courseId) {
		return this.request({
			data: {
				action: 'splms_get_course_purchase_url',
				course_id: courseId
			}
		});
	}

	/**
     * Update user profile
     * @param {FormData} formData - Form data
     * @returns {Promise}
     */
	updateProfile(formData) {
		formData.append('action', 'splms_update_profile');
		formData.append('nonce', this.nonce);
        
		return this.request({
			data: formData,
			processData: false,
			contentType: false
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
		window.console?.error('AJAX Error:', {
			status: status,
			error: error,
			response: xhr.responseText
		});
        
		// Show user-friendly error message
		window.SPLMSCore.helper.showNotification(
			'Something went wrong. Please try again.',
			'error'
		);
	}

	/**
     * Signup user
     * @param {FormData} formData - Form data
     * @returns {Promise}
     */
	signupUser(formData) {
		formData.append('action', 'splms_create_signup');

		return this.request({
			data: formData,
			processData: false,
			contentType: false
		});
	}

	/**
     * Forget password
     * @param {FormData} formData - Form data
     * @returns {Promise}
     */
	forgotPassword(formData) {
		formData.append( 'action', 'splms_forgot_password' );

		return this.request( {
			data: formData,
			processData: false,
			contentType: false
		} );
	}

	resendActivation( formData ) {
		formData.append( 'action', 'splms_resend_activation' );

		return this.request( {
			data: formData,
			processData: false,
			contentType: false
		} );
	}

	/**
     * Reset password
     * @param {FormData} formData - Form data
     * @returns {Promise}
     */
	resetPassword(formData) {
		formData.append('action', 'splms_reset_password');

		return this.request({
			data: formData,
			processData: false,
			contentType: false
		});
	}

	/**
     * Activate signup
     * @param {FormData} formData - Form data
     * @returns {Promise}
     */
	activateSignup(formData) {
		formData.append('action', 'splms_activate_signup');

		return this.request({
			data: formData,
			processData: false,
			contentType: false
		});
	}
} 