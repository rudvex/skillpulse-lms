<?php
/**
 * REST API Course Curriculum Controller
 *
 * Handles REST API endpoints for course curriculum management.
 * Provides endpoints for getting and updating course curriculum (sections, lessons, quizzes).
 *
 * @package SPLMS
 * @since 1.0.0
 *
 * @api
 * Available endpoints:
 * GET  /splms/v1/courses/{id}/curriculum - Get course curriculum structure
 * PUT  /splms/v1/courses/{id}/curriculum - Update course curriculum structure
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST API Course Curriculum Controller class.
 *
 * @since 1.0.0
 */
class SPLMS_REST_Course_Curriculum_Controller extends SPLMS_REST_Course_Controller {
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
			'/' . $this->rest_base . '/(?P<id>[\d]+)/curriculum',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_curriculum' ),
					'permission_callback' => array( $this, 'get_curriculum_permissions_check' ),
					'args'                => array(
						'id' => array(
							'description' => __( 'Unique identifier for the course.', 'skillpulse-lms' ),
							'type'        => 'integer',
							'required'    => true,
						),
					),
					'schema'              => array( $this, 'get_curriculum_schema' ),
				),
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_curriculum' ),
					'permission_callback' => array( $this, 'update_curriculum_permissions_check' ),
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
					'schema'              => array( $this, 'get_curriculum_schema' ),
				),
			)
		);
	}

	/**
	 * Get the course curriculum.
	 *
	 * Retrieves the complete curriculum structure for a course, including all sections,
	 * lessons, and quizzes organized hierarchically.
	 *
	 * @since 1.0.0
	 *
	 * @api {get} /splms/v1/courses/:id/curriculum Get Course Curriculum
	 * @apiName GetCourseCurriculum
	 * @apiGroup Courses
	 * @apiVersion 1.0.0
	 *
	 * @apiDescription Retrieve the complete curriculum structure for a course. Returns a hierarchical
	 * structure of sections, lessons, and quizzes with their order and relationships.
	 *
	 * @apiParam {Number} id Course unique identifier.
	 *
	 * @apiError (Error 404) splms_rest_course_not_found Course not found.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_curriculum( $request ) {
		$course_id = $request->get_param( 'id' );

		if ( ! splms_course_exists( $course_id ) ) {
			return new WP_Error( 'splms_rest_course_not_found', __( 'Course not found', 'skillpulse-lms' ), array( 'status' => 404 ) );
		}

		// Get course curriculum using unified method.
		$curriculum_result = splms_get_course_curriculum( $course_id );

		$curriculums = array();

		if ( isset( $curriculum_result['sections'] ) ) {
			// Pre-fetch all post IDs in a single query to avoid N+1 queries.
			$all_ids = $this->collect_curriculum_ids( $curriculum_result['sections'] );
			if ( ! empty( $all_ids ) ) {
				// Prime the post cache for all curriculum items at once.
				_prime_post_caches( $all_ids );
			}

			foreach ( $curriculum_result['sections'] as $section ) {
				if ( ! get_post( $section['id'] ) ) {
					continue;
				}

				$curriculum            = new stdClass();
				$curriculum->id        = intval( $section['id'] );
				$curriculum->permalink = esc_url_raw( $section['permalink'] );
				$curriculum->edit_url  = esc_url_raw( get_edit_post_link( $section['id'], 'raw' ) );
				$curriculum->title     = sanitize_text_field( $section['title'] );
				$curriculum->type      = sanitize_text_field( $section['type'] );
				$curriculum->order     = intval( $section['order'] );
				$curriculum->children  = $this->transform_children_to_rest_format( $section['children'] );

				$curriculums[] = $curriculum;
			}
		}

		$response = rest_ensure_response( $curriculums );

		return $response;
	}

	/**
	 * Transform children array from unified format to REST API format.
	 *
	 * Converts the array format from get_course_curriculum() to stdClass format
	 * with admin-specific fields like edit_url.
	 *
	 * @since 1.0.0
	 *
	 * @param array $children Array of child items from unified method.
	 *
	 * @return array Array of child items in REST API format.
	 */
	private function transform_children_to_rest_format( $children ) {
		$formatted_children = array();

		foreach ( $children as $child_data ) {
			if ( ! get_post( $child_data['id'] ) ) {
				continue;
			}

			$child            = new stdClass();
			$child->id        = intval( $child_data['id'] );
			$child->permalink = esc_url_raw( $child_data['permalink'] );
			$child->edit_url  = esc_url_raw( get_edit_post_link( $child_data['id'], 'raw' ) );
			$child->title     = sanitize_text_field( $child_data['title'] );
			$child->type      = sanitize_text_field( $child_data['type'] );
			$child->order     = intval( $child_data['order'] );

			// Recursively transform nested children if present.
			$child->children = ! empty( $child_data['children'] ) ? $this->transform_children_to_rest_format( $child_data['children'] ) : array();

			$formatted_children[] = $child;
		}

		return $formatted_children;
	}

	/**
	 * Recursively collect all post IDs from curriculum sections and children.
	 *
	 * @since 1.0.0
	 *
	 * @param array $items Array of curriculum items (sections or children).
	 * @return array Flat array of all post IDs.
	 */
	private function collect_curriculum_ids( $items ) {
		$ids = array();
		foreach ( $items as $item ) {
			$ids[] = intval( $item['id'] );
			if ( ! empty( $item['children'] ) ) {
				$ids = array_merge( $ids, $this->collect_curriculum_ids( $item['children'] ) );
			}
		}
		return $ids;
	}

	/**
	 * Update the course curriculum.
	 *
	 * Updates the complete curriculum structure for a course. Replaces existing curriculum
	 * with the new structure provided. Creates new items if they don't exist.
	 *
	 * @since 1.0.0
	 *
	 * @api {put} /splms/v1/courses/:id/curriculum Update Course Curriculum
	 * @apiName UpdateCourseCurriculum
	 * @apiGroup Courses
	 * @apiVersion 1.0.0
	 *
	 * @apiDescription Update the complete curriculum structure for a course. This replaces the
	 * existing curriculum with the new structure. Items are created if they don't exist.
	 *
	 * @apiParam {Number} id Course unique identifier.
	 * @apiParam {Object} curriculum Curriculum structure object.
	 * @apiParam {Array} curriculum.sections Array of section objects.
	 * @apiParam {Number} [curriculum.sections.id] Section ID (0 for new sections).
	 * @apiParam {String} curriculum.sections.title Section title.
	 * @apiParam {String} curriculum.sections.type Section type (must be 'section').
	 * @apiParam {Array} [curriculum.sections.children] Array of child items (lessons/quizzes).
	 *
	 * @apiError (Error 404) splms_rest_course_not_found Course not found.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function update_curriculum( $request ) {
		$course_id = $request->get_param( 'id' );

		if ( ! splms_course_exists( $course_id ) ) {
			return new WP_Error( 'splms_rest_course_not_found', __( 'Course not found', 'skillpulse-lms' ), array( 'status' => 404 ) );
		}

		$curriculums = $request->get_param( 'curriculum' );

		if ( empty( $curriculums ) ) {
			$curriculums = $request->get_json_params();
		}

		$course_items_query = SPLMS_Course_Items_Query::get_instance();
		$course_items_query->delete_items( $course_id );

		$relationships_query = SPLMS_Relationships_Query::get_instance();
		$relationships_query->delete_children( $course_id );

		$order = 1;

		if ( ! empty( $curriculums['sections'] ) ) {
			foreach ( $curriculums['sections'] as $curriculum ) {
				// Ensure the item exists or create it.
				$item_id = $this->ensure_item_exists( $curriculum );

				$course_items_query->add_item(
					array(
						'course_id'   => $course_id,
						'item_id'     => $item_id,
						'item_type'   => $curriculum['type'],
						'order_index' => $order,
					)
				);

				++$order;

				$this->update_children( $course_id, $item_id, $curriculum['children'], $order );
			}
		}

		return rest_ensure_response( $curriculums );
	}

	/**
	 * Update child relationships for a parent item (recursive).
	 *
	 * @since 1.0.0
	 *
	 * @param int   $course_id Course ID.
	 * @param int   $parent_id Parent item ID.
	 * @param array $children  Array of child items.
	 * @param int   &$order    Current order index (passed by reference).
	 *
	 * @return void
	 */
	public function update_children( $course_id, $parent_id, $children, &$order ) {
		$relationships_query = SPLMS_Relationships_Query::get_instance();

		// Get all existing children for the parent.
		$existing_children = $relationships_query->get_children( $parent_id );

		// Create a list of current child IDs from the `$children` array.
		$current_child_ids = array_column( $children, 'id' );

		// Find child IDs to delete (present in the database but not in the new $children).
		foreach ( $existing_children as $existing_child ) {
			if ( ! in_array( $existing_child->child_id, $current_child_ids, true ) ) {
				$relationships_query->delete_relationship( $parent_id, $existing_child->child_id );
			}
		}

		// Process and update the provided $children array.
		foreach ( $children as $child ) {
			// Ensure the child item exists or create it.
			$child_id = $this->ensure_item_exists( $child );

			// Check if the relationship already exists.
			$existing_relationship = $relationships_query->get_relationship( $parent_id, $child_id );

			if ( ! $existing_relationship ) {
				$relationships_query->insert_relationship( $parent_id, $child_id, $child['type'], $order );
			} else {
				// Optionally, update the order if necessary.
				$relationships_query->update_relationship_order( $parent_id, $child_id, $order );
			}

			++$order;

			$child_content = ! empty( $child['children'] ) ? $child['children'] : array();

			$this->update_children( $course_id, $child_id, $child_content, $order );
		}
	}

	/**
	 * Ensures that the item exists in the database or creates it.
	 *
	 * @since 1.0.0
	 *
	 * @param array $item Data of the item to check/create.
	 *
	 * @return int|null The ID of the existing or newly created item, or null on failure.
	 */
	private function ensure_item_exists( $item ) {
		$item_id       = isset( $item['id'] ) ? intval( $item['id'] ) : 0;
		$existing_post = get_post( $item_id );

		if ( ! $existing_post ) {
			// Create the post if it doesn't exist.
			$post_args = array(
				'post_title'  => isset( $item['title'] ) ? sanitize_text_field( $item['title'] ) : '',
				'post_type'   => isset( $item['type'] ) ? sanitize_text_field( $item['type'] ) : '',
				'post_status' => 'publish',
			);

			$post_id = wp_insert_post( $post_args );

			if ( is_wp_error( $post_id ) ) {
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Error logging for failed post creation.
				error_log( 'Failed to create post: ' . $post_id->get_error_message() );

				return null; // Handle the error appropriately.
			}

			return $post_id;
		} else {
			// Update the post title if it has changed.
			$new_title = isset( $item['title'] ) ? sanitize_text_field( $item['title'] ) : '';
			if ( $existing_post->post_title !== $new_title && ! empty( $new_title ) ) {
				wp_update_post(
					array(
						'ID'         => $existing_post->ID,
						'post_title' => $new_title,
					)
				);
			}
		}

		return $item_id;
	}

	/**
	 * Check if a given request has access to get the course curriculum.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return bool|WP_Error True if request has access, WP_Error object otherwise.
	 */
	public function get_curriculum_permissions_check( $request ) {
		$retval = true;

		/**
		 * Filter the course curriculum `get_items` permissions check.
		 *
		 * @param bool|WP_Error   $retval  Returned value.
		 * @param WP_REST_Request $request The request sent to the API.
		 *
		 * @since 1.0.0
		 */
		return apply_filters( 'splms_get_curriculum_permissions_check', $retval, $request );
	}

	/**
	 * Check if a given request has access to update the course curriculum.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return bool|WP_Error True if request has access, WP_Error object otherwise.
	 */
	public function update_curriculum_permissions_check( $request ) {
		$retval = true;

		/**
		 * Filter the course curriculum `update_items` permissions check.
		 *
		 * @param bool|WP_Error   $retval  Returned value.
		 * @param WP_REST_Request $request The request sent to the API.
		 *
		 * @since 1.0.0
		 */
		return apply_filters( 'splms_update_curriculum_permissions_check', $retval, $request );
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

		$params['curriculum'] = array(
			'description' => __( 'The course curriculum.', 'skillpulse-lms' ),
			'type'        => 'object',
			'required'    => false,
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
		$params['curriculum'] = array(
			'description' => __( 'The course curriculum.', 'skillpulse-lms' ),
			'type'        => 'object',
			'required'    => false,
		);

		return $params;
	}

	/**
	 * Get the curriculum schema, conforming to JSON Schema.
	 *
	 * @since 1.0.0
	 *
	 * @return array Schema definition array.
	 */
	public function get_curriculum_schema() {
		$schema = array(
			'$schema'     => 'http://json-schema.org/draft-04/schema#',
			'title'       => 'course-curriculum',
			'type'        => 'object',
			'description' => __( 'Course curriculum structure containing sections, lessons, and quizzes.', 'skillpulse-lms' ),
			'properties'  => array(
				'sections' => array(
					'description' => __( 'Array of course sections.', 'skillpulse-lms' ),
					'type'        => 'array',
					'items'       => array(
						'type'       => 'object',
						'properties' => array(
							'id'       => array(
								'description' => __( 'Section ID. Use 0 for new sections.', 'skillpulse-lms' ),
								'type'        => 'integer',
							),
							'title'    => array(
								'description' => __( 'Section title.', 'skillpulse-lms' ),
								'type'        => 'string',
								'required'    => true,
							),
							'type'     => array(
								'description' => __( 'Item type. Must be "section".', 'skillpulse-lms' ),
								'type'        => 'string',
								'enum'        => array( 'section' ),
								'required'    => true,
							),
							'children' => array(
								'description' => __( 'Array of child items (lessons, quizzes, or nested sections).', 'skillpulse-lms' ),
								'type'        => 'array',
							),
						),
					),
				),
			),
		);

		return $schema;
	}
}
