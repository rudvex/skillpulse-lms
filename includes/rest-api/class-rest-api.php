<?php
/**
 * REST API Initializer
 *
 * Main controller for initializing and registering all REST API endpoints.
 * Handles route registration, class loading, and REST API setup.
 *
 * @package SkillPulse_LMS
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST API main class.
 *
 * Handles initialization and registration of all REST API endpoints.
 *
 * @since 1.0.0
 */
class SPLMS_Rest_API {

	/**
	 * Class instance.
	 *
	 * @since 1.0.0
	 *
	 * @var SPLMS_Rest_API|null $instance
	 */
	private static $instance;

	/**
	 * Get the instance of this class.
	 *
	 * @since 1.0.0
	 *
	 * @return SPLMS_Rest_API The class instance.
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			$class_name     = __CLASS__;
			self::$instance = new $class_name();
		}

		// Only call methods if instance is not null.
		if ( self::$instance ) {
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
		$files = array(
			'includes/rest-api/functions',

			'includes/rest-api/config/class-rest-config-controller',

			// REST API Settings.
			'includes/rest-api/settings/class-rest-settings-controller',

			// REST API Courses.
			'includes/rest-api/courses/class-rest-course-controller',
			'includes/rest-api/courses/class-rest-course-actions-controller',
			'includes/rest-api/courses/admin/class-rest-course-settings-controller',
			'includes/rest-api/courses/admin/class-rest-course-curriculum-controller',

			// REST API Sections.
			'includes/rest-api/sections/class-rest-section-controller',
			'includes/rest-api/sections/admin/class-rest-section-settings-controller',

			// REST API Lessons.
			'includes/rest-api/lessons/class-rest-lesson-controller',
			'includes/rest-api/lessons/class-rest-lesson-actions-controller',
			'includes/rest-api/lessons/admin/class-rest-lesson-settings-controller',

			// REST API Quizzes.
			'includes/rest-api/quizzes/class-rest-quiz-controller',
			'includes/rest-api/quizzes/class-rest-quiz-actions-controller',
			'includes/rest-api/quizzes/class-rest-quiz-questions-controller',
			'includes/rest-api/quizzes/admin/class-rest-quiz-settings-controller',
			'includes/rest-api/quizzes/admin/class-rest-quiz-questions-controller',
			'includes/rest-api/quizzes/class-rest-quiz-attempts-controller',

			// REST API Features.
			'includes/rest-api/features/enrollments/class-rest-enrollments-controller',
			'includes/rest-api/features/signup/class-rest-signup-controller',

			// REST API Notifications (Frontend).
			'includes/rest-api/notifications/class-rest-notifications-controller',

			'includes/rest-api/admin/class-rest-admin-overview-controller',
			'includes/rest-api/admin/class-rest-admin-email-templates-controller',
		);

		foreach ( $files as $file ) {
			// Include functions file.
			if ( file_exists( SPLMS_DIR_PATH . $file . '.php' ) ) {
				require_once SPLMS_DIR_PATH . $file . '.php';
			}
		}
	}

	/**
	 * Initiate the required classes.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	protected function load_classes() {
	}

	/**
	 * Action hooks.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	protected function setup_actions() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );

		// Initialize JWT authentication handler.
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
	 * Register routes.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register_routes() {

		// Configuration.
		$controller = new SPLMS_Rest_Config_Controller();
		$controller->register_routes();

		// Settings.
		$controller = new SPLMS_Rest_Settings_Controller();
		$controller->register_routes();

		// Courses.
		$controller = new SPLMS_REST_Course_Controller();
		$controller->register_routes();

		// Course Actions (enroll, unenroll, progress, wishlist, bookmark).
		$controller = new SPLMS_REST_Course_Actions_Controller();
		$controller->register_routes();

		$controller = new SPLMS_REST_Course_Settings_Controller();
		$controller->register_routes();

		$controller = new SPLMS_REST_Course_Curriculum_Controller();
		$controller->register_routes();

		// Sections.
		$controller = new SPLMS_REST_Section_Controller();
		$controller->register_routes();

		// Section Settings.
		$controller = new SPLMS_REST_Section_Settings_Controller();
		$controller->register_routes();

		// Lessons.
		$controller = new SPLMS_REST_Lesson_Controller();
		$controller->register_routes();

		// Lesson Actions (progress, complete).
		$controller = new SPLMS_REST_Lesson_Actions_Controller();
		$controller->register_routes();

		$controller = new SPLMS_REST_Lesson_Settings_Controller();
		$controller->register_routes();

		// Quizzes.
		$controller = new SPLMS_REST_Quiz_Controller();
		$controller->register_routes();

		// Quiz Actions (start, resume, restart, state management).
		$controller = new SPLMS_REST_Quiz_Actions_Controller();
		$controller->register_routes();

		// Quiz Admin Controllers.
		$controller = new SPLMS_REST_Quiz_Settings_Controller();
		$controller->register_routes();

		// Admin Quiz Questions (for editing in admin).
		$controller = new SPLMS_REST_Quiz_Questions_Controller();
		$controller->register_routes();

		$controller = new SPLMS_REST_Quiz_Attempts_Controller();
		$controller->register_routes();

		if ( splms_get_setting( 'enable_certificates', false ) ) {
		}

		// Enrollments.
		$controller = new SPLMS_Enrollments_REST_Controller();
		$controller->register_routes();

		// Signup.
		$controller = new SPLMS_REST_Signup_Controller();
		$controller->register_routes();

		// Notifications (Frontend).
		$controller = new SPLMS_REST_Notifications_Controller();
		$controller->register_routes();

		$controller = new SPLMS_Rest_Admin_Overview_Controller();
		$controller->register_routes();

		// Email Templates.
		$controller = new SPLMS_REST_Admin_Email_Templates_Controller();
		$controller->register_routes();

		// Notifications.
	}
}
