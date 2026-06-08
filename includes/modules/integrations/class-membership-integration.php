<?php
/**
 * Abstract Membership Integration Class
 *
 * Base class for all membership plugin integrations.
 * All membership integrations must extend this class.
 *
 * @package SPLMS
 * @subpackage Integrations
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Abstract Membership Integration Class
 *
 * @since 1.0.0
 */
abstract class SPLMS_Membership_Integration {

	/**
	 * Integration ID (unique identifier).
	 *
	 * @var string
	 */
	protected $id = '';

	/**
	 * Integration name (display name).
	 *
	 * @var string
	 */
	protected $name = '';

	/**
	 * Integration instance (singleton per integration).
	 *
	 * @var array<string, static>
	 */
	private static $instances = array();

	/**
	 * Registered integrations.
	 *
	 * @var array<string, SPLMS_Membership_Integration>
	 */
	private static $registered = array();

	/**
	 * Get singleton instance of this integration.
	 *
	 * @return static
	 */
	public static function get_instance() {
		$class = get_called_class();

		if ( ! isset( self::$instances[ $class ] ) ) {
			self::$instances[ $class ] = new $class();
		}

		return self::$instances[ $class ];
	}

	/**
	 * Register an integration.
	 *
	 * @param string|SPLMS_Membership_Integration $integration Integration class name or instance.
	 * @return bool True if registered successfully.
	 */
	public static function register( $integration ) {
		// Handle class name string.
		if ( is_string( $integration ) ) {
			if ( ! class_exists( $integration ) ) {
				return false;
			}
			$integration = $integration::get_instance();
		}

		// Validate integration instance.
		if ( ! $integration instanceof self ) {
			return false;
		}

		$integration_id = $integration->get_id();

		// Validate integration ID.
		if ( empty( $integration_id ) ) {
			return false;
		}

		// Check if already registered.
		if ( isset( self::$registered[ $integration_id ] ) ) {
			return false;
		}

		// Register integration.
		self::$registered[ $integration_id ] = $integration;

		/**
		 * Action fired when a membership integration is registered.
		 *
		 * @param SPLMS_Membership_Integration $integration Integration instance.
		 * @param string                                  $id          Integration ID.
		 */
		do_action( 'splms_membership_integration_registered', $integration, $integration_id );

		return true;
	}

	/**
	 * Get registered integration by ID.
	 *
	 * @param string $integration_id Integration ID.
	 * @return SPLMS_Membership_Integration|null
	 */
	public static function get( $integration_id ) {
		return isset( self::$registered[ $integration_id ] )
			? self::$registered[ $integration_id ]
			: null;
	}

	/**
	 * Get all registered integrations.
	 *
	 * @return array<string, SPLMS_Membership_Integration>
	 */
	public static function get_all() {
		return self::$registered;
	}

	/**
	 * Get all active integrations (plugin is active).
	 *
	 * @return array<string, SPLMS_Membership_Integration>
	 */
	public static function get_active() {
		$active = array();

		foreach ( self::$registered as $id => $integration ) {
			if ( $integration->is_plugin_active() ) {
				$active[ $id ] = $integration;
			}
		}

		return $active;
	}

	/**
	 * Check if any integration is active.
	 *
	 * @return bool
	 */
	public static function has_active() {
		$active = self::get_active();
		return ! empty( $active );
	}

	/**
	 * Get all membership options from active integrations.
	 *
	 * @return array Array of membership options with 'id', 'name', and 'integration_id' keys.
	 */
	public static function get_all_membership_options() {
		$options             = array();
		$active_integrations = self::get_active();

		foreach ( $active_integrations as $integration_id => $integration ) {
			try {
				$memberships = $integration->get_available_memberships();

				if ( empty( $memberships ) || ! is_array( $memberships ) ) {
					continue;
				}

				foreach ( $memberships as $membership ) {
					// Ensure proper format.
					if ( ! isset( $membership['id'] ) || ! isset( $membership['name'] ) ) {
						continue;
					}

					$options[] = array(
						'id'               => $integration_id . '_' . $membership['id'],
						'name'             => $membership['name'],
						'integration_id'   => $integration_id,
						'integration_name' => $integration->get_name(),
					);
				}
			} catch ( Exception $e ) {
				if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
					// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Debug logging is intentional.
					error_log(
						sprintf(
							'SkillPulse LMS: Error getting memberships from %s - %s',
							$integration_id,
							$e->getMessage()
						)
					);
				}
				continue;
			}
		}

		/**
		 * Filter all membership options.
		 *
		 * @param array $options Array of membership options.
		 * @return array
		 */
		return apply_filters( 'splms_all_membership_options', $options );
	}

	/**
	 * Check if user has access to course via any active integration (static method).
	 *
	 * @param int $user_id   User ID.
	 * @param int $course_id Course ID.
	 * @return bool True if user has access.
	 */
	public static function check_user_course_access( $user_id, $course_id ) {
		// Get course settings.
		$course_settings = splms_get_course_settings( $course_id );
		$access_settings = isset( $course_settings['course_access_settings'] ) ? $course_settings['course_access_settings'] : array();

		// Get access type - membership only applies to public_free and public_paid.
		$course_access_type          = isset( $access_settings['course_access_type'] ) ? $access_settings['course_access_type'] : 'public_free';
		$membership_applicable_types = array( 'public_free', 'public_paid' );

		// If access type doesn't support membership requirements, return true (no restriction).
		if ( ! in_array( $course_access_type, $membership_applicable_types, true ) ) {
			return true;
		}

		$required_memberships = isset( $access_settings['required_memberships'] )
			? $access_settings['required_memberships']
			: array();

		// No membership required.
		if ( empty( $required_memberships ) ) {
			return true;
		}

		// Get active integrations.
		$active_integrations = self::get_active();

		// No active integrations but memberships required.
		if ( empty( $active_integrations ) ) {
			return false;
		}

		// Check each integration.
		foreach ( $active_integrations as $integration ) {
			try {
				if ( $integration->check_course_access( $user_id, $course_id ) ) {
					return true;
				}
			} catch ( Exception $e ) {
				if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
					// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Debug logging is intentional.
					error_log(
						sprintf(
							'SkillPulse LMS: Error checking course access via %s - %s',
							$integration->get_id(),
							$e->getMessage()
						)
					);
				}
				continue;
			}
		}

		return false;
	}

	/**
	 * Initialize and register all integrations.
	 * Should be called on 'init' hook.
	 *
	 * @return void
	 */
	public static function init_registrations() {
		/**
		 * Filter to register membership integrations.
		 *
		 * @param array $integrations Array of integration class names or instances.
		 * @return array
		 */
		$integrations = apply_filters( 'splms_register_membership_integrations', array() );

		foreach ( $integrations as $integration ) {
			self::register( $integration );
		}
	}

	/**
	 * Constructor.
	 */
	protected function __construct() {
		$this->init();
	}

	/**
	 * Initialize the integration.
	 * Override in child classes if needed.
	 *
	 * @return void
	 */
	protected function init() {
		// Override in child classes.
	}

	/**
	 * Get integration ID.
	 *
	 * @return string
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Get integration name.
	 *
	 * @return string
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * Check if the membership plugin is active.
	 * Must be implemented by child classes.
	 * Typically checks for plugin class existence (e.g., class_exists('MeprAppCtrl')).
	 *
	 * @return bool True if plugin is active.
	 */
	abstract public function is_plugin_active();

	/**
	 * Check if user has specific membership.
	 * Must be implemented by child classes.
	 *
	 * @param int $user_id       User ID.
	 * @param int $membership_id Membership ID.
	 * @return bool True if user has membership.
	 */
	abstract public function user_has_membership( $user_id, $membership_id );

	/**
	 * Get all active memberships for user.
	 * Must be implemented by child classes.
	 *
	 * @param int $user_id User ID.
	 * @return array Array of membership IDs.
	 */
	abstract public function get_user_memberships( $user_id );

	/**
	 * Get all available memberships.
	 * Must be implemented by child classes.
	 *
	 * @return array Array of membership options with 'id' and 'name' keys.
	 */
	abstract public function get_available_memberships();

	/**
	 * Check if user has access to course via this integration.
	 * Can be overridden in child classes for custom access logic.
	 *
	 * @param int $user_id   User ID.
	 * @param int $course_id Course ID.
	 * @return bool True if user has access.
	 */
	public function check_course_access( $user_id, $course_id ) {
		if ( ! $this->is_plugin_active() ) {
			return false;
		}

		// Get course membership requirements.
		$course_settings = splms_get_course_settings( $course_id );
		$access_settings = isset( $course_settings['course_access_settings'] ) ? $course_settings['course_access_settings'] : array();

		// Get access type - membership only applies to public_free and public_paid.
		$course_access_type          = isset( $access_settings['course_access_type'] ) ? $access_settings['course_access_type'] : 'public_free';
		$membership_applicable_types = array( 'public_free', 'public_paid' );

		// If access type doesn't support membership requirements, return true (no restriction).
		if ( ! in_array( $course_access_type, $membership_applicable_types, true ) ) {
			return true;
		}

		$required_memberships = isset( $access_settings['required_memberships'] )
			? $access_settings['required_memberships']
			: array();

		if ( empty( $required_memberships ) ) {
			return true; // No membership required.
		}

		// Parse membership IDs (format: 'integration_id_membership_id').
		foreach ( $required_memberships as $membership_value ) {
			if ( strpos( $membership_value, $this->id . '_' ) === 0 ) {
				$membership_id = str_replace( $this->id . '_', '', $membership_value );
				if ( $this->user_has_membership( $user_id, $membership_id ) ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Get membership expiration date for user (optional, for future use).
	 * Override in child classes if membership plugin supports expiration.
	 *
	 * @param int $user_id       User ID.
	 * @param int $membership_id Membership ID.
	 * @return int|null Timestamp or null if not supported/expired.
	 */
	public function get_membership_expiration( $user_id, $membership_id ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- Parameters required by interface signature.
		// Override in child classes if needed.
		return null;
	}


	/**
	 * Get membership purchase URL (optional method).
	 * Override in child classes if membership plugin supports purchase URLs.
	 *
	 * @param int|string $membership_id Membership ID (internal ID, not prefixed).
	 * @return string|false Purchase URL or false if not available.
	 */
	public function get_membership_purchase_url( $membership_id ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found -- Parameter required by interface signature.
		// Override in child classes if needed.
		return false;
	}
}
