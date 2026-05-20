<?php
/**
 * Section Settings REST API Controller
 *
 * Handles REST API endpoints for section settings management.
 * Provides endpoints for getting and updating section-specific settings.
 *
 * @package SkillPulse_LMS
 * @since 1.0.0
 *
 * @api
 * Available endpoints:
 * GET  /splms/v1/section/{id}/settings - Get section settings
 * POST /splms/v1/section/{id}/settings - Update section settings
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Section Settings REST API Controller class.
 *
 * @since 1.0.0
 */
class SPLMS_REST_Section_Settings_Controller extends WP_REST_Controller {

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function __construct() {
		$this->namespace = splms_rest_namespace() . '/' . splms_rest_version();
		$this->rest_base = 'section';
	}

	/**
	 * Register the section settings routes.
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
					'callback'            => array( $this, 'get_section_settings' ),
					'permission_callback' => array( $this, 'get_section_settings_permissions_check' ),
					'args'                => array(
						'id' => array(
							'description' => __( 'Unique identifier for the object.', 'skillpulse-lms' ),
							'type'        => 'integer',
						),
					),
				),
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_section_settings' ),
					'permission_callback' => array( $this, 'update_section_settings_permissions_check' ),
					'args'                => $this->get_item_params(),
				),
				'schema' => array( $this, 'get_section_settings_schema' ),
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
	public function get_section_settings_permissions_check( $request ) {
		$retval = true;

		/**
		 * Filter the settings `get_section_settings` permissions check.
		 *
		 * @param bool|WP_Error   $retval  Returned value.
		 * @param WP_REST_Request $request The request sent to the API.
		 *
		 * @since 1.0.0
		 */
		return apply_filters( 'splms_get_section_settings_permissions_check', $retval, $request );
	}

	/**
	 * Get section settings.
	 *
	 * Retrieves all settings for a specific section including general settings,
	 * pricing settings, preview settings, and access control.
	 *
	 * @since 1.0.0
	 *
	 * @api {get} /splms/v1/section/:id/settings Get Section Settings
	 * @apiName GetSectionSettings
	 * @apiGroup Sections
	 * @apiVersion 1.0.0
	 *
	 * @apiDescription Retrieve all settings for a section. Returns comprehensive settings
	 * including general options, pricing, preview configuration, and access control.
	 *
	 * @apiParam {Number} id Section unique identifier.
	 *
	 * @apiError (Error 404) section_not_found Section not found.
	 * @apiError (Error 403) rest_forbidden Insufficient permissions to access section settings.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_section_settings( $request ) {
		$section_id = $request->get_param( 'id' );
		$section    = get_post( $section_id );

		if ( ! $section || SPLMS_POST_TYPES['section'] !== $section->post_type ) {
			return new WP_Error( 'section_not_found', __( 'Section not found.', 'skillpulse-lms' ), array( 'status' => 404 ) );
		}

		// Get section settings using the Config Loader approach like lessons.
		$sections_instance = SPLMS_Sections::get_instance();
		$settings          = $sections_instance->get_section_settings( $section_id );

		$settings = apply_filters( 'splms_get_section_settings', $settings, $section_id );

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
	public function update_section_settings_permissions_check( $request ) {
		$retval = true;

		if ( ! current_user_can( 'edit_post', $request->get_param( 'id' ) ) ) {
			$retval = new WP_Error(
				'splms_rest_cannot_edit_settings',
				__( 'Sorry, you are not allowed to edit settings for this section.', 'skillpulse-lms' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		/**
		 * Filter the settings `update_section_settings` permissions check.
		 *
		 * @param bool|WP_Error   $retval  Returned value.
		 * @param WP_REST_Request $request The request sent to the API.
		 *
		 * @since 1.0.0
		 */
		return apply_filters( 'splms_update_section_settings_permissions_check', $retval, $request );
	}


	/**
	 * Update section settings.
	 *
	 * Updates settings for a specific section. Only provided settings are updated;
	 * existing settings are preserved. Requires edit permissions for the section.
	 *
	 * @since 1.0.0
	 *
	 * @api {post} /splms/v1/section/:id/settings Update Section Settings
	 * @apiName UpdateSectionSettings
	 * @apiGroup Sections
	 * @apiVersion 1.0.0
	 *
	 * @apiDescription Update settings for a section. Requires edit permissions for the section.
	 * Settings are merged with existing settings, so only provided settings are updated.
	 *
	 * @apiParam {Number} id Section unique identifier.
	 * @apiParam {Object} settings Settings object to update.
	 *
	 * @apiError (Error 404) section_not_found Section not found.
	 * @apiError (Error 400) splms_rest_empty_settings No settings provided.
	 * @apiError (Error 403) splms_rest_cannot_edit_settings Insufficient permissions to edit section settings.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function update_section_settings( $request ) {
		$section_id = $request->get_param( 'id' );
		$section    = get_post( $section_id );

		if ( ! $section || SPLMS_POST_TYPES['section'] !== $section->post_type ) {
			return new WP_Error( 'section_not_found', __( 'Section not found.', 'skillpulse-lms' ), array( 'status' => 404 ) );
		}

		$settings = $request->get_param( 'settings' );

		if ( empty( $settings ) ) {
			return new WP_Error(
				'splms_rest_empty_settings',
				__( 'No settings provided.', 'skillpulse-lms' ),
				array( 'status' => 400 )
			);
		}

		// Update settings using the Config Loader approach like lessons.
		$sections_instance = SPLMS_Sections::get_instance();
		$success           = $sections_instance->update_section_settings( $section_id, $settings );

		if ( ! $success ) {
			return new WP_Error(
				'splms_rest_settings_update_failed',
				__( 'Failed to update section settings.', 'skillpulse-lms' ),
				array( 'status' => 500 )
			);
		}

		// Get updated settings to return to client.
		$updated_settings = $sections_instance->get_section_settings( $section_id );

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
			'description'       => __( 'Settings for the section.', 'skillpulse-lms' ),
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
	public function get_section_settings_schema() {
		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'section-settings',
			'type'       => 'object',
			'properties' => array(),
		);

		return $schema;
	}
}
