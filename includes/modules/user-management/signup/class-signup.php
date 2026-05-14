<?php
/**
 * Signup management class.
 *
 * Core signup functionality for user registration and activation.
 *
 * @since   1.0.0
 * @package SkillPulse_LMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Signup management class.
 *
 * Core signup functionality for user registration and activation.
 *
 * @since 1.0.0
 */
class SkillPulse_LMS_Signup {

	/**
	 * Class instance.
	 *
	 * @var SkillPulse_LMS_Signup|null $instance
	 */
	private static $instance = null;

	/**
	 * Get the instance of this class.
	 *
	 * @return SkillPulse_LMS_Signup
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
		// AJAX handlers.
		add_action( 'wp_ajax_nopriv_splms_create_signup', array( $this, 'ajax_create_signup' ) );
		add_action( 'wp_ajax_nopriv_splms_activate_signup', array( $this, 'ajax_activate_signup' ) );
		add_action( 'wp_ajax_nopriv_splms_resend_activation', array( $this, 'ajax_resend_activation' ) );

		// URL handlers.
		add_action( 'init', array( $this, 'init' ) );

		// Admin hooks (only basic user query filtering).
		if ( is_admin() ) {
			add_action( 'pre_user_query', array( $this, 'remove_signups_from_user_query' ) );
		}
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
		add_filter( 'register_url', array( $this, 'maybe_redirect_to_registration' ) );
		// Login prevention for unactivated users.
		add_filter( 'authenticate', array( $this, 'prevent_unactivated_login' ), 30, 3 );
	}

	/**
	 * Initialize component.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function init() {
		// Handle activation URL.
		$this->handle_activation_url();
	}

	/**
	 * Get signups table name.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	private function get_signups_table_name() {
		global $wpdb;

		return $wpdb->prefix . 'splms_signups';
	}

	/**
	 * AJAX handler for creating signup.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function ajax_create_signup() {
		// Verify nonce.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verification is done below.
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'splms_auth_nonce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'skillpulse-lms' ) ) );
		}

		// Get form data.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified above.
		$first_name       = isset( $_POST['first_name'] ) ? sanitize_text_field( wp_unslash( $_POST['first_name'] ) ) : '';
		$last_name        = isset( $_POST['last_name'] ) ? sanitize_text_field( wp_unslash( $_POST['last_name'] ) ) : '';
		$user_name        = isset( $_POST['username'] ) ? sanitize_user( wp_unslash( $_POST['username'] ) ) : '';
		$user_email       = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
		$password         = isset( $_POST['password'] ) ? wp_unslash( $_POST['password'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Password will be validated by validate_signup_data().
		$confirm_password = isset( $_POST['confirm_password'] ) ? wp_unslash( $_POST['confirm_password'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Password will be validated by validate_signup_data().
		$user_type        = isset( $_POST['user_type'] ) ? sanitize_text_field( wp_unslash( $_POST['user_type'] ) ) : splms_get_setting( 'default_user_role', 'student' );
		$terms_accepted   = isset( $_POST['terms_accepted'] ) ? (bool) $_POST['terms_accepted'] : false;

		// Validate data.
		$validation = $this->validate_signup_data(
			array(
				'first_name'       => $first_name,
				'last_name'        => $last_name,
				'user_name'        => $user_name,
				'user_email'       => $user_email,
				'password'         => $password,
				'confirm_password' => $confirm_password,
				'user_type'        => $user_type,
				'terms_accepted'   => $terms_accepted,
			)
		);

		if ( is_wp_error( $validation ) ) {
			wp_send_json_error( array( 'message' => $validation->get_error_message() ) );
		}

		// Check if email already exists in signups.
		if ( $this->get_signup_by_email( $user_email ) ) {
			wp_send_json_error( array( 'message' => __( 'An account with this email is already pending activation.', 'skillpulse-lms' ) ) );
		}

		// Create signup.
		$signup_id = $this->create_signup(
			array(
				'first_name' => $first_name,
				'last_name'  => $last_name,
				'user_name'  => $user_name,
				'user_email' => $user_email,
				'password'   => $password,
				'user_type'  => $user_type,
			)
		);

		if ( is_wp_error( $signup_id ) ) {
			wp_send_json_error( array( 'message' => $signup_id->get_error_message() ) );
		}

		wp_send_json_success(
			array(
				'message'   => __( 'Account created successfully! Please check your email for activation instructions.', 'skillpulse-lms' ),
				'redirect'  => SkillPulse_LMS_Signup_Screen_Handler::get_instance()->get_signup_url( 'success' ),
				'signup_id' => $signup_id,
			)
		);
	}

	/**
	 * AJAX handler for activating signup.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function ajax_activate_signup() {
		// Verify nonce.
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'splms_auth_nonce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'skillpulse-lms' ) ) );
		}

		$activation_key = isset( $_POST['activation_key'] ) ? sanitize_text_field( wp_unslash( $_POST['activation_key'] ) ) : '';

		if ( empty( $activation_key ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid activation key.', 'skillpulse-lms' ) ) );
		}

		$result = $this->activate_signup( $activation_key );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}

		wp_send_json_success(
			array(
				'message'  => __( 'Account activated successfully! You can now log in.', 'skillpulse-lms' ),
				'user_id'  => $result,
				'redirect' => wp_login_url( SkillPulse_LMS_Signup_Screen_Handler::get_instance()->get_signup_url( 'success' ) ),
			)
		);
	}

	/**
	 * AJAX handler for resending activation email.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function ajax_resend_activation() {
		// Verify nonce.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verification is done below.
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'splms_resend_activation' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'skillpulse-lms' ) ) );
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified above.
		$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';

		if ( empty( $email ) ) {
			wp_send_json_error( array( 'message' => __( 'Please provide a valid email address.', 'skillpulse-lms' ) ) );
		}

		$signup = $this->get_signup_by_email( $email );

		if ( ! $signup ) {
			wp_send_json_error( array( 'message' => __( 'No pending signup found for this email address.', 'skillpulse-lms' ) ) );
		}

		// Check if signup is already activated.
		if ( 'activated' === $signup->status ) {
			wp_send_json_error( array( 'message' => __( 'This account has already been activated.', 'skillpulse-lms' ) ) );
		}

		// Send activation email.
		$email_module = SkillPulse_LMS_Email_Module::get_instance();
		$result       = $email_module->send_activation_email( $signup->id );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}

		wp_send_json_success(
			array(
				'message' => __( 'Activation email sent successfully! Please check your inbox.', 'skillpulse-lms' ),
			)
		);
	}

	/**
	 * Validate signup data.
	 *
	 * @param array $data Signup data.
	 *
	 * @since 1.0.0
	 *
	 * @return true|WP_Error
	 */
	public function validate_signup_data( $data ) {
		$errors = array();

		if ( empty( $data['first_name'] ) ) {
			$errors[] = __( 'First name is required.', 'skillpulse-lms' );
		}

		if ( empty( $data['last_name'] ) ) {
			$errors[] = __( 'Last name is required.', 'skillpulse-lms' );
		}

		if ( empty( $data['user_name'] ) ) {
			$errors[] = __( 'Username is required.', 'skillpulse-lms' );
		} elseif ( username_exists( $data['user_name'] ) ) {
			$errors[] = __( 'Username already exists.', 'skillpulse-lms' );
		} elseif ( SkillPulse_LMS_Signup_Query::get_instance()->login_exists( $data['user_name'] ) ) {
			$errors[] = __( 'Username is already registered and pending activation.', 'skillpulse-lms' );
		} elseif ( ! validate_username( $data['user_name'] ) ) {
			$errors[] = __( 'Invalid username format.', 'skillpulse-lms' );
		}

		if ( empty( $data['user_email'] ) ) {
			$errors[] = __( 'Email is required.', 'skillpulse-lms' );
		} elseif ( ! is_email( $data['user_email'] ) ) {
			$errors[] = __( 'Invalid email format.', 'skillpulse-lms' );
		} elseif ( email_exists( $data['user_email'] ) ) {
			$errors[] = __( 'Email already exists.', 'skillpulse-lms' );
		} elseif ( SkillPulse_LMS_Signup_Query::get_instance()->email_exists( $data['user_email'] ) ) {
			$errors[] = __( 'Email is already registered and pending activation.', 'skillpulse-lms' );
		}

		if ( empty( $data['password'] ) ) {
			$errors[] = __( 'Password is required.', 'skillpulse-lms' );
		} else {
			// Password strength validation.
			$password        = $data['password'];
			$password_errors = array();

			// Minimum length check.
			if ( strlen( $password ) < 8 ) {
				$password_errors[] = __( 'Password must be at least 8 characters long.', 'skillpulse-lms' );
			}

			// Check for uppercase letter.
			if ( ! preg_match( '/[A-Z]/', $password ) ) {
				$password_errors[] = __( 'Password must contain at least one uppercase letter.', 'skillpulse-lms' );
			}

			// Check for lowercase letter.
			if ( ! preg_match( '/[a-z]/', $password ) ) {
				$password_errors[] = __( 'Password must contain at least one lowercase letter.', 'skillpulse-lms' );
			}

			// Check for number.
			if ( ! preg_match( '/[0-9]/', $password ) ) {
				$password_errors[] = __( 'Password must contain at least one number.', 'skillpulse-lms' );
			}

			// Check for special character.
			if ( ! preg_match( '/[^a-zA-Z0-9]/', $password ) ) {
				$password_errors[] = __( 'Password must contain at least one special character.', 'skillpulse-lms' );
			}

			// Add password errors to main errors array.
			if ( ! empty( $password_errors ) ) {
				$errors = array_merge( $errors, $password_errors );
			}
		}

		if ( $data['password'] !== $data['confirm_password'] ) {
			$errors[] = __( 'Passwords do not match.', 'skillpulse-lms' );
		}

		if ( ! $this->validate_user_type( $data['user_type'] ) ) {
			$errors[] = __( 'Invalid user type.', 'skillpulse-lms' );
		}

		if ( ! $data['terms_accepted'] ) {
			$errors[] = __( 'You must accept the terms and conditions.', 'skillpulse-lms' );
		}

		if ( ! empty( $errors ) ) {
			return new WP_Error( 'validation_failed', implode( ' ', $errors ) );
		}

		return true;
	}

	/**
	 * Create a new signup.
	 *
	 * @param array $args Signup arguments.
	 *
	 * @since 1.0.0
	 *
	 * @return int|WP_Error
	 */
	public function create_signup( $args ) {
		global $wpdb;

		$defaults = array(
			'first_name' => '',
			'last_name'  => '',
			'user_name'  => '',
			'user_email' => '',
			'password'   => '',
			'user_type'  => splms_get_setting( 'default_user_role', 'student' ),
			'meta'       => array(),
		);

		$args = wp_parse_args( $args, $defaults );

		// User type validation.
		$args['user_type'] = $this->validate_user_type( $args['user_type'] );

		if ( is_wp_error( $args['user_type'] ) ) {
			return $args['user_type'];
		}

		// Hash password for storage.
		$hashed_password = wp_hash_password( $args['password'] );

		// Prepare meta data.
		$meta = array_merge(
			$args['meta'],
			array(
				'hashed_password' => $hashed_password,
			)
		);

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Custom table write operation.
		$result = $wpdb->insert(
			$this->get_signups_table_name(),
			array(
				'user_login'     => $args['user_name'],
				'user_email'     => $args['user_email'],
				'user_name'      => $args['user_name'],
				'first_name'     => $args['first_name'],
				'last_name'      => $args['last_name'],
				'user_type'      => $args['user_type'],
				'meta'           => maybe_serialize( $meta ),
				'registered'     => current_time( 'mysql' ),
				'activation_key' => wp_generate_password( 32, false ),
				'status'         => 'pending',
			),
			array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
		);

		if ( $result ) {
			$signup_id = $wpdb->insert_id;

			/**
			 * Fires after a signup is created.
			 *
			 * @param int   $signup_id Signup ID.
			 * @param array $args      Signup arguments.
			 *
			 * @since 1.0.0
			 */
			do_action( 'splms_signup_created', $signup_id, $args );

			return $signup_id;
		}

		return new WP_Error( 'signup_creation_failed', __( 'Failed to create signup.', 'skillpulse-lms' ) );
	}

	/**
	 * Validate user type.
	 *
	 * @param string $user_type User type.
	 *
	 * @since 1.0.0
	 *
	 * @return string|WP_Error
	 */
	private function validate_user_type( $user_type ) {
		$db_user_type = splms_get_setting( 'default_user_role', 'student' );

		// Restrict admin if someone changes using inspect element, so allow only if setting selected user type or Allow Signup is true.
		if ( ! empty( $user_type ) && $db_user_type !== $user_type && ! in_array( $user_type, array( 'student' ), true ) ) {
			return new WP_Error( 'invalid_user_type', __( 'User type is not allowed.', 'skillpulse-lms' ) );
		}

		return empty( $user_type ) ? $db_user_type : $user_type;
	}

	/**
	 * Activate a signup.
	 *
	 * @param string $activation_key Activation key.
	 *
	 * @since 1.0.0
	 *
	 * @return int|WP_Error
	 */
	public function activate_signup( $activation_key ) {
		global $wpdb;
		$signup = $this->get_signup_by_key( $activation_key );

		if ( ! $signup ) {
			return new WP_Error( 'invalid_key', __( 'Invalid activation key.', 'skillpulse-lms' ) );
		}

		if ( 'pending' !== $signup->status ) {
			return new WP_Error( 'already_activated', __( 'Account is already activated.', 'skillpulse-lms' ) );
		}

		// Check if username or email already exists in users table.
		if ( username_exists( $signup->user_login ) ) {
			return new WP_Error( 'username_exists', __( 'Username already exists.', 'skillpulse-lms' ) );
		}

		if ( email_exists( $signup->user_email ) ) {
			return new WP_Error( 'email_exists', __( 'Email already exists.', 'skillpulse-lms' ) );
		}

		// Get meta data.
		// Replace the password with the stored password.
		$stored_password = '';
		if ( ! empty( $signup->meta ) ) {
			$meta = maybe_unserialize( $signup->meta );
			if ( is_array( $meta ) && isset( $meta['hashed_password'] ) ) {
				$stored_password = $meta['hashed_password'];
			}
		}

		// Create user.
		$user_id = wp_create_user( $signup->user_login, $stored_password, $signup->user_email );

		if ( is_wp_error( $user_id ) ) {
			return $user_id;
		}

		if ( ! empty( $stored_password ) ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- $wpdb->users is safe.
			$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->users} SET user_pass = %s WHERE ID = %d", $stored_password, $user_id ) );

			// Clean user cache after direct password update.
			clean_user_cache( $user_id );
		}

		// Update user meta.
		wp_update_user(
			array(
				'ID'           => $user_id,
				'first_name'   => $signup->first_name,
				'last_name'    => $signup->last_name,
				'display_name' => $signup->user_name,
			)
		);

		// Set user role based on user type.
		$user = new WP_User( $user_id );
		$user->set_role( $signup->user_type );

		// Update signup status.
		$this->update_signup_status( $signup->id, 'activated' );

		/**
		 * Fires after a signup is activated.
		 *
		 * @param int    $user_id User ID.
		 * @param object $signup  Signup object.
		 *
		 * @since 1.0.0
		 */
		do_action( 'splms_signup_activated', $user_id, $signup );

		SkillPulse_LMS_Signup_Query::get_instance()->delete_signup( $signup->id );

		return $user_id;
	}

	/**
	 * Process activation from URL.
	 *
	 * @param string $activation_key Activation key.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function process_activation( $activation_key ) {
		$result = $this->activate_signup( $activation_key );

		if ( is_wp_error( $result ) ) {
			wp_die( esc_html( $result->get_error_message() ), esc_html__( 'Activation Failed', 'skillpulse-lms' ) );
		}

		// Redirect to login page with success message.
		$redirect_url = wp_login_url();
		$redirect_url = add_query_arg( 'activated', '1', $redirect_url );

		wp_safe_redirect( $redirect_url );
		exit;
	}

	/**
	 * Get signup by ID.
	 *
	 * @param int $signup_id Signup ID.
	 *
	 * @since 1.0.0
	 *
	 * @return object|null
	 */
	public function get_signup( $signup_id ) {
		return SkillPulse_LMS_Signup_Query::get_instance()->get_signup( $signup_id );
	}

	/**
	 * Get signup by activation key.
	 *
	 * @param string $activation_key Activation key.
	 *
	 * @since 1.0.0
	 *
	 * @return object|null
	 */
	public function get_signup_by_key( $activation_key ) {
		return SkillPulse_LMS_Signup_Query::get_instance()->get_signup_by_key( $activation_key );
	}

	/**
	 * Get signup by email.
	 *
	 * @param string $email Email address.
	 *
	 * @since 1.0.0
	 *
	 * @return object|null
	 */
	public function get_signup_by_email( $email ) {
		return SkillPulse_LMS_Signup_Query::get_instance()->get_signup_by_email( $email );
	}

	/**
	 * Update signup status.
	 *
	 * @param int    $signup_id Signup ID.
	 * @param string $status    New status.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	private function update_signup_status( $signup_id, $status ) {
		return SkillPulse_LMS_Signup_Query::get_instance()->update_signup_status( $signup_id, $status );
	}

	/**
	 * Prevent unactivated users from logging in.
	 *
	 * @param WP_User|WP_Error|null $user     User object or error.
	 * @param string                $username Username.
	 * @param string                $password Password.
	 *
	 * @since 1.0.0
	 *
	 * @return WP_User|WP_Error|null
	 */
	public function prevent_unactivated_login( $user, $username, $password ) {
		// Login form not used.
		if ( empty( $username ) && empty( $password ) ) {
			return $user;
		}

		// An existing WP_User with a user_status of 2 is an unactivated signup.
		if ( is_a( $user, 'WP_User' ) && 2 === (int) $user->user_status ) {
			$user_login = $user->user_login;
			// If no WP_User is found corresponding to the username, this is a potential signup.
		} elseif ( is_wp_error( $user ) && 'invalid_username' === $user->get_error_code() ) {
			$user_login = $username;
			// This is an activated user, so bail.
		} else {
			return $user;
		}

		// Look for the unactivated signup corresponding to the login name.
		$signup = $this->get_signup_by_email( $user_login );

		// No signup found, something is wrong. Let's bail.
		if ( ! $signup ) {
			return $user;
		}

		// Unactivated user account found!
		// Set up the feedback message.
		$resend_url_params = array(
			'action' => 'splms-resend-activation',
			'email'  => $signup->user_email,
		);

		$resend_url = wp_nonce_url(
			add_query_arg( $resend_url_params, wp_login_url() ),
			'splms-resend-activation'
		);

		$resend_string = '<br /><br />' . sprintf(
			// translators: %s: Resend activation URL.
			__( 'If you have not received an email yet, <a href="%s">click here to resend it</a>.', 'skillpulse-lms' ),
			esc_url( $resend_url )
		);

		return new WP_Error(
			'splms_account_not_activated',
			__( '<strong>ERROR</strong>: Your account has not been activated. Check your email for the activation link.', 'skillpulse-lms' ) . $resend_string
		);
	}

	/**
	 * Handle activation URL.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function handle_activation_url() {
		$activation_key = get_query_var( 'activation_key' );

		if ( ! empty( $activation_key ) ) {
			$this->process_activation( $activation_key );
		}
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
		$vars[] = 'activation_key';

		return $vars;
	}

	/**
	 * Remove signups from user query.
	 *
	 * @param WP_User_Query $query User query.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function remove_signups_from_user_query( $query ) {
		global $wpdb;

		// Bail if this is an ajax request.
		if ( defined( 'DOING_AJAX' ) ) {
			return;
		}

		// Bail if not on the users admin screen.
		if ( ! function_exists( 'get_current_screen' ) ) {
			return;
		}

		$current_screen = get_current_screen();
		if ( ! $current_screen || 'users' !== $current_screen->id ) {
			return;
		}

		// Bail if already querying by an existing role.
		if ( ! empty( $query->query_vars['role'] ) ) {
			return;
		}

		// Exclude users that are still pending activation using the query class.
		$pending_signups = SkillPulse_LMS_Signup_Query::get_instance()->get_signups(
			array(
				'status' => 'pending',
				'number' => -1,
			)
		);

		if ( ! empty( $pending_signups ) ) {
			$signup_emails = wp_list_pluck( $pending_signups, 'user_email' );

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- $wpdb->users is safe.
			$exclude_ids = $wpdb->get_col(
				$wpdb->prepare(
					"SELECT ID FROM {$wpdb->users} WHERE user_email IN (" . implode( ',', array_fill( 0, count( $signup_emails ), '%s' ) ) . ')',
					...$signup_emails
				)
			);

			if ( ! empty( $exclude_ids ) ) {
				$query->set( 'exclude', array_merge( $query->get( 'exclude', array() ), $exclude_ids ) );
			}
		}
	}

	/**
	 * Delete signup.
	 *
	 * @param int $signup_id Signup ID.
	 *
	 * @since 1.0.0
	 *
	 * @return int|false Number of rows deleted on success, false on failure.
	 */
	public function delete_signup( $signup_id ) {
		return SkillPulse_LMS_Signup_Query::get_instance()->delete_signup( $signup_id );
	}

	/**
	 * Redirect to registration page if conditions are met.
	 *
	 * @param string $url URL to redirect to.
	 *
	 * @since 1.0.0
	 *
	 * @return string Modified URL.
	 */
	public function maybe_redirect_to_registration( $url ) {
		// If this is a wp-login.php?action=register request and our registration is enabled.
		if ( strpos( $url, 'wp-login.php?action=register' ) !== false && splms_get_setting( 'user_signup_enabled', false ) ) {
			return SkillPulse_LMS_Signup_Screen_Handler::get_instance()->get_signup_url();
		}

		return $url;
	}

	/**
	 * Get activation link for a signup.
	 *
	 * @param object $signup Signup object.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_activation_link( $signup ) {
		// Use the new confirmation-based activation flow.
		$signup_screen_handler = SkillPulse_LMS_Signup_Screen_Handler::get_instance();

		return $signup_screen_handler->get_signup_url( 'activate/' . $signup->activation_key . '/' );
	}
}
