<?php
/**
 * Modules loader class.
 *
 * Handles loading and initialization of all plugin modules.
 *
 * @since   1.0.0
 * @package SkillPulse_LMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Modules loader class.
 *
 * @since 1.0.0
 */
class SPLMS_Modules {

	/**
	 * Class instance.
	 *
	 * @since 1.0.0
	 *
	 * @var SPLMS_Modules|null $instance
	 */
	private static $instance;

	/**
	 * Get the instance of this class.
	 *
	 * @since 1.0.0
	 *
	 * @return SPLMS_Modules The class instance.
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			$class_name     = __CLASS__;
			self::$instance = new $class_name();
			self::$instance->setup_globals();
			self::$instance->load_classes();
		}

		return self::$instance;
	}

	/**
	 * Setup Globals.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	protected function setup_globals() {
		$files = array(
			'includes/modules/core/class-upgrade',
			'includes/modules/core/class-config-loader',
			'includes/modules/core/class-core',
			'includes/modules/user-management/class-user-management',
			'includes/modules/courses/class-courses',
			'includes/modules/sections/class-sections',
			'includes/modules/sections/class-section-access-query',
			'includes/modules/lessons/class-lessons',
			'includes/modules/quizzes/class-quizzes',
			'includes/modules/orders/class-orders',
			'includes/modules/enrollment/class-enrollments-query',
			'includes/modules/enrollment/class-enrollment',
			'includes/modules/notifications/class-notification',
			'includes/modules/core/class-access-control',
			'includes/modules/integrations/class-membership-integration',
			'includes/modules/integrations/class-membership-display',
		);

		foreach ( $files as $file ) {
			// Include functions file.
			if ( file_exists( SPLMS_DIR_PATH . $file . '.php' ) ) {
				require_once SPLMS_DIR_PATH . $file . '.php';
			}
		}
	}

	/**
	 * Initiate the required classes.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	protected function load_classes() {
		SPLMS_Upgrade::init();
		SPLMS_Core::get_instance();
		SPLMS_User_Management::get_instance();
		SPLMS_Courses::get_instance();
		SPLMS_Sections::get_instance();
		SPLMS_Lessons::get_instance();
		SPLMS_Quizzes::get_instance();
		SPLMS_Enrollment::get_instance();
		SPLMS_Orders::get_instance();

		// Initialize notification module (includes email and in-app notifications).
		SPLMS_Notification::get_instance();
		SPLMS_Access_Control::get_instance();

		// Register built-in membership integrations.
		add_filter( 'splms_register_membership_integrations', array( $this, 'register_membership_integrations' ) );

		// Initialize membership integrations on init hook.
		add_action( 'init', array( $this, 'init_membership_integrations' ), 5 );
	}

	/**
	 * Register built-in membership integrations.
	 *
	 * @param array $integrations Array of integration class names.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function register_membership_integrations( $integrations ) {
		return $integrations;
	}

	/**
	 * Initialize membership integrations.
	 * Called on 'init' hook.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function init_membership_integrations() {
		SPLMS_Membership_Integration::init_registrations();
	}
}
