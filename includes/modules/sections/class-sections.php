<?php
/**
 * Sections module class.
 *
 * Handles course sections functionality.
 *
 * @package SkillPulse_LMS
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sections module class.
 *
 * @since 1.0.0
 */
class SPLMS_Sections {

	/**
	 * Class instance.
	 *
	 * @var null $instance
	 */
	private static $instance;

	/**
	 * Get the instance of this class.
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
	}

	/**
	 * Initiate the required classes.
	 *
	 * @since 1.0.0
	 */
	protected function load_classes() {
		// Load section access query class.
		SPLMS_Section_Access_Query::get_instance();
	}

	/**
	 * Action hooks
	 *
	 * @since 1.0.0
	 */
	protected function setup_actions() {
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
	 * Get section settings with defaults from configuration.
	 *
	 * This method loads section settings from post meta and merges them with
	 * default values from the configuration system, similar to lessons.
	 *
	 * @since 1.0.0
	 *
	 * @param int $section_id Section ID.
	 *
	 * @return array Section settings array.
	 */
	public function get_section_settings( $section_id ) {
		$all_meta = get_post_meta( $section_id );

		// Get configuration data and extract defaults.
		$config_data       = SPLMS_Config_Loader::get_config( 'sections', 'admin' );
		$defaults_settings = $this->extract_defaults_from_config( $config_data );
		$section_settings  = array();

		// Process defaults: handle grouped fields.
		foreach ( $defaults_settings as $key => $default_value ) {
			// Check if this is a group (like _splms_section_settings, _splms_section_pricing).
			if ( is_array( $default_value ) && false === isset( $field_definitions[ $key ] ) ) {
				// This is a group - process each field in the group.
				$group_name                      = $key;
				$section_settings[ $group_name ] = array();

				$meta_key   = $group_name;
				$meta_value = isset( $all_meta[ $meta_key ][0] ) ? maybe_unserialize( $all_meta[ $meta_key ][0] ) : null;

				// Ensure meta_value is an array if it's not null.
				if ( ! is_array( $meta_value ) && null !== $meta_value ) {
					$meta_value = null;
				}

				foreach ( $default_value as $field_id => $field_default ) {
					// Handle different data types properly.
					if ( is_array( $field_default ) ) {
						$section_settings[ $group_name ][ $field_id ] = isset( $meta_value[ $field_id ] ) ? $meta_value[ $field_id ] : $field_default;
					} else {
						$section_settings[ $group_name ][ $field_id ] = isset( $meta_value[ $field_id ] ) ? $meta_value[ $field_id ] : $field_default;
					}
				}
			} else {
				// Individual field.
				$meta_key                 = "_splms_{$key}";
				$section_settings[ $key ] = isset( $all_meta[ $meta_key ][0] ) ? maybe_unserialize( $all_meta[ $meta_key ][0] ) : $default_value;
			}
		}

		return $section_settings;
	}

	/**
	 * Update section settings.
	 *
	 * Updates section settings in the database, handling grouped fields properly.
	 *
	 * @since 1.0.0
	 *
	 * @param int   $section_id Section ID.
	 * @param array $settings   Settings to update.
	 *
	 * @return bool True on success, false on failure.
	 */
	public function update_section_settings( $section_id, $settings ) {
		// Get configuration to determine field groups.
		$config_data       = SPLMS_Config_Loader::get_config( 'sections', 'admin' );
		$defaults_settings = $this->extract_defaults_from_config( $config_data );

		foreach ( $settings as $key => $value ) {
			// Check if this is a grouped field.
			if ( isset( $defaults_settings[ $key ] ) && is_array( $defaults_settings[ $key ] ) ) {
				// Grouped field - save as single meta key.
				update_post_meta( $section_id, $key, $value );
			} else {
				// Individual field - save with _splms_ prefix.
				$meta_key = "_splms_{$key}";
				update_post_meta( $section_id, $meta_key, $value );
			}
		}

		return true;
	}

	/**
	 * Extract default values from configuration.
	 *
	 * Recursively processes configuration sections and fields to extract
	 * default values, handling grouped and individual fields.
	 *
	 * @since 1.0.0
	 *
	 * @param array $config_data Configuration data from Config Loader.
	 *
	 * @return array Array of default values.
	 */
	private function extract_defaults_from_config( $config_data ) {
		$defaults = array();

		if ( ! isset( $config_data['sections'] ) || ! is_array( $config_data['sections'] ) ) {
			return $defaults;
		}

		foreach ( $config_data['sections'] as $section ) {
			if ( ! isset( $section['fields'] ) || ! is_array( $section['fields'] ) ) {
				continue;
			}

			foreach ( $section['fields'] as $field ) {
				if ( ! isset( $field['id'] ) ) {
					continue;
				}

				$field_id = $field['id'];
				$default  = isset( $field['default'] ) ? $field['default'] : '';

				if ( isset( $field['group'] ) && ! empty( $field['group'] ) ) {
					// Grouped field.
					$group_name = $field['group'];
					if ( ! isset( $defaults[ $group_name ] ) ) {
						$defaults[ $group_name ] = array();
					}
					$defaults[ $group_name ][ $field_id ] = $default;
				} else {
					// Individual field.
					$defaults[ $field_id ] = $default;
				}
			}
		}

		return $defaults;
	}
}
