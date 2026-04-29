<?php
/**
 * Frontend class.
 *
 * Handles frontend functionality including scripts, styles, and query modifications.
 *
 * @package SkillPulse_LMS
 * @subpackage Frontend
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Frontend class.
 *
 * @package SkillPulse_LMS
 * @subpackage Frontend
 * @since 1.0.0
 */
class SkillPulse_LMS_Frontend {

	/**
	 * Class instance.
	 *
	 * @var SkillPulse_LMS_Frontend|null $instance
	 */
	private static $instance = null;

	/**
	 * Get the instance of this class.
	 *
	 * @since 1.0.0
	 *
	 * @return SkillPulse_LMS_Frontend
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
	 * Setup Globals.
	 *
	 * @since 1.0.0
	 */
	protected function setup_globals() {
		// Load main system components.
		$files = array(
			'includes/frontend/core/class-template',
			'includes/frontend/core/class-shortcode',
			'includes/blocks/class-blocks',
			'includes/frontend/dashboard/class-splms-dashboard',
			'includes/frontend/dashboard/class-splms-dashboard-api',
			'includes/frontend/dashboard/class-splms-dashboard-service',
			'includes/modules/certificates/class-certificate-display',
		);

		foreach ( $files as $file ) {
			// Include functions file.
			if ( file_exists( SKILLPULSE_LMS_DIR_PATH . $file . '.php' ) ) {
				require SKILLPULSE_LMS_DIR_PATH . $file . '.php';
			}
		}

		// Load header and footer template functions.
		$template_functions = array(
			'templates/shared/header-functions',
			'templates/shared/footer-functions',
		);

		foreach ( $template_functions as $file ) {
			if ( file_exists( SKILLPULSE_LMS_DIR_PATH . $file . '.php' ) ) {
				require_once SKILLPULSE_LMS_DIR_PATH . $file . '.php';
			}
		}
	}

	/**
	 * Initiate the required classes.
	 *
	 * @since 1.0.0
	 */
	protected function load_classes() {
		// Initialize core components.
		SkillPulse_LMS_Template::get_instance();
		SkillPulse_LMS_Shortcode::get_instance();
		SkillPulse_LMS_Blocks::get_instance();

		// Initialize dashboard.
		SkillPulse_LMS_Dashboard::get_instance();
		SkillPulse_LMS_Dashboard_API::get_instance();
		SkillPulse_LMS_Dashboard_Service::get_instance();

		// Allow modules to initialize frontend components.
		do_action( 'splms_frontend_loaded' );
	}

	/**
	 * Action hooks.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	protected function setup_actions() {
		add_action( 'wp_enqueue_scripts', array( $this, 'load_enqueue' ) );
		add_action( 'init', array( $this, 'register_nav_menus' ) );
	}

	/**
	 * Load enqueue scripts and styles.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function load_enqueue() {
		$this->enqueue_styles();
		$this->enqueue_scripts();
	}

	/**
	 * Enqueue frontend styles.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function enqueue_styles() {
		$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		// Fall back to minified if non-minified file doesn't exist.
		$css_file_path = SKILLPULSE_LMS_DIR_PATH . "assets/css/frontend{$min}.css";
		if ( ! file_exists( $css_file_path ) ) {
			$min           = '.min';
			$css_file_path = SKILLPULSE_LMS_DIR_PATH . 'assets/css/frontend.min.css';
		}
		$file_version = file_exists( $css_file_path ) ? filemtime( $css_file_path ) : SKILLPULSE_LMS_VERSION;

		wp_register_style(
			'splms-frontend-style',
			SKILLPULSE_LMS_ASSETS_URL . "css/frontend{$min}.css",
			array(),
			$file_version
		);

		wp_enqueue_style( 'splms-frontend-style' );

		// Enqueue fullscreen lesson/quiz viewer styles on single lesson/quiz pages.
		if ( is_singular( array( SPLMS_POST_TYPES['lesson'], SPLMS_POST_TYPES['quiz'] ) ) ) {
			$fullscreen_css_path = SKILLPULSE_LMS_DIR_PATH . "assets/css/fullscreen{$min}.css";
			if ( ! file_exists( $fullscreen_css_path ) ) {
				$fullscreen_css_path = SKILLPULSE_LMS_DIR_PATH . 'assets/css/fullscreen.min.css';
			}
			$fullscreen_version = file_exists( $fullscreen_css_path ) ? filemtime( $fullscreen_css_path ) : SKILLPULSE_LMS_VERSION;

			wp_register_style(
				'splms-fullscreen-style',
				SKILLPULSE_LMS_ASSETS_URL . "css/fullscreen{$min}.css",
				array(),
				$fullscreen_version
			);

			wp_enqueue_style( 'splms-fullscreen-style' );
		}
	}

	/**
	 * Enqueue frontend scripts.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		// Only load frontend scripts on SkillPulse LMS pages.
		$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		// Get asset file for dependencies and version.
		$asset_file = SKILLPULSE_LMS_DIR_PATH . 'assets/js/frontend.asset.php';
		$asset      = file_exists( $asset_file ) ? require $asset_file : array(
			'dependencies' => array( 'jquery' ),
			'version'      => SKILLPULSE_LMS_VERSION,
		);

		wp_register_script(
			'splms-frontend-script',
			SKILLPULSE_LMS_ASSETS_URL . 'js/frontend.js',
			array_merge( array( 'jquery', 'wp-util' ), isset( $asset['dependencies'] ) ? $asset['dependencies'] : array() ),
			isset( $asset['version'] ) ? $asset['version'] : SKILLPULSE_LMS_VERSION,
			true
		);

		// Prepare consolidated frontend data.
		$user_id        = get_current_user_id();
		$user_logged_in = is_user_logged_in();

		// Build consolidated frontend data object.
		$frontend_data = array(
			// Core AJAX/REST data.
			'ajax_url'       => admin_url( 'admin-ajax.php' ),
			'rest_url'       => rest_url(),
			'rest_nonce'     => wp_create_nonce( 'wp_rest' ),

			// User data.
			'user_id'        => $user_id,
			'user_logged_in' => $user_logged_in,

			// Nonces for different actions.
			'nonces'         => apply_filters(
				'splms_frontend_nonces',
				array(
					'splms_nonce'          => wp_create_nonce( 'splms_nonce' ),
					'splms_frontend_nonce' => wp_create_nonce( 'splms_frontend_nonce' ),
					'splms_reviews_nonce'  => wp_create_nonce( 'splms_reviews_nonce' ),
				)
			),

			// Feature settings.
			'settings'       => array(
				'enable_course_wishlist' => function_exists( 'splms_get_setting' ) ? (bool) splms_get_setting( 'enable_course_wishlist', true ) : true,
				'enable_bookmarks'       => function_exists( 'splms_get_setting' ) ? (bool) splms_get_setting( 'enable_bookmarks', true ) : true,
			),

			// Internationalization strings.
			'strings'        => array(
				// Common strings.
				'loading'               => __( 'Loading...', 'skillpulse-lms' ),
				'error'                 => __( 'Something went wrong. Please try again.', 'skillpulse-lms' ),
				'success'               => __( 'Success!', 'skillpulse-lms' ),

				// Notification strings.
				'notifications'         => __( 'Notifications', 'skillpulse-lms' ),
				'loading_notifications' => __( 'Loading notifications...', 'skillpulse-lms' ),
				'refresh'               => __( 'Refresh', 'skillpulse-lms' ),
				'mark_all_read'         => __( 'Mark all read', 'skillpulse-lms' ),
				'no_notifications'      => __( 'No notifications yet', 'skillpulse-lms' ),
				'no_notifications_desc' => __( 'We\'ll notify you when something happens.', 'skillpulse-lms' ),
				'unread'                => __( 'Unread', 'skillpulse-lms' ),
				'recent'                => __( 'Recent Notifications', 'skillpulse-lms' ),
				'just_now'              => __( 'Just now', 'skillpulse-lms' ),
				'minute_ago'            => __( 'minute ago', 'skillpulse-lms' ),
				'minutes_ago'           => __( 'minutes ago', 'skillpulse-lms' ),
				'hour_ago'              => __( 'hour ago', 'skillpulse-lms' ),
				'hours_ago'             => __( 'hours ago', 'skillpulse-lms' ),
				'day_ago'               => __( 'day ago', 'skillpulse-lms' ),
				'days_ago'              => __( 'days ago', 'skillpulse-lms' ),

				// Review strings.
				'confirm_delete_review' => __( 'Are you sure you want to delete this review?', 'skillpulse-lms' ),
				'review_submitted'      => __( 'Review submitted successfully!', 'skillpulse-lms' ),
				'review_updated'        => __( 'Review updated successfully!', 'skillpulse-lms' ),
				'review_deleted'        => __( 'Review deleted successfully!', 'skillpulse-lms' ),
				'error_occurred'        => __( 'An error occurred. Please try again.', 'skillpulse-lms' ),
				'rating_required'       => __( 'Please select a rating.', 'skillpulse-lms' ),
				'review_required'       => __( 'Please write a review.', 'skillpulse-lms' ),
				'login_required'        => __( 'Please log in to submit a review.', 'skillpulse-lms' ),
			),
		);

		// Add course_id for checkout pages.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- GET parameter for display only.
		if ( isset( $_GET['course_id'] ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- GET parameter for display only.
			$frontend_data['course_id'] = intval( wp_unslash( $_GET['course_id'] ) );
		}

		// Add course data for lesson pages.
		if ( is_singular( SPLMS_POST_TYPES['lesson'] ) ) {
			$lesson_id = get_the_ID();
			$course_id = splms_get_lesson_course( $lesson_id );

			if ( $course_id ) {
				$frontend_data['course_id']  = $course_id;
				$frontend_data['course_url'] = get_permalink( $course_id );
			}
		}

		// Localize script with consolidated data.
		wp_localize_script(
			'splms-frontend-script',
			'splms_frontend',
			$frontend_data
		);

		wp_enqueue_script( 'wp-util' );
		wp_enqueue_script( 'splms-frontend-script' );

		// Enqueue fullscreen lesson viewer scripts on single lesson pages.
		if ( is_singular( SPLMS_POST_TYPES['lesson'] ) ) {
			$lesson_viewer_asset_file = SKILLPULSE_LMS_DIR_PATH . 'assets/js/lesson-viewer.asset.php';
			$lesson_viewer_asset      = file_exists( $lesson_viewer_asset_file ) ? require $lesson_viewer_asset_file : array(
				'dependencies' => array( 'jquery' ),
				'version'      => SKILLPULSE_LMS_VERSION,
			);

			wp_register_script(
				'splms-lesson-viewer',
				SKILLPULSE_LMS_ASSETS_URL . 'js/lesson-viewer.js',
				array_merge( array( 'jquery', 'splms-frontend-script' ), isset( $lesson_viewer_asset['dependencies'] ) ? $lesson_viewer_asset['dependencies'] : array() ),
				isset( $lesson_viewer_asset['version'] ) ? $lesson_viewer_asset['version'] : SKILLPULSE_LMS_VERSION,
				true
			);

			// Only enqueue video APIs when the lesson contains video content.
			$lesson_type = splms_get_lesson_type( get_the_ID() );
			if ( 'video' === $lesson_type ) {
				wp_enqueue_script(
					'youtube-iframe-api',
					'https://www.youtube.com/iframe_api',
					array(),
					'3.0', // YouTube API version.
					true
				);

				wp_enqueue_script(
					'vimeo-player-api',
					'https://player.vimeo.com/api/player.js',
					array(),
					'2.0', // Vimeo Player API version.
					true
				);
			}

			wp_enqueue_script( 'splms-lesson-viewer' );
		}

		// Enqueue fullscreen quiz viewer scripts on single quiz pages.
		if ( is_singular( SPLMS_POST_TYPES['quiz'] ) ) {
			$quiz_viewer_asset_file = SKILLPULSE_LMS_DIR_PATH . 'assets/js/quiz-viewer.asset.php';
			$quiz_viewer_asset      = file_exists( $quiz_viewer_asset_file ) ? require $quiz_viewer_asset_file : array(
				'dependencies' => array( 'jquery' ),
				'version'      => SKILLPULSE_LMS_VERSION,
			);

			wp_register_script(
				'splms-quiz-viewer',
				SKILLPULSE_LMS_ASSETS_URL . 'js/quiz-viewer.js',
				array_merge( array( 'jquery', 'splms-frontend-script' ), isset( $quiz_viewer_asset['dependencies'] ) ? $quiz_viewer_asset['dependencies'] : array() ),
				isset( $quiz_viewer_asset['version'] ) ? $quiz_viewer_asset['version'] : SKILLPULSE_LMS_VERSION,
				true
			);

			wp_enqueue_script( 'splms-quiz-viewer' );
		}
	}

	/**
	 * Register navigation menus.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register_nav_menus() {
		register_nav_menus(
			array(
				'primary' => __( 'Primary Menu', 'skillpulse-lms' ),
				'footer'  => __( 'Footer Menu', 'skillpulse-lms' ),
			)
		);
	}
}
