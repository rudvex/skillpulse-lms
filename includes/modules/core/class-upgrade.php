<?php
/**
 * Upgrade Class
 *
 * Handle any installation upgrade or install tasks.
 *
 * @package SkillPulse_LMS
 * @subpackage Core
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Upgrade Class
 *
 * @since 1.0.0
 */
class SPLMS_Upgrade {

	/**
	 * Initialise data before plugin is fully loaded
	 *
	 * @since 1.0.0
	 */
	public static function init() {
		/**
		 * Initialize the plugin data
		 */
		$old_version = get_option( 'splms_version', false );
		if ( version_compare( $old_version, SPLMS_VERSION, 'lt' ) ) {
			add_action( 'admin_init', array( __CLASS__, 'flush_rewrite' ) );
			// Update version.
			update_option( 'splms_version', SPLMS_VERSION );

			/**
			 * Triggered when SkillPulse LMS version is updated
			 *
			 * @param string $plugin_version New plugin version
			 * @param string $old_version    Old plugin version.
			 */
			do_action( 'splms_update_version', SPLMS_VERSION, $old_version );
		}
	}

	/**
	 * Flush rewrite rules.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function flush_rewrite() {
		// Flush rewrite rules.
		flush_rewrite_rules();
	}
}
