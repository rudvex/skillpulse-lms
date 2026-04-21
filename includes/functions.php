<?php
/**
 * SkillPulse LMS Utility Functions
 *
 * Core utility functions for the SkillPulse LMS plugin.
 * Consolidated from multiple function files for better organization.
 *
 * @package SkillPulse_LMS
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


if ( ! function_exists( 'splms_get_template_part' ) ) {
	/**
	 * Get template part (from template-functions.php).
	 *
	 * @param string $slug Template slug.
	 * @param string $name Template name (default: '').
	 * @param array  $args Arguments to pass to the template (default: array()).
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	function splms_get_template_part( $slug, $name = '', $args = array() ) {
		$template = '';

		if ( $name ) {
			$template = locate_template(
				array(
					"skillpulse-lms/{$slug}-{$name}.php",
					"skillpulse-lms/{$slug}.php",
				)
			);
		} else {
			$template = locate_template( array( "skillpulse-lms/{$slug}.php" ) );
		}

		// Get default template.
		if ( ! $template && $name && file_exists( SKILLPULSE_LMS_DIR_PATH . "templates/{$slug}-{$name}.php" ) ) {
			$template = SKILLPULSE_LMS_DIR_PATH . "templates/{$slug}-{$name}.php";
		}

		if ( ! $template ) {
			$template = SKILLPULSE_LMS_DIR_PATH . "templates/{$slug}.php";
		}

		// Allow 3rd party plugins to filter template file from their plugin.
		$template = apply_filters( 'splms_get_template_part', $template, $slug, $name );

		if ( $template && file_exists( $template ) ) {
			load_template( $template, false, $args );
		}
	}
}

/**
 * Get template with theme override support.
 *
 * @param string $template_name Template name.
 * @param array  $args          Template arguments.
 * @param string $template_path Template path (optional).
 * @param string $default_path  Default path (optional).
 *
 * @since 1.0.0
 *
 * @return void
 */
function splms_get_template( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
	if ( $args && is_array( $args ) ) {
		// phpcs:ignore WordPress.PHP.DontExtract.extract_extract -- Required for template variable extraction.
		extract( $args );
	}

	$located = splms_locate_template( $template_name, $template_path, $default_path );

	if ( ! file_exists( $located ) ) {
		return;
	}

	// Allow 3rd party plugin filter template file from their plugin.
	$located = apply_filters( 'splms_get_template', $located, $template_name, $args, $template_path, $default_path );

	do_action( 'splms_before_template_part', $template_name, $template_path, $located, $args );

	include $located;

	do_action( 'splms_after_template_part', $template_name, $template_path, $located, $args );
}

/**
 * Locate template file with content-type structure.
 *
 * @param string $template_name Template name.
 * @param string $template_path Template path (optional).
 * @param string $default_path  Default path (optional).
 *
 * @since 1.0.0
 *
 * @return string Template file path.
 */
function splms_locate_template( $template_name, $template_path = '', $default_path = '' ) {
	if ( ! $template_path ) {
		$template_path = 'skillpulse-lms/';
	}

	if ( ! $default_path ) {
		$default_path = SKILLPULSE_LMS_DIR_PATH . 'templates/';
	}

	// Look within passed path within the theme - this is priority.
	$template = locate_template(
		array(
			trailingslashit( $template_path ) . $template_name,
			$template_name,
		)
	);

	// Get default template.
	if ( ! $template ) {
		$template = $default_path . $template_name;
	}

	// Return what we found.
	return apply_filters( 'splms_locate_template', $template, $template_name, $template_path );
}

/**
 * Get course difficulty level.
 *
 * @param int $course_id Course ID.
 *
 * @since 1.0.0
 *
 * @return string
 */
function splms_get_course_difficulty( $course_id = null ) {
	if ( ! $course_id ) {
		$course_id = get_the_ID();
	}

	// Try new settings system first.
	$content_info = splms_get_course_content_info( $course_id );
	$difficulty   = $content_info['difficulty_level'];

	if ( ! $difficulty ) {
		$difficulty = 'beginner';
	}

	return apply_filters( 'splms_course_difficulty', $difficulty, $course_id );
}

/**
 * Display course thumbnail.
 *
 * @param int    $course_id Course ID.
 * @param string $size      Image size.
 * @param array  $attr      Image attributes.
 *
 * @since 1.0.0
 *
 * @return void
 */
function splms_course_thumbnail( $course_id = null, $size = 'medium', $attr = array() ) {
	if ( ! $course_id ) {
		$course_id = get_the_ID();
	}

	$thumbnail_url = splms_get_course_thumbnail_url( $course_id, $size );

	$default_attr = array(
		'class'   => 'splms-course-thumbnail',
		'alt'     => get_the_title( $course_id ),
		'loading' => 'lazy',
	);

	$attr = wp_parse_args( $attr, $default_attr );

	echo '<img src="' . esc_url( $thumbnail_url ) . '"';
	foreach ( $attr as $key => $value ) {
		echo ' ' . esc_attr( $key ) . '="' . esc_attr( $value ) . '"';
	}
	echo '>';
}

/**
 * Get course thumbnail URL with fallback to placeholder.
 *
 * @param int    $course_id Course ID.
 * @param string $size      Image size.
 *
 * @since 1.0.0
 *
 * @return string Thumbnail URL.
 */
function splms_get_course_thumbnail_url( $course_id = null, $size = 'medium' ) {
	if ( ! $course_id ) {
		$course_id = get_the_ID();
	}

	$thumbnail_url = get_the_post_thumbnail_url( $course_id, $size );

	if ( ! $thumbnail_url ) {
		// Use the existing placeholder image.
		$number        = wp_rand( 1, 3 );
		$thumbnail_url = SKILLPULSE_LMS_URL_PATH . 'assets/images/placeholder/course-placeholder-1.png';
	}

	return $thumbnail_url;
}

/**
 * Get user enrolled courses (database table only).
 *
 * @param int $user_id User ID.
 *
 * @since 1.0.0
 *
 * @return array Array of course IDs.
 */
function splms_get_user_enrolled_courses( $user_id ) {
	$enrollments_query = SkillPulse_LMS_Enrollments_Query::get_instance();
	$db_enrollments    = $enrollments_query->get_user_courses( $user_id, array( 'status' => array( 'active', 'completed' ) ) );

	if ( ! empty( $db_enrollments ) ) {
		return wp_list_pluck( $db_enrollments, 'course_id' );
	}

	return array();
}

/**
 * Check if user is enrolled in course (database table only).
 *
 * @param int   $course_id Course ID.
 * @param int   $user_id   User ID (optional, defaults to current user).
 * @param array $args      Additional arguments.
 *
 * @since 1.0.0
 *
 * @return bool True if enrolled.
 */
function splms_is_user_enrolled( $course_id, $user_id = null, $args = array() ) {
	if ( ! $user_id ) {
		$user_id = get_current_user_id();
	}

	$enrollments_query = SkillPulse_LMS_Enrollments_Query::get_instance();

	return $enrollments_query->is_user_enrolled( $course_id, $user_id, $args );
}

/**
 * Get user enrollment status for a course.
 *
 * @param int $course_id Course ID.
 * @param int $user_id   User ID (optional, defaults to current user).
 *
 * @since 1.0.0
 *
 * @return string Enrollment status.
 */
function splms_get_user_enrollment_status( $course_id, $user_id = null ) {
	if ( ! $user_id ) {
		$user_id = get_current_user_id();
	}

	$enrollments_query = SkillPulse_LMS_Enrollments_Query::get_instance();

	return $enrollments_query->get_user_enrollment_status( $course_id, $user_id );
}

/**
 * Check if student self-service unenrollment is allowed.
 *
 * @since 1.0.0
 *
 * @return bool True if allowed, false otherwise.
 */
function splms_is_student_unenrollment_allowed() {
	return splms_get_setting( 'allow_student_unenrollment', false );
}

/**
 * Get enrollment expiration date for a user and course.
 *
 * @param int $user_id   User ID.
 * @param int $course_id Course ID.
 *
 * @since 1.0.0
 *
 * @return string|null Expiration date in MySQL format, or null if no expiration.
 */
function splms_get_enrollment_expiration_date( $user_id, $course_id ) {
	$enrollments_query = SkillPulse_LMS_Enrollments_Query::get_instance();
	$enrollment        = $enrollments_query->get_enrollment( $user_id, $course_id );

	if ( ! $enrollment ) {
		return null;
	}

	// Check if enrollment has explicit expiration date.
	if ( ! empty( $enrollment->access_expires ) ) {
		return $enrollment->access_expires;
	}

	// Calculate from course settings.
	$course_settings     = splms_get_course_settings( $course_id );
	$scheduling_settings = isset( $course_settings['course_scheduling_settings'] ) ? $course_settings['course_scheduling_settings'] : array();
	$enable_expiration   = isset( $scheduling_settings['enable_enrollment_expiration'] ) ? filter_var(
		$scheduling_settings['enable_enrollment_expiration'],
		FILTER_VALIDATE_BOOLEAN
	) : false;
	$expiration_days     = isset( $scheduling_settings['enrollment_expiration_days'] ) ? intval( $scheduling_settings['enrollment_expiration_days'] ) : 0;

	if ( ! $enable_expiration ) {
		return null;
	}

	if ( $expiration_days <= 0 ) {
		return null; // Unlimited access.
	}

	// Calculate expiration date from enrollment date.
	$enrollment_date = $enrollment->enrolled_at;
	if ( ! $enrollment_date ) {
		return null;
	}

	$expiration_timestamp = strtotime( $enrollment_date . ' + ' . $expiration_days . ' days' );

	return date( 'Y-m-d H:i:s', $expiration_timestamp ); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date -- Date formatting for database storage.
}

/**
 * Check if enrollment has expired.
 *
 * @param int $user_id   User ID.
 * @param int $course_id Course ID.
 *
 * @since 1.0.0
 *
 * @return bool True if expired, false otherwise.
 */
function splms_is_enrollment_expired( $user_id, $course_id ) {
	$expiration_date = splms_get_enrollment_expiration_date( $user_id, $course_id );

	if ( ! $expiration_date ) {
		return false; // No expiration set.
	}

	// phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested -- Need site timezone for expiration comparison.
	$current_timestamp    = current_time( 'timestamp' );
	$expiration_timestamp = strtotime( $expiration_date );

	return $current_timestamp >= $expiration_timestamp;
}

/**
 * Get enrollment expiration info.
 *
 * @param int $user_id   User ID.
 * @param int $course_id Course ID.
 *
 * @since 1.0.0
 *
 * @return array Expiration info with 'is_expired', 'expiration_date', 'days_remaining'.
 */
function splms_get_enrollment_expiration_info( $user_id, $course_id ) {
	$expiration_date = splms_get_enrollment_expiration_date( $user_id, $course_id );

	$default = array(
		'is_expired'      => false,
		'expiration_date' => null,
		'days_remaining'  => null,
	);

	if ( ! $expiration_date ) {
		return $default;
	}

	// phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested -- Need site timezone for expiration comparison.
	$current_timestamp    = current_time( 'timestamp' );
	$expiration_timestamp = strtotime( $expiration_date );
	$is_expired           = $current_timestamp >= $expiration_timestamp;

	$days_remaining = 0;
	if ( ! $is_expired ) {
		$seconds_remaining = $expiration_timestamp - $current_timestamp;
		$days_remaining    = max( 0, ceil( $seconds_remaining / DAY_IN_SECONDS ) );
	}

	return array(
		'is_expired'      => $is_expired,
		'expiration_date' => $expiration_date,
		'days_remaining'  => $days_remaining,
	);
}

/**
 * Get formatted course language.
 *
 * @param int $course_id Course ID.
 *
 * @since 1.0.0
 *
 * @return string
 */
function splms_get_formatted_course_language( $course_id = null ) {
	$content_info = splms_get_course_content_info( $course_id );

	$languages = array(
		'en' => __( 'English', 'skillpulse-lms' ),
		'es' => __( 'Spanish', 'skillpulse-lms' ),
		'fr' => __( 'French', 'skillpulse-lms' ),
		'de' => __( 'German', 'skillpulse-lms' ),
		'it' => __( 'Italian', 'skillpulse-lms' ),
		'pt' => __( 'Portuguese', 'skillpulse-lms' ),
		'zh' => __( 'Chinese (Simplified)', 'skillpulse-lms' ),
		'ja' => __( 'Japanese', 'skillpulse-lms' ),
	);

	return $languages[ $content_info['course_language'] ] ?? $content_info['course_language'];
}


/**
 * Get formatted learning method.
 *
 * @param int $course_id Course ID.
 *
 * @since 1.0.0
 *
 * @return string
 */
function splms_get_formatted_learning_method( $course_id = null ) {
	$content_info = splms_get_course_content_info( $course_id );

	$methods = array(
		'video'       => __( 'Video-Based Learning', 'skillpulse-lms' ),
		'text'        => __( 'Text & Reading Materials', 'skillpulse-lms' ),
		'interactive' => __( 'Interactive Content', 'skillpulse-lms' ),
		'project'     => __( 'Project-Based Learning', 'skillpulse-lms' ),
	);

	return $methods[ $content_info['learning_method'] ] ?? ucfirst( $content_info['learning_method'] );
}

/**
 * Get formatted course delivery mode.
 *
 * @param int $course_id Course ID.
 *
 * @since 1.0.0
 *
 * @return string
 */
function splms_get_formatted_delivery_mode( $course_id = null ) {
	$delivery_info = splms_get_course_delivery_info( $course_id );

	$modes = array(
		'self_paced' => __( 'Self-Paced (Pre-recorded)', 'skillpulse-lms' ),
		'cohort'     => __( 'Cohort-Based (Fixed Schedule)', 'skillpulse-lms' ),
	);

	return $modes[ $delivery_info['delivery_mode'] ] ?? ucfirst( $delivery_info['delivery_mode'] );
}

/**
 * Get enrollment dates information.
 *
 * @param int $course_id Course ID.
 *
 * @since 1.0.0
 *
 * @return array
 */
function splms_get_course_enrollment_dates( $course_id = null ) {
	$access_info = splms_get_course_access_info( $course_id );

	$enrollment_info = array(
		'start_date' => $access_info['enrollment_start'] ?? '',
		'end_date'   => $access_info['enrollment_end'] ?? '',
		'is_open'    => true,
	);

	// Check if enrollment is currently open.
	if ( ! empty( $enrollment_info['start_date'] ) && ! empty( $enrollment_info['end_date'] ) ) {
		$start_timestamp = strtotime( $enrollment_info['start_date'] );
		$end_timestamp   = strtotime( $enrollment_info['end_date'] );
		// phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested -- Using timestamp for date comparison.
		$current_time = current_time( 'timestamp' );

		$enrollment_info['is_open'] = ( $start_timestamp <= $current_time && $current_time <= $end_timestamp );
	}

	return $enrollment_info;
}

/**
 * Get course capacity information.
 *
 * @param int $course_id Course ID.
 *
 * @since 1.0.0
 *
 * @return array
 */
function splms_get_course_max_enrollment_info( $course_id = null ) {
	$access_info    = splms_get_course_access_info( $course_id );
	$students_count = splms_get_course_enrollment_count( $course_id );

	$max_enrollment = intval( $access_info['max_enrollment'] ?? 0 );

	return array(
		'max_enrollment'  => $max_enrollment,
		'enrolled_count'  => $students_count,
		'available_spots' => $max_enrollment > 0 ? max( 0, $max_enrollment - $students_count ) : PHP_INT_MAX,
		'is_full'         => $max_enrollment > 0 && $students_count >= $max_enrollment,
		'has_limit'       => $max_enrollment > 0,
	);
}

/**
 * Check if course certificate is enabled.
 *
 * @param int $course_id Course ID.
 *
 * @since 1.0.0
 *
 * @return bool
 */
function splms_is_certificate_enabled( $course_id = null ) {
	// Return false if certificates module is not loaded.
	if ( ! class_exists( 'SkillPulse_LMS_Certificates' ) ) {
		return false;
	}

	$content_info = splms_get_course_content_info( $course_id );

	return (bool) $content_info['certificate_enabled'];
}

/**
 * Get course prerequisites description.
 *
 * @param int $course_id Course ID.
 *
 * @since 1.0.0
 *
 * @return string
 */
function splms_get_course_prerequisites( $course_id = null ) {
	$content_info = splms_get_course_content_info( $course_id );

	return $content_info['prerequisites_description'] ?? '';
}

/**
 * Check if a lesson is available for guest preview.
 *
 * @param int $lesson_id Lesson ID.
 *
 * @since 1.0.0
 *
 * @return bool
 */
function splms_is_lesson_guest_preview_available( $lesson_id = null ) {
	if ( ! $lesson_id ) {
		$lesson_id = get_the_ID();
	}

	$access_control = SkillPulse_LMS_Access_Control::get_instance();

	return $access_control->user_can_access_lesson( 0, $lesson_id );
}

/**
 * Get the effective guest preview limit for a course.
 * This function combines global and course-specific settings.
 *
 * @param int $course_id Course ID.
 *
 * @since 1.0.0
 *
 * @return int|string Returns the effective limit (0 for unlimited, or number).
 */
function splms_get_effective_guest_preview_limit( $course_id = null ) {
	if ( ! $course_id ) {
		$course_id = get_the_ID();
	}

	$course_settings = splms_get_course_settings( $course_id );
	$access_settings = isset( $course_settings['course_access_settings'] ) ? $course_settings['course_access_settings'] : array();

	$enable_guest_lessons = isset( $access_settings['enable_guest_lessons'] ) ? filter_var( $access_settings['enable_guest_lessons'], FILTER_VALIDATE_BOOLEAN ) : false;

	if ( $enable_guest_lessons ) {
		$guest_lesson_limit = isset( $access_settings['guest_lesson_limit'] ) ? intval( $access_settings['guest_lesson_limit'] ) : 1;

		// Return limit if numeric, otherwise default to 1.
		return ! empty( $guest_lesson_limit ) && is_numeric( $guest_lesson_limit ) ? intval( $guest_lesson_limit ) : 1;
	}

	return 0;
}

/**
 * Get the effective guest quiz preview limit for a course.
 * This function combines global and course-specific settings.
 *
 * @param int $course_id Course ID.
 *
 * @since 1.0.0
 *
 * @return int|string Returns the effective limit (0 for unlimited, or number).
 */
function splms_get_effective_guest_quiz_preview_limit( $course_id = null ) {
	if ( ! $course_id ) {
		$course_id = get_the_ID();
	}

	$course_settings = splms_get_course_settings( $course_id );
	$access_settings = isset( $course_settings['course_access_settings'] ) ? $course_settings['course_access_settings'] : array();

	$enable_guest_quizzes = isset( $access_settings['enable_guest_quizzes'] ) ? filter_var( $access_settings['enable_guest_quizzes'], FILTER_VALIDATE_BOOLEAN ) : false;

	if ( $enable_guest_quizzes ) {
		$guest_quiz_limit = isset( $access_settings['guest_quiz_limit'] ) ? intval( $access_settings['guest_quiz_limit'] ) : 1;

		// Return limit if numeric, otherwise default to 1.
		return ! empty( $guest_quiz_limit ) && is_numeric( $guest_quiz_limit ) ? intval( $guest_quiz_limit ) : 1;
	}

	return 0;
}

/**
 * Check if a quiz is available for guest preview.
 *
 * @param int $quiz_id Quiz ID.
 *
 * @since 1.0.0
 *
 * @return bool
 */
function splms_is_quiz_guest_preview_available( $quiz_id = null ) {
	if ( ! $quiz_id ) {
		$quiz_id = get_the_ID();
	}

	$access_control = SkillPulse_LMS_Access_Control::get_instance();

	return $access_control->user_can_access_quiz( 0, $quiz_id );
}


/**
 * Get course completion criteria information.
 *
 * @param int $course_id Course ID.
 *
 * @since 1.0.0
 *
 * @return array
 */
function splms_get_course_completion_info( $course_id = null ) {
	$content_info = splms_get_course_content_info( $course_id );

	return array(
		'criteria'            => ! empty( $content_info['completion_criteria'] ) ? $content_info['completion_criteria'] : splms_get_setting( 'completion_criteria', 'all_lessons' ),
		'passing_grade'       => intval( $content_info['passing_grade'] ?? 70 ),
		'certificate_enabled' => $content_info['certificate_enabled'] ?? true,
	);
}

/**
 * Get formatted course completion criteria.
 *
 * @param int $course_id Course ID.
 *
 * @since 1.0.0
 *
 * @return string
 */
function splms_get_formatted_completion_criteria( $course_id = null ) {
	$completion_info = splms_get_course_completion_info( $course_id );

	$criteria_labels = array(
		'all_lessons'      => __( 'Complete All Lessons', 'skillpulse-lms' ),
		'lessons_and_quiz' => sprintf(
		/* translators: %d: Passing grade percentage. */
			__( 'Complete All Lessons + Pass Quizzes (%d%% minimum)', 'skillpulse-lms' ),
			$completion_info['passing_grade']
		),
	);

	return $criteria_labels[ $completion_info['criteria'] ] ?? ucfirst( str_replace( '_', ' ', $completion_info['criteria'] ) );
}


/**
 * Get a setting from the settings array.
 *
 * @param string $setting_name  Setting name.
 * @param mixed  $default_value Default value.
 *
 * @since 1.0.0
 *
 * @return mixed Setting value.
 */
/**
 * Check if in-app notifications are enabled globally.
 *
 * @since 1.0.0
 *
 * @return bool True if in-app notifications are enabled, false otherwise.
 */
function splms_is_in_app_notifications_enabled() {
	$settings = SkillPulse_LMS_Settings::get_instance()->get_all_settings();
	$enabled  = isset( $settings['notifications']['in_app_settings']['enable_in_app_notifications'] )
		? $settings['notifications']['in_app_settings']['enable_in_app_notifications']
		: true;

	return ( '1' === $enabled || true === $enabled || ( is_bool( $enabled ) && $enabled ) );
}

/**
 * Check if email notifications are enabled globally.
 *
 * @since 1.0.0
 *
 * @return bool True if email notifications are enabled, false otherwise.
 */
function splms_is_email_notifications_enabled() {
	$settings = SkillPulse_LMS_Settings::get_instance()->get_all_settings();
	$enabled  = isset( $settings['notifications']['email_settings']['enable_emails'] )
		? $settings['notifications']['email_settings']['enable_emails']
		: true;

	return ( '1' === $enabled || true === $enabled || ( is_bool( $enabled ) && $enabled ) );
}

/**
 * Get in-app notification setting value.
 *
 * @param string $setting_key Setting key (e.g., 'max_notifications_per_user').
 * @param mixed  $default_val Default value.
 *
 * @since 1.0.0
 *
 * @return mixed Setting value.
 */
function splms_get_in_app_notification_setting( $setting_key, $default_val = null ) {
	$settings = SkillPulse_LMS_Settings::get_instance()->get_all_settings();

	return isset( $settings['notifications']['in_app_settings'][ $setting_key ] )
		? $settings['notifications']['in_app_settings'][ $setting_key ]
		: $default_val;
}

/**
 * Build field to tab mapping from settings config.
 *
 * @since 1.0.0
 *
 * @return array Mapping of field_id => ['tab' => 'tab_id', 'section' => 'section_id'].
 */
function splms_build_field_to_tab_mapping() {
	return SkillPulse_LMS_Settings::get_instance()->get_field_to_tab_mapping();
}

/**
 * Get a setting value from SkillPulse LMS settings.
 *
 * @param string $setting_name  Setting name/key (field ID).
 * @param mixed  $default_value Default value if setting not found.
 *
 * @since 1.0.0
 *
 * @return mixed Setting value or default value.
 */
function splms_get_setting( $setting_name, $default_value = null ) {
	static $all_settings = null;

	if ( null === $all_settings ) {
		$all_settings = SkillPulse_LMS_Settings::get_instance()->get_all_settings();
	}

	$setting_value = $default_value;

	// All settings coming in tabs and settings name is the key.
	foreach ( $all_settings as $settings ) {
		if ( isset( $settings[ $setting_name ] ) ) {
			$setting_value = $settings[ $setting_name ];
		}

		foreach ( $settings as $key => $value ) {
			if ( isset( $value[ $setting_name ] ) ) {
				$setting_value = $value[ $setting_name ];
			}
		}
	}

	return apply_filters( 'splms_get_setting', $setting_value, $setting_name, $default_value );
}


/**
 * Get formatted price with currency symbol.
 *
 * @param float $price Price amount.
 *
 * @since 1.0.0
 *
 * @return string Formatted price.
 */
function get_splms_price_format( $price ) {
	$currency_position  = apply_filters( 'splms_currency_position', 'left' );
	$decimal_separator  = apply_filters( 'splms_price_decimal_separator', '.' );
	$thousand_separator = apply_filters( 'splms_price_thousand_separator', ',' );
	$decimals           = apply_filters( 'splms_price_decimals', 2 );

	$formatted_price = number_format( (float) $price, $decimals, $decimal_separator, $thousand_separator );

	if ( 'left' === $currency_position ) {
		$formatted_price = splms_get_currency_symbol() . $formatted_price;
	} else {
		$formatted_price = $formatted_price . splms_get_currency_symbol();
	}

	return apply_filters( 'splms_formatted_price', $formatted_price, $price );
}

/**
 * Format price with currency symbol (alias for get_splms_price_format).
 *
 * @param float $price Price amount.
 *
 * @since 1.0.0
 *
 * @return string Formatted price.
 */
function splms_format_price( $price ) {
	return get_splms_price_format( $price );
}

/**
 * Get currency symbol.
 *
 * @param string $currency_code Optional. Currency code (e.g., 'USD', 'EUR'). If provided, returns symbol for that currency.
 *
 * @since 1.0.0
 *
 * @return string Currency symbol.
 */
function splms_get_currency_symbol( $currency_code = '' ) {
	// If no currency code provided, get currency code from settings.
	if ( empty( $currency_code ) ) {
		$currency_code = splms_get_setting( 'currency', 'USD' );
	}

	// Currency symbols mapping.
	$currency_symbols = array(
		'USD' => '$',
		'EUR' => '€',
		'GBP' => '£',
		'JPY' => '¥',
		'AUD' => 'A$',
		'CAD' => 'C$',
		'INR' => '₹',
	);

	$currency_code = strtoupper( $currency_code );
	if ( isset( $currency_symbols[ $currency_code ] ) ) {
		return apply_filters( 'splms_currency_symbol', $currency_symbols[ $currency_code ], $currency_code );
	}

	// Fallback: return currency code with space if symbol not found.
	return apply_filters( 'splms_currency_symbol', $currency_code . ' ', $currency_code );
}


/**
 * Check if user has purchased a paid course.
 *
 * @param int $course_id Course ID.
 * @param int $user_id   User ID (optional, defaults to current user).
 *
 * @since 1.0.0
 *
 * @return bool True if user has purchased the course.
 */
function splms_has_user_purchased_course( $course_id, $user_id = null ) {
	if ( ! $user_id ) {
		$user_id = get_current_user_id();
	}

	if ( ! $user_id ) {
		return false;
	}

	// Use the new order access control system.
	if ( class_exists( 'SkillPulse_LMS_Order_Access_Control' ) ) {
		$access_status = SkillPulse_LMS_Order_Access_Control::get_course_access_status( $user_id, $course_id );

		return $access_status['has_access'] && 'paid' === $access_status['access_type'];
	}

	// Check if user is enrolled (which means they have access).
	$enrollment  = SkillPulse_LMS_Enrollment::get_instance();
	$is_enrolled = $enrollment->is_user_enrolled( $user_id, $course_id );

	if ( $is_enrolled ) {
		return true;
	}

	// Check order records for completed orders.
	$orders_query  = SkillPulse_LMS_Orders_Query::get_instance();
	$has_purchased = $orders_query->has_user_purchased_course( $user_id, $course_id );

	return $has_purchased;
}

/**
 * Get course purchase URL with secure token.
 *
 * @param int    $course_id     Course ID.
 * @param int    $user_id       User ID (optional, defaults to current user).
 * @param string $purchase_type Purchase type ('full_course' or 'sections').
 * @param array  $section_ids   Optional. Array of section IDs for section purchases.
 *
 * @since 1.0.0
 *
 * @return string Purchase URL. Returns empty string if user has membership access.
 */
function splms_get_course_purchase_url( $course_id, $user_id = null, $purchase_type = 'full_course', $section_ids = array() ) {
	// Check if paid courses are enabled globally.
	if ( ! splms_is_paid_courses_enabled() ) {
		return '';
	}

	// Check if payment is configured.
	if ( ! splms_is_payment_configured() ) {
		return '';
	}

	// If user has membership access, return empty (no purchase needed).
	if ( splms_user_has_membership_access_for_paid_course( $course_id, $user_id ) ) {
		return '';
	}

	// Generate secure purchase token.
	$token_manager = SkillPulse_LMS_Purchase_Token::get_instance();
	$token         = $token_manager->generate_token( $course_id, $purchase_type, $section_ids );

	if ( is_wp_error( $token ) ) {
		error_log( '[SPLMS] Failed to generate purchase token: ' . $token->get_error_message() ); // phpcs:ignore

		return '';
	}

	// Build secure purchase URL with token.
	$purchase_url = home_url( '/purchase/' );

	return add_query_arg( 'token', $token, $purchase_url );
}

/**
 * Validate purchase token and extract data.
 *
 * @param string $token Purchase token.
 *
 * @since 1.0.0
 *
 * @return array|WP_Error Token data array or WP_Error on failure.
 */
function splms_validate_purchase_token( $token ) {
	$token_manager = SkillPulse_LMS_Purchase_Token::get_instance();

	return $token_manager->validate_token( $token );
}

/**
 * Check if paid courses are enabled globally.
 *
 * @since 1.0.0
 *
 * @return bool True if paid courses are enabled, false otherwise.
 */
function splms_is_paid_courses_enabled() {
	return (bool) splms_get_setting( 'enable_paid_courses', true );
}

/**
 * Check if payment is configured.
 *
 * @since 1.0.0
 *
 * @return bool True if at least one payment method is configured.
 */
function splms_is_payment_configured() {
	// First check if paid courses are enabled globally.
	if ( ! splms_is_paid_courses_enabled() ) {
		return false;
	}

	$payment  = SkillPulse_LMS_Payment::get_instance();
	$gateways = $payment->get_gateways();

	// Check if any gateway is enabled and configured.
	foreach ( $gateways as $gateway ) {
		if ( $gateway['enabled'] && $gateway['configured'] ) {
			return true;
		}
	}

	return false;
}

/**
 * Is purchase page.
 *
 * Checks if the current page is the purchase page by verifying:
 * 1. WordPress page with slug 'purchase'
 * 2. OR URL contains '/purchase/' with either 'token' (new secure method)
 *
 * @since 1.0.0
 *
 * @return bool
 */
function is_purchase_page() {
	// Check if it's the purchase page by slug.
	if ( is_page( 'purchase' ) ) {
		return true;
	}

	// Check if URL contains '/purchase/' path.
	if ( ! isset( $_SERVER['REQUEST_URI'] ) ) {
		return false;
	}

	$request_uri = sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) );
	if ( false === strpos( $request_uri, '/purchase/' ) ) {
		return false;
	}

	// Check for either token (new secure method) or course_id.
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only conditional check.
	return isset( $_GET['token'] ) || isset( $_GET['course_id'] );
}

/**
 * Check if current page is a certificate page.
 *
 * @since 1.0.0
 *
 * @return bool True if certificate page, false otherwise.
 */
function is_certificate_page() {
	$component = get_query_var( 'splms_component' );
	return 'certificate' === $component;
}

/**
 * Render enrollment button with all access controls and validation.
 *
 * @param int   $course_id        Course ID.
 * @param int   $user_id          User ID (0 for non-logged-in users).
 * @param array $access_info      Course access information.
 * @param array $enrollment_dates Enrollment date information.
 * @param array $capacity_info    Course capacity information.
 * @param bool  $is_enrolled      Whether user is already enrolled.
 *
 * @since 1.0.0
 *
 * @return string HTML for enrollment button.
 */
function splms_render_enrollment_button( $course_id, $user_id = 0, $access_info = array(), $enrollment_dates = array(), $capacity_info = array(), $is_enrolled = false ) {
	// Parse and sanitize inputs.
	$access_info = wp_parse_args(
		$access_info,
		array(
			'course_access_type'  => 'public_free',
			'price'               => 0,
			'final_price'         => 0,
			'invited_users'       => array(),
			'prerequisite_course' => 0,
		)
	);

	$enrollment_dates = wp_parse_args(
		$enrollment_dates,
		array(
			'is_open'    => true,
			'start_date' => '',
			'end_date'   => '',
		)
	);

	$capacity_info = wp_parse_args(
		$capacity_info,
		array(
			'is_full'        => false,
			'current_count'  => 0,
			'max_enrollment' => 0,
		)
	);

	// If paid courses are disabled globally, treat all courses as free.
	if ( 'public_paid' === $access_info['course_access_type'] && ! splms_is_paid_courses_enabled() ) {
		$access_info['course_access_type'] = 'public_free';
	}

	// 1. Check if already enrolled.
	if ( $is_enrolled ) {
		return '<button class="btn btn-success" disabled>' . esc_html__( 'Already Enrolled', 'skillpulse-lms' ) . '</button>';
	}

	// 2. Check enrollment dates.
	if ( ! $enrollment_dates['is_open'] ) {
		return splms_render_enrollment_date_message( $enrollment_dates );
	}

	// 3. Check capacity.
	if ( $capacity_info['is_full'] ) {
		return '<button class="btn btn-secondary" disabled>' . esc_html__( 'Course Full', 'skillpulse-lms' ) . '</button>';
	}

	// 4. Check membership requirements.
	$membership_button = splms_check_membership_requirements( $course_id, $user_id, $access_info['course_access_type'] );
	if ( $membership_button ) {
		return $membership_button;
	}

	// 5. Render button based on access type.
	return splms_render_button_by_access_type( $course_id, $user_id, $access_info );
}

/**
 * Render enrollment date restriction message.
 *
 * @param array $enrollment_dates Enrollment date information.
 *
 * @since 1.0.0
 *
 * @return string HTML for date restriction message.
 */
function splms_render_enrollment_date_message( $enrollment_dates ) {
	$start_timestamp = strtotime( $enrollment_dates['start_date'] );
	$end_timestamp   = strtotime( $enrollment_dates['end_date'] );
	$current_time    = time();

	if ( $end_timestamp < $current_time ) {
		return '<button class="btn btn-secondary" disabled>' . esc_html__( 'Enrollment Closed', 'skillpulse-lms' ) . '</button>';
	}

	$message = sprintf(
	/* translators: 1: Start date, 2: End date. */
		esc_html__( 'Enrollment opens %1$s and closes %2$s.', 'skillpulse-lms' ),
		date_i18n( get_option( 'date_format' ), $start_timestamp ),
		date_i18n( get_option( 'date_format' ), $end_timestamp )
	);

	return '<button class="btn btn-secondary" disabled>' . $message . '</button>';
}

/**
 * Check membership requirements and return button if needed.
 *
 * @param int    $course_id          Course ID.
 * @param int    $user_id            User ID.
 * @param string $course_access_type Course access type.
 *
 * @since 1.0.0
 *
 * @return string|false Button HTML if membership check fails, false otherwise.
 */
function splms_check_membership_requirements( $course_id, $user_id, $course_access_type ) {
	// Membership requirements only apply to public_free and public_paid access types.
	$membership_applicable_types = array( 'public_free', 'public_paid' );

	if ( ! in_array( $course_access_type, $membership_applicable_types, true ) ) {
		return false;
	}

	$course_settings      = splms_get_course_settings( $course_id );
	$access_settings      = isset( $course_settings['course_access_settings'] ) ? $course_settings['course_access_settings'] : array();
	$required_memberships = isset( $access_settings['required_memberships'] ) ? $access_settings['required_memberships'] : array();

	// No membership restriction.
	if ( empty( $required_memberships ) ) {
		return false;
	}

	// User not logged in.
	if ( $user_id <= 0 ) {
		$login_url = wp_login_url( get_permalink( $course_id ) );

		return '<a href="' . esc_url( $login_url ) . '" class="btn btn-primary">' . esc_html__( 'Log In to Enroll', 'skillpulse-lms' ) . '</a>';
	}

	// User doesn't have required membership.
	if ( ! splms_user_has_required_membership( $user_id, $course_id ) ) {
		return splms_render_membership_required_message( $required_memberships );
	}

	// User has membership, continue enrollment flow.
	return false;
}

/**
 * Render enrollment button based on access type.
 *
 * @param int   $course_id   Course ID.
 * @param int   $user_id     User ID.
 * @param array $access_info Course access information.
 *
 * @since 1.0.0
 *
 * @return string Button HTML.
 */
function splms_render_button_by_access_type( $course_id, $user_id, $access_info ) {
	$course_access_type = $access_info['course_access_type'];

	switch ( $course_access_type ) {
		case 'public_free':
			return splms_render_free_course_button( $course_id, $user_id );

		case 'public_paid':
			return splms_render_paid_course_button( $course_id, $user_id );

		case 'invitation_only':
			return splms_render_invitation_button( $course_id, $user_id, $access_info );

		case 'prerequisite_required':
			return splms_render_prerequisite_button( $course_id, $user_id, $access_info );

		default:
			return splms_render_default_enroll_button( $course_id, $user_id );
	}
}

/**
 * Render free course enrollment button.
 *
 * @param int $course_id Course ID.
 * @param int $user_id   User ID.
 *
 * @since 1.0.0
 *
 * @return string Button HTML.
 */
function splms_render_free_course_button( $course_id, $user_id ) {
	if ( $user_id > 0 ) {
		return '<button class="btn btn-primary enroll-btn" data-course-id="' . esc_attr( $course_id ) . '">' .
				esc_html__( 'Enroll Now - Free', 'skillpulse-lms' ) .
				'</button>';
	}

	return '<button class="btn btn-primary start-learning-btn" data-course-id="' . esc_attr( $course_id ) . '">' .
			esc_html__( 'Start Learning', 'skillpulse-lms' ) .
			'</button>';
}

/**
 * Render paid course enrollment button.
 *
 * @param int $course_id Course ID.
 * @param int $user_id   User ID.
 *
 * @since 1.0.0
 *
 * @return string Button HTML.
 */
function splms_render_paid_course_button( $course_id, $user_id ) {
	// User not logged in.
	if ( $user_id <= 0 ) {
		if ( splms_is_payment_configured() ) {
			$login_url = wp_login_url( get_permalink() );

			return '<a href="' . esc_url( $login_url ) . '" class="btn btn-primary">' . esc_html__( 'Log In to Purchase', 'skillpulse-lms' ) . '</a>';
		}

		return '<button class="btn btn-secondary" disabled title="' . esc_attr__( 'Payment system not configured', 'skillpulse-lms' ) . '">' .
				esc_html__( 'Log In to Purchase', 'skillpulse-lms' ) .
				'</button>';
	}

	// Check if user has membership access (overrides payment).
	if ( splms_user_has_membership_access_for_paid_course( $course_id, $user_id ) ) {
		return '<button class="btn btn-primary enroll-btn" data-course-id="' . esc_attr( $course_id ) . '">' .
				esc_html__( 'Enroll Now', 'skillpulse-lms' ) .
				'</button>';
	}

	// Check if user has already purchased.
	if ( splms_has_user_purchased_course( $course_id, $user_id ) ) {
		return '<button class="btn btn-primary enroll-btn" data-course-id="' . esc_attr( $course_id ) . '">' .
				esc_html__( 'Enroll Now', 'skillpulse-lms' ) .
				'</button>';
	}

	// Check if payment is configured.
	if ( ! splms_is_payment_configured() ) {
		return '<button class="btn btn-secondary" disabled title="' . esc_attr__( 'Payment system not configured', 'skillpulse-lms' ) . '">' .
				esc_html__( 'Buy Now', 'skillpulse-lms' ) .
				'</button>';
	}

	// Show buy now button.
	$purchase_url = splms_get_course_purchase_url( $course_id, $user_id );

	// If purchase URL is empty (user has membership), show enroll button.
	if ( empty( $purchase_url ) ) {
		return '<button class="btn btn-primary enroll-btn" data-course-id="' . esc_attr( $course_id ) . '">' .
				esc_html__( 'Enroll Now', 'skillpulse-lms' ) .
				'</button>';
	}

	return '<a href="' . esc_url( $purchase_url ) . '" class="btn btn-primary buy-now-btn" data-course-id="' . esc_attr( $course_id ) . '">' .
			esc_html__( 'Buy Now', 'skillpulse-lms' ) .
			'</a>';
}

/**
 * Render invitation-only course button.
 *
 * @param int   $course_id   Course ID.
 * @param int   $user_id     User ID.
 * @param array $access_info Course access information.
 *
 * @since 1.0.0
 *
 * @return string Button HTML.
 */
function splms_render_invitation_button( $course_id, $user_id, $access_info ) {
	if ( $user_id <= 0 ) {
		$login_url = wp_login_url( get_permalink() );

		return '<a href="' . esc_url( $login_url ) . '" class="btn btn-primary">' . esc_html__( 'Log In to Check Access', 'skillpulse-lms' ) . '</a>';
	}

	$invited_users = isset( $access_info['invited_users'] ) ? $access_info['invited_users'] : array();

	if ( in_array( $user_id, $invited_users, true ) ) {
		return '<button class="btn btn-primary enroll-btn" data-course-id="' . esc_attr( $course_id ) . '">' .
				esc_html__( 'Enroll Now', 'skillpulse-lms' ) .
				'</button>';
	}

	return '<span class="access-denied">' . esc_html__( 'You are not invited to this course', 'skillpulse-lms' ) . '</span>';
}

/**
 * Render prerequisite course button.
 *
 * @param int   $course_id   Course ID.
 * @param int   $user_id     User ID.
 * @param array $access_info Course access information.
 *
 * @since 1.0.0
 *
 * @return string Button HTML.
 */
function splms_render_prerequisite_button( $course_id, $user_id, $access_info ) {
	if ( $user_id <= 0 ) {
		$login_url = wp_login_url( get_permalink() );

		return '<a href="' . esc_url( $login_url ) . '" class="btn btn-primary">' . esc_html__( 'Log In to Check Prerequisites', 'skillpulse-lms' ) . '</a>';
	}

	$prerequisite_course = isset( $access_info['prerequisite_course'] ) ? intval( $access_info['prerequisite_course'] ) : 0;

	if ( ! $prerequisite_course ) {
		return splms_render_default_enroll_button( $course_id, $user_id );
	}

	$enrollment_instance = SkillPulse_LMS_Enrollment::get_instance();

	if ( $enrollment_instance->has_user_completed_course( $user_id, $prerequisite_course ) ) {
		return '<button class="btn btn-primary enroll-btn" data-course-id="' . esc_attr( $course_id ) . '">' .
				esc_html__( 'Enroll Now', 'skillpulse-lms' ) .
				'</button>';
	}

	$prerequisite_url = get_permalink( $prerequisite_course );

	return '<a href="' . esc_url( $prerequisite_url ) . '" class="btn btn-primary">' .
			esc_html__( 'Complete Prerequisite', 'skillpulse-lms' ) .
			'</a>';
}

/**
 * Render default enrollment button.
 *
 * @param int $course_id Course ID.
 * @param int $user_id   User ID.
 *
 * @since 1.0.0
 *
 * @return string Button HTML.
 */
function splms_render_default_enroll_button( $course_id, $user_id ) {
	if ( $user_id > 0 ) {
		return '<button class="btn btn-primary enroll-btn" data-course-id="' . esc_attr( $course_id ) . '">' .
				esc_html__( 'Enroll Now', 'skillpulse-lms' ) .
				'</button>';
	}

	$login_url = wp_login_url( get_permalink() );

	return '<a href="' . esc_url( $login_url ) . '" class="btn btn-primary">' . esc_html__( 'Log In to Enroll', 'skillpulse-lms' ) . '</a>';
}

/**
 * Render course price display.
 *
 * @param array $access_info Course access information.
 *
 * @since 1.0.0
 *
 * @return string HTML for price display.
 */
function splms_render_course_price( $access_info = array() ) {
	// If paid courses are disabled globally, always show free.
	if ( ! splms_is_paid_courses_enabled() ) {
		return '<span class="price-free">' . esc_html__( 'Free', 'skillpulse-lms' ) . '</span>';
	}
	$access_info = wp_parse_args(
		$access_info,
		array(
			'price'       => 0,
			'final_price' => 0,
		)
	);

	$price_html = '';

	if ( ! empty( $access_info['final_price'] ) && $access_info['final_price'] < $access_info['price'] ) {
		$price_html  = '<span class="price-current">' . esc_html( get_splms_price_format( $access_info['final_price'] ) ) . '</span>';
		$price_html .= '<span class="price-regular">' . esc_html( get_splms_price_format( $access_info['price'] ) ) . '</span>';
	} elseif ( $access_info['price'] > 0 ) {
		$price_html = '<span class="price-current">' . esc_html( get_splms_price_format( $access_info['price'] ) ) . '</span>';
	} else {
		$price_html = '<span class="price-free">' . __( 'Free', 'skillpulse-lms' ) . '</span>';
	}

	return $price_html;
}

/**
 * Render access restriction info.
 *
 * @param string $access_type Course access type.
 *
 * @since 1.0.0
 *
 * @return string HTML for access restriction.
 */
function splms_render_access_restriction( $access_type ) {
	$restrictions = array(
		'invitation_only'       => array(
			'icon' => '📧',
			'text' => __( 'Invitation Only', 'skillpulse-lms' ),
		),
		'prerequisite_required' => array(
			'icon' => '📚',
			'text' => __( 'Prerequisite Required', 'skillpulse-lms' ),
		),
	);

	if ( isset( $restrictions[ $access_type ] ) ) {
		return '<div class="access-restriction">
                    <span class="restriction-icon">' . $restrictions[ $access_type ]['icon'] . '</span>
                    <span class="restriction-text">' . $restrictions[ $access_type ]['text'] . '</span>
                </div>';
	}

	return '';
}

/**
 * Get course type display data.
 *
 * @param array $access_info Course access information.
 *
 * @since 1.0.0
 *
 * @return array Course type data.
 */
function splms_get_course_type_data( $access_info = array() ) {
	if ( empty( $access_info ) || empty( $access_info['course_access_type'] ) ) {
		return array(
			'type'    => 'unknown',
			'label'   => __( 'Unknown', 'skillpulse-lms' ),
			'display' => __( 'Unknown Course Type', 'skillpulse-lms' ),
			'icon'    => 'help-circle',
			'class'   => 'course-type-unknown',
		);
	}

	$course_access_type = $access_info['course_access_type'];

	switch ( $course_access_type ) {
		case 'public_free':
			$course_type_data = array(
				'type'          => 'free',
				'label'         => __( 'Free Course', 'skillpulse-lms' ),
				'display'       => __( 'Free', 'skillpulse-lms' ),
				'icon'          => 'gift',
				'class'         => 'course-type-free',
				'price_display' => '<span class="price-free">' . __( 'Free', 'skillpulse-lms' ) . '</span>',
			);
			break;

		case 'public_paid':
			$course_type_data = array(
				'type'          => 'paid',
				'label'         => __( 'Paid Course', 'skillpulse-lms' ),
				'display'       => splms_render_course_price( $access_info ),
				'icon'          => 'credit-card',
				'class'         => 'course-type-paid',
				'price_display' => splms_render_course_price( $access_info ),
			);
			break;

		case 'invitation_only':
			$course_type_data = array(
				'type'                => 'invitation',
				'label'               => __( 'Invitation Only', 'skillpulse-lms' ),
				'display'             => __( 'Invitation Required', 'skillpulse-lms' ),
				'icon'                => 'mail',
				'class'               => 'course-type-invitation',
				'restriction_display' => splms_render_access_restriction( 'invitation_only' ),
			);
			break;

		case 'prerequisite_required':
			$prerequisite_course    = $access_info['prerequisite_course'] ?? 0;
			$user_id                = get_current_user_id();
			$prerequisite_completed = false;
			$prerequisite_title     = '';

			if ( $prerequisite_course ) {
				$prerequisite_title = get_the_title( $prerequisite_course );
				if ( $user_id > 0 ) {
					$prerequisite_completed = SkillPulse_LMS_Enrollment::get_instance()->has_user_completed_course( $user_id, $prerequisite_course );
				}
			}

			$course_type_data = array(
				'type'                   => 'prerequisite',
				'label'                  => __( 'Prerequisite Required', 'skillpulse-lms' ),
				'display'                => $prerequisite_completed ? __( 'Prerequisites Met - Ready to Enroll', 'skillpulse-lms' ) : __(
					'Prerequisites Required',
					'skillpulse-lms'
				),
				'icon'                   => 'layers',
				'class'                  => 'course-type-prerequisite' . ( $prerequisite_completed ? ' prerequisite-completed' : ' prerequisite-required' ),
				'prerequisite_course'    => $prerequisite_course,
				'prerequisite_title'     => $prerequisite_title,
				'prerequisite_completed' => $prerequisite_completed,
				'restriction_display'    => splms_render_access_restriction( 'prerequisite_required' ),
			);
			break;

		default:
			$course_type_data = array(
				'type'    => 'unknown',
				'label'   => __( 'Unknown', 'skillpulse-lms' ),
				'display' => esc_html( ucfirst( str_replace( '_', ' ', $course_access_type ) ) ),
				'icon'    => 'help-circle',
				'class'   => 'course-type-unknown',
			);
			break;
	}

	/**
	 * Filter course type data.
	 * Allows membership integrations and other modules to modify course type display.
	 *
	 * @param array $course_type_data Course type data array.
	 * @param array $access_info      Course access information.
	 *
	 * @since 1.0.0
	 */
	return apply_filters( 'splms_course_type_data', $course_type_data, $access_info );
}

/**
 * Get course start date information.
 *
 * @param int $course_id Course ID.
 *
 * @since 1.0.0
 *
 * @return array Array with start date info.
 */
function splms_get_course_start_date_info( $course_id = null ) {
	if ( ! $course_id ) {
		$course_id = get_the_ID();
	}

	$delivery_info = splms_get_course_delivery_info( $course_id );

	$start_date_info = array(
		'start_date'     => $delivery_info['course_start_date'] ?? '',
		'is_cohort'      => 'cohort' === $delivery_info['delivery_mode'],
		'is_available'   => true,
		'formatted_date' => '',
		'message'        => '',
	);

	// Only check start date for cohort-based courses.
	if ( $start_date_info['is_cohort'] && ! empty( $start_date_info['start_date'] ) ) {
		$start_timestamp = strtotime( $start_date_info['start_date'] );
		if ( false !== $start_timestamp ) {
			// phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested -- Using timestamp for date comparison.
			$current_timestamp                 = current_time( 'timestamp' );
			$start_date_info['is_available']   = $current_timestamp >= $start_timestamp;
			$start_date_info['formatted_date'] = date_i18n( get_option( 'date_format' ), $start_timestamp );

			if ( ! $start_date_info['is_available'] ) {
				$start_date_info['message'] = sprintf(
				/* translators: %s: Formatted start date. */
					__( 'Course content will be available starting %s.', 'skillpulse-lms' ),
					$start_date_info['formatted_date']
				);
			}
		}
	}

	return $start_date_info;
}

/**
 * Check if course content is available based on start date.
 *
 * @param int $course_id Course ID.
 *
 * @since 1.0.0
 *
 * @return bool True if content is available, false otherwise.
 */
function splms_is_course_content_available( $course_id = null ) {
	$start_date_info = splms_get_course_start_date_info( $course_id );

	return $start_date_info['is_available'];
}

/**
 * Generate share URL for social platform.
 *
 * @param int    $course_id Course ID.
 * @param string $platform  Social platform name.
 *
 * @since 1.0.0
 * @return string|false Share URL or false on failure.
 */
function splms_generate_social_share_url( $course_id, $platform ) {
	$course_url     = get_permalink( $course_id );
	$course_title   = get_the_title( $course_id );
	$course_excerpt = get_the_excerpt( $course_id );

	$encoded_url     = rawurlencode( $course_url );
	$encoded_title   = rawurlencode( $course_title );
	$encoded_excerpt = rawurlencode( $course_excerpt );

	switch ( $platform ) {
		case 'facebook':
			return "https://www.facebook.com/sharer/sharer.php?u={$encoded_url}";

		case 'twitter':
			return "https://twitter.com/intent/tweet?url={$encoded_url}&text={$encoded_title}";

		case 'linkedin':
			return "https://www.linkedin.com/sharing/share-offsite/?url={$encoded_url}";

		case 'whatsapp':
			return "https://wa.me/?text={$encoded_title}%20{$encoded_url}";

		case 'telegram':
			return "https://t.me/share/url?url={$encoded_url}&text={$encoded_title}";

		default:
			return false;
	}
}

/**
 * Check if user has required membership for course.
 *
 * @param int $user_id   User ID.
 * @param int $course_id Course ID.
 *
 * @since 1.0.0
 *
 * @return bool
 */
function splms_user_has_required_membership( $user_id, $course_id ) {
	return SkillPulse_LMS_Membership_Integration::check_user_course_access( $user_id, $course_id );
}

/**
 * Get all available memberships.
 *
 * @since 1.0.0
 *
 * @return array
 */
function splms_get_available_memberships() {
	return SkillPulse_LMS_Membership_Integration::get_all_membership_options();
}

/**
 * Check if any membership integration is active.
 *
 * @since 1.0.0
 *
 * @return bool
 */
function splms_has_membership_integration() {
	return SkillPulse_LMS_Membership_Integration::has_active();
}

/**
 * Render membership required message for course enrollment.
 *
 * @param array $required_memberships Array of required membership IDs (format: 'integration_id_membership_id').
 *
 * @since 1.0.0
 *
 * @return string HTML for membership required message.
 */
function splms_render_membership_required_message( $required_memberships ) {
	if ( empty( $required_memberships ) || ! is_array( $required_memberships ) ) {
		return '';
	}

	// Get all available memberships.
	$all_memberships = splms_get_available_memberships();
	$required_names  = array();

	// Match required membership IDs with available memberships.
	foreach ( $required_memberships as $membership_id ) {
		foreach ( $all_memberships as $membership ) {
			if ( isset( $membership['id'] ) && $membership['id'] === $membership_id ) {
				$required_names[] = array(
					'name' => $membership['name'],
					'id'   => $membership_id,
				);
				break;
			}
		}
	}

	if ( empty( $required_names ) ) {
		return '';
	}

	// Build membership list HTML.
	$membership_list = array();
	$purchase_urls   = array();

	foreach ( $required_names as $membership ) {
		$membership_list[] = esc_html( $membership['name'] );
		$purchase_url      = splms_get_membership_purchase_url( $membership['id'] );
		if ( $purchase_url ) {
			$purchase_urls[ $membership['id'] ] = $purchase_url;
		}
	}

	// Simple, clean membership required message.
	$html = '<div class="splms-membership-required">';

	foreach ( $required_names as $membership ) {
		// Add purchase link if available.
		if ( isset( $purchase_urls[ $membership['id'] ] ) ) {
			$html .= ' <a href="' . esc_url( $purchase_urls[ $membership['id'] ] ) . '" class="btn btn-primary btn-sm">' . esc_html__( 'Purchase', 'skillpulse-lms' ) . '</a>';
		}
	}

	$html .= '</div>';

	return $html;
}

/**
 * Check if user has membership access that overrides payment requirement for paid courses.
 *
 * This function checks if a course is public_paid and if the user has a required membership
 * that grants them access without needing to pay. This is used to bypass checkout for members.
 *
 * @param int $course_id Course ID.
 * @param int $user_id   User ID (optional, defaults to current user).
 *
 * @since 1.0.0
 *
 * @return bool True if user has membership access that overrides payment, false otherwise.
 */
function splms_user_has_membership_access_for_paid_course( $course_id, $user_id = null ) {
	if ( ! $user_id ) {
		$user_id = get_current_user_id();
	}

	// Must be logged in.
	if ( $user_id <= 0 ) {
		return false;
	}

	// Get course settings.
	$course_settings    = splms_get_course_settings( $course_id );
	$access_settings    = isset( $course_settings['course_access_settings'] ) ? $course_settings['course_access_settings'] : array();
	$course_access_type = isset( $access_settings['course_access_type'] ) ? $access_settings['course_access_type'] : 'public_free';

	// Only check membership for public_paid courses.
	if ( 'public_paid' !== $course_access_type ) {
		return false;
	}

	// Check if course has required memberships.
	$required_memberships = isset( $access_settings['required_memberships'] ) ? $access_settings['required_memberships'] : array();
	if ( empty( $required_memberships ) ) {
		return false;
	}

	// Check if user has required membership.
	return splms_user_has_required_membership( $user_id, $course_id );
}

/**
 * Get membership purchase URL from active integration.
 *
 * @param string $membership_id Membership ID (format: 'integration_id_membership_id').
 *
 * @since 1.0.0
 *
 * @return string|false Purchase URL or false if not available.
 */
function splms_get_membership_purchase_url( $membership_id ) {
	if ( empty( $membership_id ) || ! is_string( $membership_id ) ) {
		return false;
	}

	// Parse membership ID (format: "memberpress_123" or "woocommerce_456"). // phpcs:ignore Squiz.PHP.CommentedOutCode.Found -- This is a documentation comment, not commented code.
	$parts = explode( '_', $membership_id, 2 );
	if ( count( $parts ) !== 2 ) {
		return false;
	}

	$integration_id         = $parts[0];
	$membership_internal_id = $parts[1];

	// Get integration.
	$integration = SkillPulse_LMS_Membership_Integration::get( $integration_id );
	if ( ! $integration || ! $integration->is_plugin_active() ) {
		return false;
	}

	// Get purchase URL from integration (if method exists).
	if ( method_exists( $integration, 'get_membership_purchase_url' ) ) {
		return $integration->get_membership_purchase_url( $membership_internal_id );
	}

	// Fallback: Try to construct URL based on integration.
	return false;
}

/**
 * Get section pricing data with effective pricing and access status.
 *
 * @param int $section_id Section ID.
 * @param int $user_id    User ID (optional, defaults to current user).
 *
 * @since 1.0.0
 *
 * @return array Section pricing data with access status.
 */
function splms_get_section_pricing_with_access( $section_id, $user_id = null ) {
	if ( ! $user_id ) {
		$user_id = get_current_user_id();
	}

	// Get section pricing meta.
	$pricing = get_post_meta( $section_id, '_splms_section_pricing', true );

	$defaults = array(
		'is_free'                   => false,
		'price'                     => 0,
		'sale_price'                => 0,
		'sale_start_date'           => '',
		'sale_end_date'             => '',
		'preview_enabled'           => false,
		'preview_items'             => array(
			'lessons'     => array(),
			'quizzes'     => array(),
			'assessments' => array(),
		),
		'requires_previous_section' => false,
	);

	$pricing = is_array( $pricing ) ? wp_parse_args( $pricing, $defaults ) : $defaults;

	// Calculate effective price.
	$is_on_sale      = false;
	$effective_price = floatval( $pricing['price'] );

	if ( $pricing['sale_price'] > 0 ) {
		$current_time = time();
		$sale_active  = true;

		// Check sale start date.
		if ( ! empty( $pricing['sale_start_date'] ) ) {
			$sale_start  = strtotime( $pricing['sale_start_date'] );
			$sale_active = $sale_active && ( $current_time >= $sale_start );
		}

		// Check sale end date.
		if ( ! empty( $pricing['sale_end_date'] ) ) {
			$sale_end    = strtotime( $pricing['sale_end_date'] );
			$sale_active = $sale_active && ( $current_time <= $sale_end );
		}

		if ( $sale_active ) {
			$is_on_sale      = true;
			$effective_price = floatval( $pricing['sale_price'] );
		}
	}

	$pricing['is_on_sale']      = $is_on_sale;
	$pricing['effective_price'] = $effective_price;

	// Check if user has purchased/has access to this section.
	$pricing['user_has_access'] = splms_user_has_section_access( $section_id, $user_id );

	return $pricing;
}

/**
 * Get the parent section ID for a lesson or quiz.
 *
 * @param int $item_id Lesson or quiz ID.
 *
 * @since 1.0.0
 *
 * @return int Section ID or 0 if not found.
 */
function splms_get_item_section( $item_id ) {
	if ( ! $item_id ) {
		return 0;
	}

	// Use relationships query to get parent.
	$relationships_query = SkillPulse_LMS_Relationships_Query::get_instance();
	$parents             = $relationships_query->get_parents( $item_id );

	if ( empty( $parents ) ) {
		return 0;
	}

	// Check each parent to find the section.
	foreach ( $parents as $parent ) {
		$parent_post = get_post( $parent->parent_id );
		if ( $parent_post && SPLMS_POST_TYPES['section'] === $parent_post->post_type ) {
			return intval( $parent->parent_id );
		}
	}

	return 0;
}

/**
 * Check if user has access to a specific section (section-based pricing).
 *
 * @param int $section_id Section ID.
 * @param int $user_id    User ID (optional, defaults to current user).
 *
 * @since 1.0.0
 *
 * @return bool True if user has access to the section.
 */
function splms_user_has_section_access( $section_id, $user_id = null ) {
	if ( ! $user_id ) {
		$user_id = get_current_user_id();
	}

	if ( ! $user_id ) {
		return false;
	}

	// Check if section-based pricing is enabled globally.
	$section_pricing_enabled = splms_get_setting( 'enable_section_based_pricing', false );

	if ( ! $section_pricing_enabled ) {
		return false; // Feature disabled, no individual section access.
	}

	// Get section pricing.
	$section_pricing = get_post_meta( $section_id, '_splms_section_pricing', true );

	// If no pricing configured, section is course-only.
	if ( empty( $section_pricing ) ) {
		return false;
	}

	// If section is free or has zero price, allow access.
	if ( ! empty( $section_pricing['is_free'] ) ) {
		return true;
	}

	// Check if effective price is 0 (treat as free).
	$price      = floatval( $section_pricing['price'] ?? 0 );
	$sale_price = floatval( $section_pricing['sale_price'] ?? 0 );

	// Determine effective price considering sale.
	$effective_price = $price;
	if ( $sale_price > 0 ) {
		$current_time = time();
		$sale_active  = true;

		// Check sale start date.
		if ( ! empty( $section_pricing['sale_start_date'] ) ) {
			$sale_start  = strtotime( $section_pricing['sale_start_date'] );
			$sale_active = $sale_active && ( $current_time >= $sale_start );
		}

		// Check sale end date.
		if ( ! empty( $section_pricing['sale_end_date'] ) ) {
			$sale_end    = strtotime( $section_pricing['sale_end_date'] );
			$sale_active = $sale_active && ( $current_time <= $sale_end );
		}

		if ( $sale_active ) {
			$effective_price = $sale_price;
		}
	}

	// If effective price is 0, treat as free.
	if ( 0 === $effective_price ) {
		return true;
	}

	// Use Section Access Query class for database operations.
	$section_access_query = SkillPulse_LMS_Section_Access_Query::get_instance();
	return $section_access_query->user_has_access( $user_id, $section_id );
}

/**
 * Get user's purchased sections for a course.
 *
 * @param int $course_id Course ID.
 * @param int $user_id   User ID (optional, defaults to current user).
 *
 * @since 1.0.0
 *
 * @return array Array of section IDs user has access to.
 */
function splms_get_user_purchased_sections( $course_id, $user_id = null ) {
	if ( ! $user_id ) {
		$user_id = get_current_user_id();
	}

	if ( ! $user_id ) {
		return array();
	}

	// Use Section Access Query class for database operations.
	$section_access_query = SkillPulse_LMS_Section_Access_Query::get_instance();
	return $section_access_query->get_user_course_sections( $user_id, $course_id );
}

/**
 * Get section purchase URL with secure token.
 *
 * @param int $section_id Section ID.
 * @param int $course_id  Course ID.
 *
 * @since 1.0.0
 *
 * @return string Purchase URL.
 */
function splms_get_section_purchase_url( $section_id, $course_id ) {
	// Use the token-based URL generation for section purchases.
	return splms_get_course_purchase_url( $course_id, get_current_user_id(), 'sections', array( $section_id ) );
}

/**
 * Check if course has section-based pricing enabled.
 * Returns true if global setting is enabled AND course has sections with pricing configured.
 *
 * @param int $course_id Course ID (optional, defaults to current course).
 *
 * @since 1.0.0
 *
 * @return bool True if course has section-based pricing.
 */
function splms_course_uses_section_pricing( $course_id = null ) {
	if ( ! $course_id ) {
		$course_id = get_the_ID();
	}

	// Check if section-based pricing is enabled globally.
	if ( ! splms_get_setting( 'enable_section_based_pricing', false ) ) {
		return false;
	}

	// Check if course has sections with pricing configured.
	$course_items_query = SkillPulse_LMS_Course_Items_Query::get_instance();
	$course_items       = $course_items_query->get_items( $course_id );

	foreach ( $course_items as $course_item ) {
		if ( SPLMS_POST_TYPES['section'] !== $course_item->item_type ) {
			continue;
		}

		$section_pricing = get_post_meta( $course_item->item_id, '_splms_section_pricing', true );

		// If section has pricing configured (not free and has price > 0).
		if ( is_array( $section_pricing ) &&
			! ( isset( $section_pricing['is_free'] ) && true === $section_pricing['is_free'] ) &&
			isset( $section_pricing['price'] ) &&
			floatval( $section_pricing['price'] ) > 0
		) {
			return true; // Found at least one paid section.
		}
	}

	return false; // No paid sections found.
}

/**
 * Get section pricing summary for a course.
 *
 * @param int $course_id Course ID.
 * @param int $user_id   User ID (optional, defaults to current user).
 *
 * @since 1.0.0
 *
 * @return array Array with pricing summary data.
 */
function splms_get_course_section_pricing_summary( $course_id, $user_id = null ) {
	if ( ! $user_id ) {
		$user_id = get_current_user_id();
	}

	// Get all sections for this course.
	$course_items_query = SkillPulse_LMS_Course_Items_Query::get_instance();
	$course_items       = $course_items_query->get_items( $course_id );

	$summary = array(
		'total_sections'     => 0,
		'priced_sections'    => 0,
		'free_sections'      => 0,
		'purchased_sections' => 0,
		'total_price'        => 0,
		'sections'           => array(),
	);

	$purchased_section_ids = splms_get_user_purchased_sections( $course_id, $user_id );

	foreach ( $course_items as $course_item ) {
		if ( SPLMS_POST_TYPES['section'] !== $course_item->item_type ) {
			continue;
		}

		$section_id      = $course_item->item_id;
		$section_pricing = splms_get_section_pricing_with_access( $section_id, $user_id );

		++$summary['total_sections'];

		// Check if section has pricing configured.
		$has_pricing = $section_pricing['is_free'] || floatval( $section_pricing['price'] ) > 0;

		if ( $has_pricing ) {
			if ( $section_pricing['is_free'] ) {
				++$summary['free_sections'];
			} else {
				++$summary['priced_sections'];
				$summary['total_price'] += floatval( $section_pricing['effective_price'] );
			}
		}

		if ( in_array( (int) $section_id, $purchased_section_ids, true ) ) {
			++$summary['purchased_sections'];
		}

		$section_stats    = splms_get_section_content_stats( $section_id );
		$section_duration = splms_get_section_duration( $section_id );

		$summary['sections'][] = array(
			'section_id'      => $section_id,
			'title'           => get_the_title( $section_id ),
			'pricing'         => $section_pricing,
			'has_pricing'     => $has_pricing,
			'is_purchased'    => in_array( (int) $section_id, $purchased_section_ids, true ),
			'user_has_access' => $section_pricing['user_has_access'],
			'stats'           => $section_stats,
			'duration'        => $section_duration,
			'permalink'       => get_permalink( $section_id ),
		);
	}

	return $summary;
}

/**
 * Calculate upgrade price from purchased sections to full course.
 *
 * This function calculates how much a user needs to pay to upgrade from
 * their purchased sections to the full course, giving them credit for
 * sections they've already paid for.
 *
 * @param int $course_id Course ID.
 * @param int $user_id   User ID (optional, defaults to current user).
 *
 * @since 1.0.0
 *
 * @return array Upgrade pricing data with breakdown.
 */
function splms_calculate_upgrade_price( $course_id, $user_id = null ) {
	if ( ! $user_id ) {
		$user_id = get_current_user_id();
	}

	if ( ! $user_id ) {
		return array(
			'full_course_price'      => 0,
			'already_paid'           => 0,
			'upgrade_price'          => 0,
			'purchased_sections'     => array(),
			'has_purchased_sections' => false,
			'is_upgrade_available'   => false,
		);
	}

	// Get full course price.
	$course_info       = splms_get_course_access_info( $course_id );
	$full_course_price = floatval( $course_info['final_price'] ?? $course_info['price'] ?? 0 );

	// Get user's purchased sections.
	$purchased_section_ids = splms_get_user_purchased_sections( $course_id, $user_id );

	// Calculate total already paid.
	$total_paid         = 0;
	$purchased_sections = array();
	$section_order_ids  = array();

	foreach ( $purchased_section_ids as $section_id ) {
		$section_pricing = get_post_meta( $section_id, '_splms_section_pricing', true );

		if ( ! is_array( $section_pricing ) ) {
			continue;
		}

		// Get the actual paid price (could be sale price or regular price).
		$section_price = floatval( $section_pricing['price'] ?? 0 );

		// Check if user bought it on sale.
		$sale_price = floatval( $section_pricing['sale_price'] ?? 0 );
		if ( $sale_price > 0 && $sale_price < $section_price ) {
			$paid_price = $sale_price;
		} else {
			$paid_price = $section_price;
		}

		$total_paid += $paid_price;

		$purchased_sections[] = array(
			'section_id'    => $section_id,
			'section_title' => get_the_title( $section_id ),
			'paid_price'    => $paid_price,
		);
	}

	// Calculate upgrade price (never negative).
	$upgrade_price = max( 0, $full_course_price - $total_paid );

	// Check if upgrade is available.
	$is_upgrade_available = count( $purchased_section_ids ) > 0 && $full_course_price > 0;

	// Check if user paid more than course price (offer free upgrade).
	$paid_more_than_course = $total_paid >= $full_course_price;

	return array(
		'full_course_price'      => $full_course_price,
		'already_paid'           => $total_paid,
		'upgrade_price'          => $upgrade_price,
		'purchased_sections'     => $purchased_sections,
		'has_purchased_sections' => count( $purchased_section_ids ) > 0,
		'is_upgrade_available'   => $is_upgrade_available,
		'paid_more_than_course'  => $paid_more_than_course,
		'savings'                => $total_paid,
		'section_count'          => count( $purchased_section_ids ),
	);
}

/**
 * Check if user should see upgrade option for full course.
 *
 * @param int $course_id Course ID.
 * @param int $user_id   User ID (optional, defaults to current user).
 *
 * @since 1.0.0
 *
 * @return bool True if user should see upgrade option.
 */
function splms_user_should_see_upgrade_option( $course_id, $user_id = null ) {
	if ( ! $user_id ) {
		$user_id = get_current_user_id();
	}

	if ( ! $user_id ) {
		return false;
	}

	// Check if user already has full course access.
	if ( splms_has_user_purchased_course( $course_id, $user_id ) ) {
		return false;
	}

	// Check if course uses section pricing.
	if ( ! splms_course_uses_section_pricing( $course_id ) ) {
		return false;
	}

	// Check if user has purchased any sections.
	$purchased_sections = splms_get_user_purchased_sections( $course_id, $user_id );

	return count( $purchased_sections ) > 0;
}

/**
 * Get course curriculum with comprehensive access control data.
 *
 * This function provides a centralized way to get course curriculum data
 * with all access control logic applied consistently.
 *
 * @param int   $course_id Course ID.
 * @param int   $user_id   User ID (optional, defaults to current user).
 * @param array $options   Options array.
 *
 * @since 1.0.0
 *
 * @return array Curriculum data with access control information.
 */
function splms_get_course_curriculum( $course_id, $user_id = null, $options = array() ) {
	if ( null === $user_id ) {
		$user_id = get_current_user_id();
	}

	$defaults = array(
		'include_access'   => true,
		'include_stats'    => true,
		'include_meta'     => true,
		'check_completion' => true,
		'include_lessons'  => true,
		'include_quizzes'  => true,
	);
	$options  = wp_parse_args( $options, $defaults );

	$course_items_query = SkillPulse_LMS_Course_Items_Query::get_instance();

	$curriculum_data = $course_items_query->get_course_curriculum(
		$course_id,
		array(
			'user_id'          => $user_id,
			'check_access'     => $options['include_access'],
			'check_completion' => $options['check_completion'],
			'format'           => 'nested',
			'include_lessons'  => $options['include_lessons'],
			'include_quizzes'  => $options['include_quizzes'],
			'calculate_stats'  => $options['include_stats'],
			'fields'           => array( 'has_access', 'is_locked', 'access_meta', 'permalink', 'completed', 'description' ),
		)
	);

	return $curriculum_data;
}

/**
 * Get section curriculum with comprehensive access control data.
 *
 * This function provides a centralized way to get section curriculum data
 * with all access control logic applied consistently.
 *
 * @param int   $section_id Section ID.
 * @param int   $user_id    User ID (optional, defaults to current user).
 * @param array $options    Options array.
 *
 * @since 1.0.0
 *
 * @return array Section curriculum data with access control information.
 */
function splms_get_section_curriculum( $section_id, $user_id = null, $options = array() ) {
	if ( null === $user_id ) {
		$user_id = get_current_user_id();
	}

	$defaults = array(
		'include_access'   => true,
		'include_stats'    => true,
		'include_meta'     => true,
		'check_completion' => true,
		'include_lessons'  => true,
		'include_quizzes'  => true,
	);
	$options  = wp_parse_args( $options, $defaults );

	// Use course items query to get section data directly.
	$course_items_query = SkillPulse_LMS_Course_Items_Query::get_instance();

	// Get course ID from section using existing method.
	$course_id = $course_items_query->get_item_course_id( $section_id, SPLMS_POST_TYPES['section'] );
	if ( ! $course_id ) {
		return array(
			'section_id'  => $section_id,
			'course_id'   => 0,
			'title'       => get_the_title( $section_id ),
			'has_access'  => false,
			'is_locked'   => true,
			'access_meta' => array(),
			'items'       => array(),
			'stats'       => array(
				'total_items'   => 0,
				'total_lessons' => 0,
				'total_quizzes' => 0,
			),
		);
	}

	// Get section curriculum data efficiently - just this section.
	$section_curriculum = $course_items_query->get_course_curriculum(
		$course_id,
		array(
			'user_id'          => $user_id,
			'check_access'     => $options['include_access'],
			'check_completion' => $options['check_completion'],
			'format'           => 'nested',
			'include_lessons'  => $options['include_lessons'],
			'include_quizzes'  => $options['include_quizzes'],
			'calculate_stats'  => $options['include_stats'],
			'section_id'       => $section_id, // ✅ Fetch only this specific section!
			'fields'           => array( 'has_access', 'is_locked', 'access_meta', 'permalink', 'completed', 'description' ),
		)
	);

	// Extract the section data.
	if ( isset( $section_curriculum['sections'] ) && ! empty( $section_curriculum['sections'] ) ) {
		$section = $section_curriculum['sections'][0]; // Should only be one section.
		$items   = $section['children'] ?? array();

		// Calculate section stats.
		$total_lessons = 0;
		$total_quizzes = 0;
		foreach ( $items as $item ) {
			if ( SPLMS_POST_TYPES['lesson'] === $item['type'] ) {
				++$total_lessons;
			} elseif ( SPLMS_POST_TYPES['quiz'] === $item['type'] ) {
				++$total_quizzes;
			}
		}

		return array(
			'section_id'  => $section_id,
			'course_id'   => $course_id,
			'title'       => $section['title'] ?? get_the_title( $section_id ),
			'has_access'  => $section['has_access'] ?? false,
			'is_locked'   => $section['is_locked'] ?? true,
			'access_meta' => $section['access_meta'] ?? array(),
			'items'       => $items,
			'stats'       => array(
				'total_items'   => count( $items ),
				'total_lessons' => $total_lessons,
				'total_quizzes' => $total_quizzes,
			),
		);
	}

	// Section not found or no data.
	return array(
		'section_id'  => $section_id,
		'course_id'   => $course_id,
		'title'       => get_the_title( $section_id ),
		'has_access'  => false,
		'is_locked'   => true,
		'access_meta' => array(),
		'items'       => array(),
		'stats'       => array(
			'total_items'   => 0,
			'total_lessons' => 0,
			'total_quizzes' => 0,
		),
	);
}

/**
 * Get course sections for navigation purposes (lightweight).
 *
 * Returns only section basic data needed for navigation without
 * expensive access control checks or detailed item data.
 *
 * @since 1.0.0
 *
 * @param int $course_id Course ID.
 * @return array Array of sections with id, title, permalink, and item_count.
 */
function splms_get_course_sections_for_navigation( $course_id ) {
	if ( empty( $course_id ) ) {
		return array();
	}

	$course_items_query = SkillPulse_LMS_Course_Items_Query::get_instance();

	// Get sections with minimal data - no access checks, no item details.
	$sections = $course_items_query->get_course_curriculum(
		$course_id,
		array(
			'format'           => 'nested',
			'check_access'     => false, // Skip access control for navigation.
			'check_completion' => false, // Skip completion checks for navigation.
			'include_lessons'  => true,
			'include_quizzes'  => true,
			'calculate_stats'  => false, // Skip stats calculation.
			'fields'           => array( 'id', 'title', 'permalink' ), // Minimal fields only.
		)
	);

	$navigation_sections = array();

	if ( isset( $sections['sections'] ) && is_array( $sections['sections'] ) ) {
		foreach ( $sections['sections'] as $section ) {
			$item_count = 0;

			// Count children for display purposes.
			if ( isset( $section['children'] ) && is_array( $section['children'] ) ) {
				$item_count = count( $section['children'] );
			}

			$navigation_sections[] = array(
				'id'         => $section['id'],
				'title'      => $section['title'],
				'permalink'  => isset( $section['permalink'] ) ? $section['permalink'] : get_permalink( $section['id'] ),
				'item_count' => $item_count,
			);
		}
	}

	return $navigation_sections;
}

if ( ! function_exists( 'splms_get_course_progress_data' ) ) {
	/**
	 * Get unified course progress data for progress bar component.
	 *
	 * @since 1.0.0
	 *
	 * @param int $user_id   User ID.
	 * @param int $course_id Course ID.
	 * @return array Progress data with percentage, completed, and total.
	 */
	function splms_get_course_progress_data( $user_id, $course_id ) {
		if ( empty( $user_id ) || empty( $course_id ) ) {
			return array(
				'percentage' => 0,
				'completed'  => 0,
				'total'      => 0,
			);
		}

		// Check if user is enrolled.
		$is_enrolled = splms_is_user_enrolled( $course_id, $user_id );
		if ( ! $is_enrolled ) {
			return array(
				'percentage' => 0,
				'completed'  => 0,
				'total'      => 0,
			);
		}

		// Use the unified course curriculum method for consistency.
		$curriculum_result = splms_get_course_curriculum( $course_id, $user_id, array( 'include_stats' => true ) );

		if ( empty( $curriculum_result ) || ! isset( $curriculum_result['stats'] ) ) {
			// Fallback to lessons instance method.
			$lessons_instance = SkillPulse_LMS_Lessons::get_instance();
			$progress_data    = $lessons_instance->calculate_course_progress( $user_id, $course_id );

			return array(
				'percentage' => isset( $progress_data['percentage'] ) ? floatval( $progress_data['percentage'] ) : 0,
				'completed'  => isset( $progress_data['completed'] ) ? intval( $progress_data['completed'] ) : 0,
				'total'      => isset( $progress_data['total'] ) ? intval( $progress_data['total'] ) : 0,
			);
		}

		$stats = $curriculum_result['stats'];

		return array(
			'percentage' => isset( $stats['percentage'] ) ? floatval( $stats['percentage'] ) : 0,
			'completed'  => isset( $stats['completed'] ) ? intval( $stats['completed'] ) : 0,
			'total'      => isset( $stats['total_items'] ) ? intval( $stats['total_items'] ) : 0,
		);
	}
}
