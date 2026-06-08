<?php
/**
 * User management module class.
 *
 * Handles user management functionality including roles, profiles, and authentication.
 *
 * @package SPLMS
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * User management module class.
 *
 * Handles user management functionality including roles, profiles, and authentication.
 *
 * @since 1.0.0
 */
class SPLMS_User_Management {

	/**
	 * Class instance.
	 *
	 * @var SPLMS_User_Management|null $instance
	 */
	private static $instance;

	/**
	 * Get the instance of this class.
	 *
	 * @return SPLMS_User_Management
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			$class_name     = __CLASS__;
			self::$instance = new $class_name();
			self::$instance->setup_globals();
			self::$instance->load_classes();
			self::$instance->setup_actions();
			self::$instance->setup_filters();
		}

		return self::$instance;
	}

	/**
	 * Setup Globals.
	 *
	 * @since 1.0.0
	 */
	protected function setup_globals() {
		$files = array(
			'includes/modules/user-management/class-profile',
			'includes/modules/user-management/class-user-activity-query',
			'includes/modules/user-management/login/class-login',
		);

		if ( splms_get_setting( 'user_signup_enabled', false ) ) {
			$files[] = 'includes/modules/user-management/signup/class-signup';
			$files[] = 'includes/modules/user-management/signup/class-signup-query';
			$files[] = 'includes/modules/user-management/signup/class-signup-screen-handler';
		}

		foreach ( $files as $file ) {
			// Include functions file.
			if ( file_exists( SPLMS_DIR_PATH . $file . '.php' ) ) {
				require SPLMS_DIR_PATH . $file . '.php';
			}
		}
	}

	/**
	 * Initiate the required classes.
	 *
	 * @since 1.0.0
	 */
	protected function load_classes() {
		SPLMS_Profile::get_instance();
		// Initialize user management.
		SPLMS_User_Activity_Query::get_instance();

		// Initialize login manager.
		SPLMS_Login::get_instance();

		if ( splms_get_setting( 'user_signup_enabled', false ) ) {
			// Initialize signup management.
			SPLMS_Signup::get_instance();
			SPLMS_Signup_Screen_Handler::get_instance();
		}
	}

	/**
	 * Action hooks
	 *
	 * @since 1.0.0
	 */
	protected function setup_actions() {
		add_action( 'admin_init', array( $this, 'register_new_roles' ) );
		add_action( 'validate_username', array( $this, 'validate_username_format' ), 10, 2 );
	}

	/**
	 * Define all filter hooks.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	protected function setup_filters() {
	}

	/**
	 * Register new user roles.
	 *
	 * @since 1.0.0
	 */
	public function register_new_roles() {
		$roles = array(
			'student' => array(
				'label'        => 'Student',
				'capabilities' => array(
					'read'    => true,
					'level_0' => true,
				),
			),
		);

		foreach ( $roles as $role => $data ) {
			if ( ! get_role( $role ) ) {
				add_role( $role, $data['label'], $data['capabilities'] );
			}
		}
	}

	/**
	 * Validate username format.
	 *
	 * @since 1.0.0
	 *
	 * @param bool   $valid    Whether username is currently valid.
	 * @param string $username Username to validate.
	 *
	 * @return bool Whether username is valid.
	 */
	public function validate_username_format( $valid, $username ) {
		// Username validation: only letters, numbers, underscores, no spaces, 3-20 chars.
		if ( ! preg_match( '/^[a-zA-Z0-9_]{3,20}$/', $username ) ) {
			return false;
		}

		// Additional checks for common invalid usernames.
		$invalid_usernames = array(
			'admin',
			'administrator',
			'test',
			'user',
			'guest',
			'demo',
			'test-student',
			'teststudent',
			'student-test',
			'studenttest',
			'null',
			'undefined',
			'www',
			'ftp',
			'mail',
			'email',
			'root',
			'support',
			'help',
			'info',
			'contact',
			'api',
			'blog',
			'news',
		);

		$lower_username = strtolower( preg_replace( '/[\s\-_.]+/', '', $username ) );

		return ! in_array( $lower_username, $invalid_usernames, true );
	}
}
