<?php
/**
 * Course Actions REST API Controller
 *
 * Handles REST API endpoints for course actions: enroll, unenroll, progress, wishlist, and bookmark.
 *
 * @package SkillPulse_LMS
 * @since 1.0.0
 *
 * @api
 * Available endpoints:
 * POST   /splms/v1/courses/{id}/enroll    - Enroll user in course
 * POST   /splms/v1/courses/{id}/unenroll  - Unenroll user from course
 * GET    /splms/v1/courses/{id}/progress  - Get course progress for current user
 * POST   /splms/v1/courses/{id}/wishlist  - Toggle course wishlist
 * POST   /splms/v1/courses/{id}/bookmark  - Toggle bookmark for lesson/quiz
 * GET    /splms/v1/courses/{id}/purchase  - Get purchase information for course
 * POST   /splms/v1/courses/{id}/purchase  - Initiate purchase with payment method (creates order)
 * POST   /splms/v1/courses/{id}/purchase/verify - Verify payment and complete order
 * POST   /splms/v1/courses/{id}/purchase/webhook - Handle payment gateway webhooks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Course Actions REST API Controller class.
 *
 * @since 1.0.0
 */
class SkillPulse_LMS_REST_Course_Actions_Controller extends WP_REST_Controller {

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
	 * Register the course action routes.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register_routes() {
		// POST /splms/v1/courses/{id}/enroll - Enroll user in course.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>\d+)/enroll',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'enroll_in_course' ),
					'permission_callback' => array( $this, 'enroll_in_course_permissions_check' ),
					'args'                => array(
						'id' => array(
							'validate_callback' => function ( $param, $request, $key ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- $request and $key are required by REST API callback signature.
								return is_numeric( $param );
							},
							'sanitize_callback' => 'absint',
						),
					),
				),
			)
		);

		// POST /splms/v1/courses/{id}/unenroll - Unenroll user from course.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>\d+)/unenroll',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'unenroll_from_course' ),
					'permission_callback' => array( $this, 'unenroll_from_course_permissions_check' ),
					'args'                => array(
						'id' => array(
							'validate_callback' => function ( $param, $request, $key ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- $request and $key are required by REST API callback signature.
								return is_numeric( $param );
							},
							'sanitize_callback' => 'absint',
						),
					),
				),
			)
		);

		// GET /splms/v1/courses/{id}/progress - Get course progress for current user.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>\d+)/progress',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_course_progress' ),
					'permission_callback' => array( $this, 'get_course_progress_permissions_check' ),
					'args'                => array(
						'id' => array(
							'validate_callback' => function ( $param, $request, $key ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- $request and $key are required by REST API callback signature.
								return is_numeric( $param );
							},
							'sanitize_callback' => 'absint',
						),
					),
				),
			)
		);

		// POST /splms/v1/courses/{id}/wishlist - Toggle course wishlist.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>\d+)/wishlist',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'toggle_wishlist' ),
					'permission_callback' => array( $this, 'toggle_wishlist_permissions_check' ),
					'args'                => array(
						'id' => array(
							'validate_callback' => function ( $param, $request, $key ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- $request and $key are required by REST API callback signature.
								return is_numeric( $param );
							},
							'sanitize_callback' => 'absint',
						),
					),
				),
			)
		);

		// POST /splms/v1/courses/{id}/bookmark - Toggle bookmark for lessons/quizzes (matches frontend behavior).
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>\d+)/bookmark',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'toggle_bookmark' ),
					'permission_callback' => array( $this, 'toggle_bookmark_permissions_check' ),
					'args'                => array(
						'id'        => array(
							'validate_callback' => function ( $param, $request, $key ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- $request and $key are required by REST API callback signature.
								return is_numeric( $param );
							},
							'sanitize_callback' => 'absint',
						),
						'item_id'   => array(
							'description'       => __( 'Lesson or Quiz ID to bookmark.', 'skillpulse-lms' ),
							'type'              => 'integer',
							'validate_callback' => function ( $param, $request, $key ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
								return is_numeric( $param );
							},
							'sanitize_callback' => 'absint',
						),
						'item_type' => array(
							'description' => __( 'Type of item: lesson or quiz.', 'skillpulse-lms' ),
							'type'        => 'string',
							'enum'        => array( 'lesson', 'quiz' ),
						),
					),
				),
			)
		);
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
	 * Enroll user in course.
	 *
	 * Handles course enrollment with validation for enrollment dates, capacity,
	 * purchase requirements, and existing enrollment status.
	 *
	 * @since 1.0.0
	 *
	 * @api {post} /splms/v1/courses/:id/enroll Enroll in Course
	 * @apiName EnrollInCourse
	 * @apiGroup Courses
	 * @apiVersion 1.0.0
	 *
	 * @apiDescription Enroll the current user in a course. Validates enrollment dates,
	 * course capacity, purchase requirements, and existing enrollment status.
	 *
	 * @apiParam {Number} id Course unique identifier.
	 *
	 * @apiError (Error 401) rest_not_logged_in User must be logged in.
	 * @apiError (Error 400) invalid_course_id Invalid course ID provided.
	 * @apiError (Error 404) course_not_found Course not found or not available.
	 * @apiError (Error 403) enrollment_closed Enrollment is closed for this course.
	 * @apiError (Error 403) course_full Course is full and cannot accept more enrollments.
	 * @apiError (Error 403) purchase_required User must purchase this course before enrolling.
	 * @apiError (Error 500) enrollment_failed Failed to enroll in course.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function enroll_in_course( $request ) {
		$user_id   = get_current_user_id();
		$course_id = $request->get_param( 'id' );

		if ( ! $course_id ) {
			return new WP_Error( 'invalid_course_id', __( 'Invalid course ID.', 'skillpulse-lms' ), array( 'status' => 400 ) );
		}

		// Check if course exists and is published.
		$course = get_post( $course_id );
		if ( ! $course || SPLMS_POST_TYPES['course'] !== $course->post_type || 'publish' !== $course->post_status ) {
			return new WP_Error( 'course_not_found', __( 'Course not found or not available.', 'skillpulse-lms' ), array( 'status' => 404 ) );
		}

		// Check enrollment dates.
		$enrollment_dates = splms_get_course_enrollment_dates( $course_id );
		if ( ! $enrollment_dates['is_open'] ) {
			if ( ! empty( $enrollment_dates['start_date'] ) && ! empty( $enrollment_dates['end_date'] ) ) {
				$start_date = date_i18n( get_option( 'date_format' ), strtotime( $enrollment_dates['start_date'] ) );
				$end_date   = date_i18n( get_option( 'date_format' ), strtotime( $enrollment_dates['end_date'] ) );
				/* translators: %1$s: Start date, %2$s: End date. */
				$error_message = sprintf( __( 'Enrollment is closed for this course. Enrollment period: %1$s to %2$s', 'skillpulse-lms' ), $start_date, $end_date );
				return new WP_Error(
					'enrollment_closed',
					$error_message,
					array( 'status' => 403 )
				);
			} else {
				return new WP_Error( 'enrollment_closed', __( 'Enrollment is currently closed for this course.', 'skillpulse-lms' ), array( 'status' => 403 ) );
			}
		}

		// Check course capacity.
		$capacity_info = splms_get_course_max_enrollment_info( $course_id );
		if ( $capacity_info['is_full'] ) {
			/* translators: %1$d: Enrolled count, %2$d: Max enrollment. */
			$error_message = sprintf( __( 'This course is full (%1$d/%2$d students). No more enrollments are allowed.', 'skillpulse-lms' ), $capacity_info['enrolled_count'], $capacity_info['max_enrollment'] );
			return new WP_Error(
				'course_full',
				$error_message,
				array( 'status' => 403 )
			);
		}

		// Get course access info to check if it's a paid course.
		$course_access_info = splms_get_course_access_info( $course_id );
		$is_paid_course     = 'public_paid' === $course_access_info['course_access_type'] && splms_is_paid_courses_enabled();

		// For paid courses, check if user has purchased.
		if ( $is_paid_course ) {
			$has_purchased = splms_has_user_purchased_course( $course_id, $user_id );
			if ( ! $has_purchased ) {
				return new WP_Error( 'purchase_required', __( 'You must purchase this course before enrolling.', 'skillpulse-lms' ), array( 'status' => 402 ) );
			}
		}

		// Check membership requirements.
		if ( ! splms_user_has_required_membership( $user_id, $course_id ) ) {
			$course_settings      = splms_get_course_settings( $course_id );
			$required_memberships = isset( $course_settings['required_memberships'] )
				? $course_settings['required_memberships']
				: array();

			if ( ! empty( $required_memberships ) ) {
				return new WP_Error(
					'membership_required',
					__( 'This course requires a membership. Please purchase a membership to enroll.', 'skillpulse-lms' ),
					array( 'status' => 403 )
				);
			}
		}

		// Check if already enrolled (active status).
		$enrollments_query   = SkillPulse_LMS_Enrollments_Query::get_instance();
		$existing_enrollment = $enrollments_query->get_enrollment( $user_id, $course_id );

		// If user is already actively enrolled, return success.
		if ( $existing_enrollment && 'active' === $existing_enrollment->status ) {
			return rest_ensure_response(
				array(
					'success'       => true,
					'message'       => __( 'You are already enrolled in this course.', 'skillpulse-lms' ),
					'enrollment_id' => $existing_enrollment->id,
				)
			);
		}

		// Enroll user in database (will update if inactive, insert if new).
		$enrollment_class  = SkillPulse_LMS_Enrollment::get_instance();
		$enrollment_result = $enrollment_class->enroll_user_in_course( $user_id, $course_id );

		if ( false === $enrollment_result ) {
			return new WP_Error( 'enrollment_failed', __( 'Failed to enroll in course. Please try again.', 'skillpulse-lms' ), array( 'status' => 500 ) );
		}

		// Log activity (already done in enroll_user_in_course, but keeping for consistency).
		SkillPulse_LMS_User_Activity_Query::get_instance()->log_activity( $user_id, 'course_enrolled', $course_id );

		// Send success response.
		return rest_ensure_response(
			array(
				'success'       => true,
				'message'       => __( 'Successfully enrolled in course!', 'skillpulse-lms' ),
				'enrollment_id' => $enrollment_result,
				'status'        => 'enrolled',  // Add missing status key for tests.
			)
		);
	}

	/**
	 * Check permissions for enrolling in a course.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return bool|WP_Error True if user is logged in, WP_Error otherwise.
	 */
	public function enroll_in_course_permissions_check( $request ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- $request is required by REST API callback signature.
		if ( ! is_user_logged_in() ) {
			return new WP_Error(
				'rest_not_logged_in',
				__( 'You must be logged in to enroll in courses.', 'skillpulse-lms' ),
				array( 'status' => 401 )
			);
		}

		return true;
	}

	/**
	 * Unenroll user from a course.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function unenroll_from_course( $request ) {
		$user_id   = get_current_user_id();
		$course_id = $request->get_param( 'id' );

		if ( ! $course_id ) {
			return new WP_Error( 'invalid_course_id', __( 'Invalid course ID.', 'skillpulse-lms' ), array( 'status' => 400 ) );
		}

		// Check if student unenrollment is allowed.
		if ( ! splms_is_student_unenrollment_allowed() ) {
			return new WP_Error(
				'unenrollment_disabled',
				__( 'Student unenrollment is disabled. Please contact an administrator.', 'skillpulse-lms' ),
				array( 'status' => 403 )
			);
		}

		// Check if course exists.
		$course = get_post( $course_id );
		if ( ! $course || SPLMS_POST_TYPES['course'] !== $course->post_type ) {
			return new WP_Error( 'course_not_found', __( 'Course not found.', 'skillpulse-lms' ), array( 'status' => 404 ) );
		}

		// Check if user is enrolled.
		$enrollments_query = SkillPulse_LMS_Enrollments_Query::get_instance();
		$enrollment        = $enrollments_query->get_enrollment( $user_id, $course_id );

		if ( ! $enrollment ) {
			return new WP_Error( 'not_enrolled', __( 'You are not enrolled in this course.', 'skillpulse-lms' ), array( 'status' => 404 ) );
		}

		// Update enrollment status to 'cancelled'.
		$result = $enrollments_query->update_enrollment( $enrollment->id, array( 'status' => 'cancelled' ) );

		if ( ! $result ) {
			return new WP_Error( 'unenrollment_failed', __( 'Failed to unenroll from course. Please try again.', 'skillpulse-lms' ), array( 'status' => 500 ) );
		}

		// Log activity.
		do_action( 'splms_user_unenrolled', $user_id, $course_id );

		return rest_ensure_response(
			array(
				'success' => true,
				'message' => __( 'Successfully unenrolled from course.', 'skillpulse-lms' ),
			)
		);
	}

	/**
	 * Check permissions for unenrolling from a course.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_Error|bool True if request has access, WP_Error object otherwise.
	 */
	public function unenroll_from_course_permissions_check( $request ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- $request is required by REST API callback signature.
		if ( ! is_user_logged_in() ) {
			return new WP_Error(
				'rest_not_logged_in',
				__( 'You must be logged in to unenroll from courses.', 'skillpulse-lms' ),
				array( 'status' => 401 )
			);
		}

		return true;
	}

	/**
	 * Get course progress for current user.
	 *
	 * Retrieves detailed progress information for a course including completed lessons,
	 * passed quizzes, total items, and overall percentage.
	 *
	 * @since 1.0.0
	 *
	 * @api {get} /splms/v1/courses/:id/progress Get Course Progress
	 * @apiName GetCourseProgress
	 * @apiGroup Courses
	 * @apiVersion 1.0.0
	 *
	 * @apiDescription Retrieve course progress for the current user. Includes completed
	 * lessons, passed quizzes, total items, and overall completion percentage.
	 *
	 * @apiParam {Number} id Course unique identifier.
	 *
	 * @apiError (Error 401) rest_not_logged_in User must be logged in.
	 * @apiError (Error 403) access_denied User does not have access to this course.
	 * @apiError (Error 404) course_not_found Course not found.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_course_progress( $request ) {
		$course_id = $request->get_param( 'id' );
		$user_id   = get_current_user_id();

		if ( ! $user_id ) {
			return new WP_Error(
				'rest_not_logged_in',
				__( 'You must be logged in to view course progress.', 'skillpulse-lms' ),
				array( 'status' => 401 )
			);
		}

		// Check if course exists.
		$course = get_post( $course_id );
		if ( ! $course || SPLMS_POST_TYPES['course'] !== $course->post_type ) {
			return new WP_Error( 'course_not_found', __( 'Course not found.', 'skillpulse-lms' ), array( 'status' => 404 ) );
		}

		// Check if user has access to this course.
		if ( ! $this->user_has_course_access( $user_id, $course_id ) && ! current_user_can( 'edit_posts' ) ) {
			return new WP_Error(
				'access_denied',
				__( 'You do not have access to this course.', 'skillpulse-lms' ),
				array( 'status' => 403 )
			);
		}

		// Calculate course progress.
		$lessons_instance = SkillPulse_LMS_Lessons::get_instance();
		$progress_data    = $lessons_instance->calculate_course_progress( $user_id, $course_id );

		// Get enrollment status.
		$enrollment_status = $this->get_user_enrollment_status( $user_id, $course_id );

		// Get enrollment record for additional info.
		$enrollments_query = SkillPulse_LMS_Enrollments_Query::get_instance();
		$enrollment        = $enrollments_query->get_enrollment( $user_id, $course_id );

		$response_data = array(
			'course_id'         => $course_id,
			'user_id'           => $user_id,
			'overall_progress'  => floatval( $progress_data['percentage'] ),
			'lessons_completed' => intval( $progress_data['completed_lessons'] ),
			'total_lessons'     => intval( $progress_data['total_lessons'] ?? $progress_data['total_items'] ),
			'quizzes_completed' => intval( $progress_data['passed_quizzes'] ),
			'total_quizzes'     => intval( $progress_data['total_quizzes'] ?? 0 ),
			'time_spent'        => intval( $progress_data['time_spent'] ?? 0 ),
			'enrollment_status' => $enrollment_status,
			'last_accessed'     => $progress_data['last_accessed'],
		);

		// Add enrollment dates if available.
		if ( $enrollment ) {
			$response_data['enrolled_at']  = isset( $enrollment->enrolled_at ) ? $enrollment->enrolled_at : null;
			$response_data['completed_at'] = isset( $enrollment->completed_at ) ? $enrollment->completed_at : null;
		}

		return rest_ensure_response( $response_data );
	}

	/**
	 * Check permissions for getting course progress.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return bool|WP_Error True if user is logged in, WP_Error otherwise.
	 */
	public function get_course_progress_permissions_check( $request ) {
		if ( ! is_user_logged_in() ) {
			return new WP_Error(
				'rest_not_logged_in',
				__( 'You must be logged in to view course progress.', 'skillpulse-lms' ),
				array( 'status' => 401 )
			);
		}

		$course_id = (int) $request['id'];
		$user_id   = get_current_user_id();

		// Check if user is enrolled in the course.
		if ( ! splms_is_user_enrolled( $course_id, $user_id ) ) {
			return new WP_Error(
				'rest_course_not_enrolled',
				__( 'You must be enrolled in this course to view progress.', 'skillpulse-lms' ),
				array( 'status' => 403 )
			);
		}

		return true;
	}

	/**
	 * Toggle course wishlist.
	 *
	 * Adds or removes a course from the user's wishlist.
	 *
	 * @since 1.0.0
	 *
	 * @api {post} /splms/v1/courses/:id/wishlist Toggle Course Wishlist
	 * @apiName ToggleCourseWishlist
	 * @apiGroup Courses
	 * @apiVersion 1.0.0
	 *
	 * @apiDescription Toggle course wishlist status for the current user. Adds course to wishlist if not present, removes if already in wishlist.
	 *
	 * @apiParam {Number} id Course unique identifier.
	 *
	 * @apiError (Error 401) rest_not_logged_in User must be logged in.
	 * @apiError (Error 400) invalid_course_id Invalid course ID.
	 * @apiError (Error 404) course_not_found Course not found.
	 * @apiError (Error 403) wishlist_disabled Wishlist feature is disabled.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function toggle_wishlist( $request ) {
		$user_id   = get_current_user_id();
		$course_id = $request->get_param( 'id' );

		if ( ! $course_id ) {
			return new WP_Error( 'invalid_course_id', __( 'Invalid course ID.', 'skillpulse-lms' ), array( 'status' => 400 ) );
		}

		// Check if course exists and is published.
		$course = get_post( $course_id );
		if ( ! $course || SPLMS_POST_TYPES['course'] !== $course->post_type || 'publish' !== $course->post_status ) {
			return new WP_Error( 'course_not_found', __( 'Course not found or not available.', 'skillpulse-lms' ), array( 'status' => 404 ) );
		}

		// Check if feature enabled.
		$wishlist_enabled = function_exists( 'splms_get_setting' ) ? splms_get_setting( 'enable_course_wishlist', true ) : true;
		if ( ! $wishlist_enabled ) {
			return new WP_Error( 'wishlist_disabled', __( 'Wishlist is disabled by site admin.', 'skillpulse-lms' ), array( 'status' => 403 ) );
		}

		$wishlist = get_user_meta( $user_id, '_splms_course_wishlist', true );
		if ( ! is_array( $wishlist ) ) {
			$wishlist = array();
		}
		// Normalize to integers to ensure consistent comparisons.
		$wishlist = array_map( 'intval', $wishlist );

		$is_in_wishlist = in_array( (int) $course_id, $wishlist, true );

		if ( $is_in_wishlist ) {
			// Remove from wishlist.
			$wishlist = array_values( array_diff( $wishlist, array( (int) $course_id ) ) );
			$message  = __( 'Course removed from wishlist.', 'skillpulse-lms' );
			$action   = 'removed';
		} else {
			// Add to wishlist.
			$wishlist[] = (int) $course_id;
			$message    = __( 'Course added to wishlist.', 'skillpulse-lms' );
			$action     = 'added';
		}

		update_user_meta( $user_id, '_splms_course_wishlist', array_values( $wishlist ) );

		return rest_ensure_response(
			array(
				'success'        => true,
				'message'        => $message,
				'action'         => $action,
				'is_wishlisted'  => ! $is_in_wishlist,
				'wishlist_count' => count( $wishlist ),
			)
		);
	}

	/**
	 * Toggle bookmark for lesson/quiz (matches frontend behavior).
	 *
	 * Adds or removes a lesson or quiz from the user's bookmarks using _splms_bookmarks.
	 * This matches the frontend AJAX behavior which bookmarks lessons/quizzes, not courses.
	 *
	 * @since 1.0.0
	 *
	 * @api {post} /splms/v1/courses/:id/bookmark Toggle Lesson/Quiz Bookmark
	 * @apiName ToggleBookmark
	 * @apiGroup Courses
	 * @apiVersion 1.0.0
	 *
	 * @apiDescription Toggle bookmark status for a lesson or quiz. Requires item_id and item_type parameters.
	 *
	 * @apiParam {Number} id Course unique identifier (parent course).
	 * @apiParam {Number} item_id Lesson or Quiz ID to bookmark.
	 * @apiParam {String} item_type Type of item: 'lesson' or 'quiz'.
	 *
	 * @apiError (Error 401) rest_not_logged_in User must be logged in.
	 * @apiError (Error 400) invalid_item_id Invalid item ID.
	 * @apiError (Error 404) item_not_found Item not found or not available.
	 * @apiError (Error 403) bookmark_disabled Bookmark feature is disabled.
	 * @apiError (Error 403) not_enrolled User must be enrolled in the course.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function toggle_bookmark( $request ) {
		$user_id   = get_current_user_id();
		$course_id = $request->get_param( 'id' );
		$item_id   = $request->get_param( 'item_id' );
		$item_type = $request->get_param( 'item_type' );

		if ( ! $item_id ) {
			return new WP_Error( 'invalid_item_id', __( 'Invalid item ID. Please provide item_id and item_type (lesson or quiz).', 'skillpulse-lms' ), array( 'status' => 400 ) );
		}

		if ( ! in_array( $item_type, array( 'lesson', 'quiz' ), true ) ) {
			return new WP_Error( 'invalid_item_type', __( 'Invalid item type. Must be "lesson" or "quiz".', 'skillpulse-lms' ), array( 'status' => 400 ) );
		}

		// Check if item exists and is published.
		$item               = get_post( $item_id );
		$expected_post_type = 'lesson' === $item_type ? SPLMS_POST_TYPES['lesson'] : SPLMS_POST_TYPES['quiz'];
		if ( ! $item || $expected_post_type !== $item->post_type || 'publish' !== $item->post_status ) {
			return new WP_Error( 'item_not_found', __( 'Item not found or not available.', 'skillpulse-lms' ), array( 'status' => 404 ) );
		}

		// Check if feature enabled.
		$bookmark_enabled = function_exists( 'splms_get_setting' ) ? splms_get_setting( 'enable_bookmarks', true ) : true;
		if ( ! $bookmark_enabled ) {
			return new WP_Error( 'bookmark_disabled', __( 'Bookmarks are disabled by site admin.', 'skillpulse-lms' ), array( 'status' => 403 ) );
		}

		// Check if user is enrolled in the parent course (if course_id provided).
		if ( $course_id ) {
			$enrollments_query = SkillPulse_LMS_Enrollments_Query::get_instance();
			$enrollment        = $enrollments_query->get_enrollment( $user_id, $course_id );
			if ( ! $enrollment ) {
				/* translators: %s: Item type (lesson or quiz). */
				return new WP_Error( 'not_enrolled', sprintf( __( 'You must be enrolled in the course to bookmark %s.', 'skillpulse-lms' ), $item_type ), array( 'status' => 403 ) );
			}
		}

		$bookmarks = get_user_meta( $user_id, '_splms_bookmarks', true );
		if ( ! is_array( $bookmarks ) ) {
			$bookmarks = array();
		}
		$bookmarks = array_map( 'intval', $bookmarks );

		$is_bookmarked = in_array( (int) $item_id, $bookmarks, true );

		if ( $is_bookmarked ) {
			$bookmarks = array_values( array_diff( $bookmarks, array( (int) $item_id ) ) );
			$message   = __( 'Removed from bookmarks.', 'skillpulse-lms' );
			$action    = 'removed';
		} else {
			$bookmarks[] = (int) $item_id;
			$bookmarks   = array_values( array_unique( $bookmarks ) );
			$message     = __( 'Added to bookmarks.', 'skillpulse-lms' );
			$action      = 'added';
		}

		update_user_meta( $user_id, '_splms_bookmarks', $bookmarks );

		return rest_ensure_response(
			array(
				'success'         => true,
				'message'         => $message,
				'action'          => $action,
				'item_id'         => $item_id,
				'item_type'       => $item_type,
				'is_bookmarked'   => ! $is_bookmarked,
				'bookmarks_count' => count( $bookmarks ),
			)
		);
	}

	/**
	 * Check if user can toggle wishlist.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return bool|WP_Error True if user is logged in, WP_Error otherwise.
	 */
	public function toggle_wishlist_permissions_check( $request ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- $request is required by REST API callback signature.
		if ( ! is_user_logged_in() ) {
			return new WP_Error(
				'rest_not_logged_in',
				__( 'You must be logged in to manage your wishlist.', 'skillpulse-lms' ),
				array( 'status' => 401 )
			);
		}

		return true;
	}

	/**
	 * Check if user can toggle bookmark.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return bool|WP_Error True if user is logged in, WP_Error otherwise.
	 */
	public function toggle_bookmark_permissions_check( $request ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- $request is required by REST API callback signature.
		if ( ! is_user_logged_in() ) {
			return new WP_Error(
				'rest_not_logged_in',
				__( 'You must be logged in to manage your bookmarks.', 'skillpulse-lms' ),
				array( 'status' => 401 )
			);
		}

		return true;
	}
}
