<?php
/**
 * REST API Controller for Memberships
 *
 * @package SkillPulse_LMS
 * @subpackage REST_API
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST API Memberships Controller Class
 *
 * @since 1.0.0
 */
class SkillPulse_LMS_REST_Memberships_Controller extends WP_REST_Controller {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->namespace = 'splms/v1';
		$this->rest_base = 'memberships';
	}

	/**
	 * Register routes.
	 *
	 * @return void
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_items' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
				),
			)
		);
	}

	/**
	 * Check permissions for getting items.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return bool|WP_Error
	 */
	public function get_items_permissions_check( $request ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundInExtendedClass
		// Require manage_options capability for accessing memberships.
		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error(
				'rest_forbidden',
				__( 'Sorry, you are not allowed to access memberships.', 'skillpulse-lms' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * Get items.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_items( $request ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundInExtendedClass
		$memberships = splms_get_available_memberships();

		$formatted = array();
		foreach ( $memberships as $membership ) {
			$formatted[] = array(
				'id'             => $membership['id'],
				'name'           => $membership['name'] . ' (' . $membership['integration_name'] . ')',
				'integration_id' => $membership['integration_id'],
			);
		}

		$response = rest_ensure_response( $formatted );

		// Add pagination headers for compatibility with usePaginatedApiFetch hook.
		// Since we're not paginating, set total to count and totalPages to 1.
		$response->header( 'X-WP-Total', count( $formatted ) );
		$response->header( 'X-WP-TotalPages', 1 );

		return $response;
	}
}
