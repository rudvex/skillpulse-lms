<?php
/**
 * Course Enrollment Feature
 *
 * Helper class for enrollment-related functionality.
 * Note: Main enrollment AJAX handlers are in SkillPulse_LMS_Student class.
 *
 * @package SkillPulse_LMS
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enrollment Class
 *
 * Handles course enrollment functionality.
 *
 * @package SkillPulse_LMS
 * @since   1.0.0
 */
class SkillPulse_LMS_Enrollment {

	/**
	 * Class instance.
	 *
	 * @var SkillPulse_LMS_Enrollment|null
	 */
	private static $instance = null;

	/**
	 * Get the instance of this class.
	 *
	 * @return SkillPulse_LMS_Enrollment
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		$this->setup_hooks();
	}

	/**
	 * Setup hooks.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	private function setup_hooks() {
		// Schedule daily enrollment expiration check.
		add_action( 'splms_daily_enrollment_expiration_check', array( $this, 'check_and_expire_enrollments' ) );
		if ( ! wp_next_scheduled( 'splms_daily_enrollment_expiration_check' ) ) {
			wp_schedule_event( time(), 'daily', 'splms_daily_enrollment_expiration_check' );
		}
	}

	/**
	 * Update enrollment progress.
	 *
	 * @param int   $user_id User ID.
	 * @param int   $course_id Course ID.
	 * @param float $progress Progress percentage (0-100).
	 * @return bool True on success, false on failure.
	 */
	public function update_enrollment_progress( $user_id, $course_id, $progress ) {
		$enrollments_query = SkillPulse_LMS_Enrollments_Query::get_instance();

		// If progress is 100%, check if we need to fire completion action.
		if ( $progress >= 100 ) {
			$current_enrollment = $enrollments_query->get_enrollment( $user_id, $course_id );
			if ( $current_enrollment && 'completed' !== $current_enrollment->status ) {
				// Fire completion action before updating.
				do_action( 'splms_course_completed', $course_id, $user_id, $current_enrollment );
			}
		}

		return $enrollments_query->update_enrollment_progress( $user_id, $course_id, $progress );
	}

	/**
	 * Enroll user in course.
	 *
	 * @param int $user_id User ID.
	 * @param int $course_id Course ID.
	 * @return bool True on success, false on failure.
	 */
	public function enroll_user_in_course( $user_id, $course_id ) {
		// Check if already enrolled (active status).
		$enrollments_query   = SkillPulse_LMS_Enrollments_Query::get_instance();
		$existing_enrollment = $enrollments_query->get_enrollment( $user_id, $course_id );
		$was_already_active  = $existing_enrollment && 'active' === $existing_enrollment->status;

		if ( $was_already_active ) {
			// User is already enrolled, return enrollment ID.
			return $existing_enrollment->id;
		}

		// Check if this is a reactivation (existing enrollment with inactive status).
		$is_reactivation = $existing_enrollment && 'inactive' === $existing_enrollment->status;

		// Validate course.
		$course    = get_post( $course_id );
		$post_type = get_post_type( $course_id );
		if ( ! $course || SPLMS_POST_TYPES['course'] !== $course->post_type || 'publish' !== $course->post_status ) {
			return false;
		}

		// Fire pre-enrollment action for validation hooks.
		do_action( 'splms_before_enrollment', $user_id, $course_id, null );

		// Enroll user in database table (this will update if exists, insert if not).
		$enrollment_result = $enrollments_query->enroll_user( $user_id, $course_id );

		if ( false === $enrollment_result ) {
			return false;
		}

		// Log activity only for new enrollments (not reactivations).
		if ( ! $is_reactivation ) {
			SkillPulse_LMS_User_Activity_Query::get_instance()->log_activity( $user_id, 'course_enrolled', $course_id );
		}

		// Fire enrollment action for hooks (both new and reactivated).
		do_action( 'splms_course_enrolled', $user_id, $course_id );

		return $enrollment_result;
	}

	/**
	 * Check if user is enrolled in course.
	 *
	 * @param int $user_id User ID.
	 * @param int $course_id Course ID.
	 * @return bool True if enrolled, false otherwise.
	 */
	public function is_user_enrolled( $user_id, $course_id ) {
		// Check database table.
		$enrollments_query = SkillPulse_LMS_Enrollments_Query::get_instance();
		$db_enrollment     = $enrollments_query->get_enrollment( $user_id, $course_id );

		return $db_enrollment && 'active' === $db_enrollment->status;
	}
	/**
	 * Check if user has completed course.
	 *
	 * @param int $user_id User ID.
	 * @param int $course_id Course ID.
	 * @return bool True if completed, false otherwise.
	 */
	public function has_user_completed_course( $user_id, $course_id ) {
		$enrollments_query = SkillPulse_LMS_Enrollments_Query::get_instance();
		$enrollment        = $enrollments_query->get_enrollment( $user_id, $course_id );
		return $enrollment && 'completed' === $enrollment->status;
	}

	/**
	 * Check and expire enrollments that have passed their expiration date.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function check_and_expire_enrollments() {
		global $wpdb;

		$table_name = esc_sql( $wpdb->prefix . 'splms_enrollments' );
		// phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested -- Need site timezone for expiration comparison.
		$current_time = current_time( 'mysql' );

		// Find all active enrollments that have expired.
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name cannot be prepared, values are prepared.
		$expired_enrollments = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table_name} 
				WHERE status IN ('active', 'in_progress') 
				AND access_expires IS NOT NULL 
				AND access_expires <= %s",
				$current_time
			)
		);
		// phpcs:enable

		if ( empty( $expired_enrollments ) ) {
			return;
		}

		// Get course settings to check if auto-expire is enabled.
		foreach ( $expired_enrollments as $enrollment ) {
			$course_settings = splms_get_course_settings( $enrollment->course_id );

			// Check if auto-expire is enabled in scheduling settings.
			$scheduling_settings = isset( $course_settings['course_scheduling_settings'] ) ? $course_settings['course_scheduling_settings'] : array();
			$auto_expire         = isset( $scheduling_settings['auto_expire_enrollments'] ) ? filter_var( $scheduling_settings['auto_expire_enrollments'], FILTER_VALIDATE_BOOLEAN ) : true;

			if ( $auto_expire ) {
				// Update enrollment status to expired.
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table name is safe, validated constant.
				$wpdb->update(
					$table_name,
					array( 'status' => 'expired' ),
					array( 'id' => $enrollment->id ),
					array( '%s' ),
					array( '%d' )
				);

				/**
				 * Fires when an enrollment expires.
				 *
				 * @since 1.0.0
				 *
				 * @param object $enrollment Enrollment object.
				 */
				do_action( 'splms_enrollment_expired', $enrollment );
			}
		}
	}
}
