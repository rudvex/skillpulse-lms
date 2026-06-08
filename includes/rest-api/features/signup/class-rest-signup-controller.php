<?php
/**
 * Signup REST API Controller
 *
 * Handles REST API endpoints for signup management operations.
 * Provides endpoints for viewing, creating, activating, and deleting signups.
 *
 * @package SPLMS
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Signup REST API Controller class.
 *
 * Exposes endpoints for managing signups: list, retrieve, create, delete,
 * activate, resend activation, and fetching the signup form schema.
 *
 * @package SPLMS
 * @since 1.0.0
 *
 * @api
 * Available endpoints:
 * GET    /splms/v1/signup                - List signups
 * POST   /splms/v1/signup                - Create signup
 * GET    /splms/v1/signup/{id}           - Get signup
 * DELETE /splms/v1/signup/{id}           - Delete signup
 * POST   /splms/v1/signup/{id}/activate  - Activate signup
 * POST   /splms/v1/signup/{id}/resend    - Resend activation email
 * GET    /splms/v1/signup/form           - Get signup form schema
 */
class SPLMS_REST_Signup_Controller extends WP_REST_Controller {

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function __construct() {
		$this->namespace = splms_rest_namespace() . '/' . splms_rest_version();
		$this->rest_base = 'signup';
	}

	/**
	 * Register REST API routes.
	 *
	 * @since 1.0.0
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
					'callback'            => array( $this, 'get_signups' ),
					'permission_callback' => array( $this, 'get_signups_permissions_check' ),
					'args'                => $this->get_collection_params(),
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_signup' ),
					'permission_callback' => array( $this, 'create_signup_permissions_check' ),
					'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
				),
				'schema' => array( $this, 'get_item_schema' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_signup' ),
					'permission_callback' => array( $this, 'get_signup_permissions_check' ),
					'args'                => array(
						'context' => $this->get_context_param( array( 'default' => 'view' ) ),
					),
				),
				array(
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'delete_signup' ),
					'permission_callback' => array( $this, 'delete_signup_permissions_check' ),
				),
				'schema' => array( $this, 'get_item_schema' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)/activate',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'activate_signup' ),
					'permission_callback' => array( $this, 'activate_signup_permissions_check' ),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)/resend',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'resend_activation' ),
					'permission_callback' => array( $this, 'resend_activation_permissions_check' ),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/form',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_signup_form' ),
					'permission_callback' => array( $this, 'get_signup_form_permissions_check' ),
				),
			)
		);
	}

	/**
	 * Get signups collection.
	 *
	 * Retrieves a collection of user signups with optional filtering by status
	 * and pagination support.
	 *
	 * @since 1.0.0
	 *
	 * @api {get} /splms/v1/signup List Signups
	 * @apiName GetSignups
	 * @apiGroup Signup
	 * @apiVersion 1.0.0
	 *
	 * @apiDescription Retrieve a collection of user signups. Requires manage_options capability.
	 * Supports filtering by status (pending, activated, expired) and pagination.
	 *
	 * @apiParam {Number} [page=1] Current page of the collection.
	 * @apiParam {Number} [per_page=20] Maximum number of items to be returned.
	 * @apiParam {String} [status] Filter by signup status (pending, activated, expired).
	 *
	 * @apiError (Error 403) rest_forbidden Insufficient permissions to access signups.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response Response object.
	 */
	public function get_signups( $request ) {
		$signup_query = SPLMS_Signup_Query::get_instance();
		$signups      = $signup_query->get_signups();

		$data = array();
		foreach ( $signups as $signup ) {
			$data[] = $this->prepare_item_for_response( $signup, $request );
		}

		return rest_ensure_response( $data );
	}

	/**
	 * Get single signup.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_signup( $request ) {
		$signup_id = $request->get_param( 'id' );
		$signup    = SPLMS_Signup::get_instance()->get_signup( $signup_id );

		if ( ! $signup ) {
			return new WP_Error( 'signup_not_found', __( 'Signup not found.', 'skillpulse-lms' ), array( 'status' => 404 ) );
		}

		return rest_ensure_response( $this->prepare_item_for_response( $signup, $request ) );
	}

	/**
	 * Create signup.
	 *
	 * Creates a new user signup record. This is a public endpoint that allows
	 * users to register. An activation email is sent to the user.
	 *
	 * @since 1.0.0
	 *
	 * @api {post} /splms/v1/signup Create Signup
	 * @apiName CreateSignup
	 * @apiGroup Signup
	 * @apiVersion 1.0.0
	 *
	 * @apiDescription Create a new user signup. This is a public endpoint.
	 * Creates a signup record and sends an activation email to the user.
	 *
	 * @apiParam {String} user_name User full name.
	 * @apiParam {String} user_login Username (must be unique).
	 * @apiParam {String} user_email User email address (must be unique and valid).
	 *
	 * @apiError (Error 400) invalid_user_name Invalid or missing user name.
	 * @apiError (Error 400) invalid_user_login Invalid or duplicate username.
	 * @apiError (Error 400) invalid_user_email Invalid or duplicate email address.
	 * @apiError (Error 500) signup_creation_failed Failed to create signup.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function create_signup( $request ) {
		$params = $request->get_params();

		$signup_data = array(
			'user_name'  => sanitize_text_field( $params['user_name'] ),
			'user_login' => sanitize_user( $params['user_login'] ),
			'user_email' => sanitize_email( $params['user_email'] ),
		);

		$signup = SPLMS_Signup::get_instance()->create_signup( $signup_data );

		if ( is_wp_error( $signup ) ) {
			return $signup;
		}

		$response = $this->prepare_item_for_response( $signup, $request );
		$response = rest_ensure_response( $response );

		$response->set_status( 201 );
		$response->header( 'Location', rest_url( sprintf( '%s/%s/%d', $this->namespace, $this->rest_base, $signup->id ) ) );

		return $response;
	}

	/**
	 * Delete signup.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function delete_signup( $request ) {
		$signup_id = $request->get_param( 'id' );
		$signup    = SPLMS_Signup::get_instance()->get_signup( $signup_id );

		if ( ! $signup ) {
			return new WP_Error( 'signup_not_found', __( 'Signup not found.', 'skillpulse-lms' ), array( 'status' => 404 ) );
		}

		$deleted = SPLMS_Signup::get_instance()->delete_signup( $signup_id );

		if ( ! $deleted ) {
			return new WP_Error( 'delete_failed', __( 'Failed to delete signup.', 'skillpulse-lms' ), array( 'status' => 500 ) );
		}

		return new WP_REST_Response( null, 204 );
	}

	/**
	 * Activate signup.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function activate_signup( $request ) {
		$signup_id = $request->get_param( 'id' );
		$signup    = SPLMS_Signup::get_instance()->get_signup( $signup_id );

		if ( ! $signup ) {
			return new WP_Error( 'signup_not_found', __( 'Signup not found.', 'skillpulse-lms' ), array( 'status' => 404 ) );
		}

		$result = SPLMS_Signup::get_instance()->activate_signup( $signup_id );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return rest_ensure_response( array( 'success' => true ) );
	}

	/**
	 * Resend activation email.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function resend_activation( $request ) {
		$signup_id = $request->get_param( 'id' );
		$signup    = SPLMS_Signup::get_instance()->get_signup( $signup_id );

		if ( ! $signup ) {
			return new WP_Error( 'signup_not_found', __( 'Signup not found.', 'skillpulse-lms' ), array( 'status' => 404 ) );
		}

		$sent = SPLMS_Email_Module::get_instance()->send_activation_email( $signup_id );

		if ( ! $sent ) {
			return new WP_Error( 'send_failed', __( 'Failed to send activation email.', 'skillpulse-lms' ), array( 'status' => 500 ) );
		}

		return rest_ensure_response( array( 'success' => true ) );
	}

	/**
	 * Get signup form.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response Response object.
	 */
	public function get_signup_form( $request ) {
		$form_data = array(
			'fields'      => array(
				array(
					'name'        => 'user_name',
					'label'       => __( 'Full Name', 'skillpulse-lms' ),
					'type'        => 'text',
					'required'    => true,
					'placeholder' => __( 'Enter your full name', 'skillpulse-lms' ),
				),
				array(
					'name'        => 'user_login',
					'label'       => __( 'Username', 'skillpulse-lms' ),
					'type'        => 'text',
					'required'    => true,
					'placeholder' => __( 'Choose a username', 'skillpulse-lms' ),
				),
				array(
					'name'        => 'user_email',
					'label'       => __( 'Email Address', 'skillpulse-lms' ),
					'type'        => 'email',
					'required'    => true,
					'placeholder' => __( 'Enter your email address', 'skillpulse-lms' ),
				),
			),
			'submit_text' => __( 'Create Account', 'skillpulse-lms' ),
			'nonce_field' => wp_create_nonce( 'splms_signup_nonce' ),
		);

		return rest_ensure_response( $form_data );
	}

	/**
	 * Prepare signup for response.
	 *
	 * @since 1.0.0
	 *
	 * @param object          $signup  Signup record.
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return array Prepared signup data array.
	 */
	public function prepare_item_for_response( $signup, $request ) {
		$context = $request->get_param( 'context' );
		$context = $context ? $context : 'view';

		$data = array(
			'id'             => $signup->id,
			'user_name'      => $signup->user_name,
			'user_login'     => $signup->user_login,
			'user_email'     => $signup->user_email,
			'activation_key' => $signup->activation_key,
			'status'         => $signup->status,
			'created_at'     => mysql_to_rfc3339( $signup->created_at ),
			'updated_at'     => mysql_to_rfc3339( $signup->updated_at ),
		);

		if ( 'edit' === $context ) {
			$data['activation_link'] = SPLMS_Signup::get_instance()->get_activation_link( $signup );
		}

		return $data;
	}

	/**
	 * Get collection parameters.
	 *
	 * @since 1.0.0
	 *
	 * @return array Collection parameters array.
	 */
	public function get_collection_params() {
		$params = parent::get_collection_params();

		$params['status'] = array(
			'description' => __( 'Filter by signup status.', 'skillpulse-lms' ),
			'type'        => 'string',
			'enum'        => array( 'pending', 'activated', 'expired' ),
		);

		$params['per_page']['default'] = 20;
		$params['per_page']['maximum'] = 100;

		return $params;
	}

	/**
	 * Get endpoint arguments for item schema.
	 *
	 * @since 1.0.0
	 *
	 * @param string $method HTTP method. Default WP_REST_Server::CREATABLE.
	 *
	 * @return array Endpoint arguments array.
	 */
	public function get_endpoint_args_for_item_schema( $method = WP_REST_Server::CREATABLE ) {
		$args = array();

		if ( WP_REST_Server::CREATABLE === $method ) {
			$args['user_name'] = array(
				'description'       => __( 'User full name.', 'skillpulse-lms' ),
				'type'              => 'string',
				'required'          => true,
				'sanitize_callback' => 'sanitize_text_field',
			);

			$args['user_login'] = array(
				'description'       => __( 'Username.', 'skillpulse-lms' ),
				'type'              => 'string',
				'required'          => true,
				'sanitize_callback' => 'sanitize_user',
			);

			$args['user_email'] = array(
				'description'       => __( 'User email address.', 'skillpulse-lms' ),
				'type'              => 'string',
				'format'            => 'email',
				'required'          => true,
				'sanitize_callback' => 'sanitize_email',
			);
		}

		return $args;
	}

	/**
	 * Get item schema.
	 *
	 * @since 1.0.0
	 *
	 * @return array Item schema array.
	 */
	public function get_item_schema() {
		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'signup',
			'type'       => 'object',
			'properties' => array(
				'id'             => array(
					'description' => __( 'Unique identifier for the signup.', 'skillpulse-lms' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'user_name'      => array(
					'description' => __( 'User full name.', 'skillpulse-lms' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'user_login'     => array(
					'description' => __( 'Username.', 'skillpulse-lms' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'user_email'     => array(
					'description' => __( 'User email address.', 'skillpulse-lms' ),
					'type'        => 'string',
					'format'      => 'email',
					'context'     => array( 'view', 'edit' ),
				),
				'activation_key' => array(
					'description' => __( 'Activation key for the signup.', 'skillpulse-lms' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'status'         => array(
					'description' => __( 'Signup status.', 'skillpulse-lms' ),
					'type'        => 'string',
					'enum'        => array( 'pending', 'activated', 'expired' ),
					'context'     => array( 'view', 'edit' ),
				),
				'created_at'     => array(
					'description' => __( 'The date the signup was created.', 'skillpulse-lms' ),
					'type'        => 'string',
					'format'      => 'date-time',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'updated_at'     => array(
					'description' => __( 'The date the signup was last updated.', 'skillpulse-lms' ),
					'type'        => 'string',
					'format'      => 'date-time',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
			),
		);

		return $this->add_additional_fields_schema( $schema );
	}

	/**
	 * Check permissions for getting signups.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return bool True if user has manage_options capability, false otherwise.
	 */
	public function get_signups_permissions_check( $request ) {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Check permissions for getting single signup.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return bool True if user has manage_options capability, false otherwise.
	 */
	public function get_signup_permissions_check( $request ) {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Check permissions for creating signup.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return bool Always returns true to allow public signup.
	 */
	public function create_signup_permissions_check( $request ) {
		return true; // Allow public signup.
	}

	/**
	 * Check permissions for deleting signup.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return bool True if user has manage_options capability, false otherwise.
	 */
	public function delete_signup_permissions_check( $request ) {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Check permissions for activating signup.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return bool Always returns true to allow public activation.
	 */
	public function activate_signup_permissions_check( $request ) {
		return true; // Allow public activation.
	}

	/**
	 * Check permissions for resending activation email.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return bool True if user has manage_options capability, false otherwise.
	 */
	public function resend_activation_permissions_check( $request ) {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Check permissions for getting signup form.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return bool Always returns true to allow public access to form.
	 */
	public function get_signup_form_permissions_check( $request ) {
		return true; // Allow public access to form.
	}
}
