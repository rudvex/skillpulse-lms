<?php
/**
 * Plugin Updater for SkillPulse LMS
 *
 * Handles plugin update checks and delivery for licensed users.
 * Integrates with Brand Manager API for version management.
 *
 * @since   1.0.0
 * @package SkillPulse_LMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin Updater Class
 *
 * Manages plugin updates through WordPress update system.
 *
 * @since 1.0.0
 */
class SkillPulse_LMS_Plugin_Updater {

	/**
	 * Class instance.
	 *
	 * @var null $instance
	 */
	private static $instance = null;

	/**
	 * License configuration instance.
	 *
	 * @var SkillPulse_LMS_License_Config
	 */
	private $config;

	/**
	 * License manager instance.
	 *
	 * @var SkillPulse_LMS_License_Manager
	 */
	private $license_manager;

	/**
	 * Plugin basename.
	 *
	 * @var string
	 */
	private $plugin_basename;

	/**
	 * Plugin slug.
	 *
	 * @var string
	 */
	private $plugin_slug;

	/**
	 * Get the instance of this class.
	 *
	 * @since 1.0.0
	 *
	 * @return SkillPulse_LMS_Plugin_Updater
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	private function __construct() {
		$this->config          = SkillPulse_LMS_License_Config::get_instance();
		$this->license_manager = SkillPulse_LMS_License_Manager::get_instance();
		$this->plugin_basename = defined( 'SKILLPULSE_LMS_PLUGIN_BASENAME' ) ? SKILLPULSE_LMS_PLUGIN_BASENAME : 'skillpulse-lms/skillpulse-lms.php';
		$this->plugin_slug     = dirname( $this->plugin_basename );

		$this->setup_hooks();
	}

	/**
	 * Setup hooks.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	private function setup_hooks() {
		// Hook into WordPress update system.
		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_for_updates' ) );
		add_filter( 'plugins_api', array( $this, 'plugin_information' ), 20, 3 );

		// Add plugin row meta (view details link).
		add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );

		// Clear transients when license status changes.
		add_action( 'splms_license_activated', array( $this, 'clear_update_transient' ) );
		add_action( 'splms_license_deactivated', array( $this, 'clear_update_transient' ) );
		add_action( 'splms_license_status_changed', array( $this, 'clear_update_transient' ) );

		// AJAX handler for manual update check.
		add_action( 'wp_ajax_splms_check_for_updates', array( $this, 'ajax_check_for_updates' ) );
	}

	/**
	 * Check for plugin updates.
	 *
	 * Hooks into WordPress's update system to provide custom update information.
	 *
	 * @param object $transient Update transient object.
	 *
	 * @since 1.0.0
	 *
	 * @return object Modified transient object.
	 */
	public function check_for_updates( $transient ) {
		// If no checked data, return early.
		if ( empty( $transient->checked ) ) {
			return $transient;
		}

		// Get current plugin version.
		$current_version = isset( $transient->checked[ $this->plugin_basename ] ) ? $transient->checked[ $this->plugin_basename ] : SKILLPULSE_LMS_VERSION;

		// Try to get cached update info.
		$update_info = get_site_transient( 'splms_update_info' );

		// If no cached info or cache is expired, fetch from server.
		if ( empty( $update_info ) || false === $update_info['update_available'] ) {
			$update_info = $this->fetch_update_info();

			// Cache for 12 hours.
			if ( ! is_wp_error( $update_info ) ) {
				set_site_transient( 'splms_update_info', $update_info, 12 * HOUR_IN_SECONDS );
			}
		}

		// If we have update info and it's not an error.
		if ( ! is_wp_error( $update_info ) && is_array( $update_info ) ) {
			$latest_version = isset( $update_info['version'] ) ? $update_info['version'] : $current_version;
			$download_url   = isset( $update_info['download_url'] ) ? $update_info['download_url'] : '';

			// Compare versions.
			if ( version_compare( $latest_version, $current_version, '>' ) ) {
				// Update available.
				$update_data = array(
					'id'           => $this->plugin_slug,
					'slug'         => $this->plugin_slug,
					'plugin'       => $this->plugin_basename,
					'new_version'  => $latest_version,
					'url'          => 'https://skillpulselms.com/',
					'package'      => $download_url, // Empty if no valid license.
					'tested'       => isset( $update_info['tested'] ) ? $update_info['tested'] : get_bloginfo( 'version' ),
					'requires'     => isset( $update_info['requires'] ) ? $update_info['requires'] : '5.8',
					'requires_php' => isset( $update_info['requires_php'] ) ? $update_info['requires_php'] : '7.4',
				);

				// Add icons if provided.
				if ( isset( $update_info['icons'] ) ) {
					$update_data['icons'] = $update_info['icons'];
				}

				// Add banners if provided.
				if ( isset( $update_info['banners'] ) ) {
					$update_data['banners'] = $update_info['banners'];
				}

				$transient->response[ $this->plugin_basename ] = (object) $update_data;
			} elseif ( isset( $transient->response[ $this->plugin_basename ] ) ) {
				// No update available - remove from response if present.
				unset( $transient->response[ $this->plugin_basename ] );
			}
		}

		return $transient;
	}

	/**
	 * Fetch update information from server.
	 *
	 * @since 1.0.0
	 *
	 * @return array|WP_Error Update information or error.
	 */
	private function fetch_update_info() {
		$license     = get_option( 'splms_license', array() );
		$license_key = isset( $license['license_key'] ) ? $license['license_key'] : '';

		// Build request.
		$api_url = $this->config->get_api_endpoint() . '/plugin/update-check';

		$request_data = array(
			'plugin_slug'     => $this->plugin_slug,
			'current_version' => SKILLPULSE_LMS_VERSION,
			'site_url'        => home_url(),
		);

		// Add license key if available.
		if ( ! empty( $license_key ) ) {
			$request_data['license_key'] = $license_key;
		}

		$headers = array(
			'Content-Type' => 'application/json',
		);

		// Add signature to headers.
		$headers = $this->config->add_signature_to_headers( $headers, $request_data );

		$response = wp_remote_post(
			$api_url,
			array(
				'timeout' => 15,
				'headers' => $headers,
				'body'    => wp_json_encode( $request_data ),
			)
		);

		if ( is_wp_error( $response ) ) {
			return new WP_Error( 'api_error', __( 'Failed to connect to update server.', 'skillpulse-lms' ) );
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		$body        = wp_remote_retrieve_body( $response );
		$result      = json_decode( $body, true );

		if ( 200 !== $status_code ) {
			$message = isset( $result['message'] ) ? $result['message'] : sprintf(
				/* translators: %d: HTTP status code */
				__( 'Update check failed with status code: %d', 'skillpulse-lms' ),
				$status_code
			);

			return new WP_Error( 'update_check_failed', $message );
		}

		if ( ! $result || ! isset( $result['success'] ) || ! $result['success'] ) {
			return new WP_Error( 'invalid_response', __( 'Invalid response from update server.', 'skillpulse-lms' ) );
		}

		// Return update data.
		return isset( $result['data'] ) ? $result['data'] : array();
	}

	/**
	 * Provide plugin information for WordPress.
	 *
	 * Used when viewing plugin details in the update screen.
	 *
	 * @param false|object|array $result The result object or array.
	 * @param string             $action The type of information being requested.
	 * @param object             $args   Plugin API arguments.
	 *
	 * @since 1.0.0
	 *
	 * @return false|object Plugin information or false.
	 */
	public function plugin_information( $result, $action, $args ) {
		// Only handle plugin_information requests.
		if ( 'plugin_information' !== $action ) {
			return $result;
		}

		// Only handle requests for our plugin.
		if ( ! isset( $args->slug ) || $args->slug !== $this->plugin_slug ) {
			return $result;
		}

		// Get plugin information from server.
		$plugin_info = $this->fetch_plugin_information();

		if ( is_wp_error( $plugin_info ) ) {
			return $result;
		}

		return (object) $plugin_info;
	}

	/**
	 * Fetch plugin information from server.
	 *
	 * @since 1.0.0
	 *
	 * @return array|WP_Error Plugin information or error.
	 */
	private function fetch_plugin_information() {
		// Try to get cached info.
		$plugin_info = get_transient( 'splms_plugin_info' );

		if ( false !== $plugin_info ) {
			return $plugin_info;
		}

		$license     = get_option( 'splms_license', array() );
		$license_key = isset( $license['license_key'] ) ? $license['license_key'] : '';

		// Build request.
		$api_url = $this->config->get_api_endpoint() . '/plugin/info';

		$request_data = array(
			'plugin_slug' => $this->plugin_slug,
			'site_url'    => home_url(),
		);

		// Add license key if available.
		if ( ! empty( $license_key ) ) {
			$request_data['license_key'] = $license_key;
		}

		$headers = array(
			'Content-Type' => 'application/json',
		);

		// Add signature to headers.
		$headers = $this->config->add_signature_to_headers( $headers, $request_data );

		$response = wp_remote_post(
			$api_url,
			array(
				'timeout' => 15,
				'headers' => $headers,
				'body'    => wp_json_encode( $request_data ),
			)
		);

		if ( is_wp_error( $response ) ) {
			return new WP_Error( 'api_error', __( 'Failed to connect to update server.', 'skillpulse-lms' ) );
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		$body        = wp_remote_retrieve_body( $response );
		$result      = json_decode( $body, true );

		if ( 200 !== $status_code || ! $result || ! isset( $result['success'] ) || ! $result['success'] ) {
			return new WP_Error( 'info_fetch_failed', __( 'Failed to fetch plugin information.', 'skillpulse-lms' ) );
		}

		$plugin_info = isset( $result['data'] ) ? $result['data'] : array();

		// Build plugin information array for WordPress.
		$info = array(
			'name'           => ! empty( $plugin_info['name'] ) ? $plugin_info['name'] : 'SkillPulse LMS',
			'slug'           => $this->plugin_slug,
			'version'        => ! empty( $plugin_info['version'] ) ? $plugin_info['version'] : SKILLPULSE_LMS_VERSION,
			'author'         => '<a href="https://skillpulselms.com">SkillPulse LMS Team</a>',
			'author_profile' => 'https://skillpulselms.com',
			'homepage'       => 'https://skillpulselms.com',
			'requires'       => ! empty( $plugin_info['requires'] ) ? $plugin_info['requires'] : '5.8',
			'requires_php'   => ! empty( $plugin_info['requires_php'] ) ? $plugin_info['requires_php'] : '7.4',
			'tested'         => ! empty( $plugin_info['tested'] ) ? $plugin_info['tested'] : get_bloginfo( 'version' ),
			'last_updated'   => ! empty( $plugin_info['last_updated'] ) ? $plugin_info['last_updated'] : '',
			'sections'       => array(
				'description' => ! empty( $plugin_info['description'] ) ? $plugin_info['description'] : __( 'A comprehensive learning management system for WordPress.', 'skillpulse-lms' ),
				'changelog'   => isset( $plugin_info['changelog'] ) ? $plugin_info['changelog'] : '',
			),
		);

		// Add download link if provided (only for licensed users).
		if ( isset( $plugin_info['download_url'] ) && ! empty( $plugin_info['download_url'] ) ) {
			$info['download_link'] = $plugin_info['download_url'];
		}

		// Add banners if provided.
		if ( isset( $plugin_info['banners'] ) ) {
			$info['banners'] = $plugin_info['banners'];
		}

		// Add icons if provided.
		if ( isset( $plugin_info['icons'] ) ) {
			$info['icons'] = $plugin_info['icons'];
		}

		// Cache for 24 hours.
		set_transient( 'splms_plugin_info', $info, 24 * HOUR_IN_SECONDS );

		return $info;
	}

	/**
	 * Add plugin row meta links.
	 *
	 * @param array  $links Plugin row meta links.
	 * @param string $file  Plugin file path.
	 *
	 * @since 1.0.0
	 *
	 * @return array Modified links.
	 */
	public function plugin_row_meta( $links, $file ) {
		if ( $file !== $this->plugin_basename ) {
			return $links;
		}

		// Add "Check for updates" link.
		$check_updates_link = sprintf(
			'<a href="#" id="splms-check-updates" data-nonce="%s">%s</a>',
			wp_create_nonce( 'splms_check_updates' ),
			__( 'Check for updates', 'skillpulse-lms' )
		);

		$links[] = $check_updates_link;

		// Add inline script for AJAX check.
		add_action( 'admin_footer', array( $this, 'add_check_updates_script' ) );

		return $links;
	}

	/**
	 * Add inline script for manual update check.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function add_check_updates_script() {
		?>
		<script type="text/javascript">
		jQuery(document).ready(function($) {
			$('#splms-check-updates').on('click', function(e) {
				e.preventDefault();
				var $link = $(this);
				var originalText = $link.text();

				$link.text('<?php echo esc_js( __( 'Checking...', 'skillpulse-lms' ) ); ?>');

				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'splms_check_for_updates',
						nonce: $link.data('nonce')
					},
					success: function(response) {
						if (response.success) {
							$link.text('<?php echo esc_js( __( 'Update check complete', 'skillpulse-lms' ) ); ?>');
							// Reload page to show updated info.
							setTimeout(function() {
								location.reload();
							}, 1000);
						} else {
							$link.text(originalText);
							alert(response.data.message || '<?php echo esc_js( __( 'Update check failed.', 'skillpulse-lms' ) ); ?>');
						}
					},
					error: function() {
						$link.text(originalText);
						alert('<?php echo esc_js( __( 'Update check failed.', 'skillpulse-lms' ) ); ?>');
					}
				});
			});
		});
		</script>
		<?php
	}

	/**
	 * AJAX handler for manual update check.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function ajax_check_for_updates() {
		check_ajax_referer( 'splms_check_updates', 'nonce' );

		if ( ! current_user_can( 'update_plugins' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'skillpulse-lms' ) ) );
		}

		// Clear cached update info.
		$this->clear_update_transient();

		// Force WordPress to check for updates.
		wp_clean_plugins_cache();
		delete_site_transient( 'update_plugins' );

		// Trigger update check.
		wp_update_plugins();

		wp_send_json_success( array( 'message' => __( 'Update check completed successfully.', 'skillpulse-lms' ) ) );
	}

	/**
	 * Clear update transient.
	 *
	 * Called when license status changes.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function clear_update_transient() {
		delete_site_transient( 'splms_update_info' );
		delete_transient( 'splms_plugin_info' );
		delete_site_transient( 'update_plugins' );
	}

	/**
	 * Manually check for updates.
	 *
	 * Public method to trigger update check programmatically.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function manual_update_check() {
		$this->clear_update_transient();
		wp_update_plugins();
	}
}