<?php
/**
 * Admin Overview REST API Controller
 *
 * Handles REST API endpoints for admin dashboard overview and statistics.
 * Provides endpoints for dashboard stats, activity logs, and reports.
 *
 * @package SkillPulse_LMS
 * @since 1.0.0
 *
 * @api
 * Available endpoints:
 * GET    /splms/v1/admin/stats - Get dashboard statistics
 * GET    /splms/v1/admin/activity - Get activity logs
 * POST   /splms/v1/admin/reports - Get reports data
 * POST   /splms/v1/admin/reports/export - Export reports
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin Overview REST API Controller class.
 *
 * @since 1.0.0
 */
class SkillPulse_LMS_Rest_Admin_Overview_Controller extends WP_REST_Controller {
	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function __construct() {
		$this->namespace = splms_rest_namespace() . '/' . splms_rest_version();
		$this->rest_base = 'admin';
	}

	/**
	 * Register the overview routes.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register_routes() {
		// Overview endpoints.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/stats',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_stats' ),
				'permission_callback' => array( $this, 'get_permissions_check' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/activity',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_activity' ),
				'permission_callback' => array( $this, 'get_permissions_check' ),
			)
		);

		// Reports endpoints.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/reports',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'get_reports' ),
				'permission_callback' => array( $this, 'get_permissions_check' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/reports/export',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'export_reports' ),
				'permission_callback' => array( $this, 'get_permissions_check' ),
			)
		);
	}

	/**
	 * Check if a given request has access to admin dashboard data.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return bool|WP_Error True if request has access, WP_Error object otherwise.
	 */
	public function get_permissions_check( $request ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error(
				'splms_rest_forbidden',
				__( 'Sorry, you are not allowed to access admin dashboard.', 'skillpulse-lms' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * Get dashboard statistics.
	 *
	 * Retrieves key performance indicators and statistics for the admin dashboard,
	 * such as total courses, enrollments, students, and revenue.
	 *
	 * @since 1.0.0
	 *
	 * @api {get} /splms/v1/admin/stats Get Dashboard Statistics
	 * @apiName GetAdminStats
	 * @apiGroup Admin
	 * @apiVersion 1.0.0
	 *
	 * @apiDescription Retrieve a summary of key statistics for the SkillPulse LMS admin dashboard.
	 * This includes counts for courses, lessons, quizzes, students, and revenue figures.
	 * Requires 'manage_options' capability.
	 *
	 *     HTTP/1.1 200 OK
	 *     {
	 *         "success": true,
	 *         "stats": {
	 *             "total_courses": 10,
	 *             "total_lessons": 50,
	 *             "total_quizzes": 15,
	 *             "total_students": 120,
	 *             "total_enrollments": 200,
	 *             "active_enrollments": 150,
	 *             "completed_enrollments": 50,
	 *             "total_revenue": 15000.00,
	 *             "pending_orders": 5
	 *         }
	 *     }
	 * @apiError (Error 403) Forbidden User does not have 'manage_options' capability.
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
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_stats( $request ) {
		global $wpdb;

		$stats = array(
			'courses'     => 0,
			'lessons'     => 0,
			'quizzes'     => 0,
			'enrollments' => 0,
		);

		// Get course count.
		$courses          = wp_count_posts( SPLMS_POST_TYPES['course'] );
		$stats['courses'] = $courses->publish;

		// Get lesson count.
		$lessons          = wp_count_posts( SPLMS_POST_TYPES['lesson'] );
		$stats['lessons'] = $lessons->publish;

		// Get quiz count.
		$quizzes          = wp_count_posts( SPLMS_POST_TYPES['quiz'] );
		$stats['quizzes'] = $quizzes->publish;

		// Get enrollment count.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table query.
		$enrollment_count     = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}splms_enrollments WHERE status = 'active'" );
		$stats['enrollments'] = intval( $enrollment_count );

		return rest_ensure_response( $stats );
	}

	/**
	 * Get recent activity logs.
	 *
	 * Retrieves a list of recent activities including course creation, lesson creation,
	 * quiz creation, and enrollment events.
	 *
	 * @since 1.0.0
	 *
	 * @api {get} /splms/v1/admin/activity Get Recent Activity
	 * @apiName GetAdminActivity
	 * @apiGroup Admin
	 * @apiVersion 1.0.0
	 *
	 * @apiDescription Retrieve a list of recent activities in the SkillPulse LMS system.
	 * This includes course, lesson, and quiz creation events, as well as enrollment activities.
	 * Requires 'manage_options' capability.
	 *
	 *     HTTP/1.1 200 OK
	 *     {
	 *         "success": true,
	 *         "activity": [
	 *             {
	 *                 "title": "Introduction to API",
	 *                 "type": "course_created",
	 *                 "time": "2 hours ago",
	 *                 "date": "2023-01-01 10:00:00"
	 *             },
	 *             {
	 *                 "title": "John Doe enrolled in Introduction to API",
	 *                 "type": "enrollment",
	 *                 "time": "1 hour ago",
	 *                 "date": "2023-01-01 11:00:00"
	 *             }
	 *         ]
	 *     }
	 * @apiError (Error 403) Forbidden User does not have 'manage_options' capability.
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
	public function get_activity( $request ) {
		global $wpdb;

		$activity = array();

		// Get recent posts.
		$recent_posts = get_posts(
			array(
				'post_type'      => array(
					SPLMS_POST_TYPES['course'],
					SPLMS_POST_TYPES['lesson'],
					SPLMS_POST_TYPES['quiz'],
				),
				'posts_per_page' => 10,
				'orderby'        => 'date',
				'order'          => 'DESC',
				'post_status'    => 'publish',
			)
		);

		foreach ( $recent_posts as $post ) {
			$activity[] = array(
				'title' => sanitize_text_field( $post->post_title ),
				'type'  => SPLMS_POST_TYPES['course'] === $post->post_type ? 'course_created' :
					( SPLMS_POST_TYPES['lesson'] === $post->post_type ? 'lesson_created' : 'quiz_created' ),
				'time'  => sanitize_text_field( human_time_diff( strtotime( $post->post_date ) ) . ' ago' ),
				'date'  => sanitize_text_field( $post->post_date ),
			);
		}

		// Get recent enrollments.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table query.
		$recent_enrollments = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT e.*, p.post_title, u.display_name
			FROM {$wpdb->prefix}splms_enrollments e 
			LEFT JOIN {$wpdb->posts} p ON e.course_id = p.ID 
			LEFT JOIN {$wpdb->users} u ON e.user_id = u.ID 
			WHERE e.status = 'active' 
			ORDER BY e.enrolled_at DESC 
			LIMIT %d",
				5
			)
		);

		foreach ( $recent_enrollments as $enrollment ) {
			$activity[] = array(
				// translators: %1$s: User display name, %2$s: Course title.
				'title' => wp_kses_post( sprintf( __( '%1$s enrolled in %2$s', 'skillpulse-lms' ), sanitize_text_field( $enrollment->display_name ), sanitize_text_field( $enrollment->post_title ) ) ),
				'type'  => 'enrollment',
				'time'  => sanitize_text_field( human_time_diff( strtotime( $enrollment->enrolled_at ) ) . ' ago' ),
				'date'  => sanitize_text_field( $enrollment->enrolled_at ),
			);
		}

		// Sort by date.
		usort(
			$activity,
			function ( $a, $b ) {
				return strtotime( $b['date'] ) - strtotime( $a['date'] );
			}
		);

		// Return top 10.
		$activity = array_slice( $activity, 0, 10 );

		return rest_ensure_response( $activity );
	}

	/**
	 * Get reports data.
	 *
	 * Retrieves comprehensive report data including overview metrics, enrollment statistics,
	 * course performance, and user analytics for a specified date range.
	 *
	 * @since 1.0.0
	 *
	 * @api {post} /splms/v1/admin/reports Get Reports Data
	 * @apiName GetAdminReports
	 * @apiGroup Admin
	 * @apiVersion 1.0.0
	 *
	 * @apiDescription Retrieve comprehensive report data for the admin dashboard. This includes
	 * overview metrics, enrollment statistics, course performance data, and user analytics.
	 * Requires 'manage_options' capability.
	 *
	 * @apiParam {Object} [date_range] Date range for the report.
	 * @apiParam {String} [date_range.start] Start date (YYYY-MM-DD).
	 * @apiParam {String} [date_range.end] End date (YYYY-MM-DD).
	 * @apiParam {Object} [filters] Additional filters for the report.
	 *
	 *     HTTP/1.1 200 OK
	 *     {
	 *         "success": true,
	 *         "reports": {
	 *             "overview": {
	 *                 "totalRevenue": 15000.00,
	 *                 "totalEnrollments": 200,
	 *                 "completionRate": 25.0,
	 *                 "averageProgress": 45.5
	 *             },
	 *             "enrollments": [...],
	 *             "courses": [...],
	 *             "users": [...]
	 *         }
	 *     }
	 * @apiError (Error 403) Forbidden User does not have 'manage_options' capability.
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
	public function get_reports( $request ) {
		global $wpdb;

		$body       = $request->get_json_params();
		$date_range = isset( $body['date_range'] ) ? $body['date_range'] : array();
		$filters    = isset( $body['filters'] ) ? $body['filters'] : array();

		$start_date = isset( $date_range['start'] ) ? $date_range['start'] : gmdate( 'Y-m-d', strtotime( '-30 days' ) );
		$end_date   = isset( $date_range['end'] ) ? $date_range['end'] : gmdate( 'Y-m-d' );

		$reports = array(
			'overview'    => array(
				'totalRevenue'     => 0,
				'totalEnrollments' => 0,
				'completionRate'   => 0,
				'averageProgress'  => 0,
			),
			'enrollments' => array(),
			'courses'     => array(),
			'users'       => array(),
		);

		// Get enrollment stats.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table query.
		$enrollment_stats = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT
				COUNT(*) as total_enrollments,
				COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_enrollments,
				AVG(progress) as average_progress
			FROM {$wpdb->prefix}splms_enrollments 
			WHERE enrolled_at BETWEEN %s AND %s",
				$start_date,
				$end_date
			)
		);

		$reports['overview']['totalEnrollments'] = intval( $enrollment_stats->total_enrollments );
		$reports['overview']['completionRate']   = $enrollment_stats->total_enrollments > 0 ?
			round( ( $enrollment_stats->completed_enrollments / $enrollment_stats->total_enrollments ) * 100, 2 ) : 0;
		$reports['overview']['averageProgress']  = round( $enrollment_stats->average_progress, 2 );

		// Get course performance data.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table query.
		$course_performance = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT
				p.ID,
				p.post_title,
				COUNT(e.id) as enrollment_count,
				AVG(e.progress) as average_progress,
				COUNT(CASE WHEN e.status = 'completed' THEN 1 END) as completed_count
			FROM {$wpdb->posts} p
			LEFT JOIN {$wpdb->prefix}splms_enrollments e ON p.ID = e.course_id
			WHERE p.post_type = %s AND p.post_status = 'publish'
			GROUP BY p.ID
			ORDER BY enrollment_count DESC
			LIMIT 10",
				SPLMS_POST_TYPES['course']
			)
		);

		$reports['courses'] = array_map(
			function ( $course ) {
				return array(
					'id'               => intval( $course->ID ),
					'title'            => sanitize_text_field( $course->post_title ),
					'enrollments'      => intval( $course->enrollment_count ),
					'average_progress' => round( floatval( $course->average_progress ), 2 ),
					'completions'      => intval( $course->completed_count ),
				);
			},
			$course_performance
		);

		return rest_ensure_response( $reports );
	}

	/**
	 * Export reports data to CSV.
	 *
	 * Generates a CSV file containing report data based on the specified type and filters.
	 * Returns a download URL for the generated file.
	 *
	 * @since 1.0.0
	 *
	 * @api {post} /splms/v1/admin/reports/export Export Reports
	 * @apiName ExportAdminReports
	 * @apiGroup Admin
	 * @apiVersion 1.0.0
	 *
	 * @apiDescription Export report data to a CSV file. The export type determines what data
	 * is included in the CSV. Requires 'manage_options' capability.
	 *
	 * @apiParam {String} [type="overview"] Export type ('overview', 'enrollments', 'courses').
	 * @apiParam {Object} [date_range] Date range for the export.
	 * @apiParam {Object} [filters] Additional filters for the export.
	 *
	 *     HTTP/1.1 200 OK
	 *     {
	 *         "success": true,
	 *         "download_url": "http://example.com/wp-content/uploads/splms-exports/splms-overview-2023-01-01-12-00-00.csv",
	 *         "filename": "splms-overview-2023-01-01-12-00-00.csv"
	 *     }
	 * @apiError (Error 403) Forbidden User does not have 'manage_options' capability.
	 * @apiError (Error 500) FileCreationFailed Failed to create the export file.
	 * @apiErrorExample {json} Error-Response:
	 *     HTTP/1.1 500 Internal Server Error
	 *     {
	 *         "code": "file_creation_failed",
	 *         "message": "Failed to create export file: ...",
	 *         "data": {
	 *             "status": 500
	 *         }
	 *     }
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function export_reports( $request ) {
		$body = $request->get_json_params();
		$type = isset( $body['type'] ) ? $body['type'] : 'overview';

		// Generate CSV data based on type.
		$csv_data = $this->generate_csv_data( $type, $body );

		// Create CSV file using FileManager.
		$filename = 'splms-' . $type . '-' . gmdate( 'Y-m-d-H-i-s' ) . '.csv';

		// Build CSV content.
		$csv_content = '';
		foreach ( $csv_data as $row ) {
			$csv_content .= implode(
				',',
				array_map(
					function ( $field ) {
						return '"' . str_replace( '"', '""', $field ) . '"';
					},
					$row
				)
			) . "\n";
		}

		$file_result = SkillPulse_LMS_File_Manager::write_file( 'exports', $filename, $csv_content );

		if ( is_wp_error( $file_result ) ) {
			return new WP_Error(
				'file_creation_failed',
				__( 'Failed to create export file: ', 'skillpulse-lms' ) . $file_result->get_error_message(),
				array( 'status' => 500 )
			);
		}

		// Return download URL.
		$download_url = $file_result['fileurl'];

		return rest_ensure_response(
			array(
				'success'      => true,
				'download_url' => $download_url,
				'filename'     => $filename,
			)
		);
	}

	/**
	 * Generate CSV data for export.
	 *
	 * @since 1.0.0
	 *
	 * @param string $type   Report type.
	 * @param array  $params Parameters.
	 *
	 * @return array CSV data array.
	 */
	private function generate_csv_data( $type, $params ) {
		global $wpdb;

		$csv_data = array();

		switch ( $type ) {
			case 'enrollments':
				$csv_data[] = array( 'User', 'Course', 'Enrolled Date', 'Progress', 'Status' );
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table query.
				$enrollments = $wpdb->get_results(
					"SELECT e.*, p.post_title, u.display_name
					FROM {$wpdb->prefix}splms_enrollments e 
					LEFT JOIN {$wpdb->posts} p ON e.course_id = p.ID 
					LEFT JOIN {$wpdb->users} u ON e.user_id = u.ID 
					ORDER BY e.enrolled_at DESC"
				);
				foreach ( $enrollments as $enrollment ) {
					$csv_data[] = array(
						isset( $enrollment->display_name ) ? sanitize_text_field( $enrollment->display_name ) : '',
						isset( $enrollment->post_title ) ? sanitize_text_field( $enrollment->post_title ) : '',
						isset( $enrollment->enrolled_at ) ? sanitize_text_field( $enrollment->enrolled_at ) : '',
						isset( $enrollment->progress ) ? floatval( $enrollment->progress ) . '%' : '0%',
						isset( $enrollment->status ) ? sanitize_text_field( $enrollment->status ) : '',
					);
				}
				break;

			case 'courses':
				$csv_data[] = array( 'Course', 'Enrollments', 'Average Progress', 'Completions' );
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table query.
				$courses = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT
							p.post_title,
							COUNT(e.id) as enrollment_count,
							AVG(e.progress) as average_progress,
							COUNT(CASE WHEN e.status = 'completed' THEN 1 END) as completed_count
						FROM {$wpdb->posts} p
						LEFT JOIN {$wpdb->prefix}splms_enrollments e ON p.ID = e.course_id
						WHERE p.post_type = %s AND p.post_status = 'publish'
						GROUP BY p.ID
						ORDER BY enrollment_count DESC",
						SPLMS_POST_TYPES['course']
					)
				);
				foreach ( $courses as $course ) {
					$csv_data[] = array(
						isset( $course->post_title ) ? sanitize_text_field( $course->post_title ) : '',
						isset( $course->enrollment_count ) ? intval( $course->enrollment_count ) : 0,
						isset( $course->average_progress ) ? round( floatval( $course->average_progress ), 2 ) . '%' : '0%',
						isset( $course->completed_count ) ? intval( $course->completed_count ) : 0,
					);
				}
				break;

			default:
				$csv_data[] = array( 'Metric', 'Value' );
				$stats      = $this->get_stats_data();
				foreach ( $stats as $key => $value ) {
					$csv_data[] = array(
						sanitize_text_field( ucfirst( $key ) ),
						is_numeric( $value ) ? intval( $value ) : sanitize_text_field( $value ),
					);
				}
		}

		return $csv_data;
	}

	/**
	 * Get stats data without REST response wrapper.
	 *
	 * @since 1.0.0
	 *
	 * @return array Stats data array.
	 */
	private function get_stats_data() {
		global $wpdb;

		$stats = array(
			'courses'     => 0,
			'lessons'     => 0,
			'quizzes'     => 0,
			'enrollments' => 0,
		);

		// Get course count.
		$courses          = wp_count_posts( SPLMS_POST_TYPES['course'] );
		$stats['courses'] = $courses->publish;

		// Get lesson count.
		$lessons          = wp_count_posts( SPLMS_POST_TYPES['lesson'] );
		$stats['lessons'] = $lessons->publish;

		// Get quiz count.
		$quizzes          = wp_count_posts( SPLMS_POST_TYPES['quiz'] );
		$stats['quizzes'] = $quizzes->publish;

		// Get enrollment count.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table query.
		$enrollment_count     = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}splms_enrollments WHERE status = 'active'" );
		$stats['enrollments'] = intval( $enrollment_count );

		return $stats;
	}
}
