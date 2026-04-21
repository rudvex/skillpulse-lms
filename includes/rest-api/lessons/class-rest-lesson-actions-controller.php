<?php
/**
 * Lesson Actions REST API Controller
 *
 * Handles REST API endpoints for lesson actions: progress and complete.
 *
 * @package SkillPulse_LMS
 * @since 1.0.0
 *
 * @api
 * Available endpoints:
 * GET    /splms/v1/lessons/{id}/progress  - Get lesson progress for current user
 * PUT    /splms/v1/lessons/{id}/progress  - Update lesson progress
 * POST   /splms/v1/lessons/{id}/complete - Mark lesson as complete
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Lesson Actions REST API Controller class.
 *
 * @since 1.0.0
 */
class SkillPulse_LMS_REST_Lesson_Actions_Controller extends WP_REST_Controller {

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
	 * Register the lesson action routes.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register_routes() {
		// GET /splms/v1/lessons/{id}/progress - Get lesson progress for current user.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)/progress',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_lesson_progress' ),
					'permission_callback' => array( $this, 'get_lesson_progress_permissions_check' ),
					'args'                => array(
						'id' => array(
							'description' => __( 'Lesson ID', 'skillpulse-lms' ),
							'type'        => 'integer',
							'required'    => true,
						),
					),
				),
			)
		);

		// PUT /splms/v1/lessons/{id}/progress - Update lesson progress.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)/progress',
			array(
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_lesson_progress' ),
					'permission_callback' => array( $this, 'update_lesson_progress_permissions_check' ),
					'args'                => array(
						'id'        => array(
							'description' => __( 'Lesson ID', 'skillpulse-lms' ),
							'type'        => 'integer',
							'required'    => true,
						),
						'user_id'   => array(
							'description' => __( 'User ID', 'skillpulse-lms' ),
							'type'        => 'integer',
							'required'    => false,
						),
						'progress'  => array(
							'description' => __( 'Progress percentage', 'skillpulse-lms' ),
							'type'        => 'number',
							'required'    => false,
						),
						'completed' => array(
							'description' => __( 'Lesson completion status', 'skillpulse-lms' ),
							'type'        => 'boolean',
							'required'    => false,
						),
					),
				),
			)
		);

		// POST /splms/v1/lessons/{id}/complete - Mark lesson as complete.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)/complete',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'complete_lesson' ),
					'permission_callback' => array( $this, 'complete_lesson_permissions_check' ),
					'args'                => array(
						'id'             => array(
							'description' => __( 'Lesson ID', 'skillpulse-lms' ),
							'type'        => 'integer',
							'required'    => true,
						),
						'course_id'      => array(
							'description' => __( 'Course ID', 'skillpulse-lms' ),
							'type'        => 'integer',
							'required'    => true,
						),
						'video_progress' => array(
							'description' => __( 'Video progress percentage (for video lessons)', 'skillpulse-lms' ),
							'type'        => 'number',
							'required'    => false,
							'minimum'     => 0,
							'maximum'     => 100,
						),
					),
				),
			)
		);

		// GET /splms/v1/lessons/{id}/video-progress - Get video progress for current user.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)/video-progress',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_video_progress' ),
					'permission_callback' => array( $this, 'get_lesson_progress_permissions_check' ),
					'args'                => array(
						'id' => array(
							'description' => __( 'Lesson ID', 'skillpulse-lms' ),
							'type'        => 'integer',
							'required'    => true,
						),
					),
				),
			)
		);

		// POST /splms/v1/lessons/{id}/video-progress - Save video progress.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)/video-progress',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'save_video_progress' ),
					'permission_callback' => array( $this, 'update_lesson_progress_permissions_check' ),
					'args'                => array(
						'id'             => array(
							'description' => __( 'Lesson ID', 'skillpulse-lms' ),
							'type'        => 'integer',
							'required'    => true,
						),
						'course_id'      => array(
							'description' => __( 'Course ID', 'skillpulse-lms' ),
							'type'        => 'integer',
							'required'    => true,
						),
						'segments'       => array(
							'description' => __( 'Watched video segments (array of seconds)', 'skillpulse-lms' ),
							'type'        => 'array',
							'required'    => true,
						),
						'percentage'     => array(
							'description' => __( 'Watch percentage', 'skillpulse-lms' ),
							'type'        => 'number',
							'required'    => true,
							'minimum'     => 0,
							'maximum'     => 100,
						),
						'lastPosition'   => array(
							'description' => __( 'Last video position in seconds', 'skillpulse-lms' ),
							'type'        => 'number',
							'required'    => false,
							'minimum'     => 0,
						),
						'totalWatchTime' => array(
							'description' => __( 'Total watch time in seconds', 'skillpulse-lms' ),
							'type'        => 'integer',
							'required'    => false,
							'minimum'     => 0,
						),
						'videoType'      => array(
							'description' => __( 'Video type (youtube, vimeo, html5)', 'skillpulse-lms' ),
							'type'        => 'string',
							'required'    => false,
						),
						'videoId'        => array(
							'description' => __( 'Video ID (for YouTube/Vimeo)', 'skillpulse-lms' ),
							'type'        => 'string',
							'required'    => false,
						),
						'duration'       => array(
							'description' => __( 'Video duration in seconds', 'skillpulse-lms' ),
							'type'        => 'number',
							'required'    => false,
							'minimum'     => 0,
						),
					),
				),
			)
		);
	}

	/**
	 * Get lesson progress.
	 *
	 * Retrieves the progress information for a specific lesson and user.
	 * Returns progress percentage, completion status, and time tracking data.
	 *
	 * @since 1.0.0
	 *
	 * @api {get} /splms/v1/lessons/:id/progress Get Lesson Progress
	 * @apiName GetLessonProgress
	 * @apiGroup Lessons
	 * @apiVersion 1.0.0
	 *
	 * @apiDescription Retrieve progress information for a lesson.
	 * Requires user authentication. If user_id is not provided, uses current user.
	 *
	 * @apiParam {Number} id Lesson unique identifier.
	 * @apiParam {Number} [user_id] User ID (defaults to current user).
	 *
	 * @apiError (Error 400) no_user No user specified.
	 * @apiError (Error 401) rest_forbidden User must be logged in.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_lesson_progress( $request ) {
		$lesson_id = $request->get_param( 'id' );
		$user_id   = $request->get_param( 'user_id' ) ? $request->get_param( 'user_id' ) : get_current_user_id();

		if ( ! $user_id ) {
			return new WP_Error( 'no_user', __( 'No user specified.', 'skillpulse-lms' ), array( 'status' => 400 ) );
		}

		global $wpdb;
		$table_name = esc_sql( $wpdb->prefix . 'splms_lesson_progress' );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table name is safe, validated constant.
		$progress = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table_name} WHERE lesson_id = %d AND user_id = %d", $lesson_id, $user_id ) );

		if ( ! $progress ) {
			$progress = array(
				'lesson_id'    => $lesson_id,
				'user_id'      => $user_id,
				'progress'     => 0,
				'completed'    => false,
				'started_at'   => null,
				'completed_at' => null,
				'time_spent'   => 0,
			);
		}

		return rest_ensure_response( $progress );
	}

	/**
	 * Update lesson progress.
	 *
	 * Updates the progress information for a specific lesson and user.
	 * Can update progress percentage and completion status.
	 *
	 * @since 1.0.0
	 *
	 * @api {put} /splms/v1/lessons/:id/progress Update Lesson Progress
	 * @apiName UpdateLessonProgress
	 * @apiGroup Lessons
	 * @apiVersion 1.0.0
	 *
	 * @apiDescription Update progress information for a lesson.
	 * Requires user authentication. If user_id is not provided, uses current user.
	 *
	 * @apiParam {Number} id Lesson unique identifier.
	 * @apiParam {Number} [user_id] User ID (defaults to current user).
	 * @apiParam {Number} [progress] Progress percentage (0-100).
	 * @apiParam {Boolean} [completed] Lesson completion status.
	 *
	 * @apiError (Error 400) no_user No user specified.
	 * @apiError (Error 401) rest_forbidden User must be logged in.
	 * @apiError (Error 500) progress_update_failed Failed to update lesson progress.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function update_lesson_progress( $request ) {
		$lesson_id = $request->get_param( 'id' );
		$user_id   = $request->get_param( 'user_id' ) ? $request->get_param( 'user_id' ) : get_current_user_id();

		if ( ! $user_id ) {
			return new WP_Error( 'no_user', __( 'No user specified.', 'skillpulse-lms' ), array( 'status' => 400 ) );
		}

		global $wpdb;
		$table_name = esc_sql( $wpdb->prefix . 'splms_lesson_progress' );

		$progress_data = array(
			'lesson_id'     => $lesson_id,
			'user_id'       => $user_id,
			'last_accessed' => current_time( 'mysql' ),
		);

		if ( $request->get_param( 'progress' ) !== null ) {
			$progress_data['progress'] = $request->get_param( 'progress' );
		}

		if ( $request->get_param( 'completed' ) !== null ) {
			$progress_data['is_completed'] = $request->get_param( 'completed' ) ? 1 : 0;
			if ( $request->get_param( 'completed' ) ) {
				$progress_data['completed_at'] = current_time( 'mysql' );
				$progress_data['progress']     = 100;
			}
		}

		// Check if progress record exists.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table name is safe, validated constant.
		$existing = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table_name} WHERE lesson_id = %d AND user_id = %d", $lesson_id, $user_id ) );

		if ( $existing ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Custom table write operation.
			$result = $wpdb->update(
				$table_name,
				$progress_data,
				array(
					'lesson_id' => $lesson_id,
					'user_id'   => $user_id,
				)
			);
		} else {
			$progress_data['started_at'] = current_time( 'mysql' );
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Custom table write operation.
			$result = $wpdb->insert( $table_name, $progress_data );
		}

		if ( false === $result ) {
			return new WP_Error( 'progress_update_failed', __( 'Failed to update lesson progress.', 'skillpulse-lms' ), array( 'status' => 500 ) );
		}

		return $this->get_lesson_progress( $request );
	}

	/**
	 * Mark lesson as complete.
	 *
	 * Handles lesson completion with validation for video lessons, access control,
	 * and automatic course progress calculation.
	 *
	 * @since 1.0.0
	 *
	 * @api {post} /splms/v1/lessons/:id/complete Complete Lesson
	 * @apiName CompleteLesson
	 * @apiGroup Lessons
	 * @apiVersion 1.0.0
	 *
	 * @apiDescription Mark a lesson as complete. Validates user access, video completion
	 * requirements (for video lessons), and automatically calculates course progress.
	 *
	 * @apiParam {Number} id Lesson unique identifier.
	 * @apiParam {Number} course_id Course ID.
	 * @apiParam {Number} [video_progress] Video progress percentage (0-100) for video lessons.
	 *
	 * @apiError (Error 401) rest_not_logged_in User must be logged in.
	 * @apiError (Error 400) invalid_ids Invalid lesson or course ID.
	 * @apiError (Error 403) access_denied Access denied to lesson.
	 * @apiError (Error 403) video_incomplete Video completion requirement not met.
	 * @apiError (Error 500) completion_failed Failed to mark lesson as complete.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function complete_lesson( $request ) {
		$lesson_id = $request->get_param( 'id' );
		$course_id = $request->get_param( 'course_id' );
		$user_id   = get_current_user_id();

		if ( ! $user_id ) {
			return new WP_Error(
				'rest_not_logged_in',
				__( 'You must be logged in to complete lessons.', 'skillpulse-lms' ),
				array( 'status' => 401 )
			);
		}

		if ( ! $lesson_id || ! $course_id ) {
			return new WP_Error( 'invalid_ids', __( 'Invalid lesson or course ID.', 'skillpulse-lms' ), array( 'status' => 400 ) );
		}

		// Check if user has access to this lesson.
		$lessons_instance = SkillPulse_LMS_Lessons::get_instance();
		if ( ! $lessons_instance->user_can_access_lesson( $lesson_id, $user_id ) ) {
			return new WP_Error( 'access_denied', __( 'Access denied to this lesson.', 'skillpulse-lms' ), array( 'status' => 403 ) );
		}

		// Validate video completion requirement for video lessons.
		$lesson_settings = splms_get_lesson_settings( $lesson_id );
		$lesson_type     = isset( $lesson_settings['lesson_type'] ) ? $lesson_settings['lesson_type'] : 'text';

		if ( 'video' === $lesson_type ) {
			$completion_required = isset( $lesson_settings['lesson_completion_required'] ) ? intval( $lesson_settings['lesson_completion_required'] ) : 100;
			$video_progress      = $request->get_param( 'video_progress' ) ? floatval( $request->get_param( 'video_progress' ) ) : 0.0;

			// Server-side validation: Check if watched percentage meets requirement.
			if ( $video_progress < $completion_required ) {
				/* translators: %1$d: Required completion percentage, %2$.1f: Current progress percentage. */
				$error_message = sprintf(
					/* translators: %1$d: Required completion percentage, %2$.1f: Current progress percentage. */
					__( 'You must watch at least %1$d%% of the video to complete this lesson. Current progress: %2$.1f%%', 'skillpulse-lms' ),
					$completion_required,
					$video_progress
				);
				return new WP_Error(
					'video_incomplete',
					$error_message,
					array( 'status' => 403 )
				);
			}
		}

		// Mark lesson complete in database table.
		$progress_query = SkillPulse_LMS_Lesson_Progress_Query::get_instance();
		$result         = $progress_query->complete_lesson( $lesson_id, $user_id, $course_id );

		if ( false === $result ) {
			return new WP_Error( 'completion_failed', __( 'Failed to mark lesson as complete. Please try again.', 'skillpulse-lms' ), array( 'status' => 500 ) );
		}

		// Fire completion action.
		do_action( 'splms_lesson_completed', $lesson_id, $user_id );

		// Calculate updated course progress.
		$progress_data = $lessons_instance->calculate_course_progress( $user_id, $course_id );

		// Sync progress to enrollment database table.
		$enrollment = SkillPulse_LMS_Enrollment::get_instance();
		$enrollment->update_enrollment_progress( $user_id, $course_id, $progress_data['percentage'] );

		// Log activity.
		SkillPulse_LMS_User_Activity_Query::get_instance()->log_activity( $user_id, 'lesson_completed', $course_id, $lesson_id, 'lesson' );

		// Return success response with progress data.
		return rest_ensure_response(
			array(
				'success'  => true,
				'message'  => __( 'Lesson marked as complete!', 'skillpulse-lms' ),
				'progress' => $progress_data,
			)
		);
	}

	/**
	 * Get video progress for a lesson.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, WP_Error object on failure.
	 */
	public function get_video_progress( $request ) {
		$lesson_id = absint( $request['id'] );
		$user_id   = get_current_user_id();

		if ( ! $user_id ) {
			return new WP_Error(
				'rest_not_logged_in',
				__( 'You must be logged in to view video progress.', 'skillpulse-lms' ),
				array( 'status' => 401 )
			);
		}

		// Get video progress from database.
		$progress_query = SkillPulse_LMS_Lesson_Progress_Query::get_instance();
		$video_data     = $progress_query->get_video_progress( $user_id, $lesson_id );

		if ( null === $video_data ) {
			// Return empty progress if not found.
			$video_data = array(
				'segments'       => array(),
				'percentage'     => 0,
				'lastPosition'   => 0,
				'totalWatchTime' => 0,
				'lastUpdated'    => null,
			);
		}

		return rest_ensure_response( $video_data );
	}

	/**
	 * Save video progress for a lesson.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, WP_Error object on failure.
	 */
	public function save_video_progress( $request ) {
		$lesson_id = absint( $request['id'] );
		$course_id = absint( $request['course_id'] );
		$user_id   = get_current_user_id();

		if ( ! $user_id ) {
			return new WP_Error(
				'rest_not_logged_in',
				__( 'You must be logged in to save video progress.', 'skillpulse-lms' ),
				array( 'status' => 401 )
			);
		}

		// Validate lesson and course exist.
		if ( ! get_post( $lesson_id ) || ! get_post( $course_id ) ) {
			return new WP_Error(
				'rest_invalid_id',
				__( 'Invalid lesson or course ID.', 'skillpulse-lms' ),
				array( 'status' => 400 )
			);
		}

		// Prepare video data.
		$video_data = array(
			'segments'       => array_map( 'intval', (array) $request['segments'] ),
			'percentage'     => floatval( $request['percentage'] ),
			'lastPosition'   => floatval( $request['lastPosition'] ?? 0 ),
			'totalWatchTime' => intval( $request['totalWatchTime'] ?? 0 ),
			'lastUpdated'    => current_time( 'mysql' ),
		);

		// Add optional fields if provided.
		if ( ! empty( $request['videoType'] ) ) {
			$video_data['videoType'] = sanitize_text_field( $request['videoType'] );
		}
		if ( ! empty( $request['videoId'] ) ) {
			$video_data['videoId'] = sanitize_text_field( $request['videoId'] );
		}
		if ( ! empty( $request['duration'] ) ) {
			$video_data['duration'] = floatval( $request['duration'] );
		}

		// Save to database.
		$progress_query = SkillPulse_LMS_Lesson_Progress_Query::get_instance();
		$result         = $progress_query->save_video_progress( $user_id, $lesson_id, $course_id, $video_data );

		if ( false === $result ) {
			return new WP_Error(
				'rest_save_failed',
				__( 'Failed to save video progress.', 'skillpulse-lms' ),
				array( 'status' => 500 )
			);
		}

		// Update time_spent in lesson progress.
		if ( isset( $video_data['totalWatchTime'] ) ) {
			$progress_query->update_lesson_time_spent( $user_id, $lesson_id, $video_data['totalWatchTime'] );
		}

		return rest_ensure_response(
			array(
				'success' => true,
				'message' => __( 'Video progress saved successfully.', 'skillpulse-lms' ),
				'data'    => $video_data,
			)
		);
	}

	/**
	 * Check permissions for getting lesson progress.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return bool|WP_Error True if the request has access, WP_Error object otherwise.
	 */
	public function get_lesson_progress_permissions_check( $request ) {
		$lesson_id = $request->get_param( 'id' );
		$user_id   = get_current_user_id();

		if ( ! $lesson_id ) {
			return new WP_Error( 'invalid_lesson_id', __( 'Invalid lesson ID.', 'skillpulse-lms' ), array( 'status' => 400 ) );
		}

		$access_control = SkillPulse_LMS_Access_Control::get_instance();

		// If user is logged in, check full access control.
		if ( $user_id ) {
			if ( ! $access_control->user_can_access_lesson( $user_id, $lesson_id ) ) {
				return new WP_Error( 'rest_forbidden', __( 'Sorry, you are not allowed to view progress for this lesson.', 'skillpulse-lms' ), array( 'status' => rest_authorization_required_code() ) );
			}
			return true;
		}

		// For non-logged-in users, only allow if guest preview is enabled.
		if ( ! splms_is_lesson_guest_preview_available( $lesson_id ) ) {
			return new WP_Error( 'rest_forbidden', __( 'Sorry, you must be logged in to view lesson progress.', 'skillpulse-lms' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}

	/**
	 * Check permissions for updating lesson progress.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return bool|WP_Error True if the request has access, WP_Error object otherwise.
	 */
	public function update_lesson_progress_permissions_check( $request ) {
		return is_user_logged_in();
	}

	/**
	 * Check permissions for completing a lesson.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return bool|WP_Error True if the request has access, WP_Error object otherwise.
	 */
	public function complete_lesson_permissions_check( $request ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- $request is required by REST API callback signature.
		if ( ! is_user_logged_in() ) {
			return new WP_Error(
				'rest_not_logged_in',
				__( 'You must be logged in to complete lessons.', 'skillpulse-lms' ),
				array( 'status' => 401 )
			);
		}

		return true;
	}
}
