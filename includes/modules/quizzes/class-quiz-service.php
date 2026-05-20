<?php
/**
 * Quiz Service Layer
 *
 * Provides unified business logic for quiz operations shared by AJAX and REST APIs.
 * Eliminates code duplication and ensures consistent behavior across interfaces.
 *
 * @package SkillPulse_LMS
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Quiz Service class.
 *
 * Centralized service layer for quiz operations, used by both AJAX handlers
 * and REST API controllers to ensure consistent business logic.
 *
 * @since 1.0.0
 */
class SPLMS_Quiz_Service {

	/**
	 * Class instance.
	 *
	 * @since 1.0.0
	 * @var SPLMS_Quiz_Service|null $instance
	 */
	private static $instance = null;

	/**
	 * Quiz instance for status management.
	 *
	 * @since 1.0.0
	 * @var SPLMS_Quizzes $quiz
	 */
	private $quiz;

	/**
	 * Quiz attempts query instance.
	 *
	 * @since 1.0.0
	 * @var SPLMS_Quiz_Attempts_Query $attempts_query
	 */
	private $attempts_query;

	/**
	 * Quiz evaluator instance.
	 *
	 * @since 1.0.0
	 * @var SPLMS_Quiz_Evaluator $evaluator
	 */
	private $evaluator;

	/**
	 * Guest quiz manager instance.
	 *
	 * @since 1.0.0
	 * @var SPLMS_Guest_Quiz_Manager $guest_manager
	 */
	private $guest_manager;

	/**
	 * Get the instance of this class.
	 *
	 * @since 1.0.0
	 *
	 * @return SPLMS_Quiz_Service The singleton instance.
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	private function __construct() {
		$this->quiz           = SPLMS_Quizzes::get_instance();
		$this->attempts_query = SPLMS_Quiz_Attempts_Query::get_instance();
		$this->evaluator      = SPLMS_Quiz_Evaluator::get_instance();
		$this->guest_manager  = SPLMS_Guest_Quiz_Manager::get_instance();
	}

	/**
	 * Start a new quiz attempt.
	 *
	 * @param int   $quiz_id    Quiz ID.
	 * @param int   $user_id    User ID.
	 * @param array $params     Additional parameters (course_id, preview_mode).
	 *
	 * @since 1.0.0
	 *
	 * @return array|WP_Error Quiz data on success, WP_Error on failure.
	 */
	public function start_quiz( $quiz_id, $user_id, $params = array() ) {
		$quiz_id         = absint( $quiz_id );
		$user_id         = absint( $user_id );
		$course_id       = ! empty( $params['course_id'] ) ? absint( $params['course_id'] ) : 0;
		$is_preview_mode = ! empty( $params['preview_mode'] );

		// Validate quiz exists.
		$quiz = get_post( $quiz_id );
		if ( ! $quiz || SPLMS_POST_TYPES['quiz'] !== $quiz->post_type ) {
			return new WP_Error(
				'quiz_not_found',
				__( 'Quiz not found.', 'skillpulse-lms' )
			);
		}

		// Handle guest users with enhanced experience.
		if ( $is_preview_mode && ! $user_id ) {
			return $this->guest_manager->start_guest_quiz( $quiz_id, $params );
		}

		// Check access control for logged-in users.
		if ( ! $is_preview_mode && $user_id ) {
			$access_control = SPLMS_Access_Control::get_instance();
			if ( ! $access_control->user_can_access_quiz( $user_id, $quiz_id ) ) {
				return new WP_Error(
					'access_denied',
					__( 'Access denied to quiz.', 'skillpulse-lms' )
				);
			}
		}

		// Get quiz data.
		$quizzes_class = SPLMS_Quizzes::get_instance();
		$questions     = $quizzes_class->get_quiz_questions( $quiz_id, false );
		$settings      = $quizzes_class->get_quiz_settings( $quiz_id );

		if ( empty( $questions ) ) {
			return new WP_Error(
				'no_questions_found',
				__( 'No questions found for this quiz.', 'skillpulse-lms' )
			);
		}

		// Check attempt limit for non-preview mode.
		$attempt_id = null;
		if ( ! $is_preview_mode && $user_id ) {
			$max_attempts = $this->get_setting_value( $settings, 'max_attempts', 0 );

			if ( $max_attempts > 0 ) {
				$attempts_used = $this->attempts_query->count_completed_attempts( $user_id, $quiz_id );

				if ( $attempts_used >= $max_attempts ) {
					return new WP_Error(
						'no_attempts_remaining',
						__( 'No attempts remaining. You have used all available attempts for this quiz.', 'skillpulse-lms' ),
						array(
							'attempts_used'         => $attempts_used,
							'max_attempts'          => $max_attempts,
							'no_attempts_remaining' => true,
						)
					);
				}
			}

			// Create new attempt record.
			$attempt_id = $this->attempts_query->start_attempt( $user_id, $quiz_id, $course_id );

			if ( ! $attempt_id ) {
				return new WP_Error(
					'failed_to_start_attempt',
					__( 'Failed to start quiz attempt.', 'skillpulse-lms' )
				);
			}

			// Set status to in_progress.
			$this->quiz->update_attempt_status( $attempt_id, 'in_progress' );
		}

		// Return quiz data.
		return array(
			'success'         => true,
			'attempt_id'      => $attempt_id,
			'quiz_id'         => $quiz_id,
			'course_id'       => $course_id,
			'questions'       => $questions,
			'quiz_settings'   => $quizzes_class->flatten_quiz_settings( $settings ),
			'total_questions' => count( $questions ),
			'title'           => get_the_title( $quiz_id ),
			'is_preview_mode' => $is_preview_mode,
		);
	}

	/**
	 * Resume an in-progress quiz attempt.
	 *
	 * @param int $quiz_id Quiz ID.
	 * @param int $user_id User ID.
	 *
	 * @since 1.0.0
	 *
	 * @return array|WP_Error Quiz data with saved state on success, WP_Error on failure.
	 */
	public function resume_quiz( $quiz_id, $user_id ) {
		$quiz_id = absint( $quiz_id );
		$user_id = absint( $user_id );

		// Get in-progress attempt.
		$attempt = $this->attempts_query->get_in_progress_attempt( $user_id, $quiz_id );

		if ( ! $attempt ) {
			return new WP_Error(
				'no_in_progress_attempt',
				__( 'No in-progress attempt found.', 'skillpulse-lms' )
			);
		}

		// Get quiz data.
		$quizzes_class = SPLMS_Quizzes::get_instance();
		$questions     = $quizzes_class->get_quiz_questions( $quiz_id, false );
		$settings      = $quizzes_class->get_quiz_settings( $quiz_id );

		if ( empty( $questions ) ) {
			return new WP_Error(
				'no_questions_found',
				__( 'No questions found for this quiz.', 'skillpulse-lms' )
			);
		}

		// Process saved answers.
		$answers = $attempt->answers;
		if ( is_string( $answers ) ) {
			$decoded = json_decode( $answers, true );
			if ( json_last_error() === JSON_ERROR_NONE ) {
				$answers = $decoded;
			}
		}

		// Calculate current question.
		$current_question = $this->calculate_current_question( $questions, $answers );

		return array(
			'success'         => true,
			'attempt_id'      => $attempt->id,
			'quiz_id'         => $quiz_id,
			'course_id'       => $attempt->course_id,
			'questions'       => $questions,
			'quiz_settings'   => $quizzes_class->flatten_quiz_settings( $settings ),
			'total_questions' => count( $questions ),
			'title'           => get_the_title( $quiz_id ),
			'saved_state'     => array(
				'attempt_id'      => $attempt->id,
				'answers'         => $answers,
				'time_taken'      => $attempt->time_taken,
				'start_time'      => $attempt->attempt_time,
				'currentQuestion' => $current_question,
				'status'          => $attempt->status,
			),
		);
	}

	/**
	 * Get quiz state for guest or regular users.
	 *
	 * @param int|string $identifier    Quiz ID for guest users or user_id for regular users.
	 * @param int        $quiz_id       Quiz ID (required for regular users, ignored for guests).
	 * @param bool       $is_guest      Whether this is a guest user.
	 *
	 * @since 1.0.0
	 *
	 * @return array|WP_Error Quiz state or WP_Error on failure.
	 */
	public function get_quiz_state( $identifier, $quiz_id = 0, $is_guest = false ) {
		if ( $is_guest ) {
			$quiz_id = absint( $identifier );
			return $this->guest_manager->get_guest_quiz_state( $quiz_id );
		}

		// Handle regular users - use resume_quiz functionality.
		$user_id = absint( $identifier );
		$quiz_id = absint( $quiz_id );

		return $this->resume_quiz( $quiz_id, $user_id );
	}

	/**
	 * Save quiz state (answers and time taken).
	 *
	 * @param int|string $attempt_identifier Attempt ID for regular users or quiz_id for guest users.
	 * @param array      $answers            Quiz answers.
	 * @param int        $time_taken         Time taken in seconds.
	 * @param int        $current_question   Current question index.
	 * @param bool       $is_guest           Whether this is a guest user.
	 *
	 * @since 1.0.0
	 *
	 * @return array|WP_Error Success response or WP_Error on failure.
	 */
	public function save_quiz_state( $attempt_identifier, $answers, $time_taken = 0, $current_question = 0, $is_guest = false ) {
		$time_taken       = absint( $time_taken );
		$current_question = absint( $current_question );

		if ( ! is_array( $answers ) ) {
			return new WP_Error(
				'invalid_answers',
				__( 'Answers must be an array.', 'skillpulse-lms' )
			);
		}

		// Handle guest users.
		if ( $is_guest ) {
			$quiz_id = absint( $attempt_identifier );
			return $this->guest_manager->save_guest_quiz_state( $quiz_id, $answers, $time_taken, $current_question );
		}

		// Handle regular users.
		$attempt_id = absint( $attempt_identifier );

		// Update attempt progress.
		$updated = $this->attempts_query->update_attempt_progress( $attempt_id, $answers, $time_taken );

		if ( false === $updated ) {
			return new WP_Error(
				'failed_to_save_progress',
				__( 'Failed to save quiz progress.', 'skillpulse-lms' )
			);
		}

		// Ensure status is in_progress for active saves.
		$current_status = $this->quiz->get_attempt_status( $attempt_id );
		if ( 'draft' === $current_status ) {
			$this->quiz->update_attempt_status( $attempt_id, 'in_progress' );
		}

		return array(
			'success' => true,
			'message' => __( 'Quiz progress saved successfully.', 'skillpulse-lms' ),
		);
	}

	/**
	 * Submit quiz attempt with final answers.
	 *
	 * @param int|string $attempt_identifier Attempt ID for regular users or quiz_id for guest users.
	 * @param array      $answers            Final quiz answers.
	 * @param int        $time_taken         Total time taken in seconds.
	 * @param bool       $is_guest           Whether this is a guest user.
	 *
	 * @since 1.0.0
	 *
	 * @return array|WP_Error Submission results or WP_Error on failure.
	 */
	public function submit_quiz( $attempt_identifier, $answers, $time_taken = 0, $is_guest = false ) {
		$time_taken = absint( $time_taken );

		if ( ! is_array( $answers ) ) {
			return new WP_Error(
				'invalid_answers',
				__( 'Answers must be an array.', 'skillpulse-lms' )
			);
		}

		// Handle guest users.
		if ( $is_guest ) {
			$quiz_id = absint( $attempt_identifier );
			return $this->guest_manager->submit_guest_quiz( $quiz_id, $answers, $time_taken );
		}

		// Handle regular users.
		global $wpdb;
		$attempt_id = absint( $attempt_identifier );

		// Get attempt data.
		$table_name = esc_sql( $wpdb->prefix . 'splms_quiz_attempts' );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table query.
		$attempt = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table_name} WHERE id = %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
				$attempt_id
			)
		);

		if ( ! $attempt ) {
			return new WP_Error(
				'attempt_not_found',
				__( 'Quiz attempt not found.', 'skillpulse-lms' )
			);
		}

		// Get quiz questions and settings.
		$quizzes_class = SPLMS_Quizzes::get_instance();
		$questions     = $quizzes_class->get_quiz_questions( $attempt->quiz_id, false );
		$settings      = $quizzes_class->get_quiz_settings( $attempt->quiz_id );

		// Evaluate quiz.
		$evaluation_result = $this->evaluator->evaluate_quiz_attempt( $questions, $answers );

		if ( is_wp_error( $evaluation_result ) ) {
			return $evaluation_result;
		}

		// Calculate final score and pass/fail.
		$score        = $evaluation_result['score'];
		$total_points = $evaluation_result['total_points'];
		$percentage   = $total_points > 0 ? round( ( $score / $total_points ) * 100, 2 ) : 0;

		$passing_grade = $this->get_setting_value( $settings, 'passing_grade', 70 );
		$passed        = $percentage >= $passing_grade;

		// Determine if manual review is needed.
		$needs_manual_review = $evaluation_result['needs_manual_review'];

		// Update attempt with final data.
		$update_data = array(
			'answers'    => wp_json_encode( $answers ),
			'score'      => $score,
			'max_score'  => $total_points,
			'time_taken' => $time_taken,
			'passed'     => $passed ? 1 : 0,
		);

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table write operation.
		$wpdb->update(
			$table_name,
			$update_data,
			array( 'id' => $attempt_id ),
			array( '%s', '%f', '%f', '%d', '%d' ),
			array( '%d' )
		);

		// Update status based on review needs.
		$new_status = $needs_manual_review ? 'pending_review' : 'graded';
		$this->quiz->update_attempt_status( $attempt_id, $new_status );

		// Get attempts count for response.
		$attempts_used      = $this->attempts_query->count_completed_attempts( $attempt->user_id, $attempt->quiz_id );
		$max_attempts       = $this->get_setting_value( $settings, 'max_attempts', 0 );
		$attempts_remaining = $max_attempts > 0 ? max( 0, $max_attempts - $attempts_used ) : 0;

		return array(
			'success'             => true,
			'attempt_id'          => $attempt_id,
			'score'               => $score,
			'total_points'        => $total_points,
			'percentage'          => $percentage,
			'passed'              => $passed,
			'passing_grade'       => $passing_grade,
			'status'              => $new_status,
			'needs_manual_review' => $needs_manual_review,
			'attempts_used'       => $attempts_used,
			'attempts_remaining'  => $attempts_remaining,
			'max_attempts'        => $max_attempts,
			'status_message'      => $this->quiz->get_status_message( $new_status ),
		);
	}


	/**
	 * Clear quiz state (delete in-progress attempt).
	 *
	 * @param int $quiz_id Quiz ID.
	 * @param int $user_id User ID.
	 *
	 * @since 1.0.0
	 *
	 * @return array|WP_Error Success response or WP_Error on failure.
	 */
	public function clear_quiz_state( $quiz_id, $user_id ) {
		$quiz_id = absint( $quiz_id );
		$user_id = absint( $user_id );

		$cleared = $this->attempts_query->clear_in_progress_attempt( $user_id, $quiz_id );

		if ( false === $cleared ) {
			return new WP_Error(
				'failed_to_clear_state',
				__( 'Failed to clear quiz state or no in-progress state found.', 'skillpulse-lms' )
			);
		}

		return array(
			'success' => true,
			'message' => __( 'Quiz state cleared successfully.', 'skillpulse-lms' ),
		);
	}

	/**
	 * Grade quiz attempt manually.
	 *
	 * @param int    $attempt_id      Attempt ID.
	 * @param array  $question_scores Array of question scores.
	 * @param int    $graded_by       User ID who graded.
	 * @param string $feedback       Optional feedback.
	 *
	 * @since 1.0.0
	 *
	 * @return array|WP_Error Grading results or WP_Error on failure.
	 */
	public function grade_attempt( $attempt_id, $question_scores, $graded_by = 0, $feedback = '' ) {
		global $wpdb;

		$attempt_id = absint( $attempt_id );
		$graded_by  = absint( $graded_by );

		// Get attempt data.
		$table_name = esc_sql( $wpdb->prefix . 'splms_quiz_attempts' );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table query.
		$attempt = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table_name} WHERE id = %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
				$attempt_id
			)
		);

		if ( ! $attempt ) {
			return new WP_Error(
				'attempt_not_found',
				__( 'Quiz attempt not found.', 'skillpulse-lms' )
			);
		}

		// Validate and normalize question scores.
		$questions        = $this->quiz->get_quiz_questions( $attempt->quiz_id, true );
		$validated_scores = array();

		foreach ( $question_scores as $question_id => $points ) {
			$question_id_str = (string) $question_id;
			$question_id_int = intval( $question_id );

			// Find the question to validate and get max points.
			$max_points       = 0;
			$is_manual_review = false;
			foreach ( $questions as $question ) {
				$qid_str = isset( $question['question_id'] ) ? (string) $question['question_id'] : (string) $question['id'];
				$qid_int = isset( $question['question_id'] ) ? intval( $question['question_id'] ) : intval( $question['id'] );

				if ( ( $qid_str === $question_id_str || $qid_int === $question_id_int ) &&
					in_array( $question['type'], array( 'essay', 'long_answer', 'file_upload' ), true ) ) {
					$max_points       = floatval( $question['points'] );
					$is_manual_review = true;
					break;
				}
			}

			if ( $is_manual_review && $max_points > 0 ) {
				// Clamp points between 0 and max_points.
				$points_awarded                       = floatval( $points );
				$points_awarded                       = min( max( $points_awarded, 0 ), $max_points );
				$validated_scores[ $question_id_str ] = $points_awarded;
			}
		}

		if ( empty( $validated_scores ) ) {
			return new WP_Error(
				'invalid_scores',
				__( 'No valid manual review questions found to grade.', 'skillpulse-lms' ),
				array( 'status' => 400 )
			);
		}

		// Get current user answers and update _graded_scores.
		$user_answers = json_decode( $attempt->answers, true );
		if ( JSON_ERROR_NONE !== json_last_error() || ! is_array( $user_answers ) ) {
			$user_answers = array();
		}

		// Initialize _graded_scores if not exists.
		if ( ! isset( $user_answers['_graded_scores'] ) ) {
			$user_answers['_graded_scores'] = array();
		}

		// Update graded scores (this will overwrite existing scores for re-graded questions).
		foreach ( $validated_scores as $question_id => $points ) {
			$user_answers['_graded_scores'][ $question_id ] = $points;
		}

		// Recalculate score from scratch using centralized method.
		$score_result = $this->quiz->recalculate_attempt_score( $attempt_id, $user_answers );

		if ( is_wp_error( $score_result ) ) {
			return $score_result;
		}

		// Determine if all manual review questions are now graded.
		$all_manual_questions_graded = $this->check_all_manual_questions_graded( $questions, $user_answers['_graded_scores'], $user_answers );

		// Determine the correct status.
		$new_status = $all_manual_questions_graded ? 'graded' : 'pending_review';

		// Prepare update data.
		$update_data = array(
			'score'     => $score_result['score'],
			'max_score' => $score_result['max_score'],
			'passed'    => $score_result['passed'],
			'answers'   => wp_json_encode( $user_answers ),
		);

		if ( ! empty( $feedback ) ) {
			$update_data['feedback'] = wp_kses_post( $feedback );
		}

		// Add grading metadata if fully graded.
		if ( 'graded' === $new_status ) {
			$update_data['graded_time'] = current_time( 'mysql' );
			if ( $graded_by > 0 ) {
				$update_data['graded_by'] = $graded_by;
			}
		}

		// Log score change for audit trail.
		$old_score = floatval( $attempt->score );
		$new_score = floatval( $score_result['score'] );
		if ( $old_score !== $new_score && method_exists( $this->attempts_query, 'log_score_change' ) ) {
			$this->attempts_query->log_score_change( $attempt_id, $old_score, $new_score, $graded_by );
		}

		// Update attempt with new data.
		$format       = array( '%f', '%f', '%d', '%s' );
		$where_format = array( '%d' );

		if ( ! empty( $feedback ) ) {
			$format[] = '%s';
		}

		if ( 'graded' === $new_status ) {
			$format[] = '%s'; // graded_time.
			if ( $graded_by > 0 ) {
				$format[] = '%d'; // graded_by.
			}
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table write operation.
		$result = $wpdb->update(
			$table_name,
			$update_data,
			array( 'id' => $attempt_id ),
			$format,
			$where_format
		);

		if ( false === $result ) {
			return new WP_Error(
				'update_failed',
				__( 'Failed to update attempt data.', 'skillpulse-lms' ),
				array( 'status' => 500 )
			);
		}

		// Update status if needed.
		$current_status = isset( $attempt->status ) ? $attempt->status : 'draft';
		if ( $current_status !== $new_status ) {
			$this->quiz->update_attempt_status(
				$attempt_id,
				$new_status,
				array(
					'graded_by' => $graded_by,
					'feedback'  => $feedback,
				)
			);
		}

		return array(
			'success'    => true,
			'score'      => $score_result['score'],
			'max_score'  => $score_result['max_score'],
			'percentage' => $score_result['percentage'],
			'passed'     => (bool) $score_result['passed'],
			'status'     => $new_status,
			'message'    => 'graded' === $new_status
				? __( 'Quiz attempt graded successfully. All questions have been reviewed.', 'skillpulse-lms' )
				: __( 'Questions graded successfully. Some questions still require review.', 'skillpulse-lms' ),
		);
	}

	/**
	 * Check if all manual review questions have been graded.
	 *
	 * @param array $questions      Quiz questions.
	 * @param array $graded_scores  Graded scores array.
	 * @param array $user_answers   User answers array.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if all manual questions are graded.
	 */
	private function check_all_manual_questions_graded( $questions, $graded_scores, $user_answers ) {
		foreach ( $questions as $question ) {
			// Check if this is a manual review question.
			if ( in_array( $question['type'], array( 'essay', 'long_answer', 'file_upload' ), true ) ) {
				$question_id_str = isset( $question['question_id'] ) ? (string) $question['question_id'] : (string) $question['id'];
				$question_id_int = isset( $question['question_id'] ) ? intval( $question['question_id'] ) : intval( $question['id'] );

				// Check if user provided an answer to this question.
				$has_answer = false;
				if ( isset( $user_answers[ $question_id_str ] ) || isset( $user_answers[ $question_id_int ] ) ) {
					$answer = isset( $user_answers[ $question_id_str ] ) ? $user_answers[ $question_id_str ] : $user_answers[ $question_id_int ];

					// Check if answer is not empty.
					if ( is_array( $answer ) ) {
						$has_answer = ! empty( $answer );
					} else {
						$answer_text = trim( (string) $answer );
						$has_answer  = ! empty( $answer_text ) && '[]' !== $answer_text && '{}' !== $answer_text;
					}
				}

				// If user answered this manual question, check if it's been graded.
				if ( $has_answer ) {
					$is_graded = isset( $graded_scores[ $question_id_str ] ) || isset( $graded_scores[ $question_id_int ] );

					if ( ! $is_graded ) {
						// Found an answered manual question that hasn't been graded yet.
						return false;
					}
				}
			}
		}

		// All manual review questions that have answers have been graded.
		return true;
	}

	/**
	 * Get quiz attempts with optional filtering.
	 *
	 * @param array $args Query arguments.
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of attempts.
	 */
	public function get_quiz_attempts( $args = array() ) {
		$defaults = array(
			'quiz_id'   => 0,
			'user_id'   => 0,
			'course_id' => 0,
			'status'    => '',
			'orderby'   => 'attempt_time',
			'order'     => 'DESC',
			'limit'     => 0,
			'offset'    => 0,
		);

		$args = wp_parse_args( $args, $defaults );

		// Use status manager for status-based queries.
		if ( ! empty( $args['status'] ) ) {
			return $this->quiz->get_attempts_by_status( $args['status'], $args );
		}

		// Fallback to attempts query for other filters.
		return $this->attempts_query->get_attempts( $args );
	}

	/**
	 * Calculate current question index based on answers.
	 *
	 * @param array $questions Quiz questions.
	 * @param array $answers   User answers.
	 *
	 * @since 1.0.0
	 *
	 * @return int Current question index.
	 */
	private function calculate_current_question( $questions, $answers ) {
		if ( empty( $answers ) || ! is_array( $answers ) ) {
			return 0;
		}

		// Find the last answered question.
		$last_answered_index = -1;
		foreach ( $questions as $index => $question ) {
			$q_id = isset( $question['id'] ) ? $question['id'] : '';
			if ( isset( $answers[ $q_id ] ) && ! empty( $answers[ $q_id ] ) ) {
				$last_answered_index = $index;
			}
		}

		// Return next unanswered question, or last answered if all answered.
		return ( $last_answered_index >= 0 && $last_answered_index < count( $questions ) - 1 )
			? $last_answered_index + 1
			: max( 0, $last_answered_index );
	}

	/**
	 * Get setting value from quiz settings.
	 *
	 * @param array  $settings    Quiz settings.
	 * @param string $key         Setting key.
	 * @param mixed  $default_val Default value.
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
	 * Enhance response with status information.
	 *
	 * @param array  $response   Base response data.
	 * @param string $status     Current status.
	 * @param array  $extra_info Additional status information.
	 *
	 * @since 1.0.0
	 *
	 * @return array Enhanced response with status info.
	 */
	public function enhance_response_with_status( $response, $status, $extra_info = array() ) {
		$response['status']         = $status;
		$response['status_message'] = $this->quiz->get_status_message( $status );

		// Add next possible actions based on status.
		$next_statuses = $this->quiz->get_next_statuses( $status );
		if ( ! empty( $next_statuses ) ) {
			$response['next_actions'] = array();
			foreach ( $next_statuses as $next_status ) {
				$response['next_actions'][] = array(
					'status'  => $next_status,
					'message' => $this->quiz->get_status_message( $next_status ),
				);
			}
		}

		// Add any extra status information.
		if ( ! empty( $extra_info ) ) {
			$response['status_info'] = $extra_info;
		}

		return $response;
	}
}
