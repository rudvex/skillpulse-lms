<?php
/**
 * Signup screen handler class.
 *
 * Handles signup page routing and display.
 *
 * @since   1.0.0
 * @package SkillPulse_LMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Signup screen handler class.
 *
 * Handles signup page routing and display.
 *
 * @since 1.0.0
 */
class SkillPulse_LMS_Signup_Screen_Handler {

	/**
	 * Class instance.
	 *
	 * @var SkillPulse_LMS_Signup_Screen_Handler|null $instance
	 */
	private static $instance = null;

	/**
	 * Get the instance of this class.
	 *
	 * @return SkillPulse_LMS_Signup_Screen_Handler
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			$class_name     = __CLASS__;
			self::$instance = new $class_name();
			self::$instance->setup_actions();
			self::$instance->setup_filters();
		}

		return self::$instance;
	}

	/**
	 * Setup action hooks.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	private function setup_actions() {
		// Core WordPress hooks.
		add_action( 'init', array( $this, 'add_rewrite_rules' ) );
		add_action( 'template_redirect', array( $this, 'handle_signup_page' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_signup_scripts' ) );

		// Custom hooks for extensibility.
		add_action( 'splms_screens', array( $this, 'screen_signup' ) );

		// Flush rewrite rules on activation.
		add_action( 'splms_activation_hook', array( $this, 'flush_rewrite_rules_on_activation' ) );

		// Force flush if rules don't exist yet.
		add_action( 'init', array( $this, 'maybe_flush_rewrite_rules' ), 15 );
	}

	/**
	 * Setup filter hooks.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	private function setup_filters() {
		add_filter( 'query_vars', array( $this, 'add_query_vars' ) );
		add_filter( 'wp_redirect', array( $this, 'maybe_redirect_signup' ), 10, 1 );
	}

	/**
	 * Add rewrite rules for signup.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function add_rewrite_rules() {
		// Get signup slug from settings or use default.
		$signup_slug = $this->get_signup_slug();

		// Add rewrite rule for signup page.
		add_rewrite_rule(
			'^' . $signup_slug . '/?$',
			'index.php?splms_component=signup&splms_action=signup',
			'top'
		);

		// Add rewrite rule for signup success.
		add_rewrite_rule(
			'^' . $signup_slug . '/success/?$',
			'index.php?splms_component=signup&splms_action=success',
			'top'
		);

		// Add rewrite rule for activation.
		add_rewrite_rule(
			'^' . $signup_slug . '/activate/([^/]+)/?$',
			'index.php?splms_component=signup&splms_action=activate&activation_key=$matches[1]',
			'top'
		);
	}

	/**
	 * Add query vars.
	 *
	 * @param array $vars Query vars.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function add_query_vars( $vars ) {
		$vars[] = 'splms_component';
		$vars[] = 'splms_action';
		$vars[] = 'activation_key';

		return $vars;
	}

	/**
	 * Get signup slug.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	private function get_signup_slug() {
		// Try to get from settings first.
		$signup_page_id = splms_get_setting( 'signup_page_id', 0 );

		if ( $signup_page_id ) {
			$page = get_post( $signup_page_id );

			if ( $page && 'publish' === $page->post_status ) {
				return $page->post_name;
			}
		}

		// Default slug (matches existing system).
		return apply_filters( 'splms_signup_slug', 'signup' );
	}

	/**
	 * Get signup URL.
	 *
	 * @param string $path Additional path.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_signup_url( $path = '' ) {
		$signup_slug = $this->get_signup_slug();
		if ( $path ) {
			$path = ltrim( $path, '/' );
		}

		return home_url( '/' . $signup_slug . '/' . $path );
	}

	/**
	 * Handle signup page requests.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function handle_signup_page() {
		$component = get_query_var( 'splms_component' );
		$action    = get_query_var( 'splms_action' );

		if ( 'signup' !== $component ) {
			return;
		}

		// Redirect if signup is disabled.
		if ( ! splms_get_setting( 'user_signup_enabled', false ) ) {
			wp_safe_redirect( home_url() );
			exit;
		}

		// Set global variables for template loading.
		global $splms_signup;
		$splms_signup                    = new stdClass();
		$splms_signup->current_component = $component;
		$splms_signup->current_action    = $action;
		$splms_signup->activation_key    = get_query_var( 'activation_key' );
		$splms_signup->errors            = array();
		$splms_signup->step              = $this->get_current_step( $action );

		// Fire action hook for extensibility.
		do_action( 'splms_screens' );
	}

	/**
	 * Get current signup step.
	 *
	 * @param string $action Current action.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	private function get_current_step( $action ) {
		switch ( $action ) {
			case 'success':
				return 'signup-success';
			case 'activate':
				return 'activation';
			default:
				return 'signup';
		}
	}

	/**
	 * Main signup screen handler.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function screen_signup() {
		global $splms_signup;

		if ( ! isset( $splms_signup->current_component ) || 'signup' !== $splms_signup->current_component ) {
			return;
		}

		// Redirect logged-in users.
		if ( is_user_logged_in() ) {
			$redirect_to = apply_filters( 'splms_loggedin_signup_page_redirect_to', home_url() );
			wp_safe_redirect( $redirect_to );
			exit;
		}

		// Handle form submission.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verification is done below.
		if ( isset( $_POST['splms_signup_submit'] ) && isset( $_POST['splms_nonce'] ) && wp_verify_nonce(
			sanitize_text_field( wp_unslash( $_POST['splms_nonce'] ) ),
			'splms_signup_nonce'
		) ) {
			$this->process_signup_form();
		}

		// Handle activation form submission.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verification is done below.
		if ( 'activate' === $splms_signup->current_action && ! empty( $splms_signup->activation_key ) && isset( $_POST['splms_activate_submit'] ) && isset( $_POST['splms_activate_nonce'] ) && wp_verify_nonce(
			sanitize_text_field( wp_unslash( $_POST['splms_activate_nonce'] ) ),
			'splms_activate_nonce'
		) ) {
			$this->process_activation( $splms_signup->activation_key );
		}

		/**
		 * Fires before loading signup template.
		 */
		do_action( 'splms_core_screen_signup' );
	}

	/**
	 * Process signup form.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	private function process_signup_form() {
		global $splms_signup;

		// Validate and process form data.
		// Nonce verified in screen_signup() before calling this method.
		// phpcs:disable WordPress.Security.NonceVerification.Missing -- Nonce verified in screen_signup().
		$first_name       = isset( $_POST['first_name'] ) ? sanitize_text_field( wp_unslash( $_POST['first_name'] ) ) : '';
		$last_name        = isset( $_POST['last_name'] ) ? sanitize_text_field( wp_unslash( $_POST['last_name'] ) ) : '';
		$username         = isset( $_POST['username'] ) ? sanitize_user( wp_unslash( $_POST['username'] ) ) : '';
		$email            = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
		$password         = isset( $_POST['password'] ) ? wp_unslash( $_POST['password'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Password will be validated by validate_signup_data().
		$confirm_password = isset( $_POST['confirm_password'] ) ? wp_unslash( $_POST['confirm_password'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Password will be validated by validate_signup_data().
		$user_type        = isset( $_POST['user_type'] ) ? sanitize_text_field( wp_unslash( $_POST['user_type'] ) ) : splms_get_setting( 'default_user_role', 'student' );
		$terms_accepted   = isset( $_POST['terms_accepted'] ) && (bool) $_POST['terms_accepted'];
		// phpcs:enable WordPress.Security.NonceVerification.Missing

		// Validate data using existing signup class.
		$signup_instance = SkillPulse_LMS_Signup::get_instance();
		$validation      = $signup_instance->validate_signup_data(
			array(
				'first_name'       => $first_name,
				'last_name'        => $last_name,
				'user_name'        => $username,
				'user_email'       => $email,
				'password'         => $password,
				'confirm_password' => $confirm_password,
				'user_type'        => $user_type,
				'terms_accepted'   => $terms_accepted,
			)
		);

		if ( is_wp_error( $validation ) ) {
			$splms_signup->errors = array( 'general' => $validation->get_error_message() );

			return;
		}

		// Create signup.
		$signup_id = $signup_instance->create_signup(
			array(
				'first_name' => $first_name,
				'last_name'  => $last_name,
				'user_name'  => $username,
				'user_email' => $email,
				'password'   => $password,
				'user_type'  => $user_type,
			)
		);

		if ( is_wp_error( $signup_id ) ) {
			$splms_signup->errors = array( 'general' => $signup_id->get_error_message() );

			return;
		}

		// Redirect to success page.
		wp_safe_redirect( $this->get_signup_url() . 'success/' );
		exit;
	}

	/**
	 * Process activation.
	 *
	 * @param string $activation_key Activation key.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	private function process_activation( $activation_key ) {
		$signup_instance = SkillPulse_LMS_Signup::get_instance();
		$result          = $signup_instance->activate_signup( $activation_key );

		if ( is_wp_error( $result ) ) {
			global $splms_signup;
			$splms_signup->errors = array( 'activation' => $result->get_error_message() );

			return;
		}

		// Redirect to login with success message.
		wp_safe_redirect( add_query_arg( 'activated', '1', wp_login_url() ) );
		exit;
	}


	/**
	 * Enqueue signup scripts.
	 *
	 * Note: Frontend CSS and JS are already enqueued globally by SkillPulse_LMS_Frontend class.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function enqueue_signup_scripts() {
		global $splms_signup;

		if ( ! isset( $splms_signup->current_component ) || 'signup' !== $splms_signup->current_component ) {
			return;
		}

		// Frontend assets are already enqueued globally by the frontend class.
		// Just ensure they're loaded (they should be already).
		if ( ! wp_style_is( 'splms-frontend-style', 'enqueued' ) ) {
			wp_enqueue_style( 'splms-frontend-style' );
		}

		if ( ! wp_script_is( 'splms-frontend-script', 'enqueued' ) ) {
			wp_enqueue_script( 'splms-frontend-script' );
		}
	}

	/**
	 * Maybe redirect signup-related URLs.
	 *
	 * @param string $location Redirect location.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function maybe_redirect_signup( $location ) {
		// Handle WordPress default registration redirects.
		if ( strpos( $location, 'wp-login.php?action=register' ) !== false && splms_get_setting( 'user_signup_enabled', false ) ) {
			return $this->get_signup_url();
		}

		return $location;
	}

	/**
	 * Check if current page is signup page.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public static function is_signup_page() {
		return 'signup' === get_query_var( 'splms_component' );
	}

	/**
	 * Check if current page is activation page.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public static function is_activation_page() {
		return self::is_signup_page() && 'activate' === get_query_var( 'splms_action' );
	}

	/**
	 * Get current signup step.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public static function get_current_signup_step() {
		global $splms_signup;

		return isset( $splms_signup->step ) ? $splms_signup->step : 'signup';
	}

	/**
	 * Flush rewrite rules on plugin activation.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function flush_rewrite_rules_on_activation() {
		$this->add_rewrite_rules();
		flush_rewrite_rules();
	}

	/**
	 * Maybe flush rewrite rules if they don't work.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function maybe_flush_rewrite_rules() {
		// Only check if signup is enabled.
		if ( ! splms_get_setting( 'user_signup_enabled', false ) ) {
			return;
		}

		// Don't flush too frequently.
		if ( get_transient( 'splms_rewrite_rules_flushed' ) ) {
			return;
		}

		// Check if we need to flush rules by testing if our rules work.
		$signup_slug = $this->get_signup_slug();

		// Get rewrite rules.
		$rules    = get_option( 'rewrite_rules' );
		$our_rule = '^' . $signup_slug . '/?$';

		// If our rule doesn't exist, flush the rules.
		if ( ! isset( $rules[ $our_rule ] ) ) {
			flush_rewrite_rules();

			// Set a transient to prevent repeated flushes.
			set_transient( 'splms_rewrite_rules_flushed', true, 3600 );
		}
	}
}
