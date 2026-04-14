<?php
/**
 * Core module class.
 *
 * Handles initialization and loading of core system components.
 *
 * @package SkillPulse_LMS
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Core module class.
 *
 * @since 1.0.0
 */
class SkillPulse_LMS_Core {

	/**
	 * Class instance.
	 *
	 * @since 1.0.0
	 *
	 * @var SkillPulse_LMS_Core|null $instance
	 */
	private static $instance;

	/**
	 * Get the instance of this class.
	 *
	 * @since 1.0.0
	 *
	 * @return SkillPulse_LMS_Core The class instance.
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
			'includes/modules/core/class-database',
			'includes/modules/core/class-settings',
			'includes/modules/core/helper/class-file-manager',
		);

		foreach ( $files as $file ) {
			// Include functions file.
			if ( file_exists( SKILLPULSE_LMS_DIR_PATH . $file . '.php' ) ) {
				require SKILLPULSE_LMS_DIR_PATH . $file . '.php';
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
		SkillPulse_LMS_Database::get_instance();
		SkillPulse_LMS_Settings::get_instance();

		// Initialize file system.
		SkillPulse_LMS_File_Manager::init_directories();
	}
}
