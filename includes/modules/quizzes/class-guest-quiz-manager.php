<?php
/**
 * Guest Quiz Manager
 *
 * Handles quiz functionality for guest (non-logged-in) users
 * providing state persistence and enhanced preview capabilities.
 *
 * @package SkillPulse_LMS
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Guest Quiz Manager class.
 *
 * Provides enhanced quiz functionality for guest users including
 * session-based state persistence, limited resume capabilities,
 * and detailed feedback within the session.
 *
 * @since 1.0.0
 */
class SPLMS_Guest_Quiz_Manager {

	/**
	 * Class instance.
	 *
	 * @since 1.0.0
	 * @var SPLMS_Guest_Quiz_Manager|null $instance
	 */
	private static $instance = null;

	/**
	 * Session key prefix for guest quiz data.
	 *
	 * @since 1.0.0
	 */
	const SESSION_PREFIX = 'splms_guest_quiz_';

	/**
	 * Maximum guest session duration (24 hours).
	 *
	 * @since 1.0.0
	 */
	const SESSION_DURATION = 86400;

	/**
	 * Get the instance of this class.
	 *
	 * @since 1.0.0
	 *
	 * @return SPLMS_Guest_Quiz_Manager The singleton instance.
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
		// Initialization.
	}

	/**
	 * Get or generate a guest session ID stored in a cookie.
	 *
	 * @since 1.0.0
	 * @return string Session ID.
	 */
	private function get_guest_session_id() {
		$cookie_name = 'splms_guest_session_id';
		if ( isset( $_COOKIE[ $cookie_name ] ) ) {
			return sanitize_text_field( wp_unslash( $_COOKIE[ $cookie_name ] ) );
		}

		$session_id = wp_generate_password( 32, false );
		// Set cookie for 24 hours.
		setcookie( $cookie_name, $session_id, time() + self::SESSION_DURATION, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true );
		// Also set it in $_COOKIE immediately for the current request.
		$_COOKIE[ $cookie_name ] = $session_id;

		return $session_id;
	}

	/**
	 * Generate unique session key for transient.
	 *
	 * @param int $quiz_id Quiz ID.
	 *
	 * @since 1.0.0
	 *
	 * @return string Unique session key.
	 */
	private function get_session_key( $quiz_id ) {
		$guest_id = $this->get_guest_session_id();
		return self::SESSION_PREFIX . $guest_id . '_' . $quiz_id;
	}

	/**
	 * Start quiz for guest user.
	 *
	 * @param int   $quiz_id Quiz ID.
	 * @param array $params  Additional parameters.
	 *
	 * @since 1.0.0
	 *
	 * @return array|WP_Error Quiz data on success, WP_Error on failure.
	 */
	public function start_guest_quiz( $quiz_id, $params = array() ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- Parameter kept for future extensibility.
		$quiz_id = absint( $quiz_id );

		// Validate quiz exists and is available for preview.
		$access_control = SPLMS_Access_Control::get_instance();
		if ( ! $access_control->is_guest_preview_quiz( $quiz_id ) ) {
			return new WP_Error(
				'quiz_not_available',
				__( 'This quiz is not available for preview.', 'skillpulse-lms' )
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

		// Get guest session ID to ensure cookie is set early.
		$guest_session_id = $this->get_guest_session_id();
		$session_id       = 'guest_' . $quiz_id . '_' . time() . '_' . wp_generate_password( 8, false );

		// Initialize guest quiz session.
		$session_data = array(
			'quiz_id'          => $quiz_id,
			'session_id'       => $session_id,
			'start_time'       => current_time( 'mysql' ),
			'answers'          => array(),
			'time_taken'       => 0,
			'current_question' => 0,
			'status'           => 'in_progress',
			'is_guest'         => true,
			'expires_at'       => time() + self::SESSION_DURATION,
		);

		$this->save_session_data( $quiz_id, $session_data );

		return array(
			'success'         => true,
			'session_id'      => $session_id,
			'quiz_id'         => $quiz_id,
			'questions'       => $questions,
			'quiz_settings'   => $quizzes_class->flatten_quiz_settings( $settings ),
			'total_questions' => count( $questions ),
			'title'           => get_the_title( $quiz_id ),
			'is_guest_mode'   => true,
			'session_expires' => $session_data['expires_at'],
		);
	}

	/**
	 * Save guest quiz state.
	 *
	 * @param int   $quiz_id       Quiz ID.
	 * @param array $answers       Quiz answers.
	 * @param int   $time_taken    Time taken in seconds.
	 * @param int   $current_question Current question index.
	 *
	 * @since 1.0.0
	 *
	 * @return array|WP_Error Success response or WP_Error on failure.
	 */
	public function save_guest_quiz_state( $quiz_id, $answers, $time_taken = 0, $current_question = 0 ) {
		$quiz_id = absint( $quiz_id );

		$session_data = $this->get_session_data( $quiz_id );
		if ( ! $session_data ) {
			return new WP_Error(
				'session_not_found',
				__( 'Guest quiz session not found or expired.', 'skillpulse-lms' )
			);
		}

		// Check if session has expired.
		if ( time() > $session_data['expires_at'] ) {
			$this->clear_session_data( $quiz_id );
			return new WP_Error(
				'session_expired',
				__( 'Guest quiz session has expired. Please start a new quiz.', 'skillpulse-lms' )
			);
		}

		// Update session data.
		$session_data['answers']          = $answers;
		$session_data['time_taken']       = absint( $time_taken );
		$session_data['current_question'] = absint( $current_question );
		$session_data['last_updated']     = current_time( 'mysql' );

		$this->save_session_data( $quiz_id, $session_data );

		return array(
			'success' => true,
			'message' => __( 'Quiz progress saved successfully.', 'skillpulse-lms' ),
		);
	}

	/**
	 * Get guest quiz state.
	 *
	 * @param int $quiz_id Quiz ID.
	 *
	 * @since 1.0.0
	 *
	 * @return array|WP_Error Saved state or WP_Error if not found.
	 */
	public function get_guest_quiz_state( $quiz_id ) {
		$quiz_id = absint( $quiz_id );

		$session_data = $this->get_session_data( $quiz_id );
		if ( ! $session_data ) {
			return new WP_Error(
				'session_not_found',
				__( 'No guest quiz session found.', 'skillpulse-lms' )
			);
		}

		// Check if session has expired.
		if ( time() > $session_data['expires_at'] ) {
			$this->clear_session_data( $quiz_id );
			return new WP_Error(
				'session_expired',
				__( 'Guest quiz session has expired.', 'skillpulse-lms' )
			);
		}

		return array(
			'success'          => true,
			'session_id'       => $session_data['session_id'],
			'answers'          => $session_data['answers'],
			'time_taken'       => $session_data['time_taken'],
			'current_question' => $session_data['current_question'],
			'start_time'       => $session_data['start_time'],
			'status'           => $session_data['status'],
			'expires_at'       => $session_data['expires_at'],
		);
	}

	/**
	 * Submit guest quiz.
	 *
	 * @param int   $quiz_id    Quiz ID.
	 * @param array $answers    Final quiz answers.
	 * @param int   $time_taken Total time taken in seconds.
	 *
	 * @since 1.0.0
	 *
	 * @return array|WP_Error Submission results or WP_Error on failure.
	 */
	public function submit_guest_quiz( $quiz_id, $answers, $time_taken = 0 ) {
		$quiz_id = absint( $quiz_id );

		$session_data = $this->get_session_data( $quiz_id );
		if ( ! $session_data ) {
			return new WP_Error(
				'session_not_found',
				__( 'Guest quiz session not found.', 'skillpulse-lms' )
			);
		}

		// Get quiz questions and settings for evaluation.
		$quizzes_class = SPLMS_Quizzes::get_instance();
		$questions     = $quizzes_class->get_quiz_questions( $quiz_id, false );
		$settings      = $quizzes_class->get_quiz_settings( $quiz_id );

		// Evaluate quiz using the quiz evaluator.
		$evaluator         = SPLMS_Quiz_Evaluator::get_instance();
		$evaluation_result = $evaluator->evaluate_quiz_attempt( $questions, $answers );

		if ( is_wp_error( $evaluation_result ) ) {
			return $evaluation_result;
		}

		// Calculate final score and pass/fail.
		$score        = $evaluation_result['score'];
		$total_points = $evaluation_result['total_points'];
		$percentage   = $total_points > 0 ? round( ( $score / $total_points ) * 100, 2 ) : 0;

		// Get passing grade from settings.
		$passing_grade = 70; // Default.
		if ( ! empty( $settings ) ) {
			foreach ( $settings as $group ) {
				if ( is_array( $group ) && isset( $group['passing_grade'] ) ) {
					$passing_grade = floatval( $group['passing_grade'] );
					break;
				}
			}
		}

		$passed = $percentage >= $passing_grade;

		// Update session with final results.
		$session_data['status']       = 'completed';
		$session_data['answers']      = $answers;
		$session_data['time_taken']   = absint( $time_taken );
		$session_data['score']        = $score;
		$session_data['total_points'] = $total_points;
		$session_data['percentage']   = $percentage;
		$session_data['passed']       = $passed;
		$session_data['completed_at'] = current_time( 'mysql' );

		$this->save_session_data( $quiz_id, $session_data );

		return array(
			'success'       => true,
			'score'         => $score,
			'total_points'  => $total_points,
			'percentage'    => $percentage,
			'passed'        => $passed,
			'passing_grade' => $passing_grade,
			'status'        => 'completed',
			'is_guest_mode' => true,
			'message'       => $passed
				? __( 'Congratulations! You passed this quiz preview.', 'skillpulse-lms' )
				: __( 'Quiz completed. Consider enrolling for full course access and certification.', 'skillpulse-lms' ),
		);
	}

	/**
	 * Clear guest quiz state.
	 *
	 * @param int $quiz_id Quiz ID.
	 *
	 * @since 1.0.0
	 *
	 * @return array Success response.
	 */
	public function clear_guest_quiz_state( $quiz_id ) {
		$quiz_id = absint( $quiz_id );
		$this->clear_session_data( $quiz_id );

		return array(
			'success' => true,
			'message' => __( 'Guest quiz session cleared.', 'skillpulse-lms' ),
		);
	}

	/**
	 * Get guest quiz results.
	 *
	 * @param int $quiz_id Quiz ID.
	 *
	 * @since 1.0.0
	 *
	 * @return array|WP_Error Quiz results or WP_Error if not found.
	 */
	public function get_guest_quiz_results( $quiz_id ) {
		$quiz_id = absint( $quiz_id );

		$session_data = $this->get_session_data( $quiz_id );
		if ( ! $session_data || 'completed' !== $session_data['status'] ) {
			return new WP_Error(
				'results_not_found',
				__( 'No completed quiz results found in guest session.', 'skillpulse-lms' )
			);
		}

		return array(
			'success'       => true,
			'score'         => $session_data['score'],
			'total_points'  => $session_data['total_points'],
			'percentage'    => $session_data['percentage'],
			'passed'        => $session_data['passed'],
			'time_taken'    => $session_data['time_taken'],
			'completed_at'  => $session_data['completed_at'],
			'is_guest_mode' => true,
		);
	}

	/**
	 * Generate unique session ID.
	 *
	 * @param int $quiz_id Quiz ID.
	 *
	 * @since 1.0.0
	 *
	 * @return string Unique session ID.
	 */
	private function generate_session_id( $quiz_id ) {
		return 'guest_' . $quiz_id . '_' . time() . '_' . wp_generate_password( 8, false );
	}

	/**
	 * Save session data.
	 *
	 * @param int   $quiz_id      Quiz ID.
	 * @param array $session_data Session data to save.
	 *
	 * @since 1.0.0
	 */
	private function save_session_data( $quiz_id, $session_data ) {
		$session_key = $this->get_session_key( $quiz_id );
		set_transient( $session_key, $session_data, self::SESSION_DURATION );
	}

	/**
	 * Get session data.
	 *
	 * @param int $quiz_id Quiz ID.
	 *
	 * @since 1.0.0
	 *
	 * @return array|null Session data or null if not found.
	 */
	private function get_session_data( $quiz_id ) {
		$session_key  = $this->get_session_key( $quiz_id );
		$session_data = get_transient( $session_key );

		if ( false === $session_data ) {
			return null;
		}

		// Sanitize session data even though it is application-controlled.
		if ( is_array( $session_data ) ) {
			return map_deep( $session_data, 'sanitize_text_field' );
		}

		return sanitize_text_field( $session_data );
	}

	/**
	 * Clear session data.
	 *
	 * @param int $quiz_id Quiz ID.
	 *
	 * @since 1.0.0
	 */
	private function clear_session_data( $quiz_id ) {
		$session_key = $this->get_session_key( $quiz_id );
		delete_transient( $session_key );
	}
}
