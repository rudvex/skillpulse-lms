<?php
/**
 * In-App Notifications Class
 *
 * Handles in-app notification storage, retrieval, and AJAX operations.
 *
 * @package SkillPulse_LMS
 * @subpackage Notifications
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SkillPulse_LMS_Notifications_Query' ) ) {
	require_once SKILLPULSE_LMS_DIR_PATH . 'includes/modules/notifications/class-notifications-query.php';
}

/**
 * In-App Notifications Class
 *
 * Handles user notifications for in-app display.
 *
 * @package SkillPulse_LMS
 * @since   1.0.0
 */
class SkillPulse_LMS_In_App_Notifications {

	/**
	 * Class instance.
	 *
	 * @var SkillPulse_LMS_In_App_Notifications|null
	 */
	private static $instance = null;

	/**
	 * Notifications query instance.
	 *
	 * @var SkillPulse_LMS_Notifications_Query|null
	 */
	private $query = null;

	/**
	 * Get the instance of this class.
	 *
	 * @return SkillPulse_LMS_In_App_Notifications
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
		$this->query = SkillPulse_LMS_Notifications_Query::get_instance();
		$this->setup_hooks();
	}

	/**
	 * Setup hooks.
	 */
	private function setup_hooks() {
		// AJAX handlers for both admin and frontend users.
		add_action( 'wp_ajax_splms_mark_notification_read', array( $this, 'mark_notification_read' ) );
		add_action( 'wp_ajax_nopriv_splms_mark_notification_read', array( $this, 'mark_notification_read' ) );

		add_action( 'wp_ajax_splms_get_notifications', array( $this, 'get_notifications' ) );
		add_action( 'wp_ajax_nopriv_splms_get_notifications', array( $this, 'get_notifications' ) );

		add_action( 'wp_ajax_splms_mark_all_notifications_read', array( $this, 'mark_all_notifications_read' ) );
		add_action( 'wp_ajax_nopriv_splms_mark_all_notifications_read', array( $this, 'mark_all_notifications_read' ) );

		add_action( 'wp_ajax_splms_mark_notification_unread', array( $this, 'mark_notification_unread' ) );
		add_action( 'wp_ajax_nopriv_splms_mark_notification_unread', array( $this, 'mark_notification_unread' ) );

		add_action( 'wp_ajax_splms_delete_notification', array( $this, 'delete_notification' ) );
		add_action( 'wp_ajax_nopriv_splms_delete_notification', array( $this, 'delete_notification' ) );

		// Schedule cron for auto-delete old notifications.
		add_action( 'wp_loaded', array( $this, 'schedule_auto_delete_cron' ) );
		add_action( 'splms_auto_delete_old_notifications', array( $this, 'process_auto_delete' ) );
	}

	/**
	 * Create a notification.
	 *
	 * @param int    $user_id User ID.
	 * @param string $title Notification title.
	 * @param string $message Notification message.
	 * @param string $type Notification type (default: from settings).
	 * @param int    $course_id Course ID (optional).
	 * @param string $event_key Event key (optional).
	 * @param int    $related_id Related entity ID (optional).
	 * @param string $related_type Related entity type (optional).
	 * @return int|false Notification ID on success, false on failure.
	 */
	public function create_notification( $user_id, $title, $message, $type = null, $course_id = null, $event_key = '', $related_id = null, $related_type = null ) {
		// Check if in-app notifications are enabled globally.
		// This check ensures notifications are not created even if called directly (bypassing dispatcher).
		if ( ! function_exists( 'splms_is_in_app_notifications_enabled' ) || ! splms_is_in_app_notifications_enabled() ) {
			// Return false instead of creating notification when disabled.
			return false;
		}

		// Get default notification type from settings if not provided.
		if ( null === $type ) {
			$type = function_exists( 'splms_get_in_app_notification_setting' )
				? splms_get_in_app_notification_setting( 'default_notification_type', 'info' )
				: 'info';
		}

		// Check max notifications per user and delete oldest if limit reached.
		$max_notifications = function_exists( 'splms_get_in_app_notification_setting' )
			? splms_get_in_app_notification_setting( 'max_notifications_per_user', 100 )
			: 100;
		$max_notifications = absint( $max_notifications );

		if ( $max_notifications > 0 ) {
			// Get current notification count for this user.
			$user_notifications = $this->query->get_notifications(
				array(
					'user_id' => absint( $user_id ),
					'limit'   => 999999, // Get all to count.
				)
			);

			$current_count = count( $user_notifications );

			// If at or over limit, delete oldest notifications.
			if ( $current_count >= $max_notifications ) {
				$notifications_to_delete = $current_count - $max_notifications + 1; // +1 to make room for new notification.

				// Get oldest notifications (sorted by created_at ASC).
				$oldest_notifications = $this->query->get_notifications(
					array(
						'user_id' => absint( $user_id ),
						'limit'   => $notifications_to_delete,
						'orderby' => 'created_at',
						'order'   => 'ASC', // Oldest first.
					)
				);

				// Delete oldest notifications.
				foreach ( $oldest_notifications as $old_notification ) {
					$this->query->delete_notification( $old_notification->id, $user_id );
				}
			}
		}

		$notification_data = array(
			'user_id'      => absint( $user_id ),
			'event_key'    => sanitize_text_field( $event_key ),
			'title'        => sanitize_text_field( $title ),
			'message'      => wp_kses_post( $message ),
			'type'         => sanitize_text_field( $type ),
			'is_read'      => 0,
			'read_at'      => null,
			'course_id'    => $course_id ? absint( $course_id ) : null,
			'related_id'   => $related_id ? absint( $related_id ) : null,
			'related_type' => $related_type ? sanitize_text_field( $related_type ) : null,
			'meta'         => null,
		);

		$notification_id = $this->query->create_notification( $notification_data );

		/**
		 * Fires after a notification is created.
		 *
		 * @since 1.0.0
		 *
		 * @param int   $notification_id Notification ID.
		 * @param array  $notification_data Notification data.
		 */
		do_action( 'splms_notification_created', $notification_id, $notification_data );

		return $notification_id;
	}

	/**
	 * Mark notification as read via AJAX.
	 */
	public function mark_notification_read() {
		// Verify nonce.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verification handled by wp_verify_nonce.
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'splms_frontend_nonce' ) ) {
			wp_send_json_error( esc_html__( 'Security check failed.', 'skillpulse-lms' ) );
		}

		$user_id = get_current_user_id();
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce already verified above.
		$notification_id = isset( $_POST['notification_id'] ) ? absint( $_POST['notification_id'] ) : 0;

		if ( ! $user_id ) {
			wp_send_json_error( esc_html__( 'User not logged in.', 'skillpulse-lms' ) );
		}

		if ( ! $notification_id ) {
			wp_send_json_error( esc_html__( 'Notification ID required.', 'skillpulse-lms' ) );
		}

		// Verify notification belongs to user.
		$notification = $this->query->get_notification( $notification_id );
		if ( ! $notification || (int) $notification->user_id !== $user_id ) {
			wp_send_json_error( esc_html__( 'Notification not found.', 'skillpulse-lms' ) );
		}

		$result = $this->query->mark_read( $notification_id, $user_id );

		if ( $result ) {
			wp_send_json_success( esc_html__( 'Notification marked as read.', 'skillpulse-lms' ) );
		} else {
			wp_send_json_error( esc_html__( 'Failed to update notification.', 'skillpulse-lms' ) );
		}
	}

	/**
	 * AJAX handler to get user notifications.
	 */
	public function get_notifications() {
		// Verify nonce.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verification handled by wp_verify_nonce.
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'splms_frontend_nonce' ) ) {
			wp_send_json_error( esc_html__( 'Security check failed.', 'skillpulse-lms' ) );
		}

		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			wp_send_json_error( esc_html__( 'User not logged in.', 'skillpulse-lms' ) );
		}

		// Get query parameters.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce already verified above.
		$limit  = isset( $_POST['limit'] ) ? absint( $_POST['limit'] ) : 50;
		$offset = isset( $_POST['offset'] ) ? absint( $_POST['offset'] ) : 0;
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce already verified above.
		$is_read = isset( $_POST['is_read'] ) ? sanitize_text_field( wp_unslash( $_POST['is_read'] ) ) : null;
		if ( null !== $is_read ) {
			$is_read = 'true' === $is_read || '1' === $is_read;
		}

		$args = array(
			'user_id' => $user_id,
			'limit'   => $limit,
			'offset'  => $offset,
			'orderby' => 'created_at',
			'order'   => 'DESC',
		);

		if ( null !== $is_read ) {
			$args['is_read'] = $is_read;
		}

		$notifications = $this->query->get_notifications( $args );

		// Convert objects to arrays for JSON response.
		$notifications_array = array();
		foreach ( $notifications as $notification ) {
			$notifications_array[] = array(
				'id'           => (string) $notification->id,
				'user_id'      => (int) $notification->user_id,
				'event_key'    => $notification->event_key,
				'title'        => $notification->title,
				'message'      => $notification->message,
				'type'         => $notification->type,
				'is_read'      => (bool) $notification->is_read,
				'read_at'      => $notification->read_at,
				'course_id'    => $notification->course_id ? (int) $notification->course_id : null,
				'related_id'   => $notification->related_id ? (int) $notification->related_id : null,
				'related_type' => $notification->related_type,
				'meta'         => $notification->meta ? json_decode( $notification->meta, true ) : null,
				'created_at'   => $notification->created_at,
			);
		}

		// Get unread count.
		$unread_args          = array(
			'user_id' => $user_id,
			'is_read' => false,
		);
		$unread_notifications = $this->query->get_notifications( $unread_args );
		$unread_count         = count( $unread_notifications );

		wp_send_json_success(
			array(
				'notifications' => $notifications_array,
				'unread_count'  => $unread_count,
			)
		);
	}

	/**
	 * AJAX handler to mark all notifications as read.
	 */
	public function mark_all_notifications_read() {
		// Verify nonce.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verification handled by wp_verify_nonce.
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'splms_frontend_nonce' ) ) {
			wp_send_json_error( esc_html__( 'Security check failed.', 'skillpulse-lms' ) );
		}

		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			wp_send_json_error( esc_html__( 'User not logged in.', 'skillpulse-lms' ) );
		}

		$result = $this->query->mark_all_read( $user_id );

		if ( false !== $result ) {
			wp_send_json_success( esc_html__( 'All notifications marked as read.', 'skillpulse-lms' ) );
		} else {
			wp_send_json_error( esc_html__( 'Failed to update notifications.', 'skillpulse-lms' ) );
		}
	}

	/**
	 * AJAX handler to mark notification as unread.
	 */
	public function mark_notification_unread() {
		// Verify nonce.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verification handled by wp_verify_nonce.
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'splms_frontend_nonce' ) ) {
			wp_send_json_error( esc_html__( 'Security check failed.', 'skillpulse-lms' ) );
		}

		$user_id = get_current_user_id();
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce already verified above.
		$notification_id = isset( $_POST['notification_id'] ) ? absint( $_POST['notification_id'] ) : 0;

		if ( ! $user_id ) {
			wp_send_json_error( esc_html__( 'User not logged in.', 'skillpulse-lms' ) );
		}

		if ( ! $notification_id ) {
			wp_send_json_error( esc_html__( 'Notification ID required.', 'skillpulse-lms' ) );
		}

		// Verify notification belongs to user.
		$notification = $this->query->get_notification( $notification_id );
		if ( ! $notification || (int) $notification->user_id !== $user_id ) {
			wp_send_json_error( esc_html__( 'Notification not found.', 'skillpulse-lms' ) );
		}

		// Mark as unread by setting is_read to 0 and read_at to null.
		$result = $this->query->update_notification(
			$notification_id,
			array(
				'is_read' => 0,
				'read_at' => null,
			)
		);

		if ( $result ) {
			wp_send_json_success( esc_html__( 'Notification marked as unread.', 'skillpulse-lms' ) );
		} else {
			wp_send_json_error( esc_html__( 'Failed to update notification.', 'skillpulse-lms' ) );
		}
	}

	/**
	 * AJAX handler to delete notification.
	 */
	public function delete_notification() {
		// Verify nonce.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verification handled by wp_verify_nonce.
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'splms_frontend_nonce' ) ) {
			wp_send_json_error( esc_html__( 'Security check failed.', 'skillpulse-lms' ) );
		}

		$user_id = get_current_user_id();
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce already verified above.
		$notification_id = isset( $_POST['notification_id'] ) ? absint( $_POST['notification_id'] ) : 0;

		if ( ! $user_id ) {
			wp_send_json_error( esc_html__( 'User not logged in.', 'skillpulse-lms' ) );
		}

		if ( ! $notification_id ) {
			wp_send_json_error( esc_html__( 'Notification ID required.', 'skillpulse-lms' ) );
		}

		// Verify notification belongs to user.
		$notification = $this->query->get_notification( $notification_id );
		if ( ! $notification || (int) $notification->user_id !== $user_id ) {
			wp_send_json_error( esc_html__( 'Notification not found.', 'skillpulse-lms' ) );
		}

		$result = $this->query->delete_notification( $notification_id, $user_id );

		if ( $result ) {
			wp_send_json_success( esc_html__( 'Notification deleted.', 'skillpulse-lms' ) );
		} else {
			wp_send_json_error( esc_html__( 'Failed to delete notification.', 'skillpulse-lms' ) );
		}
	}

	/**
	 * Schedule auto-delete cron job.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function schedule_auto_delete_cron() {
		// Check if auto-delete is enabled.
		$auto_delete_enabled = function_exists( 'splms_get_in_app_notification_setting' )
			? splms_get_in_app_notification_setting( 'auto_delete_enabled', false )
			: false;
		$auto_delete_enabled = ( '1' === $auto_delete_enabled || true === $auto_delete_enabled );

		if ( ! $auto_delete_enabled ) {
			// If disabled, unschedule the cron.
			$this->unschedule_auto_delete_cron();
			return;
		}

		// Schedule daily cron if not already scheduled.
		if ( ! wp_next_scheduled( 'splms_auto_delete_old_notifications' ) ) {
			wp_schedule_event( time(), 'daily', 'splms_auto_delete_old_notifications' );
		}
	}

	/**
	 * Unschedule auto-delete cron job.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function unschedule_auto_delete_cron() {
		$timestamp = wp_next_scheduled( 'splms_auto_delete_old_notifications' );
		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, 'splms_auto_delete_old_notifications' );
		}
	}

	/**
	 * Process auto-delete of old notifications.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function process_auto_delete() {
		// Check if auto-delete is enabled.
		$auto_delete_enabled = function_exists( 'splms_get_in_app_notification_setting' )
			? splms_get_in_app_notification_setting( 'auto_delete_enabled', false )
			: false;
		$auto_delete_enabled = ( '1' === $auto_delete_enabled || true === $auto_delete_enabled );

		if ( ! $auto_delete_enabled ) {
			return;
		}

		// Get days setting.
		$days = function_exists( 'splms_get_in_app_notification_setting' )
			? splms_get_in_app_notification_setting( 'auto_delete_days', 90 )
			: 90;
		$days = absint( $days );

		if ( $days <= 0 ) {
			return;
		}

		// Delete old notifications.
		$deleted_count = $this->query->delete_old_notifications( $days );

		/**
		 * Fires after auto-delete process runs.
		 *
		 * @since 1.0.0
		 *
		 * @param int $deleted_count Number of notifications deleted.
		 * @param int $days          Number of days threshold used.
		 */
		do_action( 'splms_auto_delete_notifications_completed', $deleted_count, $days );
	}
}
