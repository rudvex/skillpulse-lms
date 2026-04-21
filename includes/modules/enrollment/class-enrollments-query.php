<?php
/**
 * Enrollments Query Class
 *
 * Handles course enrollments database operations.
 *
 * @package SkillPulse_LMS
 * @subpackage Enrollment
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

if ( ! class_exists( 'SkillPulse_LMS_Base_Query' ) ) {
	require_once SKILLPULSE_LMS_DIR_PATH . 'includes/modules/core/base/class-base-query.php';
}

/**
 * Class SkillPulse_LMS_Enrollments_Query
 *
 * @since 1.0.0
 */
class SkillPulse_LMS_Enrollments_Query extends SkillPulse_LMS_Base_Query {

	/**
	 * Request-level cache for enrollment queries.
	 *
	 * @since 1.0.0
	 *
	 * @var array Cache array keyed by "user_id_course_id".
	 */
	private static $enrollment_cache = array();

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param string $table_name Table name.
	 * @return void
	 */
	protected function __construct( $table_name ) { // phpcs:ignore Generic.CodeAnalysis.UselessOverridingMethod.Found -- Constructor needed to call parent.
		parent::__construct( $table_name );
	}

	/**
	 * Get the instance of this class.
	 *
	 * @since 1.0.0
	 *
	 * @return SkillPulse_LMS_Enrollments_Query The class instance.
	 */
	public static function get_instance() {
		return parent::get_base_instance( __CLASS__, 'splms_enrollments' );
	}

	/**
	 * Enroll user in course.
	 *
	 * @since 1.0.0
	 *
	 * @param int    $user_id           User ID.
	 * @param int    $course_id         Course ID.
	 * @param string $status            Enrollment status (default: 'active').
	 * @param string $enrollment_method Enrollment method (manual, purchase, etc.).
	 * @return int|false Enrollment ID on success, false on failure.
	 */
	public function enroll_user( $user_id, $course_id, $status = 'active', $enrollment_method = 'manual' ) {
		// Check if enrollment already exists (regardless of status).
		$existing_enrollment = $this->get_enrollment( $user_id, $course_id );

		// Calculate expiration date if course has expiration enabled.
		$access_expires  = null;
		$expiration_date = splms_get_enrollment_expiration_date( $user_id, $course_id );
		if ( $expiration_date ) {
			$access_expires = $expiration_date;
		}

		// If enrollment exists, update it instead of inserting.
		if ( $existing_enrollment ) {
			$update_data = array(
				'status'            => $status,
				'enrollment_method' => $enrollment_method,
				'last_accessed_at'  => current_time( 'mysql' ),
				// Update enrolled_at only if it's being reactivated from inactive.
				'enrolled_at'       => ( 'inactive' === $existing_enrollment->status ) ? current_time( 'mysql' ) : $existing_enrollment->enrolled_at,
			);

			// Update expiration date if provided.
			if ( $access_expires ) {
				$update_data['access_expires'] = $access_expires;
			}

			$result = $this->update(
				$update_data,
				array(
					'user_id'   => $user_id,
					'course_id' => $course_id,
				),
				$access_expires ? array( '%s', '%s', '%s', '%s', '%s' ) : array( '%s', '%s', '%s', '%s' ),
				array( '%d', '%d' )
			);

			// Clear cache after update.
			if ( false !== $result ) {
				$this->clear_enrollment_cache( $user_id, $course_id );
			}

			if ( false !== $result ) {
				return $existing_enrollment->id;
			}

			return false;
		}

		// Insert new enrollment if it doesn't exist.
		$enrollment_data = array(
			'user_id'           => $user_id,
			'course_id'         => $course_id,
			'enrolled_at'       => current_time( 'mysql' ),
			'last_accessed_at'  => current_time( 'mysql' ),
			'status'            => $status,
			'enrollment_method' => $enrollment_method,
		);

		if ( $access_expires ) {
			$enrollment_data['access_expires'] = $access_expires;
		}

		// Use direct database upsert to handle duplicate key conflicts.
		global $wpdb;

		$table_name    = esc_sql( $wpdb->prefix . 'splms_enrollments' );
		$format_values = $access_expires ? array( '%d', '%d', '%s', '%s', '%s', '%s', '%s' ) : array( '%d', '%d', '%s', '%s', '%s', '%s' );

		// Build the INSERT query with ON DUPLICATE KEY UPDATE.
		$columns = array_keys( $enrollment_data );
		$values  = array_values( $enrollment_data );

		$sql  = "INSERT INTO `{$table_name}` (`" . implode( '`, `', $columns ) . '`) VALUES (' . implode( ', ', $format_values ) . ')';
		$sql .= ' ON DUPLICATE KEY UPDATE ';

		$update_parts = array();
		foreach ( $columns as $i => $column ) {
			if ( ! in_array( $column, array( 'user_id', 'course_id' ), true ) ) { // Don't update the unique key columns.
				$update_parts[] = "`{$column}` = VALUES(`{$column}`)";
			}
		}
		$sql .= implode( ', ', $update_parts );

		$result = $wpdb->query( $wpdb->prepare( $sql, $values ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Custom table write operation.

		// Return the enrollment ID (for new inserts, use insert_id; for updates, we need to get it).
		if ( false !== $result ) {
			if ( $wpdb->insert_id > 0 ) {
				$result = $wpdb->insert_id; // New insert.
			} else {
				// Update case - get the existing enrollment ID.
				$existing = $this->get_enrollment( $user_id, $course_id );
				$result   = $existing ? $existing->id : false;
			}
		}

		// Clear cache after enrollment.
		if ( false !== $result ) {
			$this->clear_enrollment_cache( $user_id, $course_id );
		}

		return $result;
	}

	/**
	 * Update course enrollment progress percentage.
	 *
	 * @since 1.0.0
	 *
	 * @param int   $user_id   User ID.
	 * @param int   $course_id Course ID.
	 * @param float $progress  Progress percentage (0-100).
	 * @return bool True on success, false on failure.
	 */
	public function update_enrollment_progress( $user_id, $course_id, $progress ) {
		$update_data = array(
			'progress'         => $progress,
			'last_accessed_at' => current_time( 'mysql' ),
		);

		// If course is completed (100%), set completed_at.
		if ( $progress >= 100 ) {
			$update_data['completed_at'] = current_time( 'mysql' );
			$update_data['status']       = 'completed';
		}

		$result = $this->update(
			$update_data,
			array(
				'user_id'   => $user_id,
				'course_id' => $course_id,
			),
			array( '%f', '%s', '%s', '%s' ),
			array( '%d', '%d' )
		);

		// Clear cache after progress update.
		if ( false !== $result ) {
			$this->clear_enrollment_cache( $user_id, $course_id );
		}

		return $result;
	}

	/**
	 * Check if user is enrolled in course.
	 *
	 * @since 1.0.0
	 *
	 * @param int   $course_id Course ID.
	 * @param int   $user_id   User ID.
	 * @param array $args      Additional arguments.
	 * @return bool True if enrolled, false otherwise.
	 */
	public function is_user_enrolled( $course_id, $user_id = null, $args = array() ) {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		// Default statuses.
		$statuses = array( 'active', 'completed' );

		// Allow overriding via $args.
		if ( ! empty( $args['status'] ) ) {
			$statuses = is_array( $args['status'] ) ? $args['status'] : array( $args['status'] );
		}

		global $wpdb;

		// Prepare placeholders for IN clause.
		$placeholders = implode( ',', array_fill( 0, count( $statuses ), '%s' ) );

		// Final SQL with proper placeholders.
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table name cannot be prepared, values are prepared.
		$sql = "SELECT COUNT(*) FROM {$this->table_name} WHERE user_id = %d AND course_id = %d AND status IN ($placeholders)";

		// Merge values for prepare.
		$values = array_merge( array( $user_id, $course_id ), $statuses );

		// Run the query.
		if ( empty( $values ) ) {
			$count = $wpdb->get_var( $sql ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- SQL is prepared with placeholders.
		} else {
			$count = $wpdb->get_var( $wpdb->prepare( $sql, ...$values ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- SQL is prepared with placeholders.
		}

		return intval( $count ) > 0;
	}

	/**
	 * Get user enrollment status.
	 *
	 * @since 1.0.0
	 *
	 * @param int $course_id Course ID.
	 * @param int $user_id   User ID.
	 * @return string|false Enrollment status on success, false on failure.
	 */
	public function get_user_enrollment_status( $course_id, $user_id = null ) {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		$enrollment = $this->get_enrollment( $user_id, $course_id );

		if ( ! $enrollment ) {
			return false;
		}

		return $enrollment->status;
	}

	/**
	 * Get enrollment data.
	 *
	 * @since 1.0.0
	 *
	 * @param int $user_id   User ID.
	 * @param int $course_id Course ID.
	 * @return object|null Enrollment object on success, null on failure.
	 */
	public function get_enrollment( $user_id, $course_id ) {
		// Check request-level cache first.
		$cache_key = "{$user_id}_{$course_id}";
		if ( isset( self::$enrollment_cache[ $cache_key ] ) ) {
			return self::$enrollment_cache[ $cache_key ];
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table name cannot be prepared, values are prepared.
		$sql = "SELECT * FROM {$this->table_name} WHERE user_id = %d AND course_id = %d";

		$enrollment = $this->get_row( $sql, array( $user_id, $course_id ) );

		// Cache the result (even if null) to prevent duplicate queries.
		self::$enrollment_cache[ $cache_key ] = $enrollment;

		return $enrollment;
	}

	/**
	 * Clear enrollment cache for a specific user/course combination.
	 *
	 * @since 1.0.0
	 *
	 * @param int $user_id   User ID.
	 * @param int $course_id Course ID.
	 * @return void
	 */
	public function clear_enrollment_cache( $user_id = null, $course_id = null ) {
		if ( null !== $user_id && null !== $course_id ) {
			// Clear specific cache entry.
			$cache_key = "{$user_id}_{$course_id}";
			unset( self::$enrollment_cache[ $cache_key ] );
		} else {
			// Clear all cache.
			self::$enrollment_cache = array();
		}
	}

	/**
	 * Get user's enrolled courses.
	 *
	 * @since 1.0.0
	 *
	 * @param int   $user_id User ID.
	 * @param array $args    Additional arguments.
	 * @return array Array of enrollment objects.
	 */
	public function get_user_courses( $user_id, $args = array() ) {
		$defaults = array(
			'status'  => array( 'active', 'completed' ),
			'orderby' => 'enrolled_at',
			'order'   => 'DESC',
			'limit'   => null,
		);

		$args = wp_parse_args( $args, $defaults );

		global $wpdb;

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table name cannot be prepared, values are prepared.
		$sql = "SELECT * FROM {$this->table_name} WHERE user_id = %d";

		$values = array( $user_id );

		if ( $args['status'] ) {
			$statuses     = is_array( $args['status'] ) ? $args['status'] : array( $args['status'] );
			$placeholders = implode( ',', array_fill( 0, count( $statuses ), '%s' ) );
			$sql         .= " AND status IN ($placeholders)";
			$values       = array_merge( $values, $statuses );
		}

		// Validate orderby and order to prevent SQL injection.
		$allowed_orderby = array( 'enrolled_at', 'last_accessed_at', 'progress', 'completed_at' );
		$allowed_order   = array( 'ASC', 'DESC' );
		$orderby         = in_array( $args['orderby'], $allowed_orderby, true ) ? $args['orderby'] : 'enrolled_at';
		$order           = in_array( strtoupper( $args['order'] ), $allowed_order, true ) ? strtoupper( $args['order'] ) : 'DESC';
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Orderby and order are validated.
		$sql .= " ORDER BY {$orderby} {$order}";

		if ( $args['limit'] ) {
			$limit = intval( $args['limit'] );
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Limit is sanitized.
			$sql .= " LIMIT {$limit}";
		}

		return $this->get_results( $sql, $values );
	}

	/**
	 * Unenroll user from course.
	 *
	 * @since 1.0.0
	 *
	 * @param int $user_id   User ID.
	 * @param int $course_id Course ID.
	 * @return bool True on success, false on failure.
	 */
	public function unenroll_user( $user_id, $course_id ) {
		$result = $this->update(
			array( 'status' => 'inactive' ),
			array(
				'user_id'   => $user_id,
				'course_id' => $course_id,
			),
			array( '%s' ),
			array( '%d', '%d' )
		);

		// Clear cache after unenrollment.
		if ( false !== $result ) {
			$this->clear_enrollment_cache( $user_id, $course_id );
		}

		return $result;
	}

	/**
	 * Get enrollment by ID.
	 *
	 * @since 1.0.0
	 *
	 * @param int $enrollment_id Enrollment ID.
	 * @return object|null Enrollment data or null if not found.
	 */
	public function get_enrollment_by_id( $enrollment_id ) {
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table name cannot be prepared, values are prepared.
		$sql = "SELECT * FROM {$this->table_name} WHERE id = %d";

		return $this->get_row( $sql, array( $enrollment_id ) );
	}

	/**
	 * Update enrollment by ID.
	 *
	 * @since 1.0.0
	 *
	 * @param int   $enrollment_id Enrollment ID.
	 * @param array $data         Enrollment data to update.
	 * @return bool|int Number of rows affected on success, false on failure.
	 */
	public function update_enrollment( $enrollment_id, $data ) {
		global $wpdb;

		// Prepare update data with proper formats.
		$update_data    = array();
		$update_formats = array();

		if ( isset( $data['status'] ) ) {
			$update_data['status'] = $data['status'];
			$update_formats[]      = '%s';
		}

		if ( isset( $data['progress'] ) ) {
			$update_data['progress'] = $data['progress'];
			$update_formats[]        = '%f';
		}

		if ( isset( $data['completed_at'] ) ) {
			$update_data['completed_at'] = $data['completed_at'];
			$update_formats[]            = '%s';
		}

		if ( isset( $data['access_expires'] ) ) {
			$update_data['access_expires'] = $data['access_expires'];
			$update_formats[]              = '%s';
		}

		if ( empty( $update_data ) ) {
			return false;
		}

		// Handle NULL values separately (wpdb->update doesn't handle NULL well).
		$result = false;
		if ( isset( $data['completed_at'] ) && null === $data['completed_at'] ) {
			// Use direct SQL to set NULL value.
			// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name cannot be prepared, custom table write operation.
			$result = $wpdb->query(
				$wpdb->prepare(
					"UPDATE {$this->table_name} SET completed_at = NULL WHERE id = %d",
					$enrollment_id
				)
			);
			// phpcs:enable
			// Remove completed_at from update_data since we handled it separately.
			unset( $update_data['completed_at'] );
			array_pop( $update_formats );
		}

		// Update remaining fields if any.
		if ( ! empty( $update_data ) ) {
			$result = $this->update(
				$update_data,
				array( 'id' => $enrollment_id ),
				$update_formats,
				array( '%d' )
			);
		}

		// Clear cache after update if we have enrollment data.
		if ( false !== $result ) {
			// Get enrollment to clear cache by user_id and course_id.
			$enrollment = $this->get_enrollment_by_id( $enrollment_id );
			if ( $enrollment ) {
				$this->clear_enrollment_cache( $enrollment->user_id, $enrollment->course_id );
			}
		}

		return $result;
	}

	/**
	 * Delete enrollment by ID.
	 *
	 * @since 1.0.0
	 *
	 * @param int $enrollment_id Enrollment ID.
	 * @return bool True on success, false on failure.
	 * @throws Exception If database transaction fails.
	 */
	public function delete_enrollment( $enrollment_id ) {
		global $wpdb;

		// Get enrollment details first.
		$enrollment = $this->get_enrollment_by_id( $enrollment_id );
		if ( ! $enrollment ) {
			return false;
		}

		$user_id   = $enrollment->user_id;
		$course_id = $enrollment->course_id;

		// Start transaction for data consistency.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table write operation.
		$wpdb->query( 'START TRANSACTION' );

		try {
			// 1. Delete lesson progress data.
			$lesson_progress_table = esc_sql( $wpdb->prefix . 'splms_lesson_progress' );
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table write operation.
			$wpdb->delete(
				$lesson_progress_table,
				array(
					'user_id'   => $user_id,
					'course_id' => $course_id,
				),
				array( '%d', '%d' )
			);

			// 2. Delete quiz attempts data.
			$quiz_attempts_table = esc_sql( $wpdb->prefix . 'splms_quiz_attempts' );
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table write operation.
			$wpdb->delete(
				$quiz_attempts_table,
				array(
					'user_id'   => $user_id,
					'course_id' => $course_id,
				),
				array( '%d', '%d' )
			);

			// 3. Delete user activity logs for this course.
			$user_activity_table = esc_sql( $wpdb->prefix . 'splms_user_activity' );
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table write operation.
			$wpdb->delete(
				$user_activity_table,
				array(
					'user_id'   => $user_id,
					'course_id' => $course_id,
				),
				array( '%d', '%d' )
			);

			// 5. Finally delete the enrollment record.
			$result = $this->delete(
				array( 'id' => $enrollment_id ),
				array( '%d' )
			);

			if ( false === $result ) {
				throw new Exception( 'Failed to delete enrollment record' );
			}

			// Commit transaction.
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table write operation.
			$wpdb->query( 'COMMIT' );

			// Clear cache.
			$this->clear_enrollment_cache( $user_id, $course_id );

			return true;

		} catch ( Exception $e ) {
			// Rollback transaction on error.
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table write operation.
			$wpdb->query( 'ROLLBACK' );
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Error logging is necessary for debugging enrollment deletion failures.
			error_log( 'SkillPulse LMS: Failed to delete enrollment - ' . $e->getMessage() );
			return false;
		}
	}

	/**
	 * Bulk update enrollments.
	 *
	 * @since 1.0.0
	 *
	 * @param array $enrollment_ids Array of enrollment IDs.
	 * @param array $data          Data to update.
	 * @return int|false Number of rows affected on success, false on failure.
	 */
	public function bulk_update_enrollments( $enrollment_ids, $data ) {
		global $wpdb;

		if ( empty( $enrollment_ids ) || empty( $data ) ) {
			return false;
		}

		$placeholders = implode( ',', array_fill( 0, count( $enrollment_ids ), '%d' ) );

		// Build SET clause.
		$set_clauses = array();
		$values      = array();

		if ( isset( $data['status'] ) ) {
			$set_clauses[] = 'status = %s';
			$values[]      = $data['status'];
		}

		if ( isset( $data['progress'] ) ) {
			$set_clauses[] = 'progress = %f';
			$values[]      = $data['progress'];
		}

		if ( isset( $data['completed_at'] ) ) {
			$set_clauses[] = 'completed_at = %s';
			$values[]      = $data['completed_at'];
		}

		if ( empty( $set_clauses ) ) {
			return false;
		}

		$set_clause = implode( ', ', $set_clauses );
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table name cannot be prepared.
		$query = "UPDATE {$this->table_name} SET $set_clause WHERE id IN ($placeholders)";

		$query_values = array_merge( $values, $enrollment_ids );

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Query is prepared above.
		if ( empty( $query_values ) ) {
			$result = $wpdb->query( $query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Query is prepared with placeholders.
		} else {
			$result = $wpdb->query( $wpdb->prepare( $query, ...$query_values ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Query is prepared with placeholders.
		}

		// Clear all cache after bulk update.
		if ( false !== $result ) {
			$this->clear_enrollment_cache();
		}

		return $result;
	}

	/**
	 * Bulk delete enrollments.
	 *
	 * @since 1.0.0
	 *
	 * @param array $enrollment_ids Array of enrollment IDs.
	 * @return int|false Number of rows affected on success, false on failure.
	 */
	public function bulk_delete_enrollments( $enrollment_ids ) {
		global $wpdb;

		if ( empty( $enrollment_ids ) ) {
			return false;
		}

		$placeholders = implode( ',', array_fill( 0, count( $enrollment_ids ), '%d' ) );

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table name cannot be prepared.
		$query = "DELETE FROM {$this->table_name} WHERE id IN ($placeholders)";

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Query is prepared above.
		if ( empty( $enrollment_ids ) ) {
			$result = $wpdb->query( $query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Query is prepared with placeholders.
		} else {
			$result = $wpdb->query( $wpdb->prepare( $query, ...$enrollment_ids ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Query is prepared with placeholders.
		}

		// Clear all cache after bulk delete.
		if ( false !== $result ) {
			$this->clear_enrollment_cache();
		}

		return $result;
	}

	/**
	 * Get course enrollment count.
	 *
	 * @since 1.0.0
	 *
	 * @param int    $course_id Course ID.
	 * @param string $status    Optional enrollment status filter.
	 * @return int Enrollment count.
	 */
	public function get_course_enrollment_count( $course_id, $status = null ) {
		global $wpdb;

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.PreparedSQL.NotPrepared -- Table name cannot be prepared, values are prepared.
		if ( $status ) {
			$sql    = "SELECT COUNT(*) FROM {$this->table_name} WHERE course_id = %d AND status = %s";
			$result = (int) $wpdb->get_var( $wpdb->prepare( $sql, $course_id, $status ) );
		} else {
			// Count all enrollments regardless of status.
			$sql = "SELECT COUNT(*) FROM {$this->table_name} WHERE course_id = %d";
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Query is safely prepared via $wpdb->prepare().
			$result = (int) $wpdb->get_var( $wpdb->prepare( $sql, $course_id ) );
		}
		// phpcs:enable
		return $result;
	}

	/**
	 * Renew enrollment by extending expiration date.
	 *
	 * @since 1.0.0
	 *
	 * @param int $enrollment_id Enrollment ID.
	 * @param int $additional_days Number of days to extend access.
	 * @return bool True on success, false on failure.
	 */
	public function renew_enrollment( $enrollment_id, $additional_days = 0 ) {
		// Get enrollment.
		$enrollment = $this->get_enrollment_by_id( $enrollment_id );

		if ( ! $enrollment ) {
			return false;
		}

		// Calculate new expiration date.
		$new_expiration = null;
		if ( $additional_days > 0 ) {
			// Extend from current expiration or enrollment date.
			$base_date      = $enrollment->access_expires ? $enrollment->access_expires : $enrollment->enrolled_at;
			$new_expiration = date( 'Y-m-d H:i:s', strtotime( $base_date . ' + ' . $additional_days . ' days' ) ); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date -- Date formatting for database storage.
		} else {
			// Recalculate from course settings.
			$new_expiration = splms_get_enrollment_expiration_date( $enrollment->user_id, $enrollment->course_id );
		}

		// Update enrollment.
		$update_data = array(
			'status' => 'active',
		);

		if ( $new_expiration ) {
			$update_data['access_expires'] = $new_expiration;
		}

		$result = $this->update(
			$update_data,
			array( 'id' => $enrollment_id ),
			$new_expiration ? array( '%s', '%s' ) : array( '%s' ),
			array( '%d' )
		);

		// Clear cache after update.
		if ( false !== $result ) {
			$this->clear_enrollment_cache( $enrollment->user_id, $enrollment->course_id );
		}

		if ( false !== $result ) {
			// Get enrollment for action hook.
			$enrollment = $this->get_enrollment_by_id( $enrollment_id );
			if ( $enrollment ) {
				$update_data     = array(
					'status' => 'active',
				);
				$expiration_date = splms_get_enrollment_expiration_date( $enrollment->user_id, $enrollment->course_id );
				if ( $expiration_date ) {
					$update_data['access_expires'] = $expiration_date;
				}

				/**
				 * Fires when an enrollment is renewed.
				 *
				 * @since 1.0.0
				 *
				 * @param int   $enrollment_id Enrollment ID.
				 * @param int   $additional_days Number of days extended.
				 * @param array $update_data Updated enrollment data.
				 */
				do_action( 'splms_enrollment_renewed', $enrollment_id, $additional_days, $update_data );
			}
		}

		return false !== $result;
	}

	/**
	 * Get expired enrollments.
	 *
	 * @since 1.0.0
	 *
	 * @param int $limit Optional. Maximum number of results to return.
	 * @return array Array of expired enrollment records.
	 */
	public function get_expired_enrollments( $limit = null ) {
		$query = "SELECT * FROM {$this->table_name} WHERE access_expires IS NOT NULL AND access_expires < %s ORDER BY access_expires ASC";
		$args  = array( current_time( 'mysql' ) );

		if ( $limit ) {
			$query .= ' LIMIT %d';
			$args[] = absint( $limit );
		}

		return $this->get_results( $query, $args );
	}
}
