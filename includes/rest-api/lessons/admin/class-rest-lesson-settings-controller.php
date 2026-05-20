<?php
/**
 * Lesson Settings REST API Controller
 *
 * Handles REST API endpoints for lesson settings management.
 * Provides endpoints for getting and updating lesson-specific settings.
 *
 * @package SkillPulse_LMS
 * @since 1.0.0
 *
 * @api
 * Available endpoints:
 * GET  /splms/v1/lessons/{id}/settings - Get lesson settings
 * PUT  /splms/v1/lessons/{id}/settings - Update lesson settings
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Lesson Settings REST API Controller class.
 *
 * @since 1.0.0
 */
class SPLMS_REST_Lesson_Settings_Controller extends WP_REST_Controller {

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function __construct() {
		$this->namespace = splms_rest_namespace() . '/' . splms_rest_version();
		$this->rest_base = 'lessons';
	}

	/**
	 * Register the lesson settings routes.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)/settings',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_lesson_settings' ),
					'permission_callback' => array( $this, 'get_lesson_settings_permissions_check' ),
					'args'                => array(
						'id' => array(
							'description' => __( 'Unique identifier for the object.', 'skillpulse-lms' ),
							'type'        => 'integer',
						),
					),
				),
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_lesson_settings' ),
					'permission_callback' => array( $this, 'update_lesson_settings_permissions_check' ),
					'args'                => $this->get_item_params(),
				),
				'schema' => array( $this, 'get_lesson_settings_schema' ),
			)
		);
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
	public function get_lesson_settings_permissions_check( $request ) {
		$retval = true;

		/**
		 * Filter the settings `get_lesson_settings` permissions check.
		 *
		 * @param bool|WP_Error   $retval  Returned value.
		 * @param WP_REST_Request $request The request sent to the API.
		 *
		 * @since 1.0.0
		 */
		return apply_filters( 'splms_get_lesson_settings_permissions_check', $retval, $request );
	}

	/**
	 * Get lesson settings.
	 *
	 * Retrieves all settings for a specific lesson including content settings,
	 * access settings, completion settings, and media settings.
	 *
	 * @since 1.0.0
	 *
	 * @api {get} /splms/v1/lessons/:id/settings Get Lesson Settings
	 * @apiName GetLessonSettings
	 * @apiGroup Lessons
	 * @apiVersion 1.0.0
	 *
	 * @apiDescription Retrieve all settings for a lesson. Returns comprehensive settings
	 * including content type, duration, access control, completion criteria, and media attachments.
	 *
	 * @apiParam {Number} id Lesson unique identifier.
	 *
	 * @apiError (Error 404) lesson_not_found Lesson not found.
	 * @apiError (Error 403) rest_forbidden Insufficient permissions to access lesson settings.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_lesson_settings( $request ) {
		$lesson_id = $request->get_param( 'id' );
		$lesson    = get_post( $lesson_id );

		if ( ! $lesson || SPLMS_POST_TYPES['lesson'] !== $lesson->post_type ) {
			return new WP_Error( 'lesson_not_found', __( 'Lesson not found.', 'skillpulse-lms' ), array( 'status' => 404 ) );
		}

		$settings = SPLMS_Lessons::get_instance()->get_lesson_settings( $lesson_id );

		$settings = apply_filters( 'splms_get_lesson_settings', $settings, $lesson_id );

		return rest_ensure_response( $settings );
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
	public function update_lesson_settings_permissions_check( $request ) {
		$retval = true;

		if ( ! current_user_can( 'edit_post', $request->get_param( 'id' ) ) ) {
			$retval = new WP_Error(
				'splms_rest_cannot_edit_settings',
				__( 'Sorry, you are not allowed to edit settings for this lesson.', 'skillpulse-lms' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		/**
		 * Filter the settings `update_lesson_settings` permissions check.
		 *
		 * @param bool|WP_Error   $retval  Returned value.
		 * @param WP_REST_Request $request The request sent to the API.
		 *
		 * @since 1.0.0
		 */
		return apply_filters( 'splms_update_lesson_settings_permissions_check', $retval, $request );
	}


	/**
	 * Update lesson settings.
	 *
	 * Updates settings for a specific lesson. Only provided settings are updated;
	 * existing settings are preserved. Requires edit permissions for the lesson.
	 *
	 * @since 1.0.0
	 *
	 * @api {put} /splms/v1/lessons/:id/settings Update Lesson Settings
	 * @apiName UpdateLessonSettings
	 * @apiGroup Lessons
	 * @apiVersion 1.0.0
	 *
	 * @apiDescription Update settings for a lesson. Requires edit permissions for the lesson.
	 * Settings are merged with existing settings, so only provided settings are updated.
	 *
	 * @apiParam {Number} id Lesson unique identifier.
	 * @apiParam {Object} settings Settings object to update.
	 *
	 * @apiError (Error 404) lesson_not_found Lesson not found.
	 * @apiError (Error 400) splms_rest_empty_settings No settings provided.
	 * @apiError (Error 403) splms_rest_cannot_edit_settings Insufficient permissions to edit lesson settings.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function update_lesson_settings( $request ) {
		$lesson_id = $request->get_param( 'id' );
		$lesson    = get_post( $lesson_id );

		if ( ! $lesson || SPLMS_POST_TYPES['lesson'] !== $lesson->post_type ) {
			return new WP_Error( 'lesson_not_found', __( 'Lesson not found.', 'skillpulse-lms' ), array( 'status' => 404 ) );
		}

		$settings = $request->get_param( 'settings' );

		if ( empty( $settings ) ) {
			return new WP_Error(
				'splms_rest_empty_settings',
				__( 'No settings provided.', 'skillpulse-lms' ),
				array( 'status' => 400 )
			);
		}

		$updated_settings = SPLMS_Lessons::get_instance()->update_lesson_settings( $lesson_id, $settings );

		return rest_ensure_response(
			array(
				'success'  => true,
				'settings' => $updated_settings,
			)
		);
	}

	/**
	 * Get the query params for collections.
	 *
	 * @since 1.0.0
	 *
	 * @return array Item parameters array.
	 */
	public function get_item_params() {
		$params['settings'] = array(
			'description'       => __( 'Settings for the lesson.', 'skillpulse-lms' ),
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
	 * @return array Settings schema array.
	 */
	public function get_lesson_settings_schema() {
		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'lesson-settings',
			'type'       => 'object',
			'properties' => array(),
		);

		return $schema;
	}
}
