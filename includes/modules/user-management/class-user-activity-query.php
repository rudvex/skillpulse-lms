<?php
/**
 * User activity query class.
 *
 * Handles user activity logging and retrieval.
 *
 * @since   1.0.0
 * @package SkillPulse_LMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SkillPulse_LMS_Base_Query' ) ) {
	require_once SKILLPULSE_LMS_DIR_PATH . 'includes/modules/core/base/class-base-query.php';
}

/**
 * User activity query class.
 *
 * Handles user activity logging and retrieval.
 *
 * @since 1.0.0
 */
class SkillPulse_LMS_User_Activity_Query extends SkillPulse_LMS_Base_Query {

	/**
	 * Constructor.
	 *
	 * @param string $table_name Table name.
	 *
	 * @since 1.0.0
	 */
	protected function __construct( $table_name ) { // phpcs:ignore Generic.CodeAnalysis.UselessOverridingMethod.Found -- Constructor override needed for singleton pattern.
		parent::__construct( $table_name );
	}

	/**
	 * Get the instance of this class.
	 *
	 * @return SkillPulse_LMS_User_Activity_Query
	 */
	public static function get_instance() {
		return parent::get_base_instance( __CLASS__, 'splms_user_activity' );
	}

	/**
	 * Register hooks
	 */
	public function register_hooks() {
	}


	/**
	 * Log user activity.
	 *
	 * @param int    $user_id       User ID.
	 * @param string $activity_type Activity type.
	 * @param int    $course_id     Course ID (optional).
	 * @param int    $item_id       Item ID (optional).
	 * @param string $item_type     Item type (optional).
	 * @param mixed  $activity_data Additional data (optional).
	 *
	 * @since 1.0.0
	 *
	 * @return int|false Activity ID on success, false on failure.
	 */
	public function log_activity( $user_id, $activity_type, $course_id = null, $item_id = null, $item_type = null, $activity_data = null ) {
		$activity_record = array(
			'user_id'       => $user_id,
			'course_id'     => $course_id,
			'item_id'       => $item_id,
			'item_type'     => $item_type,
			'activity_type' => $activity_type,
			'activity_data' => maybe_serialize( $activity_data ),
			'created_at'    => current_time( 'mysql' ),
		);

		$activity_id = $this->insert(
			$activity_record,
			array( '%d', '%d', '%d', '%s', '%s', '%s', '%s' )
		);

		if ( $activity_id ) {
			do_action( 'splms_activity_logged', $activity_id );
		}

		return $activity_id;
	}

	/**
	 * Get activity by ID.
	 *
	 * @param int $activity_id Activity ID.
	 *
	 * @since 1.0.0
	 *
	 * @return object|null
	 */
	public function get_activity( $activity_id ) {
		$query    = "SELECT * FROM {$this->table_name} WHERE id = %d";
		$activity = $this->get_row( $query, array( $activity_id ) );

		if ( $activity ) {
			$activity->activity_data = maybe_unserialize( $activity->activity_data );
		}

		return $activity;
	}

	/**
	 * Get user's active days count for a specific period.
	 *
	 * @param int    $user_id User ID.
	 * @param string $since   Date string (e.g., '30 days ago').
	 *
	 * @since 1.0.0
	 *
	 * @return int Number of active days.
	 */
	public function get_user_active_days( $user_id, $since = '30 days' ) {
		$since_date = gmdate( 'Y-m-d H:i:s', strtotime( '-' . $since ) );
		$query      = "SELECT COUNT(DISTINCT DATE(created_at)) FROM {$this->table_name} WHERE user_id = %d AND created_at >= %s";

		return (int) $this->get_var( $query, array( $user_id, $since_date ) );
	}

	/**
	 * Get user's activity dates ordered by most recent.
	 *
	 * @param int $user_id User ID.
	 * @param int $limit   Number of dates to return.
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of activity dates.
	 */
	public function get_user_activity_dates( $user_id, $limit = 100 ) {
		$query = "SELECT DISTINCT DATE(created_at) as activity_date FROM {$this->table_name} WHERE user_id = %d ORDER BY activity_date DESC LIMIT %d";

		return $this->get_col( $query, array( $user_id, $limit ) );
	}

	/**
	 * Calculate user's learning streak in days.
	 *
	 * @param int $user_id User ID.
	 *
	 * @since 1.0.0
	 *
	 * @return int Learning streak in days.
	 */
	public function get_user_learning_streak( $user_id ) {
		$activity_dates = $this->get_user_activity_dates( $user_id, 100 );

		$streak       = 0;
		$current_date = gmdate( 'Y-m-d' );

		if ( ! empty( $activity_dates ) ) {
			foreach ( $activity_dates as $activity_date ) {
				if ( $activity_date === $current_date ) {
					++$streak;
					$current_date = gmdate( 'Y-m-d', strtotime( $current_date . ' -1 day' ) );
				} else {
					break;
				}
			}
		}

		return $streak;
	}

	/**
	 * Get user's activity count for a specific date.
	 *
	 * @param int    $user_id User ID.
	 * @param string $date    Date string (Y-m-d format).
	 *
	 * @since 1.0.0
	 *
	 * @return int Number of activities.
	 */
	public function get_user_daily_activity_count( $user_id, $date = null ) {
		if ( null === $date ) {
			$date = gmdate( 'Y-m-d' );
		}

		$query = "SELECT COUNT(*) FROM {$this->table_name} WHERE user_id = %d AND DATE(created_at) = %s";

		return (int) $this->get_var( $query, array( $user_id, $date ) );
	}

	/**
	 * Get recent user activities.
	 *
	 * @param int    $user_id User ID.
	 * @param int    $limit   Number of activities to return.
	 * @param string $since   Date string (e.g., '7 days ago').
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of activity records.
	 */
	public function get_user_recent_activities( $user_id, $limit = 20, $since = '7 days' ) {
		$since_date = gmdate( 'Y-m-d H:i:s', strtotime( '-' . $since ) );
		$query      = "SELECT * FROM {$this->table_name} WHERE user_id = %d AND created_at >= %s ORDER BY created_at DESC LIMIT %d";
		$activities = $this->get_results( $query, array( $user_id, $since_date, $limit ) );

		if ( ! empty( $activities ) ) {
			foreach ( $activities as &$activity ) {
				$activity->activity_data = maybe_unserialize( $activity->activity_data );
			}
		}

		return $activities;
	}
}
