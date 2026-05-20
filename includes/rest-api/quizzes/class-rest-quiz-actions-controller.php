<?php
/**
 * Quiz Actions REST API Controller
 *
 * Handles REST API endpoints for quiz actions: start, resume, restart, and state management.
 *
 * @package SkillPulse_LMS
 * @since 1.0.0
 *
 * @api
 * Available endpoints:
 * POST   /splms/v1/quizzes/{id}/start    - Start a new quiz attempt
 * POST   /splms/v1/quizzes/{id}/resume  - Resume an in-progress quiz attempt
 * POST   /splms/v1/quizzes/{id}/restart - Restart quiz (clear state + start new)
 * GET    /splms/v1/quizzes/{id}/state   - Get saved quiz state
 * PUT    /splms/v1/quizzes/{id}/state   - Save quiz state (auto-save)
 * DELETE /splms/v1/quizzes/{id}/state   - Clear quiz state
 * GET    /splms/v1/quizzes/{id}/attempts  - Get quiz attempts
 * POST   /splms/v1/quizzes/{id}/submit    - Submit quiz attempt
 * GET    /splms/v1/quizzes/{id}/results   - Get quiz results
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Quiz Actions REST API Controller class.
 *
 * @since 1.0.0
 */
class SPLMS_REST_Quiz_Actions_Controller extends WP_REST_Controller {

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function __construct() {
		$this->namespace = splms_rest_namespace() . '/' . splms_rest_version();
		$this->rest_base = 'quizzes';
	}

	/**
	 * Register the quiz action routes.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register_routes() {
		// Start quiz.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)/start',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'start_quiz' ),
				'permission_callback' => array( $this, 'start_quiz_permissions_check' ),
				'args'                => array(
					'id'           => array(
						'description' => __( 'Quiz ID', 'skillpulse-lms' ),
						'type'        => 'integer',
						'required'    => true,
					),
					'course_id'    => array(
						'description' => __( 'Course ID', 'skillpulse-lms' ),
						'type'        => 'integer',
						'required'    => false,
					),
					'preview_mode' => array(
						'description' => __( 'Preview mode (for non-logged-in users)', 'skillpulse-lms' ),
						'type'        => 'boolean',
						'required'    => false,
						'default'     => false,
					),
				),
			)
		);

		// Resume quiz.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)/resume',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'resume_quiz' ),
				'permission_callback' => array( $this, 'resume_quiz_permissions_check' ),
				'args'                => array(
					'id' => array(
						'description' => __( 'Quiz ID', 'skillpulse-lms' ),
						'type'        => 'integer',
						'required'    => true,
					),
				),
			)
		);

		// Restart quiz.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)/restart',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'restart_quiz' ),
				'permission_callback' => array( $this, 'restart_quiz_permissions_check' ),
				'args'                => array(
					'id'        => array(
						'description' => __( 'Quiz ID', 'skillpulse-lms' ),
						'type'        => 'integer',
						'required'    => true,
					),
					'course_id' => array(
						'description' => __( 'Course ID', 'skillpulse-lms' ),
						'type'        => 'integer',
						'required'    => false,
					),
				),
			)
		);

		// Get quiz state.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)/state',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_quiz_state' ),
				'permission_callback' => array( $this, 'get_quiz_state_permissions_check' ),
				'args'                => array(
					'id' => array(
						'description' => __( 'Quiz ID', 'skillpulse-lms' ),
						'type'        => 'integer',
						'required'    => true,
					),
				),
			)
		);

		// Save quiz state.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)/state',
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'save_quiz_state' ),
				'permission_callback' => array( $this, 'save_quiz_state_permissions_check' ),
				'args'                => array(
					'id'         => array(
						'description' => __( 'Quiz ID', 'skillpulse-lms' ),
						'type'        => 'integer',
						'required'    => true,
					),
					'attempt_id' => array(
						'description' => __( 'Attempt ID', 'skillpulse-lms' ),
						'type'        => 'integer',
						'required'    => true,
					),
					'answers'    => array(
						'description' => __( 'Quiz answers', 'skillpulse-lms' ),
						'type'        => 'object',
						'required'    => true,
					),
					'time_taken' => array(
						'description' => __( 'Time taken (in seconds)', 'skillpulse-lms' ),
						'type'        => 'integer',
						'required'    => false,
						'default'     => 0,
					),
				),
			)
		);

		// Clear quiz state.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)/state',
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( $this, 'clear_quiz_state' ),
				'permission_callback' => array( $this, 'clear_quiz_state_permissions_check' ),
				'args'                => array(
					'id' => array(
						'description' => __( 'Quiz ID', 'skillpulse-lms' ),
						'type'        => 'integer',
						'required'    => true,
					),
				),
			)
		);

		// Get quiz attempts.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)/attempts',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_quiz_attempts' ),
				'permission_callback' => array( $this, 'get_quiz_attempts_permissions_check' ),
				'args'                => array(
					'id'      => array(
						'description' => __( 'Quiz ID', 'skillpulse-lms' ),
						'type'        => 'integer',
						'required'    => true,
					),
					'user_id' => array(
						'description' => __( 'User ID', 'skillpulse-lms' ),
						'type'        => 'integer',
						'required'    => false,
					),
				),
			)
		);

		// Submit quiz attempt.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)/submit',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'submit_quiz_attempt' ),
				'permission_callback' => array( $this, 'submit_quiz_attempt_permissions_check' ),
				'args'                => array(
					'id'         => array(
						'description' => __( 'Quiz ID', 'skillpulse-lms' ),
						'type'        => 'integer',
						'required'    => true,
					),
					'answers'    => array(
						'description' => __( 'Quiz answers', 'skillpulse-lms' ),
						'type'        => 'array',
						'items'       => array( 'type' => 'object' ),
						'required'    => true,
					),
					'time_taken' => array(
						'description' => __( 'Time taken to complete quiz (in seconds)', 'skillpulse-lms' ),
						'type'        => 'integer',
						'required'    => false,
					),
				),
			)
		);

		// Get quiz results.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)/results',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_quiz_results' ),
				'permission_callback' => array( $this, 'get_quiz_results_permissions_check' ),
				'args'                => array(
					'id'      => array(
						'description' => __( 'Quiz ID', 'skillpulse-lms' ),
						'type'        => 'integer',
						'required'    => true,
					),
					'user_id' => array(
						'description' => __( 'User ID', 'skillpulse-lms' ),
						'type'        => 'integer',
						'required'    => false,
					),
				),
			)
		);
	}

	/**
	 * Start quiz.
	 *
	 * Starts a new quiz attempt and returns quiz data including questions and settings.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function start_quiz( $request ) {
		$quiz_id         = $request->get_param( 'id' );
		$user_id         = get_current_user_id();
		$course_id       = $request->get_param( 'course_id' ) ? absint( $request->get_param( 'course_id' ) ) : 0;
		$is_preview_mode = $request->get_param( 'preview_mode' ) ? true : false;

		if ( ! $quiz_id ) {
			return new WP_Error( 'invalid_quiz_id', __( 'Invalid quiz ID.', 'skillpulse-lms' ), array( 'status' => 400 ) );
		}

		// For preview mode, allow non-logged-in users.
		if ( ! $is_preview_mode && ! $user_id ) {
			return new WP_Error( 'user_not_logged_in', __( 'User not logged in.', 'skillpulse-lms' ), array( 'status' => 401 ) );
		}

		// Check quiz access control.
		$access_control = SPLMS_Access_Control::get_instance();

		// Check if quiz is available for preview mode.
		if ( $is_preview_mode ) {
			if ( ! $access_control->is_guest_preview_quiz( $quiz_id ) ) {
				return new WP_Error( 'quiz_not_available_for_preview', __( 'Quiz not available for preview.', 'skillpulse-lms' ), array( 'status' => 403 ) );
			}
		} elseif ( ! $access_control->user_can_access_quiz( $user_id, $quiz_id ) ) {
			// For non-preview mode, check full access control including course start date.
			return new WP_Error( 'access_denied', __( 'Access denied to quiz.', 'skillpulse-lms' ), array( 'status' => 403 ) );
		}

		// Get quiz data.
		$quizzes_class = SPLMS_Quizzes::get_instance();
		$questions     = $quizzes_class->get_quiz_questions( $quiz_id, false );
		$settings      = $quizzes_class->get_quiz_settings( $quiz_id );

		if ( empty( $questions ) ) {
			return new WP_Error( 'no_questions_found', __( 'No questions found for this quiz.', 'skillpulse-lms' ), array( 'status' => 404 ) );
		}

		// Check attempt limit before allowing quiz start (only for non-preview mode).
		if ( ! $is_preview_mode && $user_id ) {
			$max_attempts = $this->get_setting_value( $settings, 'max_attempts', 0 );

			if ( $max_attempts > 0 ) {
				$attempts_query = SPLMS_Quiz_Attempts_Query::get_instance();
				$attempts_used  = $attempts_query->count_completed_attempts( $user_id, $quiz_id );

				if ( $attempts_used >= $max_attempts ) {
					return new WP_Error(
						'no_attempts_remaining',
						__( 'No attempts remaining. You have used all available attempts for this quiz.', 'skillpulse-lms' ),
						array(
							'status'                => 403,
							'attempts_used'         => $attempts_used,
							'max_attempts'          => $max_attempts,
							'no_attempts_remaining' => true,
						)
					);
				}
			}
		}

		// Only create database attempt for non-preview mode.
		if ( ! $is_preview_mode ) {
			// Start a new attempt in the database (or get existing in-progress attempt).
			$attempts_query = SPLMS_Quiz_Attempts_Query::get_instance();
			$attempt_id     = $attempts_query->start_attempt( $user_id, $quiz_id, $course_id );

			// Ensure we have a valid attempt_id.
			if ( ! $attempt_id ) {
				// Check if there's an in-progress attempt.
				$in_progress_attempt = $attempts_query->get_in_progress_attempt( $user_id, $quiz_id );
				if ( $in_progress_attempt ) {
					$attempt_id = $in_progress_attempt->id;
				} else {
					return new WP_Error( 'failed_to_start_attempt', __( 'Failed to start quiz attempt.', 'skillpulse-lms' ), array( 'status' => 500 ) );
				}
			}
		} else {
			$attempt_id = null;
		}

		// Flatten settings for frontend.
		$flattened_settings = $quizzes_class->flatten_quiz_settings( $settings );

		// Build response with expected structure.
		$quiz_data = array(
			'attempt_id'      => $attempt_id,
			'quiz_id'         => $quiz_id,
			'course_id'       => $course_id,
			'questions'       => $questions,
			'quiz_settings'   => $flattened_settings,
			'total_questions' => count( $questions ),
			'title'           => get_the_title( $quiz_id ),
			'is_preview_mode' => $is_preview_mode,
		);

		return rest_ensure_response( $quiz_data );
	}

	/**
	 * Resume quiz.
	 *
	 * Resumes an in-progress quiz attempt.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function resume_quiz( $request ) {
		$quiz_id = $request->get_param( 'id' );
		$user_id = get_current_user_id();

		if ( ! $user_id ) {
			return new WP_Error( 'user_not_logged_in', __( 'User not logged in.', 'skillpulse-lms' ), array( 'status' => 401 ) );
		}

		if ( ! $quiz_id ) {
			return new WP_Error( 'invalid_quiz_id', __( 'Invalid quiz ID.', 'skillpulse-lms' ), array( 'status' => 400 ) );
		}

		// Get in-progress attempt from database.
		$attempts_query = SPLMS_Quiz_Attempts_Query::get_instance();
		$attempt        = $attempts_query->get_in_progress_attempt( $user_id, $quiz_id );

		if ( ! $attempt ) {
			return new WP_Error( 'no_in_progress_attempt', __( 'No in-progress attempt found.', 'skillpulse-lms' ), array( 'status' => 404 ) );
		}

		// Get quiz data.
		$quizzes_class = SPLMS_Quizzes::get_instance();
		$questions     = $quizzes_class->get_quiz_questions( $quiz_id, false );
		$settings      = $quizzes_class->get_quiz_settings( $quiz_id );

		if ( empty( $questions ) ) {
			return new WP_Error( 'no_questions_found', __( 'No questions found for this quiz.', 'skillpulse-lms' ), array( 'status' => 404 ) );
		}

		// Decode answers if they're stored as JSON string.
		$answers = $attempt->answers;
		if ( is_string( $answers ) ) {
			$decoded = json_decode( $answers, true );
			if ( json_last_error() === JSON_ERROR_NONE ) {
				$answers = $decoded;
			}
		}

		// Calculate current question from answers (find first unanswered question, or last answered).
		$current_question = 0;
		if ( ! empty( $answers ) && is_array( $answers ) ) {
			// Find the last answered question.
			$last_answered_index = -1;
			foreach ( $questions as $index => $question ) {
				$q_id = isset( $question['id'] ) ? $question['id'] : '';
				if ( isset( $answers[ $q_id ] ) && ! empty( $answers[ $q_id ] ) ) {
					$last_answered_index = $index;
				}
			}
			// Set current question to next unanswered, or last answered if all answered.
			$current_question = ( $last_answered_index >= 0 && $last_answered_index < count( $questions ) - 1 )
				? $last_answered_index + 1
				: max( 0, $last_answered_index );
		}

		// Flatten settings for frontend.
		$flattened_settings = $quizzes_class->flatten_quiz_settings( $settings );

		$quiz_data = array(
			'attempt_id'      => $attempt->id,
			'quiz_id'         => $quiz_id,
			'course_id'       => $attempt->course_id,
			'questions'       => $questions,
			'quiz_settings'   => $flattened_settings,
			'total_questions' => count( $questions ),
			'title'           => get_the_title( $quiz_id ),
			'saved_state'     => array(
				'attempt_id'      => $attempt->id,
				'answers'         => $answers,
				'time_taken'      => $attempt->time_taken,
				'start_time'      => $attempt->attempt_time,
				'currentQuestion' => $current_question,
			),
		);

		return rest_ensure_response( $quiz_data );
	}

	/**
	 * Restart quiz.
	 *
	 * Clears in-progress state and starts a new quiz attempt.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function restart_quiz( $request ) {
		$quiz_id   = $request->get_param( 'id' );
		$user_id   = get_current_user_id();
		$course_id = $request->get_param( 'course_id' ) ? absint( $request->get_param( 'course_id' ) ) : 0;

		if ( ! $user_id ) {
			return new WP_Error( 'user_not_logged_in', __( 'User not logged in.', 'skillpulse-lms' ), array( 'status' => 401 ) );
		}

		if ( ! $quiz_id ) {
			return new WP_Error( 'invalid_quiz_id', __( 'Invalid quiz ID.', 'skillpulse-lms' ), array( 'status' => 400 ) );
		}

		// Clear in-progress attempt from database (only truly in-progress ones).
		$attempts_query = SPLMS_Quiz_Attempts_Query::get_instance();
		$cleared        = $attempts_query->clear_in_progress_attempt( $user_id, $quiz_id );

		if ( false === $cleared ) {
			return new WP_Error( 'failed_to_clear_state', __( 'Failed to clear quiz state or no in-progress state found.', 'skillpulse-lms' ), array( 'status' => 500 ) );
		}

		// Start a new attempt.
		$attempt_id = $attempts_query->start_attempt( $user_id, $quiz_id, $course_id );

		if ( ! $attempt_id ) {
			return new WP_Error( 'failed_to_start_attempt', __( 'Failed to start quiz attempt.', 'skillpulse-lms' ), array( 'status' => 500 ) );
		}

		// Get quiz data.
		$quizzes_class = SPLMS_Quizzes::get_instance();
		$questions     = $quizzes_class->get_quiz_questions( $quiz_id, false );
		$settings      = $quizzes_class->get_quiz_settings( $quiz_id );

		if ( empty( $questions ) ) {
			return new WP_Error( 'no_questions_found', __( 'No questions found for this quiz.', 'skillpulse-lms' ), array( 'status' => 404 ) );
		}

		// Flatten settings for frontend.
		$flattened_settings = $quizzes_class->flatten_quiz_settings( $settings );

		$quiz_data = array(
			'attempt_id'      => $attempt_id,
			'quiz_id'         => $quiz_id,
			'course_id'       => $course_id,
			'questions'       => $questions,
			'quiz_settings'   => $flattened_settings,
			'total_questions' => count( $questions ),
			'title'           => get_the_title( $quiz_id ),
		);

		return rest_ensure_response( $quiz_data );
	}

	/**
	 * Get quiz state.
	 *
	 * Retrieves saved quiz state for resuming an in-progress attempt.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_quiz_state( $request ) {
		$quiz_id = $request->get_param( 'id' );
		$user_id = get_current_user_id();

		if ( ! $user_id ) {
			return new WP_Error( 'user_not_logged_in', __( 'User not logged in.', 'skillpulse-lms' ), array( 'status' => 401 ) );
		}

		if ( ! $quiz_id ) {
			return new WP_Error( 'invalid_quiz_id', __( 'Invalid quiz ID.', 'skillpulse-lms' ), array( 'status' => 400 ) );
		}

		// Get in-progress attempt from database.
		$attempts_query = SPLMS_Quiz_Attempts_Query::get_instance();
		$attempt        = $attempts_query->get_in_progress_attempt( $user_id, $quiz_id );

		if ( ! $attempt || empty( $attempt->answers ) ) {
			return new WP_Error( 'no_saved_state', __( 'No saved quiz state found.', 'skillpulse-lms' ), array( 'status' => 404 ) );
		}

		// Decode answers if they're stored as JSON string.
		$answers = $attempt->answers;
		if ( is_string( $answers ) ) {
			$decoded = json_decode( $answers, true );
			if ( json_last_error() === JSON_ERROR_NONE ) {
				$answers = $decoded;
			}
		}

		// Calculate current question from answers.
		$current_question = 0;
		if ( ! empty( $answers ) && is_array( $answers ) ) {
			// Get questions to determine current question index.
			$quizzes_class = SPLMS_Quizzes::get_instance();
			$questions     = $quizzes_class->get_quiz_questions( $quiz_id, false );
			if ( ! empty( $questions ) ) {
				// Find the last answered question.
				$last_answered_index = -1;
				foreach ( $questions as $index => $question ) {
					$q_id = isset( $question['id'] ) ? $question['id'] : '';
					if ( isset( $answers[ $q_id ] ) && ! empty( $answers[ $q_id ] ) ) {
						$last_answered_index = $index;
					}
				}
				// Set current question to next unanswered, or last answered if all answered.
				$current_question = ( $last_answered_index >= 0 && $last_answered_index < count( $questions ) - 1 )
					? $last_answered_index + 1
					: max( 0, $last_answered_index );
			}
		}

		$saved_state = array(
			'attempt_id'      => $attempt->id,
			'answers'         => $answers,
			'time_taken'      => $attempt->time_taken,
			'start_time'      => $attempt->attempt_time,
			'currentQuestion' => $current_question,
		);

		return rest_ensure_response( $saved_state );
	}

	/**
	 * Save quiz state.
	 *
	 * Saves quiz progress (answers and time taken) for an in-progress attempt.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function save_quiz_state( $request ) {
		$user_id    = get_current_user_id();
		$quiz_id    = $request->get_param( 'id' );
		$attempt_id = $request->get_param( 'attempt_id' );
		$answers    = $request->get_param( 'answers' );
		$time_taken = $request->get_param( 'time_taken' ) ? absint( $request->get_param( 'time_taken' ) ) : 0;

		if ( ! $user_id ) {
			return new WP_Error( 'user_not_logged_in', __( 'User not logged in.', 'skillpulse-lms' ), array( 'status' => 401 ) );
		}

		if ( ! $quiz_id || ! $attempt_id ) {
			return new WP_Error( 'invalid_parameters', __( 'Invalid quiz ID or attempt ID.', 'skillpulse-lms' ), array( 'status' => 400 ) );
		}

		if ( ! is_array( $answers ) ) {
			return new WP_Error( 'invalid_answers', __( 'Answers must be an array.', 'skillpulse-lms' ), array( 'status' => 400 ) );
		}

		// Update attempt progress in database.
		$attempts_query = SPLMS_Quiz_Attempts_Query::get_instance();
		$updated        = $attempts_query->update_attempt_progress( $attempt_id, $answers, $time_taken );

		if ( false === $updated ) {
			return new WP_Error( 'failed_to_save_progress', __( 'Failed to save quiz progress.', 'skillpulse-lms' ), array( 'status' => 500 ) );
		}

		return rest_ensure_response(
			array(
				'success' => true,
				'message' => __( 'Quiz progress saved successfully.', 'skillpulse-lms' ),
			)
		);
	}

	/**
	 * Clear quiz state.
	 *
	 * Clears in-progress quiz attempt state.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function clear_quiz_state( $request ) {
		$quiz_id = $request->get_param( 'id' );
		$user_id = get_current_user_id();

		if ( ! $user_id ) {
			return new WP_Error( 'user_not_logged_in', __( 'User not logged in.', 'skillpulse-lms' ), array( 'status' => 401 ) );
		}

		if ( ! $quiz_id ) {
			return new WP_Error( 'invalid_quiz_id', __( 'Invalid quiz ID.', 'skillpulse-lms' ), array( 'status' => 400 ) );
		}

		// Clear in-progress attempt from database (only truly in-progress ones).
		$attempts_query = SPLMS_Quiz_Attempts_Query::get_instance();
		$cleared        = $attempts_query->clear_in_progress_attempt( $user_id, $quiz_id );

		if ( false === $cleared ) {
			return new WP_Error( 'failed_to_clear_state', __( 'Failed to clear quiz state or no in-progress state found.', 'skillpulse-lms' ), array( 'status' => 404 ) );
		}

		return rest_ensure_response(
			array(
				'success' => true,
				'message' => __( 'Quiz state cleared successfully.', 'skillpulse-lms' ),
			)
		);
	}

	/**
	 * Check if a given request has access to start a quiz.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return bool|WP_Error True if request has access, WP_Error object otherwise.
	 */
	public function start_quiz_permissions_check( $request ) {
		// Preview mode allows non-logged-in users.
		$is_preview_mode = $request->get_param( 'preview_mode' );
		if ( $is_preview_mode ) {
			return true;
		}

		if ( ! is_user_logged_in() ) {
			return new WP_Error( 'rest_forbidden', __( 'Sorry, you are not allowed to start this quiz.', 'skillpulse-lms' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}

	/**
	 * Check if a given request has access to resume a quiz.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return bool|WP_Error True if request has access, WP_Error object otherwise.
	 */
	public function resume_quiz_permissions_check( $request ) {
		if ( ! is_user_logged_in() ) {
			return new WP_Error( 'rest_forbidden', __( 'Sorry, you are not allowed to resume this quiz.', 'skillpulse-lms' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}

	/**
	 * Check if a given request has access to restart a quiz.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return bool|WP_Error True if request has access, WP_Error object otherwise.
	 */
	public function restart_quiz_permissions_check( $request ) {
		if ( ! is_user_logged_in() ) {
			return new WP_Error( 'rest_forbidden', __( 'Sorry, you are not allowed to restart this quiz.', 'skillpulse-lms' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}

	/**
	 * Check if a given request has access to get quiz state.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return bool|WP_Error True if request has access, WP_Error object otherwise.
	 */
	public function get_quiz_state_permissions_check( $request ) {
		if ( ! is_user_logged_in() ) {
			return new WP_Error( 'rest_forbidden', __( 'Sorry, you are not allowed to get quiz state.', 'skillpulse-lms' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}

	/**
	 * Check if a given request has access to save quiz state.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return bool|WP_Error True if request has access, WP_Error object otherwise.
	 */
	public function save_quiz_state_permissions_check( $request ) {
		if ( ! is_user_logged_in() ) {
			return new WP_Error( 'rest_forbidden', __( 'Sorry, you are not allowed to save quiz state.', 'skillpulse-lms' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}

	/**
	 * Check if a given request has access to clear quiz state.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return bool|WP_Error True if request has access, WP_Error object otherwise.
	 */
	public function clear_quiz_state_permissions_check( $request ) {
		if ( ! is_user_logged_in() ) {
			return new WP_Error( 'rest_forbidden', __( 'Sorry, you are not allowed to clear quiz state.', 'skillpulse-lms' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}

	/**
	 * Get a specific quiz setting value.
	 *
	 * Handles both grouped and non-grouped field access.
	 *
	 * @param array  $settings Full settings array.
	 * @param string $key      Setting key (e.g., 'passing_grade' or 'quiz_grading_settings.passing_grade').
	 * @param mixed  $default_val  Default value if not found.
	 *
	 * @since 1.0.0
	 *
	 * @return mixed Setting value or default.
	 */
	private function get_setting_value( $settings, $key, $default_val = null ) {
		// Try direct access first.
		if ( isset( $settings[ $key ] ) ) {
			return $settings[ $key ];
		}

		// Try grouped access (group.field format).
		if ( false !== strpos( $key, '.' ) ) {
			list( $group, $field ) = explode( '.', $key, 2 );
			if ( isset( $settings[ $group ][ $field ] ) ) {
				return $settings[ $group ][ $field ];
			}
		}

		// Try finding in any group.
		foreach ( $settings as $group_key => $group_value ) {
			if ( is_array( $group_value ) && isset( $group_value[ $key ] ) ) {
				return $group_value[ $key ];
			}
		}

		return $default_val;
	}

	/**
	 * Get quiz attempts.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_quiz_attempts( $request ) {
		$quiz_id = $request->get_param( 'id' );
		$user_id = $request->get_param( 'user_id' ) ? absint( $request->get_param( 'user_id' ) ) : get_current_user_id();

		if ( ! $quiz_id ) {
			return new WP_Error( 'invalid_quiz_id', __( 'Invalid quiz ID.', 'skillpulse-lms' ), array( 'status' => 400 ) );
		}

		// Check quiz access control.
		$access_control = SPLMS_Access_Control::get_instance();

		// If user is logged in, check full access control.
		if ( $user_id ) {
			if ( ! $access_control->user_can_access_quiz( $user_id, $quiz_id ) ) {
				return new WP_Error( 'access_denied', __( 'Access denied to quiz.', 'skillpulse-lms' ), array( 'status' => 403 ) );
			}
		} elseif ( ! $access_control->is_guest_preview_quiz( $quiz_id ) ) {
			// For non-logged-in users, only allow if guest preview is enabled.
			return new WP_Error( 'rest_forbidden', __( 'Sorry, you must be logged in to view quiz attempts.', 'skillpulse-lms' ), array( 'status' => rest_authorization_required_code() ) );
		} else {
			// For guest preview, set user_id to 0 to get guest attempts.
			$user_id = 0;
		}

		// Use consistent formatting method for all quiz attempt responses.
		$quizzes_instance   = SPLMS_Quizzes::get_instance();
		$formatted_attempts = $quizzes_instance->get_formatted_quiz_attempts( $user_id, $quiz_id, true );

		return rest_ensure_response( $formatted_attempts );
	}

	/**
	 * Submit quiz attempt.
	 *
	 * Submits a quiz attempt with user answers, calculates the score,
	 * and saves the attempt to the database. Returns detailed results.
	 *
	 * @since 1.0.0
	 *
	 * @api {post} /splms/v1/quizzes/:id/submit Submit Quiz Attempt
	 * @apiName SubmitQuizAttempt
	 * @apiGroup Quizzes
	 * @apiVersion 1.0.0
	 *
	 * @apiDescription Submit a quiz attempt with answers. Automatically calculates
	 * score, determines pass/fail status, and saves the attempt. Requires user authentication.
	 *
	 * @apiParam {Number} id Quiz unique identifier.
	 * @apiParam {Array} answers Array of answer objects keyed by question ID.
	 * @apiParam {Number} [time_taken] Time taken to complete quiz in seconds.
	 *
	 * @apiError (Error 400) no_user No user specified.
	 * @apiError (Error 404) quiz_not_found Quiz not found.
	 * @apiError (Error 401) rest_forbidden User must be logged in.
	 * @apiError (Error 500) attempt_save_failed Failed to save quiz attempt.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function submit_quiz_attempt( $request ) {
		$quiz_id    = $request->get_param( 'id' );
		$answers    = $request->get_param( 'answers' );
		$time_taken = $request->get_param( 'time_taken' ) ? $request->get_param( 'time_taken' ) : 0;
		$user_id    = get_current_user_id();

		if ( ! $user_id ) {
			return new WP_Error( 'no_user', __( 'No user specified.', 'skillpulse-lms' ), array( 'status' => 400 ) );
		}

		$quiz = get_post( $quiz_id );
		if ( ! $quiz || SPLMS_POST_TYPES['quiz'] !== $quiz->post_type ) {
			return new WP_Error( 'quiz_not_found', __( 'Quiz not found.', 'skillpulse-lms' ), array( 'status' => 404 ) );
		}

		// Get quiz questions for scoring using the query class.
		$quizzes_class = SPLMS_Quizzes::get_instance();
		$questions     = $quizzes_class->get_quiz_questions( $quiz_id, false );
		$quiz_settings = $quizzes_class->get_quiz_settings( $quiz_id );

		// Calculate score.
		$score_result = $this->calculate_quiz_score( $questions, $answers );
		$score        = $score_result['score'];
		$total_points = $score_result['total_points'];

		// Check if passed.
		$passing_grade = isset( $quiz_settings['passing_grade'] ) ? $quiz_settings['passing_grade'] : 70;
		$percentage    = $total_points > 0 ? round( ( $score / $total_points ) * 100, 2 ) : 0;
		$passed        = $percentage >= $passing_grade;

		// Save attempt.
		global $wpdb;
		$table_name = esc_sql( $wpdb->prefix . 'splms_quiz_attempts' );

		$attempt_data = array(
			'quiz_id'        => $quiz_id,
			'user_id'        => $user_id,
			'course_id'      => (int) $quiz->post_parent,
			'score'          => $score,
			'max_score'      => $total_points,
			'passed'         => $passed ? 1 : 0,
			'status'         => 'graded', // Mark as graded since score is calculated automatically.
			'attempt_time'   => current_time( 'mysql' ),
			'submitted_time' => current_time( 'mysql' ),
			'graded_time'    => current_time( 'mysql' ),
			'time_taken'     => $time_taken,
			'answers'        => wp_json_encode( $answers ),
		);

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Custom table write operation.
		$result = $wpdb->insert( $table_name, $attempt_data );

		if ( false === $result ) {
			return new WP_Error( 'attempt_save_failed', __( 'Failed to save quiz attempt.', 'skillpulse-lms' ), array( 'status' => 500 ) );
		}

		// Get attempts count and remaining attempts after submission.
		$attempts_query     = SPLMS_Quiz_Attempts_Query::get_instance();
		$attempts_used      = $attempts_query->count_completed_attempts( $user_id, $quiz_id );
		$max_attempts       = isset( $quiz_settings['max_attempts'] ) ? intval( $quiz_settings['max_attempts'] ) : 0;
		$attempts_remaining = $max_attempts > 0 ? max( 0, $max_attempts - $attempts_used ) : 0;

		return rest_ensure_response(
			array(
				'success'            => true,
				'attempt_id'         => $wpdb->insert_id,
				'score'              => $score,
				'total_points'       => $total_points,
				'percentage'         => $percentage,
				'passed'             => $passed,
				'passing_grade'      => $passing_grade,
				'attempts_used'      => $attempts_used,
				'attempts_remaining' => $attempts_remaining,
				'max_attempts'       => $max_attempts,
			)
		);
	}

	/**
	 * Get quiz results.
	 *
	 * Retrieves comprehensive quiz results for a user including best attempt,
	 * latest attempt, and attempt statistics.
	 *
	 * @since 1.0.0
	 *
	 * @api {get} /splms/v1/quizzes/:id/results Get Quiz Results
	 * @apiName GetQuizResults
	 * @apiGroup Quizzes
	 * @apiVersion 1.0.0
	 *
	 * @apiDescription Retrieve comprehensive quiz results for a user.
	 * Includes best attempt, latest attempt, and attempt count. Requires user authentication.
	 *
	 * @apiParam {Number} id Quiz unique identifier.
	 * @apiParam {Number} [user_id] User ID (defaults to current user).
	 *
	 * @apiError (Error 400) no_user No user specified.
	 * @apiError (Error 401) rest_forbidden User must be logged in.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_quiz_results( $request ) {
		$quiz_id = $request->get_param( 'id' );
		$user_id = $request->get_param( 'user_id' ) ? $request->get_param( 'user_id' ) : get_current_user_id();

		if ( ! $user_id ) {
			return new WP_Error( 'no_user', __( 'No user specified.', 'skillpulse-lms' ), array( 'status' => 400 ) );
		}

		global $wpdb;
		$table_name = esc_sql( $wpdb->prefix . 'splms_quiz_attempts' );

		// Get best attempt.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table name is safe, validated constant.
		$best_attempt = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table_name} WHERE quiz_id = %d AND user_id = %d ORDER BY score DESC, attempt_date DESC LIMIT 1", $quiz_id, $user_id ) );

		// Get latest attempt.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table name is safe, validated constant.
		$latest_attempt = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table_name} WHERE quiz_id = %d AND user_id = %d ORDER BY attempt_date DESC LIMIT 1", $quiz_id, $user_id ) );

		// Get attempt count.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table name is safe, validated constant.
		$attempt_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table_name} WHERE quiz_id = %d AND user_id = %d", $quiz_id, $user_id ) );

		$results = array(
			'quiz_id'        => $quiz_id,
			'user_id'        => $user_id,
			'attempt_count'  => intval( $attempt_count ),
			'best_attempt'   => $best_attempt ? array(
				'score'        => $best_attempt->score,
				'total_points' => $best_attempt->total_points,
				'percentage'   => $best_attempt->total_points > 0 ? ( $best_attempt->score / $best_attempt->total_points ) * 100 : 0,
				'passed'       => $best_attempt->passed,
				'attempt_date' => $best_attempt->attempt_date,
				'time_taken'   => $best_attempt->time_taken,
			) : null,
			'latest_attempt' => $latest_attempt ? array(
				'score'        => $latest_attempt->score,
				'total_points' => $latest_attempt->total_points,
				'percentage'   => $latest_attempt->total_points > 0 ? ( $latest_attempt->score / $latest_attempt->total_points ) * 100 : 0,
				'passed'       => $latest_attempt->passed,
				'attempt_date' => $latest_attempt->attempt_date,
				'time_taken'   => $latest_attempt->time_taken,
			) : null,
		);

		return rest_ensure_response( $results );
	}

	/**
	 * Calculate quiz score.
	 *
	 * @since 1.0.0
	 *
	 * @param array $questions Quiz questions.
	 * @param array $answers   User answers.
	 *
	 * @return array Score calculation result.
	 */
	private function calculate_quiz_score( $questions, $answers ) {
		$score        = 0;
		$total_points = 0;

		foreach ( $questions as $question ) {
			$question_id     = $question['id'];
			$question_points = isset( $question['points'] ) ? intval( $question['points'] ) : 1;
			$total_points   += $question_points;

			if ( ! isset( $answers[ $question_id ] ) ) {
				continue;
			}

			$user_answer = $answers[ $question_id ];
			$correct     = false;

			switch ( $question['type'] ) {
				case 'multiple_choice':
				case 'true_false':
					// Single select - compare option IDs.
					$user_answer_id    = is_array( $user_answer ) ? trim( (string) $user_answer[0] ) : trim( (string) $user_answer );
					$correct_answer_id = trim( (string) $question['correct_answer'] );
					$correct           = ( $user_answer_id === $correct_answer_id );
					break;

				case 'multiple_select':
					// Multiple select - compare arrays of option IDs.
					$user_answer_array    = is_array( $user_answer ) ? $user_answer : array( $user_answer );
					$correct_answer_array = is_array( $question['correct_answer'] ) ? $question['correct_answer'] : array( $question['correct_answer'] );

					// Normalize arrays - convert to strings and trim.
					$user_normalized    = array_map(
						function ( $val ) {
							return trim( (string) $val );
						},
						$user_answer_array
					);
					$correct_normalized = array_map(
						function ( $val ) {
							return trim( (string) $val );
						},
						$correct_answer_array
					);

					// Remove empty values.
					$user_normalized    = array_filter( $user_normalized );
					$correct_normalized = array_filter( $correct_normalized );

					// Sort arrays for comparison.
					sort( $user_normalized );
					sort( $correct_normalized );

					$partial_credit = isset( $question['settings']['partial_credit'] ) && $question['settings']['partial_credit'];

					if ( $partial_credit ) {
						// Calculate partial credit: correct selections / total correct - wrong selections penalty.
						$correct_selected = count( array_intersect( $user_normalized, $correct_normalized ) );
						$wrong_selected   = count( array_diff( $user_normalized, $correct_normalized ) );
						$total_correct    = count( $correct_normalized );

						if ( $total_correct > 0 ) {
							$partial_score = max( 0, ( $correct_selected / $total_correct ) - ( $wrong_selected * 0.1 ) );
							$score        += $question_points * $partial_score;
						}
						// Set $correct to false to prevent double-scoring.
						$correct = false;
					} else {
						// All-or-nothing.
						$correct = ( $user_normalized === $correct_normalized );
					}
					break;

				case 'short_answer':
					$correct = strtolower( trim( $user_answer ) ) === strtolower( trim( $question['correct_answer'] ) );
					break;

				case 'matching':
					// Matching - compare associative arrays {left_id => right_id}.
					$settings = maybe_unserialize( $question['settings'] );
					if ( ! is_array( $settings ) ) {
						$settings = array();
					}
					$pairs          = isset( $settings['pairs'] ) ? $settings['pairs'] : array();
					$partial_credit = isset( $settings['partial_credit'] ) && $settings['partial_credit'];

					// Parse user answer - associative array {left_id => right_id}.
					$user_pairs = is_array( $user_answer ) ? $user_answer : array();
					if ( is_string( $user_answer ) && ! empty( $user_answer ) ) {
						$decoded = json_decode( $user_answer, true );
						if ( is_array( $decoded ) ) {
							$user_pairs = $decoded;
						}
					}

					// Get correct answer from question array (populated from correct_answer_json).
					// correct_answer is an associative array {left_id => right_id}.
					$correct_pairs_assoc = isset( $question['correct_answer'] ) && is_array( $question['correct_answer'] )
						? $question['correct_answer']
						: array();

					$correct_pairs_count = 0;
					$total_pairs         = count( $correct_pairs_assoc );

					// Compare associative arrays.
					foreach ( $correct_pairs_assoc as $left_id => $right_id ) {
						$left_id  = trim( (string) $left_id );
						$right_id = trim( (string) $right_id );

						if ( isset( $user_pairs[ $left_id ] ) ) {
							$user_right_id = trim( (string) $user_pairs[ $left_id ] );
							if ( $user_right_id === $right_id ) {
								++$correct_pairs_count;
							}
						}
					}

					if ( $partial_credit && $total_pairs > 0 ) {
						// Award partial credit based on correct pairs.
						$partial_score = $correct_pairs_count / $total_pairs;
						$score        += $question_points * $partial_score;
						// Set $correct to false to prevent double-scoring.
						$correct = false;
					} else {
						// All-or-nothing.
						$correct = ( $correct_pairs_count === $total_pairs );
					}
					break;

				case 'ordering':
					// Ordering - compare order arrays with partial credit support.
					$user_order = is_array( $user_answer ) ? $user_answer : array();
					$settings   = maybe_unserialize( $question['settings'] );
					if ( ! is_array( $settings ) ) {
						$settings = array();
					}
					$correct_items = isset( $settings['items'] ) ? $settings['items'] : ( isset( $question['items'] ) ? $question['items'] : array() );
					// Get correct_order from question array (populated from correct_answer_json).
					// correct_answer is an array of option IDs in correct order.
					$correct_order  = isset( $question['correct_answer'] ) && is_array( $question['correct_answer'] )
							? $question['correct_answer']
						: array_keys( $correct_items );
					$partial_credit = isset( $settings['partial_credit'] ) && $settings['partial_credit'];

					if ( $partial_credit ) {
						// Calculate partial credit: count items in correct position.
						$correct_positions = 0;
						$total_items       = count( $correct_order );

						$user_order_count = count( $user_order );
						$max_items        = min( $user_order_count, $total_items );
						for ( $i = 0; $i < $max_items; $i++ ) {
							if ( isset( $user_order[ $i ] ) && isset( $correct_order[ $i ] ) && $user_order[ $i ] === $correct_order[ $i ] ) {
								++$correct_positions;
							}
						}

						if ( $total_items > 0 ) {
							$partial_score = $correct_positions / $total_items;
							$score        += $question_points * $partial_score;
						}
						// Set $correct to false to prevent double-scoring.
						$correct = false;
					} else {
						// All-or-nothing.
						$correct = ( $user_order === $correct_order );
					}
					break;

				case 'file_upload':
					// File upload requires manual grading.
					$correct = false;
					break;

				case 'essay':
					// Essays require manual grading.
					$correct = false;
					break;
			}

			if ( $correct ) {
				$score += $question_points;
			}
		}

		return array(
			'score'        => $score,
			'total_points' => $total_points,
		);
	}

	/**
	 * Check if a given request has access to get quiz attempts.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return bool|WP_Error True if request has access, WP_Error object otherwise.
	 */
	public function get_quiz_attempts_permissions_check( $request ) {
		$quiz_id = $request->get_param( 'id' );
		$user_id = get_current_user_id();

		if ( ! $quiz_id ) {
			return new WP_Error( 'invalid_quiz_id', __( 'Invalid quiz ID.', 'skillpulse-lms' ), array( 'status' => 400 ) );
		}

		$access_control = SPLMS_Access_Control::get_instance();

		// If user is logged in, check full access control.
		if ( $user_id ) {
			if ( ! $access_control->user_can_access_quiz( $user_id, $quiz_id ) ) {
				return new WP_Error( 'rest_forbidden', __( 'Sorry, you are not allowed to access this quiz.', 'skillpulse-lms' ), array( 'status' => rest_authorization_required_code() ) );
			}
			return true;
		}

		// For non-logged-in users, only allow if guest preview is enabled.
		if ( ! $access_control->is_guest_preview_quiz( $quiz_id ) ) {
			return new WP_Error( 'rest_forbidden', __( 'Sorry, you must be logged in to view quiz attempts.', 'skillpulse-lms' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}

	/**
	 * Check if a given request has access to submit a quiz attempt.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return bool|WP_Error True if request has access, WP_Error object otherwise.
	 */
	public function submit_quiz_attempt_permissions_check( $request ) {
		$quiz_id = $request->get_param( 'id' );
		$user_id = get_current_user_id();

		if ( ! $quiz_id ) {
			return new WP_Error( 'invalid_quiz_id', __( 'Invalid quiz ID.', 'skillpulse-lms' ), array( 'status' => 400 ) );
		}

		$access_control = SPLMS_Access_Control::get_instance();

		// If user is logged in, check full access control.
		if ( $user_id ) {
			if ( ! $access_control->user_can_access_quiz( $user_id, $quiz_id ) ) {
				return new WP_Error( 'rest_forbidden', __( 'Sorry, you are not allowed to submit this quiz.', 'skillpulse-lms' ), array( 'status' => rest_authorization_required_code() ) );
			}
			return true;
		}

		// For non-logged-in users, only allow if guest preview is enabled.
		if ( ! $access_control->is_guest_preview_quiz( $quiz_id ) ) {
			return new WP_Error( 'rest_forbidden', __( 'Sorry, you must be logged in to submit this quiz.', 'skillpulse-lms' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}

	/**
	 * Check if a given request has access to get quiz results.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return bool|WP_Error True if request has access, WP_Error object otherwise.
	 */
	public function get_quiz_results_permissions_check( $request ) {
		$quiz_id = $request->get_param( 'id' );
		$user_id = get_current_user_id();

		if ( ! $quiz_id ) {
			return new WP_Error( 'invalid_quiz_id', __( 'Invalid quiz ID.', 'skillpulse-lms' ), array( 'status' => 400 ) );
		}

		$access_control = SPLMS_Access_Control::get_instance();

		// If user is logged in, check full access control.
		if ( $user_id ) {
			if ( ! $access_control->user_can_access_quiz( $user_id, $quiz_id ) ) {
				return new WP_Error( 'rest_forbidden', __( 'Sorry, you are not allowed to access this quiz.', 'skillpulse-lms' ), array( 'status' => rest_authorization_required_code() ) );
			}
			return true;
		}

		// For non-logged-in users, only allow if guest preview is enabled.
		if ( ! $access_control->is_guest_preview_quiz( $quiz_id ) ) {
			return new WP_Error( 'rest_forbidden', __( 'Sorry, you must be logged in to view quiz results.', 'skillpulse-lms' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}
}
