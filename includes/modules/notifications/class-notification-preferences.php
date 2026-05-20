<?php
/**
 * Notification Preferences Class
 *
 * Handles user notification preferences management.
 *
 * @package SkillPulse_LMS
 * @subpackage Notifications
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * SkillPulse LMS Notification Preferences Class
 *
 * @since 1.0.0
 */
class SPLMS_Notification_Preferences {

	/**
	 * Class instance.
	 *
	 * @var SPLMS_Notification_Preferences|null $instance
	 */
	private static $instance = null;

	/**
	 * Get the instance of this class.
	 *
	 * @since 1.0.0
	 *
	 * @return SPLMS_Notification_Preferences The class instance.
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			$class_name     = __CLASS__;
			self::$instance = new $class_name();
		}

		return self::$instance;
	}

	/**
	 * Get default notification preferences.
	 *
	 * @since 1.0.0
	 *
	 * @return array Default preferences.
	 */
	public function get_default_preferences() {
		return array(
			'enabled'                  => true,
			'email_enabled'            => true,
			'in_app_enabled'           => true,
			'course_enrollment'        => array(
				'email'  => true,
				'in_app' => true,
			),
			'course_completion'        => array(
				'email'  => true,
				'in_app' => true,
			),
			'enrollment_reminder'      => array(
				'email'  => true,
				'in_app' => true,
			),
			'lesson_completion'        => array(
				'email'  => true,
				'in_app' => true,
			),
			'quiz_completion'          => array(
				'email'  => true,
				'in_app' => true,
			),
			'certificate_generated'    => array(
				'email'  => true,
				'in_app' => true,
			),
			'certificate_awarded'      => array(
				'email'  => true,
				'in_app' => true,
			),
			'signup_created'           => array(
				'email'  => true,
				'in_app' => true,
			),
			'signup_activated'         => array(
				'email'  => true,
				'in_app' => true,
			),
			'review_reply'             => array(
				'email'  => true,
				'in_app' => true,
			),
			'review_new'               => array(
				'email'  => true,
				'in_app' => true,
			),
			'review_moderation'        => array(
				'email'  => true,
				'in_app' => false,
			),
			'review_moderation_result' => array(
				'email'  => true,
				'in_app' => true,
			),
			'order_completed'          => array(
				'email'  => true,
				'in_app' => true,
			),
			'order_refunded'           => array(
				'email'  => true,
				'in_app' => true,
			),
			'order_cancelled'          => array(
				'email'  => true,
				'in_app' => true,
			),
		);
	}

	/**
	 * Get user notification preferences.
	 *
	 * @since 1.0.0
	 *
	 * @param int $user_id User ID.
	 * @return array User preferences merged with defaults.
	 */
	public function get_user_preferences( $user_id ) {
		$defaults    = $this->get_default_preferences();
		$preferences = get_user_meta( $user_id, '_splms_notification_preferences', true );

		if ( ! is_array( $preferences ) ) {
			return $defaults;
		}

		// Merge with defaults to ensure all keys exist.
		return wp_parse_args( $preferences, $defaults );
	}

	/**
	 * Save user notification preferences.
	 *
	 * @since 1.0.0
	 *
	 * @param int   $user_id     User ID.
	 * @param array $preferences Preferences array.
	 * @return bool True on success, false on failure.
	 */
	public function save_user_preferences( $user_id, $preferences ) {
		// Sanitize preferences.
		$sanitized = $this->sanitize_preferences( $preferences );

		return update_user_meta( $user_id, '_splms_notification_preferences', $sanitized );
	}

	/**
	 * Sanitize notification preferences.
	 *
	 * @since 1.0.0
	 *
	 * @param array $preferences Raw preferences.
	 * @return array Sanitized preferences.
	 */
	private function sanitize_preferences( $preferences ) {
		$defaults  = $this->get_default_preferences();
		$sanitized = array();

		// Sanitize global settings.
		if ( isset( $preferences['enabled'] ) ) {
			$sanitized['enabled'] = (bool) $preferences['enabled'];
		}
		if ( isset( $preferences['email_enabled'] ) ) {
			$sanitized['email_enabled'] = (bool) $preferences['email_enabled'];
		}
		if ( isset( $preferences['in_app_enabled'] ) ) {
			$sanitized['in_app_enabled'] = (bool) $preferences['in_app_enabled'];
		}

		// Sanitize per-event preferences.
		$event_keys = array(
			'course_enrollment',
			'enrollment_reminder',
			'course_completion',
			'lesson_completion',
			'quiz_completion',
			'certificate_generated',
			'certificate_awarded',
			'signup_created',
			'signup_activated',
			'review_reply',
			'review_new',
			'review_moderation',
			'review_moderation_result',
			'order_completed',
			'order_refunded',
			'order_cancelled',
		);

		foreach ( $event_keys as $event_key ) {
			if ( isset( $preferences[ $event_key ] ) && is_array( $preferences[ $event_key ] ) ) {
				$sanitized[ $event_key ] = array(
					'email'  => isset( $preferences[ $event_key ]['email'] ) ? (bool) $preferences[ $event_key ]['email'] : $defaults[ $event_key ]['email'],
					'in_app' => isset( $preferences[ $event_key ]['in_app'] ) ? (bool) $preferences[ $event_key ]['in_app'] : $defaults[ $event_key ]['in_app'],
				);
			}
		}

		return $sanitized;
	}

	/**
	 * Check if user wants to receive email notification for a specific event.
	 *
	 * @since 1.0.0
	 *
	 * @param int    $user_id User ID.
	 * @param string $event_key Event key (e.g., 'course_enrollment').
	 * @return bool True if user wants email notification, false otherwise.
	 */
	public function user_wants_email( $user_id, $event_key ) {
		$preferences = $this->get_user_preferences( $user_id );

		// Check if notifications are globally disabled.
		if ( ! $preferences['enabled'] ) {
			return false;
		}

		// Check if email notifications are globally disabled.
		if ( ! $preferences['email_enabled'] ) {
			return false;
		}

		// Check event-specific preference.
		if ( isset( $preferences[ $event_key ]['email'] ) ) {
			return (bool) $preferences[ $event_key ]['email'];
		}

		// Default to true if preference not set.
		return true;
	}

	/**
	 * Check if user wants to receive in-app notification for a specific event.
	 *
	 * @since 1.0.0
	 *
	 * @param int    $user_id User ID.
	 * @param string $event_key Event key (e.g., 'course_enrollment').
	 * @return bool True if user wants in-app notification, false otherwise.
	 */
	public function user_wants_in_app( $user_id, $event_key ) {
		$preferences = $this->get_user_preferences( $user_id );

		// Check if notifications are globally disabled.
		if ( ! $preferences['enabled'] ) {
			return false;
		}

		// Check if in-app notifications are globally disabled.
		if ( ! $preferences['in_app_enabled'] ) {
			return false;
		}

		// Check event-specific preference.
		if ( isset( $preferences[ $event_key ]['in_app'] ) ) {
			return (bool) $preferences[ $event_key ]['in_app'];
		}

		// Default to true if preference not set.
		return true;
	}

	/**
	 * Get event display name.
	 *
	 * @since 1.0.0
	 *
	 * @param string $event_key Event key.
	 * @return string Display name.
	 */
	public function get_event_display_name( $event_key ) {
		$names = array(
			'course_enrollment'        => __( 'Course Enrollment', 'skillpulse-lms' ),
			'course_completion'        => __( 'Course Completion', 'skillpulse-lms' ),
			'enrollment_reminder'      => __( 'Enrollment Reminder', 'skillpulse-lms' ),
			'lesson_completion'        => __( 'Lesson Completion', 'skillpulse-lms' ),
			'quiz_completion'          => __( 'Quiz Completion', 'skillpulse-lms' ),
			'certificate_generated'    => __( 'Certificate Generated', 'skillpulse-lms' ),
			'certificate_awarded'      => __( 'Certificate Awarded', 'skillpulse-lms' ),
			'signup_created'           => __( 'Account Created', 'skillpulse-lms' ),
			'signup_activated'         => __( 'Account Activated', 'skillpulse-lms' ),
			'review_reply'             => __( 'Review Reply', 'skillpulse-lms' ),
			'review_new'               => __( 'New Review', 'skillpulse-lms' ),
			'review_moderation'        => __( 'Review Moderation', 'skillpulse-lms' ),
			'review_moderation_result' => __( 'Review Moderation Result', 'skillpulse-lms' ),
			'order_completed'          => __( 'Order Completed', 'skillpulse-lms' ),
			'order_refunded'           => __( 'Order Refunded', 'skillpulse-lms' ),
			'order_cancelled'          => __( 'Order Cancelled', 'skillpulse-lms' ),
		);

		return isset( $names[ $event_key ] ) ? $names[ $event_key ] : $event_key;
	}

	/**
	 * Get event description.
	 *
	 * @since 1.0.0
	 *
	 * @param string $event_key Event key.
	 * @return string Description.
	 */
	public function get_event_description( $event_key ) {
		$descriptions = array(
			'course_enrollment'        => __( 'Receive notifications when you enroll in a course', 'skillpulse-lms' ),
			'course_completion'        => __( 'Receive notifications when you complete a course', 'skillpulse-lms' ),
			'enrollment_reminder'      => __( 'Receive reminder emails to continue your learning', 'skillpulse-lms' ),
			'lesson_completion'        => __( 'Receive notifications when you complete a lesson', 'skillpulse-lms' ),
			'quiz_completion'          => __( 'Receive notifications when you complete a quiz', 'skillpulse-lms' ),
			'certificate_generated'    => __( 'Receive notifications when a certificate is generated', 'skillpulse-lms' ),
			'certificate_awarded'      => __( 'Receive notifications when you are awarded a certificate', 'skillpulse-lms' ),
			'signup_created'           => __( 'Receive notifications when your account is created', 'skillpulse-lms' ),
			'signup_activated'         => __( 'Receive notifications when your account is activated', 'skillpulse-lms' ),
			'review_reply'             => __( 'Receive notifications when someone replies to your review', 'skillpulse-lms' ),
			'review_new'               => __( 'Receive notifications when a new review is posted on your course', 'skillpulse-lms' ),
			'review_moderation'        => __( 'Receive notifications when a review needs moderation (admin only)', 'skillpulse-lms' ),
			'review_moderation_result' => __( 'Receive notifications about review moderation results', 'skillpulse-lms' ),
			'order_completed'          => __( 'Receive notifications when your course order is completed', 'skillpulse-lms' ),
			'order_refunded'           => __( 'Receive notifications when your order is refunded', 'skillpulse-lms' ),
			'order_cancelled'          => __( 'Receive notifications when your order is cancelled', 'skillpulse-lms' ),
		);

		return isset( $descriptions[ $event_key ] ) ? $descriptions[ $event_key ] : '';
	}

	/**
	 * Get all available events with their display names and descriptions.
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of events with keys as event_key and values as array with 'name' and 'description'.
	 */
	public function get_available_events() {
		$event_keys = array(
			'course_enrollment',
			'course_completion',
			'enrollment_reminder',
			'lesson_completion',
			'quiz_completion',
			'certificate_generated',
			'certificate_awarded',
			'signup_created',
			'signup_activated',
			'review_reply',
			'review_new',
			'review_moderation',
			'review_moderation_result',
			'order_completed',
			'order_refunded',
			'order_cancelled',
		);

		$events = array();
		foreach ( $event_keys as $event_key ) {
			$events[ $event_key ] = array(
				'name'        => $this->get_event_display_name( $event_key ),
				'description' => $this->get_event_description( $event_key ),
			);
		}

		return $events;
	}
}
