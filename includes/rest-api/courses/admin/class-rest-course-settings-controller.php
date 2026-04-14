<?php
/**
 * REST API Course Settings Controller
 *
 * Handles REST API endpoints for course settings management.
 * Provides endpoints for getting and updating course settings including access control,
 * content settings, delivery settings, and completion criteria.
 *
 * @package SkillPulse_LMS
 * @since 1.0.0
 *
 * @api
 * Available endpoints:
 * GET  /splms/v1/courses/{id}/settings - Get course settings
 * PUT  /splms/v1/courses/{id}/settings - Update course settings
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST API Course Settings Controller class.
 *
 * @since 1.0.0
 */
class SkillPulse_LMS_REST_Course_Settings_Controller extends SkillPulse_LMS_REST_Course_Controller {

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function __construct() { // phpcs:ignore Generic.CodeAnalysis.UselessOverridingMethod.Found -- Method override needed for future customization.
		parent::__construct();
	}

	/**
	 * Register the routes for the objects of the controller.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register_routes() {
		// Parent routes are not needed for this controller.

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)/settings',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_course_settings' ),
					'permission_callback' => array( $this, 'get_course_settings_permissions_check' ),
					'args'                => array(
						'id' => array(
							'description' => __( 'Unique identifier for the course.', 'skillpulse-lms' ),
							'type'        => 'integer',
							'required'    => true,
						),
					),
					'schema'              => array( $this, 'get_settings_schema' ),
				),
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_course_settings' ),
					'permission_callback' => array( $this, 'update_course_settings_permissions_check' ),
					'args'                => array_merge(
						array(
							'id' => array(
								'description' => __( 'Unique identifier for the course.', 'skillpulse-lms' ),
								'type'        => 'integer',
								'required'    => true,
							),
						),
						$this->get_item_params()
					),
					'schema'              => array( $this, 'get_settings_schema' ),
				),
			)
		);
	}

	/**
	 * Get course settings.
	 *
	 * Retrieves all settings for a specific course including access control,
	 * content settings, delivery settings, and completion criteria.
	 *
	 * @since 1.0.0
	 *
	 * @api {get} /splms/v1/courses/:id/settings Get Course Settings
	 * @apiName GetCourseSettings
	 * @apiGroup Courses
	 * @apiVersion 1.0.0
	 *
	 * @apiDescription Retrieve all settings for a course. Returns comprehensive settings including
	 * access control, content information, delivery mode, and completion criteria.
	 *
	 * @apiParam {Number} id Course unique identifier.
	 *
	 * @apiError (Error 404) course_not_found Course not found.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_course_settings( $request ) {
		$course_id = $request->get_param( 'id' );

		$course = get_post( $course_id );

		if ( ! $course || SPLMS_POST_TYPES['course'] !== $course->post_type ) {
			return new WP_Error( 'course_not_found', __( 'Course not found.', 'skillpulse-lms' ), array( 'status' => 404 ) );
		}

		$settings = splms_get_course_settings( $course_id );

		$settings = apply_filters( 'splms_get_course_settings', $settings, $course_id );

		return rest_ensure_response( $settings );
	}

	/**
	 * Check if a given request has access to get settings.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return bool|WP_Error True if request has access, WP_Error object otherwise.
	 */
	public function get_course_settings_permissions_check( $request ) {
		$retval = true;

		/**
		 * Filter the settings `get_course_settings` permissions check.
		 *
		 * @param bool|WP_Error   $retval  Returned value.
		 * @param WP_REST_Request $request The request sent to the API.
		 *
		 * @since 1.0.0
		 */
		return apply_filters( 'splms_get_course_settings_permissions_check', $retval, $request );
	}

	/**
	 * Update course settings.
	 *
	 * Updates settings for a specific course. Requires edit permissions for the course.
	 * Settings are merged with existing settings, so only provided settings are updated.
	 *
	 * @since 1.0.0
	 *
	 * @api {put} /splms/v1/courses/:id/settings Update Course Settings
	 * @apiName UpdateCourseSettings
	 * @apiGroup Courses
	 * @apiVersion 1.0.0
	 *
	 * @apiDescription Update settings for a course. Requires edit permissions. Only provided
	 * settings are updated; existing settings are preserved.
	 *
	 * @apiParam {Number} id Course unique identifier.
	 * @apiParam {Object} settings Course settings object.
	 * @apiParam {Object} [settings.course_access_settings] Access control settings.
	 * @apiParam {Object} [settings.course_content_info] Content information.
	 * @apiParam {Object} [settings.course_delivery_info] Delivery information.
	 *
	 * @apiError (Error 404) course_not_found Course not found.
	 * @apiError (Error 400) splms_rest_empty_settings No settings provided.
	 * @apiError (Error 403) splms_rest_cannot_edit_settings Insufficient permissions to edit settings.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function update_course_settings( $request ) {
		$course_id = $request->get_param( 'id' );

		if ( ! splms_course_exists( $course_id ) ) {
			return new WP_Error( 'course_not_found', __( 'Course not found.', 'skillpulse-lms' ), array( 'status' => 404 ) );
		}

		$settings = $request->get_param( 'settings' );

		if ( empty( $settings ) ) {
			return new WP_Error(
				'splms_rest_empty_settings',
				__( 'No settings provided.', 'skillpulse-lms' ),
				array( 'status' => 400 )
			);
		}

		$updated_settings = splms_update_course_settings( $course_id, $settings );

		return rest_ensure_response(
			array(
				'success'  => true,
				'settings' => $updated_settings,
			)
		);
	}

	/**
	 * Check if a given request has access to update settings.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return bool|WP_Error True if request has access, WP_Error object otherwise.
	 */
	public function update_course_settings_permissions_check( $request ) {
		$retval = true;

		// Verify nonce for WordPress REST API requests.
		$nonce = $request->get_header( 'X-WP-Nonce' );
		if ( ! $nonce || ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
			$retval = new WP_Error(
				'splms_rest_invalid_nonce',
				__( 'Invalid security token. Please refresh the page and try again.', 'skillpulse-lms' ),
				array( 'status' => 403 )
			);
		}

		if ( true === $retval && ! current_user_can( 'edit_post', $request->get_param( 'id' ) ) ) {
			$retval = new WP_Error(
				'splms_rest_cannot_edit_settings',
				__( 'Sorry, you are not allowed to edit settings for this course.', 'skillpulse-lms' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		/**
		 * Filter the settings `update_course_settings` permissions check.
		 *
		 * @param bool|WP_Error   $retval  Returned value.
		 * @param WP_REST_Request $request The request sent to the API.
		 *
		 * @since 1.0.0
		 */
		return apply_filters( 'splms_update_course_settings_permissions_check', $retval, $request );
	}

	/**
	 * Get the query params for collections.
	 *
	 * @since 1.0.0
	 *
	 * @return array Collection parameters array.
	 */
	public function get_collection_params() {
		$params = parent::get_collection_params();

		$params['settings'] = array(
			'description'       => __( 'Settings for the course.', 'skillpulse-lms' ),
			'type'              => 'object',
			'context'           => array( 'view', 'edit' ),
			'sanitize_callback' => 'wp_parse_args',
			'default'           => array(),
		);

		return $params;
	}

	/**
	 * Get the query params for single items.
	 *
	 * @since 1.0.0
	 *
	 * @return array Item parameters array.
	 */
	public function get_item_params() {
		$params['settings'] = array(
			'description'       => __( 'Settings for the course.', 'skillpulse-lms' ),
			'type'              => 'object',
			'context'           => array( 'view', 'edit' ),
			'sanitize_callback' => 'wp_parse_args',
			'default'           => array(),
		);

		return $params;
	}

	/**
	 * Get the settings schema, conforming to JSON Schema.
	 *
	 * @since 1.0.0
	 *
	 * @return array Schema definition array.
	 */
	public function get_settings_schema() {
		$schema = array(
			'$schema'     => 'http://json-schema.org/draft-04/schema#',
			'title'       => 'course-settings',
			'type'        => 'object',
			'description' => __( 'Course settings including access control, content information, delivery settings, and completion criteria.', 'skillpulse-lms' ),
			'properties'  => array(
				'course_access_settings' => array(
					'description' => __( 'Course access control settings.', 'skillpulse-lms' ),
					'type'        => 'object',
					'properties'  => array(
						'course_access_type' => array(
							'description' => __( 'Access type: public_free, public_paid, invitation_only, prerequisite_required.', 'skillpulse-lms' ),
							'type'        => 'string',
							'enum'        => array( 'public_free', 'public_paid', 'invitation_only', 'prerequisite_required' ),
						),
						'price'              => array(
							'description' => __( 'Course price (for paid courses).', 'skillpulse-lms' ),
							'type'        => 'number',
						),
					),
				),
				'course_content_info'    => array(
					'description' => __( 'Course content information.', 'skillpulse-lms' ),
					'type'        => 'object',
					'properties'  => array(
						'difficulty_level' => array(
							'description' => __( 'Course difficulty: beginner, intermediate, advanced, all.', 'skillpulse-lms' ),
							'type'        => 'string',
							'enum'        => array( 'beginner', 'intermediate', 'advanced', 'all' ),
						),
						'course_language'  => array(
							'description' => __( 'Course language code (en, es, fr, etc.).', 'skillpulse-lms' ),
							'type'        => 'string',
						),
					),
				),
				'course_delivery_info'   => array(
					'description' => __( 'Course delivery information.', 'skillpulse-lms' ),
					'type'        => 'object',
					'properties'  => array(
						'delivery_mode' => array(
							'description' => __( 'Delivery mode: self_paced or cohort.', 'skillpulse-lms' ),
							'type'        => 'string',
							'enum'        => array( 'self_paced', 'cohort' ),
						),
					),
				),
			),
		);

		return $schema;
	}
}
