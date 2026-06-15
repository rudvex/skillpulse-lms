<?php
/**
 * In-App Notification Templates Class
 *
 * Handles in-app notification template management, default templates, and placeholder replacement.
 *
 * @package SPLMS
 * @subpackage Notifications
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * In-App Notification Templates Class
 *
 * @since 1.0.0
 */
class SPLMS_In_App_Templates {

	/**
	 * Class instance.
	 *
	 * @var SPLMS_In_App_Templates|null $instance
	 */
	private static $instance = null;

	/**
	 * Get the instance of this class.
	 *
	 * @since 1.0.0
	 *
	 * @return SPLMS_In_App_Templates The class instance.
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			$class_name     = __CLASS__;
			self::$instance = new $class_name();
		}

		return self::$instance;
	}

	/**
	 * Get default templates with placeholders.
	 *
	 * @since 1.0.0
	 *
	 * @return array Default templates array.
	 */
	public function get_default_templates() {
		return array(
			'course_enrollment'        => array(
				'title'   => __( 'Welcome to {course_title}!', 'skillpulse-lms' ),
				'message' => __( 'You have been enrolled in the course "{course_title}". Start learning now!', 'skillpulse-lms' ),
				'type'    => 'success',
			),
			'course_completion'        => array(
				'title'   => __( 'Course Completed!', 'skillpulse-lms' ),
				'message' => __( 'Congratulations! You have successfully completed "{course_title}".', 'skillpulse-lms' ),
				'type'    => 'success',
			),
			'lesson_completion'        => array(
				'title'   => __( 'Lesson Completed', 'skillpulse-lms' ),
				'message' => __( 'You have completed the lesson "{lesson_title}" in "{course_title}".', 'skillpulse-lms' ),
				'type'    => 'info',
			),
			'quiz_completion'          => array(
				'title'   => __( 'Quiz Completed', 'skillpulse-lms' ),
				'message' => __( 'You have completed the quiz "{quiz_title}" in "{course_title}".', 'skillpulse-lms' ),
				'type'    => 'info',
			),
			'quiz_passed'              => array(
				'title'   => __( 'Quiz Passed!', 'skillpulse-lms' ),
				'message' => __( 'Congratulations! You passed the quiz "{quiz_title}" with a score of {score}%.', 'skillpulse-lms' ),
				'type'    => 'success',
			),
			'quiz_failed'              => array(
				'title'   => __( 'Quiz Not Passed', 'skillpulse-lms' ),
				'message' => __( 'You scored {score}% on the quiz "{quiz_title}". The passing score is {passing_score}%.', 'skillpulse-lms' ),
				'type'    => 'warning',
			),
			'certificate_awarded'      => array(
				'title'   => __( 'Certificate Awarded!', 'skillpulse-lms' ),
				'message' => __( 'Congratulations! You have been awarded a certificate for completing "{course_title}".', 'skillpulse-lms' ),
				'type'    => 'success',
			),
			'signup_created'           => array(
				'title'   => __( 'Account Created', 'skillpulse-lms' ),
				'message' => __( 'Welcome to {site_name}! Your account has been created successfully.', 'skillpulse-lms' ),
				'type'    => 'info',
			),
			'signup_activated'         => array(
				'title'   => __( 'Account Activated', 'skillpulse-lms' ),
				'message' => __( 'Your account has been activated. You can now access all features on {site_name}.', 'skillpulse-lms' ),
				'type'    => 'success',
			),
			'review_reply'             => array(
				'title'   => __( 'New Reply to Your Review', 'skillpulse-lms' ),
				'message' => __( '{author_name} replied to your review for "{course_title}".', 'skillpulse-lms' ),
				'type'    => 'info',
			),
			'review_new'               => array(
				'title'   => __( 'New Review on Your Course', 'skillpulse-lms' ),
				'message' => __( '{reviewer_name} left a {rating}-star review on "{course_title}".', 'skillpulse-lms' ),
				'type'    => 'info',
			),
			'review_moderation'        => array(
				'title'   => __( 'Review Pending Moderation', 'skillpulse-lms' ),
				'message' => __( 'A new review on "{course_title}" is pending moderation.', 'skillpulse-lms' ),
				'type'    => 'warning',
			),
			'review_moderation_result' => array(
				'title'   => __( 'Review Moderation Result', 'skillpulse-lms' ),
				'message' => __( 'Your review for "{course_title}" has been {moderation_status}.', 'skillpulse-lms' ),
				'type'    => 'info',
			),
			'order_completed'          => array(
				'title'   => __( 'Order Confirmation - Order #{order_id}', 'skillpulse-lms' ),
				'message' => __( 'Thank you for your purchase! Your order #{order_id} for the course "{course_title}" has been confirmed and your payment has been processed successfully.', 'skillpulse-lms' ),
				'type'    => 'success',
			),
			'order_refunded'           => array(
				'title'   => __( 'Order Refunded - Order #{order_id}', 'skillpulse-lms' ),
				'message' => __( 'Your order #{order_id} for the course "{course_title}" has been refunded. Refund Amount: {refund_amount}', 'skillpulse-lms' ),
				'type'    => 'warning',
			),
			'order_cancelled'          => array(
				'title'   => __( 'Order Cancelled - Order #{order_id}', 'skillpulse-lms' ),
				'message' => __( 'Your order #{order_id} for the course "{course_title}" has been cancelled.', 'skillpulse-lms' ),
				'type'    => 'warning',
			),
		);
	}

	/**
	 * Get template for a specific event.
	 *
	 * @since 1.0.0
	 *
	 * @param string $event_key Event key.
	 * @return array|false Template array or false if not found.
	 */
	public function get_template( $event_key ) {
		// Get saved template from options.
		$option_name    = "splms_in_app_template_{$event_key}";
		$saved_template = get_option( $option_name, array() );

		// Get default template.
		$default_templates = $this->get_default_templates();
		$default_template  = isset( $default_templates[ $event_key ] ) ? $default_templates[ $event_key ] : array();

		// Merge saved template with defaults.
		$template = wp_parse_args( $saved_template, $default_template );

		// If template is disabled, return false.
		if ( isset( $template['is_enabled'] ) && ! $template['is_enabled'] ) {
			return false;
		}

		// Ensure we have title and message.
		if ( empty( $template['title'] ) && empty( $template['message'] ) ) {
			return false;
		}

		// Set default type if not set.
		if ( empty( $template['type'] ) ) {
			$template['type'] = 'info';
		}

		return $template;
	}

	/**
	 * Replace placeholders in template text.
	 *
	 * @since 1.0.0
	 *
	 * @param string $text         Text with placeholders.
	 * @param array  $replacements Replacement values.
	 * @return string Text with placeholders replaced.
	 */
	public function replace_placeholders( $text, $replacements ) {
		if ( empty( $text ) || empty( $replacements ) ) {
			return $text;
		}

		foreach ( $replacements as $placeholder => $value ) {
			// Ensure value is a string before using str_replace.
			if ( is_array( $value ) ) {
				$value = implode( ', ', $value );
			} elseif ( is_object( $value ) ) {
				$value = (string) $value;
			} elseif ( is_numeric( $value ) ) {
				// Convert numeric values (int, float) to string.
				$value = (string) $value;
			} elseif ( ! is_string( $value ) ) {
				$value = '';
			}

			// Replace placeholder in format {placeholder}.
			$text = str_replace( '{' . $placeholder . '}', $value, $text );
		}

		return $text;
	}

	/**
	 * Get template with placeholders replaced.
	 *
	 * @since 1.0.0
	 *
	 * @param string $event_key    Event key.
	 * @param array  $replacements Replacement values.
	 * @return array|false Template array with replaced placeholders or false if not found.
	 */
	public function get_template_with_replacements( $event_key, $replacements = array() ) {
		$template = $this->get_template( $event_key );

		if ( ! $template ) {
			return false;
		}

		// Replace placeholders in title and message.
		if ( ! empty( $template['title'] ) ) {
			$template['title'] = $this->replace_placeholders( $template['title'], $replacements );
		}

		if ( ! empty( $template['message'] ) ) {
			$template['message'] = $this->replace_placeholders( $template['message'], $replacements );
		}

		return $template;
	}

	/**
	 * Get all templates (for admin UI).
	 *
	 * @since 1.0.0
	 *
	 * @return array All templates with defaults merged.
	 */
	public function get_all_templates() {
		// Check cache first (15-minute TTL for performance).
		$cache_key = 'splms_in_app_templates_all';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		$default_templates = $this->get_default_templates();
		$templates         = array();

		// Batch retrieve all options at once to reduce database queries (12 queries -> 1 query).
		global $wpdb;
		$option_names = array();
		foreach ( array_keys( $default_templates ) as $event_key ) {
			$option_names[] = "splms_in_app_template_{$event_key}";
		}

		// Get all options in a single query if we have any.
		$results = array();
		if ( ! empty( $option_names ) ) {
			$placeholders = implode( ',', array_fill( 0, count( $option_names ), '%s' ) );
			$query        = $wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Placeholders are properly prepared, table name is safe.
				"SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name IN ($placeholders)",
				$option_names
			);
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Query is prepared above.
			$db_results = $wpdb->get_results( $query, OBJECT_K );
			if ( $db_results ) {
				$results = $db_results;
			}
		}

		// Process templates.
		foreach ( $default_templates as $event_key => $default_template ) {
			$option_name    = "splms_in_app_template_{$event_key}";
			$saved_template = array();

			// Get saved template from batch results.
			if ( isset( $results[ $option_name ] ) ) {
				$saved_template = maybe_unserialize( $results[ $option_name ]->option_value );
				if ( ! is_array( $saved_template ) ) {
					$saved_template = array();
				}
			}

			$templates[ $event_key ]              = wp_parse_args( $saved_template, $default_template );
			$templates[ $event_key ]['event_key'] = $event_key;

			// Ensure is_enabled is set (default to true if not set).
			if ( ! isset( $templates[ $event_key ]['is_enabled'] ) ) {
				$templates[ $event_key ]['is_enabled'] = true;
			}
		}

		// Cache the result for 15 minutes.
		set_transient( $cache_key, $templates, 15 * MINUTE_IN_SECONDS );

		return $templates;
	}

	/**
	 * Clear templates cache.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function clear_cache() {
		delete_transient( 'splms_in_app_templates_all' );
	}
}
