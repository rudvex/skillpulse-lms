<?php
/**
 * Admin Report REST API Controller
 *
 * Handles REST API endpoints for various admin reports and analytics.
 * Provides endpoints for user progress, course analytics, lesson engagement, certificates, and reports export.
 *
 * @package SkillPulse_LMS
 * @since 1.0.0
 *
 * @api
 * Available endpoints:
 * POST   /splms/v1/admin/reports - Generate reports
 * POST   /splms/v1/admin/reports/user-progress - Get user progress report
 * POST   /splms/v1/admin/reports/course-analytics - Get course analytics report
 * POST   /splms/v1/admin/reports/lesson-engagement - Get lesson engagement report
 * POST   /splms/v1/admin/reports/certificates - Get certificates report
 * POST   /splms/v1/admin/reports/export - Export reports
 * GET    /splms/v1/admin/reports/overview - Get overview metrics
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin Report REST API Controller class.
 *
 * @since 1.0.0
 */
class SkillPulse_LMS_Rest_Admin_Report_Controller extends WP_REST_Controller {
	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function __construct() {
		$this->namespace = splms_rest_namespace() . '/' . splms_rest_version();
		$this->rest_base = 'admin/reports';
	}

	/**
	 * Register the reports routes.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register_routes() {
		// Main reports endpoint.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'get_reports' ),
				'permission_callback' => array( $this, 'get_permissions_check' ),
				'args'                => $this->get_reports_args(),
			)
		);

		// User progress report.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/user-progress',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'get_user_progress_report' ),
				'permission_callback' => array( $this, 'get_permissions_check' ),
				'args'                => $this->get_reports_args(),
			)
		);

		// Course analytics report.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/course-analytics',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'get_course_analytics_report' ),
				'permission_callback' => array( $this, 'get_permissions_check' ),
				'args'                => $this->get_reports_args(),
			)
		);

		// Lesson engagement report.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/lesson-engagement',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'get_lesson_engagement_report' ),
				'permission_callback' => array( $this, 'get_permissions_check' ),
				'args'                => $this->get_reports_args(),
			)
		);

		// Certificates report.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/certificates',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'get_certificates_report' ),
				'permission_callback' => array( $this, 'get_permissions_check' ),
				'args'                => $this->get_reports_args(),
			)
		);

		// Export reports.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/export',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'export_reports' ),
				'permission_callback' => array( $this, 'get_permissions_check' ),
				'args'                => $this->get_export_args(),
			)
		);

		// Overview metrics.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/overview',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_overview_metrics' ),
				'permission_callback' => array( $this, 'get_permissions_check' ),
			)
		);

		// Charts data.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/charts',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'get_charts_data' ),
				'permission_callback' => array( $this, 'get_permissions_check' ),
				'args'                => $this->get_reports_args(),
			)
		);
	}

	/**
	 * Check if a given request has access to reports.
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
				__( 'Sorry, you are not allowed to access reports.', 'skillpulse-lms' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * Get reports arguments.
	 *
	 * @since 1.0.0
	 *
	 * @return array Reports arguments array.
	 */
	public function get_reports_args() {
		return array(
			'date_range' => array(
				'type'        => 'object',
				'description' => __( 'Date range for the report', 'skillpulse-lms' ),
				'properties'  => array(
					'start' => array(
						'type'   => 'string',
						'format' => 'date-time',
					),
					'end'   => array(
						'type'   => 'string',
						'format' => 'date-time',
					),
				),
			),
			'filters'    => array(
				'type'        => 'object',
				'description' => __( 'Report filters', 'skillpulse-lms' ),
				'properties'  => array(
					'course' => array(
						'type' => 'string',
					),
					'user'   => array(
						'type' => 'string',
					),
					'status' => array(
						'type' => 'string',
					),
				),
			),
			'page'       => array(
				'type'    => 'integer',
				'default' => 1,
			),
			'per_page'   => array(
				'type'    => 'integer',
				'default' => 20,
			),
		);
	}

	/**
	 * Get export arguments.
	 *
	 * @since 1.0.0
	 *
	 * @return array Export arguments array.
	 */
	public function get_export_args() {
		return array_merge(
			$this->get_reports_args(),
			array(
				'type' => array(
					'type'        => 'string',
					'description' => __( 'Export type', 'skillpulse-lms' ),
					'enum'        => array( 'overview', 'user-progress', 'course-analytics', 'lesson-engagement', 'certificates' ),
					'default'     => 'overview',
				),
			)
		);
	}

	/**
	 * Get main reports data.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_reports( $request ) {
		$body       = $request->get_json_params();
		$date_range = isset( $body['date_range'] ) ? $body['date_range'] : array();
		$filters    = isset( $body['filters'] ) ? $body['filters'] : array();

		$start_date = isset( $date_range['start'] ) ? gmdate( 'Y-m-d', strtotime( $date_range['start'] ) ) : gmdate( 'Y-m-d', strtotime( '-30 days' ) );
		$end_date   = isset( $date_range['end'] ) ? gmdate( 'Y-m-d', strtotime( $date_range['end'] ) ) : gmdate( 'Y-m-d' );

		$reports = array(
			'overview'    => $this->get_overview_data( $start_date, $end_date, $filters ),
			'enrollments' => $this->get_enrollments_data( $start_date, $end_date, $filters ),
			'courses'     => $this->get_courses_data( $start_date, $end_date, $filters ),
			'users'       => $this->get_users_data( $start_date, $end_date, $filters ),
		);

		return rest_ensure_response( $reports );
	}

	/**
	 * Get overview metrics.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_overview_metrics( $request ) {
		$metrics = $this->get_overview_data( gmdate( 'Y-m-d', strtotime( '-30 days' ) ), gmdate( 'Y-m-d' ), array() );

		return rest_ensure_response( $metrics );
	}

	/**
	 * Get user progress report.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_user_progress_report( $request ) {
		global $wpdb;

		$body       = $request->get_json_params();
		$date_range = isset( $body['date_range'] ) ? $body['date_range'] : array();
		$filters    = isset( $body['filters'] ) ? $body['filters'] : array();
		$page       = isset( $body['page'] ) ? intval( $body['page'] ) : 1;
		$per_page   = isset( $body['per_page'] ) ? intval( $body['per_page'] ) : 20;

		$start_date = isset( $date_range['start'] ) ? gmdate( 'Y-m-d', strtotime( $date_range['start'] ) ) : gmdate( 'Y-m-d', strtotime( '-30 days' ) );
		$end_date   = isset( $date_range['end'] ) ? gmdate( 'Y-m-d', strtotime( $date_range['end'] ) ) : gmdate( 'Y-m-d' );

		$offset = ( $page - 1 ) * $per_page;

		// Build WHERE clause.
		$where_clauses = array( 'DATE(e.enrolled_at) BETWEEN %s AND %s' );
		$where_values  = array( $start_date, $end_date );

		if ( ! empty( $filters['course'] ) ) {
			$where_clauses[] = 'e.course_id = %d';
			$where_values[]  = intval( $filters['course'] );
		}

		if ( ! empty( $filters['user'] ) ) {
			$where_clauses[] = 'e.user_id = %d';
			$where_values[]  = intval( $filters['user'] );
		}

		if ( ! empty( $filters['status'] ) && 'all' !== $filters['status'] ) {
			$where_clauses[] = 'e.status = %s';
			$where_values[]  = sanitize_text_field( $filters['status'] );
		}

		$where_clause = implode( ' AND ', $where_clauses );

		// Get total count.
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber,WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare, PluginCheck.Security.DirectDB.UnescapedDBParameter -- $where_clause is built with proper placeholders, dynamic WHERE clause.
		if ( empty( $where_values ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- $where_clause is safe and has no placeholders.
			$total_query = "SELECT COUNT(*) FROM {$wpdb->prefix}splms_enrollments e WHERE {$where_clause}";
		} else {
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare, PluginCheck.Security.DirectDB.UnescapedDBParameter -- $where_clause is safely constructed above and may not have placeholders.
			$total_query = $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}splms_enrollments e WHERE {$where_clause}", ...$where_values );
		}
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- $total_query is prepared above.
		$total = $wpdb->get_var( $total_query );

		// Get user progress data.
		$query_string = "SELECT 
				e.user_id,
				e.course_id,
				e.progress,
				e.status,
				e.enrolled_at,
				e.completed_at,
				e.last_accessed_at,
				u.display_name,
				u.user_email,
				p.post_title as course_title,
				COUNT(DISTINCT enrolled_courses.id) as total_enrolled_courses,
				COUNT(DISTINCT completed_courses.id) as total_completed_courses
			FROM {$wpdb->prefix}splms_enrollments e
			LEFT JOIN {$wpdb->users} u ON e.user_id = u.ID
			LEFT JOIN {$wpdb->posts} p ON e.course_id = p.ID
			LEFT JOIN {$wpdb->prefix}splms_enrollments enrolled_courses ON e.user_id = enrolled_courses.user_id
			LEFT JOIN {$wpdb->prefix}splms_enrollments completed_courses ON e.user_id = completed_courses.user_id AND completed_courses.status = 'completed'
			WHERE {$where_clause}
			GROUP BY e.id
			ORDER BY e.enrolled_at DESC
			LIMIT %d OFFSET %d";
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- $where_clause is built with proper placeholders, $query_string contains interpolated variables that are safe.
		$all_values = array_merge( $where_values, array( $per_page, $offset ) );
		if ( empty( $all_values ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- $query_string is safely constructed above.
			$query = $query_string;
		} else {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- $query_string is safely constructed above.
			$query = $wpdb->prepare( $query_string, ...$all_values );
		}
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- $query is prepared above.
		$results = $wpdb->get_results( $query );

		$users = array();
		foreach ( $results as $row ) {
			$users[] = array(
				'user_id'                 => intval( $row->user_id ),
				'display_name'            => isset( $row->display_name ) ? sanitize_text_field( $row->display_name ) : '',
				'user_email'              => isset( $row->user_email ) ? sanitize_email( $row->user_email ) : '',
				'course_id'               => intval( $row->course_id ),
				'course_title'            => isset( $row->course_title ) ? sanitize_text_field( $row->course_title ) : '',
				'progress'                => floatval( $row->progress ),
				'status'                  => isset( $row->status ) ? sanitize_text_field( $row->status ) : '',
				'enrolled_at'             => isset( $row->enrolled_at ) ? sanitize_text_field( $row->enrolled_at ) : '',
				'completed_at'            => isset( $row->completed_at ) ? sanitize_text_field( $row->completed_at ) : '',
				'last_accessed_at'        => isset( $row->last_accessed_at ) ? sanitize_text_field( $row->last_accessed_at ) : '',
				'total_enrolled_courses'  => intval( $row->total_enrolled_courses ),
				'total_completed_courses' => intval( $row->total_completed_courses ),
				'completion_percentage'   => $row->total_enrolled_courses > 0 ?
					round( ( $row->total_completed_courses / $row->total_enrolled_courses ) * 100, 2 ) : 0,
			);
		}

		return rest_ensure_response(
			array(
				'data'       => $users,
				'pagination' => array(
					'total'       => intval( $total ),
					'page'        => $page,
					'per_page'    => $per_page,
					'total_pages' => ceil( $total / $per_page ),
				),
			)
		);
	}

	/**
	 * Get course analytics report.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_course_analytics_report( $request ) {
		global $wpdb;

		$body       = $request->get_json_params();
		$date_range = isset( $body['date_range'] ) ? $body['date_range'] : array();
		$filters    = isset( $body['filters'] ) ? $body['filters'] : array();
		$page       = isset( $body['page'] ) ? intval( $body['page'] ) : 1;
		$per_page   = isset( $body['per_page'] ) ? intval( $body['per_page'] ) : 20;

		$start_date = isset( $date_range['start'] ) ? gmdate( 'Y-m-d', strtotime( $date_range['start'] ) ) : gmdate( 'Y-m-d', strtotime( '-30 days' ) );
		$end_date   = isset( $date_range['end'] ) ? gmdate( 'Y-m-d', strtotime( $date_range['end'] ) ) : gmdate( 'Y-m-d' );

		$offset = ( $page - 1 ) * $per_page;

		// Build WHERE clause for course filter.
		$course_where = '';
		if ( ! empty( $filters['course'] ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- $course_where is built with proper placeholders.
			$course_where = $wpdb->prepare( 'AND p.ID = %d', intval( $filters['course'] ) );
		}

		// Get course analytics data.
		$query_string = "SELECT 
				p.ID as course_id,
				p.post_title as course_title,
				p.post_date as course_created,
				COUNT(DISTINCT e.user_id) as total_enrollments,
				COUNT(DISTINCT CASE WHEN e.status = 'completed' THEN e.user_id END) as completed_enrollments,
				COUNT(DISTINCT CASE WHEN e.status = 'active' THEN e.user_id END) as active_enrollments,
				AVG(e.progress) as average_progress,
				AVG(CASE WHEN e.completed_at IS NOT NULL THEN 
					DATEDIFF(e.completed_at, e.enrolled_at) END) as avg_completion_days,
				MAX(e.enrolled_at) as last_enrollment_date,
				(SELECT COUNT(*) FROM {$wpdb->posts} lessons 
				 WHERE lessons.post_parent = p.ID 
				 AND lessons.post_type = %s 
				 AND lessons.post_status = 'publish') as total_lessons,
				(SELECT COUNT(*) FROM {$wpdb->posts} quizzes 
				 WHERE quizzes.post_parent = p.ID 
				 AND quizzes.post_type = %s 
				 AND quizzes.post_status = 'publish') as total_quizzes
			FROM {$wpdb->posts} p
			LEFT JOIN {$wpdb->prefix}splms_enrollments e ON p.ID = e.course_id 
				AND DATE(e.enrolled_at) BETWEEN %s AND %s
			WHERE p.post_type = %s 
				AND p.post_status = 'publish'
				{$course_where}
			GROUP BY p.ID
			ORDER BY total_enrollments DESC
			LIMIT %d OFFSET %d";
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- $course_where is built with proper placeholders, $query_string contains interpolated variables that are safe.
		$query = $wpdb->prepare( $query_string, SPLMS_POST_TYPES['lesson'], SPLMS_POST_TYPES['quiz'], $start_date, $end_date, SPLMS_POST_TYPES['course'], $per_page, $offset );
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- $query is prepared above.
		$results = $wpdb->get_results( $query );

		// Get total count.
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- $course_where is built with proper placeholders.
		$total_query = $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->posts} p WHERE p.post_type = %s AND p.post_status = 'publish' {$course_where}", SPLMS_POST_TYPES['course'] );
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- $total_query is prepared above.
		$total = $wpdb->get_var( $total_query );

		$courses = array();
		foreach ( $results as $row ) {
			$completion_rate = $row->total_enrollments > 0 ?
				round( ( $row->completed_enrollments / $row->total_enrollments ) * 100, 2 ) : 0;

			$courses[] = array(
				'course_id'             => intval( $row->course_id ),
				'course_title'          => isset( $row->course_title ) ? sanitize_text_field( $row->course_title ) : '',
				'course_created'        => isset( $row->course_created ) ? sanitize_text_field( $row->course_created ) : '',
				'total_enrollments'     => intval( $row->total_enrollments ),
				'completed_enrollments' => intval( $row->completed_enrollments ),
				'active_enrollments'    => intval( $row->active_enrollments ),
				'completion_rate'       => $completion_rate,
				'average_progress'      => round( floatval( $row->average_progress ), 2 ),
				'avg_completion_days'   => $row->avg_completion_days ? round( floatval( $row->avg_completion_days ), 1 ) : null,
				'last_enrollment_date'  => isset( $row->last_enrollment_date ) ? sanitize_text_field( $row->last_enrollment_date ) : '',
				'total_lessons'         => intval( $row->total_lessons ),
				'total_quizzes'         => intval( $row->total_quizzes ),
			);
		}

		return rest_ensure_response(
			array(
				'data'       => $courses,
				'pagination' => array(
					'total'       => intval( $total ),
					'page'        => $page,
					'per_page'    => $per_page,
					'total_pages' => ceil( $total / $per_page ),
				),
			)
		);
	}

	/**
	 * Get lesson engagement report.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_lesson_engagement_report( $request ) {
		global $wpdb;

		$body       = $request->get_json_params();
		$date_range = isset( $body['date_range'] ) ? $body['date_range'] : array();
		$filters    = isset( $body['filters'] ) ? $body['filters'] : array();
		$page       = isset( $body['page'] ) ? intval( $body['page'] ) : 1;
		$per_page   = isset( $body['per_page'] ) ? intval( $body['per_page'] ) : 20;

		$start_date = isset( $date_range['start'] ) ? gmdate( 'Y-m-d', strtotime( $date_range['start'] ) ) : gmdate( 'Y-m-d', strtotime( '-30 days' ) );
		$end_date   = isset( $date_range['end'] ) ? gmdate( 'Y-m-d', strtotime( $date_range['end'] ) ) : gmdate( 'Y-m-d' );

		$offset = ( $page - 1 ) * $per_page;

		// Build WHERE clause for course filter.
		$course_where = '';
		if ( ! empty( $filters['course'] ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- $course_where is built with proper placeholders.
			$course_where = $wpdb->prepare( 'AND course.ID = %d', intval( $filters['course'] ) );
		}

		// Get lesson engagement data.
		$query_string = "SELECT 
				l.ID as lesson_id,
				l.post_title as lesson_title,
				l.post_parent as course_id,
				course.post_title as course_title,
				l.post_date as lesson_created,
				COUNT(DISTINCT lp.user_id) as total_views,
				COUNT(DISTINCT CASE WHEN lp.is_completed = 1 THEN lp.user_id END) as completed_views,
				AVG(lp.time_spent) as avg_time_spent,
				MAX(lp.completed_at) as last_accessed
			FROM {$wpdb->posts} l
			LEFT JOIN {$wpdb->posts} course ON l.post_parent = course.ID
			LEFT JOIN {$wpdb->prefix}splms_lesson_progress lp ON l.ID = lp.lesson_id
			WHERE l.post_type = %s 
				AND l.post_status = 'publish'
				AND (lp.completed_at IS NULL OR DATE(lp.completed_at) BETWEEN %s AND %s)
				{$course_where}
			GROUP BY l.ID
			ORDER BY total_views DESC
			LIMIT %d OFFSET %d";
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- $course_where is built with proper placeholders, $query_string contains interpolated variables that are safe.
		$query = $wpdb->prepare( $query_string, SPLMS_POST_TYPES['lesson'], $start_date, $end_date, $per_page, $offset );
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- $query is prepared above.
		$results = $wpdb->get_results( $query );

		// Get total count.
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- $course_where is built with proper placeholders.
		$total_query = $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->posts} l LEFT JOIN {$wpdb->posts} course ON l.post_parent = course.ID WHERE l.post_type = %s AND l.post_status = 'publish' {$course_where}", SPLMS_POST_TYPES['lesson'] );
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- $total_query is prepared above.
		$total = $wpdb->get_var( $total_query );

		$lessons = array();
		foreach ( $results as $row ) {
			$completion_rate = $row->total_views > 0 ?
				round( ( $row->completed_views / $row->total_views ) * 100, 2 ) : 0;

			$lessons[] = array(
				'lesson_id'       => intval( $row->lesson_id ),
				'lesson_title'    => isset( $row->lesson_title ) ? sanitize_text_field( $row->lesson_title ) : '',
				'course_id'       => intval( $row->course_id ),
				'course_title'    => isset( $row->course_title ) ? sanitize_text_field( $row->course_title ) : '',
				'lesson_created'  => isset( $row->lesson_created ) ? sanitize_text_field( $row->lesson_created ) : '',
				'total_views'     => intval( $row->total_views ),
				'completed_views' => intval( $row->completed_views ),
				'completion_rate' => $completion_rate,
				'avg_time_spent'  => $row->avg_time_spent ? round( floatval( $row->avg_time_spent ), 2 ) : 0,
				'last_accessed'   => isset( $row->last_accessed ) ? sanitize_text_field( $row->last_accessed ) : '',
			);
		}

		return rest_ensure_response(
			array(
				'data'       => $lessons,
				'pagination' => array(
					'total'       => intval( $total ),
					'page'        => $page,
					'per_page'    => $per_page,
					'total_pages' => ceil( $total / $per_page ),
				),
			)
		);
	}

	/**
	 * Get certificates report.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_certificates_report( $request ) {
		global $wpdb;

		$body       = $request->get_json_params();
		$date_range = isset( $body['date_range'] ) ? $body['date_range'] : array();
		$filters    = isset( $body['filters'] ) ? $body['filters'] : array();
		$page       = isset( $body['page'] ) ? intval( $body['page'] ) : 1;
		$per_page   = isset( $body['per_page'] ) ? intval( $body['per_page'] ) : 20;

		$start_date = isset( $date_range['start'] ) ? gmdate( 'Y-m-d', strtotime( $date_range['start'] ) ) : gmdate( 'Y-m-d', strtotime( '-30 days' ) );
		$end_date   = isset( $date_range['end'] ) ? gmdate( 'Y-m-d', strtotime( $date_range['end'] ) ) : gmdate( 'Y-m-d' );

		$offset = ( $page - 1 ) * $per_page;

		// Build WHERE clause (using enrollments table as certificates source).
		$where_clauses = array( 'DATE(e.completed_at) BETWEEN %s AND %s', "e.status = 'completed'" );
		$where_values  = array( $start_date, $end_date );

		if ( ! empty( $filters['course'] ) ) {
			$where_clauses[] = 'e.course_id = %d';
			$where_values[]  = intval( $filters['course'] );
		}

		if ( ! empty( $filters['user'] ) ) {
			$where_clauses[] = 'e.user_id = %d';
			$where_values[]  = intval( $filters['user'] );
		}

		$where_clause = implode( ' AND ', $where_clauses );

		// Get certificates data from new certificate system.
		$certificate_args = array(
			'post_type'      => SPLMS_POST_TYPES['certificate'],
			'posts_per_page' => $per_page,
			'offset'         => $offset,
			'orderby'        => 'date',
			'order'          => 'DESC',
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Necessary for filtering certificates.
			'meta_query'     => array(),
		);

		// Add date filter.
		if ( $start_date && $end_date ) {
			$certificate_args['date_query'] = array(
				array(
					'after'     => $start_date,
					'before'    => $end_date,
					'inclusive' => true,
				),
			);
		}

		// Add course filter.
		if ( ! empty( $filters['course'] ) ) {
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Meta query needed for filtering certificates.
			$certificate_args['meta_query'][] = array(
				'key'     => '_splms_certificate_course_id',
				'value'   => intval( $filters['course'] ),
				'compare' => '=',
			);
		}

		// Add user filter.
		if ( ! empty( $filters['user'] ) ) {
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Meta query needed for filtering certificates.
			$certificate_args['meta_query'][] = array(
				'key'     => '_splms_certificate_user_id',
				'value'   => intval( $filters['user'] ),
				'compare' => '=',
			);
		}

		// Only get issued certificates.
		// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Meta query needed for filtering certificates.
		$certificate_args['meta_query'][] = array(
			'key'     => '_splms_certificate_status',
			'value'   => 'issued',
			'compare' => '=',
		);

		$certificate_query = new WP_Query( $certificate_args );
		$total             = $certificate_query->found_posts;

		$certificates = array();
		foreach ( $certificate_query->posts as $certificate_post ) {
			$certificate_id = $certificate_post->ID;
			$user_id        = get_post_meta( $certificate_id, '_splms_certificate_user_id', true );
			$course_id      = get_post_meta( $certificate_id, '_splms_certificate_course_id', true );

			$user   = get_userdata( $user_id );
			$course = get_post( $course_id );

			$generated_date = get_post_meta( $certificate_id, '_splms_certificate_generated_date', true );
			$token          = get_post_meta( $certificate_id, '_splms_certificate_token', true );

			$certificates[] = array(
				'user_id'         => intval( $user_id ),
				'display_name'    => $user ? sanitize_text_field( $user->display_name ) : __( 'Unknown User', 'skillpulse-lms' ),
				'user_email'      => $user ? sanitize_email( $user->user_email ) : '',
				'course_id'       => intval( $course_id ),
				'course_title'    => $course ? sanitize_text_field( $course->post_title ) : __( 'Unknown Course', 'skillpulse-lms' ),
				'issued_date'     => sanitize_text_field( $generated_date ),
				'certificate_id'  => intval( $certificate_id ),
				'certificate_url' => esc_url_raw( home_url( '/certificate/verify/' . sanitize_text_field( $token ) ) ),
			);
		}

		return rest_ensure_response(
			array(
				'data'       => $certificates,
				'pagination' => array(
					'total'       => intval( $total ),
					'page'        => $page,
					'per_page'    => $per_page,
					'total_pages' => ceil( $total / $per_page ),
				),
			)
		);
	}

	/**
	 * Get charts data.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_charts_data( $request ) {
		global $wpdb;

		$body       = $request->get_json_params();
		$date_range = isset( $body['date_range'] ) ? $body['date_range'] : array();
		$filters    = isset( $body['filters'] ) ? $body['filters'] : array();

		$start_date = isset( $date_range['start'] ) ? gmdate( 'Y-m-d', strtotime( $date_range['start'] ) ) : gmdate( 'Y-m-d', strtotime( '-30 days' ) );
		$end_date   = isset( $date_range['end'] ) ? gmdate( 'Y-m-d', strtotime( $date_range['end'] ) ) : gmdate( 'Y-m-d' );

		$charts = array(
			'enrollment_trends'  => $this->get_enrollment_trends( $start_date, $end_date, $filters ),
			'course_performance' => $this->get_course_performance_chart( $start_date, $end_date, $filters ),
			'completion_rates'   => $this->get_completion_rates_chart( $start_date, $end_date, $filters ),
		);

		return rest_ensure_response( $charts );
	}

	/**
	 * Export reports data.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function export_reports( $request ) {
		$body       = $request->get_json_params();
		$type       = isset( $body['type'] ) ? $body['type'] : 'overview';
		$date_range = isset( $body['date_range'] ) ? $body['date_range'] : array();
		$filters    = isset( $body['filters'] ) ? $body['filters'] : array();

		// Generate CSV data based on type.
		$csv_data = $this->generate_csv_data( $type, $date_range, $filters );

		// Create CSV file using FileManager.
		$filename = 'splms-' . $type . '-report-' . gmdate( 'Y-m-d-H-i-s' ) . '.csv';

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

		$file_result = SkillPulse_LMS_File_Manager::write_file( 'reports', $filename, $csv_content );

		if ( is_wp_error( $file_result ) ) {
			return new WP_Error(
				'file_creation_failed',
				__( 'Failed to create report file: ', 'skillpulse-lms' ) . $file_result->get_error_message(),
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
	 * Get overview data.
	 *
	 * @since 1.0.0
	 *
	 * @param string $start_date Start date.
	 * @param string $end_date   End date.
	 * @param array  $filters    Filters.
	 *
	 * @return array Overview data array.
	 */
	private function get_overview_data( $start_date, $end_date, $filters ) {
		global $wpdb;

		$overview = array(
			'totalRevenue'       => 0,
			'totalEnrollments'   => 0,
			'activeEnrollments'  => 0,
			'completionRate'     => 0,
			'averageProgress'    => 0,
			'totalCourses'       => 0,
			'totalUsers'         => 0,
			'certificatesIssued' => 0,
		);

		// Get enrollment stats.
		$enrollment_stats = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT 
				COUNT(*) as total_enrollments,
				COUNT(CASE WHEN status = 'active' THEN 1 END) as active_enrollments,
				COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_enrollments,
				AVG(progress) as average_progress
			FROM {$wpdb->prefix}splms_enrollments 
			WHERE DATE(enrolled_at) BETWEEN %s AND %s",
				$start_date,
				$end_date
			)
		);

		$overview['totalEnrollments']  = intval( $enrollment_stats->total_enrollments );
		$overview['activeEnrollments'] = intval( $enrollment_stats->active_enrollments );

		$overview['completionRate']  = ! empty( $enrollment_stats->total_enrollments ) && ! empty( $enrollment_stats->completed_enrollments ) && $enrollment_stats->total_enrollments > 0 ?
			round( ( $enrollment_stats->completed_enrollments / $enrollment_stats->total_enrollments ) * 100, 2 ) : 0;
		$overview['averageProgress'] = ! empty( $enrollment_stats->average_progress ) ? round( $enrollment_stats->average_progress, 2 ) : 0;

		// Get course count.
		$courses                  = wp_count_posts( SPLMS_POST_TYPES['course'] );
		$overview['totalCourses'] = $courses->publish;

		// Get unique users count.
		$unique_users           = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT user_id) FROM {$wpdb->prefix}splms_enrollments 
			WHERE DATE(enrolled_at) BETWEEN %s AND %s",
				$start_date,
				$end_date
			)
		);
		$overview['totalUsers'] = intval( $unique_users );

		// Get certificates issued.
		$certificates                   = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->prefix}splms_enrollments 
			WHERE status = 'completed' AND DATE(completed_at) BETWEEN %s AND %s",
				$start_date,
				$end_date
			)
		);
		$overview['certificatesIssued'] = intval( $certificates );

		return $overview;
	}

	/**
	 * Get enrollments data.
	 *
	 * @since 1.0.0
	 *
	 * @param string $start_date Start date.
	 * @param string $end_date   End date.
	 * @param array  $filters    Filters.
	 *
	 * @return array Enrollments data array.
	 */
	private function get_enrollments_data( $start_date, $end_date, $filters ) {
		global $wpdb;

		$enrollments = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT 
				e.user_id,
				e.course_id,
				e.progress,
				e.status,
				e.enrolled_at,
				e.completed_at,
				u.display_name,
				u.user_email,
				p.post_title as course_title
			FROM {$wpdb->prefix}splms_enrollments e
			LEFT JOIN {$wpdb->users} u ON e.user_id = u.ID
			LEFT JOIN {$wpdb->posts} p ON e.course_id = p.ID
			WHERE DATE(e.enrolled_at) BETWEEN %s AND %s
			ORDER BY e.enrolled_at DESC
			LIMIT 100",
				$start_date,
				$end_date
			)
		);

		return array_map(
			function ( $enrollment ) {
				return array(
					'user_id'      => intval( $enrollment->user_id ),
					'display_name' => $enrollment->display_name,
					'user_email'   => $enrollment->user_email,
					'course_id'    => intval( $enrollment->course_id ),
					'course_title' => $enrollment->course_title,
					'progress'     => floatval( $enrollment->progress ),
					'status'       => $enrollment->status,
					'enrolled_at'  => $enrollment->enrolled_at,
					'completed_at' => $enrollment->completed_at,
				);
			},
			$enrollments
		);
	}

	/**
	 * Get courses data.
	 *
	 * @since 1.0.0
	 *
	 * @param string $start_date Start date.
	 * @param string $end_date   End date.
	 * @param array  $filters    Filters.
	 *
	 * @return array Courses data array.
	 */
	private function get_courses_data( $start_date, $end_date, $filters ) {
		global $wpdb;

		$courses = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT 
				p.ID,
				p.post_title,
				COUNT(e.id) as enrollment_count,
				AVG(e.progress) as average_progress,
				COUNT(CASE WHEN e.status = 'completed' THEN 1 END) as completed_count
			FROM {$wpdb->posts} p
			LEFT JOIN {$wpdb->prefix}splms_enrollments e ON p.ID = e.course_id
				AND DATE(e.enrolled_at) BETWEEN %s AND %s
			WHERE p.post_type = %s AND p.post_status = 'publish'
			GROUP BY p.ID
			ORDER BY enrollment_count DESC
			LIMIT 50",
				$start_date,
				$end_date,
				SPLMS_POST_TYPES['course']
			)
		);

		return array_map(
			function ( $course ) {
				return array(
					'id'               => intval( $course->ID ),
					'title'            => $course->post_title,
					'enrollments'      => intval( $course->enrollment_count ),
					'average_progress' => round( $course->average_progress, 2 ),
					'completions'      => intval( $course->completed_count ),
					'completion_rate'  => $course->enrollment_count > 0 ?
					round( ( $course->completed_count / $course->enrollment_count ) * 100, 2 ) : 0,
				);
			},
			$courses
		);
	}

	/**
	 * Get users data.
	 *
	 * @since 1.0.0
	 *
	 * @param string $start_date Start date.
	 * @param string $end_date   End date.
	 * @param array  $filters    Filters.
	 *
	 * @return array Users data array.
	 */
	private function get_users_data( $start_date, $end_date, $filters ) {
		global $wpdb;

		$users = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT 
				u.ID,
				u.display_name,
				u.user_email,
				u.user_registered,
				COUNT(e.id) as enrollment_count,
				AVG(e.progress) as average_progress,
				COUNT(CASE WHEN e.status = 'completed' THEN 1 END) as completed_count
			FROM {$wpdb->users} u
			LEFT JOIN {$wpdb->prefix}splms_enrollments e ON u.ID = e.user_id
				AND DATE(e.enrolled_at) BETWEEN %s AND %s
			WHERE u.user_registered IS NOT NULL
			GROUP BY u.ID
			HAVING enrollment_count > 0
			ORDER BY enrollment_count DESC
			LIMIT 100",
				$start_date,
				$end_date
			)
		);

		return array_map(
			function ( $user ) {
				return array(
					'id'               => intval( $user->ID ),
					'display_name'     => $user->display_name,
					'user_email'       => $user->user_email,
					'user_registered'  => $user->user_registered,
					'enrollments'      => intval( $user->enrollment_count ),
					'average_progress' => round( $user->average_progress, 2 ),
					'completions'      => intval( $user->completed_count ),
				);
			},
			$users
		);
	}

	/**
	 * Get enrollment trends chart data.
	 *
	 * @since 1.0.0
	 *
	 * @param string $start_date Start date.
	 * @param string $end_date   End date.
	 * @param array  $filters    Filters.
	 *
	 * @return array Enrollment trends data array.
	 */
	private function get_enrollment_trends( $start_date, $end_date, $filters ) {
		global $wpdb;

		$trends = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT 
				DATE(enrolled_at) as date,
				COUNT(*) as enrollments
			FROM {$wpdb->prefix}splms_enrollments
			WHERE DATE(enrolled_at) BETWEEN %s AND %s
			GROUP BY DATE(enrolled_at)
			ORDER BY date ASC",
				$start_date,
				$end_date
			)
		);

		$labels = array();
		$data   = array();

		foreach ( $trends as $trend ) {
			$labels[] = gmdate( 'M j', strtotime( $trend->date ) );
			$data[]   = intval( $trend->enrollments );
		}

		return array(
			'labels' => $labels,
			'data'   => $data,
		);
	}

	/**
	 * Get course performance chart data.
	 *
	 * @since 1.0.0
	 *
	 * @param string $start_date Start date.
	 * @param string $end_date   End date.
	 * @param array  $filters    Filters.
	 *
	 * @return array Course performance chart data array.
	 */
	private function get_course_performance_chart( $start_date, $end_date, $filters ) {
		global $wpdb;

		$performance = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT 
				p.post_title,
				COUNT(e.id) as enrollment_count,
				COUNT(CASE WHEN e.status = 'completed' THEN 1 END) as completed_count
			FROM {$wpdb->posts} p
			LEFT JOIN {$wpdb->prefix}splms_enrollments e ON p.ID = e.course_id
				AND DATE(e.enrolled_at) BETWEEN %s AND %s
			WHERE p.post_type = %s AND p.post_status = 'publish'
			GROUP BY p.ID
			HAVING enrollment_count > 0
			ORDER BY enrollment_count DESC
			LIMIT 10",
				$start_date,
				$end_date,
				SPLMS_POST_TYPES['course']
			)
		);

		$labels      = array();
		$enrollments = array();
		$completions = array();

		foreach ( $performance as $course ) {
			$labels[]      = $course->post_title;
			$enrollments[] = intval( $course->enrollment_count );
			$completions[] = intval( $course->completed_count );
		}

		return array(
			'labels'   => $labels,
			'datasets' => array(
				array(
					'label' => __( 'Enrollments', 'skillpulse-lms' ),
					'data'  => $enrollments,
				),
				array(
					'label' => __( 'Completions', 'skillpulse-lms' ),
					'data'  => $completions,
				),
			),
		);
	}

	/**
	 * Get completion rates chart data.
	 *
	 * @since 1.0.0
	 *
	 * @param string $start_date Start date.
	 * @param string $end_date   End date.
	 * @param array  $filters    Filters.
	 *
	 * @return array Completion rates chart data array.
	 */
	private function get_completion_rates_chart( $start_date, $end_date, $filters ) {
		global $wpdb;

		$rates = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT 
				p.post_title,
				COUNT(e.id) as enrollment_count,
				COUNT(CASE WHEN e.status = 'completed' THEN 1 END) as completed_count
			FROM {$wpdb->posts} p
			LEFT JOIN {$wpdb->prefix}splms_enrollments e ON p.ID = e.course_id
				AND DATE(e.enrolled_at) BETWEEN %s AND %s
			WHERE p.post_type = %s AND p.post_status = 'publish'
			GROUP BY p.ID
			HAVING enrollment_count > 0
			ORDER BY enrollment_count DESC
			LIMIT 10",
				$start_date,
				$end_date,
				SPLMS_POST_TYPES['course']
			)
		);

		$labels = array();
		$data   = array();

		foreach ( $rates as $course ) {
			$completion_rate = $course->enrollment_count > 0 ?
				round( ( $course->completed_count / $course->enrollment_count ) * 100, 2 ) : 0;

			$labels[] = $course->post_title;
			$data[]   = $completion_rate;
		}

		return array(
			'labels' => $labels,
			'data'   => $data,
		);
	}

	/**
	 * Generate CSV data for export.
	 *
	 * @since 1.0.0
	 *
	 * @param string $type       Report type.
	 * @param array  $date_range Date range.
	 * @param array  $filters    Filters.
	 *
	 * @return array CSV data array.
	 */
	private function generate_csv_data( $type, $date_range, $filters ) {
		global $wpdb;

		$csv_data   = array();
		$start_date = isset( $date_range['start'] ) ? gmdate( 'Y-m-d', strtotime( $date_range['start'] ) ) : gmdate( 'Y-m-d', strtotime( '-30 days' ) );
		$end_date   = isset( $date_range['end'] ) ? gmdate( 'Y-m-d', strtotime( $date_range['end'] ) ) : gmdate( 'Y-m-d' );

		switch ( $type ) {
			case 'user-progress':
				$csv_data[] = array( 'User ID', 'Name', 'Email', 'Course', 'Progress %', 'Status', 'Enrolled Date', 'Completed Date', 'Last Accessed' );
				$users      = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT 
						e.user_id,
						e.course_id,
						e.progress,
						e.status,
						e.enrolled_at,
						e.completed_at,
						e.last_accessed_at,
						u.display_name,
						u.user_email,
						p.post_title as course_title
					FROM {$wpdb->prefix}splms_enrollments e
					LEFT JOIN {$wpdb->users} u ON e.user_id = u.ID
					LEFT JOIN {$wpdb->posts} p ON e.course_id = p.ID
					WHERE DATE(e.enrolled_at) BETWEEN %s AND %s
					ORDER BY e.enrolled_at DESC
					LIMIT 1000",
						$start_date,
						$end_date
					)
				);
				foreach ( $users as $user ) {
					$csv_data[] = array(
						intval( $user->user_id ),
						isset( $user->display_name ) ? sanitize_text_field( $user->display_name ) : '',
						isset( $user->user_email ) ? sanitize_email( $user->user_email ) : '',
						isset( $user->course_title ) ? sanitize_text_field( $user->course_title ) : '',
						floatval( $user->progress ) . '%',
						isset( $user->status ) ? sanitize_text_field( $user->status ) : '',
						isset( $user->enrolled_at ) ? sanitize_text_field( $user->enrolled_at ) : '',
						isset( $user->completed_at ) ? sanitize_text_field( $user->completed_at ) : '',
						isset( $user->last_accessed_at ) ? sanitize_text_field( $user->last_accessed_at ) : '',
					);
				}
				break;

			case 'course-analytics':
				$csv_data[] = array( 'Course ID', 'Course Title', 'Total Enrollments', 'Completed', 'Active', 'Completion Rate %', 'Average Progress %', 'Avg Completion Days' );
				$courses    = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT 
						p.ID,
						p.post_title,
						COUNT(DISTINCT e.user_id) as total_enrollments,
						COUNT(DISTINCT CASE WHEN e.status = 'completed' THEN e.user_id END) as completed_enrollments,
						COUNT(DISTINCT CASE WHEN e.status = 'active' THEN e.user_id END) as active_enrollments,
						AVG(e.progress) as average_progress,
						AVG(CASE WHEN e.completed_at IS NOT NULL THEN 
							DATEDIFF(e.completed_at, e.enrolled_at) END) as avg_completion_days
					FROM {$wpdb->posts} p
					LEFT JOIN {$wpdb->prefix}splms_enrollments e ON p.ID = e.course_id
						AND DATE(e.enrolled_at) BETWEEN %s AND %s
					WHERE p.post_type = %s AND p.post_status = 'publish'
					GROUP BY p.ID
					ORDER BY total_enrollments DESC",
						$start_date,
						$end_date,
						SPLMS_POST_TYPES['course']
					)
				);
				foreach ( $courses as $course ) {
					$completion_rate = $course->total_enrollments > 0 ?
						round( ( $course->completed_enrollments / $course->total_enrollments ) * 100, 2 ) : 0;
					$csv_data[]      = array(
						intval( $course->ID ),
						isset( $course->post_title ) ? sanitize_text_field( $course->post_title ) : '',
						intval( $course->total_enrollments ),
						intval( $course->completed_enrollments ),
						intval( $course->active_enrollments ),
						$completion_rate . '%',
						round( floatval( $course->average_progress ), 2 ) . '%',
						$course->avg_completion_days ? round( floatval( $course->avg_completion_days ), 1 ) : 'N/A',
					);
				}
				break;

			case 'certificates':
				$csv_data[]   = array( 'User ID', 'Name', 'Email', 'Course', 'Completion Date', 'Certificate ID', 'Certificate URL' );
				$certificates = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT 
						e.user_id,
						e.course_id,
						e.completed_at,
						e.certificate_id,
						u.display_name,
						u.user_email,
						p.post_title as course_title,
						cm.meta_value as certificate_url
					FROM {$wpdb->prefix}splms_enrollments e
					LEFT JOIN {$wpdb->users} u ON e.user_id = u.ID
					LEFT JOIN {$wpdb->posts} p ON e.course_id = p.ID
					LEFT JOIN {$wpdb->postmeta} cm ON e.certificate_id = cm.post_id 
						// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Meta key needed for certificate URL lookup.
						AND cm.meta_key = 'certificate_url'
					WHERE e.status = 'completed' AND DATE(e.completed_at) BETWEEN %s AND %s
					ORDER BY e.completed_at DESC",
						$start_date,
						$end_date
					)
				);
				foreach ( $certificates as $cert ) {
					$csv_data[] = array(
						intval( $cert->user_id ),
						isset( $cert->display_name ) ? sanitize_text_field( $cert->display_name ) : '',
						isset( $cert->user_email ) ? sanitize_email( $cert->user_email ) : '',
						isset( $cert->course_title ) ? sanitize_text_field( $cert->course_title ) : '',
						isset( $cert->completed_at ) ? sanitize_text_field( $cert->completed_at ) : '',
						isset( $cert->certificate_id ) ? intval( $cert->certificate_id ) : 0,
						isset( $cert->certificate_url ) ? esc_url_raw( $cert->certificate_url ) : '',
					);
				}
				break;

			default:
				$csv_data[] = array( 'Metric', 'Value' );
				$overview   = $this->get_overview_data( $start_date, $end_date, $filters );
				foreach ( $overview as $key => $value ) {
					$csv_data[] = array(
						sanitize_text_field( ucfirst( str_replace( '_', ' ', $key ) ) ),
						is_numeric( $value ) ? floatval( $value ) : sanitize_text_field( $value ),
					);
				}
		}

		return $csv_data;
	}
}
