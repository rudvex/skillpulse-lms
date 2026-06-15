<?php
/**
 * Setup Wizard Controller
 *
 * Main wizard controller that handles wizard initialization, routing,
 * and integration with existing SkillPulse LMS systems.
 *
 * @package SPLMS
 * @since 1.0.0
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class SPLMS_Setup_Wizard
 *
 * Manages the setup wizard flow and integrates with the license system.
 */
class SPLMS_Setup_Wizard {

	/**
	 * Single instance of the class.
	 *
	 * @var SPLMS_Setup_Wizard
	 */
	private static $instance;

	/**
	 * Wizard steps definition.
	 *
	 * @var array
	 */
	private $steps;

	/**
	 * Current step identifier.
	 *
	 * @var string
	 */
	private $current_step;

	/**
	 * Wizard data storage.
	 *
	 * @var array
	 */
	private $wizard_data;

	/**
	 * Get single instance of the class.
	 *
	 * @return SPLMS_Setup_Wizard
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
			self::$instance->setup_actions();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		$this->steps        = $this->define_steps();
		$this->wizard_data  = get_option( 'splms_wizard_data', array() );
		$this->current_step = $this->get_current_step();
	}

	/**
	 * Setup WordPress action hooks.
	 */
	private function setup_actions() {
		add_action( 'activated_plugin', array( $this, 'activation_redirect' ) );
		add_action( 'admin_init', array( $this, 'maybe_redirect_to_wizard' ) );
		// Menu registration handled by admin menus class for consistency.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_wizard_assets' ) );
	}

	/**
	 * Define wizard steps.
	 *
	 * @return array Array of step configurations.
	 */
	private function define_steps() {
		return array(
			'welcome'     => array(
				'title'       => __( 'Welcome', 'skillpulse-lms' ),
				'description' => __( 'Welcome to SkillPulse LMS', 'skillpulse-lms' ),
				'required'    => true,
			),
			'license'     => array(
				'title'       => __( 'License Setup', 'skillpulse-lms' ),
				'description' => __( 'Activate your license', 'skillpulse-lms' ),
				'required'    => false,
			),
			'basic-setup' => array(
				'title'       => __( 'Basic Setup', 'skillpulse-lms' ),
				'description' => __( 'Configure essential settings', 'skillpulse-lms' ),
				'required'    => true,
			),
			'finish'      => array(
				'title'       => __( 'Complete', 'skillpulse-lms' ),
				'description' => __( 'Setup complete', 'skillpulse-lms' ),
				'required'    => true,
			),
		);
	}

	/**
	 * Handle plugin activation redirect.
	 *
	 * @param string $plugin Plugin file path.
	 */
	public function activation_redirect( $plugin ) {
		if ( plugin_basename( SPLMS_FILE ) === $plugin ) {
			// Only redirect if this is a single plugin activation (not bulk).
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only presence check on standard WP activation parameter.
			if ( ! isset( $_GET['activate-multi'] ) ) {
				set_transient( 'splms_wizard_redirect', true, 30 );
			}
		}
	}

	/**
	 * Maybe redirect to wizard after activation.
	 */
	public function maybe_redirect_to_wizard() {
		// Check for redirect transient.
		if ( get_transient( 'splms_wizard_redirect' ) ) {
			delete_transient( 'splms_wizard_redirect' );

			// Only redirect if wizard not completed and user can manage options.
			if ( ! $this->is_wizard_completed() && current_user_can( 'manage_options' ) ) {
				wp_safe_redirect( admin_url( 'admin.php?page=splms-setup-wizard' ) );
				exit;
			}
		}
	}


	/**
	 * Render wizard page.
	 */
	public function render_wizard_page() {
		// Verify user capabilities.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'skillpulse-lms' ) );
		}

		// Check if wizard is already completed.
		if ( $this->is_wizard_completed() ) {
			wp_safe_redirect( admin_url( 'admin.php?page=skillpulse-lms' ) );
			exit;
		}

		// Output wizard page HTML.
		?>
		<div class="wrap">
			<div id="splms-setup-wizard-root"></div>
		</div>
		<?php
	}

	/**
	 * Enqueue wizard assets.
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue_wizard_assets( $hook ) {

		// Only load on wizard page.
		if ( 'skillpulse-lms_page_splms-setup-wizard' !== $hook ) {
			return;
		}

		// Enqueue WordPress dependencies.
		wp_enqueue_script( 'wp-element' );
		wp_enqueue_script( 'wp-components' );
		wp_enqueue_script( 'wp-data' );
		wp_enqueue_script( 'wp-i18n' );
		wp_enqueue_script( 'wp-api-fetch' );
		wp_enqueue_script( 'wp-util' );

		// Enqueue wizard React app.
		$wizard_asset_file = SPLMS_DIR_PATH . 'assets/js/wizard.asset.php';
		if ( file_exists( $wizard_asset_file ) ) {
			$wizard_asset = include $wizard_asset_file;
			wp_enqueue_script(
				'splms-wizard',
				SPLMS_URL_PATH . 'assets/js/wizard.js',
				$wizard_asset['dependencies'],
				$wizard_asset['version'],
				true
			);
		}

		// Enqueue wizard styles.
		wp_enqueue_style(
			'splms-wizard',
			SPLMS_URL_PATH . 'assets/css/wizard.css',
			array(),
			SPLMS_VERSION
		);

		// Localize wizard data.
		wp_localize_script(
			'splms-wizard',
			'splmsWizardData',
			array(
				'apiUrl'        => rest_url( 'skillpulse-lms/v1/' ),
				'nonce'         => wp_create_nonce( 'splms_wizard_nonce' ),
				'currentStep'   => $this->current_step,
				'steps'         => $this->steps,
				'adminEmail'    => get_option( 'admin_email' ),
				'siteUrl'       => home_url(),
				'siteName'      => get_bloginfo( 'name' ),
				'timezone'      => get_option( 'timezone_string' ) ? get_option( 'timezone_string' ) : 'UTC',
				'timezones'     => $this->get_wordpress_timezones(),
				'mainUrl'       => admin_url( 'admin.php?page=skillpulse-lms' ),
				'ajaxUrl'       => admin_url( 'admin-ajax.php' ),
				'licenseNonce'  => wp_create_nonce( 'splms_license_nonce' ),
				'licenseInfo'   => $this->get_license_info(),
				'licenseStatus' => $this->get_license_status(),
			)
		);
	}

	/**
	 * Check if wizard is completed.
	 *
	 * @return bool True if wizard completed.
	 */
	public function is_wizard_completed() {
		return get_option( 'splms_wizard_completed', false );
	}

	/**
	 * Get current wizard step.
	 *
	 * @return string Current step identifier.
	 */
	private function get_current_step() {
		$wizard_data = get_option( 'splms_wizard_data', array() );
		return isset( $wizard_data['current_step'] ) ? $wizard_data['current_step'] : 'welcome';
	}

	/**
	 * Get license status.
	 *
	 * @return string License status.
	 */
	private function get_license_status() {
		$license_info = $this->get_license_info();
		return isset( $license_info['status'] ) ? $license_info['status'] : 'inactive';
	}

	/**
	 * Get full license info for the wizard.
	 *
	 * @return array License info array.
	 */
	private function get_license_info() {
		if ( class_exists( 'SPLMS_License_Manager' ) ) {
			return SPLMS_License_Manager::get_instance()->get_license_info();
		}

		return array(
			'license_key'      => '',
			'status'           => 'inactive',
			'domain_activated' => false,
		);
	}

	/**
	 * Save wizard step data.
	 *
	 * @param string $step Step identifier.
	 * @param array  $data Step data.
	 * @return array Result array.
	 */
	public function save_step_data( $step, $data ) {
		// Get existing wizard data.
		$wizard_data = get_option( 'splms_wizard_data', array() );

		// Initialize if empty.
		if ( empty( $wizard_data ) ) {
			$wizard_data = array(
				'version'    => SPLMS_VERSION,
				'started_at' => time(),
				'steps_data' => array(),
			);
		}

		// Save step data.
		$wizard_data['steps_data'][ $step ] = $data;
		$wizard_data['current_step']        = $step;
		$wizard_data['updated_at']          = time();

		// Update option.
		update_option( 'splms_wizard_data', $wizard_data );

		return array(
			'success' => true,
			'message' => __( 'Step data saved successfully.', 'skillpulse-lms' ),
		);
	}

	/**
	 * Complete wizard.
	 *
	 * @return array Result array.
	 */
	public function complete_wizard() {
		// Mark wizard as completed.
		update_option( 'splms_wizard_completed', true );

		// Update wizard data.
		$wizard_data                 = get_option( 'splms_wizard_data', array() );
		$wizard_data['completed']    = true;
		$wizard_data['completed_at'] = time();
		update_option( 'splms_wizard_data', $wizard_data );

		return array(
			'success'      => true,
			'message'      => __( 'Wizard completed successfully.', 'skillpulse-lms' ),
			'redirect_url' => admin_url( 'admin.php?page=skillpulse-lms' ),
		);
	}

	/**
	 * Get WordPress timezone options.
	 *
	 * @return array Array of timezone options.
	 */
	private function get_wordpress_timezones() {
		$timezones = array();

		// Get timezone identifiers from WordPress.
		$wp_timezones = wp_timezone_choice( '' );

		if ( ! empty( $wp_timezones ) ) {
			// Parse the WordPress timezone dropdown HTML to extract options.
			preg_match_all( '/<option value="([^"]*)"[^>]*>([^<]*)<\/option>/', $wp_timezones, $matches );

			if ( ! empty( $matches[1] ) && ! empty( $matches[2] ) ) {
				foreach ( $matches[1] as $index => $value ) {
					$label       = isset( $matches[2][ $index ] ) ? $matches[2][ $index ] : $value;
					$timezones[] = array(
						'value' => $value,
						'label' => html_entity_decode( $label, ENT_QUOTES | ENT_HTML5, 'UTF-8' ),
					);
				}
			}
		}

		// If no timezones found, provide fallback.
		if ( empty( $timezones ) ) {
			$timezones = array(
				array(
					'value' => 'UTC',
					'label' => __( 'UTC', 'skillpulse-lms' ),
				),
				array(
					'value' => 'America/New_York',
					'label' => __( 'Eastern Time (US)', 'skillpulse-lms' ),
				),
				array(
					'value' => 'America/Chicago',
					'label' => __( 'Central Time (US)', 'skillpulse-lms' ),
				),
				array(
					'value' => 'America/Denver',
					'label' => __( 'Mountain Time (US)', 'skillpulse-lms' ),
				),
				array(
					'value' => 'America/Los_Angeles',
					'label' => __( 'Pacific Time (US)', 'skillpulse-lms' ),
				),
				array(
					'value' => 'Europe/London',
					'label' => __( 'London', 'skillpulse-lms' ),
				),
				array(
					'value' => 'Europe/Paris',
					'label' => __( 'Paris', 'skillpulse-lms' ),
				),
				array(
					'value' => 'Europe/Berlin',
					'label' => __( 'Berlin', 'skillpulse-lms' ),
				),
				array(
					'value' => 'Asia/Tokyo',
					'label' => __( 'Tokyo', 'skillpulse-lms' ),
				),
				array(
					'value' => 'Asia/Shanghai',
					'label' => __( 'Shanghai', 'skillpulse-lms' ),
				),
				array(
					'value' => 'Australia/Sydney',
					'label' => __( 'Sydney', 'skillpulse-lms' ),
				),
			);
		}

		return $timezones;
	}

	/**
	 * Reset wizard (for testing purposes).
	 */
	public function reset_wizard() {
		delete_option( 'splms_wizard_completed' );
		delete_option( 'splms_wizard_data' );
	}
}