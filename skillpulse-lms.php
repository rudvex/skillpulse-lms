<?php
/**
 * Plugin Name: SkillPulse LMS
 * Description: Create and deliver online courses with ease. A powerful WordPress LMS solution for educators and course creators.
 * Version: 1.0.0
 * Author: SkillPulseLMS
 * Author URI: https://skillpulselms.com
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: skillpulse-lms
 *
 * @package SkillPulse_LMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load constants.
require_once plugin_dir_path( __FILE__ ) . 'constants.php';

// Register activation hook.
register_activation_hook( __FILE__, array( 'SkillPulse_LMS', 'activation_hook' ) );
// Register deactivation hook.
register_deactivation_hook( __FILE__, array( 'SkillPulse_LMS', 'deactivation_hook' ) );

if ( ! defined( 'SKILLPULSE_LMS_VERSION' ) ) {
	return;
}

if ( ! defined( 'SKILLPULSE_LMS_PLUGIN_BASENAME' ) ) {
	define( 'SKILLPULSE_LMS_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
}

if ( ! defined( 'SKILLPULSE_LMS_FILE' ) ) {
	define( 'SKILLPULSE_LMS_FILE', __FILE__ );
}

/**
 * Main class of SkillPulse LMS.
 *
 * @package SkillPulse_LMS
 */
if ( ! class_exists( 'SkillPulse_LMS' ) ) {
	/**
	 * Main SkillPulse LMS class.
	 *
	 * @since   1.0.0
	 * @package SkillPulse_LMS
	 */
	class SkillPulse_LMS {


		/**
		 * Class instance.
		 *
		 * @since 1.0.0
		 *
		 * @var SkillPulse_LMS|null $instance
		 */
		private static $instance;

		/**
		 * Get the instance of this class.
		 *
		 * @since 1.0.0
		 *
		 * @return SkillPulse_LMS The class instance.
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
		 *
		 * @return void
		 */
		protected function setup_globals() {
			require_once SKILLPULSE_LMS_DIR_PATH . 'includes/functions.php';
			require_once SKILLPULSE_LMS_DIR_PATH . 'includes/class-main.php';
		}

		/**
		 * Initiate the required classes.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		protected function load_classes() {
			// Initialize plugin main class.
			SkillPulse_LMS_Main::get_instance();
		}

		/**
		 * Action hooks.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		protected function setup_actions() {
			add_action( 'admin_init', array( $this, 'initialize_admin' ) );
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
		 * Called on admin_init.
		 *
		 * Flush rewrite rules are not called directly on activation hook, because CPT are not initialized yet.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function initialize_admin() {
			if ( is_admin() && 'activated' === get_option( 'splms_activation_hook' ) ) {
				delete_option( 'splms_activation_hook' );
				flush_rewrite_rules();
			}
		}

		/**
		 * Activation hook.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public static function activation_hook() {
			// Load constants first.
			require_once SKILLPULSE_LMS_DIR_PATH . 'constants.php';

			// Load database class and create tables directly.
			require_once SKILLPULSE_LMS_DIR_PATH . 'includes/modules/core/class-database.php';
			$database = SkillPulse_LMS_Database::get_instance();
			$database->create_tables();

			add_option( 'splms_activation_hook', 'activated' );

			/**
			 * Fires after plugin activation tasks are completed.
			 *
			 * @since 1.0.0
			 */
			do_action( 'splms_activation_hook' );

			// Flush rewrite rules on activation.
			flush_rewrite_rules();
		}

		/**
		 * Deactivation hook.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public static function deactivation_hook() {
			// Flush rewrite rules on deactivation.
			flush_rewrite_rules();

			// Unschedule notification auto-delete cron.
			if ( class_exists( 'SkillPulse_LMS_In_App_Notifications' ) ) {
				$in_app_notifications = SkillPulse_LMS_In_App_Notifications::get_instance();
				if ( method_exists( $in_app_notifications, 'unschedule_auto_delete_cron' ) ) {
					$in_app_notifications->unschedule_auto_delete_cron();
				}
			}

			/**
			 * Fires after plugin deactivation tasks are completed.
			 *
			 * @since 1.0.0
			 */
			// Clear legacy license check cron.
			wp_clear_scheduled_hook( 'splms_daily_license_check' );

			do_action( 'splms_deactivation_hook' );
		}
	}
}


if ( ! function_exists( 'splms_load' ) ) {
	/**
	 * Load the SkillPulse LMS plugin instance.
	 *
	 * @since 1.0.0
	 *
	 * @return SkillPulse_LMS The plugin instance.
	 */
	function splms_load() { // phpcs:ignore Universal.Files.SeparateFunctionsFromOO.Mixed -- WordPress pattern: main plugin file contains both class and function.
		return SkillPulse_LMS::get_instance();
	}
}

/**
 * Init the plugin and load the plugin instance.
 *
 * @since 1.0.0
 */
splms_load();

// Load WP-CLI commands.
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once plugin_dir_path( __FILE__ ) . 'bin/class-cli-commands.php';
}

// Load API Documentation WordPress integration.
if ( file_exists( plugin_dir_path( __FILE__ ) . 'api-docs/init.php' ) ) {
	require_once plugin_dir_path( __FILE__ ) . 'api-docs/init.php';
}
