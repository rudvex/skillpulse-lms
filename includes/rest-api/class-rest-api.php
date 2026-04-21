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
class SkillPulse_LMS_Rest_API {

	/**
	 * Class instance.
	 *
	 * @since 1.0.0
	 *
	 * @var SkillPulse_LMS_Rest_API|null $instance
	 */
	private static $instance;

	/**
	 * Get the instance of this class.
	 *
	 * @since 1.0.0
	 *
	 * @return SkillPulse_LMS_Rest_API The class instance.
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

			'includes/rest-api/admin/class-rest-admin-overview-controller',
		);

		foreach ( $files as $file ) {
			// Include functions file.
			if ( file_exists( SKILLPULSE_LMS_DIR_PATH . $file . '.php' ) ) {
				require_once SKILLPULSE_LMS_DIR_PATH . $file . '.php';
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
		// Setup trial API limits (since [SPLMS_VERSION]).
		add_filter( 'rest_pre_dispatch', array( $this, 'check_trial_api_limits' ), 10, 3 );
		add_action( 'rest_after_insert_splms_course', array( $this, 'track_api_usage' ), 10, 3 );
		add_action( 'rest_after_insert_splms_lesson', array( $this, 'track_api_usage' ), 10, 3 );
		add_action( 'rest_after_insert_splms_quiz', array( $this, 'track_api_usage' ), 10, 3 );
		add_filter( 'rest_post_dispatch', array( $this, 'add_trial_rate_limit_headers' ), 10, 3 );
	}

	/**
	 * Check trial API limits before processing requests.
	 *
	 * Prevents API requests when trial limit is exceeded and returns 429 status.
	 *
	 * @since [SPLMS_VERSION]
	 *
	 * @param mixed           $result  Response to replace the requested version with.
	 * @param WP_REST_Server  $server  Server instance.
	 * @param WP_REST_Request $request Request used to generate the response.
	 * @return mixed Response or WP_Error if limit exceeded.
	 */
	public function check_trial_api_limits( $result, $server, $request ) {
		// Only check SkillPulse LMS API endpoints.
		$route = $request->get_route();
		if ( 0 !== strpos( $route, '/skillpulse-lms/' ) ) {
			return $result; // Not our API.
		}

		// Only check if trial manager is available.
		if ( ! class_exists( 'SkillPulse_LMS_Trial_Manager' ) ) {
			return $result;
		}

		$trial_manager = SkillPulse_LMS_Trial_Manager::get_instance();

		// Allow unlimited API calls if trial is expired or license is active.
		if ( $trial_manager->is_trial_expired() || $trial_manager->has_valid_license() ) {
			return $result;
		}

		// Check for essential endpoints that should not be limited.
		if ( $this->is_essential_endpoint( $route ) ) {
			return $result;
		}

		// For simplified trial system, we don't enforce API limits during active trial.
		// Just allow all API calls during trial period.
		if ( $trial_manager->is_trial_active() ) {
			/**
			 * Fire action to track API usage.
			 *
			 * @since [SPLMS_VERSION]
			 *
			 * @param string $route   API route being accessed.
			 * @param array  $args    Request arguments.
			 */
			do_action( 'splms_api_request', $route, $request->get_params() );
		}

		return $result;
	}

	/**
	 * Track API usage for specific endpoints.
	 *
	 * Tracks usage for data modification endpoints during trial period.
	 *
	 * @since [SPLMS_VERSION]
	 *
	 * @param WP_Post         $post     Inserted or updated post object.
	 * @param WP_REST_Request $request  Request object.
	 * @param bool            $creating True when creating a post, false when updating.
	 * @return void
	 */
	public function track_api_usage( $post, $request, $creating ) {
		// For simplified trial system, we don't track specific API usage.
		// Just fire an action for potential future tracking.
		if ( ! class_exists( 'SkillPulse_LMS_Trial_Manager' ) ) {
			return;
		}

		$trial_manager = SkillPulse_LMS_Trial_Manager::get_instance();

		if ( ! $trial_manager->is_trial_active() ) {
			return;
		}

		// Fire action for tracking if needed.
		do_action( 'splms_api_usage_tracked', $post->post_type, $creating );
	}

	/**
	 * Add trial rate limiting headers to API responses.
	 *
	 * Provides clients with information about API limits and remaining calls.
	 *
	 * @since [SPLMS_VERSION]
	 *
	 * @param WP_HTTP_Response $response Result to send to the client.
	 * @param WP_REST_Server   $server   Server instance.
	 * @param WP_REST_Request  $request  Request used to generate the response.
	 * @return WP_HTTP_Response Response with rate limit headers.
	 */
	public function add_trial_rate_limit_headers( $response, $server, $request ) {
		// Only add headers to SkillPulse LMS API endpoints.
		$route = $request->get_route();
		if ( 0 !== strpos( $route, '/skillpulse-lms/' ) ) {
			return $response;
		}

		// Only add headers if trial manager is available.
		if ( ! class_exists( 'SkillPulse_LMS_Trial_Manager' ) ) {
			return $response;
		}

		$trial_manager = SkillPulse_LMS_Trial_Manager::get_instance();

		// Only add headers during trial period.
		if ( ! $trial_manager->is_trial_active() ) {
			return $response;
		}

		// For simplified trial system, add basic trial headers.
		$trial_data = $trial_manager->get_trial_data();
		if ( $trial_data ) {
			$response->header( 'X-Trial-Active', 'true' );
			$response->header( 'X-Trial-Days-Remaining', $trial_manager->get_days_remaining() );
			$response->header( 'X-Trial-Expires', gmdate( 'c', $trial_data['expires_at'] ) );
		}

		return $response;
	}

	/**
	 * Check if API endpoint is essential and should not be limited.
	 *
	 * Essential endpoints are never blocked during trial period.
	 *
	 * @since [SPLMS_VERSION]
	 *
	 * @param string $route API route path.
	 * @return bool True if essential endpoint.
	 */
	private function is_essential_endpoint( $route ) {
		$essential_patterns = array(
			'/skillpulse-lms/v1/auth/',
			'/skillpulse-lms/v1/license/',
			'/skillpulse-lms/v1/trial/',
			'/skillpulse-lms/v1/config/',
		);

		/**
		 * Filter essential API endpoints that bypass trial limits.
		 *
		 * @since [SPLMS_VERSION]
		 *
		 * @param array $essential_patterns Array of essential endpoint patterns.
		 */
		$essential_patterns = apply_filters( 'splms_essential_api_endpoints', $essential_patterns );

		foreach ( $essential_patterns as $pattern ) {
			if ( 0 === strpos( $route, $pattern ) ) {
				return true;
			}
		}

		return false;
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
		$controller = new SkillPulse_LMS_Rest_Config_Controller();
		$controller->register_routes();

		// Settings.
		$controller = new SkillPulse_LMS_Rest_Settings_Controller();
		$controller->register_routes();

		// Courses.
		$controller = new SkillPulse_LMS_REST_Course_Controller();
		$controller->register_routes();

		// Course Actions (enroll, unenroll, progress, wishlist, bookmark).
		$controller = new SkillPulse_LMS_REST_Course_Actions_Controller();
		$controller->register_routes();

		$controller = new SkillPulse_LMS_REST_Course_Settings_Controller();
		$controller->register_routes();

		$controller = new SkillPulse_LMS_REST_Course_Curriculum_Controller();
		$controller->register_routes();

		// Sections.
		$controller = new SkillPulse_LMS_REST_Section_Controller();
		$controller->register_routes();

		// Section Settings.
		$controller = new SkillPulse_LMS_REST_Section_Settings_Controller();
		$controller->register_routes();

		// Lessons.
		$controller = new SkillPulse_LMS_REST_Lesson_Controller();
		$controller->register_routes();

		// Lesson Actions (progress, complete).
		$controller = new SkillPulse_LMS_REST_Lesson_Actions_Controller();
		$controller->register_routes();

		$controller = new SkillPulse_LMS_REST_Lesson_Settings_Controller();
		$controller->register_routes();

		// Quizzes.
		$controller = new SkillPulse_LMS_REST_Quiz_Controller();
		$controller->register_routes();

		// Quiz Actions (start, resume, restart, state management).
		$controller = new SkillPulse_LMS_REST_Quiz_Actions_Controller();
		$controller->register_routes();

		// Quiz Admin Controllers.
		$controller = new SkillPulse_LMS_REST_Quiz_Settings_Controller();
		$controller->register_routes();

		// Admin Quiz Questions (for editing in admin).
		$controller = new SkillPulse_LMS_REST_Quiz_Questions_Controller();
		$controller->register_routes();

		$controller = new SkillPulse_LMS_REST_Quiz_Attempts_Controller();
		$controller->register_routes();

		if ( splms_get_setting( 'enable_certificates', false ) ) {
		}

		// Enrollments.
		$controller = new SkillPulse_LMS_Enrollments_REST_Controller();
		$controller->register_routes();

		// Signup.
		$controller = new SkillPulse_LMS_REST_Signup_Controller();
		$controller->register_routes();

		$controller = new SkillPulse_LMS_Rest_Admin_Overview_Controller();
		$controller->register_routes();


	}
}
