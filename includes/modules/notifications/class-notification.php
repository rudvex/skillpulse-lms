<?php
/**
 * Notifications Module Class
 *
 * Centralized notification management system.
 *
 * @package SkillPulse_LMS
 * @subpackage Notifications
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * SkillPulse LMS Notification Module Class
 *
 * @since 1.0.0
 */
class SkillPulse_LMS_Notification {

	/**
	 * Class instance.
	 *
	 * @var SkillPulse_LMS_Notification|null $instance
	 */
	private static $instance = null;

	/**
	 * Get the instance of this class.
	 *
	 * @since 1.0.0
	 *
	 * @return SkillPulse_LMS_Notification The class instance.
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
	 * Setup globals.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	private function setup_globals() {
		$files = array(
			'includes/modules/notifications/email/class-email-module',
			'includes/modules/notifications/in-app/class-in-app-notifications',
			'includes/modules/notifications/in-app/class-in-app-templates',
			'includes/modules/notifications/dispatcher/class-notification-dispatcher',
			'includes/modules/notifications/class-notification-preferences',
		);

		foreach ( $files as $file ) {
			if ( file_exists( SKILLPULSE_LMS_DIR_PATH . $file . '.php' ) ) {
				require_once SKILLPULSE_LMS_DIR_PATH . $file . '.php';
			}
		}
	}

	/**
	 * Load required classes.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	private function load_classes() {
		// Initialize email module (now under notifications).
		SkillPulse_LMS_Email_Module::get_instance();

		// Initialize in-app notifications.
		SkillPulse_LMS_In_App_Notifications::get_instance();

		// Initialize notification dispatcher.
		SkillPulse_LMS_Notification_Dispatcher::get_instance();
	}
}
