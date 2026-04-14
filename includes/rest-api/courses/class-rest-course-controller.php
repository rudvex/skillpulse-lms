<?php
/**
 * REST API Course Controller
 *
 * Base controller for course REST API endpoints.
 *
 * @package SkillPulse_LMS
 * @since 1.0.0
 *
 * @api
 * Available endpoints:
 * GET    /splms/v1/courses              - List all courses (with access filtering)
 * GET    /splms/v1/courses/{id}          - Get single course details
 * POST   /splms/v1/courses               - Create a new course
 * PUT    /splms/v1/courses/{id}          - Update a course
 * DELETE /splms/v1/courses/{id}          - Delete a course
 * GET    /splms/v1/courses/my-courses     - Get current user's enrolled courses
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST API Course Controller class.
 *
 * @since 1.0.0
 */
class SkillPulse_LMS_REST_Course_Controller extends WP_REST_Controller {

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function __construct() {
		$this->namespace = splms_rest_namespace() . '/' . splms_rest_version();
		$this->rest_base = 'courses';
	}

	/**
	 * Register the routes for the objects of the controller.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register_routes() {
		// List courses.
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

		// Single course.
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
							'description' => __( 'Course ID', 'skillpulse-lms' ),
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
							'description' => __( 'Course ID', 'skillpulse-lms' ),
							'type'        => 'integer',
							'required'    => true,
						),
						'force' => array(
							'type'        => 'boolean',
							'default'     => false,  // Default to trash, not force delete.
							'description' => __( 'Whether to bypass Trash and force deletion.', 'skillpulse-lms' ),
						),
					),
				),
			)
		);

		// Add custom route for user's enrolled courses.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/my-courses',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_my_courses' ),
					'permission_callback' => array( $this, 'get_my_courses_permissions_check' ),
					'args'                => $this->get_collection_params(),
					'schema'              => array( $this, 'get_public_item_schema' ),
				),
			)
		);
	}

	/**
	 * Get items (courses) with user-based filtering.
	 *
	 * Retrieves a collection of courses with automatic filtering based on user access.
	 * Authenticated users see courses they have access to, while public users see only public free courses.
	 *
	 * @since 1.0.0
	 *
	 * @api {get} /splms/v1/courses List Courses
	 * @apiName GetCourses
	 * @apiGroup Courses
	 * @apiVersion 1.0.0
	 *
	 * @apiDescription Retrieve a collection of courses. Results are automatically filtered based on user access.
	 * Authenticated users see courses they're enrolled in or have access to. Public users see only public free courses.
	 *
	 * @apiParam {Number} [page=1] Current page of the collection.
	 * @apiParam {Number} [per_page=10] Maximum number of items to be returned in result set.
	 * @apiParam {String} [search] Limit results to those matching a string.
	 * @apiParam {String} [orderby=date] Sort collection by object attribute.
	 * @apiParam {String} [order=desc] Order sort attribute ascending or descending.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_items( $request ) {
		$user_id = is_user_logged_in() ? get_current_user_id() : 0;

		// Prepare query arguments.
		$args = array(
			'post_type'      => SPLMS_POST_TYPES['course'],
			'post_status'    => 'publish',
			'posts_per_page' => $request->get_param( 'per_page' ) ? absint( $request->get_param( 'per_page' ) ) : 10,
			'paged'          => $request->get_param( 'page' ) ? absint( $request->get_param( 'page' ) ) : 1,
		);

		// Handle search.
		if ( $request->get_param( 'search' ) ) {
			$args['s'] = sanitize_text_field( $request->get_param( 'search' ) );
		}

		// Handle ordering.
		$orderby         = $request->get_param( 'orderby' ) ? sanitize_text_field( $request->get_param( 'orderby' ) ) : 'date';
		$order           = $request->get_param( 'order' ) ? sanitize_text_field( $request->get_param( 'order' ) ) : 'desc';
		$args['orderby'] = $orderby;
		$args['order']   = $order;

		// Handle categories.
		if ( $request->get_param( 'categories' ) ) {
			$categories = is_array( $request->get_param( 'categories' ) )
				? array_map( 'absint', $request->get_param( 'categories' ) )
				: array( absint( $request->get_param( 'categories' ) ) );

			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- Tax query is necessary for category filtering.
			$args['tax_query'] = array(
				array(
					'taxonomy' => 'sp-course-category',
					'field'    => 'term_id',
					'terms'    => $categories,
				),
			);
		}

		// Query courses.
		$query   = new WP_Query( $args );
		$courses = array();

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$course = get_post();

				// Check if user should see this course.
				if ( is_user_logged_in() ) {
					// Authenticated users: show if they have access or are admin.
					if ( $this->user_has_course_access( $user_id, $course->ID ) || current_user_can( 'edit_posts' ) ) {
						$courses[] = $this->prepare_response_for_collection(
							$this->prepare_item_for_response( $course, $request )
						);
					} else {
						// Check if it's a public free course.
						$access_info = splms_get_course_access_info( $course->ID );
						if ( 'public_free' === $access_info['course_access_type'] || ! splms_is_paid_courses_enabled() ) {
							$courses[] = $this->prepare_response_for_collection(
								$this->prepare_item_for_response( $course, $request )
							);
						}
					}
				} else {
					// Non-authenticated users: only show public free courses.
					$access_info = splms_get_course_access_info( $course->ID );
					if ( 'public_free' === $access_info['course_access_type'] || ! splms_is_paid_courses_enabled() ) {
						$courses[] = $this->prepare_response_for_collection(
							$this->prepare_item_for_response( $course, $request )
						);
					}
				}
			}
		}

		wp_reset_postdata();

		$response = rest_ensure_response( $courses );
		$response->header( 'X-WP-Total', $query->found_posts );
		$response->header( 'X-WP-TotalPages', $query->max_num_pages );

		/**
		 * Fires after a list of courses response is prepared via the REST API.
		 *
		 * @param WP_REST_Response $response The response data.
		 * @param WP_REST_Request  $request  The request sent to the API.
		 */
		do_action( 'splms_rest_course_items_response', $response, $request );

		return $response;
	}

	/**
	 * Permission check for listing courses.
	 *
	 * Open by default; filter allows custom restriction.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return bool|WP_Error
	 */
	public function get_items_permissions_check( $request ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found -- signature per REST.
		$user_id    = get_current_user_id();
		$has_access = true;

		/**
		 * Filter courses list access.
		 *
		 * @param bool|WP_Error   $has_access Whether the request is allowed. Default true.
		 * @param int             $user_id    Current user ID.
		 * @param WP_REST_Request $request    Request object.
		 */
		$has_access = apply_filters( 'splms_rest_courses_permissions_check', $has_access, $user_id, $request );

		if ( is_wp_error( $has_access ) ) {
			return $has_access;
		}

		if ( false === $has_access ) {
			return new WP_Error(
				'rest_forbidden',
				__( 'Sorry, you are not allowed to perform this action.', 'skillpulse-lms' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * Get single course item with user access validation.
	 *
	 * Retrieves a single course by ID with access validation.
	 * Returns 403 error if user doesn't have access to the course (unless it's public free).
	 *
	 * @since 1.0.0
	 *
	 * @api {get} /splms/v1/courses/:id Get Course
	 * @apiName GetCourse
	 * @apiGroup Courses
	 * @apiVersion 1.0.0
	 *
	 * @apiDescription Retrieve a single course by ID. Access is validated based on user permissions and course access settings.
	 *
	 * @apiParam {Number} id Course unique identifier.
	 *
	 * @apiError (Error 404) rest_post_invalid_id Invalid course ID.
	 * @apiError (Error 403) rest_cannot_access User does not have access to this course.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_item( $request ) {
		$course_id = (int) $request->get_param( 'id' );
		$course    = get_post( $course_id );

		if ( ! $course || SPLMS_POST_TYPES['course'] !== $course->post_type || 'publish' !== $course->post_status ) {
			return new WP_Error(
				'rest_post_invalid_id',
				__( 'Invalid course ID.', 'skillpulse-lms' ),
				array( 'status' => 404 )
			);
		}

		$user_id = is_user_logged_in() ? get_current_user_id() : 0;

		// Validate access for authenticated users.
		if ( is_user_logged_in() ) {
			if ( ! $this->user_has_course_access( $user_id, $course_id ) && ! current_user_can( 'edit_posts' ) ) {
				$access_info = splms_get_course_access_info( $course_id );
				if ( 'public_free' !== $access_info['course_access_type'] && splms_is_paid_courses_enabled() ) {
					return new WP_Error(
						'rest_cannot_access',
						__( 'You do not have access to this course.', 'skillpulse-lms' ),
						array( 'status' => 403 )
					);
				}
			}
		}

		$response = $this->prepare_item_for_response( $course, $request );

		/**
		 * Fires after a course response is prepared via the REST API.
		 *
		 * @param WP_REST_Response $response The response data.
		 * @param WP_Post          $course   Course post object.
		 * @param WP_REST_Request  $request  The request sent to the API.
		 */
		do_action( 'splms_rest_course_item_response', $response, $course, $request );

		return $response;
	}

	/**
	 * Permission check for single course retrieval.
	 *
	 * Open by default; access is validated inside get_item. Filter allows custom restriction.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return bool|WP_Error
	 */
	public function get_item_permissions_check( $request ) {
		$user_id    = get_current_user_id();
		$has_access = true;

		$has_access = apply_filters( 'splms_rest_course_permissions_check', $has_access, $request->get_param( 'id' ), $user_id, $request );

		if ( is_wp_error( $has_access ) ) {
			return $has_access;
		}

		if ( false === $has_access ) {
			return new WP_Error(
				'rest_forbidden',
				__( 'Sorry, you are not allowed to perform this action.', 'skillpulse-lms' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * Get user's enrolled courses.
	 *
	 * Retrieves a list of courses that the currently authenticated user is enrolled in.
	 * Returns courses with enrollment status and access information.
	 *
	 * @since 1.0.0
	 *
	 * @api {get} /splms/v1/courses/my-courses Get My Enrolled Courses
	 * @apiName GetMyCourses
	 * @apiGroup Courses
	 * @apiVersion 1.0.0
	 *
	 * @apiDescription Retrieve all courses that the authenticated user is enrolled in.
	 * This endpoint requires user authentication and returns only courses the user has access to.
	 *
	 * @apiParam {Number} [page=1] Current page of the collection.
	 * @apiParam {Number} [per_page=10] Maximum number of items to be returned in result set.
	 * @apiParam {String} [search] Limit results to those matching a string.
	 * @apiParam {String} [orderby=date] Sort collection by object attribute.
	 * @apiParam {String} [order=desc] Order sort attribute ascending or descending.
	 *
	 * @apiError (Error 401) rest_not_logged_in User must be logged in.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_my_courses( $request ) {
		$user_id = get_current_user_id();

		if ( ! $user_id ) {
			return new WP_Error(
				'rest_not_logged_in',
				__( 'You must be logged in to view your courses.', 'skillpulse-lms' ),
				array( 'status' => 401 )
			);
		}

		// Get user's enrolled course IDs.
		$enrolled_course_ids = splms_get_user_enrolled_courses( $user_id );

		if ( empty( $enrolled_course_ids ) ) {
			return rest_ensure_response( array() );
		}

		// Get course posts.
		$args = array(
			'post_type'      => SPLMS_POST_TYPES['course'],
			'post__in'       => $enrolled_course_ids,
			'posts_per_page' => $request->get_param( 'per_page' ) ? $request->get_param( 'per_page' ) : 10,
			'paged'          => $request->get_param( 'page' ) ? $request->get_param( 'page' ) : 1,
			'post_status'    => 'publish',
		);

		$query   = new WP_Query( $args );
		$courses = array();

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$course = get_post();

				$courses[] = $this->prepare_response_for_collection(
					$this->prepare_item_for_response( $course, $request )
				);
			}
		}

		wp_reset_postdata();

		$response = rest_ensure_response( $courses );
		$response->header( 'X-WP-Total', $query->found_posts );
		$response->header( 'X-WP-TotalPages', $query->max_num_pages );

		return $response;
	}

	/**
	 * Check permissions for getting user's courses.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return bool|WP_Error True if user is logged in, WP_Error otherwise.
	 */
	public function get_my_courses_permissions_check( $request ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found -- $request is required by REST API callback signature.
		if ( ! is_user_logged_in() ) {
			return new WP_Error(
				'rest_not_logged_in',
				__( 'You must be logged in to view your courses.', 'skillpulse-lms' ),
				array( 'status' => 401 )
			);
		}

		return true;
	}

	/**
	 * Create course.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function create_item( $request ) {
		// Validate and sanitize input parameters.
		$title_param   = $request->get_param( 'title' );
		$content_param = $request->get_param( 'content' );
		$status        = $request->get_param( 'status' );
		$excerpt       = $request->get_param( 'excerpt' );

		// Handle title - can be string or object with 'raw' property.
		if ( is_array( $title_param ) && isset( $title_param['raw'] ) ) {
			$title = $title_param['raw'];
		} elseif ( is_string( $title_param ) ) {
			$title = $title_param;
		} else {
			return new WP_Error( 'invalid_title', __( 'Course title must be a string or object with raw property.', 'skillpulse-lms' ), array( 'status' => 400 ) );
		}

		// Handle content - can be string or object with 'raw' property.
		if ( is_array( $content_param ) && isset( $content_param['raw'] ) ) {
			$content = $content_param['raw'];
		} elseif ( is_string( $content_param ) ) {
			$content = $content_param;
		} else {
			return new WP_Error( 'invalid_content', __( 'Course content must be a string or object with raw property.', 'skillpulse-lms' ), array( 'status' => 400 ) );
		}

		// Ensure status is a string if provided.
		if ( $status && is_array( $status ) ) {
			return new WP_Error( 'invalid_status', __( 'Course status must be a string.', 'skillpulse-lms' ), array( 'status' => 400 ) );
		}

		$course_data = array(
			'post_type'    => SPLMS_POST_TYPES['course'],
			'post_title'   => sanitize_text_field( $title ),
			'post_content' => wp_kses_post( $content ),
			'post_status'  => $status ? sanitize_text_field( $status ) : 'draft',
		);

		if ( $excerpt ) {
			if ( is_array( $excerpt ) ) {
				return new WP_Error( 'invalid_excerpt', __( 'Course excerpt must be a string.', 'skillpulse-lms' ), array( 'status' => 400 ) );
			}
			$course_data['post_excerpt'] = sanitize_textarea_field( $excerpt );
		}

		$course_id = wp_insert_post( $course_data, true );

		if ( is_wp_error( $course_id ) ) {
			return new WP_Error( 'course_creation_failed', __( 'Failed to create course.', 'skillpulse-lms' ), array( 'status' => 500 ) );
		}

		// Set categories if provided.
		if ( $request->get_param( 'categories' ) ) {
			$categories = is_array( $request->get_param( 'categories' ) ) ? $request->get_param( 'categories' ) : array( $request->get_param( 'categories' ) );
			wp_set_post_terms( $course_id, array_map( 'absint', $categories ), 'sp-course-category' );
		}

		// Handle price if provided.
		if ( $request->get_param( 'price' ) !== null ) {
			$price = $request->get_param( 'price' );
			if ( is_numeric( $price ) ) {
				// Get current pricing settings.
				$pricing_settings = get_post_meta( $course_id, '_splms_course_pricing_settings', true );
				if ( ! is_array( $pricing_settings ) ) {
					$pricing_settings = array();
				}

				// Update the course price.
				$pricing_settings['course_price'] = floatval( $price );

				// Save back to post meta.
				update_post_meta( $course_id, '_splms_course_pricing_settings', $pricing_settings );
			}
		}

		// Handle other common course metadata.
		if ( $request->get_param( 'difficulty' ) ) {
			// Get current content settings.
			$content_settings = get_post_meta( $course_id, '_splms_course_content_settings', true );
			if ( ! is_array( $content_settings ) ) {
				$content_settings = array();
			}
			$content_settings['difficulty_level'] = sanitize_text_field( $request->get_param( 'difficulty' ) );
			update_post_meta( $course_id, '_splms_course_content_settings', $content_settings );
		}

		if ( $request->get_param( 'duration' ) ) {
			// Get current content settings.
			$content_settings = get_post_meta( $course_id, '_splms_course_content_settings', true );
			if ( ! is_array( $content_settings ) ) {
				$content_settings = array();
			}
			$content_settings['course_duration'] = sanitize_text_field( $request->get_param( 'duration' ) );
			update_post_meta( $course_id, '_splms_course_content_settings', $content_settings );
		}

		$course   = get_post( $course_id );
		$response = $this->prepare_item_for_response( $course, $request );
		$response = rest_ensure_response( $response );

		// Set 201 Created status for new course.
		$response->set_status( 201 );

		return $response;
	}

	/**
	 * Update course.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function update_item( $request ) {
		$course_id = (int) $request->get_param( 'id' );
		$course    = get_post( $course_id );

		if ( ! $course || SPLMS_POST_TYPES['course'] !== $course->post_type ) {
			return new WP_Error( 'rest_post_invalid_id', __( 'Invalid course ID.', 'skillpulse-lms' ), array( 'status' => 404 ) );
		}

		$course_data = array(
			'ID' => $course_id,
		);

		if ( $request->get_param( 'title' ) ) {
			$course_data['post_title'] = $request->get_param( 'title' );
		}

		if ( $request->get_param( 'content' ) ) {
			$course_data['post_content'] = $request->get_param( 'content' );
		}

		if ( $request->get_param( 'status' ) ) {
			$course_data['post_status'] = $request->get_param( 'status' );
		}

		if ( $request->get_param( 'excerpt' ) ) {
			$course_data['post_excerpt'] = $request->get_param( 'excerpt' );
		}

		$result = wp_update_post( $course_data, true );

		if ( is_wp_error( $result ) ) {
			return new WP_Error( 'course_update_failed', __( 'Failed to update course.', 'skillpulse-lms' ), array( 'status' => 500 ) );
		}

		// Update categories if provided.
		if ( $request->get_param( 'categories' ) ) {
			$categories = is_array( $request->get_param( 'categories' ) ) ? $request->get_param( 'categories' ) : array( $request->get_param( 'categories' ) );
			wp_set_post_terms( $course_id, array_map( 'absint', $categories ), 'sp-course-category' );
		}

		// Handle price if provided.
		if ( $request->get_param( 'price' ) !== null ) {
			$price = $request->get_param( 'price' );
			if ( is_numeric( $price ) ) {
				// Get current pricing settings.
				$pricing_settings = get_post_meta( $course_id, '_splms_course_pricing_settings', true );
				if ( ! is_array( $pricing_settings ) ) {
					$pricing_settings = array();
				}

				// Update the course price.
				$pricing_settings['course_price'] = floatval( $price );

				// Save back to post meta.
				update_post_meta( $course_id, '_splms_course_pricing_settings', $pricing_settings );
			}
		}

		// Handle other common course metadata.
		if ( $request->get_param( 'difficulty' ) ) {
			// Get current content settings.
			$content_settings = get_post_meta( $course_id, '_splms_course_content_settings', true );
			if ( ! is_array( $content_settings ) ) {
				$content_settings = array();
			}
			$content_settings['difficulty_level'] = sanitize_text_field( $request->get_param( 'difficulty' ) );
			update_post_meta( $course_id, '_splms_course_content_settings', $content_settings );
		}

		if ( $request->get_param( 'duration' ) ) {
			// Get current content settings.
			$content_settings = get_post_meta( $course_id, '_splms_course_content_settings', true );
			if ( ! is_array( $content_settings ) ) {
				$content_settings = array();
			}
			$content_settings['course_duration'] = sanitize_text_field( $request->get_param( 'duration' ) );
			update_post_meta( $course_id, '_splms_course_content_settings', $content_settings );
		}

		$updated_course = get_post( $course_id );
		$response       = $this->prepare_item_for_response( $updated_course, $request );

		return rest_ensure_response( $response );
	}

	/**
	 * Delete course.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function delete_item( $request ) {
		$course_id = (int) $request->get_param( 'id' );
		$course    = get_post( $course_id );

		if ( ! $course || SPLMS_POST_TYPES['course'] !== $course->post_type ) {
			return new WP_Error( 'rest_post_invalid_id', __( 'Invalid course ID.', 'skillpulse-lms' ), array( 'status' => 404 ) );
		}

		$force = $request->get_param( 'force' );
		$force = ( null === $force ) ? false : (bool) $force;  // Default to trash, not hard delete.

		// Use wp_trash_post directly for non-forced deletions to ensure proper trash functionality.
		if ( ! $force && post_type_supports( $course->post_type, 'trash' ) && get_post_status( $course_id ) !== 'trash' ) {
			$result = wp_trash_post( $course_id );
		} else {
			$result = wp_delete_post( $course_id, $force );
		}

		if ( ! $result ) {
			return new WP_Error( 'course_deletion_failed', __( 'Failed to delete course.', 'skillpulse-lms' ), array( 'status' => 500 ) );
		}

		return rest_ensure_response( array( 'deleted' => true ) );
	}

	/**
	 * Permission check for create.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return bool|WP_Error
	 */
	public function create_item_permissions_check( $request ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found -- signature per REST.
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
	 * Permission check for update.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return bool|WP_Error
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
	 * Permission check for delete.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return bool|WP_Error
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
	 * Check if user has access to a course.
	 *
	 * @since 1.0.0
	 *
	 * @param int $user_id   User ID.
	 * @param int $course_id Course ID.
	 * @return bool True if user has access, false otherwise.
	 */
	private function user_has_course_access( $user_id, $course_id ) {
		// Check if user is enrolled.
		if ( splms_is_user_enrolled( $course_id, $user_id ) ) {
			return true;
		}

		// Check course access control.
		if ( class_exists( 'SkillPulse_LMS_Access_Control' ) ) {
			$access_control = SkillPulse_LMS_Access_Control::get_instance();
			return $access_control->user_can_access_course( $user_id, $course_id );
		}

		// Fallback: check if course is public free.
		$access_info = splms_get_course_access_info( $course_id );
		return 'public_free' === $access_info['access_type'];
	}

	/**
	 * Get user enrollment status for a course.
	 *
	 * @since 1.0.0
	 *
	 * @param int $user_id   User ID.
	 * @param int $course_id Course ID.
	 * @return string Enrollment status.
	 */
	private function get_user_enrollment_status( $user_id, $course_id ) {
		if ( ! function_exists( 'splms_get_user_enrollment_status' ) ) {
			return 'not_enrolled';
		}

		return splms_get_user_enrollment_status( $course_id, $user_id );
	}


	/**
	 * Prepare a single course output for response.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Post         $post    Course post object.
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response object.
	 */
	public function prepare_item_for_response( $post, $request ) {
		$GLOBALS['post'] = $post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		setup_postdata( $post );

		$is_single_request = ! empty( $request->get_param( 'id' ) ) && absint( $request->get_param( 'id' ) ) === $post->ID;

		// Also treat create/update responses as single requests (they need full data).
		// For POST (create) and PUT/PATCH (update) requests, we always want full response data.
		$method = $request->get_method();
		if ( ! $is_single_request && in_array( $method, array( 'POST', 'PUT', 'PATCH' ), true ) ) {
			$is_single_request = true;
		}

		$fields    = $this->get_fields_for_response( $request );
		$user_id   = is_user_logged_in() ? get_current_user_id() : 0;
		$course_id = $post->ID;

		// Get course access and content info only for single requests (performance optimization).
		if ( $is_single_request ) {
			$access_info  = splms_get_course_access_info( $course_id );
			$content_info = splms_get_course_content_info( $course_id );
			$type_data    = splms_get_course_type_data( $access_info );
		} else {
			// For list view, only get minimal info needed for display.
			$access_info  = array();
			$content_info = array();
			$type_data    = array();
		}

		// Base fields for every course.
		$data = array(
			'id'             => $course_id,
			'title'          => array(
				'raw'      => $post->post_title,
				'rendered' => get_the_title( $course_id ),
			),
			'slug'           => $post->post_name,
			'link'           => get_permalink( $course_id ),
			'date'           => mysql2date( 'c', $post->post_date, false ),
			'date_gmt'       => mysql2date( 'c', $post->post_date_gmt, false ),
			'modified'       => mysql2date( 'c', $post->post_modified, false ),
			'modified_gmt'   => mysql2date( 'c', $post->post_modified_gmt, false ),
			'status'         => $post->post_status,
			'type'           => $post->post_type,
			'featured_media' => (int) get_post_thumbnail_id( $course_id ),
			'excerpt'        => array(
				'raw'      => $post->post_excerpt,
				'rendered' => apply_filters( 'the_excerpt', $post->post_excerpt ),
			),
		);

		// Add content (only for single requests - will be filtered by context in schema).
		if ( $is_single_request ) {
			$data['content'] = array(
				'raw'      => $post->post_content,
				'rendered' => apply_filters( 'the_content', $post->post_content ),
			);
			// Add block_version if function exists (WordPress core function).
			if ( function_exists( 'block_version' ) ) {
				$data['content']['block_version'] = block_version( $post->post_content );
			}
		}

		// Categories and tags.
		$categories                 = get_the_terms( $course_id, 'sp-course-category' );
		$data['sp-course-category'] = $categories && ! is_wp_error( $categories ) ? wp_list_pluck( $categories, 'term_id' ) : array();

		$tags                  = get_the_terms( $course_id, 'sp-course-tag' );
		$data['sp-course-tag'] = $tags && ! is_wp_error( $tags ) ? wp_list_pluck( $tags, 'term_id' ) : array();

		// For list view, get minimal info needed for display.
		if ( ! $is_single_request ) {
			$minimal_access_info  = splms_get_course_access_info( $course_id );
			$minimal_type_data    = splms_get_course_type_data( $minimal_access_info );
			$minimal_content_info = splms_get_course_content_info( $course_id );
		}

		// Price information (always include for list view).
		$currency_symbol = function_exists( 'get_woocommerce_currency_symbol' ) ? get_woocommerce_currency_symbol() : '$';
		if ( $is_single_request ) {
			$data['price'] = array(
				'raw'      => floatval( $access_info['price'] ?? 0 ),
				'final'    => floatval( $access_info['final_price'] ?? 0 ),
				'currency' => $currency_symbol,
				'display'  => $type_data['price_display'] ?? '',
			);
		} else {
			$data['price'] = array(
				'raw'      => floatval( $minimal_access_info['price'] ?? 0 ),
				'final'    => floatval( $minimal_access_info['final_price'] ?? 0 ),
				'currency' => $currency_symbol,
				'display'  => $minimal_type_data['price_display'] ?? '',
			);
		}

		// Course access type and course type (always include).
		if ( $is_single_request ) {
			$data['access_type'] = $access_info['course_access_type'] ?? '';
			$data['course_type'] = $type_data['type'] ?? 'unknown';
		} else {
			$data['access_type'] = $minimal_access_info['course_access_type'] ?? '';
			$data['course_type'] = $minimal_type_data['type'] ?? 'unknown';
		}

		// Difficulty and duration (always include).
		if ( $is_single_request ) {
			$data['difficulty'] = $content_info['difficulty_level'] ?? 'beginner';
			$data['duration']   = array(
				'value' => intval( $content_info['course_duration_value'] ?? 1 ),
				'unit'  => $content_info['course_duration_unit'] ?? 'months',
			);
		} else {
			$data['difficulty'] = $minimal_content_info['difficulty_level'] ?? 'beginner';
			$data['duration']   = array(
				'value' => intval( $minimal_content_info['course_duration_value'] ?? 1 ),
				'unit'  => $minimal_content_info['course_duration_unit'] ?? 'months',
			);
		}

		// User-specific fields.
		if ( $user_id > 0 ) {
			$data['enrollment_status'] = $this->get_user_enrollment_status( $user_id, $course_id );
			$data['has_access']        = $this->user_has_course_access( $user_id, $course_id ) || current_user_can( 'edit_posts' );
			$data['is_wishlisted']     = $this->is_course_in_wishlist( $user_id, $course_id );
		} else {
			$data['enrollment_status'] = 'not_enrolled';
			$data['has_access']        = false;
			$data['is_wishlisted']     = false;
		}

		// Membership restriction information (always include for access checks).
		$membership_info                        = $this->get_membership_restriction_info( $course_id, $user_id );
		$data['requires_membership']            = $membership_info['requires_membership'];
		$data['required_memberships']           = $membership_info['required_memberships'];
		$data['required_memberships_info']      = $membership_info['required_memberships_info'];
		$data['user_has_membership_access']     = $membership_info['user_has_membership_access'];
		$data['membership_restriction_message'] = $membership_info['membership_restriction_message'];

		// Additional detailed fields (only for single requests - will be filtered by context in schema).
		if ( $is_single_request ) {
			// Course content details.
			$data['language']                  = $content_info['course_language'] ?? 'en';
			$data['learning_method']           = $content_info['learning_method'] ?? 'text';
			$data['course_level']              = $content_info['course_level'] ?? 'beginner';
			$data['learning_outcomes']         = $content_info['learning_outcomes'] ?? array();
			$data['prerequisites_description'] = $content_info['prerequisites_description'] ?? '';

			// Certificate information.
			$data['certificate_enabled']     = $content_info['certificate_enabled'] ?? false;
			$data['certificate_template_id'] = $content_info['certificate_template_id'] ?? '';
			$data['completion_criteria']     = $content_info['completion_criteria'] ?? 'all_lessons';
			$data['passing_grade']           = intval( $content_info['passing_grade'] ?? 70 );

			// Pricing details.
			$data['discount'] = array(
				'type'  => $access_info['discount_type'] ?? 'percentage',
				'value' => floatval( $access_info['discount_value'] ?? 0 ),
			);

			// Enrollment and scheduling details.
			$data['enrollment']          = array(
				'start_date' => $access_info['enrollment_start'] ?? '',
				'end_date'   => $access_info['enrollment_end'] ?? '',
			);
			$data['course_delivery']     = $access_info['course_delivery'] ?? 'self_paced';
			$data['course_start_date']   = $access_info['course_start_date'] ?? '';
			$data['live_class_schedule'] = $access_info['live_class_schedule'] ?? '';
			$data['max_enrollment']      = intval( $access_info['max_enrollment'] ?? 0 );

			// Prerequisite course information.
			if ( 'prerequisite_required' === ( $access_info['course_access_type'] ?? '' ) ) {
				$prerequisite_course_id      = $access_info['prerequisite_course'] ?? 0;
				$data['prerequisite_course'] = array(
					'id'    => intval( $prerequisite_course_id ),
					'title' => $prerequisite_course_id > 0 ? get_the_title( $prerequisite_course_id ) : '',
				);
				if ( $user_id > 0 ) {
					$data['prerequisite_course']['completed'] = SkillPulse_LMS_Enrollment::get_instance()->has_user_completed_course( $user_id, $prerequisite_course_id );
				}
			} else {
				$data['prerequisite_course'] = null;
			}

			// Author/Instructor information.
			$author         = get_userdata( $post->post_author );
			$data['author'] = array(
				'id'         => (int) $post->post_author,
				'name'       => $author ? $author->display_name : '',
				'slug'       => $author ? $author->user_nicename : '',
				'avatar_url' => $author ? get_avatar_url( $post->post_author, array( 'size' => 96 ) ) : '',
			);

			// Curriculum statistics.
			$curriculum_stats   = $this->get_course_curriculum_stats( $course_id );
			$data['curriculum'] = $curriculum_stats;

			// Enrolled students count.
			$data['enrolled_students_count'] = splms_get_course_enrollment_count( $course_id );

			// Course settings summary.
			$data['settings'] = array(
				'course_access_type'  => $access_info['course_access_type'] ?? '',
				'course_delivery'     => $access_info['course_delivery'] ?? 'self_paced',
				'max_enrollment'      => intval( $access_info['max_enrollment'] ?? 0 ),
				'certificate_enabled' => $content_info['certificate_enabled'] ?? false,
			);
		}

		$context = ! empty( $request['context'] ) ? $request['context'] : 'view';
		$data    = $this->add_additional_fields_to_object( $data, $request );
		$data    = $this->filter_response_by_context( $data, $context );

		// Add meta field for single course responses as expected by tests.
		if ( $is_single_request ) {
			$data['meta'] = array(
				'author_id' => (int) $post->post_author,
				'permalink' => get_permalink( $course_id ),
				'edit_link' => current_user_can( 'edit_post', $course_id ) ? get_edit_post_link( $course_id, 'rest' ) : null,
			);
		}

		// Wrap the data in a response object.
		$response = rest_ensure_response( $data );

		// Add links.
		$response->add_links( $this->prepare_links( $post ) );

		/**
		 * Filters course response.
		 *
		 * @param WP_REST_Response $response Rest response.
		 * @param WP_Post          $post     Course post object.
		 * @param WP_REST_Request  $request  Request object.
		 */
		return apply_filters( 'splms_rest_prepare_course', $response, $post, $request );
	}

	/**
	 * Prepare links for the request.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Post $post Course post object.
	 * @return array Links for the given course.
	 */
	protected function prepare_links( $post ) {
		$links = array(
			'self'       => array(
				array(
					'href' => rest_url( $this->namespace . '/' . $this->rest_base . '/' . $post->ID ),
				),
			),
			'collection' => array(
				array(
					'href' => rest_url( $this->namespace . '/' . $this->rest_base ),
				),
			),
		);

		return $links;
	}

	/**
	 * Get course curriculum statistics.
	 *
	 * @since 1.0.0
	 *
	 * @param int $course_id Course ID.
	 * @return array Curriculum statistics.
	 */
	private function get_course_curriculum_stats( $course_id ) {
		// Get course curriculum using unified method with stats calculation.
		$curriculum_result = splms_get_course_curriculum( $course_id, null, array( 'include_stats' => true ) );

		// Return stats in expected format.
		$stats = array(
			'sections_count' => isset( $curriculum_result['stats']['total_sections'] ) ? $curriculum_result['stats']['total_sections'] : 0,
			'lessons_count'  => isset( $curriculum_result['stats']['total_lessons'] ) ? $curriculum_result['stats']['total_lessons'] : 0,
			'quizzes_count'  => isset( $curriculum_result['stats']['total_quizzes'] ) ? $curriculum_result['stats']['total_quizzes'] : 0,
			'total_items'    => isset( $curriculum_result['stats']['total_items'] ) ? $curriculum_result['stats']['total_items'] : 0,
		);

		return $stats;
	}

	/**
	 * Check if course is in user's wishlist.
	 *
	 * @since 1.0.0
	 *
	 * @param int $user_id   User ID.
	 * @param int $course_id Course ID.
	 * @return bool True if course is in wishlist, false otherwise.
	 */
	private function is_course_in_wishlist( $user_id, $course_id ) {
		if ( ! $user_id || ! $course_id ) {
			return false;
		}

		$wishlist = get_user_meta( $user_id, '_splms_course_wishlist', true );
		if ( ! is_array( $wishlist ) ) {
			return false;
		}

		$wishlist = array_map( 'intval', $wishlist );
		return in_array( (int) $course_id, $wishlist, true );
	}

	/**
	 * Get membership restriction information for a course.
	 *
	 * @since 1.0.0
	 *
	 * @param int $course_id Course ID.
	 * @param int $user_id    User ID (optional, defaults to current user).
	 * @return array Membership restriction information.
	 */
	private function get_membership_restriction_info( $course_id, $user_id = null ) {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		$course_settings      = splms_get_course_settings( $course_id );
		$access_settings      = isset( $course_settings['course_access_settings'] ) ? $course_settings['course_access_settings'] : array();
		$course_access_type   = isset( $access_settings['course_access_type'] ) ? $access_settings['course_access_type'] : 'public_free';
		$required_memberships = isset( $access_settings['required_memberships'] ) ? $access_settings['required_memberships'] : array();

		// Only check membership for public_free and public_paid courses.
		$requires_membership = ! empty( $required_memberships ) && in_array( $course_access_type, array( 'public_free', 'public_paid' ), true );

		$info = array(
			'requires_membership'            => $requires_membership,
			'required_memberships'           => $requires_membership ? $required_memberships : array(),
			'required_memberships_info'      => array(),
			'user_has_membership_access'     => false,
			'membership_restriction_message' => '',
		);

		if ( ! $requires_membership ) {
			return $info;
		}

		// Get membership details.
		$all_memberships  = splms_get_available_memberships();
		$memberships_info = array();

		foreach ( $required_memberships as $membership_id ) {
			foreach ( $all_memberships as $membership ) {
				if ( isset( $membership['id'] ) && $membership['id'] === $membership_id ) {
					$purchase_url       = splms_get_membership_purchase_url( $membership_id );
					$memberships_info[] = array(
						'id'             => $membership_id,
						'name'           => $membership['name'],
						'integration_id' => isset( $membership['integration_id'] ) ? $membership['integration_id'] : '',
						'purchase_url'   => $purchase_url ? $purchase_url : '',
					);
					break;
				}
			}
		}

		$info['required_memberships_info'] = $memberships_info;

		// Check if user has membership access (only for authenticated users).
		if ( $user_id > 0 ) {
			$info['user_has_membership_access'] = splms_user_has_required_membership( $user_id, $course_id );

			// Generate restriction message if user doesn't have access.
			if ( ! $info['user_has_membership_access'] ) {
				$membership_names = array();
				foreach ( $memberships_info as $membership ) {
					$membership_names[] = $membership['name'];
				}
				if ( ! empty( $membership_names ) ) {
					/* translators: %s: Membership names. */
					$info['membership_restriction_message'] = sprintf(
						/* translators: %s: Membership names. */
						__( 'This course requires one of the following memberships: %s', 'skillpulse-lms' ),
						implode( ', ', $membership_names )
					);
				}
			}
		} else {
			// For non-authenticated users, show general message.
			$membership_names = array();
			foreach ( $memberships_info as $membership ) {
				$membership_names[] = $membership['name'];
			}
			if ( ! empty( $membership_names ) ) {
				/* translators: %s: Membership names. */
				$info['membership_restriction_message'] = sprintf(
					/* translators: %s: Membership names. */
					__( 'This course requires one of the following memberships: %s. Please log in and purchase a membership to access this course.', 'skillpulse-lms' ),
					implode( ', ', $membership_names )
				);
			}
		}

		return $info;
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
			'page'       => array(
				'description' => __( 'Current page of the collection.', 'skillpulse-lms' ),
				'type'        => 'integer',
				'default'     => 1,
				'minimum'     => 1,
			),
			'per_page'   => array(
				'description' => __( 'Maximum number of items to be returned in result set.', 'skillpulse-lms' ),
				'type'        => 'integer',
				'default'     => 10,
				'minimum'     => 1,
				'maximum'     => 100,
			),
			'search'     => array(
				'description' => __( 'Limit results to those matching a string.', 'skillpulse-lms' ),
				'type'        => 'string',
			),
			'orderby'    => array(
				'description' => __( 'Sort collection by object attribute.', 'skillpulse-lms' ),
				'type'        => 'string',
				'default'     => 'date',
				'enum'        => array( 'date', 'title' ),
			),
			'order'      => array(
				'description' => __( 'Order sort attribute ascending or descending.', 'skillpulse-lms' ),
				'type'        => 'string',
				'default'     => 'desc',
				'enum'        => array( 'asc', 'desc' ),
			),
			'categories' => array(
				'description' => __( 'Limit results to courses belonging to specific category IDs.', 'skillpulse-lms' ),
				'type'        => 'array',
				'items'       => array(
					'type' => 'integer',
				),
			),
		);
	}

	/**
	 * Retrieves the course schema, conforming to JSON Schema.
	 *
	 * @since 1.0.0
	 *
	 * @return array Item schema data.
	 */
	public function get_item_schema() {
		return $this->get_public_item_schema();
	}

	/**
	 * Retrieves the public course schema, conforming to JSON Schema.
	 *
	 * @since 1.0.0
	 *
	 * @return array Item schema data.
	 */
	public function get_public_item_schema() {
		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'course',
			'type'       => 'object',
			'properties' => array(
				'id'             => array(
					'description' => __( 'Unique identifier for the course.', 'skillpulse-lms' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'title'          => array(
					'description' => __( 'The title for the course.', 'skillpulse-lms' ),
					'type'        => array( 'string', 'object' ),
					'context'     => array( 'view', 'edit' ),
					'properties'  => array(
						'raw'      => array(
							'description' => __( 'Title for the course, as it exists in the database.', 'skillpulse-lms' ),
							'type'        => 'string',
							'context'     => array( 'edit' ),
						),
						'rendered' => array(
							'description' => __( 'HTML title for the course, transformed for display.', 'skillpulse-lms' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
							'readonly'    => true,
						),
					),
				),
				'slug'           => array(
					'description' => __( 'An alphanumeric identifier for the course unique to its type.', 'skillpulse-lms' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'link'           => array(
					'description' => __( 'URL to the course.', 'skillpulse-lms' ),
					'type'        => 'string',
					'format'      => 'uri',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'date'           => array(
					'description' => __( "The date the course was published, in the site's timezone.", 'skillpulse-lms' ),
					'type'        => 'string',
					'format'      => 'date-time',
					'context'     => array( 'view', 'edit' ),
				),
				'date_gmt'       => array(
					'description' => __( 'The date the course was published, as GMT.', 'skillpulse-lms' ),
					'type'        => 'string',
					'format'      => 'date-time',
					'context'     => array( 'view', 'edit' ),
				),
				'modified'       => array(
					'description' => __( "The date the course was last modified, in the site's timezone.", 'skillpulse-lms' ),
					'type'        => 'string',
					'format'      => 'date-time',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'modified_gmt'   => array(
					'description' => __( 'The date the course was last modified, as GMT.', 'skillpulse-lms' ),
					'type'        => 'string',
					'format'      => 'date-time',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'status'         => array(
					'description' => __( 'A named status for the course.', 'skillpulse-lms' ),
					'type'        => 'string',
					'enum'        => array( 'publish', 'future', 'draft', 'pending', 'private' ),
					'context'     => array( 'view', 'edit' ),
				),
				'type'           => array(
					'description' => __( 'Type of post for the course.', 'skillpulse-lms' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'featured_media' => array(
					'description' => __( 'The ID of the featured media for the course.', 'skillpulse-lms' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit' ),
				),
				'excerpt'        => array(
					'description' => __( 'The excerpt for the course.', 'skillpulse-lms' ),
					'type'        => 'object',
					'context'     => array( 'view', 'edit' ),
					'properties'  => array(
						'raw'      => array(
							'description' => __( 'Excerpt for the course, as it exists in the database.', 'skillpulse-lms' ),
							'type'        => 'string',
							'context'     => array( 'edit' ),
						),
						'rendered' => array(
							'description' => __( 'HTML excerpt for the course, transformed for display.', 'skillpulse-lms' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
							'readonly'    => true,
						),
					),
				),
				'content'        => array(
					'description' => __( 'The content for the course.', 'skillpulse-lms' ),
					'type'        => array( 'string', 'object' ),
					'context'     => array( 'view', 'edit' ),
					'properties'  => array(
						'raw'      => array(
							'description' => __( 'Content for the course, as it exists in the database.', 'skillpulse-lms' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
						),
						'rendered' => array(
							'description' => __( 'HTML content for the course, transformed for display.', 'skillpulse-lms' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
						),
					),
					'readonly'    => true,
				),
			),
		);

		// Custom fields.
		$schema['properties']['enrollment_status'] = array(
			'description' => __( 'User\'s enrollment status for this course.', 'skillpulse-lms' ),
			'type'        => 'string',
			'enum'        => array( 'active', 'completed', 'not_enrolled' ),
			'context'     => array( 'view', 'edit' ),
			'readonly'    => true,
		);

		$schema['properties']['has_access'] = array(
			'description' => __( 'Whether the user has access to this course.', 'skillpulse-lms' ),
			'type'        => 'boolean',
			'context'     => array( 'view', 'edit' ),
			'readonly'    => true,
		);

		$schema['properties']['requires_membership'] = array(
			'description' => __( 'Whether this course requires a membership to access.', 'skillpulse-lms' ),
			'type'        => 'boolean',
			'context'     => array( 'view', 'edit' ),
			'readonly'    => true,
		);

		$schema['properties']['required_memberships'] = array(
			'description' => __( 'Array of required membership IDs for this course.', 'skillpulse-lms' ),
			'type'        => 'array',
			'items'       => array(
				'type' => 'string',
			),
			'context'     => array( 'view', 'edit' ),
			'readonly'    => true,
		);

		$schema['properties']['required_memberships_info'] = array(
			'description' => __( 'Detailed information about required memberships (id, name, purchase_url).', 'skillpulse-lms' ),
			'type'        => 'array',
			'items'       => array(
				'type'       => 'object',
				'properties' => array(
					'id'             => array(
						'type'        => 'string',
						'description' => __( 'Membership ID.', 'skillpulse-lms' ),
					),
					'name'           => array(
						'type'        => 'string',
						'description' => __( 'Membership name.', 'skillpulse-lms' ),
					),
					'integration_id' => array(
						'type'        => 'string',
						'description' => __( 'Integration ID (e.g., memberpress, woocommerce).', 'skillpulse-lms' ),
					),
					'purchase_url'   => array(
						'type'        => 'string',
						'format'      => 'uri',
						'description' => __( 'URL to purchase this membership.', 'skillpulse-lms' ),
					),
				),
			),
			'context'     => array( 'view', 'edit' ),
			'readonly'    => true,
		);

		$schema['properties']['user_has_membership_access'] = array(
			'description' => __( 'Whether the current user has the required membership access (only for authenticated users).', 'skillpulse-lms' ),
			'type'        => 'boolean',
			'context'     => array( 'view', 'edit' ),
			'readonly'    => true,
		);

		$schema['properties']['membership_restriction_message'] = array(
			'description' => __( 'Message explaining membership restriction if user does not have access.', 'skillpulse-lms' ),
			'type'        => 'string',
			'context'     => array( 'view', 'edit' ),
			'readonly'    => true,
		);

		$schema['properties']['price'] = array(
			'description' => __( 'Course price information.', 'skillpulse-lms' ),
			'type'        => 'object',
			'context'     => array( 'view' ),
			'properties'  => array(
				'raw'      => array(
					'type'        => 'number',
					'description' => __( 'Raw price value.', 'skillpulse-lms' ),
				),
				'final'    => array(
					'type'        => 'number',
					'description' => __( 'Final price after discount.', 'skillpulse-lms' ),
				),
				'currency' => array(
					'type'        => 'string',
					'description' => __( 'Currency symbol.', 'skillpulse-lms' ),
				),
				'display'  => array(
					'type'        => 'string',
					'description' => __( 'Formatted price display.', 'skillpulse-lms' ),
				),
			),
			'readonly'    => true,
		);

		$schema['properties']['access_type'] = array(
			'description' => __( 'Course access type (public_free, public_paid, etc.).', 'skillpulse-lms' ),
			'type'        => 'string',
			'context'     => array( 'view', 'edit' ),
			'readonly'    => true,
		);

		$schema['properties']['course_type'] = array(
			'description' => __( 'Simplified course type (free, paid, etc.).', 'skillpulse-lms' ),
			'type'        => 'string',
			'context'     => array( 'view', 'edit' ),
			'readonly'    => true,
		);

		$schema['properties']['difficulty'] = array(
			'description' => __( 'Course difficulty level.', 'skillpulse-lms' ),
			'type'        => 'string',
			'context'     => array( 'view', 'edit' ),
			'readonly'    => true,
		);

		$schema['properties']['duration'] = array(
			'description' => __( 'Course duration information.', 'skillpulse-lms' ),
			'type'        => 'object',
			'context'     => array( 'view' ),
			'properties'  => array(
				'value' => array(
					'type'        => 'integer',
					'description' => __( 'Duration value.', 'skillpulse-lms' ),
				),
				'unit'  => array(
					'type'        => 'string',
					'description' => __( 'Duration unit (days, weeks, months).', 'skillpulse-lms' ),
				),
			),
			'readonly'    => true,
		);

		$schema['properties']['content'] = array(
			'description' => __( 'Course content.', 'skillpulse-lms' ),
			'type'        => 'object',
			'context'     => array( 'view' ),
			'properties'  => array(
				'raw'      => array(
					'type'        => 'string',
					'description' => __( 'Content for the object, as it exists in the database.', 'skillpulse-lms' ),
					'context'     => array( 'view' ),
				),
				'rendered' => array(
					'type'        => 'string',
					'description' => __( 'HTML content for the object, transformed for display.', 'skillpulse-lms' ),
					'context'     => array( 'view' ),
				),
			),
			'readonly'    => true,
		);

		$schema['properties']['language'] = array(
			'description' => __( 'Course language code.', 'skillpulse-lms' ),
			'type'        => 'string',
			'context'     => array( 'view', 'edit' ),
			'readonly'    => true,
		);

		$schema['properties']['learning_method'] = array(
			'description' => __( 'Course learning method.', 'skillpulse-lms' ),
			'type'        => 'string',
			'context'     => array( 'view', 'edit' ),
			'readonly'    => true,
		);

		$schema['properties']['certificate_enabled'] = array(
			'description' => __( 'Whether certificate is enabled for this course.', 'skillpulse-lms' ),
			'type'        => 'boolean',
			'context'     => array( 'view', 'edit' ),
			'readonly'    => true,
		);

		$schema['properties']['is_wishlisted'] = array(
			'description' => __( 'Whether the course is in user\'s wishlist.', 'skillpulse-lms' ),
			'type'        => 'boolean',
			'context'     => array( 'view', 'edit' ),
			'readonly'    => true,
		);

		$schema['properties']['sp-course-category'] = array(
			'description' => __( 'Course category term IDs.', 'skillpulse-lms' ),
			'type'        => 'array',
			'items'       => array( 'type' => 'integer' ),
			'context'     => array( 'view', 'edit' ),
			'readonly'    => true,
		);

		$schema['properties']['sp-course-tag'] = array(
			'description' => __( 'Course tag term IDs.', 'skillpulse-lms' ),
			'type'        => 'array',
			'items'       => array( 'type' => 'integer' ),
			'context'     => array( 'view' ),
			'readonly'    => true,
		);

		// Detailed fields for single course view (public schema).
		$schema['properties']['course_level'] = array(
			'description' => __( 'Course level (beginner, intermediate, advanced).', 'skillpulse-lms' ),
			'type'        => 'string',
			'context'     => array( 'view' ),
			'readonly'    => true,
		);

		$schema['properties']['learning_outcomes'] = array(
			'description' => __( 'Learning outcomes for the course.', 'skillpulse-lms' ),
			'type'        => 'array',
			'items'       => array( 'type' => 'string' ),
			'context'     => array( 'view' ),
			'readonly'    => true,
		);

		$schema['properties']['prerequisites_description'] = array(
			'description' => __( 'Prerequisites description for the course.', 'skillpulse-lms' ),
			'type'        => 'string',
			'context'     => array( 'view' ),
			'readonly'    => true,
		);

		$schema['properties']['certificate_template_id'] = array(
			'description' => __( 'Certificate template ID.', 'skillpulse-lms' ),
			'type'        => 'string',
			'context'     => array( 'view' ),
			'readonly'    => true,
		);

		$schema['properties']['completion_criteria'] = array(
			'description' => __( 'Course completion criteria.', 'skillpulse-lms' ),
			'type'        => 'string',
			'context'     => array( 'view' ),
			'readonly'    => true,
		);

		$schema['properties']['passing_grade'] = array(
			'description' => __( 'Passing grade percentage for course completion.', 'skillpulse-lms' ),
			'type'        => 'integer',
			'context'     => array( 'view' ),
			'readonly'    => true,
		);

		$schema['properties']['discount'] = array(
			'description' => __( 'Course discount information.', 'skillpulse-lms' ),
			'type'        => 'object',
			'context'     => array( 'view' ),
			'properties'  => array(
				'type'  => array(
					'type'        => 'string',
					'description' => __( 'Discount type (percentage or fixed).', 'skillpulse-lms' ),
				),
				'value' => array(
					'type'        => 'number',
					'description' => __( 'Discount value.', 'skillpulse-lms' ),
				),
			),
			'readonly'    => true,
		);

		$schema['properties']['enrollment'] = array(
			'description' => __( 'Enrollment date information.', 'skillpulse-lms' ),
			'type'        => 'object',
			'context'     => array( 'view' ),
			'properties'  => array(
				'start_date' => array(
					'type'        => 'string',
					'format'      => 'date-time',
					'description' => __( 'Enrollment start date.', 'skillpulse-lms' ),
				),
				'end_date'   => array(
					'type'        => 'string',
					'format'      => 'date-time',
					'description' => __( 'Enrollment end date.', 'skillpulse-lms' ),
				),
			),
			'readonly'    => true,
		);

		$schema['properties']['course_delivery'] = array(
			'description' => __( 'Course delivery mode (self_paced, live, etc.).', 'skillpulse-lms' ),
			'type'        => 'string',
			'context'     => array( 'view' ),
			'readonly'    => true,
		);

		$schema['properties']['course_start_date'] = array(
			'description' => __( 'Course start date.', 'skillpulse-lms' ),
			'type'        => 'string',
			'format'      => 'date-time',
			'context'     => array( 'view' ),
			'readonly'    => true,
		);

		$schema['properties']['live_class_schedule'] = array(
			'description' => __( 'Live class schedule information.', 'skillpulse-lms' ),
			'type'        => 'string',
			'context'     => array( 'view' ),
			'readonly'    => true,
		);

		$schema['properties']['max_enrollment'] = array(
			'description' => __( 'Maximum number of students that can enroll.', 'skillpulse-lms' ),
			'type'        => 'integer',
			'context'     => array( 'view' ),
			'readonly'    => true,
		);

		$schema['properties']['prerequisite_course'] = array(
			'description' => __( 'Prerequisite course information.', 'skillpulse-lms' ),
			'type'        => 'object',
			'context'     => array( 'view' ),
			'properties'  => array(
				'id'        => array(
					'type'        => 'integer',
					'description' => __( 'Prerequisite course ID.', 'skillpulse-lms' ),
				),
				'title'     => array(
					'type'        => 'string',
					'description' => __( 'Prerequisite course title.', 'skillpulse-lms' ),
				),
				'completed' => array(
					'type'        => 'boolean',
					'description' => __( 'Whether user has completed prerequisite course.', 'skillpulse-lms' ),
				),
			),
			'readonly'    => true,
		);

		$schema['properties']['author'] = array(
			'description' => __( 'Course author/instructor information.', 'skillpulse-lms' ),
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
		);

		$schema['properties']['curriculum'] = array(
			'description' => __( 'Course curriculum statistics.', 'skillpulse-lms' ),
			'type'        => 'object',
			'context'     => array( 'view' ),
			'properties'  => array(
				'sections_count' => array(
					'type'        => 'integer',
					'description' => __( 'Number of sections in the course.', 'skillpulse-lms' ),
				),
				'lessons_count'  => array(
					'type'        => 'integer',
					'description' => __( 'Number of lessons in the course.', 'skillpulse-lms' ),
				),
				'quizzes_count'  => array(
					'type'        => 'integer',
					'description' => __( 'Number of quizzes in the course.', 'skillpulse-lms' ),
				),
				'total_items'    => array(
					'type'        => 'integer',
					'description' => __( 'Total number of curriculum items (lessons + quizzes).', 'skillpulse-lms' ),
				),
			),
			'readonly'    => true,
		);

		$schema['properties']['enrolled_students_count'] = array(
			'description' => __( 'Number of enrolled students in the course.', 'skillpulse-lms' ),
			'type'        => 'integer',
			'context'     => array( 'view' ),
			'readonly'    => true,
		);

		$schema['properties']['settings'] = array(
			'description' => __( 'Course settings summary.', 'skillpulse-lms' ),
			'type'        => 'object',
			'context'     => array( 'view' ),
			'properties'  => array(
				'course_access_type'  => array(
					'type'        => 'string',
					'description' => __( 'Course access type.', 'skillpulse-lms' ),
				),
				'course_delivery'     => array(
					'type'        => 'string',
					'description' => __( 'Course delivery mode.', 'skillpulse-lms' ),
				),
				'max_enrollment'      => array(
					'type'        => 'integer',
					'description' => __( 'Maximum enrollment limit.', 'skillpulse-lms' ),
				),
				'certificate_enabled' => array(
					'type'        => 'boolean',
					'description' => __( 'Whether certificate is enabled.', 'skillpulse-lms' ),
				),
			),
			'readonly'    => true,
		);

		return $this->add_additional_fields_schema( $schema );
	}
}
