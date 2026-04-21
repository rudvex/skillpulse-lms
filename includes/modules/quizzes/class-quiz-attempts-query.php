<?php
/**
 * Quiz Attempts Query Class
 *
 * Handles quiz attempts database operations.
 *
 * @since   1.0.0
 * @package SkillPulse_LMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

if ( ! class_exists( 'SkillPulse_LMS_Base_Query' ) ) {
	require_once SKILLPULSE_LMS_DIR_PATH . 'includes/modules/core/base/class-base-query.php';
}

/**
 * Class SkillPulse_LMS_Quiz_Attempts_Query
 *
 * Handles quiz attempts database operations
 *
 * @since 1.0.0
 */
class SkillPulse_LMS_Quiz_Attempts_Query extends SkillPulse_LMS_Base_Query {

	/**
	 * Constructor.
	 *
	 * @param string $table_name The table name.
	 *
	 * @since 1.0.0
	 */
	protected function __construct( $table_name ) { // phpcs:ignore Generic.CodeAnalysis.UselessOverridingMethod.Found -- Valid pattern for base query classes.
		parent::__construct( $table_name );
	}

	/**
	 * Get the instance of this class.
	 *
	 * @since 1.0.0
	 *
	 * @return SkillPulse_LMS_Quiz_Attempts_Query
	 */
	public static function get_instance() {
		return parent::get_base_instance( __CLASS__, 'splms_quiz_attempts' );
	}

	/**
	 * Start a new quiz attempt.
	 * Prevents duplicate attempts by checking for existing in-progress attempt.
	 *
	 * @param int $user_id   User ID.
	 * @param int $quiz_id   Quiz ID.
	 * @param int $course_id Course ID.
	 *
	 * @since 1.0.0
	 *
	 * @return int|false Attempt ID on success, false on failure.
	 */
	public function start_attempt( $user_id, $quiz_id, $course_id ) {
		global $wpdb;

		// Use database lock to prevent race conditions.
		$lock_name    = 'quiz_attempt_' . $user_id . '_' . $quiz_id;
		$lock_timeout = 5; // Seconds.

		// Acquire lock.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Lock name is safe.
		$lock_acquired = $wpdb->get_var(
			$wpdb->prepare(
				'SELECT GET_LOCK(%s, %d)',
				$lock_name,
				$lock_timeout
			)
		);

		if ( ! $lock_acquired ) {
			return false;
		}

		// Check if there's already an in-progress attempt (now protected by lock).
		$existing_attempt = $this->get_in_progress_attempt( $user_id, $quiz_id );
		if ( $existing_attempt ) {
			// Release lock.
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Lock name is safe.
			$wpdb->query( $wpdb->prepare( 'SELECT RELEASE_LOCK(%s)', $lock_name ) );

			return $existing_attempt->id;
		}

		// Create new attempt (protected by lock - no race conditions).
		$current_time = current_time( 'mysql' );

		$attempt_data = array(
			'user_id'      => $user_id,
			'quiz_id'      => $quiz_id,
			'course_id'    => $course_id,
			'answers'      => wp_json_encode( array() ),
			'score'        => 0.00,
			'max_score'    => 100.00,
			'passed'       => 0,
			'attempt_time' => $current_time,
			'time_taken'   => 0,
		);

		$attempt_id = $this->insert(
			$attempt_data,
			array( '%d', '%d', '%d', '%s', '%f', '%f', '%d', '%s', '%d' )
		);

		// Release lock.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Lock name is safe.
		$wpdb->query( $wpdb->prepare( 'SELECT RELEASE_LOCK(%s)', $lock_name ) );

		return $attempt_id;
	}

	/**
	 * Update quiz attempt progress (answers and time).
	 *
	 * @param int   $attempt_id Attempt ID.
	 * @param array $answers    Current answers.
	 * @param int   $time_taken Time taken so far (seconds).
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function update_attempt_progress( $attempt_id, $answers, $time_taken = 0 ) {
		// Validate attempt exists.
		$attempt = $this->get_attempt_by_id( $attempt_id );
		if ( ! $attempt ) {
			return false;
		}

		// Don't allow updates to completed attempts (time_taken > 0 means submitted).
		if ( intval( $attempt->time_taken ) > 0 ) {
			return false;
		}

		// Verify ownership (calling code should pass current user ID for validation).
		// This is a data integrity check to prevent accidental cross-user updates.
		$current_user_id = get_current_user_id();
		if ( $current_user_id && intval( $attempt->user_id ) !== $current_user_id ) {
			return false;
		}

		return $this->update(
			array(
				'answers'    => wp_json_encode( $answers ),
				'time_taken' => $time_taken,
			),
			array( 'id' => $attempt_id ),
			array( '%s', '%d' ),
			array( '%d' )
		);
	}

	/**
	 * Complete a quiz attempt.
	 *
	 * @param int    $attempt_id    Attempt ID.
	 * @param array  $final_answers Final answers.
	 * @param float  $score         Score achieved (points earned).
	 * @param float  $max_score     Maximum possible score (total points).
	 * @param int    $time_taken    Total time taken (seconds).
	 * @param string $status        Attempt status (default: 'graded').
	 *
	 * @since 1.0.0
	 *
	 * @return bool|int Returns number of rows affected on success, false on failure.
	 */
	public function complete_attempt( $attempt_id, $final_answers, $score, $max_score, $time_taken, $status = 'graded' ) {
		// Validate attempt exists.
		$attempt = $this->get_attempt_by_id( $attempt_id );
		if ( ! $attempt ) {
			return false;
		}

		// Ensure score and max_score are valid floats.
		$score      = floatval( $score );
		$max_score  = floatval( $max_score );
		$time_taken = intval( $time_taken );

		// Calculate pass/fail status based on quiz settings.
		$quizzes_class = SkillPulse_LMS_Quizzes::get_instance();
		$quiz_settings = $quizzes_class->get_quiz_settings( $attempt->quiz_id );
		$passing_grade = isset( $quiz_settings['passing_grade'] ) ? $quiz_settings['passing_grade'] : 70;
		$percentage    = $max_score > 0 ? round( ( $score / $max_score ) * 100, 2 ) : 0;
		$passed        = $percentage >= $passing_grade;

		// Encode answers as JSON.
		$answers_json = wp_json_encode( $final_answers );
		if ( JSON_ERROR_NONE !== json_last_error() ) {
			return false;
		}

		// Perform update.
		$result = $this->update(
			array(
				'answers'        => $answers_json,
				'score'          => $score,
				'max_score'      => $max_score,
				'passed'         => $passed ? 1 : 0,
				'time_taken'     => $time_taken,
				'status'         => $status,
				'submitted_time' => current_time( 'mysql' ),
				'graded_time'    => current_time( 'mysql' ),
			),
			array( 'id' => $attempt_id ),
			array( '%s', '%f', '%f', '%d', '%d', '%s', '%s', '%s' ),
			array( '%d' )
		);

		// Handle result.
		if ( false === $result ) {
			return false;
		} elseif ( 0 === $result ) {
			// No rows updated - this could mean the attempt doesn't exist or values didn't change.
			// Verify attempt still exists.
			$verify_attempt = $this->get_attempt_by_id( $attempt_id );
			if ( ! $verify_attempt ) {
				return false;
			}

			// If attempt exists, consider it successful (values might not have changed).
			return true;
		}

		return true;
	}

	/**
	 * Get user's in-progress attempt for a quiz.
	 * IMPORTANT: Only returns truly in-progress attempts (not completed attempts).
	 * A completed attempt will have time_taken > 0, so we exclude those.
	 *
	 * @param int $user_id User ID.
	 * @param int $quiz_id Quiz ID.
	 *
	 * @since 1.0.0
	 *
	 * @return object|null
	 */
	public function get_in_progress_attempt( $user_id, $quiz_id ) {
		// Only return attempts that are truly in-progress based on status.
		// In-progress means status is 'draft' or 'in_progress' (not submitted/completed).
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table name cannot be prepared.
		$sql = "SELECT * FROM {$this->table_name}
				WHERE user_id = %d
				AND quiz_id = %d
				AND status IN ('draft', 'in_progress')
				ORDER BY attempt_time DESC
				LIMIT 1";

		return $this->get_row( $sql, array( $user_id, $quiz_id ) );
	}

	/**
	 * Get attempt by ID.
	 *
	 * @param int $attempt_id Attempt ID.
	 *
	 * @since 1.0.0
	 *
	 * @return object|null
	 */
	public function get_attempt_by_id( $attempt_id ) {
		$sql = "SELECT * FROM {$this->table_name} WHERE id = %d LIMIT 1";

		return $this->get_row( $sql, array( $attempt_id ) );
	}

	/**
	 * Get user's completed attempts for a quiz.
	 * Returns unique attempts ordered by attempt_time DESC.
	 * An attempt is considered completed if:
	 * - time_taken > 0 (user actually took the quiz), OR
	 * - answers is not empty (user submitted answers), OR
	 * - score > 0.00 OR passed = 1 (traditional completion check).
	 *
	 * @param int $user_id User ID.
	 * @param int $quiz_id Quiz ID.
	 *
	 * @since 1.0.0
	 *
	 * @return array Unique attempts array.
	 */
	public function get_completed_attempts( $user_id, $quiz_id ) {
		// Get attempts that have been submitted/completed.
		// Check: time_taken > 0 OR answers is not empty OR score > 0 OR passed = 1.
		// This ensures all submitted attempts are included, even 0% failed attempts.
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table name cannot be prepared.
		$sql = "SELECT DISTINCT * FROM {$this->table_name} 
				WHERE user_id = %d AND quiz_id = %d 
				AND (time_taken > 0 OR (answers != '' AND answers != '[]' AND answers IS NOT NULL) OR score > 0.00 OR passed = 1) 
				ORDER BY attempt_time DESC";

		$results = $this->get_results( $sql, array( $user_id, $quiz_id ) );

		// Additional deduplication by attempt ID (in case of exact duplicates).
		$unique_attempts = array();
		$seen_ids        = array();

		foreach ( $results as $attempt ) {
			if ( ! in_array( $attempt->id, $seen_ids, true ) ) {
				$seen_ids[]        = $attempt->id;
				$decoded_answers   = json_decode( $attempt->answers, true );
				$attempt->answers  = is_array( $decoded_answers ) ? $decoded_answers : array();
				$unique_attempts[] = $attempt;
			}
		}

		return $unique_attempts;
	}

	/**
	 * Count user's completed attempts for a quiz.
	 * An attempt is considered completed if:
	 * - time_taken > 0 (user actually took the quiz), OR
	 * - answers is not empty (user submitted answers), OR
	 * - score > 0.00 OR passed = 1 (traditional completion check).
	 *
	 * @param int $user_id User ID.
	 * @param int $quiz_id Quiz ID.
	 *
	 * @since 1.0.0
	 *
	 * @return int
	 */
	public function count_completed_attempts( $user_id, $quiz_id ) {
		// Count attempts that have been submitted/completed based on status.
		// Completed means status is 'submitted', 'pending_review', 'graded', etc. (not draft or in_progress).
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table name cannot be prepared.
		$sql = "SELECT COUNT(*) FROM {$this->table_name}
				WHERE user_id = %d AND quiz_id = %d
				AND status NOT IN ('draft', 'in_progress')";

		$result = $this->get_row( $sql, array( $user_id, $quiz_id ) );

		return $result ? intval( array_values( (array) $result )[0] ) : 0;
	}

	/**
	 * Delete attempt and associated files.
	 *
	 * @param int $attempt_id Attempt ID.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function delete_attempt( $attempt_id ) {
		// Get attempt to retrieve file URLs.
		$attempt = $this->get_attempt_by_id( $attempt_id );

		if ( $attempt ) {
			// Parse answers for file URLs.
			$answers = json_decode( $attempt->answers, true );

			if ( is_array( $answers ) ) {
				foreach ( $answers as $question_id => $answer ) {
					// Check if answer is file URL.
					if ( is_string( $answer ) && false !== strpos( $answer, '/uploads/' ) ) {
						// Convert URL to file path.
						$upload_dir = wp_upload_dir();
						$file_path  = str_replace(
							$upload_dir['baseurl'],
							$upload_dir['basedir'],
							$answer
						);

						// Delete file if it exists.
						if ( file_exists( $file_path ) ) {
							wp_delete_file( $file_path );
						}
					}
				}
			}
		}

		// Delete audit log.
		$audit_key = 'splms_attempt_audit_' . $attempt_id;
		delete_option( $audit_key );

		// Delete attempt record.
		return $this->delete(
			array( 'id' => $attempt_id ),
			array( '%d' )
		);
	}

	/**
	 * Check if user has passed a quiz.
	 *
	 * @param int $user_id User ID.
	 * @param int $quiz_id Quiz ID.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function has_user_passed( $user_id, $quiz_id ) {
		$sql = "SELECT COUNT(*) FROM {$this->table_name} WHERE user_id = %d AND quiz_id = %d AND passed = 1";

		$result = $this->get_row( $sql, array( $user_id, $quiz_id ) );

		return $result ? intval( array_values( (array) $result )[0] ) > 0 : false;
	}

	/**
	 * Clear in-progress attempt (abandon attempt).
	 * IMPORTANT: Only deletes attempts that are truly in-progress (not completed).
	 * This should NEVER delete completed attempts, even if called accidentally.
	 *
	 * @param int $user_id User ID.
	 * @param int $quiz_id Quiz ID.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function clear_in_progress_attempt( $user_id, $quiz_id ) {
		// Only delete attempts that are truly in-progress:.
		// - score = 0.00 AND passed = 0 (not completed)
		// - AND time_taken = 0 (not submitted yet).
		// - AND answers is empty or just empty array (no answers saved).
		// This ensures we NEVER delete completed attempts.
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table name cannot be prepared.
		$sql = "DELETE FROM {$this->table_name}
				WHERE user_id = %d
				AND quiz_id = %d
				AND score = 0.00
				AND passed = 0
				AND time_taken = 0
				AND (answers = '' OR answers = '[]' OR answers IS NULL)";

		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- SQL is prepared above.
		$result = $wpdb->query( $wpdb->prepare( $sql, $user_id, $quiz_id ) );

		return false !== $result;
	}

	/**
	 * Clean up abandoned attempts older than specified days.
	 * Run as daily cron job to prevent database bloat.
	 *
	 * @param int $days_old Number of days to consider attempt abandoned (default 7).
	 *
	 * @since 1.0.0
	 *
	 * @return int|false Number of attempts deleted, or false on failure.
	 */
	public function cleanup_abandoned_attempts( $days_old = 7 ) {
		global $wpdb;

		// Calculate cutoff date.
		$cutoff_date = gmdate( 'Y-m-d H:i:s', strtotime( '-' . intval( $days_old ) . ' days' ) );

		// Delete only truly in-progress attempts (not submitted).
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table name cannot be prepared.
		$sql = "DELETE FROM {$this->table_name}
				WHERE score = 0.00
				AND passed = 0
				AND time_taken = 0
				AND attempt_time < %s";

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- SQL is prepared above.
		$result = $wpdb->query( $wpdb->prepare( $sql, $cutoff_date ) );

		return $result;
	}

	/**
	 * Log score changes for audit trail.
	 * Stores audit trail without requiring new table.
	 *
	 * @param int   $attempt_id Attempt ID.
	 * @param float $old_score  Old score value.
	 * @param float $new_score  New score value.
	 * @param int   $changed_by User ID who made the change.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function log_score_change( $attempt_id, $old_score, $new_score, $changed_by ) {
		// Get existing audit log for this attempt.
		$audit_key = 'splms_attempt_audit_' . $attempt_id;
		$audit_log = get_option( $audit_key, array() );

		if ( ! is_array( $audit_log ) ) {
			$audit_log = array();
		}

		// Add new audit entry.
		$audit_log[] = array(
			'action'     => 'score_updated',
			'old_value'  => floatval( $old_score ),
			'new_value'  => floatval( $new_score ),
			'changed_by' => intval( $changed_by ),
			'changed_at' => current_time( 'mysql' ),
		);

		// Keep only last 20 entries per attempt to avoid bloat.
		if ( count( $audit_log ) > 20 ) {
			$audit_log = array_slice( $audit_log, - 20 );
		}

		// Save audit log (autoload = false for performance).
		return update_option( $audit_key, $audit_log, false );
	}

	/**
	 * Get audit log for an attempt.
	 *
	 * @param int $attempt_id Attempt ID.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_audit_log( $attempt_id ) {
		$audit_key = 'splms_attempt_audit_' . $attempt_id;
		$audit_log = get_option( $audit_key, array() );

		return is_array( $audit_log ) ? $audit_log : array();
	}
}
