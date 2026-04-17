<?php
/**
 * Enrollments REST API Controller
 *
 * Handles REST API endpoints for course enrollments management operations.
 * Provides endpoints for viewing, creating, updating, and deleting enrollments.
 *
 * @package SkillPulse_LMS
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enrollments REST API Controller class.
 *
 * Provides endpoints to manage course enrollments including listing, retrieval,
 * creation, update, deletion, and bulk actions. Also exposes helper endpoints
 * to fetch courses and users for filtering.
 *
 * @package SkillPulse_LMS
 * @since 1.0.0
 *
 * @api
 * Available endpoints:
 * GET    /splms/v1/enrollments                 - List enrollments
 * GET    /splms/v1/enrollments/{id}            - Get single enrollment
 * POST   /splms/v1/enrollments                 - Create enrollment
 * PUT    /splms/v1/enrollments/{id}            - Update enrollment
 * DELETE /splms/v1/enrollments/{id}            - Delete enrollment
 * POST   /splms/v1/enrollments/bulk            - Bulk actions (activate/deactivate/delete)
 * POST   /splms/v1/enrollments/bulk-activate   - Bulk activate
 * POST   /splms/v1/enrollments/bulk-deactivate - Bulk deactivate
 * POST   /splms/v1/enrollments/bulk-delete     - Bulk delete
 * GET    /splms/v1/courses                     - Helper: list courses for filters
 * GET    /splms/v1/users                       - Helper: list users for filters
 */
class SkillPulse_LMS_Enrollments_REST_Controller extends WP_REST_Controller {

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'enrollments';

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function __construct() {
		$this->namespace = splms_rest_namespace() . '/' . splms_rest_version();
	}

	/**
	 * Register the routes for the objects of the controller.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register_routes() {
		// Get enrollments.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_enrollments' ),
				'permission_callback' => array( $this, 'get_enrollments_permissions_check' ),
				'args'                => $this->get_collection_params(),
			)
		);

		// Get single enrollment.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_enrollment' ),
				'permission_callback' => array( $this, 'get_enrollment_permissions_check' ),
				'args'                => array(
					'id' => array(
						'description' => __( 'Enrollment ID', 'skillpulse-lms' ),
						'type'        => 'integer',
						'required'    => true,
					),
				),
			)
		);

		// Create enrollment.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'create_enrollment' ),
				'permission_callback' => array( $this, 'create_enrollment_permissions_check' ),
				'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
			)
		);

		// Update enrollment.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)',
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'update_enrollment' ),
				'permission_callback' => array( $this, 'update_enrollment_permissions_check' ),
				'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
			)
		);

		// Delete enrollment.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)',
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( $this, 'delete_enrollment' ),
				'permission_callback' => array( $this, 'delete_enrollment_permissions_check' ),
				'args'                => array(
					'id' => array(
						'description' => __( 'Enrollment ID', 'skillpulse-lms' ),
						'type'        => 'integer',
						'required'    => true,
					),
				),
			)
		);

		// Bulk actions.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/bulk',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'bulk_actions' ),
				'permission_callback' => array( $this, 'bulk_actions_permissions_check' ),
				'args'                => array(
					'enrollment_ids' => array(
						'description' => __( 'Array of enrollment IDs', 'skillpulse-lms' ),
						'type'        => 'array',
						'items'       => array( 'type' => 'integer' ),
						'required'    => true,
					),
					'action'         => array(
						'description' => __( 'Bulk action', 'skillpulse-lms' ),
						'type'        => 'string',
						'enum'        => array( 'activate', 'deactivate', 'delete' ),
					),
				),
			)
		);

		// Bulk actions.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/bulk-activate',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'bulk_activate' ),
				'permission_callback' => array( $this, 'bulk_actions_permissions_check' ),
				'args'                => array(
					'enrollment_ids' => array(
						'description' => __( 'Array of enrollment IDs', 'skillpulse-lms' ),
						'type'        => 'array',
						'items'       => array( 'type' => 'integer' ),
						'required'    => true,
					),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/bulk-deactivate',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'bulk_deactivate' ),
				'permission_callback' => array( $this, 'bulk_actions_permissions_check' ),
				'args'                => array(
					'enrollment_ids' => array(
						'description' => __( 'Array of enrollment IDs', 'skillpulse-lms' ),
						'type'        => 'array',
						'items'       => array( 'type' => 'integer' ),
						'required'    => true,
					),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/bulk-delete',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'bulk_delete' ),
				'permission_callback' => array( $this, 'bulk_actions_permissions_check' ),
				'args'                => array(
					'enrollment_ids' => array(
						'description' => __( 'Array of enrollment IDs', 'skillpulse-lms' ),
						'type'        => 'array',
						'items'       => array( 'type' => 'integer' ),
						'required'    => true,
					),
				),
			)
		);

		// Send reminder emails.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/send-reminder',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'send_reminder_emails' ),
				'permission_callback' => array( $this, 'bulk_actions_permissions_check' ),
				'args'                => array(
					'enrollment_ids' => array(
						'description' => __( 'Array of enrollment IDs', 'skillpulse-lms' ),
						'type'        => 'array',
						'items'       => array( 'type' => 'integer' ),
						'required'    => true,
					),
				),
			)
		);

		// Get courses for filter.
		register_rest_route(
			$this->namespace,
			'/courses',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_courses' ),
				'permission_callback' => array( $this, 'get_courses_permissions_check' ),
			)
		);

		// Get lessons progress for enrollment.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>\d+)/lessons',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_enrollment_lessons' ),
				'permission_callback' => array( $this, 'get_enrollment_permissions_check' ),
				'args'                => array(
					'id' => array(
						'description'       => __( 'Enrollment ID', 'skillpulse-lms' ),
						'type'              => 'integer',
						'required'          => true,
						'validate_callback' => function ( $param, $request, $key ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- $request and $key are required by REST API callback signature.
							return is_numeric( $param );
						},
						'sanitize_callback' => 'absint',
					),
				),
			)
		);

		// Get activity timeline for enrollment.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>\d+)/activity',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_enrollment_activity' ),
				'permission_callback' => array( $this, 'get_enrollment_permissions_check' ),
				'args'                => array(
					'id' => array(
						'description'       => __( 'Enrollment ID', 'skillpulse-lms' ),
						'type'              => 'integer',
						'required'          => true,
						'validate_callback' => function ( $param, $request, $key ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- $request and $key are required by REST API callback signature.
							return is_numeric( $param );
						},
						'sanitize_callback' => 'absint',
					),
				),
			)
		);

		// Renew enrollment.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>\d+)/renew',
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'renew_enrollment' ),
				'permission_callback' => array( $this, 'update_enrollment_permissions_check' ),
				'args'                => array(
					'id'              => array(
						'description'       => __( 'Enrollment ID', 'skillpulse-lms' ),
						'type'              => 'integer',
						'required'          => true,
						'validate_callback' => function ( $param, $request, $key ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- $request and $key are required by REST API callback signature.
							return is_numeric( $param );
						},
						'sanitize_callback' => 'absint',
					),
					'additional_days' => array(
						'description'       => __( 'Number of days to extend access (0 to recalculate from course settings)', 'skillpulse-lms' ),
						'type'              => 'integer',
						'default'           => 0,
						'minimum'           => 0,
						'sanitize_callback' => 'absint',
					),
				),
			)
		);

		// Get users for filter.
		register_rest_route(
			$this->namespace,
			'/users',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_users' ),
				'permission_callback' => array( $this, 'get_users_permissions_check' ),
			)
		);
	}

	/**
	 * Get enrollments.
	 *
	 * Retrieves a collection of course enrollments with advanced filtering,
	 * search, sorting, and pagination support.
	 *
	 * @since 1.0.0
	 *
	 * @api {get} /splms/v1/enrollments List Enrollments
	 * @apiName GetEnrollments
	 * @apiGroup Enrollments
	 * @apiVersion 1.0.0
	 *
	 * @apiDescription Retrieve a collection of course enrollments with filtering and pagination.
	 * Requires manage_options capability. Supports filtering by course, user, status, enrollment method,
	 * and date range. Includes real-time progress data.
	 *
	 * @apiParam {String} [search] Search term for user name, email, or course title.
	 * @apiParam {Number} [course_id] Filter by course ID.
	 * @apiParam {String} [status] Filter by enrollment status (active, inactive, completed, cancelled, suspended).
	 * @apiParam {Number} [user_id] Filter by user ID.
	 * @apiParam {String} [enrollment_method] Filter by enrollment method (manual, self_enrolled, purchase, admin, bulk_import).
	 * @apiParam {String} [date_start] Start date for enrollment date range filter (YYYY-MM-DD).
	 * @apiParam {String} [date_end] End date for enrollment date range filter (YYYY-MM-DD).
	 * @apiParam {String} [sort_by=enrolled_at] Sort by field (enrolled_at, completed_at, progress, user_name, course_title).
	 * @apiParam {String} [sort_order=desc] Sort order (asc, desc).
	 * @apiParam {Number} [per_page=20] Number of items per page.
	 * @apiParam {Number} [page=1] Page number.
	 *
	 * @apiError (Error 403) rest_forbidden Insufficient permissions to access enrollments.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_enrollments( $request ) {
		global $wpdb;

		$table_name = esc_sql( $wpdb->prefix . 'splms_enrollments' );

		// Get parameters.
		$search            = $request->get_param( 'search' );
		$course_id         = $request->get_param( 'course_id' );
		$status            = $request->get_param( 'status' );
		$user_id           = $request->get_param( 'user_id' );
		$enrollment_method = $request->get_param( 'enrollment_method' );
		$date_start        = $request->get_param( 'date_start' );
		$date_end          = $request->get_param( 'date_end' );
		$sort_by           = $request->get_param( 'sort_by' ) ? $request->get_param( 'sort_by' ) : 'enrolled_at';
		$sort_order        = $request->get_param( 'sort_order' ) ? $request->get_param( 'sort_order' ) : 'desc';
		$per_page          = $request->get_param( 'per_page' ) ? $request->get_param( 'per_page' ) : 20;
		$page              = $request->get_param( 'page' ) ? $request->get_param( 'page' ) : 1;

		// Build the query.
		$query = "
            SELECT e.*, 
                   u.display_name as user_name,
                   u.user_email,
                   p.post_title as course_title
            FROM {$table_name} e
            LEFT JOIN {$wpdb->users} u ON e.user_id = u.ID
            LEFT JOIN {$wpdb->posts} p ON e.course_id = p.ID
            WHERE 1=1
        ";

		$where_params = array();

		// Add search condition.
		if ( ! empty( $search ) ) {
			$query         .= ' AND (u.display_name LIKE %s OR u.user_email LIKE %s OR p.post_title LIKE %s)';
			$search_term    = '%' . $wpdb->esc_like( $search ) . '%';
			$where_params[] = $search_term;
			$where_params[] = $search_term;
			$where_params[] = $search_term;
		}

		// Add course filter.
		if ( ! empty( $course_id ) ) {
			$query         .= ' AND e.course_id = %d';
			$where_params[] = (int) $course_id;
		}

		// Add status filter.
		if ( ! empty( $status ) ) {
			$query         .= ' AND e.status = %s';
			$where_params[] = $status;
		}

		// Add user filter.
		if ( ! empty( $user_id ) ) {
			$query         .= ' AND e.user_id = %d';
			$where_params[] = (int) $user_id;
		}

		// Add enrollment method filter.
		if ( ! empty( $enrollment_method ) ) {
			$query         .= ' AND e.enrollment_method = %s';
			$where_params[] = $enrollment_method;
		}

		// Add date range filters.
		if ( ! empty( $date_start ) ) {
			$query         .= ' AND e.enrolled_at >= %s';
			$where_params[] = $date_start . ' 00:00:00';
		}

		if ( ! empty( $date_end ) ) {
			$query         .= ' AND e.enrolled_at <= %s';
			$where_params[] = $date_end . ' 23:59:59';
		}

		// Get total count using proper query building.
		$count_query = "
            SELECT COUNT(e.id)
            FROM {$table_name} e
            LEFT JOIN {$wpdb->users} u ON e.user_id = u.ID
            LEFT JOIN {$wpdb->posts} p ON e.course_id = p.ID
            WHERE 1=1
        ";

		// Apply same WHERE conditions as main query for accurate count.
		if ( ! empty( $search ) ) {
			$count_query .= ' AND (u.display_name LIKE %s OR u.user_email LIKE %s OR p.post_title LIKE %s)';
		}

		if ( ! empty( $course_id ) ) {
			$count_query .= ' AND e.course_id = %d';
		}

		if ( ! empty( $status ) ) {
			$count_query .= ' AND e.status = %s';
		}

		if ( ! empty( $user_id ) ) {
			$count_query .= ' AND e.user_id = %d';
		}

		if ( ! empty( $enrollment_method ) ) {
			$count_query .= ' AND e.enrollment_method = %s';
		}

		if ( ! empty( $date_start ) ) {
			$count_query .= ' AND e.enrolled_at >= %s';
		}

		if ( ! empty( $date_end ) ) {
			$count_query .= ' AND e.enrolled_at <= %s';
		}

		// Execute count query with same parameters.
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- $count_query is built with proper placeholders and prepared.
		$total = $wpdb->get_var( $wpdb->prepare( $count_query, $where_params ) );

		// Add sorting.
		$allowed_sort_columns = array( 'enrolled_at', 'completed_at', 'progress', 'user_name', 'course_title' );
		if ( in_array( $sort_by, $allowed_sort_columns, true ) ) {
			$sort_column = $sort_by;
			if ( 'user_name' === $sort_by ) {
				$sort_column = 'u.display_name';
			} elseif ( 'course_title' === $sort_by ) {
				$sort_column = 'p.post_title';
			} else {
				$sort_column = 'e.' . $sort_by;
			}
			$query .= " ORDER BY {$sort_column} " . ( 'asc' === $sort_order ? 'ASC' : 'DESC' );
		}

		// Add pagination.
		$offset         = ( (int) $page - 1 ) * (int) $per_page;
		$query         .= ' LIMIT %d OFFSET %d';
		$where_params[] = (int) $per_page;
		$where_params[] = (int) $offset;

		// Execute query.
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- $query is built with proper placeholders and prepared.
		$enrollments = $wpdb->get_results( $wpdb->prepare( $query, $where_params ) );

		// Process results.
		$processed_enrollments = array();
		foreach ( $enrollments as $enrollment ) {
			$processed_enrollments[] = $this->prepare_enrollment_for_response( $enrollment );
		}

		$response = array(
			'success' => true,
			'data'    => array(
				'enrollments' => $processed_enrollments,
				'total'       => intval( $total ),
				'page'        => intval( $page ),
				'per_page'    => intval( $per_page ),
				'total_pages' => ceil( $total / $per_page ),
			),
		);

		return rest_ensure_response( $response );
	}

	/**
	 * Get single enrollment.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_enrollment( $request ) {
		$enrollment_id = $request->get_param( 'id' );

		$enrollment = SkillPulse_LMS_Enrollments_Query::get_instance()->get_enrollment_by_id( $enrollment_id );

		if ( ! $enrollment ) {
			return new WP_Error( 'enrollment_not_found', __( 'Enrollment not found.', 'skillpulse-lms' ), array( 'status' => 404 ) );
		}

		$response = array(
			'success' => true,
			'data'    => $this->prepare_enrollment_for_response( $enrollment ),
		);

		return rest_ensure_response( $response );
	}

	/**
	 * Create enrollment.
	 *
	 * Creates a new course enrollment for a user. Validates that the user
	 * and course exist, and checks for duplicate enrollments.
	 *
	 * @since 1.0.0
	 *
	 * @api {post} /splms/v1/enrollments Create Enrollment
	 * @apiName CreateEnrollment
	 * @apiGroup Enrollments
	 * @apiVersion 1.0.0
	 *
	 * @apiDescription Create a new course enrollment for a user.
	 * Requires manage_options capability. Validates user and course existence.
	 *
	 * @apiParam {Number} user_id User ID to enroll.
	 * @apiParam {Number} course_id Course ID to enroll in.
	 * @apiParam {String} [status=active] Enrollment status (active, inactive, completed, cancelled, suspended).
	 *
	 * @apiError (Error 400) invalid_user Invalid user ID.
	 * @apiError (Error 400) invalid_course Invalid course ID.
	 * @apiError (Error 400) enrollment_exists User is already enrolled in this course.
	 * @apiError (Error 403) rest_forbidden Insufficient permissions to create enrollment.
	 * @apiError (Error 500) enrollment_creation_failed Failed to create enrollment.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function create_enrollment( $request ) {
		$user_id   = $request->get_param( 'user_id' );
		$course_id = $request->get_param( 'course_id' );
		$status    = $request->get_param( 'status' ) ? $request->get_param( 'status' ) : 'active';

		// Validate user exists.
		$user = get_user_by( 'id', $user_id );
		if ( ! $user ) {
			return new WP_Error( 'invalid_user', __( 'Invalid user ID.', 'skillpulse-lms' ), array( 'status' => 400 ) );
		}

		// Validate course exists.
		$course = get_post( $course_id );
		if ( ! $course || SPLMS_POST_TYPES['course'] !== $course->post_type ) {
			return new WP_Error( 'invalid_course', __( 'Invalid course ID.', 'skillpulse-lms' ), array( 'status' => 400 ) );
		}

		$enrollments_query = SkillPulse_LMS_Enrollments_Query::get_instance();

		// Check if enrollment already exists.
		$existing_enrollment = $enrollments_query->get_enrollment( $user_id, $course_id );
		if ( $existing_enrollment ) {
			return new WP_Error( 'enrollment_exists', __( 'User is already enrolled in this course.', 'skillpulse-lms' ), array( 'status' => 400 ) );
		}

		// Create enrollment.
		$enrollment_id = $enrollments_query->enroll_user( $user_id, $course_id, $status );

		if ( ! $enrollment_id ) {
			return new WP_Error( 'enrollment_creation_failed', __( 'Failed to create enrollment.', 'skillpulse-lms' ), array( 'status' => 500 ) );
		}

		$enrollment = $enrollments_query->get_enrollment_by_id( $enrollment_id );

		$response = array(
			'success' => true,
			'data'    => $this->prepare_enrollment_for_response( $enrollment ),
		);

		return rest_ensure_response( $response );
	}

	/**
	 * Update enrollment.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function update_enrollment( $request ) {
		$enrollment_id = $request->get_param( 'id' );
		$status        = $request->get_param( 'status' );
		$progress      = $request->get_param( 'progress' );

		$enrollment_handler = SkillPulse_LMS_Enrollments_Query::get_instance();
		$enrollment         = $enrollment_handler->get_enrollment_by_id( $enrollment_id );

		if ( ! $enrollment ) {
			return new WP_Error( 'enrollment_not_found', __( 'Enrollment not found.', 'skillpulse-lms' ), array( 'status' => 404 ) );
		}

		// Prepare update data.
		$update_data = array();
		if ( $status ) {
			$update_data['status'] = $status;
			if ( 'completed' === $status ) {
				$update_data['completed_at'] = current_time( 'mysql' );
			} elseif ( 'cancelled' === $status ) {
				// Clear completion date when cancelling enrollment.
				$update_data['completed_at'] = null;
			}
		}
		if ( null !== $progress ) {
			$update_data['progress'] = $progress;
		}

		// Update enrollment.
		$result = $enrollment_handler->update_enrollment( $enrollment_id, $update_data );

		if ( ! $result ) {
			return new WP_Error( 'enrollment_update_failed', __( 'Failed to update enrollment.', 'skillpulse-lms' ), array( 'status' => 500 ) );
		}

		$updated_enrollment = $enrollment_handler->get_enrollment_by_id( $enrollment_id );

		$response = array(
			'success' => true,
			'data'    => $this->prepare_enrollment_for_response( $updated_enrollment ),
		);

		return rest_ensure_response( $response );
	}

	/**
	 * Delete enrollment.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function delete_enrollment( $request ) {
		$enrollment_id = $request->get_param( 'id' );

		$enrollment_query = SkillPulse_LMS_Enrollments_Query::get_instance();
		$enrollment       = $enrollment_query->get_enrollment_by_id( $enrollment_id );

		if ( ! $enrollment ) {
			return new WP_Error( 'enrollment_not_found', __( 'Enrollment not found.', 'skillpulse-lms' ), array( 'status' => 404 ) );
		}

		$result = $enrollment_query->delete_enrollment( $enrollment_id );

		if ( ! $result ) {
			return new WP_Error( 'enrollment_deletion_failed', __( 'Failed to delete enrollment.', 'skillpulse-lms' ), array( 'status' => 500 ) );
		}

		$response = array(
			'success' => true,
			'data'    => array(
				'message'    => __( 'Enrollment deleted successfully.', 'skillpulse-lms' ),
				'deleted_id' => $enrollment_id,
			),
		);

		return rest_ensure_response( $response );
	}

	/**
	 * Bulk actions dispatcher.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function bulk_actions( $request ) {
		$enrollment_ids = $request->get_param( 'enrollment_ids' );

		if ( ! $enrollment_ids ) {
			return new WP_Error( 'invalid_enrollment_ids', __( 'Invalid enrollment IDs.', 'skillpulse-lms' ), array( 'status' => 400 ) );
		}

		$action = $request->get_param( 'action' );

		if ( ! $action ) {
			return new WP_Error( 'invalid_action', __( 'Invalid action.', 'skillpulse-lms' ), array( 'status' => 400 ) );
		}

		switch ( $action ) {
			case 'activate':
				$response = $this->bulk_activate( $request );
				break;
			case 'deactivate':
				$response = $this->bulk_deactivate( $request );
				break;
			case 'delete':
				$response = $this->bulk_delete( $request );
				break;
			default:
				$response = new WP_Error( 'invalid_action', __( 'Invalid action.', 'skillpulse-lms' ), array( 'status' => 400 ) );
		}

		return rest_ensure_response( $response );
	}

	/**
	 * Bulk activate enrollments.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function bulk_activate( $request ) {
		$enrollment_ids = $request->get_param( 'enrollment_ids' );
		$result         = SkillPulse_LMS_Enrollments_Query::get_instance()->bulk_update_enrollments( $enrollment_ids, array( 'status' => 'active' ) );

		if ( ! $result ) {
			return new WP_Error( 'bulk_activation_failed', __( 'Failed to activate enrollments.', 'skillpulse-lms' ), array( 'status' => 500 ) );
		}

		$response = array(
			'success' => true,
			'data'    => array(
				// translators: %d: The number of enrollments activated.
				'message'      => sprintf( __( 'Successfully activated %d enrollments.', 'skillpulse-lms' ), count( $enrollment_ids ) ),
				'affected_ids' => $enrollment_ids,
			),
		);

		return rest_ensure_response( $response );
	}

	/**
	 * Bulk deactivate enrollments.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function bulk_deactivate( $request ) {
		$enrollment_ids = $request->get_param( 'enrollment_ids' );
		$result         = SkillPulse_LMS_Enrollments_Query::get_instance()->bulk_update_enrollments( $enrollment_ids, array( 'status' => 'inactive' ) );

		if ( ! $result ) {
			return new WP_Error( 'bulk_deactivation_failed', __( 'Failed to deactivate enrollments.', 'skillpulse-lms' ), array( 'status' => 500 ) );
		}

		$response = array(
			'success' => true,
			'data'    => array(
				// translators: %d: The number of enrollments deactivated.
				'message'      => sprintf( __( 'Successfully deactivated %d enrollments.', 'skillpulse-lms' ), count( $enrollment_ids ) ),
				'affected_ids' => $enrollment_ids,
			),
		);

		return rest_ensure_response( $response );
	}

	/**
	 * Bulk delete enrollments.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function bulk_delete( $request ) {
		$enrollment_ids = $request->get_param( 'enrollment_ids' );

		$enrollment_handler = SkillPulse_LMS_Enrollments_Query::get_instance();
		$result             = $enrollment_handler->bulk_delete_enrollments( $enrollment_ids );

		if ( ! $result ) {
			return new WP_Error( 'bulk_deletion_failed', __( 'Failed to delete enrollments.', 'skillpulse-lms' ), array( 'status' => 500 ) );
		}

		$response = array(
			'success' => true,
			'data'    => array(
				// translators: %d: The number of enrollments deleted.
				'message'     => sprintf( __( 'Successfully deleted %d enrollments.', 'skillpulse-lms' ), count( $enrollment_ids ) ),
				'deleted_ids' => $enrollment_ids,
			),
		);

		return rest_ensure_response( $response );
	}

	/**
	 * Get courses for filter dropdown.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_courses( $request ) {
		$courses = get_posts(
			array(
				'post_type'   => SPLMS_POST_TYPES['course'],
				'post_status' => 'publish',
				'numberposts' => - 1,
				'fields'      => 'ids',
			)
		);

		$course_data = array();
		foreach ( $courses as $course_id ) {
			$course_data[] = array(
				'id'    => $course_id,
				'title' => get_the_title( $course_id ),
			);
		}

		$response = array(
			'success' => true,
			'data'    => $course_data,
		);

		return rest_ensure_response( $response );
	}

	/**
	 * Get users for filter dropdown.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_users( $request ) {
		$users = get_users(
			array(
				'role__in' => array( 'student', 'subscriber', 'contributor' ),
				'number'   => 100,
				'fields'   => array( 'ID', 'display_name' ),
			)
		);

		$user_data = array();
		foreach ( $users as $user ) {
			$user_data[] = array(
				'id'   => $user->ID,
				'name' => $user->display_name,
			);
		}

		$response = array(
			'success' => true,
			'data'    => $user_data,
		);

		return rest_ensure_response( $response );
	}

	/**
	 * Prepare enrollment for response.
	 *
	 * @since 1.0.0
	 *
	 * @param object $enrollment Enrollment object.
	 *
	 * @return array Formatted enrollment data.
	 */
	private function prepare_enrollment_for_response( $enrollment ) {
		$user   = get_user_by( 'id', $enrollment->user_id );
		$course = get_post( $enrollment->course_id );

		// Get real-time progress from lessons module (source of truth).
		$lessons_instance = SkillPulse_LMS_Lessons::get_instance();
		$progress_data    = array();
		if ( $lessons_instance ) {
			$progress_data = $lessons_instance->calculate_course_progress( $enrollment->user_id, $enrollment->course_id );
		}

		$actual_progress   = floatval( $progress_data['percentage'] ?? 0 );
		$completed_lessons = intval( $progress_data['completed_lessons'] ?? 0 );
		$passed_quizzes    = intval( $progress_data['passed_quizzes'] ?? 0 );
		$total_items       = intval( $progress_data['total_items'] ?? 0 );

		// Calculate totals by type.
		$total_lessons = $this->get_course_lesson_count( $enrollment->course_id );
		$total_quizzes = $this->get_course_quiz_count( $enrollment->course_id );

		// Get last activity from database.
		$last_activity = $this->get_user_last_activity( $enrollment->user_id, $enrollment->course_id );

		return array(
			'id'                 => intval( $enrollment->id ),
			'user_id'            => intval( $enrollment->user_id ),
			'user_name'          => $user ? $user->display_name : __( 'Unknown User', 'skillpulse-lms' ),
			'user_email'         => $user ? $user->user_email : '',
			'user_avatar'        => $user ? get_avatar_url( $user->ID, array( 'size' => 40 ) ) : '',
			'user_role'          => $user ? implode( ', ', $user->roles ) : '',
			'course_id'          => intval( $enrollment->course_id ),
			'course_title'       => $course ? $course->post_title : __( 'Unknown Course', 'skillpulse-lms' ),
			'enrolled_at'        => $enrollment->enrolled_at,
			'completed_at'       => $enrollment->completed_at,
			'status'             => $enrollment->status,
			'progress'           => $actual_progress,
			'enrollment_method'  => $this->format_enrollment_method( $enrollment->enrollment_method ),
			// Real-time progress data.
			'lessons_completed'  => $completed_lessons,
			'total_lessons'      => $total_lessons,
			'quizzes_passed'     => $passed_quizzes,
			'total_quizzes'      => $total_quizzes,
			'total_items'        => $total_items,
			'completed_items'    => $completed_lessons + $passed_quizzes,
			'last_activity'      => $last_activity,
			// Calculated fields.
			'time_spent'         => $this->get_user_time_spent( $enrollment->user_id, $enrollment->course_id ),
			'certificate_earned' => $actual_progress >= 100,
			'certificate_url'    => $actual_progress >= 100 ? $this->get_certificate_url( $enrollment->user_id, $enrollment->course_id ) : '',
			// Additional fields.
			'order_id'           => $this->get_enrollment_order_id( $enrollment->id ),
			// Expiration info.
			'access_expires'     => $enrollment->access_expires ?? null,
			'expiration_info'    => splms_get_enrollment_expiration_info( $enrollment->user_id, $enrollment->course_id ),
		);
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
			'search'            => array(
				'description' => __( 'Search term.', 'skillpulse-lms' ),
				'type'        => 'string',
			),
			'course_id'         => array(
				'description' => __( 'Course ID to filter by.', 'skillpulse-lms' ),
				'type'        => 'integer',
			),
			'status'            => array(
				'description' => __( 'Enrollment status to filter by.', 'skillpulse-lms' ),
				'type'        => 'string',
				'enum'        => array( 'active', 'inactive', 'completed', 'cancelled', 'suspended' ),
			),
			'user_id'           => array(
				'description' => __( 'User ID to filter by.', 'skillpulse-lms' ),
				'type'        => 'integer',
			),
			'enrollment_method' => array(
				'description' => __( 'Enrollment method to filter by.', 'skillpulse-lms' ),
				'type'        => 'string',
				'enum'        => array( 'manual', 'self_enrolled', 'purchase', 'admin', 'bulk_import' ),
			),
			'date_start'        => array(
				'description' => __( 'Start date for enrollment date range filter.', 'skillpulse-lms' ),
				'type'        => 'string',
				'format'      => 'date',
			),
			'date_end'          => array(
				'description' => __( 'End date for enrollment date range filter.', 'skillpulse-lms' ),
				'type'        => 'string',
				'format'      => 'date',
			),
			'sort_by'           => array(
				'description' => __( 'Sort by field.', 'skillpulse-lms' ),
				'type'        => 'string',
				'default'     => 'enrolled_at',
				'enum'        => array( 'enrolled_at', 'completed_at', 'progress', 'user_name', 'course_title' ),
			),
			'sort_order'        => array(
				'description' => __( 'Sort order.', 'skillpulse-lms' ),
				'type'        => 'string',
				'default'     => 'desc',
				'enum'        => array( 'asc', 'desc' ),
			),
			'per_page'          => array(
				'description' => __( 'Number of items per page.', 'skillpulse-lms' ),
				'type'        => 'integer',
				'default'     => 20,
				'minimum'     => 1,
				'maximum'     => 100,
			),
			'page'              => array(
				'description' => __( 'Page number.', 'skillpulse-lms' ),
				'type'        => 'integer',
				'default'     => 1,
				'minimum'     => 1,
			),
		);
	}

	/**
	 * Check permissions for getting enrollments.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return bool|WP_Error True if the request has access, WP_Error object otherwise.
	 */
	public function get_enrollments_permissions_check( $request ) {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Check permissions for getting single enrollment.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return bool|WP_Error True if the request has access, WP_Error object otherwise.
	 */
	public function get_enrollment_permissions_check( $request ) {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Check permissions for creating enrollment.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return bool|WP_Error True if the request has access, WP_Error object otherwise.
	 */
	public function create_enrollment_permissions_check( $request ) {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Check permissions for updating enrollment.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return bool|WP_Error True if the request has access, WP_Error object otherwise.
	 */
	public function update_enrollment_permissions_check( $request ) {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Check permissions for deleting enrollment.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return bool|WP_Error True if the request has access, WP_Error object otherwise.
	 */
	public function delete_enrollment_permissions_check( $request ) {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Check permissions for bulk actions.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return bool|WP_Error True if the request has access, WP_Error object otherwise.
	 */
	public function bulk_actions_permissions_check( $request ) {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Check permissions for getting courses.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return bool|WP_Error True if the request has access, WP_Error object otherwise.
	 */
	public function get_courses_permissions_check( $request ) {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Check permissions for getting users.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return bool|WP_Error True if the request has access, WP_Error object otherwise.
	 */
	public function get_users_permissions_check( $request ) {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Get course lesson count.
	 *
	 * @since 1.0.0
	 *
	 * @param int $course_id Course ID.
	 *
	 * @return int Lesson count.
	 */
	private function get_course_lesson_count( $course_id ) {
		$lessons_instance = SkillPulse_LMS_Lessons::get_instance();
		if ( ! $lessons_instance ) {
			return 0;
		}
		$lessons = $lessons_instance->get_course_lessons( $course_id );

		return count( $lessons );
	}

	/**
	 * Get course quiz count.
	 *
	 * @since 1.0.0
	 *
	 * @param int $course_id Course ID.
	 *
	 * @return int Quiz count.
	 */
	private function get_course_quiz_count( $course_id ) {
		// Get course curriculum using unified method.
		$curriculum_result = splms_get_course_curriculum( $course_id );

		$quiz_count = 0;

		// Count quizzes from curriculum.
		if ( isset( $curriculum_result['sections'] ) ) {
			foreach ( $curriculum_result['sections'] as $section ) {
				if ( isset( $section['children'] ) ) {
					foreach ( $section['children'] as $child ) {
						if ( SPLMS_POST_TYPES['quiz'] === $child['type'] ) {
							++$quiz_count;
						}
					}
				}
			}
		}

		return $quiz_count;
	}

	/**
	 * Get user's last activity for a course.
	 *
	 * @since 1.0.0
	 *
	 * @param int $user_id   User ID.
	 * @param int $course_id Course ID.
	 *
	 * @return string Last activity datetime.
	 */
	private function get_user_last_activity( $user_id, $course_id ) {
		global $wpdb;

		// Check lesson progress table (only has completed_at).
		$lesson_progress_table = esc_sql( $wpdb->prefix . 'splms_lesson_progress' );
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table name is safe, validated constant.
		$last_lesson_activity = $wpdb->get_var( $wpdb->prepare( "SELECT MAX(completed_at) FROM {$lesson_progress_table} WHERE user_id = %d AND course_id = %d AND completed_at IS NOT NULL", $user_id, $course_id ) );

		// Check quiz attempts table (has attempt_time).
		$quiz_attempts_table = esc_sql( $wpdb->prefix . 'splms_quiz_attempts' );
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table name is safe, validated constant.
		$last_quiz_activity = $wpdb->get_var( $wpdb->prepare( "SELECT MAX(attempt_time) FROM {$quiz_attempts_table} WHERE user_id = %d AND course_id = %d", $user_id, $course_id ) );

		// Return the most recent activity.
		if ( $last_lesson_activity && $last_quiz_activity ) {
			return max( $last_lesson_activity, $last_quiz_activity );
		} elseif ( $last_lesson_activity ) {
			return $last_lesson_activity;
		} elseif ( $last_quiz_activity ) {
			return $last_quiz_activity;
		}

		return '';
	}

	/**
	 * Get user's total time spent on a course.
	 *
	 * @since 1.0.0
	 *
	 * @param int $user_id   User ID.
	 * @param int $course_id Course ID.
	 *
	 * @return string Formatted time spent.
	 */
	private function get_user_time_spent( $user_id, $course_id ) {
		global $wpdb;

		// Get total time from lesson progress.
		$lesson_progress_table = esc_sql( $wpdb->prefix . 'splms_lesson_progress' );
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table name is safe, validated constant.
		$lesson_time = $wpdb->get_var( $wpdb->prepare( "SELECT SUM(time_spent) FROM {$lesson_progress_table} WHERE user_id = %d AND course_id = %d", $user_id, $course_id ) );

		// Get total time from quiz attempts.
		$quiz_attempts_table = esc_sql( $wpdb->prefix . 'splms_quiz_attempts' );
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table name is safe, validated constant.
		$quiz_time = $wpdb->get_var( $wpdb->prepare( "SELECT SUM(time_taken) FROM {$quiz_attempts_table} WHERE user_id = %d AND course_id = %d", $user_id, $course_id ) );

		$total_seconds = intval( $lesson_time ) + intval( $quiz_time );

		if ( $total_seconds < 60 ) {
			return $total_seconds . ' seconds';
		} elseif ( $total_seconds < 3600 ) {
			return round( $total_seconds / 60, 1 ) . ' minutes';
		} else {
			return round( $total_seconds / 3600, 1 ) . ' hours';
		}
	}

	/**
	 * Format enrollment method for display.
	 *
	 * @since 1.0.0
	 *
	 * @param string $method Raw enrollment method from database.
	 *
	 * @return string Formatted enrollment method.
	 */
	private function format_enrollment_method( $method ) {
		// Handle invalid values.
		if ( empty( $method ) || '0' === $method || 0 === $method ) {
			return 'manual';
		}

		// Map method values to display names.
		$method_map = array(
			'manual'        => __( 'Manual', 'skillpulse-lms' ),
			'self_enrolled' => __( 'Self Enrolled', 'skillpulse-lms' ),
			'purchase'      => __( 'Purchase', 'skillpulse-lms' ),
			'admin'         => __( 'Admin', 'skillpulse-lms' ),
			'bulk_import'   => __( 'Bulk Import', 'skillpulse-lms' ),
		);

		return isset( $method_map[ $method ] ) ? $method_map[ $method ] : ucfirst( $method );
	}

	/**
	 * Get certificate URL for user and course.
	 *
	 * @since 1.0.0
	 *
	 * @param int $user_id   User ID.
	 * @param int $course_id Course ID.
	 *
	 * @return string Certificate URL or empty string.
	 */
	private function get_certificate_url( $user_id, $course_id ) {
		// Check if certificates are enabled.
		if ( ! splms_get_setting( 'enable_certificates', false ) ) {
			return '';
		}

		// Check if certificate class exists.
		if ( ! class_exists( 'SkillPulse_LMS_Certificates' ) ) {
			return '';
		}

		$certificates     = SkillPulse_LMS_Certificates::get_instance();
		$certificate_link = $certificates->get_certificate_link( $user_id, $course_id );

		return ! empty( $certificate_link ) ? $certificate_link : '';
	}

	/**
	 * Get order ID associated with enrollment.
	 *
	 * @since 1.0.0
	 *
	 * @param int $enrollment_id Enrollment ID.
	 *
	 * @return string Order ID or empty string.
	 */
	private function get_enrollment_order_id( $enrollment_id ) {
		global $wpdb;

		if ( ! class_exists( 'SkillPulse_LMS_Orders_Query' ) ) {
			return '';
		}

		// Find order that has this enrollment_id in its meta.
		$meta_table = esc_sql( $wpdb->prefix . 'splms_order_meta' );
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table name is safe, validated constant.
		$sql = "SELECT order_id FROM $meta_table WHERE meta_key = 'enrollment_id' AND meta_value = %s LIMIT 1";
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- $sql is built with table name and proper placeholders.
		$order_id = $wpdb->get_var( $wpdb->prepare( $sql, $enrollment_id ) );

		return $order_id ? $order_id : '';
	}

	/**
	 * Send reminder emails to enrolled users.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function send_reminder_emails( $request ) {
		$enrollment_ids = $request->get_param( 'enrollment_ids' );

		if ( ! is_array( $enrollment_ids ) || empty( $enrollment_ids ) ) {
			return new WP_Error( 'invalid_enrollment_ids', __( 'Invalid enrollment IDs provided.', 'skillpulse-lms' ), array( 'status' => 400 ) );
		}

		// Sanitize enrollment IDs.
		$enrollment_ids = array_map( 'absint', $enrollment_ids );
		$enrollment_ids = array_filter( $enrollment_ids );

		if ( empty( $enrollment_ids ) ) {
			return new WP_Error( 'invalid_enrollment_ids', __( 'No valid enrollment IDs provided.', 'skillpulse-lms' ), array( 'status' => 400 ) );
		}

		// Get enrollment class.
		$enrollment_class = SkillPulse_LMS_Enrollments_Query::get_instance();

		$sent_count   = 0;
		$failed_count = 0;
		$errors       = array();

		foreach ( $enrollment_ids as $enrollment_id ) {
			$enrollment = $enrollment_class->get_enrollment_by_id( $enrollment_id );

			if ( ! $enrollment ) {
				++$failed_count;
				$errors[] = sprintf(
					/* translators: %d: Enrollment ID */
					__( 'Enrollment #%d not found.', 'skillpulse-lms' ),
					$enrollment_id
				);
				continue;
			}

			$user = get_userdata( $enrollment->user_id );
			if ( ! $user || ! $user->user_email ) {
				++$failed_count;
				$errors[] = sprintf(
					/* translators: %d: Enrollment ID */
					__( 'User not found for enrollment #%d.', 'skillpulse-lms' ),
					$enrollment_id
				);
				continue;
			}

			$course = get_post( $enrollment->course_id );
			if ( ! $course ) {
				++$failed_count;
				$errors[] = sprintf(
					/* translators: %d: Enrollment ID */
					__( 'Course not found for enrollment #%d.', 'skillpulse-lms' ),
					$enrollment_id
				);
				continue;
			}

			// Prepare email replacements.
			$replacements = array(
				'user_name'    => $user->display_name,
				'course_title' => $course->post_title,
				'course_url'   => get_permalink( $course->ID ),
				'progress'     => number_format( floatval( $enrollment->progress ), 1 ) . '%',
			);

			// Send email using notification dispatcher with proper template.
			$notification_results = SkillPulse_LMS_Notification_Dispatcher::get_instance()->send_notification(
				$user->user_email,
				'enrollment_reminder',
				$replacements,
				$user->ID
			);

			// Check email result (notification dispatcher returns array with 'email' and 'in_app' keys).
			$email_result = isset( $notification_results['email'] ) ? $notification_results['email'] : null;

			if ( is_wp_error( $email_result ) ) {
				++$failed_count;
				$errors[] = sprintf(
					/* translators: %1$d: Enrollment ID, %2$s: Error message */
					__( 'Failed to send email for enrollment #%1$d: %2$s', 'skillpulse-lms' ),
					$enrollment_id,
					$email_result->get_error_message()
				);
			} else {
				++$sent_count;
			}
		}

		$response_data = array(
			'success'      => true,
			'sent_count'   => $sent_count,
			'failed_count' => $failed_count,
			'total_count'  => count( $enrollment_ids ),
		);

		if ( ! empty( $errors ) ) {
			$response_data['errors'] = $errors;
		}

		return rest_ensure_response( $response_data );
	}

	/**
	 * Get lessons with progress for a specific enrollment.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_enrollment_lessons( $request ) {
		$enrollment_id = $request->get_param( 'id' );

		// Get enrollment.
		$enrollment = SkillPulse_LMS_Enrollments_Query::get_instance()->get_enrollment_by_id( $enrollment_id );

		if ( ! $enrollment ) {
			return new WP_Error( 'enrollment_not_found', __( 'Enrollment not found.', 'skillpulse-lms' ), array( 'status' => 404 ) );
		}

		$user_id   = $enrollment->user_id;
		$course_id = $enrollment->course_id;

		// Get course lessons.
		$lessons_instance = SkillPulse_LMS_Lessons::get_instance();
		$lessons          = $lessons_instance->get_course_lessons( $course_id );

		if ( empty( $lessons ) ) {
			return rest_ensure_response(
				array(
					'success' => true,
					'lessons' => array(),
				)
			);
		}

		// Get all lesson progress for this user and course in one query.
		global $wpdb;
		$progress_table = esc_sql( $wpdb->prefix . 'splms_lesson_progress' );
		$lesson_ids     = array_map( 'intval', wp_list_pluck( $lessons, 'ID' ) );

		if ( empty( $lesson_ids ) ) {
			return rest_ensure_response(
				array(
					'success' => true,
					'lessons' => array(),
				)
			);
		}

		$placeholders = implode( ',', array_fill( 0, count( $lesson_ids ), '%d' ) );
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber,Generic.Formatting.MultipleStatementAlignment -- Table name and placeholders are safe. Table name cannot be prepared. Placeholders are dynamically generated.
		$progress_query = $wpdb->prepare(
			"SELECT * FROM {$progress_table} WHERE user_id = %d AND course_id = %d AND lesson_id IN ($placeholders)",
			array_merge( array( $user_id, $course_id ), $lesson_ids )
		);
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Query is safely prepared via $wpdb->prepare().
		$progress_records = $wpdb->get_results( $progress_query );
		// phpcs:enable

		// Create a map of lesson_id => progress for quick lookup.
		$progress_map = array();
		foreach ( $progress_records as $progress ) {
			$progress_map[ $progress->lesson_id ] = $progress;
		}

		// Combine lessons with their progress.
		$lessons_with_progress = array();
		foreach ( $lessons as $lesson ) {
			$progress_data = isset( $progress_map[ $lesson->ID ] ) ? $progress_map[ $lesson->ID ] : null;

			$lessons_with_progress[] = array(
				'id'           => $lesson->ID,
				'title'        => array(
					'rendered' => get_the_title( $lesson->ID ),
					'raw'      => $lesson->post_title,
				),
				'link'         => get_permalink( $lesson->ID ),
				'status'       => $lesson->post_status,
				'progress'     => $progress_data ? 100 : 0, // Progress is binary (completed or not).
				'completed'    => $progress_data ? (bool) $progress_data->is_completed : false,
				'completed_at' => $progress_data && $progress_data->completed_at ? $progress_data->completed_at : null,
				'started_at'   => $progress_data && $progress_data->time_spent > 0 ? $progress_data->time_spent : null,
				'time_spent'   => $progress_data ? intval( $progress_data->time_spent ) : 0,
			);
		}

		return rest_ensure_response(
			array(
				'success' => true,
				'lessons' => $lessons_with_progress,
			)
		);
	}

	/**
	 * Get activity timeline for a specific enrollment.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_enrollment_activity( $request ) {
		$enrollment_id = $request->get_param( 'id' );

		// Get enrollment.
		$enrollment = SkillPulse_LMS_Enrollments_Query::get_instance()->get_enrollment_by_id( $enrollment_id );

		if ( ! $enrollment ) {
			return new WP_Error( 'enrollment_not_found', __( 'Enrollment not found.', 'skillpulse-lms' ), array( 'status' => 404 ) );
		}

		$user_id   = $enrollment->user_id;
		$course_id = $enrollment->course_id;

		$activities = array();

		// Enrollment date.
		if ( $enrollment->enrolled_at ) {
			$activities[] = array(
				'type'        => 'enrollment',
				'date'        => $enrollment->enrolled_at,
				'title'       => __( 'Enrolled in course', 'skillpulse-lms' ),
				'description' => __( 'Student enrolled in the course', 'skillpulse-lms' ),
				'icon'        => 'admin-users',
			);
		}

		// Lesson completions.
		global $wpdb;
		$progress_table = esc_sql( $wpdb->prefix . 'splms_lesson_progress' );
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table name is safe, validated constant.
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is safe, validated constant.
		$completed_lessons = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT lp.*, p.post_title as lesson_title FROM {$progress_table} lp 
				INNER JOIN {$wpdb->posts} p ON lp.lesson_id = p.ID 
				WHERE lp.user_id = %d AND lp.course_id = %d AND lp.is_completed = 1 
				ORDER BY lp.completed_at ASC",
				$user_id,
				$course_id
			)
		);
		// phpcs:enable

		foreach ( $completed_lessons as $lesson_progress ) {
			$activities[] = array(
				'type'        => 'lesson_completed',
				'date'        => $lesson_progress->completed_at,
				'title'       => __( 'Completed lesson', 'skillpulse-lms' ),
				/* translators: %d: Lesson ID. */
				'description' => $lesson_progress->lesson_title ? $lesson_progress->lesson_title : sprintf( __( 'Lesson #%d', 'skillpulse-lms' ), $lesson_progress->lesson_id ),
				'icon'        => 'book',
			);
		}

		// Quiz attempts.
		$attempts_table = esc_sql( $wpdb->prefix . 'splms_quiz_attempts' );
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is safe, validated constant.
		$quiz_attempts = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT qa.*, p.post_title as quiz_title FROM {$attempts_table} qa 
				INNER JOIN {$wpdb->posts} p ON qa.quiz_id = p.ID 
				WHERE qa.user_id = %d AND qa.course_id = %d 
				ORDER BY qa.attempt_time ASC",
				$user_id,
				$course_id
			)
		);
		// phpcs:enable

		foreach ( $quiz_attempts as $attempt ) {
			$status       = $attempt->passed ? __( 'Passed', 'skillpulse-lms' ) : __( 'Failed', 'skillpulse-lms' );
			$activities[] = array(
				'type'        => 'quiz_attempt',
				'date'        => $attempt->attempt_time,
				'title'       => __( 'Quiz attempt', 'skillpulse-lms' ),
				'description' => sprintf(
					/* translators: %1$s: Quiz title, %2$s: Status */
					__( '%1$s - %2$s', 'skillpulse-lms' ),
					$attempt->quiz_title ? $attempt->quiz_title : sprintf(
						/* translators: %d: Quiz ID. */
						__( 'Quiz #%d', 'skillpulse-lms' ),
						$attempt->quiz_id
					),
					$status
				),
				'icon'        => 'quiz',
				'score'       => floatval( $attempt->score ),
				'max_score'   => floatval( $attempt->max_score ),
				'passed'      => (bool) $attempt->passed,
			);
		}

		// Course completion.
		if ( $enrollment->completed_at ) {
			$activities[] = array(
				'type'        => 'course_completed',
				'date'        => $enrollment->completed_at,
				'title'       => __( 'Course completed', 'skillpulse-lms' ),
				'description' => __( 'Student completed the course', 'skillpulse-lms' ),
				'icon'        => 'yes-alt',
			);
		}

		// Certificate earned (if applicable).
		if ( $enrollment->completed_at ) {
			$certificates = SkillPulse_LMS_Certificates::get_instance();
			if ( $certificates->user_has_certificate( $user_id, $course_id ) ) {
				$activities[] = array(
					'type'        => 'certificate_earned',
					'date'        => $enrollment->completed_at,
					'title'       => __( 'Certificate earned', 'skillpulse-lms' ),
					'description' => __( 'Student earned a certificate for completing the course', 'skillpulse-lms' ),
					'icon'        => 'awards',
				);
			}
		}

		// Sort activities by date.
		usort(
			$activities,
			function ( $a, $b ) {
				return strtotime( $a['date'] ) - strtotime( $b['date'] );
			}
		);

		return rest_ensure_response(
			array(
				'success'    => true,
				'activities' => $activities,
			)
		);
	}

	/**
	 * Renew enrollment by extending expiration date.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function renew_enrollment( $request ) {
		$enrollment_id   = $request->get_param( 'id' );
		$additional_days = $request->get_param( 'additional_days' );

		$enrollment_handler = SkillPulse_LMS_Enrollments_Query::get_instance();
		$result             = $enrollment_handler->renew_enrollment( $enrollment_id, $additional_days );

		if ( ! $result ) {
			return new WP_Error( 'renewal_failed', __( 'Failed to renew enrollment.', 'skillpulse-lms' ), array( 'status' => 500 ) );
		}

		// Get updated enrollment.
		$enrollment = $enrollment_handler->get_enrollment_by_id( $enrollment_id );

		return rest_ensure_response(
			array(
				'success'    => true,
				'message'    => __( 'Enrollment renewed successfully.', 'skillpulse-lms' ),
				'enrollment' => $this->prepare_enrollment_for_response( $enrollment ),
			)
		);
	}
}
