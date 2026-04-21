<?php
/**
 * Sections REST API Controller
 *
 * Handles REST API endpoints for course section management.
 * Provides endpoints for CRUD operations on course sections.
 *
 * @package SkillPulse_LMS
 * @since 1.0.0
 *
 * @api
 * Available endpoints:
 * GET    /splms/v1/section              - List sections (with course_id filter)
 * POST   /splms/v1/section              - Create section
 * GET    /splms/v1/section/{id}         - Get single section
 * PUT    /splms/v1/section/{id}         - Update section
 * DELETE /splms/v1/section/{id}         - Delete section
 * GET    /splms/v1/section/{id}/items   - Get lessons/quizzes in section
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sections REST API Controller class.
 *
 * @since 1.0.0
 */
class SkillPulse_LMS_REST_Section_Controller extends WP_REST_Controller {

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
	 * Register the section routes.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register_routes() {
		// Get sections.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_sections' ),
				'permission_callback' => array( $this, 'get_sections_permissions_check' ),
				'args'                => $this->get_collection_params(),
			)
		);

		// Get single section.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_section' ),
				'permission_callback' => array( $this, 'get_section_permissions_check' ),
				'args'                => array(
					'id' => array(
						'description' => __( 'Section ID', 'skillpulse-lms' ),
						'type'        => 'integer',
						'required'    => true,
					),
				),
				'schema'              => array( $this, 'get_public_item_schema' ),
			)
		);

		// Create section.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'create_section' ),
				'permission_callback' => array( $this, 'create_section_permissions_check' ),
				'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
			)
		);

		// Update section.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)',
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'update_section' ),
				'permission_callback' => array( $this, 'update_section_permissions_check' ),
				'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
			)
		);

		// Delete section.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)',
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( $this, 'delete_section' ),
				'permission_callback' => array( $this, 'delete_section_permissions_check' ),
				'args'                => array(
					'id' => array(
						'description' => __( 'Section ID', 'skillpulse-lms' ),
						'type'        => 'integer',
						'required'    => true,
					),
				),
			)
		);

		// Get section items (lessons/quizzes).
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)/items',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_section_items' ),
				'permission_callback' => array( $this, 'get_section_items_permissions_check' ),
				'args'                => array(
					'id' => array(
						'description' => __( 'Section ID', 'skillpulse-lms' ),
						'type'        => 'integer',
						'required'    => true,
					),
				),
			)
		);
	}

	/**
	 * Get sections.
	 *
	 * Retrieves a collection of sections with optional filtering by course,
	 * search, and pagination support.
	 *
	 * @since 1.0.0
	 *
	 * @api {get} /splms/v1/section List Sections
	 * @apiName GetSections
	 * @apiGroup Sections
	 * @apiVersion 1.0.0
	 *
	 * @apiDescription Retrieve a collection of sections. Supports filtering by course,
	 * search, and pagination. Requires edit_posts capability.
	 *
	 * @apiParam {Number} [page=1] Current page of the collection.
	 * @apiParam {Number} [per_page=10] Maximum number of items to be returned.
	 * @apiParam {String} [search] Limit results to those matching a string.
	 * @apiParam {Number} [course_id] Filter sections by course ID.
	 * @apiParam {String} [orderby=date] Sort collection by attribute.
	 * @apiParam {String} [order=desc] Order sort attribute ascending or descending.
	 * @apiParam {String} [include] Comma-separated list of section IDs to include.
	 *
	 * @apiError (Error 403) rest_forbidden Insufficient permissions to access sections.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_sections( $request ) {
		$args = array(
			'post_type'      => SPLMS_POST_TYPES['section'],
			'post_status'    => 'any',
			'posts_per_page' => $request->get_param( 'per_page' ) ? $request->get_param( 'per_page' ) : 10,
			'paged'          => $request->get_param( 'page' ) ? $request->get_param( 'page' ) : 1,
		);

		// Filter by course_id - get sections that belong to a specific course.
		$course_id = $request->get_param( 'course_id' );
		if ( $course_id ) {
			$course_items_query = SkillPulse_LMS_Course_Items_Query::get_instance();
			$course_items       = $course_items_query->get_items( absint( $course_id ) );

			// Extract section IDs from course items.
			$section_ids = array();
			foreach ( $course_items as $course_item ) {
				if ( SPLMS_POST_TYPES['section'] === $course_item->item_type ) {
					$section_ids[] = intval( $course_item->item_id );
				}
			}

			if ( ! empty( $section_ids ) ) {
				$args['post__in'] = $section_ids;
				$args['orderby']  = 'post__in';
			} else {
				// No sections found for this course.
				$response = rest_ensure_response(
					array(
						'sections' => array(),
						'total'    => 0,
						'pages'    => 0,
					)
				);
				$response->header( 'X-WP-Total', 0 );
				$response->header( 'X-WP-TotalPages', 0 );
				return $response;
			}
		}

		// Include specific post IDs.
		if ( $request->get_param( 'include' ) ) {
			$include_ids = explode( ',', $request->get_param( 'include' ) );
			$include_ids = array_map( 'absint', $include_ids );
			$include_ids = array_filter( $include_ids );
			if ( ! empty( $include_ids ) ) {
				if ( isset( $args['post__in'] ) ) {
					// Intersect with existing post__in if course_id filter is active.
					$args['post__in'] = array_intersect( $args['post__in'], $include_ids );
				} else {
					$args['post__in'] = $include_ids;
					$args['orderby']  = 'post__in';
				}
			}
		}

		// Search functionality.
		if ( $request->get_param( 'search' ) ) {
			$args['s'] = sanitize_text_field( $request->get_param( 'search' ) );
		}

		// Order by.
		if ( $request->get_param( 'orderby' ) ) {
			$args['orderby'] = sanitize_text_field( $request->get_param( 'orderby' ) );
		}

		// Order direction.
		if ( $request->get_param( 'order' ) ) {
			$args['order'] = sanitize_text_field( $request->get_param( 'order' ) );
		}

		$sections_query = new WP_Query( $args );
		$sections       = array();

		if ( $sections_query->have_posts() ) {
			while ( $sections_query->have_posts() ) {
				$sections_query->the_post();
				$section = get_post();

				$sections[] = $this->prepare_response_for_collection(
					$this->prepare_section_for_response( $section, $request )
				);
			}
		}

		wp_reset_postdata();

		$response = rest_ensure_response( $sections );
		$response->header( 'X-WP-Total', (int) $sections_query->found_posts );
		$response->header( 'X-WP-TotalPages', (int) $sections_query->max_num_pages );

		/**
		 * Fires after a list of sections response is prepared via the REST API.
		 *
		 * @param WP_REST_Response $response The response data.
		 * @param WP_REST_Request  $request  The request sent to the API.
		 */
		do_action( 'splms_rest_section_items_response', $response, $request );

		return $response;
	}

	/**
	 * Get single section.
	 *
	 * Retrieves a single section by ID with all associated metadata.
	 *
	 * @since 1.0.0
	 *
	 * @api {get} /splms/v1/section/:id Get Section
	 * @apiName GetSection
	 * @apiGroup Sections
	 * @apiVersion 1.0.0
	 *
	 * @apiDescription Retrieve a single section by ID with all metadata.
	 * Requires edit_posts capability.
	 *
	 * @apiParam {Number} id Section unique identifier.
	 *
	 * @apiError (Error 404) section_not_found Section not found.
	 * @apiError (Error 403) rest_forbidden Insufficient permissions to access section.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_section( $request ) {
		$section_id = $request->get_param( 'id' );
		$section    = get_post( $section_id );

		if ( ! $section || SPLMS_POST_TYPES['section'] !== $section->post_type ) {
			return new WP_Error( 'section_not_found', __( 'Section not found.', 'skillpulse-lms' ), array( 'status' => 404 ) );
		}

		$response = $this->prepare_section_for_response( $section, $request );

		/**
		 * Fires after a section response is prepared via the REST API.
		 *
		 * @param WP_REST_Response $response The response data.
		 * @param WP_Post          $section  Section post object.
		 * @param WP_REST_Request  $request  The request sent to the API.
		 */
		do_action( 'splms_rest_section_item_response', $response, $section, $request );

		return $response;
	}

	/**
	 * Create section.
	 *
	 * Creates a new section and optionally links it to a course.
	 *
	 * @since 1.0.0
	 *
	 * @api {post} /splms/v1/section Create Section
	 * @apiName CreateSection
	 * @apiGroup Sections
	 * @apiVersion 1.0.0
	 *
	 * @apiDescription Create a new section. Optionally link it to a course.
	 * Requires edit_posts capability.
	 *
	 * @apiParam {String} title Section title (required).
	 * @apiParam {String} [content] Section content/description.
	 * @apiParam {String} [status=publish] Section status.
	 * @apiParam {Number} [course_id] Course ID to link this section to.
	 * @apiParam {Number} [order] Order index within the course.
	 *
	 * @apiError (Error 400) invalid_section_data Invalid section data provided.
	 * @apiError (Error 403) rest_forbidden Insufficient permissions to create section.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function create_section( $request ) {
		$title = $request->get_param( 'title' );
		if ( empty( $title ) ) {
			return new WP_Error( 'invalid_section_data', __( 'Section title is required.', 'skillpulse-lms' ), array( 'status' => 400 ) );
		}

		$section_data = array(
			'post_type'    => SPLMS_POST_TYPES['section'],
			'post_title'   => sanitize_text_field( $title ),
			'post_content' => $request->get_param( 'content' ) ? wp_kses_post( $request->get_param( 'content' ) ) : '',
			'post_status'  => $request->get_param( 'status' ) ? sanitize_text_field( $request->get_param( 'status' ) ) : 'publish',
		);

		$section_id = wp_insert_post( $section_data );

		if ( is_wp_error( $section_id ) ) {
			return new WP_Error( 'section_creation_failed', __( 'Failed to create section.', 'skillpulse-lms' ), array( 'status' => 500 ) );
		}

		// Link section to course if course_id is provided.
		$course_id = $request->get_param( 'course_id' );
		if ( $course_id ) {
			$course_items_query = SkillPulse_LMS_Course_Items_Query::get_instance();
			$order_index        = $request->get_param( 'order' ) ? absint( $request->get_param( 'order' ) ) : 0;

			// Get max order index if not provided.
			if ( 0 === $order_index ) {
				$course_items = $course_items_query->get_items( absint( $course_id ) );
				$order_index  = ! empty( $course_items ) ? count( $course_items ) + 1 : 1;
			}

			$course_items_query->add_item(
				array(
					'course_id'   => absint( $course_id ),
					'item_id'     => $section_id,
					'item_type'   => SPLMS_POST_TYPES['section'],
					'order_index' => $order_index,
				)
			);
		}

		$section  = get_post( $section_id );
		$response = $this->prepare_section_for_response( $section, $request );

		return rest_ensure_response( $response );
	}

	/**
	 * Update section.
	 *
	 * Updates an existing section.
	 *
	 * @since 1.0.0
	 *
	 * @api {put} /splms/v1/section/:id Update Section
	 * @apiName UpdateSection
	 * @apiGroup Sections
	 * @apiVersion 1.0.0
	 *
	 * @apiDescription Update an existing section.
	 * Requires edit_posts capability.
	 *
	 * @apiParam {Number} id Section unique identifier.
	 * @apiParam {String} [title] Section title.
	 * @apiParam {String} [content] Section content/description.
	 * @apiParam {String} [status] Section status.
	 * @apiParam {Number} [order] Order index within the course.
	 *
	 * @apiError (Error 404) section_not_found Section not found.
	 * @apiError (Error 403) rest_forbidden Insufficient permissions to update section.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function update_section( $request ) {
		$section_id = $request->get_param( 'id' );
		$section    = get_post( $section_id );

		if ( ! $section || SPLMS_POST_TYPES['section'] !== $section->post_type ) {
			return new WP_Error( 'section_not_found', __( 'Section not found.', 'skillpulse-lms' ), array( 'status' => 404 ) );
		}

		$section_data = array(
			'ID' => $section_id,
		);

		if ( $request->get_param( 'title' ) ) {
			$section_data['post_title'] = sanitize_text_field( $request->get_param( 'title' ) );
		}

		if ( $request->get_param( 'content' ) ) {
			$section_data['post_content'] = wp_kses_post( $request->get_param( 'content' ) );
		}

		if ( $request->get_param( 'status' ) ) {
			$section_data['post_status'] = sanitize_text_field( $request->get_param( 'status' ) );
		}

		$updated_id = wp_update_post( $section_data );

		if ( is_wp_error( $updated_id ) ) {
			return new WP_Error( 'section_update_failed', __( 'Failed to update section.', 'skillpulse-lms' ), array( 'status' => 500 ) );
		}

		// Update order if provided.
		$order = $request->get_param( 'order' );
		if ( null !== $order ) {
			$course_items_query = SkillPulse_LMS_Course_Items_Query::get_instance();
			$course_item        = $course_items_query->get_item( $section_id );
			if ( $course_item ) {
				$course_items_query->update_item(
					$section_id,
					array(
						'order_index' => absint( $order ),
					)
				);
			}
		}

		$updated_section = get_post( $section_id );
		$response        = $this->prepare_section_for_response( $updated_section, $request );

		return rest_ensure_response( $response );
	}

	/**
	 * Delete section.
	 *
	 * Deletes a section and removes it from any associated courses.
	 *
	 * @since 1.0.0
	 *
	 * @api {delete} /splms/v1/section/:id Delete Section
	 * @apiName DeleteSection
	 * @apiGroup Sections
	 * @apiVersion 1.0.0
	 *
	 * @apiDescription Delete a section. This will also remove it from any courses.
	 * Requires delete_posts capability.
	 *
	 * @apiParam {Number} id Section unique identifier.
	 *
	 * @apiError (Error 404) section_not_found Section not found.
	 * @apiError (Error 403) rest_forbidden Insufficient permissions to delete section.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function delete_section( $request ) {
		$section_id = $request->get_param( 'id' );
		$section    = get_post( $section_id );

		if ( ! $section || SPLMS_POST_TYPES['section'] !== $section->post_type ) {
			return new WP_Error( 'section_not_found', __( 'Section not found.', 'skillpulse-lms' ), array( 'status' => 404 ) );
		}

		// Delete relationships first.
		$relationships_query = SkillPulse_LMS_Relationships_Query::get_instance();
		$relationships_query->delete_children( $section_id );
		$relationships_query->delete_parents( $section_id );

		// Delete from course items.
		$course_items_query = SkillPulse_LMS_Course_Items_Query::get_instance();
		$course_items_query->delete_item( $section_id );

		// Delete the post.
		$deleted = wp_delete_post( $section_id, true );

		if ( ! $deleted ) {
			return new WP_Error( 'section_deletion_failed', __( 'Failed to delete section.', 'skillpulse-lms' ), array( 'status' => 500 ) );
		}

		$response = rest_ensure_response(
			array(
				'deleted'  => true,
				'previous' => $this->prepare_section_for_response( $section, $request ),
			)
		);

		return $response;
	}

	/**
	 * Get section items.
	 *
	 * Retrieves all lessons and quizzes within a section.
	 *
	 * @since 1.0.0
	 *
	 * @api {get} /splms/v1/section/:id/items Get Section Items
	 * @apiName GetSectionItems
	 * @apiGroup Sections
	 * @apiVersion 1.0.0
	 *
	 * @apiDescription Retrieve all lessons and quizzes within a section.
	 * Requires edit_posts capability.
	 *
	 * @apiParam {Number} id Section unique identifier.
	 *
	 * @apiError (Error 404) section_not_found Section not found.
	 * @apiError (Error 403) rest_forbidden Insufficient permissions to access section items.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_section_items( $request ) {
		$section_id = $request->get_param( 'id' );
		$section    = get_post( $section_id );

		if ( ! $section || SPLMS_POST_TYPES['section'] !== $section->post_type ) {
			return new WP_Error( 'section_not_found', __( 'Section not found.', 'skillpulse-lms' ), array( 'status' => 404 ) );
		}

		$relationships_query = SkillPulse_LMS_Relationships_Query::get_instance();
		$children            = $relationships_query->get_children( $section_id );

		$items = array();

		foreach ( $children as $relationship ) {
			$item_post = get_post( $relationship->child_id );
			if ( ! $item_post ) {
				continue;
			}

			$item = array(
				'id'        => intval( $relationship->child_id ),
				'title'     => sanitize_text_field( get_the_title( $relationship->child_id ) ),
				'type'      => sanitize_text_field( $relationship->child_type ),
				'order'     => intval( $relationship->order_index ),
				'permalink' => esc_url_raw( get_permalink( $relationship->child_id ) ),
				'edit_url'  => esc_url_raw( get_edit_post_link( $relationship->child_id, 'raw' ) ),
			);

			$items[] = $item;
		}

		$response = rest_ensure_response( $items );

		return $response;
	}

	/**
	 * Prepare section data for response.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Post         $section Section post object.
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return WP_REST_Response Response object.
	 */
	public function prepare_section_for_response( $section, $request ) {
		$GLOBALS['post'] = $section; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		setup_postdata( $section );

		$is_single_request = ! empty( $request->get_param( 'id' ) ) && absint( $request->get_param( 'id' ) ) === $section->ID;
		$section_id        = $section->ID;

		// Get course information if section is linked to a course.
		$course_items_query = SkillPulse_LMS_Course_Items_Query::get_instance();
		$course_item        = $course_items_query->get_item( $section_id );

		// Base fields for every section.
		$data = array(
			'id'           => intval( $section_id ),
			'title'        => array(
				'raw'      => $section->post_title,
				'rendered' => get_the_title( $section_id ),
			),
			'slug'         => $section->post_name,
			'link'         => get_permalink( $section_id ),
			'status'       => $section->post_status,
			'type'         => $section->post_type,
			'date'         => mysql2date( 'c', $section->post_date, false ),
			'date_gmt'     => mysql2date( 'c', $section->post_date_gmt, false ),
			'modified'     => mysql2date( 'c', $section->post_modified, false ),
			'modified_gmt' => mysql2date( 'c', $section->post_modified_gmt, false ),
		);

		// Add content (only for single requests - will be filtered by context in schema).
		if ( $is_single_request ) {
			$data['content'] = array(
				'raw'      => $section->post_content,
				'rendered' => apply_filters( 'the_content', $section->post_content ), // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Core filter.
			);
		}

		// Add course information if linked (always include).
		if ( $course_item ) {
			$data['course_id'] = intval( $course_item->course_id );
			$data['order']     = intval( $course_item->order_index );
		}

		// Get child items count (always include).
		$relationships_query = SkillPulse_LMS_Relationships_Query::get_instance();
		$children            = $relationships_query->get_children( $section_id );
		$data['items_count'] = count( $children );

		// Additional detailed fields (only for single requests - will be filtered by context in schema).
		if ( $is_single_request ) {
			// Author/Instructor information.
			$author         = get_userdata( $section->post_author );
			$data['author'] = array(
				'id'         => (int) $section->post_author,
				'name'       => $author ? $author->display_name : '',
				'slug'       => $author ? $author->user_nicename : '',
				'avatar_url' => $author ? get_avatar_url( $section->post_author, array( 'size' => 96 ) ) : '',
			);
		}

		$context = ! empty( $request['context'] ) ? $request['context'] : 'view';
		$data    = $this->add_additional_fields_to_object( $data, $request );
		$data    = $this->filter_response_by_context( $data, $context );

		// Wrap the data in a response object.
		$response = rest_ensure_response( $data );

		// Add links.
		$response->add_links( $this->prepare_links( $section ) );

		/**
		 * Filters section response.
		 *
		 * @param WP_REST_Response $response Rest response.
		 * @param WP_Post          $section  Section post object.
		 * @param WP_REST_Request  $request  Request object.
		 */
		return apply_filters( 'splms_rest_prepare_section', $response, $section, $request );
	}

	/**
	 * Prepare links for the request.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Post $section Section post object.
	 * @return array Links for the given section.
	 */
	protected function prepare_links( $section ) {
		$links = array(
			'self'       => array(
				array(
					'href' => rest_url( $this->namespace . '/' . $this->rest_base . '/' . $section->ID ),
				),
			),
			'collection' => array(
				array(
					'href' => rest_url( $this->namespace . '/' . $this->rest_base ),
				),
			),
		);

		// Add course link if section has a parent course.
		$course_id = SkillPulse_LMS_Course_Items_Query::get_instance()->get_item_course_id( $section->ID );
		if ( ! empty( $course_id ) ) {
			$links['course'] = array(
				array(
					'href'       => rest_url( splms_rest_namespace() . '/' . splms_rest_version() . '/courses/' . $course_id ),
					'embeddable' => true,
				),
			);
		}

		// Add items link.
		$links['items'] = array(
			array(
				'href' => rest_url( $this->namespace . '/' . $this->rest_base . '/' . $section->ID . '/items' ),
			),
		);

		return $links;
	}

	/**
	 * Check permissions for getting sections.
	 *
	 * Allows open access by default. Use the 'splms_rest_sections_permissions_check' filter
	 * to add custom access restrictions.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return bool|WP_Error True if the request has access, WP_Error object otherwise.
	 */
	public function get_sections_permissions_check( $request ) {
		$user_id = get_current_user_id();

		// Default: allow open access.
		$has_access = true;

		/**
		 * Filter sections list access permission check.
		 *
		 * Allows developers to add custom access restrictions for the sections list endpoint.
		 * Return true to allow access, false or WP_Error to deny access.
		 *
		 * @since 1.0.0
		 *
		 * @param bool|WP_Error $has_access Whether the user has access. Default true (open access).
		 * @param int            $user_id    User ID (0 for non-logged-in users).
		 * @param WP_REST_Request $request   Request object.
		 */
		$has_access = apply_filters( 'splms_rest_sections_permissions_check', $has_access, $user_id, $request );

		// If filter returns WP_Error, return it directly.
		if ( is_wp_error( $has_access ) ) {
			return $has_access;
		}

		// If filter returns false, deny access.
		if ( false === $has_access ) {
			return new WP_Error(
				'rest_forbidden',
				__( 'Sorry, you are not allowed to perform this action.', 'skillpulse-lms' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		// Allow access (default behavior).
		return true;
	}

	/**
	 * Check permissions for getting single section.
	 *
	 * Allows open access by default. Use the 'splms_rest_section_permissions_check' filter
	 * to add custom access restrictions.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return bool|WP_Error True if the request has access, WP_Error object otherwise.
	 */
	public function get_section_permissions_check( $request ) {
		$section_id = $request->get_param( 'id' );
		$user_id    = get_current_user_id();

		// Default: allow open access.
		$has_access = true;

		/**
		 * Filter section access permission check.
		 *
		 * Allows developers to add custom access restrictions for sections.
		 * Return true to allow access, false or WP_Error to deny access.
		 *
		 * @since 1.0.0
		 *
		 * @param bool|WP_Error $has_access Whether the user has access. Default true (open access).
		 * @param int            $section_id Section ID.
		 * @param int            $user_id    User ID (0 for non-logged-in users).
		 * @param WP_REST_Request $request   Request object.
		 */
		$has_access = apply_filters( 'splms_rest_section_permissions_check', $has_access, $section_id, $user_id, $request );

		// If filter returns WP_Error, return it directly.
		if ( is_wp_error( $has_access ) ) {
			return $has_access;
		}

		// If filter returns false, deny access.
		if ( false === $has_access ) {
			return new WP_Error(
				'rest_forbidden',
				__( 'Sorry, you are not allowed to perform this action.', 'skillpulse-lms' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		// Allow access (default behavior).
		return true;
	}

	/**
	 * Check if a given request has access to create a section.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return bool|WP_Error True if request has access, WP_Error object otherwise.
	 */
	public function create_section_permissions_check( $request ) {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return new WP_Error( 'rest_forbidden', __( 'Sorry, you are not allowed to create sections.', 'skillpulse-lms' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}

	/**
	 * Check if a given request has access to update a section.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return bool|WP_Error True if request has access, WP_Error object otherwise.
	 */
	public function update_section_permissions_check( $request ) {
		$section = get_post( $request->get_param( 'id' ) );

		if ( ! $section ) {
			return true; // Will be handled by get_section.
		}

		if ( ! current_user_can( 'edit_post', $section->ID ) ) {
			return new WP_Error( 'rest_forbidden', __( 'Sorry, you are not allowed to update this section.', 'skillpulse-lms' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}

	/**
	 * Check if a given request has access to delete a section.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return bool|WP_Error True if request has access, WP_Error object otherwise.
	 */
	public function delete_section_permissions_check( $request ) {
		$section = get_post( $request->get_param( 'id' ) );

		if ( ! $section ) {
			return true; // Will be handled by delete_section.
		}

		if ( ! current_user_can( 'delete_post', $section->ID ) ) {
			return new WP_Error( 'rest_forbidden', __( 'Sorry, you are not allowed to delete this section.', 'skillpulse-lms' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}

	/**
	 * Check permissions for getting section items.
	 *
	 * Allows open access by default. Use the 'splms_rest_section_items_permissions_check' filter
	 * to add custom access restrictions.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return bool|WP_Error True if the request has access, WP_Error object otherwise.
	 */
	public function get_section_items_permissions_check( $request ) {
		$section_id = $request->get_param( 'id' );
		$user_id    = get_current_user_id();

		// Default: allow open access.
		$has_access = true;

		/**
		 * Filter section items access permission check.
		 *
		 * Allows developers to add custom access restrictions for section items endpoint.
		 * Return true to allow access, false or WP_Error to deny access.
		 *
		 * @since 1.0.0
		 *
		 * @param bool|WP_Error $has_access Whether the user has access. Default true (open access).
		 * @param int            $section_id Section ID.
		 * @param int            $user_id    User ID (0 for non-logged-in users).
		 * @param WP_REST_Request $request   Request object.
		 */
		$has_access = apply_filters( 'splms_rest_section_items_permissions_check', $has_access, $section_id, $user_id, $request );

		// If filter returns WP_Error, return it directly.
		if ( is_wp_error( $has_access ) ) {
			return $has_access;
		}

		// If filter returns false, deny access.
		if ( false === $has_access ) {
			return new WP_Error(
				'rest_forbidden',
				__( 'Sorry, you are not allowed to perform this action.', 'skillpulse-lms' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		// Allow access (default behavior).
		return true;
	}

	/**
	 * Get the query params for collections.
	 *
	 * @since 1.0.0
	 *
	 * @return array Collection parameters array.
	 */
	public function get_collection_params() {
		return array(
			'page'      => array(
				'description' => __( 'Current page of the collection.', 'skillpulse-lms' ),
				'type'        => 'integer',
				'default'     => 1,
				'minimum'     => 1,
			),
			'per_page'  => array(
				'description' => __( 'Maximum number of items to be returned in result set.', 'skillpulse-lms' ),
				'type'        => 'integer',
				'default'     => 10,
				'minimum'     => 1,
				'maximum'     => 100,
			),
			'search'    => array(
				'description' => __( 'Limit results to those matching a string.', 'skillpulse-lms' ),
				'type'        => 'string',
			),
			'course_id' => array(
				'description' => __( 'Filter sections by course ID.', 'skillpulse-lms' ),
				'type'        => 'integer',
			),
			'orderby'   => array(
				'description' => __( 'Sort collection by object attribute.', 'skillpulse-lms' ),
				'type'        => 'string',
				'default'     => 'date',
				'enum'        => array( 'date', 'title', 'menu_order' ),
			),
			'order'     => array(
				'description' => __( 'Order sort attribute ascending or descending.', 'skillpulse-lms' ),
				'type'        => 'string',
				'default'     => 'desc',
				'enum'        => array( 'asc', 'desc' ),
			),
			'include'   => array(
				'description' => __( 'Limit result set to specific section IDs (comma-separated).', 'skillpulse-lms' ),
				'type'        => 'string',
			),
		);
	}

	/**
	 * Retrieves the section schema, conforming to JSON Schema.
	 *
	 * @since 1.0.0
	 *
	 * @return array Item schema data.
	 */
	public function get_item_schema() {
		return $this->get_public_item_schema();
	}

	/**
	 * Retrieves the public section schema, conforming to JSON Schema.
	 *
	 * @since 1.0.0
	 *
	 * @return array Item schema data.
	 */
	public function get_public_item_schema() {
		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'section',
			'type'       => 'object',
			'properties' => array(
				'id'           => array(
					'description' => __( 'Unique identifier for the section.', 'skillpulse-lms' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'title'        => array(
					'description' => __( 'The title for the section.', 'skillpulse-lms' ),
					'type'        => 'object',
					'context'     => array( 'view', 'edit' ),
					'properties'  => array(
						'raw'      => array(
							'description' => __( 'Title for the section, as it exists in the database.', 'skillpulse-lms' ),
							'type'        => 'string',
							'context'     => array( 'edit' ),
						),
						'rendered' => array(
							'description' => __( 'HTML title for the section, transformed for display.', 'skillpulse-lms' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
							'readonly'    => true,
						),
					),
				),
				'slug'         => array(
					'description' => __( 'An alphanumeric identifier for the section unique to its type.', 'skillpulse-lms' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'link'         => array(
					'description' => __( 'URL to the section.', 'skillpulse-lms' ),
					'type'        => 'string',
					'format'      => 'uri',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'date'         => array(
					'description' => __( "The date the section was published, in the site's timezone.", 'skillpulse-lms' ),
					'type'        => 'string',
					'format'      => 'date-time',
					'context'     => array( 'view', 'edit' ),
				),
				'date_gmt'     => array(
					'description' => __( 'The date the section was published, as GMT.', 'skillpulse-lms' ),
					'type'        => 'string',
					'format'      => 'date-time',
					'context'     => array( 'view', 'edit' ),
				),
				'modified'     => array(
					'description' => __( "The date the section was last modified, in the site's timezone.", 'skillpulse-lms' ),
					'type'        => 'string',
					'format'      => 'date-time',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'modified_gmt' => array(
					'description' => __( 'The date the section was last modified, as GMT.', 'skillpulse-lms' ),
					'type'        => 'string',
					'format'      => 'date-time',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'status'       => array(
					'description' => __( 'A named status for the section.', 'skillpulse-lms' ),
					'type'        => 'string',
					'enum'        => array( 'publish', 'future', 'draft', 'pending', 'private' ),
					'context'     => array( 'view', 'edit' ),
				),
				'type'         => array(
					'description' => __( 'Type of post for the section.', 'skillpulse-lms' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'content'      => array(
					'description' => __( 'The content for the section.', 'skillpulse-lms' ),
					'type'        => 'object',
					'context'     => array( 'view' ),
					'properties'  => array(
						'raw'      => array(
							'description' => __( 'Content for the section, as it exists in the database.', 'skillpulse-lms' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
						),
						'rendered' => array(
							'description' => __( 'HTML content for the section, transformed for display.', 'skillpulse-lms' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
						),
					),
					'readonly'    => true,
				),
				'course_id'    => array(
					'description' => __( 'The ID of the parent course (if linked).', 'skillpulse-lms' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'order'        => array(
					'description' => __( 'Order index within the course.', 'skillpulse-lms' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'items_count'  => array(
					'description' => __( 'Number of items (lessons/quizzes) in this section.', 'skillpulse-lms' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'author'       => array(
					'description' => __( 'Section author/instructor information.', 'skillpulse-lms' ),
					'type'        => 'object',
					'context'     => array( 'view' ),
					'properties'  => array(
						'id'         => array(
							'type'        => 'integer',
							'description' => __( 'Author user ID.', 'skillpulse-lms' ),
						),
						'name'       => array(
							'type'        => 'string',
							'description' => __( 'Author display name.', 'skillpulse-lms' ),
						),
						'slug'       => array(
							'type'        => 'string',
							'description' => __( 'Author user slug.', 'skillpulse-lms' ),
						),
						'avatar_url' => array(
							'type'        => 'string',
							'format'      => 'uri',
							'description' => __( 'Author avatar URL.', 'skillpulse-lms' ),
						),
					),
					'readonly'    => true,
				),
			),
		);

		return $this->add_additional_fields_schema( $schema );
	}

	/**
	 * Get the endpoint arguments for item schema.
	 *
	 * @since 1.0.0
	 *
	 * @param string $method HTTP method.
	 *
	 * @return array Endpoint arguments.
	 */
	public function get_endpoint_args_for_item_schema( $method = WP_REST_Server::CREATABLE ) {
		$args = array();

		if ( WP_REST_Server::CREATABLE === $method || WP_REST_Server::EDITABLE === $method ) {
			$args['title'] = array(
				'description' => __( 'Section title.', 'skillpulse-lms' ),
				'type'        => 'string',
				'required'    => WP_REST_Server::CREATABLE === $method,
			);

			$args['content'] = array(
				'description' => __( 'Section content/description.', 'skillpulse-lms' ),
				'type'        => 'string',
				'required'    => false,
			);

			$args['status'] = array(
				'description' => __( 'Section status.', 'skillpulse-lms' ),
				'type'        => 'string',
				'enum'        => array( 'publish', 'draft', 'private', 'pending' ),
				'required'    => false,
			);

			if ( WP_REST_Server::CREATABLE === $method ) {
				$args['course_id'] = array(
					'description' => __( 'Course ID to link this section to.', 'skillpulse-lms' ),
					'type'        => 'integer',
					'required'    => false,
				);
			}

			$args['order'] = array(
				'description' => __( 'Order index within the course.', 'skillpulse-lms' ),
				'type'        => 'integer',
				'required'    => false,
			);
		}

		return $args;
	}
}
