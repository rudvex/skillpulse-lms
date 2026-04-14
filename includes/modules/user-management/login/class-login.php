<?php
/**
 * Login module class.
 *
 * Handles user login functionality and login page customization.
 *
 * @since   1.0.0
 * @package SkillPulse_LMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Login module class.
 *
 * Handles user login functionality and login page customization.
 *
 * @since 1.0.0
 */
class SkillPulse_LMS_Login {


	/**
	 * Class instance.
	 *
	 * @var object|null $instance
	 */
	private static $instance;

	/**
	 * Get the instance of this class.
	 *
	 * @return SkillPulse_LMS_Login
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			$class_name     = __CLASS__;
			self::$instance = new $class_name();
			self::$instance->setup_hooks();
		}

		return self::$instance;
	}

	/**
	 * Setup WordPress hooks for login handling.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	protected function setup_hooks() {
		// AJAX handlers for authentication.
		add_action( 'wp_ajax_nopriv_splms_forgot_password', array( $this, 'handle_forgot_password' ) );
		add_action( 'wp_ajax_nopriv_splms_reset_password', array( $this, 'handle_reset_password' ) );

		// WordPress login screen customization.
		add_action( 'login_head', array( $this, 'customize_login_head' ) );
		add_filter( 'login_headerurl', array( $this, 'customize_login_header_url' ) );
		add_filter( 'login_headertext', array( $this, 'customize_login_header_text' ) );
		add_filter( 'login_message', array( $this, 'customize_login_message' ) );
		add_filter( 'login_form_bottom', array( $this, 'customize_login_form_bottom' ) );

		// Handle WordPress login processing.
		add_action( 'wp_login', array( $this, 'handle_wp_login_success' ), 10, 2 );
		add_action( 'wp_login_failed', array( $this, 'handle_wp_login_failed' ) );

		// Removed login_url filter to prevent redirect loops and memory exhaustion.
		add_filter( 'logout_url', array( $this, 'handle_logout_url' ), 10, 2 );
		add_filter( 'logout_redirect', array( $this, 'handle_logout_redirect' ), 10, 3 );
	}

	/**
	 * Customize login page head with custom styles and scripts.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function customize_login_head() {
		// Check if custom login styling is enabled.
		if ( ! splms_get_setting( 'customize_login_screen', true ) ) {
			return;
		}

		// Debug: Add a comment to verify this is being called.
		echo '<!-- SkillPulse LMS Login Customization Loaded -->';

		// Add custom CSS for login page.
		echo '<style>
			/* Custom SkillPulse LMS Login Styles - Modern Design */
			/* Reset and Base Styles */
			html{
				height: auto;
			}
		body.login-action-login,
		body.login-action-lostpassword,
		body.login-action-checkemail {
			background: linear-gradient(90deg, #E8ECF4 100%, #F1F3F9 0%);
			font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
			padding: 0;
			min-height: calc(100dvh - 48px);
			height: auto;
			margin: 0;
			padding: 24px 16px;
			display: flex;
			flex-direction: column;
			align-items: center;
			justify-content: center;
		}

		#login {
			max-width: 450px;
			width: 100%;
			padding: 34px 24px;
			background: linear-gradient(135deg, #F8F9FF 70.71%, #F0F2FF 0%);
			box-shadow: 0px 4px 12px 0px #00000014;
			border-radius: 12px;
			margin: auto;
			border: 1px solid #E1E5F0;
			animation: fadeIn 0.4s ease-in-out;
		}

		@keyframes fadeIn {
			from {
				opacity: 0;
				transform: translateY(10px);
			}
			to {
				opacity: 1;
				transform: translateY(0);
			}
		}

		/* Logo Styling */
		#login h1 {
			text-align: center;
			margin: 0;
			padding: 0;
		}

		#login h1 a {
			display: block;
			background-size: contain;
			background-repeat: no-repeat;
			background-position: center;
			text-indent: -9999px;
			overflow: hidden;
			margin: 0 auto;
			color: #1e3a8a;
			background-size: contain;
			background-repeat: no-repeat;
			background-position: center;
			width: auto;
			height: auto;
			text-indent: 0;
			font-size: 34px;
			line-height: 42px;
			padding: 0 54px;
			font-weight: 700;
			text-decoration: none;
			display: block;
			text-align: center;
			margin-bottom: 16px;
			background: transparent;
			// background: linear-gradient(135deg, #7e75ff, #1e3a8a);
			// -webkit-background-clip: text;
			// -webkit-text-fill-color: transparent;
			// background-clip: text;
			word-wrap: break-word;
			overflow-wrap: break-word;
		}
		#login h1 a:focus{
			outline: unset;
			border: unset;
			box-shadow: unset;
		}
		/* Card Container - Form */
		#login form {
			background: transparent;
			padding: 0;
			position: relative;
			border: none;
			box-shadow: none;
		}



		/* Subtitle (via login_message hook) */
		#login .login-message{
			text-align: center;
			margin: 0 0 24px 0;
			padding: 0;
		}

		#login .splms-login-subtitle {
			text-align: center;
			margin: 0 0 34px 0;
			padding: 0;
		}

		#login .login-message p,
		#login .splms-login-subtitle p {
			color: #374151;
			font-size: 14px;
			line-height: 20px;
			margin: 0;
			font-weight: 400;
		}

		/* Form Labels */
		#login form label {
			  display: block;
				font-weight: 600;
				margin-bottom: 4px;
				font-size: 14px;
				line-height: 20px;
				color: #374151;
		}

		/* Input Fields */
		#login form .input,
		#login form input[type="text"],
		#login form input[type="password"],
		#login form input[type="email"] {
			width: 100%;
			padding: 0 16px;
			border: 1px solid #c0c0c0;
			border-radius: 8px;
			font-size: 16px;
			line-height: 24px;
			transition: all 0.2s ease;
			background-color: transparent;
			color: #111827;
			box-sizing: border-box;
			margin-bottom: 16px;
			min-height: 44px;
		}

		#login form .input:focus,
		#login form input[type="text"]:focus,
		#login form input[type="password"]:focus,
		#login form input[type="email"]:focus {
			outline: none;
			border: 1px solid #1e3a8a;
			box-shadow: unset;
		}

		/* Password Toggle Button */
		#login form .wp-pwd {
			position: relative;
		}

		#login form .wp-pwd input[type="password"] {
			padding-right: 40px;
		}

		#login form .wp-pwd .button {
			position: absolute !important;
			right: 8px !important;
			background: none !important;
			border: none !important;
			box-shadow: none !important;
			padding: 4px 8px !important;
			color: #6B7280 !important;
			cursor: pointer !important;
		}

		#login form .wp-pwd .button:hover {
			color: #1e3a8a !important;
			background: none !important;
		}

		/* Remember Me Checkbox */
		#login form .forgetmenot {
			display: flex;
			align-items: center;
			gap: 8px;
			margin: 0 0 20px 0;
		}

		#login form .forgetmenot input[type="checkbox"] {
			width: 18px;
			height: 18px;
			margin: 0;
			accent-color: #1e3a8a;
			cursor: pointer;
		}

		#login form .forgetmenot input[type="checkbox"]:focus {
			outline: unset;
			box-shadow: unset;
			border: 1px solid #1e3a8a;
		}

		#login form .forgetmenot label {
			margin: 0;
			font-weight: 400;
			cursor: pointer;
		}

		/* Submit Button */
		#login form input[type="submit"],
		#login form #wp-submit {
			display: flex;
			width: 100%;
			background: #1e3a8a;
			padding: 9px 15px;
			font-size: 16px;
			line-height: 24px;
			font-weight: 500;
			border-radius: 8px;
			border: 1px solid #1e3a8a;
			color: #FFFFFF;
			transition: all 0.3s ease;
		}

		#login form input[type="submit"]:hover,
		#login form #wp-submit:hover {
  			color: #1e3a8a;
			background: transparent;
		}

		#login form input[type="submit"]:focus,
		#login form #wp-submit:focus {
			outline: unset;
			box-shadow: unset;

		}

		/* Button Container */
		#login form p.submit {
			margin: 0;
			padding: 0;
		}

		/* Navigation Links (Register, Lost Password) */
		#login #nav,
		#login #backtoblog {
			text-align: center;
			margin-top: 20px;
			padding-top: 0;
		}

		#login #nav a,
		#login #backtoblog a {
			color: #1e3a8a;
			text-decoration: none;
			font-size: 0.875rem;
			font-weight: 500;
			transition: all 0.2s ease;
		}

		#login #nav a:hover,
		#login #backtoblog a:hover {
			text-decoration: underline;
		}

		#login #nav a:focus,
		#login #backtoblog a:focus{
			outline: unset;
			border: unset;
			box-shadow: unset;
		}

		/* Error Messages */
		#login #login_error,
		#login .message {
			background: #fef2f2;
			border: 1px solid #fecaca;
			color: #dc2626;
			padding: 12px 16px;
			border-radius: 8px;
			margin-bottom: 16px;
			font-size: 0.875rem;
			line-height: 1.5;
		}

		#login .message {
			background: #f0fdf4;
			border-color: #bbf7d0;
			color: #166534;
		}

		/* Responsive Design */
		@media (max-width: 767px) {
			#login h1 a{
			  	font-size: 28px;
				line-height: 38px;
				padding: 0;
			}
		}
		@media (max-width: 580px) {
			#login{
			   width: auto;
			   padding: 24px 16px;
			}
		}

		/* Hide default WordPress styling that might interfere */
		#login #login {
			padding: 0;
		}
		
		</style>';

		// Get custom logo URL from settings or filter.
		$logo_url = apply_filters( 'splms_login_logo_url', '' );

		if ( ! empty( $logo_url ) ) {
			echo '<style>
				#login h1 a {
					background-image: url(' . esc_url( $logo_url ) . ') !important;
					background-size: contain !important;
					background-repeat: no-repeat !important;
					background-position: center !important;
					width: 200px !important;
					height: 80px !important;
					text-indent: -9999px !important;
					overflow: hidden !important;
					-webkit-background-clip: unset !important;
					-webkit-text-fill-color: unset !important;
					background-clip: unset !important;
				}
			</style>';
		}
	}

	/**
	 * Customize login header URL.
	 *
	 * @param string $url Login header URL.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function customize_login_header_url( $url ) {
		// Check if custom login styling is enabled.
		if ( ! splms_get_setting( 'customize_login_screen', true ) ) {
			return $url;
		}

		return home_url();
	}

	/**
	 * Customize login header text.
	 *
	 * @param string $text Login header text.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function customize_login_header_text( $text ) {
		// Check if custom login styling is enabled.
		if ( ! splms_get_setting( 'customize_login_screen', true ) ) {
			return $text;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- GET parameter is safe for display purposes.
		$action = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : '';

		if ( 'lostpassword' === $action ) {
			return __( 'Lost your password?', 'skillpulse-lms' );
		}

		if ( in_array( $action, array( 'rp', 'resetpass' ), true ) ) {
			return __( 'Reset password', 'skillpulse-lms' );
		}

		if ( 'confirm_admin_email' === $action ) {
			return __( 'Confirm admin email', 'skillpulse-lms' );
		}

		// Get custom welcome title from settings.
		// translators: %s: Site name.
		$default_welcome_title = sprintf( __( 'Welcome to %s', 'skillpulse-lms' ), get_bloginfo( 'name' ) );
		$welcome_title         = splms_get_setting( 'login_welcome_title', $default_welcome_title );

		// Replace {sitename} placeholder with actual site name.
		$welcome_title = str_replace( '{sitename}', get_bloginfo( 'name' ), $welcome_title );

		// Allow filtering of the login header text.
		$custom_text = apply_filters( 'splms_login_header_text', $welcome_title );

		return $custom_text;
	}

	/**
	 * Customize login message (appears above the form).
	 *
	 * @param string $message Login message.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function customize_login_message( $message ) {
		// Check if custom login styling is enabled.
		if ( ! splms_get_setting( 'customize_login_screen', true ) ) {
			return $message;
		}

		// Only add subtitle if no existing message (like errors).
		if ( empty( $message ) ) {
			$welcome_subtitle = splms_get_setting( 'login_welcome_subtitle', __( 'Sign in to continue your learning journey', 'skillpulse-lms' ) );
			$welcome_subtitle = apply_filters( 'splms_login_welcome_subtitle', $welcome_subtitle );

			$message  = '<div class="splms-login-subtitle">';
			$message .= '<p>' . esc_html( $welcome_subtitle ) . '</p>';
			$message .= '</div>';
		}

		return $message;
	}

	/**
	 * Customize login form bottom section.
	 *
	 * @param string $content Login form content.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function customize_login_form_bottom( $content ) {
		// Check if custom login styling is enabled.
		if ( ! splms_get_setting( 'customize_login_screen', true ) ) {
			return $content;
		}

		// Add custom content after the form.
		if ( splms_get_setting( 'user_signup_enabled', false ) ) {
			$custom_content  = '<div class="splms-login-signup" style="text-align: center; margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid #e1e5e9;">';
			$custom_content .= '<p style="color: #666; margin-bottom: 0.5rem;">';
			$custom_content .= esc_html__( "Don't have an account?", 'skillpulse-lms' );
			$custom_content .= '</p>';
			$custom_content .= '<a href="' . esc_url( wp_registration_url() ) . '" style="color: #667eea; text-decoration: none; font-weight: 500;">';
			$custom_content .= esc_html__( 'Sign up here', 'skillpulse-lms' );
			$custom_content .= '</a>';
			$custom_content .= '</div>';

			return $content . $custom_content;
		}

		return $content;
	}

	/**
	 * Handle WordPress login success.
	 *
	 * @param string  $user_login User login name.
	 * @param WP_User $user       User object.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function handle_wp_login_success( $user_login, $user ) {
		// Log the successful login.
		do_action( 'splms_user_login_success', $user->ID, $user_login );

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- GET parameter is safe for redirect purposes.
		if ( ! isset( $_REQUEST['redirect_to'] ) || empty( $_REQUEST['redirect_to'] ) ) {
			$redirect_url = home_url();
			wp_safe_redirect( $redirect_url );
			exit;
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,WordPress.Security.NonceVerification.Recommended -- Will be sanitized and validated below.
		$redirect_url = isset( $_REQUEST['redirect_to'] ) ? wp_unslash( $_REQUEST['redirect_to'] ) : '';
		$redirect_url = wp_sanitize_redirect( $redirect_url );
		$redirect_url = wp_validate_redirect( $redirect_url, home_url() );
		wp_safe_redirect( $redirect_url );
		exit;
	}

	/**
	 * Handle WordPress login failure.
	 *
	 * @param string $username Username that failed to login.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function handle_wp_login_failed( $username ) {
		// Log the failed login attempt.
		do_action( 'splms_user_login_failed', $username );
	}

	/**
	 * Handle forgot password AJAX request.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function handle_forgot_password() {
		// Verify nonce.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verification is done below.
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'splms_auth_nonce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'skillpulse-lms' ) ) );
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified above.
		$user_login = isset( $_POST['user_login'] ) ? sanitize_text_field( wp_unslash( $_POST['user_login'] ) ) : '';

		if ( empty( $user_login ) ) {
			wp_send_json_error( array( 'message' => __( 'Username or email is required.', 'skillpulse-lms' ) ) );
		}

		// Use WordPress core function.
		$result = retrieve_password( $user_login );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}

		wp_send_json_success( array( 'message' => __( 'Password reset email sent! Please check your inbox.', 'skillpulse-lms' ) ) );
	}

	/**
	 * Handle reset password AJAX request.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function handle_reset_password() {
		// Verify nonce.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verification is done below.
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'splms_auth_nonce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'skillpulse-lms' ) ) );
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified above.
		$rp_key           = isset( $_POST['rp_key'] ) ? sanitize_text_field( wp_unslash( $_POST['rp_key'] ) ) : '';
		$rp_login         = isset( $_POST['rp_login'] ) ? sanitize_text_field( wp_unslash( $_POST['rp_login'] ) ) : '';
		$new_password     = isset( $_POST['new_password'] ) ? wp_unslash( $_POST['new_password'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Password will be validated by reset_password().
		$confirm_password = isset( $_POST['confirm_password'] ) ? wp_unslash( $_POST['confirm_password'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Password will be validated by reset_password().

		// Validate required fields.
		if ( empty( $rp_key ) || empty( $rp_login ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid reset link. Please request a new password reset link.', 'skillpulse-lms' ) ) );
		}

		if ( empty( $new_password ) ) {
			wp_send_json_error( array( 'message' => __( 'New password is required.', 'skillpulse-lms' ) ) );
		}

		if ( $new_password !== $confirm_password ) {
			wp_send_json_error( array( 'message' => __( 'Passwords do not match.', 'skillpulse-lms' ) ) );
		}

		// Validate password strength.
		if ( strlen( $new_password ) < 6 ) {
			wp_send_json_error( array( 'message' => __( 'Password must be at least 6 characters long.', 'skillpulse-lms' ) ) );
		}

		// Check reset key and get user.
		$user = check_password_reset_key( $rp_key, $rp_login );

		if ( is_wp_error( $user ) ) {
			wp_send_json_error( array( 'message' => $user->get_error_message() ) );
		}

		if ( ! $user || ! is_object( $user ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid reset link. Please request a new password reset link.', 'skillpulse-lms' ) ) );
		}

		/**
		 * Reset the password.
		 *
		 * @var WP_User $user User object.
		 */
		reset_password( $user, $new_password );

		wp_send_json_success(
			array(
				'message'  => __( 'Password reset successfully! You can now log in with your new password.', 'skillpulse-lms' ),
				'redirect' => wp_login_url(),
			)
		);
	}

	/**
	 * Handle logout URL.
	 *
	 * @param string $logout_url Logout URL.
	 * @param string $redirect   Redirect URL.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function handle_logout_url( $logout_url, $redirect ) {
		$redirect_page_id = splms_get_setting( 'logout_redirect_page_id' );
		if ( $redirect_page_id ) {
			$redirect_page = get_post( $redirect_page_id );
			if ( $redirect_page ) {
				$logout_url = add_query_arg( 'redirect_to', get_permalink( $redirect_page_id ), $logout_url );
			} elseif ( ! empty( $redirect ) ) {
				$logout_url = add_query_arg( 'redirect_to', rawurlencode( $redirect ), $logout_url );
			}
		}

		return $logout_url;
	}

	/**
	 * Handle logout redirect after user logs out.
	 *
	 * @param string  $redirect_to           The redirect destination URL.
	 * @param string  $requested_redirect_to The requested redirect destination URL passed as a parameter.
	 * @param WP_User $user                  The WP_User object for the user that's logging out.
	 *
	 * @since 1.0.0
	 *
	 * @return string Redirect URL.
	 */
	public function handle_logout_redirect(
		$redirect_to,
		$requested_redirect_to,
		$user
	) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- Parameter required by WordPress filter signature.
		$redirect_page_id = splms_get_setting( 'logout_redirect_page_id' );

		if ( $redirect_page_id ) {
			$redirect_page = get_post( $redirect_page_id );
			if ( $redirect_page && 'publish' === $redirect_page->post_status ) {
				return get_permalink( $redirect_page_id );
			}
		}

		// If a redirect was requested via URL parameter, use it.
		if ( ! empty( $requested_redirect_to ) ) {
			return $requested_redirect_to;
		}

		// Default: redirect to login page with loggedout parameter.
		return $redirect_to;
	}

	/**
	 * Check if current page is login page.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function is_login_page() {
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- REQUEST_URI is used for comparison only.
		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
		$current_url = home_url( $request_uri );
		if ( strpos( $current_url, wp_login_url() ) !== false ) {
			return true;
		}

		return false;
	}
}
