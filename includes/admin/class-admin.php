<?php
/**
 * Admin
 *
 * Main admin class that orchestrates all admin functionality.
 * Handles asset enqueuing, admin menus, and admin interface management.
 *
 * @package SkillPulse_LMS
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class SPLMS_Admin
 *
 * Main admin class that orchestrates all admin functionality.
 * Handles asset enqueuing, admin menus, and admin interface management.
 *
 * @since 1.0.0
 */
class SPLMS_Admin {

	/**
	 * Class instance.
	 *
	 * @since 1.0.0
	 * @var SPLMS_Admin|null $instance
	 */
	private static $instance;

	/**
	 * Get the instance of this class.
	 *
	 * @since 1.0.0
	 *
	 * @return SPLMS_Admin The singleton instance.
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			$class_name     = __CLASS__;
			self::$instance = new $class_name();
			self::$instance->setup_globals();
			self::$instance->load_classes();
			self::$instance->setup_actions();
		}

		return self::$instance;
	}

	/**
	 * Setup globals by including necessary admin files.
	 *
	 * @since 1.0.0
	 */
	protected function setup_globals() {
		$files = array(
			'includes/admin/manager/class-admin-menus',
			'includes/admin/manager/class-admin-post-types',
			'includes/admin/manager/class-admin-metaboxes',
			'includes/admin/manager/class-admin-columns',
		);

		if ( splms_get_setting( 'user_signup_enabled', false ) ) {
			$files[] = 'includes/admin/signup/class-signup-admin';
			$files[] = 'includes/admin/signup/class-signup-list-table';
		}

		// Include wizard files only if wizard is not completed.
		if ( ! get_option( 'splms_wizard_completed', false ) ) {
			$files[] = 'includes/admin/wizard/class-setup-wizard';
			$files[] = 'includes/admin/wizard/class-wizard-ajax';
		}

		foreach ( $files as $file ) {
			// Include functions file.
			if ( file_exists( SPLMS_DIR_PATH . $file . '.php' ) ) {
				require SPLMS_DIR_PATH . $file . '.php';
			}
		}
	}

	/**
	 * Load and initialize the required admin classes.
	 *
	 * @since 1.0.0
	 */
	protected function load_classes() {
		SPLMS_Admin_Menus::get_instance();
		SPLMS_Admin_Post_Types::get_instance();
		SPLMS_Admin_Metaboxes::get_instance();
		SPLMS_Admin_Columns::get_instance();

		if ( splms_get_setting( 'user_signup_enabled', false ) ) {
			SPLMS_Signup_Admin::get_instance();
		}
	}

	/**
	 * Setup action hooks.
	 *
	 * @since 1.0.0
	 */
	protected function setup_actions() {
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'all_admin_notices', array( $this, 'admin_header' ), 0 );
		add_action( 'admin_body_class', array( $this, 'admin_body_class' ) );
		add_action( 'admin_notices', array( $this, 'course_pricing_validation_notice' ) );
		add_action( 'init', array( $this, 'init_wizard_classes' ) );
	}

	/**
	 * Initialize wizard classes after textdomain is loaded.
	 *
	 * @since 1.0.0
	 */
	public function init_wizard_classes() {
		// Initialize wizard classes only if wizard is not completed.
		if ( ! get_option( 'splms_wizard_completed', false ) ) {
			SPLMS_Setup_Wizard::get_instance();
			SPLMS_Wizard_Ajax::get_instance();
		}
	}


	/**
	 * Enqueue the admin assets.
	 *
	 * @since 1.0.0
	 */
	public function admin_enqueue_scripts() {
		if ( ! $this->is_splms_admin_screen() ) {
			return;
		}

		// Wizard page has its own JS bundle — only load shared styles, skip admin JS.
		if ( $this->is_wizard_screen() ) {
			wp_enqueue_style( 'wp-components' );
			wp_enqueue_style(
				'splms-admin-style',
				SPLMS_URL_PATH . 'assets/css/admin.min.css',
				array(),
				SPLMS_VERSION
			);
			return;
		}

		wp_enqueue_media();
		wp_enqueue_script( 'wp-util' );

		$admin_asset_file      = include SPLMS_DIR_PATH . 'assets/js/admin.asset.php';
		$react_core_asset_file = include SPLMS_DIR_PATH . 'assets/js/react-core.asset.php';

		// Register the admin style and script.
		wp_register_style(
			'splms-admin-style',
			SPLMS_URL_PATH . 'assets/css/admin.min.css',
			array(),
			SPLMS_VERSION
		);

		wp_register_script(
			'splms-admin-script',
			SPLMS_URL_PATH . 'assets/js/admin.js',
			$admin_asset_file['dependencies'],
			$admin_asset_file['version'],
			array( 'in_footer' => true )
		);

		wp_register_script(
			'splms-react-core-script',
			SPLMS_URL_PATH . 'assets/js/react-core.js',
			$react_core_asset_file['dependencies'],
			$react_core_asset_file['version'],
			array( 'in_footer' => true )
		);

		// Get email templates data.
		$email_templates = SPLMS_Email_Templates::get_instance()->get_all_templates();

		// Get global settings for cross-store conditional logic.
		$global_settings = SPLMS_Settings::get_instance()->get_all_settings();

		// Add membership integration status to global settings.
		$global_settings['has_membership_integration'] = splms_has_membership_integration();

		// Smart configuration loading - minimal data only.
		$localize_data = array(
			'nonce'             => wp_create_nonce( 'skillpulse-lms' ),
			'restNonce'         => wp_create_nonce( 'skillpulse-lms' ),
			'license_nonce'     => wp_create_nonce( 'splms_license_nonce' ),
			'admin_nonce'       => wp_create_nonce( 'splms_admin_nonce' ),
			'image_url'         => SPLMS_URL_PATH . 'assets/images/',
			'back_cta_label'    => __( 'Back to Courses', 'skillpulse-lms' ),
			'siteUrl'           => site_url(),
			'homeUrl'           => home_url(),
			'adminUrl'          => admin_url(),
			'coursesUrl'        => admin_url( 'edit.php?post_type=' . SPLMS_POST_TYPES['course'] ),
			'posts_url'         => admin_url( 'post.php' ),
			'all_sp_post_types' => SPLMS_POST_TYPES,
			'email_templates'   => $email_templates,
			'global_settings'   => $global_settings,
			// Configuration loading URLs for on-demand fetching.
			'config_api_url'    => rest_url( 'splms/v1/config/' ),
			'config_nonce'      => wp_create_nonce( 'wp_rest' ),
			'rest_url'          => rest_url(),
			'ajax_url'          => admin_url( 'admin-ajax.php' ),
		);

		$localize_data = apply_filters( 'splms_admin_localize_data', $localize_data );

		wp_localize_script( 'splms-react-core-script', 'SPLMSCore_Data', $localize_data );

		// Enqueue the admin style and script.
		wp_enqueue_style( 'wp-components' );
		wp_enqueue_style( 'splms-admin-style' );
		wp_enqueue_script( 'splms-admin-script' );
		wp_enqueue_script( 'splms-react-core-script' );
	}

	/**
	 * Check if current admin screen is a SkillPulse LMS screen.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if on a SkillPulse LMS admin screen.
	 */
	private function is_splms_admin_screen() {
		$screen = get_current_screen();

		if ( ! $screen ) {
			return false;
		}

		// Check for SkillPulse LMS post types.
		$splms_post_types = array_values( SPLMS_POST_TYPES );
		if ( in_array( $screen->post_type, $splms_post_types, true ) ) {
			return true;
		}

		// Check for SkillPulse LMS admin pages.
		if ( false !== strpos( $screen->id, 'skillpulse-lms' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if current screen is the setup wizard page.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if on the setup wizard page.
	 */
	private function is_wizard_screen() {
		$screen = get_current_screen();
		return $screen && 'skillpulse-lms_page_splms-setup-wizard' === $screen->id;
	}


	/**
	 * Add the admin header.
	 *
	 * @since 1.0.0
	 */
	public function admin_header() {
		global $current_screen, $pagenow;

		// Check if we're on the course listing page.
		if ( 'edit.php' === $pagenow && isset( $_GET['post_type'] ) && SPLMS_POST_TYPES['course'] === $_GET['post_type'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			?>
			<div id="splms-course-listing-header-wrapper"></div>
			<?php
			return;
		}

		// Check if we're on the lesson listing page.
		if ( 'edit.php' === $pagenow && isset( $_GET['post_type'] ) && SPLMS_POST_TYPES['lesson'] === $_GET['post_type'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			?>
			<div id="splms-lesson-listing-header-wrapper"></div>
			<?php
			return;
		}

		// Check if we're on the quiz listing page.
		if ( 'edit.php' === $pagenow && isset( $_GET['post_type'] ) && SPLMS_POST_TYPES['quiz'] === $_GET['post_type'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			?>
			<div id="splms-quiz-listing-header-wrapper"></div>
			<?php
			return;
		}

		// Check if we're on the section listing page.
		if ( 'edit.php' === $pagenow && isset( $_GET['post_type'] ) && SPLMS_POST_TYPES['section'] === $_GET['post_type'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			?>
			<div id="splms-section-listing-header-wrapper"></div>
			<?php
			return;
		}

		// Check if we're on any of our custom post type edit screens.
		if ( empty( $current_screen ) ) {
			return;
		}

		// Course editor header.
		if ( SPLMS_POST_TYPES['course'] === $current_screen->id ) {
			?>
			<div id="splms-course-header-wrapper"></div>
			<?php
			return;
		}

		// Lesson editor header.
		if ( SPLMS_POST_TYPES['lesson'] === $current_screen->id ) {
			?>
			<div id="splms-lesson-header-wrapper"></div>
			<?php
			return;
		}

		// Quiz editor header.
		if ( SPLMS_POST_TYPES['quiz'] === $current_screen->id ) {
			?>
			<div id="splms-quiz-header-wrapper"></div>
			<?php
			return;
		}

		// Section editor header.
		if ( SPLMS_POST_TYPES['section'] === $current_screen->id ) {
			?>
			<div id="splms-section-header-wrapper"></div>
			<?php
			return;
		}
	}

	/**
	 * Get the plugin icon as base64 encoded SVG.
	 *
	 * @since 1.0.0
	 *
	 * @return string Base64 encoded SVG icon.
	 */
	public function get_plugin_icon() {
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local file, not remote URL.
		$icon = file_get_contents( SPLMS_DIR_PATH . '/assets/images/logo-icon.svg' );

		return 'data:image/svg+xml;base64,' . base64_encode( $icon ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
	}

	/**
	 * Modify the admin body classes for specific screens.
	 *
	 * @since 1.0.0
	 *
	 * @param string $class_name Existing body class(es).
	 *
	 * @return string Modified body class(es) with additional LMS-specific classes.
	 */
	public function admin_body_class( $class_name = '' ) {

		$screen = get_current_screen();
		if ( in_array( $screen->id, SPLMS_POST_TYPES, true ) ) {
			$class_name .= ' skillpulse-lms-post-type ' . $screen->post_type . '-post-type';
		}

		if ( in_array( $screen->post_type, SPLMS_POST_TYPES, true ) ) {
			$class_name .= ' skillpulse-lms-screen';
		}

		$class_name .= ' skillpulse-lms-user-admin';

		return $class_name;
	}


	/**
	 * Display pricing validation notice for courses with invalid pricing.
	 *
	 * Shows an admin notice when a course using section-based pricing
	 * has a price that doesn't make business sense relative to individual sections.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function course_pricing_validation_notice() {
		global $post, $pagenow;

		// Only show on course edit screens.
		if ( ! in_array( $pagenow, array( 'post.php', 'post-new.php' ), true ) ) {
			return;
		}

		if ( ! $post || SPLMS_POST_TYPES['course'] !== $post->post_type ) {
			return;
		}

		// Only validate published courses.
		if ( 'publish' !== $post->post_status ) {
			return;
		}

		// Validate pricing.
		$validation = splms_validate_course_pricing( $post->ID );

		if ( is_wp_error( $validation ) ) {
			$smart_price = splms_calculate_smart_course_price( $post->ID, 'discount' );

			printf(
				'<div class="notice notice-error"><p><strong>%s</strong></p><p>%s</p><p><strong>%s:</strong> %s</p><p><a href="#" class="button button-secondary" onclick="return false;">%s</a></p></div>',
				esc_html__( 'Course Pricing Issue Detected', 'skillpulse-lms' ),
				wp_kses_post( $validation->get_error_message() ),
				esc_html__( 'Recommended Bundle Price', 'skillpulse-lms' ),
				esc_html(
					splms_get_price_format( $smart_price ) . ' ' . sprintf(
					/* translators: %s: Discount percentage */
						__( '(%s%% discount from sections total)', 'skillpulse-lms' ),
						'15'
					)
				),
				esc_html__( 'Learn more about pricing strategies', 'skillpulse-lms' )
			);
		}
	}
}
