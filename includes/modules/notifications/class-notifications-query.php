<?php
/**
 * Notifications Query Class
 *
 * Handles database operations for in-app notifications.
 *
 * @package SkillPulse_LMS
 * @subpackage Notifications
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

if ( ! class_exists( 'SkillPulse_LMS_Base_Query' ) ) {
	require_once SKILLPULSE_LMS_DIR_PATH . 'includes/modules/core/base/class-base-query.php';
}

/**
 * Class SkillPulse_LMS_Notifications_Query
 *
 * @since 1.0.0
 */
class SkillPulse_LMS_Notifications_Query extends SkillPulse_LMS_Base_Query {

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
	 * @return SkillPulse_LMS_Notifications_Query The class instance.
	 */
	public static function get_instance() {
		return parent::get_base_instance( __CLASS__, 'splms_notifications' );
	}

	/**
	 * Create a new notification.
	 *
	 * @since 1.0.0
	 *
	 * @param array $data Notification data.
	 * @return int|false Notification ID on success, false on failure.
	 */
	public function create_notification( $data ) {
		// Get default notification type from settings.
		$default_type = function_exists( 'splms_get_in_app_notification_setting' )
			? splms_get_in_app_notification_setting( 'default_notification_type', 'info' )
			: 'info';

		$defaults = array(
			'user_id'      => 0,
			'event_key'    => '',
			'title'        => '',
			'message'      => '',
			'type'         => $default_type,
			'is_read'      => 0,
			'read_at'      => null,
			'course_id'    => null,
			'related_id'   => null,
			'related_type' => null,
			'meta'         => null,
			'created_at'   => current_time( 'mysql' ),
		);

		$data = wp_parse_args( $data, $defaults );

		// Serialize meta if it's an array.
		if ( is_array( $data['meta'] ) ) {
			$data['meta'] = wp_json_encode( $data['meta'] );
		}

		return $this->insert(
			$data,
			array( '%d', '%s', '%s', '%s', '%s', '%d', '%s', '%d', '%d', '%s', '%s', '%s' )
		);
	}

	/**
	 * Get notifications with filters.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args Query arguments.
	 * @return array Array of notification objects.
	 */
	public function get_notifications( $args = array() ) {
		global $wpdb;

		$defaults = array(
			'user_id'      => null,
			'event_key'    => null,
			'is_read'      => null,
			'course_id'    => null,
			'related_id'   => null,
			'related_type' => null,
			'type'         => null,
			'limit'        => 50,
			'offset'       => 0,
			'orderby'      => 'created_at',
			'order'        => 'DESC',
		);

		$args = wp_parse_args( $args, $defaults );

		$where        = array( '1=1' );
		$where_values = array();

		if ( ! empty( $args['user_id'] ) ) {
			$where[]        = 'user_id = %d';
			$where_values[] = $args['user_id'];
		}

		if ( ! empty( $args['event_key'] ) ) {
			$where[]        = 'event_key = %s';
			$where_values[] = $args['event_key'];
		}

		if ( isset( $args['is_read'] ) && null !== $args['is_read'] ) {
			$where[]        = 'is_read = %d';
			$where_values[] = $args['is_read'] ? 1 : 0;
		}

		if ( ! empty( $args['course_id'] ) ) {
			$where[]        = 'course_id = %d';
			$where_values[] = $args['course_id'];
		}

		if ( ! empty( $args['related_id'] ) ) {
			$where[]        = 'related_id = %d';
			$where_values[] = $args['related_id'];
		}

		if ( ! empty( $args['related_type'] ) ) {
			$where[]        = 'related_type = %s';
			$where_values[] = $args['related_type'];
		}

		if ( ! empty( $args['type'] ) ) {
			$where[]        = 'type = %s';
			$where_values[] = $args['type'];
		}

		$where_clause = implode( ' AND ', $where );

		// Validate orderby.
		$allowed_orderby = array( 'id', 'created_at', 'read_at', 'user_id', 'event_key' );
		$orderby         = in_array( $args['orderby'], $allowed_orderby, true ) ? $args['orderby'] : 'created_at';

		// Validate order.
		$order = strtoupper( $args['order'] ) === 'ASC' ? 'ASC' : 'DESC';

		$limit  = absint( $args['limit'] );
		$offset = absint( $args['offset'] );

		$query          = "SELECT * FROM {$this->table_name} WHERE {$where_clause} ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d";
		$where_values[] = $limit;
		$where_values[] = $offset;

		return $this->get_results( $query, $where_values );
	}

	/**
	 * Get single notification by ID.
	 *
	 * @since 1.0.0
	 *
	 * @param int $id Notification ID.
	 * @return object|null Notification object or null if not found.
	 */
	public function get_notification( $id ) {
		$query = "SELECT * FROM {$this->table_name} WHERE id = %d";
		return $this->get_row( $query, array( $id ) );
	}

	/**
	 * Update notification.
	 *
	 * @since 1.0.0
	 *
	 * @param int   $id   Notification ID.
	 * @param array $data Data to update.
	 * @return bool True on success, false on failure.
	 */
	public function update_notification( $id, $data ) {
		// Serialize meta if it's an array.
		if ( isset( $data['meta'] ) && is_array( $data['meta'] ) ) {
			$data['meta'] = wp_json_encode( $data['meta'] );
		}

		$format = array();
		foreach ( $data as $key => $value ) {
			if ( in_array( $key, array( 'user_id', 'course_id', 'related_id', 'is_read' ), true ) ) {
				$format[] = '%d';
			} elseif ( in_array( $key, array( 'read_at', 'created_at' ), true ) ) {
				$format[] = '%s';
			} else {
				$format[] = '%s';
			}
		}

		return $this->update(
			$data,
			array( 'id' => $id ),
			$format,
			array( '%d' )
		);
	}

	/**
	 * Mark notification as read.
	 *
	 * @since 1.0.0
	 *
	 * @param int $id      Notification ID.
	 * @param int $user_id User ID (for verification).
	 * @return bool True on success, false on failure.
	 */
	public function mark_read( $id, $user_id = null ) {
		$data = array(
			'is_read' => 1,
			'read_at' => current_time( 'mysql' ),
		);

		$where        = array( 'id' => $id );
		$where_format = array( '%d' );

		if ( $user_id ) {
			$where['user_id'] = $user_id;
			$where_format[]   = '%d';
		}

		return $this->update(
			$data,
			$where,
			array( '%d', '%s' ),
			$where_format
		);
	}

	/**
	 * Mark all user notifications as read.
	 *
	 * @since 1.0.0
	 *
	 * @param int $user_id User ID.
	 * @return int|false Number of rows updated or false on failure.
	 */
	public function mark_all_read( $user_id ) {
		global $wpdb;

		$query = $wpdb->prepare(
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table name is safe, values are prepared.
			"UPDATE {$this->table_name} SET is_read = 1, read_at = %s WHERE user_id = %d AND is_read = 0",
			current_time( 'mysql' ),
			$user_id
		);

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Query is prepared above.
		return $wpdb->query( $query );
	}

	/**
	 * Delete notification.
	 *
	 * @since 1.0.0
	 *
	 * @param int $id      Notification ID.
	 * @param int $user_id User ID (for verification, optional).
	 * @return bool True on success, false on failure.
	 */
	public function delete_notification( $id, $user_id = null ) {
		$where        = array( 'id' => $id );
		$where_format = array( '%d' );

		if ( $user_id ) {
			$where['user_id'] = $user_id;
			$where_format[]   = '%d';
		}

		return $this->delete( $where, $where_format );
	}

	/**
	 * Delete old notifications.
	 *
	 * @since 1.0.0
	 *
	 * @param int $days Number of days old to delete.
	 * @return int|false Number of rows deleted or false on failure.
	 */
	public function delete_old_notifications( $days = 90 ) {
		global $wpdb;

		// Use current_time to match the timezone used when creating notifications.
		// phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested -- Need site timezone for deletion comparison.
		$date = gmdate( 'Y-m-d H:i:s', strtotime( "-{$days} days", current_time( 'timestamp' ) ) );

		$query = $wpdb->prepare(
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table name is safe, values are prepared.
			"DELETE FROM {$this->table_name} WHERE created_at < %s",
			$date
		);

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Query is prepared above.
		return $wpdb->query( $query );
	}

	/**
	 * Get notification statistics.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args Query arguments.
	 * @return array Statistics array.
	 */
	public function get_stats( $args = array() ) {
		global $wpdb;

		$defaults = array(
			'user_id' => null,
		);

		$args = wp_parse_args( $args, $defaults );

		$where        = array( '1=1' );
		$where_values = array();

		if ( ! empty( $args['user_id'] ) ) {
			$where[]        = 'user_id = %d';
			$where_values[] = $args['user_id'];
		}

		$where_clause = implode( ' AND ', $where );

		$stats = array();

		// Total notifications.
		$query          = "SELECT COUNT(*) as total FROM {$this->table_name} WHERE {$where_clause}";
		$result         = $this->get_row( $query, $where_values );
		$stats['total'] = $result ? (int) $result->total : 0;

		// Unread notifications.
		$query           = "SELECT COUNT(*) as unread FROM {$this->table_name} WHERE {$where_clause} AND is_read = 0";
		$result          = $this->get_row( $query, $where_values );
		$stats['unread'] = $result ? (int) $result->unread : 0;

		// Read notifications.
		$stats['read'] = $stats['total'] - $stats['unread'];

		// Notifications by event.
		$query             = "SELECT event_key, COUNT(*) as count FROM {$this->table_name} WHERE {$where_clause} GROUP BY event_key";
		$results           = $this->get_results( $query, $where_values );
		$stats['by_event'] = array();
		foreach ( $results as $result ) {
			$stats['by_event'][ $result->event_key ] = (int) $result->count;
		}

		return $stats;
	}

	/**
	 * Get user notifications.
	 *
	 * @since 1.0.0
	 *
	 * @param int   $user_id User ID.
	 * @param array $args    Additional query arguments.
	 * @return array Array of notification objects.
	 */
	public function get_user_notifications( $user_id, $args = array() ) {
		$args['user_id'] = $user_id;
		return $this->get_notifications( $args );
	}
}
