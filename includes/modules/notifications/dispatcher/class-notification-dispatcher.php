<?php
/**
 * Notification Dispatcher Class
 *
 * Handles unified notification dispatching (email and in-app).
 *
 * @package SkillPulse_LMS
 * @subpackage Notifications
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * SkillPulse LMS Notification Dispatcher Class
 *
 * @since 1.0.0
 */
class SPLMS_Notification_Dispatcher {

	/**
	 * Class instance.
	 *
	 * @var SPLMS_Notification_Dispatcher|null $instance
	 */
	private static $instance = null;

	/**
	 * Get the instance of this class.
	 *
	 * @since 1.0.0
	 *
	 * @return SPLMS_Notification_Dispatcher The class instance.
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			$class_name     = __CLASS__;
			self::$instance = new $class_name();
			self::$instance->setup_actions();
		}

		return self::$instance;
	}

	/**
	 * Setup action hooks.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	private function setup_actions() {
		// Schedule email notifications.
		add_action( 'splms_schedule_email_notification', array( $this, 'send_scheduled_notification' ), 10, 3 );

		// Hook into WordPress cron for scheduled emails.
		add_action( 'wp_loaded', array( $this, 'schedule_cron_events' ) );
		add_action( 'splms_email_cron', array( $this, 'process_scheduled_emails' ) );
	}

	/**
	 * Schedule cron events.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function schedule_cron_events() {
		if ( ! wp_next_scheduled( 'splms_email_cron' ) ) {
			wp_schedule_event( time(), 'hourly', 'splms_email_cron' );
		}
	}

	/**
	 * Send a scheduled notification.
	 *
	 * @since 1.0.0
	 *
	 * @param array $notification_data Notification data.
	 * @return void
	 */
	public function send_scheduled_notification( $notification_data ) {
		$email_templates = SPLMS_Email_Templates::get_instance();
		$template        = $email_templates->get_template( $notification_data['template_key'] );

		if ( empty( $template ) || ! $template['is_active'] ) {
			return;
		}

		$email_sender = SPLMS_Email_Sender::get_instance();
		$result       = $email_sender->send_email( $notification_data['to_email'], $template, $notification_data['replacements'] );

		// Log the result.
		$this->log_notification_result( $notification_data, $result );
	}

	/**
	 * Process scheduled emails.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function process_scheduled_emails() {
		$settings    = SPLMS_Settings::get_instance()->get_all_settings();
		$batch_limit = isset( $settings['notifications']['email_settings']['batch_email_limit'] )
			? absint( $settings['notifications']['email_settings']['batch_email_limit'] )
			: 50;
		$batch_limit = intval( $batch_limit );

		// Get scheduled notifications.
		$scheduled_notifications = $this->get_scheduled_notifications();

		if ( empty( $scheduled_notifications ) ) {
			return;
		}

		// Process notifications in batches.
		$batch = array_slice( $scheduled_notifications, 0, $batch_limit, true );

		foreach ( $batch as $notification_id => $notification_data ) {
			// Check if it's time to send this notification.
			if ( isset( $notification_data['scheduled_at'] ) && $notification_data['scheduled_at'] <= time() ) {
				$this->send_scheduled_notification( $notification_data );

				// Remove from scheduled notifications.
				$this->cancel_scheduled_notification( $notification_id );
			}
		}

		/**
		 * Fires when processing scheduled emails.
		 *
		 * @param array $scheduled_emails Array of scheduled email data.
		 */
		do_action( 'splms_process_scheduled_emails' );
	}

	/**
	 * Send notification using configured methods.
	 *
	 * @since 1.0.0
	 *
	 * @param string $to_email     Recipient email address.
	 * @param string $template_key Template key.
	 * @param array  $replacements Placeholder replacements.
	 * @param int    $user_id      Optional user ID for in-app notifications.
	 * @return array Results for each notification method.
	 */
	public function send_notification( $to_email, $template_key, $replacements = array(), $user_id = null ) {
		$results = array();

		// Map template key to event key for preferences.
		$event_key = $this->get_event_key_from_template( $template_key );

		// Check user preferences if user_id is provided.
		$prefs_instance = SPLMS_Notification_Preferences::get_instance();
		$send_email     = true;
		$send_in_app    = true; // Default to true, will be checked against user preferences.

		if ( $user_id ) {
			$send_email  = $prefs_instance->user_wants_email( $user_id, $event_key );
			$send_in_app = $prefs_instance->user_wants_in_app( $user_id, $event_key );
		}

		// Send email notification if user wants it.
		if ( $send_email ) {
			// Check if attachments are provided in replacements.
			$attachments = isset( $replacements['_attachments'] ) ? $replacements['_attachments'] : array();
			// Remove _attachments from replacements (it's not a placeholder).
			unset( $replacements['_attachments'] );

			$results['email'] = $this->send_email_notification( $to_email, $template_key, $replacements, $attachments );
		} else {
			$results['email'] = new WP_Error( 'preference_disabled', __( 'Email notification disabled by user preference.', 'skillpulse-lms' ) );
		}

		// Send in-app notification if user wants it and user_id is provided.
		if ( $send_in_app && $user_id ) {
			$results['in_app'] = $this->send_in_app_notification( $user_id, $template_key, $replacements );
		} elseif ( $user_id ) {
			$results['in_app'] = new WP_Error( 'preference_disabled', __( 'In-app notification disabled by user preference.', 'skillpulse-lms' ) );
		}

		return $results;
	}

	/**
	 * Map template key to event key for preferences and in-app templates.
	 *
	 * @since 1.0.0
	 *
	 * @param string $template_key Template key.
	 * @return string Event key.
	 */
	private function get_event_key_from_template( $template_key ) {
		// Map template keys to event keys.
		$mapping = array(
			'course_enrollment'        => 'course_enrollment',
			'course_completion'        => 'course_completion',
			'enrollment_reminder'      => 'enrollment_reminder',
			'lesson_completion'        => 'lesson_completion',
			'quiz_completion'          => 'quiz_completion',
			'quiz_passed'              => 'quiz_passed',
			'quiz_failed'              => 'quiz_failed',
			'certificate_email'        => 'certificate_generated',
			'certificate_awarded'      => 'certificate_awarded',
			'activation_email'         => 'signup_created',
			'welcome_email'            => 'signup_activated',
			'review_reply'             => 'review_reply',
			'review_new'               => 'review_new',
			'review_moderation'        => 'review_moderation',
			'review_moderation_result' => 'review_moderation_result',
			'order_completed'          => 'order_completed',
			'order_refunded'           => 'order_refunded',
			'order_cancelled'          => 'order_cancelled',
		);

		return isset( $mapping[ $template_key ] ) ? $mapping[ $template_key ] : $template_key;
	}

	/**
	 * Send email notification.
	 *
	 * @since 1.0.0
	 *
	 * @param string $to_email     Recipient email address.
	 * @param string $template_key Template key.
	 * @param array  $replacements Placeholder replacements.
	 * @param array  $attachments  Optional. Email attachments.
	 * @return bool|WP_Error True on success, WP_Error on failure.
	 */
	private function send_email_notification( $to_email, $template_key, $replacements, $attachments = array() ) {
		// Check if email notifications are enabled globally.
		if ( ! function_exists( 'splms_is_email_notifications_enabled' ) || ! splms_is_email_notifications_enabled() ) {
			return new WP_Error( 'emails_disabled', __( 'Email notifications are disabled globally.', 'skillpulse-lms' ) );
		}

		$email_templates = SPLMS_Email_Templates::get_instance();
		$template        = $email_templates->get_template( $template_key );

		if ( empty( $template ) || ! $template['is_active'] ) {
			return new WP_Error( 'template_not_found', __( 'Email template not found or inactive.', 'skillpulse-lms' ) );
		}

		$email_sender = SPLMS_Email_Sender::get_instance();
		return $email_sender->send_email( $to_email, $template, $replacements, $attachments );
	}

	/**
	 * Send in-app notification.
	 *
	 * @since 1.0.0
	 *
	 * @param int    $user_id      User ID.
	 * @param string $template_key Template key.
	 * @param array  $replacements Placeholder replacements.
	 * @return bool|WP_Error True on success, WP_Error on failure.
	 */
	private function send_in_app_notification( $user_id, $template_key, $replacements ) {
		if ( ! $user_id ) {
			return new WP_Error( 'no_user_id', __( 'User ID required for in-app notifications.', 'skillpulse-lms' ) );
		}

		// Check if in-app notifications are enabled globally.
		$in_app_enabled = function_exists( 'splms_is_in_app_notifications_enabled' ) && splms_is_in_app_notifications_enabled();
		$in_app_enabled = ( '1' === $in_app_enabled || true === $in_app_enabled );

		if ( ! $in_app_enabled ) {
			return new WP_Error( 'notifications_disabled', __( 'In-app notifications are disabled.', 'skillpulse-lms' ) );
		}

		// Map template key to event key for in-app templates.
		$event_key = $this->get_event_key_from_template( $template_key );

		// Ensure common placeholders are available.
		if ( ! isset( $replacements['site_name'] ) ) {
			$replacements['site_name'] = get_bloginfo( 'name' );
		}

		// Get in-app notification template.
		$in_app_templates = SPLMS_In_App_Templates::get_instance();
		$template         = $in_app_templates->get_template_with_replacements( $event_key, $replacements );

		// If in-app template not found or disabled, fallback to email template.
		if ( ! $template ) {
			// Fallback to email template for backward compatibility.
			$email_templates = SPLMS_Email_Templates::get_instance();
			$email_template  = $email_templates->get_template( $template_key );

			if ( empty( $email_template ) ) {
				return new WP_Error( 'template_not_found', __( 'Template not found.', 'skillpulse-lms' ) );
			}

			// Get default notification type from settings.
			$default_type = function_exists( 'splms_get_in_app_notification_setting' )
				? splms_get_in_app_notification_setting( 'default_notification_type', 'info' )
				: 'info';

			// Extract plain text from email template (strip HTML for in-app display).
			$email_subject = $email_template['subject'] ?? '';
			$email_content = $email_template['content'] ?? '';

			// Strip HTML tags and decode HTML entities for in-app notification.
			$plain_title   = wp_strip_all_tags( $email_subject );
			$plain_message = wp_strip_all_tags( $email_content );

			// Remove excessive whitespace and newlines.
			$plain_message = preg_replace( '/\s+/', ' ', $plain_message );
			$plain_message = trim( $plain_message );

			// Limit message length for in-app notifications (max 500 characters).
			if ( strlen( $plain_message ) > 500 ) {
				$plain_message = substr( $plain_message, 0, 497 ) . '...';
			}

			// Use email template as fallback (with HTML stripped).
			$template = array(
				'title'   => $plain_title,
				'message' => $plain_message,
				'type'    => $default_type,
			);

			// Replace placeholders in fallback template.
			$template['title']   = $this->replace_placeholders( $template['title'], $replacements );
			$template['message'] = $this->replace_placeholders( $template['message'], $replacements );
		}

		// Use the unified in-app notification class to create notifications.
		$in_app_notifications = SPLMS_In_App_Notifications::get_instance();

		// Extract course_id from replacements if available.
		$course_id    = null;
		$related_id   = null;
		$related_type = null;

		if ( isset( $replacements['course_id'] ) ) {
			$course_id = absint( $replacements['course_id'] );
		} elseif ( isset( $replacements['course_url'] ) ) {
			// Try to extract course ID from course URL.
			$course_url = $replacements['course_url'];
			$post_id    = url_to_postid( $course_url );
			if ( $post_id ) {
				$course_id = $post_id;
			}
		}

		// Extract related_id and related_type from replacements.
		if ( isset( $replacements['lesson_id'] ) ) {
			$related_id   = absint( $replacements['lesson_id'] );
			$related_type = 'lesson';
		} elseif ( isset( $replacements['quiz_id'] ) ) {
			$related_id   = absint( $replacements['quiz_id'] );
			$related_type = 'quiz';
		} elseif ( isset( $replacements['certificate_id'] ) ) {
			$related_id   = absint( $replacements['certificate_id'] );
			$related_type = 'certificate';
		}

		// Get default notification type from settings if not provided.
		$default_type = get_option( 'splms_default_notification_type', 'info' );

		// Create notification using the unified method.
		$notification_id = $in_app_notifications->create_notification(
			$user_id,
			$template['title'],
			$template['message'],
			$template['type'] ?? $default_type,
			$course_id,
			$event_key,
			$related_id,
			$related_type
		);

		/**
		 * Fires when an in-app notification is created.
		 *
		 * @param int    $notification_id Notification ID.
		 * @param int    $user_id        User ID.
		 * @param string $event_key      Event key used.
		 */
		do_action( 'splms_in_app_notification_created', $notification_id, $user_id, $event_key );

		return true;
	}

	/**
	 * Replace placeholders in text.
	 *
	 * @since 1.0.0
	 *
	 * @param string $text         Text with placeholders.
	 * @param array  $replacements Replacement values.
	 * @return string Text with placeholders replaced.
	 */
	private function replace_placeholders( $text, $replacements ) {
		foreach ( $replacements as $placeholder => $value ) {
			$text = str_replace( '{' . $placeholder . '}', $value, $text );
		}

		return $text;
	}

	/**
	 * Log notification result.
	 *
	 * @since 1.0.0
	 *
	 * @param array         $notification_data Notification data.
	 * @param bool|WP_Error $result           Send result.
	 * @return void
	 */
	private function log_notification_result( $notification_data, $result ) {
		$log_entry = array(
			'timestamp'     => current_time( 'mysql' ),
			'to_email'      => $notification_data['to_email'],
			'template_key'  => $notification_data['template_key'],
			'success'       => ! is_wp_error( $result ),
			'error_message' => is_wp_error( $result ) ? $result->get_error_message() : '',
		);

		$logs   = get_option( 'splms_email_logs', array() );
		$logs[] = $log_entry;

		// Keep only last 1000 log entries.
		if ( count( $logs ) > 1000 ) {
			$logs = array_slice( $logs, -1000 );
		}

		update_option( 'splms_email_logs', $logs );
	}

	/**
	 * Get scheduled notifications.
	 *
	 * @since 1.0.0
	 *
	 * @return array Scheduled notifications.
	 */
	public function get_scheduled_notifications() {
		return get_option( 'splms_scheduled_notifications', array() );
	}

	/**
	 * Cancel a scheduled notification.
	 *
	 * @since 1.0.0
	 *
	 * @param int $notification_id Notification ID.
	 * @return bool True on success, false on failure.
	 */
	public function cancel_scheduled_notification( $notification_id ) {
		$notifications = get_option( 'splms_scheduled_notifications', array() );

		if ( isset( $notifications[ $notification_id ] ) ) {
			wp_clear_scheduled_hook( 'splms_schedule_email_notification', array( $notifications[ $notification_id ] ) );
			unset( $notifications[ $notification_id ] );
			update_option( 'splms_scheduled_notifications', $notifications );
			return true;
		}

		return false;
	}
}
