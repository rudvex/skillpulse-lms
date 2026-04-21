<?php
/**
 * Lessons REST API Controller
 *
 * Handles REST API endpoints for lesson management.
 * Provides endpoints for CRUD operations and progress tracking on lessons.
 *
 * @package SkillPulse_LMS
 * @since 1.0.0
 *
 * @api
 * Available endpoints:
 * GET    /splms/v1/lessons           - List lessons
 * POST   /splms/v1/lessons           - Create lesson
 * GET    /splms/v1/lessons/{id}      - Get single lesson
 * PUT    /splms/v1/lessons/{id}      - Update lesson
 * DELETE /splms/v1/lessons/{id}      - Delete lesson
 * GET    /splms/v1/lessons/{id}/progress - Get lesson progress
 * PUT    /splms/v1/lessons/{id}/progress - Update lesson progress
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Lessons REST API Controller class.
 *
 * @since 1.0.0
 */
class SkillPulse_LMS_REST_Lesson_Controller extends WP_REST_Controller {

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
	 * Register the lesson routes.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register_routes() {
		// Get lessons.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_lessons' ),
				'permission_callback' => array( $this, 'get_lessons_permissions_check' ),
				'args'                => $this->get_collection_params(),
				'schema'              => array( $this, 'get_public_item_schema' ),
			)
		);

		// Get single lesson.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_lesson' ),
				'permission_callback' => array( $this, 'get_lesson_permissions_check' ),
				'args'                => array(
					'id' => array(
						'description' => __( 'Lesson ID', 'skillpulse-lms' ),
						'type'        => 'integer',
						'required'    => true,
					),
				),
				'schema'              => array( $this, 'get_public_item_schema' ),
			)
		);

		// Create lesson.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'create_lesson' ),
				'permission_callback' => array( $this, 'create_lesson_permissions_check' ),
				'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
			)
		);

		// Update lesson.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)',
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'update_lesson' ),
				'permission_callback' => array( $this, 'update_lesson_permissions_check' ),
				'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
			)
		);

		// Delete lesson.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)',
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( $this, 'delete_lesson' ),
				'permission_callback' => array( $this, 'delete_lesson_permissions_check' ),
				'args'                => array(
					'id' => array(
						'description' => __( 'Lesson ID', 'skillpulse-lms' ),
						'type'        => 'integer',
						'required'    => true,
					),
				),
			)
		);
	}

	/**
	 * Get lessons.
	 *
	 * Retrieves a collection of lessons with optional filtering by course,
	 * search, and pagination support.
	 *
	 * @since 1.0.0
	 *
	 * @api {get} /splms/v1/lessons List Lessons
	 * @apiName GetLessons
	 * @apiGroup Lessons
	 * @apiVersion 1.0.0
	 *
	 * @apiDescription Retrieve a collection of lessons. Supports filtering by course,
	 * search, and pagination. Requires edit_posts capability.
	 *
	 * @apiParam {Number} [page=1] Current page of the collection.
	 * @apiParam {Number} [per_page=10] Maximum number of items to be returned.
	 * @apiParam {String} [search] Limit results to those matching a string.
	 * @apiParam {Number} [course_id] Filter lessons by course ID.
	 * @apiParam {String} [orderby=date] Sort collection by attribute.
	 * @apiParam {String} [order=desc] Order sort attribute ascending or descending.
	 * @apiParam {String} [include] Comma-separated list of lesson IDs to include.
	 *
	 * @apiError (Error 403) rest_forbidden Insufficient permissions to access lessons.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_lessons( $request ) {
		$args = array(
			'post_type'      => SPLMS_POST_TYPES['lesson'],
			'post_status'    => 'any',
			'posts_per_page' => $request->get_param( 'per_page' ) ? $request->get_param( 'per_page' ) : 10,
			'paged'          => $request->get_param( 'page' ) ? $request->get_param( 'page' ) : 1,
		);

		// Filter by course.
		if ( $request->get_param( 'course_id' ) ) {
			$args['post_parent'] = $request->get_param( 'course_id' );
		}

		// Include specific post IDs.
		if ( $request->get_param( 'include' ) ) {
			$include_ids = explode( ',', $request->get_param( 'include' ) );
			$include_ids = array_map( 'intval', $include_ids );
			$include_ids = array_filter( $include_ids );
			if ( ! empty( $include_ids ) ) {
				$args['post__in'] = $include_ids;
				// When using post__in, we should order by the order of IDs provided.
				$args['orderby'] = 'post__in';
			}
		}

		// Search functionality.
		if ( $request->get_param( 'search' ) ) {
			$args['s'] = $request->get_param( 'search' );
		}

		// Order by.
		if ( $request->get_param( 'orderby' ) ) {
			$args['orderby'] = $request->get_param( 'orderby' );
		}

		// Order direction.
		if ( $request->get_param( 'order' ) ) {
			$args['order'] = $request->get_param( 'order' );
		}

		$lessons_query = new WP_Query( $args );
		$lessons       = array();

		if ( $lessons_query->have_posts() ) {
			while ( $lessons_query->have_posts() ) {
				$lessons_query->the_post();
				$lesson = get_post();

				$lessons[] = $this->prepare_response_for_collection(
					$this->prepare_lesson_for_response( $lesson, $request )
				);
			}
		}

		wp_reset_postdata();

		$response = rest_ensure_response( $lessons );
		$response->header( 'X-WP-Total', (int) $lessons_query->found_posts );
		$response->header( 'X-WP-TotalPages', (int) $lessons_query->max_num_pages );

		/**
		 * Fires after a list of lessons response is prepared via the REST API.
		 *
		 * @param WP_REST_Response $response The response data.
		 * @param WP_REST_Request  $request  The request sent to the API.
		 */
		do_action( 'splms_rest_lesson_items_response', $response, $request );

		return $response;
	}

	/**
	 * Get single lesson.
	 *
	 * Retrieves a single lesson by ID with all associated metadata.
	 *
	 * @since 1.0.0
	 *
	 * @api {get} /splms/v1/lessons/:id Get Lesson
	 * @apiName GetLesson
	 * @apiGroup Lessons
	 * @apiVersion 1.0.0
	 *
	 * @apiDescription Retrieve a single lesson by ID with all metadata.
	 * Requires edit_posts capability.
	 *
	 * @apiParam {Number} id Lesson unique identifier.
	 *
	 * @apiError (Error 404) lesson_not_found Lesson not found.
	 * @apiError (Error 403) rest_forbidden Insufficient permissions to access lesson.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_lesson( $request ) {
		$lesson_id = $request->get_param( 'id' );
		$lesson    = get_post( $lesson_id );

		if ( ! $lesson || SPLMS_POST_TYPES['lesson'] !== $lesson->post_type ) {
			return new WP_Error( 'lesson_not_found', __( 'Lesson not found.', 'skillpulse-lms' ), array( 'status' => 404 ) );
		}

		$response = $this->prepare_lesson_for_response( $lesson, $request );

		/**
		 * Fires after a lesson response is prepared via the REST API.
		 *
		 * @param WP_REST_Response $response The response data.
		 * @param WP_Post          $lesson   Lesson post object.
		 * @param WP_REST_Request  $request  The request sent to the API.
		 */
		do_action( 'splms_rest_lesson_item_response', $response, $lesson, $request );

		return $response;
	}

	/**
	 * Create lesson.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function create_lesson( $request ) {
		$lesson_data = array(
			'post_type'    => SPLMS_POST_TYPES['lesson'],
			'post_title'   => $request->get_param( 'title' ),
			'post_content' => $request->get_param( 'content' ),
			'post_status'  => $request->get_param( 'status' ) ? $request->get_param( 'status' ) : 'draft',
			'post_parent'  => $request->get_param( 'course_id' ) ? $request->get_param( 'course_id' ) : 0,
		);

		$lesson_id = wp_insert_post( $lesson_data );

		if ( is_wp_error( $lesson_id ) ) {
			return new WP_Error( 'lesson_creation_failed', __( 'Failed to create lesson.', 'skillpulse-lms' ), array( 'status' => 500 ) );
		}

		// Save lesson meta.
		$this->save_lesson_meta( $lesson_id, $request );

		$lesson   = get_post( $lesson_id );
		$response = $this->prepare_lesson_for_response( $lesson, $request );

		return rest_ensure_response( $response );
	}

	/**
	 * Update lesson.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function update_lesson( $request ) {
		$lesson_id = $request->get_param( 'id' );
		$lesson    = get_post( $lesson_id );

		if ( ! $lesson || SPLMS_POST_TYPES['lesson'] !== $lesson->post_type ) {
			return new WP_Error( 'lesson_not_found', __( 'Lesson not found.', 'skillpulse-lms' ), array( 'status' => 404 ) );
		}

		$lesson_data = array(
			'ID' => $lesson_id,
		);

		if ( $request->get_param( 'title' ) ) {
			$lesson_data['post_title'] = $request->get_param( 'title' );
		}

		if ( $request->get_param( 'content' ) ) {
			$lesson_data['post_content'] = $request->get_param( 'content' );
		}

		if ( $request->get_param( 'status' ) ) {
			$lesson_data['post_status'] = $request->get_param( 'status' );
		}

		if ( $request->get_param( 'course_id' ) ) {
			$lesson_data['post_parent'] = $request->get_param( 'course_id' );
		}

		$result = wp_update_post( $lesson_data );

		if ( is_wp_error( $result ) ) {
			return new WP_Error( 'lesson_update_failed', __( 'Failed to update lesson.', 'skillpulse-lms' ), array( 'status' => 500 ) );
		}

		// Save lesson meta.
		$this->save_lesson_meta( $lesson_id, $request );

		$updated_lesson = get_post( $lesson_id );
		$response       = $this->prepare_lesson_for_response( $updated_lesson, $request );

		return rest_ensure_response( $response );
	}

	/**
	 * Delete lesson.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function delete_lesson( $request ) {
		$lesson_id = $request->get_param( 'id' );
		$lesson    = get_post( $lesson_id );

		if ( ! $lesson || SPLMS_POST_TYPES['lesson'] !== $lesson->post_type ) {
			return new WP_Error( 'lesson_not_found', __( 'Lesson not found.', 'skillpulse-lms' ), array( 'status' => 404 ) );
		}

		$result = wp_delete_post( $lesson_id, true );

		if ( ! $result ) {
			return new WP_Error( 'lesson_deletion_failed', __( 'Failed to delete lesson.', 'skillpulse-lms' ), array( 'status' => 500 ) );
		}

		return rest_ensure_response( array( 'success' => true ) );
	}


	/**
	 * Save lesson meta.
	 *
	 * @since 1.0.0
	 *
	 * @param int             $lesson_id Lesson ID.
	 * @param WP_REST_Request $request   Full details about the request.
	 *
	 * @return void
	 */
	private function save_lesson_meta( $lesson_id, $request ) {
		$meta_fields = array(
			'lesson_type',
			'lesson_duration',
			'lesson_video_url',
			'lesson_attachments',
			'lesson_prerequisites',
			'lesson_drip_settings',
			'lesson_completion_settings',
		);

		foreach ( $meta_fields as $field ) {
			if ( $request->get_param( $field ) !== null ) {
				update_post_meta( $lesson_id, '_splms_' . $field, $request->get_param( $field ) );
			}
		}
	}

	/**
	 * Prepare lesson for response.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Post         $lesson  Lesson post object.
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response Response object.
	 */
	public function prepare_lesson_for_response( $lesson, $request ) {
		$GLOBALS['post'] = $lesson; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		setup_postdata( $lesson );

		$is_single_request = ! empty( $request->get_param( 'id' ) ) && absint( $request->get_param( 'id' ) ) === $lesson->ID;
		$user_id           = is_user_logged_in() ? get_current_user_id() : 0;
		$lesson_id         = $lesson->ID;

		// Get lesson settings only for single requests (performance optimization).
		if ( $is_single_request ) {
			$lesson_settings = splms_get_lesson_settings( $lesson_id );
		} else {
			$lesson_settings = array();
		}

		// Base fields for every lesson.
		$data = array(
			'id'             => $lesson_id,
			'title'          => array(
				'raw'      => $lesson->post_title,
				'rendered' => get_the_title( $lesson_id ),
			),
			'slug'           => $lesson->post_name,
			'link'           => get_permalink( $lesson_id ),
			'date'           => mysql2date( 'c', $lesson->post_date, false ),
			'date_gmt'       => mysql2date( 'c', $lesson->post_date_gmt, false ),
			'modified'       => mysql2date( 'c', $lesson->post_modified, false ),
			'modified_gmt'   => mysql2date( 'c', $lesson->post_modified_gmt, false ),
			'status'         => $lesson->post_status,
			'type'           => $lesson->post_type,
			'featured_media' => (int) get_post_thumbnail_id( $lesson_id ),
			'excerpt'        => array(
				'raw'      => $lesson->post_excerpt,
				'rendered' => apply_filters( 'the_excerpt', $lesson->post_excerpt ), // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Core filter.
			),
			'course_id'      => (int) $lesson->post_parent,
		);

		// Add content (only for single requests - will be filtered by context in schema).
		if ( $is_single_request ) {
			$data['content'] = array(
				'raw'      => $lesson->post_content,
				'rendered' => apply_filters( 'the_content', $lesson->post_content ), // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Core filter.
			);
			// Add block_version if function exists (WordPress core function).
			if ( function_exists( 'block_version' ) ) {
				$data['content']['block_version'] = block_version( $lesson->post_content );
			}
		}

		// Lesson type and basic settings (always include).
		if ( $is_single_request ) {
			$data['lesson_type']     = $lesson_settings['lesson_type'] ?? 'text';
			$data['lesson_duration'] = $lesson_settings['lesson_duration'] ?? '';
		} else {
			$minimal_settings        = splms_get_lesson_settings( $lesson_id );
			$data['lesson_type']     = $minimal_settings['lesson_type'] ?? 'text';
			$data['lesson_duration'] = $minimal_settings['lesson_duration'] ?? '';
		}

		// User-specific fields.
		if ( $user_id > 0 ) {
			// Get lesson progress for current user.
			global $wpdb;
			$table_name = esc_sql( $wpdb->prefix . 'splms_lesson_progress' );
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table name is safe, validated constant.
			$progress = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table_name} WHERE lesson_id = %d AND user_id = %d", $lesson_id, $user_id ) );

			if ( $progress ) {
				$data['progress'] = array(
					'percentage'    => floatval( $progress->progress ?? 0 ),
					'completed'     => (bool) ( $progress->is_completed ?? false ),
					'started_at'    => $progress->started_at ?? null,
					'completed_at'  => $progress->completed_at ?? null,
					'last_accessed' => $progress->last_accessed ?? null,
					'time_spent'    => intval( $progress->time_spent ?? 0 ),
				);
			} else {
				$data['progress'] = array(
					'percentage'    => 0,
					'completed'     => false,
					'started_at'    => null,
					'completed_at'  => null,
					'last_accessed' => null,
					'time_spent'    => 0,
				);
			}
		} else {
			$data['progress'] = null;
		}

		// Additional detailed fields (only for single requests - will be filtered by context in schema).
		if ( $is_single_request ) {
			// Lesson video URL.
			$data['video_url'] = $lesson_settings['lesson_video_url'] ?? '';

			// Lesson attachments.
			$data['attachments'] = $lesson_settings['lesson_attachments'] ?? array();

			// Lesson prerequisites.
			$data['prerequisites'] = $lesson_settings['lesson_prerequisites'] ?? array();

			// Lesson drip settings.
			$data['drip_settings'] = $lesson_settings['lesson_drip_settings'] ?? array();

			// Lesson completion settings.
			$data['completion_settings'] = $lesson_settings['lesson_completion_settings'] ?? array();

			// Author/Instructor information.
			$author         = get_userdata( $lesson->post_author );
			$data['author'] = array(
				'id'         => (int) $lesson->post_author,
				'name'       => $author ? $author->display_name : '',
				'slug'       => $author ? $author->user_nicename : '',
				'avatar_url' => $author ? get_avatar_url( $lesson->post_author, array( 'size' => 96 ) ) : '',
			);
		}

		$context = ! empty( $request['context'] ) ? $request['context'] : 'view';
		$data    = $this->add_additional_fields_to_object( $data, $request );
		$data    = $this->filter_response_by_context( $data, $context );

		// Wrap the data in a response object.
		$response = rest_ensure_response( $data );

		// Add links.
		$response->add_links( $this->prepare_links( $lesson ) );

		/**
		 * Filters lesson response.
		 *
		 * @param WP_REST_Response $response Rest response.
		 * @param WP_Post          $lesson   Lesson post object.
		 * @param WP_REST_Request  $request  Request object.
		 */
		return apply_filters( 'splms_rest_prepare_lesson', $response, $lesson, $request );
	}

	/**
	 * Prepare links for the request.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Post $lesson Lesson post object.
	 * @return array Links for the given lesson.
	 */
	protected function prepare_links( $lesson ) {
		$links = array(
			'self'       => array(
				array(
					'href' => rest_url( $this->namespace . '/' . $this->rest_base . '/' . $lesson->ID ),
				),
			),
			'collection' => array(
				array(
					'href' => rest_url( $this->namespace . '/' . $this->rest_base ),
				),
			),
		);

		// Add course link if lesson has a parent course.
		if ( $lesson->post_parent > 0 ) {
			$links['course'] = array(
				array(
					'href'       => rest_url( splms_rest_namespace() . '/' . splms_rest_version() . '/courses/' . $lesson->post_parent ),
					'embeddable' => true,
				),
			);
		}

		return $links;
	}

	/**
	 * Retrieves the lesson schema, conforming to JSON Schema.
	 *
	 * @since 1.0.0
	 *
	 * @return array Item schema data.
	 */
	public function get_item_schema() {
		return $this->get_public_item_schema();
	}

	/**
	 * Retrieves the public lesson schema, conforming to JSON Schema.
	 *
	 * @since 1.0.0
	 *
	 * @return array Item schema data.
	 */
	public function get_public_item_schema() {
		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'lesson',
			'type'       => 'object',
			'properties' => array(
				'id'                  => array(
					'description' => __( 'Unique identifier for the lesson.', 'skillpulse-lms' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'title'               => array(
					'description' => __( 'The title for the lesson.', 'skillpulse-lms' ),
					'type'        => 'object',
					'context'     => array( 'view', 'edit' ),
					'properties'  => array(
						'raw'      => array(
							'description' => __( 'Title for the lesson, as it exists in the database.', 'skillpulse-lms' ),
							'type'        => 'string',
							'context'     => array( 'edit' ),
						),
						'rendered' => array(
							'description' => __( 'HTML title for the lesson, transformed for display.', 'skillpulse-lms' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
							'readonly'    => true,
						),
					),
				),
				'slug'                => array(
					'description' => __( 'An alphanumeric identifier for the lesson unique to its type.', 'skillpulse-lms' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'link'                => array(
					'description' => __( 'URL to the lesson.', 'skillpulse-lms' ),
					'type'        => 'string',
					'format'      => 'uri',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'date'                => array(
					'description' => __( "The date the lesson was published, in the site's timezone.", 'skillpulse-lms' ),
					'type'        => 'string',
					'format'      => 'date-time',
					'context'     => array( 'view', 'edit' ),
				),
				'date_gmt'            => array(
					'description' => __( 'The date the lesson was published, as GMT.', 'skillpulse-lms' ),
					'type'        => 'string',
					'format'      => 'date-time',
					'context'     => array( 'view', 'edit' ),
				),
				'modified'            => array(
					'description' => __( "The date the lesson was last modified, in the site's timezone.", 'skillpulse-lms' ),
					'type'        => 'string',
					'format'      => 'date-time',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'modified_gmt'        => array(
					'description' => __( 'The date the lesson was last modified, as GMT.', 'skillpulse-lms' ),
					'type'        => 'string',
					'format'      => 'date-time',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'status'              => array(
					'description' => __( 'A named status for the lesson.', 'skillpulse-lms' ),
					'type'        => 'string',
					'enum'        => array( 'publish', 'future', 'draft', 'pending', 'private' ),
					'context'     => array( 'view', 'edit' ),
				),
				'type'                => array(
					'description' => __( 'Type of Post for the lesson.', 'skillpulse-lms' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'featured_media'      => array(
					'description' => __( 'The ID of the featured media for the lesson.', 'skillpulse-lms' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit' ),
				),
				'excerpt'             => array(
					'description' => __( 'The excerpt for the lesson.', 'skillpulse-lms' ),
					'type'        => 'object',
					'context'     => array( 'view', 'edit' ),
					'properties'  => array(
						'raw'      => array(
							'description' => __( 'Excerpt for the lesson, as it exists in the database.', 'skillpulse-lms' ),
							'type'        => 'string',
							'context'     => array( 'edit' ),
						),
						'rendered' => array(
							'description' => __( 'HTML excerpt for the lesson, transformed for display.', 'skillpulse-lms' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
							'readonly'    => true,
						),
					),
				),
				'content'             => array(
					'description' => __( 'The content for the lesson.', 'skillpulse-lms' ),
					'type'        => 'object',
					'context'     => array( 'view' ),
					'properties'  => array(
						'raw'      => array(
							'description' => __( 'Content for the lesson, as it exists in the database.', 'skillpulse-lms' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
						),
						'rendered' => array(
							'description' => __( 'HTML content for the lesson, transformed for display.', 'skillpulse-lms' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
						),
					),
					'readonly'    => true,
				),
				'course_id'           => array(
					'description' => __( 'The ID of the parent course.', 'skillpulse-lms' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit' ),
				),
				'lesson_type'         => array(
					'description' => __( 'The type of lesson (text, video, etc.).', 'skillpulse-lms' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'lesson_duration'     => array(
					'description' => __( 'The duration of the lesson.', 'skillpulse-lms' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'progress'            => array(
					'description' => __( 'User progress for this lesson (only for authenticated users).', 'skillpulse-lms' ),
					'type'        => 'object',
					'context'     => array( 'view' ),
					'properties'  => array(
						'percentage'    => array(
							'type'        => 'number',
							'description' => __( 'Progress percentage (0-100).', 'skillpulse-lms' ),
						),
						'completed'     => array(
							'type'        => 'boolean',
							'description' => __( 'Whether the lesson is completed.', 'skillpulse-lms' ),
						),
						'started_at'    => array(
							'type'        => 'string',
							'format'      => 'date-time',
							'description' => __( 'When the lesson was started.', 'skillpulse-lms' ),
						),
						'completed_at'  => array(
							'type'        => 'string',
							'format'      => 'date-time',
							'description' => __( 'When the lesson was completed.', 'skillpulse-lms' ),
						),
						'last_accessed' => array(
							'type'        => 'string',
							'format'      => 'date-time',
							'description' => __( 'Last time the lesson was accessed.', 'skillpulse-lms' ),
						),
						'time_spent'    => array(
							'type'        => 'integer',
							'description' => __( 'Time spent on lesson in seconds.', 'skillpulse-lms' ),
						),
					),
					'readonly'    => true,
				),
				'video_url'           => array(
					'description' => __( 'Video URL for video lessons.', 'skillpulse-lms' ),
					'type'        => 'string',
					'format'      => 'uri',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'attachments'         => array(
					'description' => __( 'Lesson attachments.', 'skillpulse-lms' ),
					'type'        => 'array',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'prerequisites'       => array(
					'description' => __( 'Lesson prerequisites.', 'skillpulse-lms' ),
					'type'        => 'array',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'drip_settings'       => array(
					'description' => __( 'Lesson drip settings.', 'skillpulse-lms' ),
					'type'        => 'object',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'completion_settings' => array(
					'description' => __( 'Lesson completion settings.', 'skillpulse-lms' ),
					'type'        => 'object',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'author'              => array(
					'description' => __( 'Lesson author/instructor information.', 'skillpulse-lms' ),
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
				'description' => __( 'Limit results to lessons belonging to a specific course.', 'skillpulse-lms' ),
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
				'description' => __( 'Limit result set to specific lesson IDs (comma-separated).', 'skillpulse-lms' ),
				'type'        => 'string',
			),
		);
	}

	/**
	 * Check permissions for getting lessons.
	 *
	 * Allows open access by default. Use the 'splms_rest_lessons_permissions_check' filter
	 * to add custom access restrictions.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return bool|WP_Error True if the request has access, WP_Error object otherwise.
	 */
	public function get_lessons_permissions_check( $request ) {
		$user_id = get_current_user_id();

		// Default: allow open access.
		$has_access = true;

		/**
		 * Filter lessons list access permission check.
		 *
		 * Allows developers to add custom access restrictions for the lessons list endpoint.
		 * Return true to allow access, false or WP_Error to deny access.
		 *
		 * @since 1.0.0
		 *
		 * @param bool|WP_Error $has_access Whether the user has access. Default true (open access).
		 * @param int            $user_id    User ID (0 for non-logged-in users).
		 * @param WP_REST_Request $request   Request object.
		 */
		$has_access = apply_filters( 'splms_rest_lessons_permissions_check', $has_access, $user_id, $request );

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
	 * Check permissions for getting single lesson.
	 *
	 * Allows open access by default. Use the 'splms_rest_lesson_permissions_check' filter
	 * to add custom access restrictions. The filter receives access control and guest preview
	 * check results for convenience.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return bool|WP_Error True if the request has access, WP_Error object otherwise.
	 */
	public function get_lesson_permissions_check( $request ) {
		$lesson_id = $request->get_param( 'id' );
		$user_id   = get_current_user_id();

		// Default: allow open access.
		$has_access = true;

		if ( ! $lesson_id ) {
			$has_access = new WP_Error( 'invalid_lesson_id', __( 'Invalid lesson ID.', 'skillpulse-lms' ), array( 'status' => 400 ) );
		}

		if ( ! empty( $user_id ) ) {
			// Check access control for logged-in users.
			$access_control = SkillPulse_LMS_Access_Control::get_instance();
			if ( ! $access_control->user_can_access_lesson( $user_id, $lesson_id ) ) {
				$has_access = new WP_Error( 'rest_forbidden', __( 'Sorry, you are not allowed to access this lesson.', 'skillpulse-lms' ), array( 'status' => rest_authorization_required_code() ) );
			}
		}

		// For non-logged-in users, only allow if guest preview is enabled.
		if ( empty( $user_id ) && ! splms_is_lesson_guest_preview_available( $lesson_id ) ) {
			$has_access = new WP_Error( 'rest_forbidden', __( 'Sorry, you must be logged in to access this lesson.', 'skillpulse-lms' ), array( 'status' => rest_authorization_required_code() ) );
		}

		/**
		 * Filter lesson access permission check.
		 *
		 * Allows developers to add custom access restrictions for lessons.
		 * Return true to allow access, false or WP_Error to deny access.
		 *
		 * Access control and guest preview check results are provided for convenience.
		 * You can use these in your filter to enforce restrictions:
		 * - For logged-in users: use $access_control_result
		 * - For non-logged-in users: use $guest_preview_available
		 *
		 * @since 1.0.0
		 *
		 * @param bool|WP_Error $has_access            Whether the user has access. Default true (open access).
		 * @param int            $lesson_id            Lesson ID.
		 * @param int            $user_id              User ID (0 for non-logged-in users).
		 * @param bool|null      $guest_preview_available Guest preview availability for non-logged-in users (null for logged-in users).
		 * @param WP_REST_Request $request             Request object.
		 */
		$has_access = apply_filters( 'splms_rest_lesson_permissions_check', $has_access, $lesson_id, $user_id, $request );

		// If filter returns WP_Error, return it directly.
		if ( is_wp_error( $has_access ) ) {
			return $has_access;
		}

		// If filter returns false, deny access.
		if ( false === $has_access ) {
			return new WP_Error(
				'rest_forbidden',
				__( 'Sorry, you are not allowed to access this lesson.', 'skillpulse-lms' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		// Allow access (default behavior).
		return true;
	}

	/**
	 * Check permissions for creating lesson.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return bool|WP_Error True if the request has access, WP_Error object otherwise.
	 */
	public function create_lesson_permissions_check( $request ) {
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
	 * Check permissions for updating lesson.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return bool|WP_Error True if the request has access, WP_Error object otherwise.
	 */
	public function update_lesson_permissions_check( $request ) {
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
	 * Check permissions for deleting lesson.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return bool|WP_Error True if the request has access, WP_Error object otherwise.
	 */
	public function delete_lesson_permissions_check( $request ) {
		return current_user_can( 'delete_posts' );
	}
}
