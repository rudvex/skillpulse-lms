<?php
/**
 * Quiz Settings REST API Controller
 *
 * Handles REST API endpoints for quiz settings management.
 * Provides endpoints for getting and updating quiz-specific settings.
 *
 * @package SkillPulse_LMS
 * @since 1.0.0
 *
 * @api
 * Available endpoints:
 * GET  /splms/v1/quizzes/{id}/settings - Get quiz settings
 * PUT  /splms/v1/quizzes/{id}/settings - Update quiz settings
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Quiz Settings REST API Controller class.
 *
 * @since 1.0.0
 */
class SPLMS_REST_Quiz_Settings_Controller extends WP_REST_Controller {

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function __construct() {
		$this->namespace = splms_rest_namespace() . '/' . splms_rest_version();
		$this->rest_base = 'quizzes';
	}

	/**
	 * Register the quiz settings routes.
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
					'callback'            => array( $this, 'get_quiz_settings' ),
					'permission_callback' => array( $this, 'get_quiz_settings_permissions_check' ),
					'args'                => array(
						'id' => array(
							'description' => __( 'Unique identifier for the object.', 'skillpulse-lms' ),
							'type'        => 'integer',
						),
					),
				),
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_quiz_settings' ),
					'permission_callback' => array( $this, 'update_quiz_settings_permissions_check' ),
					'args'                => $this->get_item_params(),
				),
				'schema' => array( $this, 'get_quiz_settings_schema' ),
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
	public function get_quiz_settings_permissions_check( $request ) {
		$quiz_id = $request->get_param( 'id' );

		if ( ! current_user_can( 'edit_post', $quiz_id ) ) {
			return new WP_Error(
				'rest_forbidden',
				__( 'Sorry, you are not allowed to view quiz settings.', 'skillpulse-lms' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		$retval = true;

		/**
		 * Filter the settings `get_quiz_settings` permissions check.
		 *
		 * @param bool|WP_Error   $retval  Returned value.
		 * @param WP_REST_Request $request The request sent to the API.
		 *
		 * @since 1.0.0
		 */
		return apply_filters( 'splms_get_quiz_settings_permissions_check', $retval, $request );
	}

	/**
	 * Get quiz settings.
	 *
	 * Retrieves all settings for a specific quiz including timing, grading,
	 * attempt limits, and display options.
	 *
	 * @since 1.0.0
	 *
	 * @api {get} /splms/v1/quizzes/:id/settings Get Quiz Settings
	 * @apiName GetQuizSettings
	 * @apiGroup Quizzes
	 * @apiVersion 1.0.0
	 *
	 * @apiDescription Retrieve all settings for a quiz. Returns comprehensive settings including
	 * time limits, passing grade, attempt limits, question randomization, and result display options.
	 *
	 * @apiParam {Number} id Quiz unique identifier.
	 *
	 * @apiError (Error 404) quiz_not_found Quiz not found.
	 * @apiError (Error 403) rest_forbidden Insufficient permissions to access quiz settings.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_quiz_settings( $request ) {
		$quiz_id = $request->get_param( 'id' );
		$quiz    = get_post( $quiz_id );

		if ( ! $quiz || SPLMS_POST_TYPES['quiz'] !== $quiz->post_type ) {
			return new WP_Error( 'quiz_not_found', __( 'Quiz not found.', 'skillpulse-lms' ), array( 'status' => 404 ) );
		}

		$quizzes_instance = SPLMS_Quizzes::get_instance();
		$settings         = $quizzes_instance->get_quiz_settings( $quiz_id );

		// Return grouped structure (same as course and lesson settings).
		// Frontend JS handles grouped structure correctly.
		$settings = apply_filters( 'splms_get_quiz_settings', $settings, $quiz_id );

		// Ensure we return grouped structure (not flattened).
		// The settings should already be in grouped format from get_quiz_settings().
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
	public function update_quiz_settings_permissions_check( $request ) {
		if ( ! current_user_can( 'edit_post', $request->get_param( 'id' ) ) ) {
			return new WP_Error(
				'splms_rest_cannot_edit_settings',
				__( 'Sorry, you are not allowed to edit settings for this quiz.', 'skillpulse-lms' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		$retval = true;

		/**
		 * Filter the settings `update_quiz_settings` permissions check.
		 *
		 * @param bool|WP_Error   $retval  Returned value.
		 * @param WP_REST_Request $request The request sent to the API.
		 *
		 * @since 1.0.0
		 */
		return apply_filters( 'splms_update_quiz_settings_permissions_check', $retval, $request );
	}

	/**
	 * Update quiz settings.
	 *
	 * Updates settings for a specific quiz. Only provided settings are updated;
	 * existing settings are preserved. Requires edit permissions for the quiz.
	 *
	 * @since 1.0.0
	 *
	 * @api {put} /splms/v1/quizzes/:id/settings Update Quiz Settings
	 * @apiName UpdateQuizSettings
	 * @apiGroup Quizzes
	 * @apiVersion 1.0.0
	 *
	 * @apiDescription Update settings for a quiz. Requires edit permissions for the quiz.
	 * Settings are merged with existing settings, so only provided settings are updated.
	 *
	 * @apiParam {Number} id Quiz unique identifier.
	 * @apiParam {Object} settings Settings object to update.
	 *
	 * @apiError (Error 404) quiz_not_found Quiz not found.
	 * @apiError (Error 400) splms_rest_empty_settings No settings provided.
	 * @apiError (Error 403) splms_rest_cannot_edit_settings Insufficient permissions to edit quiz settings.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function update_quiz_settings( $request ) {
		$quiz_id = $request->get_param( 'id' );
		$quiz    = get_post( $quiz_id );

		if ( ! $quiz || SPLMS_POST_TYPES['quiz'] !== $quiz->post_type ) {
			return new WP_Error( 'quiz_not_found', __( 'Quiz not found.', 'skillpulse-lms' ), array( 'status' => 404 ) );
		}

		$settings = $request->get_param( 'settings' );

		if ( empty( $settings ) ) {
			return new WP_Error(
				'splms_rest_empty_settings',
				__( 'No settings provided.', 'skillpulse-lms' ),
				array( 'status' => 400 )
			);
		}

		$updated_settings = SPLMS_Quizzes::get_instance()->update_quiz_settings( $quiz_id, $settings );

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
			'description'       => __( 'Settings for the quiz.', 'skillpulse-lms' ),
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
	public function get_quiz_settings_schema() {
		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'quiz-settings',
			'type'       => 'object',
			'properties' => array(),
		);

		return $schema;
	}
}
