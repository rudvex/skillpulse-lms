<?php
/**
 * REST API Certificate Controller
 *
 * Handles REST API endpoints for certificate management and generation.
 * Provides endpoints for certificate templates, certificate generation, and user certificates.
 *
 * @package SkillPulse_LMS
 * @since 1.0.0
 *
 * @api
 * Available endpoints:
 * GET    /splms/v1/certificates - Get all certificate templates
 * GET    /splms/v1/certificates/{id} - Get single certificate template
 * POST   /splms/v1/certificates - Create certificate template
 * POST   /splms/v1/certificates/templates - Get certificate templates list
 * POST   /splms/v1/certificates/{id}/generate - Generate certificate for user
 * GET    /splms/v1/certificates/user/{user_id} - Get certificates for user
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST API Certificate Controller class.
 *
 * @since 1.0.0
 */
class SkillPulse_LMS_REST_Certificate_Controller extends WP_REST_Controller {

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function __construct() {
		$this->namespace = splms_rest_namespace() . '/' . splms_rest_version();
		$this->rest_base = 'certificate';
	}

	/**
	 * Register the routes for the objects of the controller.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register_routes() {
		// List certificates.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_items' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'                => $this->get_collection_params(),
					'schema'              => array( $this, 'get_public_item_schema' ),
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_item' ),
					'permission_callback' => array( $this, 'create_item_permissions_check' ),
					'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
				),
			)
		);

		// Single certificate.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_item' ),
					'permission_callback' => array( $this, 'get_item_permissions_check' ),
					'args'                => array(
						'id' => array(
							'description' => __( 'Certificate ID', 'skillpulse-lms' ),
							'type'        => 'integer',
							'required'    => true,
						),
					),
				),
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_item' ),
					'permission_callback' => array( $this, 'update_item_permissions_check' ),
					'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
				),
				array(
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'delete_item' ),
					'permission_callback' => array( $this, 'delete_item_permissions_check' ),
					'args'                => array(
						'id'    => array(
							'description' => __( 'Certificate ID', 'skillpulse-lms' ),
							'type'        => 'integer',
							'required'    => true,
						),
						'force' => array(
							'type'        => 'boolean',
							'default'     => true,
							'description' => __( 'Whether to bypass Trash and force deletion.', 'skillpulse-lms' ),
						),
					),
				),
			)
		);

		// Additional certificate-specific routes.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/templates',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_certificate_templates' ),
					'permission_callback' => array( $this, 'get_certificate_templates_permissions_check' ),
					'args'                => $this->get_collection_params(),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)/generate',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'generate_certificate' ),
					'permission_callback' => array( $this, 'generate_certificate_permissions_check' ),
					'args'                => array(
						'id'              => array(
							'description' => __( 'Unique identifier for the certificate template.', 'skillpulse-lms' ),
							'type'        => 'integer',
						),
						'user_id'         => array(
							'description' => __( 'User ID to generate certificate for.', 'skillpulse-lms' ),
							'type'        => 'integer',
							'required'    => true,
						),
						'course_id'       => array(
							'description' => __( 'Course ID the certificate is for.', 'skillpulse-lms' ),
							'type'        => 'integer',
							'required'    => true,
						),
						'completion_date' => array(
							'description' => __( 'Date when the course was completed.', 'skillpulse-lms' ),
							'type'        => 'string',
							'format'      => 'date-time',
						),
					),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/user/(?P<user_id>[\d]+)',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_user_certificates' ),
					'permission_callback' => array( $this, 'get_user_certificates_permissions_check' ),
					'args'                => array(
						'user_id'   => array(
							'description' => __( 'User ID to get certificates for.', 'skillpulse-lms' ),
							'type'        => 'integer',
						),
						'course_id' => array(
							'description' => __( 'Filter by specific course ID.', 'skillpulse-lms' ),
							'type'        => 'integer',
						),
					),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);
	}

	/**
	 * Get all certificate templates.
	 *
	 * Retrieves a collection of all available certificate templates.
	 *
	 * @since 1.0.0
	 *
	 * @api {get} /splms/v1/certificates/templates Get Certificate Templates
	 * @apiName GetCertificateTemplates
	 * @apiGroup Certificates
	 * @apiVersion 1.0.0
	 *
	 * @apiDescription Retrieve a list of all available certificate templates.
	 * This endpoint is used to fetch templates that can be assigned to courses.
	 * Requires 'edit_posts' capability.
	 *
	 *     HTTP/1.1 200 OK
	 *     {
	 *         "success": true,
	 *         "templates": [
	 *             {
	 *                 "id": 501,
	 *                 "title": "Standard Course Completion Certificate",
	 *                 "status": "publish"
	 *             }
	 *         ]
	 *     }
	 * @apiError (Error 403) Forbidden User does not have 'edit_posts' capability.
	 * @apiErrorExample {json} Error-Response:
	 *     HTTP/1.1 403 Forbidden
	 *     {
	 *         "code": "rest_forbidden",
	 *         "message": "Sorry, you are not allowed to do that.",
	 *         "data": {
	 *             "status": 403
	 *         }
	 *     }
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_certificate_templates( $request ) {
		$args = array(
			'post_type'      => SPLMS_POST_TYPES['certificate'],
			'post_status'    => 'publish',
			'posts_per_page' => $request->get_param( 'per_page' ) ? $request->get_param( 'per_page' ) : 10,
			'paged'          => $request->get_param( 'page' ) ? $request->get_param( 'page' ) : 1,
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Necessary for filtering out issued certificates.
		);

		// Add search if provided.
		if ( $request->get_param( 'search' ) ) {
			$args['s'] = $request->get_param( 'search' );
		}

		// Add orderby if provided.
		if ( $request->get_param( 'orderby' ) ) {
			$args['orderby'] = $request->get_param( 'orderby' );
		}

		// Add order if provided.
		if ( $request->get_param( 'order' ) ) {
			$args['order'] = $request->get_param( 'order' );
		}

		$query        = new WP_Query( $args );
		$certificates = array();

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$certificates[] = $this->prepare_response_for_collection(
					$this->prepare_item_for_response( get_post(), $request )
				);
			}
		}

		wp_reset_postdata();

		$response = rest_ensure_response( $certificates );

		// Add pagination headers like WordPress standard.
		$response->header( 'X-WP-Total', $query->found_posts );
		$response->header( 'X-WP-TotalPages', $query->max_num_pages );

		return $response;
	}

	/**
	 * Check if a given request has access to get certificate templates.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_Error|bool True if request has access, WP_Error object otherwise.
	 */
	public function get_certificate_templates_permissions_check( $request ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found -- $request is required by REST API callback signature.
		$retval = true;

		/**
		 * Filter the certificate templates permissions check.
		 *
		 * @param bool|WP_Error   $retval  Returned value.
		 * @param WP_REST_Request $request The request sent to the API.
		 *
		 * @since 1.0.0
		 */
		return apply_filters( 'splms_get_certificate_templates_permissions_check', $retval, $request );
	}

	/**
	 * Get items (certificates).
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_items( $request ) {
		$args = array(
			'post_type'      => SPLMS_POST_TYPES['certificate'],
			'post_status'    => 'publish',
			'posts_per_page' => $request->get_param( 'per_page' ) ? absint( $request->get_param( 'per_page' ) ) : 10,
			'paged'          => $request->get_param( 'page' ) ? absint( $request->get_param( 'page' ) ) : 1,
		);

		// Add search if provided.
		if ( $request->get_param( 'search' ) ) {
			$args['s'] = sanitize_text_field( $request->get_param( 'search' ) );
		}

		// Add orderby if provided.
		if ( $request->get_param( 'orderby' ) ) {
			$args['orderby'] = sanitize_text_field( $request->get_param( 'orderby' ) );
		}

		// Add order if provided.
		if ( $request->get_param( 'order' ) ) {
			$args['order'] = sanitize_text_field( $request->get_param( 'order' ) );
		}

		$query        = new WP_Query( $args );
		$certificates = array();

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$certificates[] = $this->prepare_response_for_collection(
					$this->prepare_item_for_response( get_post(), $request )
				);
			}
		}

		wp_reset_postdata();

		$response = rest_ensure_response( $certificates );
		$response->header( 'X-WP-Total', (int) $query->found_posts );
		$response->header( 'X-WP-TotalPages', (int) $query->max_num_pages );

		return $response;
	}

	/**
	 * Get item (single certificate).
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_item( $request ) {
		$certificate_id = (int) $request->get_param( 'id' );
		$certificate    = get_post( $certificate_id );

		if ( ! $certificate || SPLMS_POST_TYPES['certificate'] !== $certificate->post_type ) {
			return new WP_Error(
				'rest_post_invalid_id',
				__( 'Invalid certificate ID.', 'skillpulse-lms' ),
				array( 'status' => 404 )
			);
		}

		return $this->prepare_item_for_response( $certificate, $request );
	}

	/**
	 * Create item (certificate).
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function create_item( $request ) {
		try {
			// Validate required fields.
			if ( empty( $request['title'] ) ) {
				return new WP_Error(
					'missing_title',
					__( 'Certificate title is required.', 'skillpulse-lms' ),
					array( 'status' => 400 )
				);
			}

			// Validate status field.
			$valid_statuses = array( 'draft', 'publish', 'private' );
			$status         = isset( $request['status'] ) ? sanitize_text_field( $request['status'] ) : 'draft';
			if ( ! in_array( $status, $valid_statuses, true ) ) {
				return new WP_Error(
					'invalid_status',
					__( 'Invalid certificate status. Must be draft, publish, or private.', 'skillpulse-lms' ),
					array( 'status' => 400 )
				);
			}

			// Prepare post data.
			$post_data = array(
				'post_type'   => SPLMS_POST_TYPES['certificate'],
				'post_status' => $status,
				'post_title'  => sanitize_text_field( $request['title'] ),
			);

			if ( isset( $request['content'] ) ) {
				$post_data['post_content'] = wp_kses_post( $request['content'] );
			}

			// Create the post.
			$post_id = wp_insert_post( $post_data, true );

			if ( is_wp_error( $post_id ) ) {
				return new WP_Error(
					'post_creation_failed',
					__( 'Failed to create certificate post.', 'skillpulse-lms' ),
					array( 'status' => 500 )
				);
			}

			// Save certificate builder data (canvas and elements).
			if ( isset( $request['canvas'] ) || isset( $request['elements'] ) ) {
				$builder_data = array();

				if ( isset( $request['canvas'] ) ) {
					$builder_data['canvas'] = array(
						'width'      => isset( $request['canvas']['width'] ) ? max( 100, absint( $request['canvas']['width'] ) ) : 800,
						'height'     => isset( $request['canvas']['height'] ) ? max( 100, absint( $request['canvas']['height'] ) ) : 600,
						'background' => isset( $request['canvas']['background'] ) ? $this->sanitize_color( $request['canvas']['background'] ) : '#ffffff',
					);
				}

				if ( isset( $request['elements'] ) && is_array( $request['elements'] ) ) {
					$sanitized_elements       = array_map( array( $this, 'sanitize_element' ), $request['elements'] );
					$builder_data['elements'] = array_filter( $sanitized_elements ); // Remove invalid elements.
				}

				update_post_meta( $post_id, '_splms_certificate_builder_data', $builder_data );
			}

			// Prepare response.
			$certificate = get_post( $post_id );
			if ( ! $certificate ) {
				return new WP_Error(
					'post_not_found',
					__( 'Certificate was created but could not be retrieved.', 'skillpulse-lms' ),
					array( 'status' => 500 )
				);
			}

			$response = $this->prepare_item_for_response( $certificate, $request );
			$response->set_status( 201 );
			$response->header( 'Location', rest_url( sprintf( '%s/%s/%d', $this->namespace, $this->rest_base, $post_id ) ) );

			return $response;

		} catch ( Exception $e ) {
			return new WP_Error(
				'creation_exception',
				__( 'An unexpected error occurred while creating the certificate.', 'skillpulse-lms' ),
				array( 'status' => 500 )
			);
		}
	}

	/**
	 * Update item (certificate).
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function update_item( $request ) {
		try {
			$certificate_id = (int) $request->get_param( 'id' );
			$certificate    = get_post( $certificate_id );

			// Validate certificate exists and is correct type.
			if ( ! $certificate || SPLMS_POST_TYPES['certificate'] !== $certificate->post_type ) {
				return new WP_Error(
					'rest_post_invalid_id',
					__( 'Invalid certificate ID.', 'skillpulse-lms' ),
					array( 'status' => 404 )
				);
			}

			// Validate status field if provided.
			if ( isset( $request['status'] ) ) {
				$valid_statuses = array( 'draft', 'publish', 'private' );
				$status         = sanitize_text_field( $request['status'] );
				if ( ! in_array( $status, $valid_statuses, true ) ) {
					return new WP_Error(
						'invalid_status',
						__( 'Invalid certificate status. Must be draft, publish, or private.', 'skillpulse-lms' ),
						array( 'status' => 400 )
					);
				}
			}

			// Prepare update data.
			$post_data = array( 'ID' => $certificate_id );

			if ( isset( $request['title'] ) ) {
				if ( empty( trim( $request['title'] ) ) ) {
					return new WP_Error(
						'empty_title',
						__( 'Certificate title cannot be empty.', 'skillpulse-lms' ),
						array( 'status' => 400 )
					);
				}
				$post_data['post_title'] = sanitize_text_field( $request['title'] );
			}

			if ( isset( $request['content'] ) ) {
				$post_data['post_content'] = wp_kses_post( $request['content'] );
			}

			if ( isset( $request['status'] ) ) {
				$post_data['post_status'] = sanitize_text_field( $request['status'] );
			}

			// Update the post.
			$updated = wp_update_post( $post_data, true );

			if ( is_wp_error( $updated ) ) {
				return new WP_Error(
					'post_update_failed',
					__( 'Failed to update certificate.', 'skillpulse-lms' ),
					array( 'status' => 500 )
				);
			}

			// Update certificate builder data (canvas and elements).
			if ( isset( $request['canvas'] ) || isset( $request['elements'] ) ) {
				$existing_data = get_post_meta( $certificate_id, '_splms_certificate_builder_data', true );
				$builder_data  = is_array( $existing_data ) ? $existing_data : array();

				if ( isset( $request['canvas'] ) ) {
					$builder_data['canvas'] = array(
						'width'      => isset( $request['canvas']['width'] ) ? max( 100, absint( $request['canvas']['width'] ) ) : 800,
						'height'     => isset( $request['canvas']['height'] ) ? max( 100, absint( $request['canvas']['height'] ) ) : 600,
						'background' => isset( $request['canvas']['background'] ) ? $this->sanitize_color( $request['canvas']['background'] ) : '#ffffff',
					);
				}

				if ( isset( $request['elements'] ) && is_array( $request['elements'] ) ) {
					$sanitized_elements       = array_map( array( $this, 'sanitize_element' ), $request['elements'] );
					$builder_data['elements'] = array_filter( $sanitized_elements ); // Remove invalid elements.
				}

				update_post_meta( $certificate_id, '_splms_certificate_builder_data', $builder_data );
			}

			// Prepare response.
			$certificate = get_post( $certificate_id );
			if ( ! $certificate ) {
				return new WP_Error(
					'post_not_found',
					__( 'Certificate was updated but could not be retrieved.', 'skillpulse-lms' ),
					array( 'status' => 500 )
				);
			}

			return $this->prepare_item_for_response( $certificate, $request );

		} catch ( Exception $e ) {
			return new WP_Error(
				'update_exception',
				__( 'An unexpected error occurred while updating the certificate.', 'skillpulse-lms' ),
				array( 'status' => 500 )
			);
		}
	}

	/**
	 * Delete item (certificate).
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function delete_item( $request ) {
		$certificate_id = (int) $request->get_param( 'id' );
		$certificate    = get_post( $certificate_id );
		$force          = (bool) $request->get_param( 'force' );

		if ( ! $certificate || SPLMS_POST_TYPES['certificate'] !== $certificate->post_type ) {
			return new WP_Error(
				'rest_post_invalid_id',
				__( 'Invalid certificate ID.', 'skillpulse-lms' ),
				array( 'status' => 404 )
			);
		}

		if ( $force ) {
			$result = wp_delete_post( $certificate_id, true );
		} else {
			$result = wp_trash_post( $certificate_id );
		}

		if ( ! $result ) {
			return new WP_Error(
				'rest_cannot_delete',
				__( 'The certificate cannot be deleted.', 'skillpulse-lms' ),
				array( 'status' => 500 )
			);
		}

		$response = new WP_REST_Response();
		$response->set_data(
			array(
				'deleted'  => true,
				'previous' => $this->prepare_item_for_response( $certificate, $request )->get_data(),
			)
		);

		return $response;
	}

	/**
	 * Permission check for getting items.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return bool|WP_Error True if request has access, WP_Error object otherwise.
	 */
	public function get_items_permissions_check( $request ) {
		$has_access = true;

		/**
		 * Filter the certificate items permissions check.
		 *
		 * @param bool|WP_Error   $has_access Returned value.
		 * @param WP_REST_Request $request    The request sent to the API.
		 *
		 * @since 1.0.0
		 */
		$has_access = apply_filters( 'splms_rest_certificates_permissions_check', $has_access, $request );

		if ( is_wp_error( $has_access ) ) {
			return $has_access;
		}

		if ( false === $has_access ) {
			return new WP_Error(
				'rest_forbidden',
				__( 'Sorry, you are not allowed to view certificates.', 'skillpulse-lms' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * Permission check for getting single item.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return bool|WP_Error True if request has access, WP_Error object otherwise.
	 */
	public function get_item_permissions_check( $request ) {
		$has_access = true;

		/**
		 * Filter the certificate item permissions check.
		 *
		 * @param bool|WP_Error   $has_access Returned value.
		 * @param WP_REST_Request  $request    The request sent to the API.
		 *
		 * @since 1.0.0
		 */
		$has_access = apply_filters( 'splms_rest_certificate_permissions_check', $has_access, $request );

		if ( is_wp_error( $has_access ) ) {
			return $has_access;
		}

		if ( false === $has_access ) {
			return new WP_Error(
				'rest_forbidden',
				__( 'Sorry, you are not allowed to view this certificate.', 'skillpulse-lms' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * Permission check for creating item.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return bool|WP_Error True if request has access, WP_Error object otherwise.
	 */
	public function create_item_permissions_check( $request ) {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return new WP_Error(
				'rest_forbidden',
				__( 'Sorry, you are not allowed to perform this action.', 'skillpulse-lms' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * Permission check for updating item.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return bool|WP_Error True if request has access, WP_Error object otherwise.
	 */
	public function update_item_permissions_check( $request ) {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return new WP_Error(
				'rest_forbidden',
				__( 'Sorry, you are not allowed to perform this action.', 'skillpulse-lms' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * Permission check for deleting item.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return bool|WP_Error True if request has access, WP_Error object otherwise.
	 */
	public function delete_item_permissions_check( $request ) {
		if ( ! current_user_can( 'delete_posts' ) ) {
			return new WP_Error(
				'rest_forbidden',
				__( 'Sorry, you are not allowed to perform this action.', 'skillpulse-lms' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * Prepare item for response.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Post         $post    Certificate post object.
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response object.
	 */
	public function prepare_item_for_response( $post, $request ) {
		$data = $this->prepare_certificate_template_for_response( $post );

		// Use filter_response_by_context to filter based on schema context.
		$data = $this->filter_response_by_context( $data, $request->get_param( 'context' ) ? $request->get_param( 'context' ) : 'view' );

		$response = rest_ensure_response( $data );
		$response->add_links( $this->prepare_links( $post ) );

		return $response;
	}

	/**
	 * Prepare links for the response.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Post $post Certificate post object.
	 * @return array Links for the given post.
	 */
	protected function prepare_links( $post ) {
		$base = sprintf( '%s/%s', $this->namespace, $this->rest_base );

		$links = array(
			'self'       => array(
				array(
					'href' => rest_url( trailingslashit( $base ) . $post->ID ),
				),
			),
			'collection' => array(
				array(
					'href' => rest_url( $base ),
				),
			),
		);

		return $links;
	}

	/**
	 * Get the query params for collections.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_collection_params() {
		return array(
			'page'     => array(
				'description' => __( 'Current page of the collection.', 'skillpulse-lms' ),
				'type'        => 'integer',
				'default'     => 1,
				'minimum'     => 1,
			),
			'per_page' => array(
				'description' => __( 'Maximum number of items to be returned in result set.', 'skillpulse-lms' ),
				'type'        => 'integer',
				'default'     => 10,
				'minimum'     => 1,
				'maximum'     => 100,
			),
			'search'   => array(
				'description' => __( 'Limit results to those matching a string.', 'skillpulse-lms' ),
				'type'        => 'string',
			),
			'orderby'  => array(
				'description' => __( 'Sort collection by object attribute.', 'skillpulse-lms' ),
				'type'        => 'string',
				'default'     => 'date',
				'enum'        => array(
					'date',
					'id',
					'include',
					'title',
					'slug',
					'modified',
					'menu_order',
				),
			),
			'order'    => array(
				'description' => __( 'Order sort attribute ascending or descending.', 'skillpulse-lms' ),
				'type'        => 'string',
				'default'     => 'desc',
				'enum'        => array( 'asc', 'desc' ),
			),
		);
	}

	/**
	 * Retrieves the certificate schema, conforming to JSON Schema.
	 *
	 * @since 1.0.0
	 *
	 * @return array Item schema data.
	 */
	public function get_item_schema() {
		return $this->get_public_item_schema();
	}

	/**
	 * Retrieves the public certificate schema, conforming to JSON Schema.
	 *
	 * @since 1.0.0
	 *
	 * @return array Public item schema data.
	 */
	public function get_public_item_schema() {
		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'certificate',
			'type'       => 'object',
			'properties' => array(
				'id'           => array(
					'description' => __( 'Unique identifier for the certificate.', 'skillpulse-lms' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'title'        => array(
					'description' => __( 'The title for the certificate.', 'skillpulse-lms' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'content'      => array(
					'description' => __( 'The content for the certificate.', 'skillpulse-lms' ),
					'type'        => 'object',
					'context'     => array( 'view', 'edit' ),
					'properties'  => array(
						'raw'       => array(
							'description' => __( 'Content for the certificate, as it exists in the database.', 'skillpulse-lms' ),
							'type'        => 'string',
							'context'     => array( 'edit' ),
						),
						'rendered'  => array(
							'description' => __( 'HTML content for the certificate, transformed for display.', 'skillpulse-lms' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
							'readonly'    => true,
						),
						'protected' => array(
							'description' => __( 'Whether the content is protected with a password.', 'skillpulse-lms' ),
							'type'        => 'boolean',
							'context'     => array( 'view', 'edit' ),
							'readonly'    => true,
						),
					),
				),
				'status'       => array(
					'description' => __( 'A named status for the certificate.', 'skillpulse-lms' ),
					'type'        => 'string',
					'enum'        => array( 'publish', 'draft', 'private' ),
					'context'     => array( 'view', 'edit' ),
				),
				'date'         => array(
					'description' => __( "The date the certificate was published, in the site's timezone.", 'skillpulse-lms' ),
					'type'        => 'string',
					'format'      => 'date-time',
					'context'     => array( 'view', 'edit' ),
				),
				'date_gmt'     => array(
					'description' => __( 'The date the certificate was published, as GMT.', 'skillpulse-lms' ),
					'type'        => 'string',
					'format'      => 'date-time',
					'context'     => array( 'view', 'edit' ),
				),
				'modified'     => array(
					'description' => __( "The date the certificate was last modified, in the site's timezone.", 'skillpulse-lms' ),
					'type'        => 'string',
					'format'      => 'date-time',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'modified_gmt' => array(
					'description' => __( 'The date the certificate was last modified, as GMT.', 'skillpulse-lms' ),
					'type'        => 'string',
					'format'      => 'date-time',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'is_default'   => array(
					'description' => __( 'Whether this is the default certificate template.', 'skillpulse-lms' ),
					'type'        => 'boolean',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'canvas'       => array(
					'description' => __( 'Canvas configuration for the certificate builder.', 'skillpulse-lms' ),
					'type'        => 'object',
					'context'     => array( 'view', 'edit' ),
					'properties'  => array(
						'width'      => array(
							'type' => 'integer',
						),
						'height'     => array(
							'type' => 'integer',
						),
						'background' => array(
							'type' => 'string',
						),
					),
				),
				'elements'     => array(
					'description' => __( 'Certificate builder elements.', 'skillpulse-lms' ),
					'type'        => 'array',
					'context'     => array( 'view', 'edit' ),
					'items'       => array(
						'type'       => 'object',
						'properties' => array(
							'id'     => array(
								'type' => 'string',
							),
							'type'   => array(
								'type' => 'string',
							),
							'x'      => array(
								'type' => 'number',
							),
							'y'      => array(
								'type' => 'number',
							),
							'width'  => array(
								'type' => 'number',
							),
							'height' => array(
								'type' => 'number',
							),
						),
					),
				),
			),
		);

		return $this->add_additional_fields_schema( $schema );
	}

	/**
	 * Generate a certificate for a user.
	 *
	 * Creates a certificate for a specific user and course combination using a certificate template.
	 * Returns the certificate ID, token, and URL for verification.
	 *
	 * @since 1.0.0
	 *
	 * @api {post} /splms/v1/certificates/:id/generate Generate Certificate
	 * @apiName GenerateCertificate
	 * @apiGroup Certificates
	 * @apiVersion 1.0.0
	 *
	 * @apiDescription Generate a certificate for a user upon course completion. This endpoint
	 * creates a certificate post and stores metadata including user ID, course ID, and completion date.
	 * Requires 'manage_options' capability.
	 *
	 * @apiParam {Number} id Certificate template ID.
	 * @apiParam {Number} user_id User ID to generate certificate for.
	 * @apiParam {Number} course_id Course ID the certificate is for.
	 * @apiParam {String} [completion_date] Date when the course was completed (ISO 8601 format).
	 *
	 *     HTTP/1.1 200 OK
	 *     {
	 *         "success": true,
	 *         "certificate_id": 1001,
	 *         "certificate_token": "abc123def456",
	 *         "certificate_url": "http://example.com/certificate/abc123def456",
	 *         "user": {
	 *             "id": 1,
	 *             "name": "John Doe",
	 *             "email": "john@example.com"
	 *         },
	 *         "course": {
	 *             "id": 123,
	 *             "title": "Introduction to REST API"
	 *         },
	 *         "completion_date": "2023-01-01 12:00:00"
	 *     }
	 * @apiError (Error 404) CertificateNotFound Certificate template not found.
	 * @apiError (Error 404) UserNotFound User not found.
	 * @apiError (Error 404) CourseNotFound Course not found.
	 * @apiError (Error 409) CertificateExists Certificate already exists for this user and course.
	 * @apiError (Error 403) Forbidden User does not have 'manage_options' capability.
	 * @apiErrorExample {json} Error-Response (Certificate Exists):
	 *     HTTP/1.1 409 Conflict
	 *     {
	 *         "code": "certificate_exists",
	 *         "message": "Certificate already exists for this user and course.",
	 *         "data": {
	 *             "status": 409
	 *         }
	 *     }
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function generate_certificate( $request ) {
		$template_id           = absint( $request->get_param( 'id' ) );
		$user_id               = absint( $request->get_param( 'user_id' ) );
		$course_id             = absint( $request->get_param( 'course_id' ) );
		$completion_date_param = sanitize_text_field( $request->get_param( 'completion_date' ) );
		$completion_date       = $completion_date_param ? $completion_date_param : current_time( 'mysql' );

		// Verify certificate template exists.
		$template = get_post( $template_id );
		if ( ! $template || SPLMS_POST_TYPES['certificate'] !== $template->post_type ) {
			return new WP_Error( 'certificate_not_found', __( 'Certificate template not found.', 'skillpulse-lms' ), array( 'status' => 404 ) );
		}

		// Verify user exists.
		$user = get_user_by( 'id', $user_id );
		if ( ! $user ) {
			return new WP_Error( 'user_not_found', __( 'User not found.', 'skillpulse-lms' ), array( 'status' => 404 ) );
		}

		// Verify course exists.
		$course = get_post( $course_id );
		if ( ! $course || SPLMS_POST_TYPES['course'] !== $course->post_type ) {
			return new WP_Error( 'course_not_found', __( 'Course not found.', 'skillpulse-lms' ), array( 'status' => 404 ) );
		}

		// Check if course is completed before allowing certificate generation.
		$enrollments_query = SkillPulse_LMS_Enrollments_Query::get_instance();
		$enrollment        = $enrollments_query->get_enrollment( $user_id, $course_id );
		if ( ! $enrollment || 'completed' !== $enrollment->status ) {
			return new WP_Error( 'course_not_completed', __( 'Course must be completed before generating a certificate.', 'skillpulse-lms' ), array( 'status' => 400 ) );
		}

		// Check if certificate already exists for this user/course combination (check both post and user meta).
		$existing_certificate = $this->get_user_certificate_for_course( $user_id, $course_id );
		if ( $existing_certificate ) {
			return new WP_Error( 'certificate_exists', __( 'Certificate already exists for this user and course.', 'skillpulse-lms' ), array( 'status' => 409 ) );
		}

		// Also check user meta to prevent duplicates.
		$certificates = SkillPulse_LMS_Certificates::get_instance();
		if ( $certificates->user_has_certificate( $user_id, $course_id ) ) {
			return new WP_Error( 'certificate_exists', __( 'Certificate already exists for this user and course.', 'skillpulse-lms' ), array( 'status' => 409 ) );
		}

		// Use unified award_certificate method which creates certificate post with all meta fields.
		$certificate_post_id = $certificates->award_certificate( $user_id, $course_id, $template_id, $completion_date );

		if ( ! $certificate_post_id ) {
			return new WP_Error( 'certificate_generation_failed', __( 'Failed to generate certificate.', 'skillpulse-lms' ), array( 'status' => 500 ) );
		}

		// Get certificate token for URL.
		$certificate_token = get_post_meta( $certificate_post_id, '_splms_certificate_token', true );
		$certificate_url   = $this->get_certificate_url( $certificate_token );

		return rest_ensure_response(
			array(
				'success'           => true,
				'certificate_id'    => $certificate_post_id,
				'certificate_token' => $certificate_token,
				'certificate_url'   => $certificate_url,
				'user'              => array(
					'id'    => $user->ID,
					'name'  => $user->display_name,
					'email' => $user->user_email,
				),
				'course'            => array(
					'id'    => $course->ID,
					'title' => $course->post_title,
				),
				'completion_date'   => $completion_date,
			)
		);
	}

	/**
	 * Check if a given request has access to generate certificates.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_Error|bool True if request has access, WP_Error object otherwise.
	 */
	public function generate_certificate_permissions_check( $request ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found -- $request is required by REST API callback signature.
		$retval = true;

		// Check if user can manage certificates.
		if ( ! current_user_can( 'manage_options' ) ) {
			$retval = new WP_Error(
				'splms_rest_cannot_generate_certificate',
				__( 'Sorry, you are not allowed to generate certificates.', 'skillpulse-lms' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		/**
		 * Filter the certificate generation permissions check.
		 *
		 * @param bool|WP_Error   $retval  Returned value.
		 * @param WP_REST_Request $request The request sent to the API.
		 *
		 * @since 1.0.0
		 */
		return apply_filters( 'splms_generate_certificate_permissions_check', $retval, $request );
	}

	/**
	 * Get certificates for a specific user.
	 *
	 * Retrieves all certificates earned by a user, optionally filtered by course ID.
	 * Users can view their own certificates, and admins can view any user's certificates.
	 *
	 * @since 1.0.0
	 *
	 * @api {get} /splms/v1/certificates/user/:user_id Get User Certificates
	 * @apiName GetUserCertificates
	 * @apiGroup Certificates
	 * @apiVersion 1.0.0
	 *
	 * @apiDescription Retrieve all certificates earned by a specific user. This endpoint
	 * returns certificates with their associated course information and completion dates.
	 * Users can view their own certificates, and administrators can view any user's certificates.
	 *
	 * @apiParam {Number} user_id User ID to get certificates for.
	 * @apiParam {Number} [course_id] Filter by specific course ID.
	 *
	 *     HTTP/1.1 200 OK
	 *     {
	 *         "success": true,
	 *         "user_id": 1,
	 *         "certificates": [
	 *             {
	 *                 "id": 1001,
	 *                 "course_id": 123,
	 *                 "course_title": "Introduction to REST API",
	 *                 "template_id": 501,
	 *                 "template_title": "Standard Course Completion Certificate",
	 *                 "completion_date": "2023-01-01 12:00:00",
	 *                 "generated_date": "2023-01-01 12:05:00",
	 *                 "status": "issued",
	 *                 "certificate_url": "http://example.com/certificate/abc123def456"
	 *             }
	 *         ]
	 *     }
	 * @apiError (Error 404) UserNotFound User not found.
	 * @apiError (Error 403) Forbidden User does not have permission to view these certificates.
	 * @apiErrorExample {json} Error-Response:
	 *     HTTP/1.1 404 Not Found
	 *     {
	 *         "code": "user_not_found",
	 *         "message": "User not found.",
	 *         "data": {
	 *             "status": 404
	 *         }
	 *     }
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_user_certificates( $request ) {
		$user_id   = absint( $request->get_param( 'user_id' ) );
		$course_id = absint( $request->get_param( 'course_id' ) );

		// Verify user exists.
		$user = get_user_by( 'id', $user_id );
		if ( ! $user ) {
			return new WP_Error( 'user_not_found', __( 'User not found.', 'skillpulse-lms' ), array( 'status' => 404 ) );
		}

		$certificates = $this->get_user_certificates_data( $user_id, $course_id );

		return rest_ensure_response(
			array(
				'user_id'      => $user_id,
				'certificates' => $certificates,
			)
		);
	}

	/**
	 * Check if a given request has access to get user certificates.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_Error|bool
	 */
	public function get_user_certificates_permissions_check( $request ) {
		$user_id         = absint( $request->get_param( 'user_id' ) );
		$current_user_id = get_current_user_id();

		// Users can view their own certificates, admins can view any.
		if ( $user_id !== $current_user_id && ! current_user_can( 'manage_options' ) ) {
			return new WP_Error(
				'splms_rest_cannot_view_certificates',
				__( 'Sorry, you are not allowed to view these certificates.', 'skillpulse-lms' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		/**
		 * Filter the user certificates permissions check.
		 *
		 * @param bool|WP_Error   $retval  Returned value.
		 * @param WP_REST_Request $request The request sent to the API.
		 *
		 * @since 1.0.0
		 */
		return apply_filters( 'splms_get_user_certificates_permissions_check', true, $request );
	}

	/**
	 * Prepare certificate template for response.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Post $post Certificate template post object.
	 *
	 * @return array Certificate template data.
	 */
	private function prepare_certificate_template_for_response( $post ) {
		$is_default   = get_post_meta( $post->ID, '_splms_certificate_is_default', true );
		$builder_data = get_post_meta( $post->ID, '_splms_certificate_builder_data', true );
		$data         = array(
			'id'             => $post->ID,
			'date'           => $this->prepare_date_response( $post->post_date_gmt, $post->post_date ),
			'date_gmt'       => $this->prepare_date_response( $post->post_date_gmt ),
			'guid'           => array(
				'rendered' => get_the_guid( $post->ID ),
			),
			'modified'       => $this->prepare_date_response( $post->post_modified_gmt, $post->post_modified ),
			'modified_gmt'   => $this->prepare_date_response( $post->post_modified_gmt ),
			'password'       => $post->post_password,
			'slug'           => $post->post_name,
			'status'         => $post->post_status,
			'type'           => $post->post_type,
			'link'           => get_permalink( $post->ID ),
			'title'          => get_the_title( $post->ID ),
			'content'        => array(
				'rendered'  => apply_filters( 'the_content', $post->post_content ),
				'protected' => (bool) $post->post_password,
			),
			'excerpt'        => array(
				'rendered'  => get_the_excerpt( $post ),
				'protected' => (bool) $post->post_password,
			),
			'author'         => (int) $post->post_author,
			'featured_media' => (int) get_post_thumbnail_id( $post->ID ),
			'parent'         => (int) $post->post_parent,
			'menu_order'     => (int) $post->menu_order,
			'comment_status' => $post->comment_status,
			'ping_status'    => $post->ping_status,
			'sticky'         => is_sticky( $post->ID ),
			'template'       => get_page_template_slug( $post->ID ),
			'format'         => get_post_format( $post->ID ),
			'meta'           => $this->prepare_meta_for_response( $post ),
			'categories'     => $this->prepare_terms_for_response( $post->ID, 'category' ),
			'tags'           => $this->prepare_terms_for_response( $post->ID, 'post_tag' ),
			'featured_image' => $this->prepare_featured_image_for_response( $post->ID ),
			'is_default'     => (bool) ( '1' === $is_default ),
		);

		// Add certificate builder data if available.
		if ( is_array( $builder_data ) ) {
			$data['canvas']   = isset( $builder_data['canvas'] ) ? $builder_data['canvas'] : array(
				'width'      => 800,
				'height'     => 600,
				'background' => '#ffffff',
			);
			$data['elements'] = isset( $builder_data['elements'] ) ? $builder_data['elements'] : array();
		} else {
			// Default values if no builder data exists.
			$data['canvas']   = array(
				'width'      => 800,
				'height'     => 600,
				'background' => '#ffffff',
			);
			$data['elements'] = array();
		}

		$current_user_id = get_current_user_id();
		// If user is logged in, add certificate URL if they have earned this certificate.
		if ( $current_user_id ) {
			$data['user_certificate_url'] = $this->get_user_certificate_url( $current_user_id, $post->ID );
		}

		return $data;
	}

	/**
	 * Get user certificate URL.
	 *
	 * @since 1.0.0
	 *
	 * @param int $user_id       User ID.
	 * @param int $certificate_id Certificate ID.
	 *
	 * @return string Certificate URL.
	 */
	private function get_user_certificate_url( $user_id, $certificate_id ) {
		$certificate_token = get_post_meta( $certificate_id, '_splms_certificate_token', true );
		return home_url( '/certificate/' . $certificate_token );
	}

	/**
	 * Prepare date response.
	 *
	 * @param string $date_gmt GMT date.
	 * @param string $date     Local date.
	 *
	 * @since 1.0.0
	 *
	 * @return string|null ISO 8601 date or null.
	 */
	protected function prepare_date_response( $date_gmt, $date = null ) {
		if ( '0000-00-00 00:00:00' === $date_gmt ) {
			return null;
		}

		if ( isset( $date ) ) {
			return mysql2date( 'c', $date );
		}

		return mysql2date( 'c', $date_gmt );
	}

	/**
	 * Prepare meta for response.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Post $post Post object.
	 *
	 * @return array Meta data.
	 */
	private function prepare_meta_for_response( $post ) {
		$meta          = get_post_meta( $post->ID );
		$prepared_meta = array();

		foreach ( $meta as $key => $value ) {
			// Only include our certificate meta fields.
			if ( strpos( $key, '_splms_certificate_' ) === 0 ) {
				$prepared_meta[ $key ] = maybe_unserialize( $value[0] );
			}
		}

		return $prepared_meta;
	}

	/**
	 * Prepare terms for response.
	 *
	 * @since 1.0.0
	 *
	 * @param int    $post_id  Post ID.
	 * @param string $taxonomy Taxonomy name.
	 *
	 * @return array Terms data.
	 */
	private function prepare_terms_for_response( $post_id, $taxonomy ) {
		$terms = get_the_terms( $post_id, $taxonomy );

		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			return array();
		}

		$prepared_terms = array();
		foreach ( $terms as $term ) {
			$prepared_terms[] = array(
				'id'          => $term->term_id,
				'count'       => $term->count,
				'description' => $term->description,
				'link'        => get_term_link( $term ),
				'name'        => $term->name,
				'slug'        => $term->slug,
				'taxonomy'    => $term->taxonomy,
			);
		}

		return $prepared_terms;
	}

	/**
	 * Prepare featured image for response.
	 *
	 * @since 1.0.0
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return array|null Featured image data or null.
	 */
	private function prepare_featured_image_for_response( $post_id ) {
		$featured_image_id = get_post_thumbnail_id( $post_id );

		if ( ! $featured_image_id ) {
			return null;
		}

		$attachment = get_post( $featured_image_id );
		if ( ! $attachment ) {
			return null;
		}

		return array(
			'id'             => $attachment->ID,
			'date'           => $this->prepare_date_response( $attachment->post_date_gmt, $attachment->post_date ),
			'date_gmt'       => $this->prepare_date_response( $attachment->post_date_gmt ),
			'guid'           => array(
				'rendered' => get_the_guid( $attachment->ID ),
			),
			'modified'       => $this->prepare_date_response( $attachment->post_modified_gmt, $attachment->post_modified ),
			'modified_gmt'   => $this->prepare_date_response( $attachment->post_modified_gmt ),
			'slug'           => $attachment->post_name,
			'status'         => $attachment->post_status,
			'type'           => $attachment->post_type,
			'link'           => get_permalink( $attachment->ID ),
			'title'          => array(
				'rendered' => get_the_title( $attachment->ID ),
			),
			'author'         => (int) $attachment->post_author,
			'comment_status' => $attachment->comment_status,
			'ping_status'    => $attachment->ping_status,
			'template'       => get_page_template_slug( $attachment->ID ),
			'meta'           => $this->prepare_meta_for_response( $attachment ),
			'description'    => array(
				'rendered' => apply_filters( 'the_content', $attachment->post_content ),
			),
			'caption'        => array(
				'rendered' => apply_filters( 'the_content', $attachment->post_excerpt ),
			),
			'alt_text'       => get_post_meta( $attachment->ID, '_wp_attachment_image_alt', true ),
			'media_type'     => wp_attachment_is_image( $attachment->ID ) ? 'image' : 'file',
			'mime_type'      => $attachment->post_mime_type,
			'media_details'  => wp_get_attachment_metadata( $attachment->ID ),
			'source_url'     => wp_get_attachment_url( $attachment->ID ),
		);
	}

	/**
	 * Get user certificate for specific course.
	 *
	 * @since 1.0.0
	 *
	 * @param int $user_id   User ID.
	 * @param int $course_id Course ID.
	 *
	 * @return WP_Post|null Certificate post or null if not found.
	 */
	private function get_user_certificate_for_course( $user_id, $course_id ) {
		$args = array(
			'post_type'      => SPLMS_POST_TYPES['certificate'],
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Necessary for filtering certificates by user.
			'meta_query'     => array(
				'relation' => 'AND',
				array(
					'key'     => '_splms_certificate_user_id',
					'value'   => $user_id,
					'compare' => '=',
				),
				array(
					'key'     => '_splms_certificate_course_id',
					'value'   => $course_id,
					'compare' => '=',
				),
				array(
					'key'     => '_splms_certificate_status',
					'value'   => 'issued',
					'compare' => '=',
				),
			),
		);

		$query       = new WP_Query( $args );
		$certificate = $query->have_posts() ? $query->posts[0] : null;

		wp_reset_postdata();

		return $certificate;
	}

	/**
	 * Generate certificate token for verification.
	 *
	 * @since 1.0.0
	 *
	 * @param int $certificate_id Certificate ID.
	 *
	 * @return string Certificate token.
	 */
	private function generate_certificate_token( $certificate_id ) {
		$token = wp_generate_password( 32, false );

		// Store token in certificate meta.
		update_post_meta( $certificate_id, '_splms_certificate_token', $token );

		return $token;
	}

	/**
	 * Get certificate URL.
	 *
	 * @since 1.0.0
	 *
	 * @param string $token Certificate token.
	 *
	 * @return string Certificate URL.
	 */
	private function get_certificate_url( $token ) {
		return home_url( '/certificate/' . $token );
	}

	/**
	 * Sanitize color value.
	 *
	 * @since 1.0.0
	 *
	 * @param string $color Color value to sanitize.
	 *
	 * @return string Sanitized color value.
	 */
	private function sanitize_color( $color ) {
		if ( empty( $color ) || ! is_string( $color ) ) {
			return '#ffffff';
		}

		// Remove any whitespace.
		$color = trim( $color );

		// Check if it's a valid hex color (3 or 6 characters after #).
		if ( preg_match( '/^#([a-fA-F0-9]{3}){1,2}$/', $color ) ) {
			return $color;
		}

		// Check for rgb/rgba format.
		if ( preg_match( '/^rgba?\(\s*\d+\s*,\s*\d+\s*,\s*\d+\s*(,\s*[\d.]+\s*)?\)$/', $color ) ) {
			return sanitize_text_field( $color );
		}

		// Check for named colors (basic validation).
		$named_colors = array(
			'black',
			'white',
			'red',
			'green',
			'blue',
			'yellow',
			'cyan',
			'magenta',
			'silver',
			'gray',
			'maroon',
			'olive',
			'lime',
			'aqua',
			'teal',
			'navy',
			'fuchsia',
			'purple',
		);
		if ( in_array( strtolower( $color ), $named_colors, true ) ) {
			return strtolower( $color );
		}

		// Default fallback.
		return '#ffffff';
	}

	/**
	 * Sanitize certificate element data.
	 *
	 * @since 1.0.0
	 *
	 * @param array $element Element data.
	 *
	 * @return array Sanitized element data.
	 */
	private function sanitize_element( $element ) {
		if ( ! is_array( $element ) ) {
			return array();
		}

		$sanitized = array();

		// Required properties.
		if ( isset( $element['id'] ) ) {
			$sanitized['id'] = sanitize_text_field( $element['id'] );
		}

		if ( isset( $element['type'] ) ) {
			$sanitized['type'] = sanitize_text_field( $element['type'] );
		}

		if ( isset( $element['x'] ) ) {
			$sanitized['x'] = (float) $element['x'];
		}

		if ( isset( $element['y'] ) ) {
			$sanitized['y'] = (float) $element['y'];
		}

		if ( isset( $element['width'] ) ) {
			$sanitized['width'] = max( 1, (float) $element['width'] );
		}

		if ( isset( $element['height'] ) ) {
			$sanitized['height'] = max( 1, (float) $element['height'] );
		}

		// Optional common properties.
		if ( isset( $element['rotation'] ) ) {
			$sanitized['rotation'] = max( -180, min( 180, (float) $element['rotation'] ) );
		}

		if ( isset( $element['opacity'] ) ) {
			$sanitized['opacity'] = max( 0, min( 1, (float) $element['opacity'] ) );
		}

		if ( isset( $element['locked'] ) ) {
			$sanitized['locked'] = (bool) $element['locked'];
		}

		// Content properties.
		if ( isset( $element['content'] ) ) {
			$sanitized['content'] = sanitize_textarea_field( $element['content'] );
		}

		if ( isset( $element['src'] ) ) {
			$sanitized['src'] = esc_url_raw( $element['src'] );
		}

		if ( isset( $element['alt'] ) ) {
			$sanitized['alt'] = sanitize_text_field( $element['alt'] );
		}

		// Style object handling.
		if ( isset( $element['style'] ) && is_array( $element['style'] ) ) {
			$sanitized['style'] = $this->sanitize_element_style( $element['style'] );
		}

		// Handle remaining properties.
		foreach ( $element as $key => $value ) {
			$processed_keys = array( 'id', 'type', 'x', 'y', 'width', 'height', 'rotation', 'opacity', 'locked', 'content', 'src', 'alt', 'style' );
			if ( ! in_array( $key, $processed_keys, true ) ) {
				// Validate key is safe without changing case (preserve camelCase).
				$safe_key = $this->validate_element_key( $key );
				if ( $safe_key ) {
					if ( is_string( $value ) ) {
						$sanitized[ $safe_key ] = sanitize_text_field( $value );
					} elseif ( is_numeric( $value ) ) {
						$sanitized[ $safe_key ] = (float) $value;
					} elseif ( is_bool( $value ) ) {
						$sanitized[ $safe_key ] = (bool) $value;
					} elseif ( is_array( $value ) ) {
						// Recursively sanitize nested arrays.
						$sanitized[ $safe_key ] = $this->sanitize_array_recursive( $value );
					}
				}
			}
		}
		return $sanitized;
	}

	/**
	 * Sanitize element style properties.
	 *
	 * @since 1.0.0
	 *
	 * @param array $style Style properties.
	 *
	 * @return array Sanitized style properties.
	 */
	private function sanitize_element_style( $style ) {
		if ( ! is_array( $style ) ) {
			return array();
		}

		$sanitized_style = array();

		// Common style properties.
		if ( isset( $style['fontSize'] ) ) {
			$sanitized_style['fontSize'] = max( 8, min( 72, (int) $style['fontSize'] ) );
		}

		if ( isset( $style['fontFamily'] ) ) {
			$sanitized_style['fontFamily'] = sanitize_text_field( $style['fontFamily'] );
		}

		if ( isset( $style['color'] ) ) {
			$sanitized_style['color'] = $this->sanitize_color( $style['color'] );
		}

		if ( isset( $style['backgroundColor'] ) ) {
			$sanitized_style['backgroundColor'] = $this->sanitize_color( $style['backgroundColor'] );
		}

		if ( isset( $style['textAlign'] ) ) {
			$valid_aligns                 = array( 'left', 'center', 'right', 'justify' );
			$sanitized_style['textAlign'] = in_array( $style['textAlign'], $valid_aligns, true ) ? $style['textAlign'] : 'left';
		}

		if ( isset( $style['fontWeight'] ) ) {
			$valid_weights                 = array( 'normal', 'bold', '100', '200', '300', '400', '500', '600', '700', '800', '900' );
			$sanitized_style['fontWeight'] = in_array( $style['fontWeight'], $valid_weights, true ) ? $style['fontWeight'] : 'normal';
		}

		if ( isset( $style['fontStyle'] ) ) {
			$valid_styles                 = array( 'normal', 'italic', 'oblique' );
			$sanitized_style['fontStyle'] = in_array( $style['fontStyle'], $valid_styles, true ) ? $style['fontStyle'] : 'normal';
		}

		// Sanitize remaining style properties.
		foreach ( $style as $key => $value ) {
			$processed_keys = array( 'fontSize', 'fontFamily', 'color', 'backgroundColor', 'textAlign', 'fontWeight', 'fontStyle' );
			if ( ! in_array( $key, $processed_keys, true ) ) {
				// Validate key is safe without changing case (preserve camelCase).
				$safe_key = $this->validate_element_key( $key );
				if ( $safe_key ) {
					if ( is_string( $value ) ) {
						$sanitized_style[ $safe_key ] = sanitize_text_field( $value );
					} elseif ( is_numeric( $value ) ) {
						$sanitized_style[ $safe_key ] = (float) $value;
					}
				}
			}
		}

		return $sanitized_style;
	}

	/**
	 * Recursively sanitize array data.
	 *
	 * @param array $array_val Array to sanitize.
	 *
	 * @since 1.0.0
	 *
	 * @return array Sanitized array.
	 */
	private function sanitize_array_recursive( $array_val ) {
		if ( ! is_array( $array_val ) ) {
			return array();
		}

		foreach ( $array_val as $key => $value ) {
			if ( is_string( $value ) ) {
				$array_val[ $key ] = sanitize_text_field( $value );
			} elseif ( is_array( $value ) ) {
				$array_val[ $key ] = $this->sanitize_array_recursive( $value );
			}
		}

		return $array_val;
	}

	/**
	 * Get user certificates data.
	 *
	 * @since 1.0.0
	 *
	 * @param int      $user_id   User ID.
	 * @param int|null $course_id Optional course ID to filter by.
	 *
	 * @return array User certificates.
	 */
	private function get_user_certificates_data( $user_id, $course_id = null ) {
		$args = array(
			'post_type'      => SPLMS_POST_TYPES['certificate'],
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Necessary for filtering certificates by user and status.
			'meta_query'     => array(
				array(
					'key'     => '_splms_certificate_user_id',
					'value'   => $user_id,
					'compare' => '=',
				),
				array(
					'key'     => '_splms_certificate_status',
					'value'   => 'issued',
					'compare' => '=',
				),
			),
			'orderby'        => 'meta_value',
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Necessary for ordering certificates by generation date.
			'meta_key'       => '_splms_certificate_generated_date',
			'order'          => 'DESC',
		);

		// Add course filter if specified.
		if ( $course_id ) {
			$args['meta_query'][] = array(
				'key'     => '_splms_certificate_course_id',
				'value'   => $course_id,
				'compare' => '=',
			);
		}

		$query                  = new WP_Query( $args );
		$formatted_certificates = array();

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$cert_id = get_the_ID();

				$course_id_meta  = get_post_meta( $cert_id, '_splms_certificate_course_id', true );
				$template_id     = get_post_meta( $cert_id, '_splms_certificate_template_id', true );
				$completion_date = get_post_meta( $cert_id, '_splms_certificate_completion_date', true );
				$generated_date  = get_post_meta( $cert_id, '_splms_certificate_generated_date', true );
				$status          = get_post_meta( $cert_id, '_splms_certificate_status', true );
				$token           = get_post_meta( $cert_id, '_splms_certificate_token', true );

				// Get course and template titles.
				$course_title   = '';
				$template_title = '';

				if ( $course_id_meta ) {
					$course       = get_post( $course_id_meta );
					$course_title = $course ? $course->post_title : '';
				}

				if ( $template_id ) {
					$template       = get_post( $template_id );
					$template_title = $template ? $template->post_title : '';
				}

				$formatted_certificates[] = array(
					'id'              => $cert_id,
					'course_id'       => $course_id_meta,
					'course_title'    => $course_title,
					'template_id'     => $template_id,
					'template_title'  => $template_title,
					'completion_date' => $completion_date,
					'generated_date'  => $generated_date,
					'status'          => $status,
					'certificate_url' => $token ? $this->get_certificate_url( $token ) : null,
				);
			}
		}

		wp_reset_postdata();

		return $formatted_certificates;
	}

	/**
	 * Validate element key without changing case (preserve camelCase)
	 *
	 * @since 1.0.0
	 *
	 * @param string $key Key to validate.
	 *
	 * @return string|false Validated key or false if invalid.
	 */
	private function validate_element_key( $key ) {
		// Only allow alphanumeric characters and underscores, preserve original case.
		if ( ! is_string( $key ) || ! preg_match( '/^[a-zA-Z0-9_]+$/', $key ) ) {
			return false;
		}

		// Limit key length for security.
		if ( strlen( $key ) > 50 ) {
			return false;
		}

		return $key; // Return original key with preserved case.
	}
}
