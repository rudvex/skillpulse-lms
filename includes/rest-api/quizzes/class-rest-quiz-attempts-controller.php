<?php
/**
 * Quiz Attempts REST API Controller
 *
 * Handles REST API endpoints for quiz attempts management operations.
 * Provides endpoints for viewing, verifying, and deleting quiz attempts.
 *
 * @since   1.0.0
 *
 * @package SkillPulse_LMS
 * @api
 * Available endpoints:
 * GET    /splms/v1/quiz-attempts - Get list of quiz attempts
 * GET    /splms/v1/quiz-attempts/{id} - Get single quiz attempt by ID
 * POST   /splms/v1/quiz-attempts/{id}/verify - Verify a quiz attempt
 * DELETE /splms/v1/quiz-attempts/{id} - Delete a quiz attempt
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class SkillPulse_LMS_REST_Quiz_Attempts_Controller
 *
 * @since 1.0.0
 */
class SkillPulse_LMS_REST_Quiz_Attempts_Controller extends WP_REST_Controller {

	/**
	 * Table name constant.
	 *
	 * @since 1.0.0
	 */
	const TABLE_NAME = 'splms_quiz_attempts';

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function __construct() {
		$this->namespace = splms_rest_namespace() . '/' . splms_rest_version();
		$this->rest_base = 'quiz-attempts';
	}

	/**
	 * Register the routes for the objects of the controller.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register_routes() {
		// GET /splms/v1/quiz-attempts - Get quiz attempts list.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_attempts' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'                => $this->get_collection_params(),
				),
			)
		);

		// GET /splms/v1/quiz-attempts/{id} - Get single attempt.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>\d+)',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_attempt' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'                => array(
						'id' => array(
							'validate_callback' => function ( $param ) {
								return is_numeric( $param );
							},
							'sanitize_callback' => 'absint',
						),
					),
				),
			)
		);

		// POST /splms/v1/quiz-attempts/{id}/verify - Verify attempt.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>\d+)/verify',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'verify_attempt' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'                => array(
						'id' => array(
							'validate_callback' => function ( $param ) {
								return is_numeric( $param );
							},
							'sanitize_callback' => 'absint',
						),
					),
				),
			)
		);

		// DELETE /splms/v1/quiz-attempts/{id} - Delete attempt.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>\d+)',
			array(
				array(
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'delete_attempt' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'                => array(
						'id' => array(
							'validate_callback' => function ( $param ) {
								return is_numeric( $param );
							},
							'sanitize_callback' => 'absint',
						),
					),
				),
			)
		);

		// GET /splms/v1/quiz-attempts/{id}/questions - Get questions with answer comparison.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>\d+)/questions',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_attempt_questions' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'                => array(
						'id' => array(
							'validate_callback' => function ( $param ) {
								return is_numeric( $param );
							},
							'sanitize_callback' => 'absint',
						),
					),
				),
			)
		);

		// POST /splms/v1/quiz-attempts/{id}/feedback - Update feedback.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>\d+)/feedback',
			array(
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_feedback' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'                => array(
						'id'       => array(
							'validate_callback' => function ( $param ) {
								return is_numeric( $param );
							},
							'sanitize_callback' => 'absint',
						),
						'feedback' => array(
							'description'       => __( 'Instructor feedback', 'skillpulse-lms' ),
							'type'              => 'string',
							'sanitize_callback' => 'wp_kses_post',
						),
					),
				),
			)
		);

		// POST /splms/v1/quiz-attempts/{id}/grade - Grade essay questions and update attempt score.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>\d+)/grade',
			array(
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'grade_attempt' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'                => array(
						'id'              => array(
							'validate_callback' => function ( $param ) {
								return is_numeric( $param );
							},
							'sanitize_callback' => 'absint',
						),
						'question_scores' => array(
							'description' => __( 'Question scores array (question_id => points)', 'skillpulse-lms' ),
							'type'        => 'object',
							'required'    => true,
						),
					),
				),
			)
		);
	}

	/**
	 * Check if a given request has access to get items.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @since 1.0.0
	 *
	 * @return WP_Error|bool True if request has access, WP_Error object otherwise.
	 */
	public function get_items_permissions_check( $request ) {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Get collection parameters for the controller.
	 *
	 * @since 1.0.0
	 *
	 * @return array Collection parameters.
	 */
	public function get_collection_params() {
		return array(
			'page'           => array(
				'description'       => __( 'Current page of the collection.', 'skillpulse-lms' ),
				'type'              => 'integer',
				'default'           => 1,
				'sanitize_callback' => 'absint',
				'validate_callback' => 'rest_validate_request_arg',
			),
			'per_page'       => array(
				'description'       => __( 'Maximum number of items to be returned in result set.', 'skillpulse-lms' ),
				'type'              => 'integer',
				'default'           => 20,
				'minimum'           => 1,
				'maximum'           => 100,
				'sanitize_callback' => 'absint',
				'validate_callback' => 'rest_validate_request_arg',
			),
			'search'         => array(
				'description'       => __( 'Search by user, course, or quiz.', 'skillpulse-lms' ),
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'user_id'        => array(
				'description'       => __( 'Filter by user ID.', 'skillpulse-lms' ),
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
			),
			'course_id'      => array(
				'description'       => __( 'Filter by course ID.', 'skillpulse-lms' ),
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
			),
			'quiz_id'        => array(
				'description'       => __( 'Filter by quiz ID.', 'skillpulse-lms' ),
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
			),
			'passed'         => array(
				'description'       => __( 'Filter by passed status (1 or 0).', 'skillpulse-lms' ),
				'type'              => 'integer',
				'enum'              => array( 0, 1 ),
				'sanitize_callback' => 'absint',
			),
			'pending_review' => array(
				'description'       => __( 'Filter by pending review status (1 or 0). Note: Applied after fetching results.', 'skillpulse-lms' ),
				'type'              => 'integer',
				'enum'              => array( 0, 1 ),
				'sanitize_callback' => 'absint',
			),
			'status'         => array(
				'description'       => __( 'Filter by quiz attempt status. Valid values: draft, in_progress, submitted, pending_review, graded, expired, requires_resubmission.', 'skillpulse-lms' ),
				'type'              => 'string',
				'enum'              => array( 'draft', 'in_progress', 'submitted', 'pending_review', 'graded', 'expired', 'requires_resubmission' ),
				'sanitize_callback' => 'sanitize_text_field',
			),
			'order_by'       => array(
				'description' => __( 'Order by field.', 'skillpulse-lms' ),
				'type'        => 'string',
				'default'     => 'attempt_time',
				'enum'        => array( 'id', 'user_id', 'quiz_id', 'course_id', 'score', 'passed', 'attempt_time', 'status' ),
			),
			'order'          => array(
				'description'       => __( 'Order direction.', 'skillpulse-lms' ),
				'type'              => 'string',
				'default'           => 'DESC',
				'sanitize_callback' => 'sanitize_text_field',
			),
		);
	}

	/**
	 * Get quiz attempts with filters.
	 *
	 * Retrieves a collection of quiz attempts with advanced filtering, search,
	 * sorting, and pagination support. Includes user, course, and quiz information.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @since          1.0.0
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 * @api            {get} /splms/v1/quiz-attempts List Quiz Attempts
	 * @apiName        GetQuizAttempts
	 * @apiGroup       Quiz Attempts
	 * @apiVersion     1.0.0
	 *
	 * @apiDescription Retrieve a collection of quiz attempts with filtering and pagination.
	 * Requires manage_options capability. Supports filtering by user, course, quiz, pass status,
	 * and verification status. Includes detection of pending manual review for essay questions.
	 *
	 * @apiParam {Number} [page=1] Current page of the collection.
	 * @apiParam {Number} [per_page=20] Maximum number of items to be returned.
	 * @apiParam {String} [search] Search by user name, email, course title, or quiz title.
	 * @apiParam {Number} [user_id] Filter by user ID.
	 * @apiParam {Number} [course_id] Filter by course ID.
	 * @apiParam {Number} [quiz_id] Filter by quiz ID.
	 * @apiParam {Number} [passed] Filter by passed status (0 or 1).
	 * @apiParam {Number} [pending_review] Filter by pending review status (0 or 1).
	 * @apiParam {String} [order_by=attempt_time] Sort by field.
	 * @apiParam {String} [order=DESC] Sort order (ASC or DESC).
	 *
	 * @apiError (Error 403) rest_forbidden Insufficient permissions to access quiz attempts.
	 */
	public function get_attempts( $request ) {
		global $wpdb;

		$per_page = $request->get_param( 'per_page' ) ? $request->get_param( 'per_page' ) : 20;
		$page     = $request->get_param( 'page' ) ? $request->get_param( 'page' ) : 1;
		$offset   = ( $page - 1 ) * $per_page;

		// Build WHERE clause.
		$where        = array( '1=1' );
		$where_values = array();

		// Search.
		$search = $request->get_param( 'search' );
		if ( $search ) {
			$search_like = '%' . $wpdb->esc_like( $search ) . '%';

			// Get matching user IDs.
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table query.
			$user_ids = $wpdb->get_col(
				$wpdb->prepare(
					"SELECT ID FROM {$wpdb->users} WHERE display_name LIKE %s OR user_email LIKE %s",
					$search_like,
					$search_like
				)
			);

			// Get matching quiz IDs.
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table query.
			$quiz_ids = $wpdb->get_col(
				$wpdb->prepare(
					"SELECT ID FROM {$wpdb->posts} WHERE post_type = %s AND post_title LIKE %s",
					SPLMS_POST_TYPES['quiz'],
					$search_like
				)
			);

			// Get matching course IDs.
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table query.
			$course_ids = $wpdb->get_col(
				$wpdb->prepare(
					"SELECT ID FROM {$wpdb->posts} WHERE post_type = %s AND post_title LIKE %s",
					SPLMS_POST_TYPES['course'],
					$search_like
				)
			);

			// Build search conditions.
			$search_conditions = array();
			if ( ! empty( $user_ids ) ) {
				$user_ids_placeholders = implode( ',', array_fill( 0, count( $user_ids ), '%d' ) );
				$search_conditions[]   = "user_id IN ($user_ids_placeholders)";
				$where_values          = array_merge( $where_values, $user_ids );
			}
			if ( ! empty( $quiz_ids ) ) {
				$quiz_ids_placeholders = implode( ',', array_fill( 0, count( $quiz_ids ), '%d' ) );
				$search_conditions[]   = "quiz_id IN ($quiz_ids_placeholders)";
				$where_values          = array_merge( $where_values, $quiz_ids );
			}
			if ( ! empty( $course_ids ) ) {
				$course_ids_placeholders = implode( ',', array_fill( 0, count( $course_ids ), '%d' ) );
				$search_conditions[]     = "course_id IN ($course_ids_placeholders)";
				$where_values            = array_merge( $where_values, $course_ids );
			}

			if ( ! empty( $search_conditions ) ) {
				$where[] = '(' . implode( ' OR ', $search_conditions ) . ')';
			} else {
				// No matches found, return empty result.
				$where[] = '1=0';
			}
		}

		// Filters.
		if ( $request->get_param( 'user_id' ) ) {
			$where[]        = 'user_id = %d';
			$where_values[] = intval( $request->get_param( 'user_id' ) );
		}

		if ( $request->get_param( 'course_id' ) ) {
			$where[]        = 'course_id = %d';
			$where_values[] = intval( $request->get_param( 'course_id' ) );
		}

		if ( $request->get_param( 'quiz_id' ) ) {
			$where[]        = 'quiz_id = %d';
			$where_values[] = intval( $request->get_param( 'quiz_id' ) );
		}

		$passed = $request->get_param( 'passed' );
		if ( null !== $passed ) {
			$where[]        = 'passed = %d';
			$where_values[] = intval( $passed );
		}

		// Status filter using new status field.
		$status = $request->get_param( 'status' );
		if ( ! empty( $status ) ) {
			// Support for new status system.
			$status_manager = SkillPulse_LMS_Quiz_Status_Manager::get_instance();
			if ( $status_manager->is_valid_status( $status ) ) {
				$where[]        = 'status = %s';
				$where_values[] = sanitize_text_field( $status );
			}
		}

		$where_clause = implode( ' AND ', $where );

		// Order by.
		$orderby = $request->get_param( 'order_by' ) ? $request->get_param( 'order_by' ) : 'attempt_time';
		$order   = strtoupper( $request->get_param( 'order' ) ? $request->get_param( 'order' ) : 'DESC' );
		// Ensure order is either ASC or DESC.
		$order = ( 'ASC' === $order ) ? 'ASC' : 'DESC';

		$allowed_orderby = array( 'id', 'user_id', 'quiz_id', 'course_id', 'score', 'passed', 'attempt_time', 'status' );
		if ( ! in_array( $orderby, $allowed_orderby, true ) ) {
			$orderby = 'attempt_time';
		}

		// Get total count.
		$table_name = $wpdb->prefix . self::TABLE_NAME;
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table name is safe, validated constant.
		$count_query = "SELECT COUNT(*) FROM {$table_name} WHERE {$where_clause}";
		if ( ! empty( $where_values ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- $count_query is built with proper placeholders and prepared.
			$count_query = $wpdb->prepare( $count_query, ...$where_values );
		}
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- $count_query is prepared above.
		$total_items = $wpdb->get_var( $count_query );

		// Get items.
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table name and orderby are safe, validated constants.
		$query        = "SELECT * FROM {$table_name} WHERE {$where_clause} ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d";
		$query_values = array_merge( $where_values, array( $per_page, $offset ) );

		if ( empty( $query_values ) ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Query has no placeholders when values is empty.
			$attempts = $wpdb->get_results( $query );
		} else {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- $query is built with proper placeholders and prepared.
			$query = $wpdb->prepare( $query, ...$query_values );
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- $query is prepared above.
			$attempts = $wpdb->get_results( $query );
		}

		// Format attempts with related data.
		$formatted_attempts = array();
		foreach ( $attempts as $attempt ) {
			$formatted_attempts[] = $this->prepare_response_for_collection(
				$this->prepare_item_for_response( $attempt, $request )
			);
		}

		// Apply pending_review filter (post-filter since it's a calculated field).
		$pending_review = $request->get_param( 'pending_review' );
		if ( null !== $pending_review ) {
			$pending_review_bool = 1 === intval( $pending_review );
			$formatted_attempts  = array_values(
				array_filter(
					$formatted_attempts,
					function ( $attempt ) use ( $pending_review_bool ) {
						$is_pending = isset( $attempt['pending_review'] ) && $attempt['pending_review'];

						return $is_pending === $pending_review_bool;
					}
				)
			);
			// Update total items to reflect filtered count.
			$total_items = count( $formatted_attempts );
		}

		$total_pages = ceil( $total_items / $per_page );

		// Maintain backward compatibility with frontend that expects success wrapper.
		$response = rest_ensure_response(
			array(
				'success'      => true,
				'attempts'     => $formatted_attempts,
				'total_items'  => intval( $total_items ),
				'total_pages'  => intval( $total_pages ),
				'current_page' => intval( $page ),
				'per_page'     => intval( $per_page ),
			)
		);
		$response->header( 'X-WP-Total', $total_items );
		$response->header( 'X-WP-TotalPages', $total_pages );

		/**
		 * Fires after a list of quiz attempts response is prepared via the REST API.
		 *
		 * @param WP_REST_Response $response The response data.
		 * @param WP_REST_Request  $request  The request sent to the API.
		 */
		do_action( 'splms_rest_quiz_attempts_items_response', $response, $request );

		return $response;
	}

	/**
	 * Get single quiz attempt by ID.
	 *
	 * Retrieves a single quiz attempt with all details including user answers,
	 * scores, and related user/quiz/course information.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @since          1.0.0
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 * @api            {get} /splms/v1/quiz-attempts/:id Get Quiz Attempt
	 * @apiName        GetQuizAttempt
	 * @apiGroup       Quiz Attempts
	 * @apiVersion     1.0.0
	 *
	 * @apiDescription Retrieve a single quiz attempt by ID with all details.
	 * Requires manage_options capability. Includes user answers, scores, and pending review status.
	 *
	 * @apiParam {Number} id Attempt unique identifier.
	 *
	 * @apiError (Error 404) attempt_not_found Quiz attempt not found.
	 * @apiError (Error 403) rest_forbidden Insufficient permissions to access quiz attempt.
	 */
	public function get_attempt( $request ) {
		$attempt_id     = $request->get_param( 'id' );
		$attempts_query = SkillPulse_LMS_Quiz_Attempts_Query::get_instance();
		$attempt        = $attempts_query->get_attempt_by_id( $attempt_id );

		if ( ! $attempt ) {
			return new WP_Error( 'attempt_not_found', __( 'Quiz attempt not found.', 'skillpulse-lms' ), array( 'status' => 404 ) );
		}

		$response = $this->prepare_item_for_response( $attempt, $request );

		// Maintain backward compatibility with frontend that expects success wrapper.
		$data = $response->get_data();
		$response->set_data(
			array(
				'success' => true,
				'attempt' => $data,
			)
		);

		/**
		 * Fires after a quiz attempt response is prepared via the REST API.
		 *
		 * @param WP_REST_Response $response The response data.
		 * @param object           $attempt  Quiz attempt object.
		 * @param WP_REST_Request  $request  The request sent to the API.
		 */
		do_action( 'splms_rest_quiz_attempt_item_response', $response, $attempt, $request );

		return $response;
	}

	/**
	 * Prepare a single quiz attempt output for response.
	 *
	 * @param object          $attempt Quiz attempt object.
	 * @param WP_REST_Request $request Request object.
	 *
	 * @since 1.0.0
	 *
	 * @return WP_REST_Response Response object.
	 */
	public function prepare_item_for_response( $attempt, $request ) {
		// Get related data.
		$user   = get_userdata( $attempt->user_id );
		$course = get_post( $attempt->course_id );
		$quiz   = get_post( $attempt->quiz_id );

		// Decode answers.
		$answers = json_decode( $attempt->answers, true );
		if ( JSON_ERROR_NONE !== json_last_error() || ! is_array( $answers ) ) {
			$answers = array();
		}

		// Use status field directly instead of calculating pending review.
		$status         = isset( $attempt->status ) ? $attempt->status : 'draft';
		$pending_review = 'pending_review' === $status;

		// Check if quiz has manual review questions for has_manual_review flag.
		$has_manual_review = false;
		if ( $quiz ) {
			$quizzes_class = SkillPulse_LMS_Quizzes::get_instance();
			$questions     = $quizzes_class->get_quiz_questions( $attempt->quiz_id );
			foreach ( $questions as $question ) {
				if ( in_array( $question['type'], array( 'essay', 'long_answer', 'file_upload' ), true ) ) {
					$has_manual_review = true;
					break;
				}
			}
		}

		$fields = $this->get_fields_for_response( $request );

		// Calculate percentage from score and max_score.
		$max_score  = floatval( $attempt->max_score );
		$score      = floatval( $attempt->score );
		$percentage = $max_score > 0 ? round( ( $score / $max_score ) * 100, 2 ) : 0;

		// Determine passed status based on status and percentage.
		$passed = false;
		if ( 'graded' === $status ) {
			// For graded attempts, determine pass/fail based on percentage.
			$passing_grade = isset( $attempt->passing_grade ) ? floatval( $attempt->passing_grade ) : 70;
			$passed        = $percentage >= $passing_grade;
		}

		// Base fields for every attempt.
		$data = array(
			'id'                => intval( $attempt->id ),
			'user_id'           => intval( $attempt->user_id ),
			'course_id'         => intval( $attempt->course_id ),
			'quiz_id'           => intval( $attempt->quiz_id ),
			'score'             => $score,
			'max_score'         => $max_score,
			'percentage'        => $percentage,
			'passed'            => $passed,
			'attempt_time'      => $attempt->attempt_time,
			'time_taken'        => intval( $attempt->time_taken ),
			'status'            => $status,
			'pending_review'    => $pending_review,
			'has_manual_review' => $has_manual_review,
		);

		// User information - always include for consistency with enrollments.
		$data['user'] = $user ? array(
			'ID'           => $user->ID,
			'display_name' => $user->display_name,
			'user_email'   => $user->user_email,
			'user_login'   => $user->user_login,
		) : null;

		// Also include user_name, user_email, and user_avatar at top level for consistency with enrollments format.
		$data['user_name']   = $user ? $user->display_name : '';
		$data['user_email']  = $user ? $user->user_email : '';
		$data['user_avatar'] = $user ? get_avatar_url( $user->ID, array( 'size' => 60 ) ) : '';

		// Course information.
		if ( rest_is_field_included( 'course', $fields ) ) {
			$data['course'] = $course ? array(
				'ID'         => $course->ID,
				'post_title' => $course->post_title,
			) : null;
		}

		// Quiz information.
		if ( rest_is_field_included( 'quiz', $fields ) ) {
			$data['quiz'] = $quiz ? array(
				'ID'         => $quiz->ID,
				'post_title' => $quiz->post_title,
			) : null;
		}

		// Answers (only for single request or if explicitly requested).
		$is_single_request = ! empty( $request->get_param( 'id' ) ) && absint( $request->get_param( 'id' ) ) === $attempt->id;
		if ( $is_single_request || rest_is_field_included( 'answers', $fields ) ) {
			$data['answers'] = $answers;
		}

		// Feedback (only for single request).
		if ( $is_single_request || rest_is_field_included( 'feedback', $fields ) ) {
			$data['feedback'] = isset( $attempt->feedback ) ? $attempt->feedback : '';
		}

		$context = ! empty( $request['context'] ) ? $request['context'] : 'view';
		$data    = $this->add_additional_fields_to_object( $data, $request );
		$data    = $this->filter_response_by_context( $data, $context );

		// Wrap the data in a response object.
		$response = rest_ensure_response( $data );

		// Add links.
		$response->add_links( $this->prepare_links( $attempt ) );

		/**
		 * Filters quiz attempt response.
		 *
		 * @param WP_REST_Response $response Rest response.
		 * @param object           $attempt  Quiz attempt object.
		 * @param WP_REST_Request  $request  Request object.
		 */
		return apply_filters( 'splms_rest_prepare_quiz_attempt', $response, $attempt, $request );
	}

	/**
	 * Check if quiz attempt has pending review questions.
	 *
	 * @param object        $attempt Quiz attempt object.
	 * @param WP_Post|false $quiz    Quiz post object or false.
	 * @param array         $answers Decoded answers array.
	 *
	 * @since 1.0.0
	 *
	 * @return array Array with 'pending_review' and 'has_manual_review' keys.
	 */
	private function check_pending_review( $attempt, $quiz, $answers ) {
		$pending_review    = false;
		$has_manual_review = false;

		if ( ! $quiz ) {
			return array(
				'pending_review'    => false,
				'has_manual_review' => false,
			);
		}

		$quizzes_class                 = SkillPulse_LMS_Quizzes::get_instance();
		$questions                     = $quizzes_class->get_quiz_questions( $attempt->quiz_id );
		$essay_question_ids_normalized = array();

		foreach ( $questions as $question ) {
			if ( in_array( $question['type'], array( 'essay', 'long_answer', 'file_upload' ), true ) ) {
				$has_manual_review                     = true;
				$qid                                   = (string) $question['id'];
				$essay_question_ids_normalized[ $qid ] = true;
			}
		}

		if ( $has_manual_review && ! empty( $answers ) ) {
			// Get graded scores to check if essay questions have been graded.
			$graded_scores = isset( $answers['_graded_scores'] ) && is_array( $answers['_graded_scores'] )
				? $answers['_graded_scores']
				: array();

			// Check each essay question: if it has an answer but is not in graded_scores, it's pending review.
			foreach ( $answers as $answer_question_id => $answer_value ) {
				// Skip _graded_scores key.
				if ( '_graded_scores' === $answer_question_id ) {
					continue;
				}

				$normalized_qid         = (string) $answer_question_id;
				$answer_question_id_int = intval( $answer_question_id );

				// Check if this is an essay question.
				if ( isset( $essay_question_ids_normalized[ $normalized_qid ] ) ) {
					// Check if answer is not empty.
					$is_empty = false;
					if ( is_array( $answer_value ) ) {
						$is_empty = empty( $answer_value ) || count( $answer_value ) === 0;
					} else {
						$is_empty = empty( $answer_value ) ||
									'' === trim( (string) $answer_value ) ||
									'[]' === $answer_value ||
									'{}' === $answer_value;
					}

					if ( ! $is_empty ) {
						// Check if this question has been graded.
						$is_graded = isset( $graded_scores[ $normalized_qid ] ) || isset( $graded_scores[ $answer_question_id_int ] );

						// If essay question has an answer but hasn't been graded, it's pending review.
						if ( ! $is_graded ) {
							$pending_review = true;
							break;
						}
					}
				}
			}
		}

		return array(
			'pending_review'    => $pending_review,
			'has_manual_review' => $has_manual_review,
		);
	}

	/**
	 * Prepare links for the request.
	 *
	 * @param object $attempt Quiz attempt object.
	 *
	 * @since 1.0.0
	 *
	 * @return array Links for the given quiz attempt.
	 */
	protected function prepare_links( $attempt ) {
		$links = array(
			'self'       => array(
				array(
					'href' => rest_url( $this->namespace . '/' . $this->rest_base . '/' . $attempt->id ),
				),
			),
			'collection' => array(
				array(
					'href' => rest_url( $this->namespace . '/' . $this->rest_base ),
				),
			),
		);

		if ( $attempt->quiz_id ) {
			$links['quiz'] = array(
				array(
					'href'       => rest_url( splms_rest_namespace() . '/' . splms_rest_version() . '/quizzes/' . $attempt->quiz_id ),
					'embeddable' => true,
				),
			);
		}

		if ( $attempt->course_id ) {
			$links['course'] = array(
				array(
					'href'       => rest_url( splms_rest_namespace() . '/' . splms_rest_version() . '/courses/' . $attempt->course_id ),
					'embeddable' => true,
				),
			);
		}

		if ( $attempt->user_id ) {
			$links['user'] = array(
				array(
					'href'       => rest_url( 'wp/v2/users/' . $attempt->user_id ),
					'embeddable' => true,
				),
			);
		}

		return $links;
	}

	/**
	 * Update a quiz attempt status to graded.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @since 1.0.0
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function verify_attempt( $request ) {
		$attempt_id = $request->get_param( 'id' );

		global $wpdb;
		$table_name = $wpdb->prefix . self::TABLE_NAME;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Custom table write operation.
		$result = $wpdb->update(
			$table_name,
			array(
				'status'      => 'graded',
				'graded_time' => current_time( 'mysql' ),
			),
			array( 'id' => $attempt_id ),
			array( '%s', '%s' ),
			array( '%d' )
		);

		if ( false === $result ) {
			return new WP_Error( 'grade_failed', __( 'Failed to update quiz attempt status.', 'skillpulse-lms' ), array( 'status' => 500 ) );
		}

		return rest_ensure_response(
			array(
				'success' => true,
				'message' => __( 'Quiz attempt status updated successfully.', 'skillpulse-lms' ),
			)
		);
	}

	/**
	 * Delete a quiz attempt.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @since 1.0.0
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function delete_attempt( $request ) {
		$attempt_id     = $request->get_param( 'id' );
		$attempts_query = SkillPulse_LMS_Quiz_Attempts_Query::get_instance();

		$result = $attempts_query->delete_attempt( $attempt_id );

		if ( ! $result ) {
			return new WP_Error( 'delete_failed', __( 'Failed to delete quiz attempt.', 'skillpulse-lms' ), array( 'status' => 500 ) );
		}

		return rest_ensure_response(
			array(
				'success' => true,
				'message' => __( 'Quiz attempt deleted successfully.', 'skillpulse-lms' ),
			)
		);
	}

	/**
	 * Get quiz attempt questions with answer comparison.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @since 1.0.0
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_attempt_questions( $request ) {
		$attempt_id     = $request->get_param( 'id' );
		$attempts_query = SkillPulse_LMS_Quiz_Attempts_Query::get_instance();
		$attempt        = $attempts_query->get_attempt_by_id( $attempt_id );

		if ( ! $attempt ) {
			return new WP_Error( 'attempt_not_found', __( 'Quiz attempt not found.', 'skillpulse-lms' ), array( 'status' => 404 ) );
		}

		// Get quiz questions.
		$questions_query = SkillPulse_LMS_Quiz_Questions_Query::get_instance();
		$questions       = $questions_query->get_quiz_questions( $attempt->quiz_id );

		// Decode user answers.
		$user_answers = json_decode( $attempt->answers, true );
		if ( JSON_ERROR_NONE !== json_last_error() || ! is_array( $user_answers ) ) {
			$user_answers = array();
		}

		// Get graded scores if available.
		$graded_scores = isset( $user_answers['_graded_scores'] ) && is_array( $user_answers['_graded_scores'] )
			? $user_answers['_graded_scores']
			: array();

		// Format questions with answer comparison.
		$formatted_questions  = array();
		$correct_count        = 0;
		$incorrect_count      = 0;
		$pending_review_count = 0;

		foreach ( $questions as $index => $question ) {
			$question_id     = (string) $question->id;
			$question_id_int = intval( $question->id );

			// Try multiple key formats to find the answer.
			$given_answer = null;
			if ( isset( $user_answers[ $question_id ] ) ) {
				$given_answer = $user_answers[ $question_id ];
			} elseif ( isset( $user_answers[ $question_id_int ] ) ) {
				$given_answer = $user_answers[ $question_id_int ];
			}

			// Check if this question needs manual review.
			$needs_manual_review = in_array( $question->question_type, array( 'essay', 'long_answer', 'file_upload' ), true );
			$question_points     = floatval( $question->points );

			// Determine if question is correct and if it has been graded.
			$is_correct    = false;
			$earned_points = null;
			$is_graded     = false;

			if ( $needs_manual_review ) {
				// For manually graded questions, check if they have been graded.
				if ( isset( $graded_scores[ $question_id ] ) ) {
					$earned_points = floatval( $graded_scores[ $question_id ] );
					$is_graded     = true;
				} elseif ( isset( $graded_scores[ $question_id_int ] ) ) {
					$earned_points = floatval( $graded_scores[ $question_id_int ] );
					$is_graded     = true;
				}

				// Question is correct if it received full points.
				if ( $is_graded ) {
					$is_correct = ( $earned_points >= $question_points && $question_points > 0 );
				}
				// If not graded yet, $is_correct remains false and $earned_points is null.
			} else {
				// For auto-graded questions, use compare_answers.
				$correct_answer = $this->get_correct_answer_for_question( $question );
				$is_correct     = $this->compare_answers( $question, $given_answer, $correct_answer );
				$is_graded      = true; // Auto-graded questions are always "graded".
				$earned_points  = $is_correct ? $question_points : 0.0;
			}

			// Count questions properly: only count graded questions as correct/incorrect.
			if ( $needs_manual_review && ! $is_graded ) {
				// Pending review: not graded yet.
				++$pending_review_count;
			} elseif ( $is_correct ) {
				// Correct: graded and correct (auto-graded correct OR manually graded with full points).
				++$correct_count;
			} else {
				// Incorrect: graded and incorrect (auto-graded incorrect OR manually graded with less than full points).
				++$incorrect_count;
			}

			$formatted_questions[] = array(
				'id'                  => intval( $question->id ),
				'type'                => $question->question_type,
				'question_text'       => $question->question_text,
				'points'              => $question_points,
				'earned_points'       => $earned_points, // Points awarded (null if not graded yet).
				'given_answer'        => $this->format_answer_for_display( $question, $given_answer, false ),
				'correct_answer'      => $this->format_answer_for_display( $question, $this->get_correct_answer_for_question( $question ), true ),
				'is_correct'          => $is_correct,
				'is_graded'           => $is_graded, // Whether question has been graded.
				'explanation'         => $question->explanation,
				'options'             => $this->get_question_options( $question ),
				'needs_manual_review' => $needs_manual_review,
			);
		}

		return rest_ensure_response(
			array(
				'success'              => true,
				'questions'            => $formatted_questions,
				'correct_count'        => $correct_count,
				'incorrect_count'      => $incorrect_count,
				'pending_review_count' => $pending_review_count,
				'total_questions'      => count( $formatted_questions ),
			)
		);
	}

	/**
	 * Get correct answer for a question.
	 *
	 * @param object $question Question object from database.
	 *
	 * @since 1.0.0
	 *
	 * @return string|array|mixed Correct answer. Returns string for single answer questions, array for multiple/ordering/matching, empty string for essay/file_upload.
	 */
	private function get_correct_answer_for_question( $question ) {
		// Use correct_answer_json (normalized format).
		if ( isset( $question->correct_answer ) && is_array( $question->correct_answer ) && ! empty( $question->correct_answer ) ) {
			$correct_answer_data = $question->correct_answer;
			if ( isset( $correct_answer_data['answers'] ) ) {
				$answers = $correct_answer_data['answers'];

				switch ( $question->question_type ) {
					case 'multiple_choice':
					case 'true_false':
						return is_array( $answers ) && ! empty( $answers ) ? $answers[0] : '';

					case 'multiple_select':
						return is_array( $answers ) ? $answers : array();

					case 'short_answer':
					case 'fill_blank':
						return is_array( $answers ) && ! empty( $answers ) ? $answers[0] : '';

					case 'matching':
						return is_array( $answers ) ? $answers : array();

					case 'ordering':
						return is_array( $answers ) ? $answers : array();

					default:
						return '';
				}
			}
		}

		// Return empty default if correct_answer_json is not available.
		return '';
	}

	/**
	 * Compare user answer with correct answer.
	 *
	 * @param object $question       Question object.
	 * @param mixed  $given_answer   User's answer.
	 * @param mixed  $correct_answer Correct answer.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if correct, false otherwise.
	 */
	private function compare_answers( $question, $given_answer, $correct_answer ) {
		if ( null === $given_answer ) {
			return false;
		}

		switch ( $question->question_type ) {
			case 'multiple_choice':
			case 'true_false':
				return (string) $given_answer === (string) $correct_answer;

			case 'multiple_select':
				if ( ! is_array( $given_answer ) || ! is_array( $correct_answer ) ) {
					return false;
				}
				sort( $given_answer );
				sort( $correct_answer );

				return $given_answer === $correct_answer;

			case 'short_answer':
			case 'fill_blank':
				return strtolower( trim( (string) $given_answer ) ) === strtolower( trim( (string) $correct_answer ) );

			case 'matching':
				// Matching: compare associative arrays {left_id => right_id}.
				if ( ! is_array( $given_answer ) || ! is_array( $correct_answer ) ) {
					return false;
				}

				// Convert given_answer from sequential array to associative array if needed.
				// Frontend stores as: [rightText0, rightText1, ...] or [rightId0, rightId1, ...].
				// We need: {left_id => right_id}.
				$given_normalized = array();

				// Check if given_answer is sequential (numeric keys starting from 0).
				$is_sequential = false;
				if ( ! empty( $given_answer ) ) {
					$keys = array_keys( $given_answer );
					// Check if keys are sequential numeric indices (0, 1, 2, 3, etc.).
					$is_sequential = ( array_keys( $keys ) === $keys );
				}

				if ( $is_sequential ) {
					// Convert sequential array to associative array using question pairs.
					// Get pairs from question settings to map indices to left_ids.
					$settings = maybe_unserialize( $question->settings );
					if ( ! is_array( $settings ) ) {
						$settings = array();
					}
					$pairs = isset( $settings['pairs'] ) && is_array( $settings['pairs'] ) ? $settings['pairs'] : array();

					// Get options to map right values to right_ids.
					$options = is_array( $question->options ) ? $question->options : array();

					// Build mapping: left_index => left_id, right_text => right_id.
					$left_index_to_id = array();
					$right_text_to_id = array();
					$right_id_to_id   = array(); // Also map right_id to itself.

					foreach ( $pairs as $pair_index => $pair ) {
						$left_id  = isset( $pair['left_id'] ) ? trim( (string) $pair['left_id'] ) : '';
						$right_id = isset( $pair['right_id'] ) ? trim( (string) $pair['right_id'] ) : '';

						if ( ! empty( $left_id ) ) {
							$left_index_to_id[ $pair_index ] = $left_id;
						}

						if ( ! empty( $right_id ) ) {
							$right_id_to_id[ $right_id ] = $right_id;
						}
					}

					// Also build text-to-id mapping from options.
					foreach ( $options as $option ) {
						$option_id   = isset( $option['id'] ) ? trim( (string) $option['id'] ) : '';
						$option_data = isset( $option['option_data'] ) ? $option['option_data'] : array();
						$pair_side   = isset( $option_data['pair_side'] ) ? $option_data['pair_side'] : '';

						if ( 'right' === $pair_side ) {
							$right_text = isset( $option_data['right'] ) ? $option_data['right'] : ( isset( $option['text'] ) ? $option['text'] : '' );
							if ( ! empty( $right_text ) && ! empty( $option_id ) ) {
								$right_text_to_id[ trim( (string) $right_text ) ] = $option_id;
							}
						}
					}

					// Convert sequential array to associative array.
					foreach ( $given_answer as $index => $right_value ) {
						$left_id = isset( $left_index_to_id[ $index ] ) ? $left_index_to_id[ $index ] : null;

						if ( $left_id ) {
							// Try to find right_id from right_value.
							$right_id            = null;
							$right_value_trimmed = trim( (string) $right_value );

							// Check if it's already a right_id.
							if ( isset( $right_id_to_id[ $right_value_trimmed ] ) ) {
								$right_id = $right_id_to_id[ $right_value_trimmed ];
							} elseif ( isset( $right_text_to_id[ $right_value_trimmed ] ) ) {
								// Check if it's a right text.
								$right_id = $right_text_to_id[ $right_value_trimmed ];
							} else {
								// Try case-insensitive match.
								foreach ( $right_text_to_id as $text => $id ) {
									if ( strtolower( $text ) === strtolower( $right_value_trimmed ) ) {
										$right_id = $id;
										break;
									}
								}
							}

							if ( $right_id ) {
								$given_normalized[ $left_id ] = $right_id;
							}
						}
					}
				} else {
					// Already associative array, just normalize.
					foreach ( $given_answer as $left_id => $right_id ) {
						$given_normalized[ trim( (string) $left_id ) ] = trim( (string) $right_id );
					}
				}

				// Normalize correct answer.
				$correct_normalized = array();
				foreach ( $correct_answer as $left_id => $right_id ) {
					$correct_normalized[ trim( (string) $left_id ) ] = trim( (string) $right_id );
				}

				// Compare normalized arrays.
				return $given_normalized === $correct_normalized;

			case 'ordering':
				// Ordering: compare arrays of option IDs in order.
				if ( ! is_array( $given_answer ) || ! is_array( $correct_answer ) ) {
					return false;
				}
				// Normalize both arrays (trim and convert to strings).
				$given_normalized   = array_map(
					function ( $id ) {
						return trim( (string) $id );
					},
					$given_answer
				);
				$correct_normalized = array_map(
					function ( $id ) {
						return trim( (string) $id );
					},
					$correct_answer
				);

				// Compare arrays (order matters for ordering questions).
				return $given_normalized === $correct_normalized;

			default:
				return false;
		}
	}

	/**
	 * Format answer for display.
	 *
	 * @param object $question          Question object.
	 * @param mixed  $answer            Answer to format.
	 * @param bool   $is_correct_answer Whether this is the correct answer.
	 *
	 * @since 1.0.0
	 *
	 * @return string Formatted answer.
	 */
	private function format_answer_for_display( $question, $answer, $is_correct_answer = false ) {
		// Check if answer is truly empty (null, empty string, or empty array).
		if ( null === $answer || '' === $answer || ( is_array( $answer ) && empty( $answer ) ) ) {
			return __( 'No answer provided', 'skillpulse-lms' );
		}

		// Use decoded options from query class (already decoded from options_json).
		$options = is_array( $question->options ) ? $question->options : array();

		$settings = maybe_unserialize( $question->settings );
		if ( ! is_array( $settings ) ) {
			$settings = array();
		}

		switch ( $question->question_type ) {
			case 'multiple_choice':
			case 'true_false':
				// For correct answer, answer is option ID - convert to text.
				// For given answer, it might already be text or ID.
				$option_id = trim( (string) $answer );
				foreach ( $options as $option ) {
					$opt_id = isset( $option['id'] ) ? trim( (string) $option['id'] ) : '';
					if ( $opt_id === $option_id ) {
						return isset( $option['text'] ) ? $option['text'] : $option_id;
					}
				}

				// If not found by ID, return as-is (might be text already).
				return (string) $answer;

			case 'multiple_select':
				// For correct answer, answer is array of option IDs - convert to texts.
				// For given answer, it might be array of IDs or texts.
				if ( ! is_array( $answer ) ) {
					return (string) $answer;
				}
				$answer_texts = array();
				$answer_ids   = array_map(
					function ( $id ) {
						return trim( (string) $id );
					},
					$answer
				);

				foreach ( $options as $option ) {
					$opt_id = isset( $option['id'] ) ? trim( (string) $option['id'] ) : '';
					if ( in_array( $opt_id, $answer_ids, true ) ) {
						$text = isset( $option['text'] ) ? $option['text'] : $opt_id;
						if ( ! empty( $text ) ) {
							$answer_texts[] = $text;
						}
					}
				}

				return ! empty( $answer_texts ) ? implode( ', ', $answer_texts ) : implode( ', ', $answer );

			case 'short_answer':
			case 'fill_blank':
				// Given answer is the text entered by user.
				$answer_text = trim( (string) $answer );

				return ! empty( $answer_text ) ? $answer_text : __( 'No answer provided', 'skillpulse-lms' );

			case 'matching':
				// For matching, correct answer is stored as {left_id => right_id}.
				// We need to map IDs to text from options.
				if ( is_array( $answer ) ) {
					$pair_texts = array();

					// Build ID to text mapping from options.
					$id_to_text = array();
					foreach ( $options as $option ) {
						$option_id = isset( $option['id'] ) ? trim( (string) $option['id'] ) : '';
						if ( ! empty( $option_id ) ) {
							// Get text from option_data or text field.
							$option_data = isset( $option['option_data'] ) ? $option['option_data'] : array();
							$pair_side   = isset( $option_data['pair_side'] ) ? $option_data['pair_side'] : '';

							if ( 'left' === $pair_side && isset( $option_data['left'] ) ) {
								$id_to_text[ $option_id ] = array(
									'side' => 'left',
									'text' => $option_data['left'],
								);
							} elseif ( 'right' === $pair_side && isset( $option_data['right'] ) ) {
								$id_to_text[ $option_id ] = array(
									'side' => 'right',
									'text' => $option_data['right'],
								);
							}
						}
					}

					// Format answer as associative array {left_id => right_id}.
					foreach ( $answer as $left_id => $right_id ) {
						$left_id  = trim( (string) $left_id );
						$right_id = trim( (string) $right_id );

						$left_text  = $left_id;
						$right_text = $right_id;

						// Get left text.
						if ( isset( $id_to_text[ $left_id ] ) ) {
							$left_data = $id_to_text[ $left_id ];
							if ( 'left' === $left_data['side'] ) {
								$left_text = $left_data['text'];
							} elseif ( 'both' === $left_data['side'] ) {
								$left_text = $left_data['left'];
							} elseif ( isset( $left_data['text'] ) ) {
								$left_text = $left_data['text'];
							}
						}

						// Get right text.
						if ( isset( $id_to_text[ $right_id ] ) ) {
							$right_data = $id_to_text[ $right_id ];
							if ( 'right' === $right_data['side'] ) {
								$right_text = $right_data['text'];
							} elseif ( 'both' === $right_data['side'] ) {
								$right_text = $right_data['right'];
							} elseif ( isset( $right_data['text'] ) ) {
								$right_text = $right_data['text'];
							}
						}

						$pair_texts[] = $left_text . ' → ' . $right_text;
					}

					return ! empty( $pair_texts ) ? implode( '; ', $pair_texts ) : implode( ' → ', $answer );
				}

				return (string) $answer;

			case 'ordering':
				// For ordering, answer can be:
				// 1. Array of option IDs (from user answers) - need to convert to text
				// 2. Array of text values (from correct_answer_json) - already text, use directly.
				if ( is_array( $answer ) && ! empty( $answer ) ) {
					$order_texts = array();

					// Build a map of option ID to text for quick lookup.
					$id_to_text_map = array();
					foreach ( $options as $option ) {
						$opt_id = isset( $option['id'] ) ? trim( (string) $option['id'] ) : '';
						if ( ! empty( $opt_id ) ) {
							// Get text from option_data or text field.
							$text = '';
							if ( isset( $option['option_data']['text'] ) ) {
								$text = $option['option_data']['text'];
							} elseif ( isset( $option['text'] ) ) {
								$text = $option['text'];
							}
							if ( ! empty( $text ) ) {
								$id_to_text_map[ $opt_id ] = $text;
							}
						}
					}

					// Process each value in the answer array.
					foreach ( $answer as $value ) {
						$value_str = trim( (string) $value );

						// Check if this value is an option ID (exists in our map).
						if ( isset( $id_to_text_map[ $value_str ] ) ) {
							// It's an option ID, convert to text.
							$order_texts[] = $id_to_text_map[ $value_str ];
						} else {
							// It's already a text value (from correct_answer_json), use directly.
							$order_texts[] = $value_str;
						}
					}

					return ! empty( $order_texts ) ? implode( ' → ', $order_texts ) : __( 'No answer provided', 'skillpulse-lms' );
				}

				return __( 'No answer provided', 'skillpulse-lms' );

			case 'essay':
				// Essay answer is the text entered by user.
				$answer_text = trim( (string) $answer );

				return ! empty( $answer_text ) ? $answer_text : __( 'No answer provided', 'skillpulse-lms' );

			case 'file_upload':
				// File upload answer might be file name or path.
				if ( is_array( $answer ) ) {
					// If it's an array, might contain file info.
					if ( isset( $answer['name'] ) ) {
						return $answer['name'];
					} elseif ( isset( $answer['url'] ) ) {
						return $answer['url'];
					} elseif ( ! empty( $answer ) ) {
						return implode( ', ', array_filter( $answer ) );
					}
				}
				$answer_text = trim( (string) $answer );

				return ! empty( $answer_text ) ? $answer_text : __( 'No answer provided', 'skillpulse-lms' );

			default:
				// For unknown types, return as string or show "No answer provided".
				if ( is_array( $answer ) ) {
					return ! empty( $answer ) ? implode( ', ', array_filter( $answer ) ) : __( 'No answer provided', 'skillpulse-lms' );
				}
				$answer_text = trim( (string) $answer );

				return ! empty( $answer_text ) ? $answer_text : __( 'No answer provided', 'skillpulse-lms' );
		}
	}

	/**
	 * Get question options.
	 *
	 * @param object $question Question object.
	 *
	 * @since 1.0.0
	 *
	 * @return array Question options.
	 */
	private function get_question_options( $question ) {
		$options = maybe_unserialize( $question->options_json );
		if ( ! is_array( $options ) ) {
			return array();
		}

		// Get correct answers from correct_answer_json (source of truth).
		$correct_answer_texts = array();
		if ( isset( $question->correct_answer ) && is_array( $question->correct_answer ) ) {
			$correct_answer_data = $question->correct_answer;
			if ( isset( $correct_answer_data['answers'] ) && is_array( $correct_answer_data['answers'] ) ) {
				if ( 'multiple_select' === $question->question_type ) {
					$correct_answer_texts = $correct_answer_data['answers'];
				} else {
					$correct_answer_texts = ! empty( $correct_answer_data['answers'] )
						? array( $correct_answer_data['answers'][0] )
						: array();
				}
			}
		}

		$formatted_options = array();
		foreach ( $options as $option ) {
			$option_text = isset( $option['text'] ) ? trim( (string) $option['text'] ) : '';

			// Determine is_correct ONLY by checking if option text is in correct_answer_json.
			$is_correct = in_array( $option_text, $correct_answer_texts, true );

			$formatted_options[] = array(
				'id'         => isset( $option['id'] ) ? $option['id'] : '',
				'text'       => $option_text,
				'is_correct' => $is_correct,
			);
		}

		return $formatted_options;
	}

	/**
	 * Update feedback for a quiz attempt.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @since 1.0.0
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function update_feedback( $request ) {
		$attempt_id = $request->get_param( 'id' );
		$feedback   = $request->get_param( 'feedback' );

		global $wpdb;
		$table_name = $wpdb->prefix . self::TABLE_NAME;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Custom table write operation.
		$result = $wpdb->update(
			$table_name,
			array( 'feedback' => $feedback ),
			array( 'id' => $attempt_id ),
			array( '%s' ),
			array( '%d' )
		);

		if ( false === $result ) {
			return new WP_Error( 'update_failed', __( 'Failed to update feedback.', 'skillpulse-lms' ), array( 'status' => 500 ) );
		}

		return rest_ensure_response(
			array(
				'success'  => true,
				'message'  => __( 'Feedback updated successfully.', 'skillpulse-lms' ),
				'feedback' => $feedback,
			)
		);
	}

	/**
	 * Grade essay questions and update attempt score.
	 *
	 * Manually grades essay, long answer, and file upload questions and recalculates
	 * the total score and pass/fail status for the attempt.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @since          1.0.0
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 * @api            {put} /splms/v1/quiz-attempts/:id/grade Grade Quiz Attempt
	 * @apiName        GradeQuizAttempt
	 * @apiGroup       Quiz Attempts
	 * @apiVersion     1.0.0
	 *
	 * @apiDescription Manually grade essay questions and update attempt score.
	 * Requires manage_options capability. Recalculates total score and pass/fail status
	 * based on provided question scores.
	 *
	 * @apiParam {Number} id Attempt unique identifier.
	 * @apiParam {Object} question_scores Object mapping question IDs to awarded points.
	 *
	 * @apiError (Error 404) attempt_not_found Quiz attempt not found.
	 * @apiError (Error 400) invalid_scores Invalid question scores provided.
	 * @apiError (Error 403) rest_forbidden Insufficient permissions to grade quiz attempt.
	 * @apiError (Error 500) grade_failed Failed to update attempt score.
	 */
	/**
	 * Grade quiz attempt questions.
	 *
	 * Simplified grading: Accept question_id and points, update _graded_scores,
	 * then recalculate total score from scratch.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @since 1.0.0
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function grade_attempt( $request ) {
		$attempt_id = $request->get_param( 'id' );

		// Get question_scores and optional feedback from JSON body.
		$json_params     = $request->get_json_params();
		$question_scores = isset( $json_params['question_scores'] ) ? $json_params['question_scores'] : $request->get_param( 'question_scores' );
		$feedback        = isset( $json_params['feedback'] ) ? sanitize_textarea_field( $json_params['feedback'] ) : '';

		if ( ! is_array( $question_scores ) || empty( $question_scores ) ) {
			return new WP_Error( 'invalid_scores', __( 'Invalid question scores provided.', 'skillpulse-lms' ), array( 'status' => 400 ) );
		}

		// Use Quiz Service for unified grading logic.
		$quiz_service = SkillPulse_LMS_Quiz_Service::get_instance();
		$graded_by    = get_current_user_id();

		$result = $quiz_service->grade_attempt( $attempt_id, $question_scores, $graded_by, $feedback );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return rest_ensure_response( $result );
	}
}
