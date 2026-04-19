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
class SkillPulse_LMS_Modules {

	/**
	 * Class instance.
	 *
	 * @since 1.0.0
	 *
	 * @var SkillPulse_LMS_Modules|null $instance
	 */
	private static $instance;

	/**
	 * Get the instance of this class.
	 *
	 * @since 1.0.0
	 *
	 * @return SkillPulse_LMS_Modules The class instance.
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
			'includes/modules/activity/class-user-activity-query',
			'includes/modules/enrollment/class-enrollments-query',
			'includes/modules/enrollment/class-enrollment',
			'includes/modules/core/class-access-control',
			'includes/modules/integrations/class-membership-integration',
			'includes/modules/integrations/class-membership-display',
		);

		foreach ( $files as $file ) {
			// Include functions file.
			if ( file_exists( SKILLPULSE_LMS_DIR_PATH . $file . '.php' ) ) {
				require_once SKILLPULSE_LMS_DIR_PATH . $file . '.php';
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
		SkillPulse_LMS_Upgrade::init();
		SkillPulse_LMS_Core::get_instance();
		SkillPulse_LMS_User_Management::get_instance();
		SkillPulse_LMS_Courses::get_instance();
		SkillPulse_LMS_Sections::get_instance();
		SkillPulse_LMS_Lessons::get_instance();
		SkillPulse_LMS_Quizzes::get_instance();
		SkillPulse_LMS_Enrollment::get_instance();
		SkillPulse_LMS_Orders::get_instance();

		SkillPulse_LMS_Access_Control::get_instance();

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
		SkillPulse_LMS_Membership_Integration::init_registrations();
	}
}
