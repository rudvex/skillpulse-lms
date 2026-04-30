<?php
/**
 * Main plugin class loader.
 *
 * @since   1.0.0
 * @package SkillPulse_LMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main plugin class.
 *
 * Handles initialization and loading of all plugin components.
 *
 * @since 1.0.0
 */
class SkillPulse_LMS_Main {

	/**
	 * Class instance.
	 *
	 * @since 1.0.0
	 *
	 * @var SkillPulse_LMS_Main|null $instance
	 */
	private static $instance;

	/**
	 * Get the instance of this class.
	 *
	 * @since 1.0.0
	 *
	 * @return SkillPulse_LMS_Main The class instance.
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
		// Load main system components.
		$files = array(
			'includes/modules/class-modules',
			'includes/rest-api/class-rest-api',
			'includes/admin/class-admin',
			'includes/frontend/class-frontend',
		);

		foreach ( $files as $file ) {
			$path = SKILLPULSE_LMS_DIR_PATH . $file . '.php';
			if ( file_exists( $path ) ) {
				require_once $path;
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
		// Load modules.
		SkillPulse_LMS_Modules::get_instance();
		// Then load REST API.
		SkillPulse_LMS_Rest_API::get_instance();

		// Then load other components.
		SkillPulse_LMS_Admin::get_instance();
		SkillPulse_LMS_Frontend::get_instance();

	}
}
