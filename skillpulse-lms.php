<?php
/**
 * Plugin Name: SkillPulse LMS
 * Description: Create and deliver online courses with ease. A powerful WordPress LMS solution for educators and course creators.
 * Version: 1.0.1
 * Author: SkillPulseLMS
 * Author URI: https://skillpulselms.com
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: skillpulse-lms
 *
 * @package SPLMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load constants.
require_once plugin_dir_path( __FILE__ ) . 'constants.php';

// Register activation hook.
register_activation_hook( __FILE__, array( 'SPLMS', 'activation_hook' ) );
// Register deactivation hook.
register_deactivation_hook( __FILE__, array( 'SPLMS', 'deactivation_hook' ) );

if ( ! defined( 'SPLMS_VERSION' ) ) {
	return;
}

if ( ! defined( 'SPLMS_PLUGIN_BASENAME' ) ) {
	define( 'SPLMS_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
}

if ( ! defined( 'SPLMS_FILE' ) ) {
	define( 'SPLMS_FILE', __FILE__ );
}

/**
 * Main class of SkillPulse LMS.
 *
 * @package SPLMS
 */
if ( ! class_exists( 'SPLMS' ) ) {
	/**
	 * Main SkillPulse LMS class.
	 *
	 * @since   1.0.0
	 * @package SPLMS
	 */
	class SPLMS {

		/**
		 * Class instance.
		 *
		 * @since 1.0.0
		 *
		 * @var SPLMS|null $instance
		 */
		private static $instance;

		/**
		 * Get the instance of this class.
		 *
		 * @since 1.0.0
		 *
		 * @return SPLMS The class instance.
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
			require_once SPLMS_DIR_PATH . 'includes/functions.php';
			require_once SPLMS_DIR_PATH . 'includes/class-main.php';
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
			SPLMS_Main::get_instance();
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
			require_once SPLMS_DIR_PATH . 'constants.php';

			// Load database class and create tables directly.
			require_once SPLMS_DIR_PATH . 'includes/modules/core/class-database.php';
			$database = SPLMS_Database::get_instance();
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
			if ( class_exists( 'SPLMS_In_App_Notifications' ) ) {
				$in_app_notifications = SPLMS_In_App_Notifications::get_instance();
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
	 * @return SPLMS The plugin instance.
	 */
	function splms_load() { // phpcs:ignore Universal.Files.SeparateFunctionsFromOO.Mixed -- WordPress pattern: main plugin file contains both class and function.
		return SPLMS::get_instance();
	}
}

/**
 * Init the plugin and load the plugin instance.
 *
 * @since 1.0.0
 */
splms_load();
