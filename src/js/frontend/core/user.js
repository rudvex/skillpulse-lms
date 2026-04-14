/**
 * SkillPulse LMS User Module
 * Handles user authentication state and user-related functionality
 */

export class SPLMSUser {
	constructor() {
		this.isLoggedIn = false;
		this.userId = null;
		this.userData = null;
		this.loginUrl = '';
		this.init();
	}

	init() {
		this.checkLoginStatus();
		this.setLoginUrl();
	}

	/**
     * Check if user is logged in by looking for WordPress user data
     */
	checkLoginStatus() {
		// Check if WordPress user data is available
		if (typeof window.splmsUserData !== 'undefined') {
			this.isLoggedIn = window.splmsUserData.isLoggedIn || false;
			this.userId = window.splmsUserData.userId || null;
			this.userData = window.splmsUserData.userData || null;
		} else {
			// Check for common WordPress indicators
			this.isLoggedIn = this.detectLoginStatus();
		}
	}

	/**
     * Detect login status using common WordPress indicators
     */
	detectLoginStatus() {
		// Check for WordPress admin bar
		if (document.getElementById('wpadminbar')) {
			return true;
		}

		// Check for logged-in class on body
		if (document.body && document.body.classList.contains('logged-in')) {
			return true;
		}

		// Check for user-specific elements
		if (document.querySelector('.user-menu, .user-profile, .user-avatar')) {
			return true;
		}

		return false;
	}

	/**
     * Set login URL
     */
	setLoginUrl() {
		if (typeof window.splmsUserData !== 'undefined' && window.splmsUserData.loginUrl) {
			this.loginUrl = window.splmsUserData.loginUrl;
		} else {
			// Default WordPress login URL
			this.loginUrl = '/wp-login.php';
		}
	}

	/**
     * Get user ID
     */
	getUserId() {
		return this.userId;
	}

	/**
     * Get user data
     */
	getUserData() {
		return this.userData;
	}

	/**
     * Check if user is logged in
     */
	isUserLoggedIn() {
		return this.isLoggedIn;
	}

	/**
     * Get login URL
     */
	getLoginUrl() {
		return this.loginUrl;
	}

	/**
     * Redirect to login page with return URL
     */
	redirectToLogin(returnUrl = null) {
		if (!returnUrl) {
			returnUrl = encodeURIComponent(window.location.href);
		}
		window.location.href = `${this.loginUrl}?redirect_to=${returnUrl}`;
	}

	/**
     * Refresh user data
     */
	refresh() {
		this.checkLoginStatus();
		this.setLoginUrl();
	}
}
