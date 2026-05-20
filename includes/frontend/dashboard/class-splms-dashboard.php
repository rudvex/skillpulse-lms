<?php
/**
 * Dashboard Class
 *
 * Handles dashboard functionality with template loading
 *
 * @package SkillPulse_LMS
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Dashboard Class
 *
 * Handles dashboard functionality with template loading and shortcode rendering.
 *
 * @package SkillPulse_LMS
 * @since   1.0.0
 */
class SPLMS_Dashboard {

	/**
	 * Class instance.
	 *
	 * @var SPLMS_Dashboard|null $instance
	 */
	private static $instance = null;

	/**
	 * Available dashboard tabs.
	 *
	 * @var array
	 */
	private $dashboard_tabs = array(
		'my-courses',
		'wishlist',
		'bookmarks',
		'certificates',
		'notifications',
		'orders',
		'settings',
	);

	/**
	 * Get the instance of this class.
	 *
	 * @return SPLMS_Dashboard
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
			self::$instance->setup_hooks();
		}

		return self::$instance;
	}

	/**
	 * Setup hooks
	 *
	 * @since 1.0.0
	 */
	private function setup_hooks() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_dashboard_assets' ), 20 );
		add_action( 'init', array( $this, 'add_dashboard_rewrite_rules' ) );
		add_filter( 'query_vars', array( $this, 'add_dashboard_query_vars' ) );
		add_action( 'wp', array( $this, 'handle_dashboard_request' ) );
	}

	/**
	 * Get dashboard service instance.
	 *
	 * @since 1.0.0
	 *
	 * @return SPLMS_Dashboard_Service
	 */
	public function get_service() {
		return SPLMS_Dashboard_Service::get_instance();
	}

	/**
	 * Enqueue dashboard assets.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_dashboard_assets() {
		// Only load on dashboard page.
		$dashboard_page_id = splms_get_setting( 'dashboard_page_id', 0 );
		if ( ! $dashboard_page_id || ! is_page( $dashboard_page_id ) ) {
			return;
		}

		// Enqueue dashboard script.
		$asset_file = SPLMS_DIR_PATH . 'assets/js/dashboard.asset.php';
		$asset      = file_exists( $asset_file ) ? require $asset_file : array(
			'dependencies' => array( 'jquery' ),
			'version'      => SPLMS_VERSION,
		);

		// Ensure frontend script dependency is included.
		$dependencies = array_merge( array( 'jquery', 'splms-frontend-script' ), isset( $asset['dependencies'] ) ? $asset['dependencies'] : array() );
		$dependencies = array_unique( $dependencies );

		wp_register_script(
			'splms-dashboard',
			SPLMS_ASSETS_URL . 'js/dashboard.js',
			$dependencies,
			isset( $asset['version'] ) ? $asset['version'] : SPLMS_VERSION,
			true
		);

		// Enqueue HugeIcons font with preconnect for faster loading.
		wp_enqueue_style(
			'hugicons-font',
			'https://cdn.hugeicons.com/font/hgi-stroke-rounded.css',
			array(),
			'1.0.0'
		);

		add_filter(
			'wp_resource_hints',
			function ( $urls, $relation_type ) {
				if ( 'preconnect' === $relation_type ) {
					$urls[] = array(
						'href'        => 'https://cdn.hugeicons.com',
						'crossorigin' => 'anonymous',
					);
				}
				return $urls;
			},
			10,
			2
		);

		// Dashboard styles are included in frontend.min.css.
		wp_enqueue_script( 'splms-dashboard' );

		// Also enqueue frontend script for modal functionality.
		if ( ! wp_script_is( 'splms-frontend-script', 'enqueued' ) ) {
			wp_enqueue_script( 'splms-frontend-script' );
		}

		// Localize script.
		wp_localize_script(
			'splms-dashboard',
			'splms_dashboard',
			array(
				'rest_url'   => rest_url( 'skillpulse/v1/' ),
				'rest_nonce' => wp_create_nonce( 'wp_rest' ),
				'ajax_url'   => admin_url( 'admin-ajax.php' ),
				'nonce'      => wp_create_nonce( 'splms_nonce' ),
				'nonces'     => apply_filters(
					'splms_frontend_nonces',
					array(
						'splms_nonce'          => wp_create_nonce( 'splms_nonce' ),
						'splms_frontend_nonce' => wp_create_nonce( 'splms_frontend_nonce' ),
					)
				),
				'strings'    => array(
					'loading'            => __( 'Loading...', 'skillpulse-lms' ),
					'error'              => __( 'Something went wrong. Please try again.', 'skillpulse-lms' ),
					'success'            => __( 'Success!', 'skillpulse-lms' ),
					'saving'             => __( 'Saving...', 'skillpulse-lms' ),
					'changing'           => __( 'Changing...', 'skillpulse-lms' ),
					'passwords_mismatch' => __( 'Passwords do not match.', 'skillpulse-lms' ),
					'create_course'      => __( 'Create New Course', 'skillpulse-lms' ),
				),
			)
		);
	}

	/**
	 * Add dashboard rewrite rules.
	 *
	 * @since 1.0.0
	 */
	public function add_dashboard_rewrite_rules() {
		// Get dashboard page ID.
		$dashboard_page_id = splms_get_setting( 'dashboard_page_id', 0 );

		if ( ! $dashboard_page_id ) {
			return;
		}

		// Get dashboard page slug.
		$dashboard_page = get_post( $dashboard_page_id );
		if ( ! $dashboard_page ) {
			return;
		}

		$dashboard_slug = $dashboard_page->post_name;

		// Add rewrite rule for settings sub-tabs: /dashboard/settings/password/.
		add_rewrite_rule(
			$dashboard_slug . '/settings/([^/]+)/?$',
			'index.php?page_id=' . $dashboard_page_id . '&splms_dashboard_tab=settings&splms_settings_tab=$matches[1]',
			'top'
		);

		// Add rewrite rule for dashboard tabs with actions and IDs: /dashboard/tab-name/action/id/.
		add_rewrite_rule(
			$dashboard_slug . '/([^/]+)/([^/]+)/([^/]+)/?$',
			'index.php?page_id=' . $dashboard_page_id . '&splms_dashboard_tab=$matches[1]&splms_dashboard_action=$matches[2]&splms_dashboard_id=$matches[3]',
			'top'
		);

		// Add rewrite rule for dashboard tabs: /dashboard/tab-name/.
		add_rewrite_rule(
			$dashboard_slug . '/([^/]+)/?$',
			'index.php?page_id=' . $dashboard_page_id . '&splms_dashboard_tab=$matches[1]',
			'top'
		);

		// Check if we need to flush rewrite rules.
		$current_rules          = get_option( 'rewrite_rules' );
		$expected_rule_simple   = $dashboard_slug . '/([^/]+)/?$';
		$expected_rule_complex  = $dashboard_slug . '/([^/]+)/([^/]+)/([^/]+)/?$';
		$expected_rule_settings = $dashboard_slug . '/settings/([^/]+)/?$';

		if ( ! isset( $current_rules[ $expected_rule_simple ] ) || ! isset( $current_rules[ $expected_rule_complex ] ) || ! isset( $current_rules[ $expected_rule_settings ] ) ) {
			// Schedule rewrite flush on next page load.
			add_option( 'splms_flush_rewrite_rules', true );
		}
	}

	/**
	 * Add dashboard query variables.
	 *
	 * @since 1.0.0
	 *
	 * @param array $vars Query variables.
	 * @return array
	 */
	public function add_dashboard_query_vars( $vars ) {
		$vars[] = 'splms_dashboard_tab';
		$vars[] = 'splms_dashboard_action';
		$vars[] = 'splms_dashboard_id';
		$vars[] = 'splms_settings_tab';
		return $vars;
	}

	/**
	 * Handle dashboard requests and validate tabs.
	 *
	 * @since 1.0.0
	 */
	public function handle_dashboard_request() {
		// Flush rewrite rules if needed.
		if ( get_option( 'splms_flush_rewrite_rules' ) ) {
			flush_rewrite_rules();
			delete_option( 'splms_flush_rewrite_rules' );
		}

		$tab = get_query_var( 'splms_dashboard_tab' );

		if ( empty( $tab ) ) {
			return;
		}

		// Check if we're on the dashboard page.
		$dashboard_page_id = splms_get_setting( 'dashboard_page_id', 0 );
		if ( ! $dashboard_page_id || ! is_page( $dashboard_page_id ) ) {
			return;
		}

		// Validate tab.
		if ( ! $this->is_valid_tab( $tab ) ) {
			// Redirect to main dashboard for invalid tabs.
			wp_safe_redirect( home_url( '/dashboard/' ) );
			exit;
		}
	}

	/**
	 * Check if a tab is valid.
	 *
	 * @since 1.0.0
	 *
	 * @param string $tab Tab name.
	 * @return bool
	 */
	public function is_valid_tab( $tab ) {
		return in_array( $tab, $this->dashboard_tabs, true );
	}

	/**
	 * Get current dashboard tab.
	 *
	 * @since 1.0.0
	 *
	 * @return string|null
	 */
	public function get_current_tab() {
		$tab = get_query_var( 'splms_dashboard_tab' );
		return $this->is_valid_tab( $tab ) ? $tab : null;
	}

	/**
	 * Get dashboard tab URL.
	 *
	 * @since 1.0.0
	 *
	 * @param string $tab Tab name.
	 * @return string
	 */
	public function get_tab_url( $tab ) {
		if ( empty( $tab ) ) {
			return home_url( '/dashboard/' );
		}

		return home_url( '/dashboard/' . $tab . '/' );
	}

	/**
	 * Get current settings tab.
	 *
	 * @since 1.0.0
	 *
	 * @return string|null
	 */
	public function get_current_settings_tab() {
		$tab = get_query_var( 'splms_settings_tab' );
		return $this->is_valid_settings_tab( $tab ) ? $tab : 'profile';
	}

	/**
	 * Check if a settings tab is valid.
	 *
	 * @since 1.0.0
	 *
	 * @param string $tab Settings tab name.
	 * @return bool
	 */
	private function is_valid_settings_tab( $tab ) {
		$valid_tabs = array( 'profile', 'password', 'notifications' );
		return in_array( $tab, $valid_tabs, true );
	}

	/**
	 * Get settings tab URL.
	 *
	 * @since 1.0.0
	 *
	 * @param string $settings_tab Settings tab name.
	 * @return string
	 */
	public function get_settings_tab_url( $settings_tab = '' ) {
		if ( empty( $settings_tab ) || 'profile' === $settings_tab ) {
			return home_url( '/dashboard/settings/' );
		}

		return home_url( '/dashboard/settings/' . $settings_tab . '/' );
	}
}
