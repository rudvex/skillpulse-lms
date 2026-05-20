<?php
/**
 * Settings management class.
 *
 * Handles plugin settings, configuration, and validation.
 *
 * @since   1.0.0
 * @package SkillPulse_LMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings management class.
 *
 * @since 1.0.0
 */
class SPLMS_Settings {

	/**
	 * Class instance.
	 *
	 * @since 1.0.0
	 *
	 * @var SPLMS_Settings|null $instance
	 */
	private static $instance;

	/**
	 * Valid tab names.
	 *
	 * @since 1.0.0
	 *
	 * @var array $valid_tabs
	 */
	private $valid_tabs = array();

	/**
	 * Get the instance of this class.
	 *
	 * @since 1.0.0
	 *
	 * @return SPLMS_Settings The class instance.
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
			self::$instance->setup_globals();
			self::$instance->load_classes();
			self::$instance->setup_actions();
			self::$instance->setup_filters();
		}

		return self::$instance;
	}

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	private function __construct() {
	}

	/**
	 * Setup Globals.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	protected function setup_globals() {
		$this->valid_tabs = $this->get_valid_tabs_from_config();
	}

	/**
	 * Initiate the required classes.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	protected function load_classes() {
		// Load any required setting classes.
	}


	/**
	 * Action hooks.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	protected function setup_actions() {
		add_action( 'splms_settings_updated', array( $this, 'handle_settings_update' ), 10, 2 );
	}

	/**
	 * Define all filter hooks.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	protected function setup_filters() {
		add_filter( 'registration_errors', array( $this, 'validate_user_signup' ), 10, 1 );
		add_filter( 'wp_mail_from', array( $this, 'filter_mail_from' ) );
		add_filter( 'wp_mail_from_name', array( $this, 'filter_mail_from_name' ) );
	}

	/**
	 * Get valid tab names from configuration.
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of valid tab names.
	 */
	public function get_valid_tabs_from_config() {
		$config = SPLMS_Config_Loader::get_config( 'settings', 'admin' );
		$tabs   = array();

		if ( isset( $config['tabs'] ) && is_array( $config['tabs'] ) ) {
			foreach ( $config['tabs'] as $tab ) {
				if ( isset( $tab['id'] ) ) {
					$tabs[] = $tab['id'];
				}
			}
		}

		/**
		 * Filter to add special tabs that are managed separately (not in main settings config).
		 *
		 * @since 1.0.0
		 *
		 * @param array $tabs Array of tab IDs. Filters can add to this array.
		 */
		$tabs = apply_filters( 'splms_special_settings_tabs', $tabs );

		return $tabs;
	}

	/**
	 * Get valid tab names.
	 *
	 * @since 1.0.0
	 *
	 * @return array Valid tab names.
	 */
	public function get_valid_tabs() {
		// Always get fresh tabs with filter applied (in case filters are registered after initialization).
		return $this->get_valid_tabs_from_config();
	}

	/**
	 * Check if tab is valid.
	 *
	 * @param string $tab Tab name.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if tab is valid.
	 */
	public function is_valid_tab( $tab ) {
		return in_array( $tab, $this->get_valid_tabs(), true );
	}


	/**
	 * Get all settings with WordPress core integration.
	 *
	 * @since 1.0.0
	 *
	 * @return array All settings.
	 */
	public function get_all_settings() {
		$settings = get_option( 'splms_settings', array() );

		// Apply WordPress core settings sync.
		$settings = $this->merge_with_wp_core_settings( $settings );

		return apply_filters( 'splms_settings', $settings );
	}

	/**
	 * Merge plugin settings with WordPress core settings.
	 *
	 * @param array $settings Plugin settings.
	 *
	 * @since 1.0.0
	 *
	 * @return array Merged settings.
	 */
	public function merge_with_wp_core_settings( $settings ) {
		// Add WordPress core settings to general_settings tab.
		if ( ! isset( $settings['general']['general_settings'] ) ) {
			$settings['general']['general_settings'] = array();
		}

		// User signup settings from WordPress.
		$settings['general']['general_settings']['user_signup_enabled'] = get_option( 'users_can_register', false ) ? true : false;
		$settings['general']['general_settings']['default_user_role']   = get_option( 'default_role', 'subscriber' );

		return $settings;
	}


	/**
	 * Sanitize settings data based on field types and validation rules.
	 *
	 * @param array  $data Settings data to sanitize.
	 * @param string $tab  The settings tab being updated.
	 *
	 * @since 1.0.0
	 *
	 * @return array|WP_Error Sanitized data or error object.
	 */
	public function sanitize_settings_data( $data, $tab ) {
		$sanitized = $data;

		/**
		 * Filter sanitized settings data.
		 *
		 * @param array  $sanitized The sanitized settings data.
		 * @param array  $data      The original settings data.
		 * @param string $tab       The settings tab being updated.
		 */
		return apply_filters( 'splms_sanitize_settings_data', $sanitized, $data, $tab );
	}

	/**
	 * Handle settings update hook.
	 *
	 * @param string $tab          Updated tab.
	 * @param array  $data         Settings data.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function handle_settings_update( $tab, $data ) {
		switch ( $tab ) {
			case 'general':
				$this->handle_user_settings_update( $data );
				break;

			case 'notifications':
				$this->handle_notification_settings_update( $data );
				break;
		}

		// Clear any relevant caches.
		wp_cache_delete( 'splms_settings', 'options' );
	}

	/**
	 * Handle user settings updates.
	 *
	 * @param array $data User settings data.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	private function handle_user_settings_update( $data ) {
		// Handle user signup changes.
		if ( isset( $data['general_settings']['user_signup_enabled'] ) ) {
			$current_signup = get_option( 'users_can_register', false );
			$new_signup     = (bool) $data['general_settings']['user_signup_enabled'];

			if ( $new_signup !== $current_signup ) {
				update_option( 'users_can_register', $new_signup ? 1 : 0 );
			}
		}

		// Handle default role changes.
		if ( isset( $data['general_settings']['default_user_role'] ) ) {
			if ( ! function_exists( 'get_editable_roles' ) ) {
				require_once ABSPATH . 'wp-admin/includes/user.php';
			}
			$roles = get_editable_roles();
			if ( array_key_exists( $data['general_settings']['default_user_role'], $roles ) ) {
				update_option( 'default_role', $data['general_settings']['default_user_role'] );
			}
		}
	}

	/**
	 * Handle notification settings updates.
	 *
	 * @param array $data Notification settings data.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	private function handle_notification_settings_update( $data ) {
		// Update WordPress email settings.
		if ( isset( $data['from_name'] ) && ! empty( $data['from_name'] ) ) {
			update_option( 'blogname', sanitize_text_field( $data['from_name'] ) );
		}

		if ( isset( $data['from_email'] ) && is_email( $data['from_email'] ) ) {
			update_option( 'admin_email', sanitize_email( $data['from_email'] ) );
		}
	}

	/**
	 * Validate user signup.
	 *
	 * @param WP_Error $errors               Signup errors.
	 *
	 * @since 1.0.0
	 *
	 * @return WP_Error Errors object.
	 */
	public function validate_user_signup( $errors ) {
		// Check if signup is enabled.
		if ( true !== splms_get_setting( 'user_signup_enabled', false ) ) {
			$errors->add( 'signup_disabled', __( 'User signup is currently disabled.', 'skillpulse-lms' ) );
		}

		return $errors;
	}

	/**
	 * Filter mail from address.
	 *
	 * @param string $from_email From email.
	 *
	 * @since 1.0.0
	 *
	 * @return string Filtered from email.
	 */
	public function filter_mail_from( $from_email ) {
		$db_from_email = splms_get_setting( 'from_email', get_option( 'admin_email' ) );
		if ( isset( $db_from_email ) &&
			is_email( $db_from_email ) ) {
			return $db_from_email;
		}

		return $from_email;
	}

	/**
	 * Filter mail from name.
	 *
	 * @param string $from_name From name.
	 *
	 * @since 1.0.0
	 *
	 * @return string Filtered from name.
	 */
	public function filter_mail_from_name( $from_name ) {
		$settings = $this->get_all_settings();

		if ( ! empty( $settings['notifications']['from_name'] ) ) {
			return $settings['notifications']['from_name'];
		}

		return $from_name;
	}

	/**
	 * Get a specific setting value.
	 *
	 * @param string $key         Settings key (must be in tab.key format).
	 * @param mixed  $default_val Default value.
	 *
	 * @since 1.0.0
	 *
	 * @return mixed Setting value.
	 */
	public function get_setting( $key, $default_val = null ) {
		// Handle tab.key format.
		if ( strpos( $key, '.' ) !== false ) {
			list( $tab, $setting_key ) = explode( '.', $key, 2 );

			return $this->get_tab_setting( $tab, $setting_key, $default_val );
		}

		return $default_val;
	}

	/**
	 * Get a specific setting value from a specific tab.
	 *
	 * @param string $tab     Settings tab.
	 * @param string $key     Settings key.
	 * @param mixed  $default_val Default value.
	 *
	 * @since 1.0.0
	 *
	 * @return mixed Setting value.
	 */
	public function get_tab_setting( $tab, $key = '', $default_val = null ) {
		$settings = $this->get_all_settings();

		if ( isset( $settings[ $tab ][ $key ] ) ) {
			return $settings[ $tab ][ $key ];
		}

		if ( empty( $key ) ) {
			return isset( $settings[ $tab ] ) ? $settings[ $tab ] : $default_val;
		}

		return $default_val;
	}

	/**
	 * Update settings for a specific tab.
	 *
	 * This method is optimized for the unified config/settings approach where
	 * React sends complete tab data, eliminating the need for defaults merging.
	 *
	 * @param string $tab  The settings tab to update.
	 * @param array  $data Complete settings data for the tab.
	 *
	 * @since 1.0.0
	 *
	 * @return array|WP_Error Updated settings array on success, WP_Error on failure.
	 */
	public function update_tab_settings( $tab, $data ) {

		// Quick validation - tab exists.
		if ( ! $this->is_valid_tab( $tab ) ) {
			return new WP_Error(
				'splms_rest_invalid_tab',
				/* translators: %1$s: Tab name, %2$s: Valid tabs list. */
				sprintf( __( 'Invalid tab: %1$s. Valid: %2$s', 'skillpulse-lms' ), $tab, implode( ', ', $this->get_valid_tabs() ) ),
				array( 'status' => 400 )
			);
		}

		// Quick validation - data format.
		if ( ! is_array( $data ) || empty( $data ) ) {
			return new WP_Error(
				'splms_rest_invalid_data',
				__( 'Invalid settings data format. Expected non-empty array.', 'skillpulse-lms' ),
				array( 'status' => 400 )
			);
		}

		// Sanitize the complete data (React sends all fields).
		$sanitized_data = $this->sanitize_settings_data( $data, $tab );
		if ( is_wp_error( $sanitized_data ) ) {
			return $sanitized_data;
		}

		// Get current saved settings directly from database (no need for config loading/merging).
		$settings         = get_option( 'splms_settings', array() );
		$settings[ $tab ] = $sanitized_data;

		// Update database.
		$this->update_all_settings( $settings );

		// Clear config cache to ensure fresh data on next request.
		if ( class_exists( 'SPLMS_Config_Loader' ) ) {
			SPLMS_Config_Loader::clear_cache( 'settings' );
		}

		/**
		 * Fires after settings are updated.
		 *
		 * @param string $tab            The settings tab that was updated.
		 * @param array  $sanitized_data The sanitized settings data.
		 * @param array  $settings       The complete settings array.
		 */
		do_action( 'splms_settings_updated', $tab, $sanitized_data, $settings );

		// Apply WordPress core settings sync before returning.
		return $this->merge_with_wp_core_settings( $settings );
	}

	/**
	 * Update all settings.
	 *
	 * @param array $settings Settings array.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True on success.
	 */
	public function update_all_settings( $settings ) {
		return update_option( 'splms_settings', $settings );
	}
}
