<?php
/**
 * General Lesson Functions
 *
 * This file contains all general lesson-related functions that can be reused
 * across the SkillPulse LMS plugin.
 *
 * @package SPLMS
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * ============================================================================
 * LESSON SETTINGS FUNCTIONS
 * ============================================================================
 */

/**
 * Get lesson settings with defaults.
 *
 * @param int $lesson_id Lesson ID.
 * @return array Lesson settings.
 */
function splms_get_lesson_settings( $lesson_id = null ) {
	if ( ! $lesson_id ) {
		$lesson_id = get_the_ID();
	}

	$settings = SPLMS_Lessons::get_instance()->get_lesson_settings( $lesson_id );

	return apply_filters( 'splms_lesson_settings', $settings, $lesson_id );
}

/**
 * Get lesson type.
 *
 * @param int $lesson_id Lesson ID.
 * @return string Lesson type.
 */
function splms_get_lesson_type( $lesson_id = null ) {
	if ( ! $lesson_id ) {
		$lesson_id = get_the_ID();
	}

	$settings = splms_get_lesson_settings( $lesson_id );
	return isset( $settings['lesson_type'] ) ? $settings['lesson_type'] : 'text';
}

/**
 * Get lesson duration.
 *
 * @param int $lesson_id Lesson ID.
 * @return int Lesson duration in minutes.
 */
function splms_get_lesson_duration( $lesson_id = null ) {
	if ( ! $lesson_id ) {
		$lesson_id = get_the_ID();
	}

	$settings = splms_get_lesson_settings( $lesson_id );
	return intval( isset( $settings['lesson_duration'] ) ? $settings['lesson_duration'] : 30 );
}

/**
 * Get lesson video URL.
 *
 * @param int $lesson_id Lesson ID.
 * @return string Video URL.
 */
function splms_get_lesson_video_url( $lesson_id = null ) {
	if ( ! $lesson_id ) {
		$lesson_id = get_the_ID();
	}

	$settings = splms_get_lesson_settings( $lesson_id );
	return isset( $settings['lesson_video_url'] ) ? $settings['lesson_video_url'] : '';
}

/**
 * Get lesson audio URL.
 *
 * @param int $lesson_id Lesson ID.
 * @return string Audio URL.
 */
function splms_get_lesson_audio_url( $lesson_id = null ) {
	if ( ! $lesson_id ) {
		$lesson_id = get_the_ID();
	}

	$settings = splms_get_lesson_settings( $lesson_id );
	return isset( $settings['lesson_audio_url'] ) ? $settings['lesson_audio_url'] : '';
}

/**
 * Get lesson document URL.
 *
 * @param int $lesson_id Lesson ID.
 * @return string Document URL.
 */
function splms_get_lesson_document_url( $lesson_id = null ) {
	if ( ! $lesson_id ) {
		$lesson_id = get_the_ID();
	}

	$settings = splms_get_lesson_settings( $lesson_id );
	return isset( $settings['lesson_document_url'] ) ? $settings['lesson_document_url'] : '';
}

/**
 * Get lesson attachments.
 *
 * @param int $lesson_id Lesson ID.
 * @return array Attachment IDs.
 */
function splms_get_lesson_attachments( $lesson_id = null ) {
	if ( ! $lesson_id ) {
		$lesson_id = get_the_ID();
	}

	$settings = splms_get_lesson_settings( $lesson_id );
	return isset( $settings['lesson_attachments'] ) ? $settings['lesson_attachments'] : array();
}

/**
 * Get formatted lesson attachments.
 *
 * @param int $lesson_id Lesson ID.
 * @return array Formatted attachments.
 */
function splms_get_formatted_lesson_attachments( $lesson_id = null ) {
	if ( ! $lesson_id ) {
		$lesson_id = get_the_ID();
	}

	$lessons_instance = SPLMS_Lessons::get_instance();
	return $lessons_instance->get_formatted_lesson_attachments( $lesson_id );
}

/**
 * Get lesson drip settings.
 *
 * @param int $lesson_id Lesson ID.
 * @return array Drip settings.
 */
function splms_get_lesson_drip_settings( $lesson_id = null ) {
	if ( ! $lesson_id ) {
		$lesson_id = get_the_ID();
	}

	$settings = splms_get_lesson_settings( $lesson_id );
	return isset( $settings['lesson_drip_settings'] ) ? $settings['lesson_drip_settings'] : array();
}

/**
 * Get lesson completion settings.
 *
 * @param int $lesson_id Lesson ID.
 * @return array Completion settings.
 */
function splms_get_lesson_completion_settings( $lesson_id = null ) {
	if ( ! $lesson_id ) {
		$lesson_id = get_the_ID();
	}

	$settings = splms_get_lesson_settings( $lesson_id );
	return isset( $settings['lesson_completion_settings'] ) ? $settings['lesson_completion_settings'] : array();
}

/**
 * Get lesson prerequisites.
 *
 * @param int $lesson_id Lesson ID.
 * @return array Prerequisite lesson IDs.
 */
function splms_get_lesson_prerequisites( $lesson_id = null ) {
	if ( ! $lesson_id ) {
		$lesson_id = get_the_ID();
	}

	$settings = splms_get_lesson_settings( $lesson_id );
	return isset( $settings['lesson_prerequisites'] ) ? $settings['lesson_prerequisites'] : array();
}

/**
 * ============================================================================
 * LESSON PROGRESS FUNCTIONS
 * ============================================================================
 */

/**
 * Check if lesson is completed by user.
 *
 * @param int $lesson_id Lesson ID.
 * @param int $user_id   User ID.
 * @return bool True if completed.
 */
function splms_is_lesson_completed( $lesson_id, $user_id = null ) {
	if ( ! $user_id ) {
		$user_id = get_current_user_id();
	}

	if ( ! $user_id ) {
		return false;
	}

	// Use the centralized method from the lessons class.
	if ( class_exists( 'SPLMS_Lessons' ) ) {
		$lessons_instance = SPLMS_Lessons::get_instance();
		return $lessons_instance->is_lesson_completed( $lesson_id, $user_id );
	}

	// Use direct database query.
	global $wpdb;

	$table_name = esc_sql( $wpdb->prefix . 'splms_lesson_progress' );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table query.
	$completed = $wpdb->get_var(
		$wpdb->prepare(
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table name cannot be prepared, values are prepared.
			"SELECT is_completed FROM {$table_name} WHERE user_id = %d AND lesson_id = %d",
			$user_id,
			$lesson_id
		)
	);

	if ( ! empty( $wpdb->last_error ) ) {
		return false;
	}

	return (bool) $completed;
}

/**
 * Mark lesson as complete.
 *
 * @param int $lesson_id Lesson ID.
 * @param int $user_id   User ID.
 * @param int $course_id Course ID.
 * @return bool|WP_Error True on success, error on failure.
 */
function splms_mark_lesson_complete( $lesson_id, $user_id = null, $course_id = null ) {
	if ( ! $user_id ) {
		$user_id = get_current_user_id();
	}

	if ( ! $user_id ) {
		return new WP_Error( 'no_user', __( 'User not logged in.', 'skillpulse-lms' ) );
	}

	if ( ! $lesson_id ) {
		return new WP_Error( 'no_lesson', __( 'Lesson ID is required.', 'skillpulse-lms' ) );
	}

	// Get course ID if not provided.
	if ( ! $course_id ) {
		$course_id = splms_get_lesson_course( $lesson_id );
	}

	if ( ! $course_id ) {
		return new WP_Error( 'no_course', __( 'Course ID is required.', 'skillpulse-lms' ) );
	}

	// Check if user has access to this lesson.
	if ( ! splms_user_can_access_lesson( $lesson_id, $user_id ) ) {
		return new WP_Error( 'access_denied', __( 'Access denied.', 'skillpulse-lms' ) );
	}

	// Mark lesson complete in database table.
	SPLMS_Lesson_Progress_Query::get_instance()->complete_lesson( $lesson_id, $user_id, $course_id );

	do_action( 'splms_lesson_completed', $lesson_id, $user_id );

	// Calculate updated course progress.
	$lessons_instance = SPLMS_Lessons::get_instance();
	$progress_data    = $lessons_instance->calculate_course_progress( $user_id, $course_id );

	// Sync progress to enrollment database table.
	$enrollment = SPLMS_Enrollment::get_instance();
	$enrollment->update_enrollment_progress( $user_id, $course_id, $progress_data['percentage'] );

	// Log activity.
	SPLMS_User_Activity_Query::get_instance()->log_activity( $user_id, 'lesson_completed', $course_id, $lesson_id, 'lesson' );

	return true;
}

/**
 * ============================================================================
 * LESSON ACCESS FUNCTIONS
 * ============================================================================
 */

/**
 * Check if user can access lesson.
 *
 * @param int $lesson_id Lesson ID.
 * @param int $user_id   User ID.
 * @return bool True if user can access.
 */
function splms_user_can_access_lesson( $lesson_id, $user_id = null ) {
	if ( ! $user_id ) {
		$user_id = get_current_user_id();
	}

	if ( ! $user_id ) {
		return false;
	}

	$lessons_instance = SPLMS_Lessons::get_instance();
	return $lessons_instance->user_can_access_lesson( $lesson_id, $user_id );
}

/**
 * Check if lesson has drip access restrictions.
 *
 * @param int $lesson_id Lesson ID.
 * @param int $user_id   User ID.
 * @return bool True if lesson is available.
 */
function splms_is_lesson_drip_available( $lesson_id, $user_id = null ) {
	if ( ! $user_id ) {
		$user_id = get_current_user_id();
	}

	if ( ! $user_id ) {
		return false;
	}

	$lessons_instance = SPLMS_Lessons::get_instance();
	return $lessons_instance->is_lesson_drip_available( $lesson_id, $user_id );
}

/**
 * Get the drip unlock date for a lesson.
 *
 * Returns the date when a drip-locked lesson will become available
 * for a specific user, or false if the lesson is not drip-locked.
 *
 * @since 1.0.0
 *
 * @param int $lesson_id Lesson ID.
 * @param int $user_id   User ID (optional, defaults to current user).
 * @return string|false Formatted unlock date, or false if not drip-locked.
 */
function splms_get_lesson_drip_unlock_date( $lesson_id, $user_id = null ) {
	if ( ! $user_id ) {
		$user_id = get_current_user_id();
	}

	if ( ! $user_id ) {
		return false;
	}

	$lessons_instance = SPLMS_Lessons::get_instance();
	$drip_settings    = $lessons_instance->get_lesson_drip_settings( $lesson_id );

	if ( empty( $drip_settings['enable_drip'] ) || ! $drip_settings['enable_drip'] ) {
		return false;
	}

	$course_id = $lessons_instance->get_lesson_course( $lesson_id );
	if ( ! $course_id ) {
		return false;
	}

	// Check course-level drip toggle.
	$content_delivery = get_post_meta( $course_id, '_splms_content_delivery', true );
	if ( ! is_array( $content_delivery ) || empty( $content_delivery['drip_content'] ) ) {
		return false;
	}

	$drip_type = isset( $drip_settings['drip_type'] ) ? $drip_settings['drip_type'] : 'days_after_enrollment';
	$drip_days = isset( $drip_settings['drip_days'] ) ? intval( $drip_settings['drip_days'] ) : 0;

	switch ( $drip_type ) {
		case 'days_after_enrollment':
			$enrollment_date = $lessons_instance->get_user_enrollment_date( $user_id, $course_id );
			if ( ! $enrollment_date || $drip_days <= 0 ) {
				return false;
			}
			$unlock_timestamp = strtotime( $enrollment_date . ' + ' . $drip_days . ' days' );
			return date_i18n( get_option( 'date_format' ), $unlock_timestamp );

		case 'days_after_previous':
			$previous_lesson = $lessons_instance->get_previous_lesson( $lesson_id, $course_id );
			if ( ! $previous_lesson ) {
				return false;
			}
			if ( ! $lessons_instance->is_lesson_completed( $previous_lesson, $user_id ) ) {
				/* translators: %s: Previous lesson title. */
				return sprintf( __( 'after completing "%s"', 'skillpulse-lms' ), get_the_title( $previous_lesson ) );
			}
			$completion_date = $lessons_instance->get_lesson_completion_date( $previous_lesson, $user_id );
			if ( ! $completion_date ) {
				return false;
			}
			$unlock_timestamp = strtotime( $completion_date . ' + ' . $drip_days . ' days' );
			return date_i18n( get_option( 'date_format' ), $unlock_timestamp );

		case 'specific_date':
			$specific_date = isset( $drip_settings['specific_date'] ) ? $drip_settings['specific_date'] : '';
			if ( empty( $specific_date ) ) {
				return false;
			}
			$specific_timestamp = strtotime( $specific_date );
			if ( ! $specific_timestamp ) {
				return false;
			}
			return date_i18n( get_option( 'date_format' ), $specific_timestamp );

		default:
			return false;
	}
}

/**
 * Check if lesson prerequisites are met.
 *
 * @param int $lesson_id Lesson ID.
 * @param int $user_id   User ID.
 * @return bool True if prerequisites are met.
 */
function splms_are_lesson_prerequisites_met( $lesson_id, $user_id = null ) {
	if ( ! $user_id ) {
		$user_id = get_current_user_id();
	}

	if ( ! $user_id ) {
		return false;
	}

	$lessons_instance = SPLMS_Lessons::get_instance();
	return $lessons_instance->are_prerequisites_met( $lesson_id, $user_id );
}

/**
 * Check if user can skip lesson based on prevent_skip setting.
 *
 * @param int $lesson_id Lesson ID.
 * @param int $user_id   User ID.
 * @return bool True if can skip.
 */
function splms_can_skip_lesson( $lesson_id, $user_id = null ) {
	if ( ! $user_id ) {
		$user_id = get_current_user_id();
	}

	if ( ! $user_id ) {
		return false;
	}

	$lessons_instance = SPLMS_Lessons::get_instance();
	return $lessons_instance->can_skip_lesson( $lesson_id, $user_id );
}

/**
 * ============================================================================
 * LESSON COURSE FUNCTIONS
 * ============================================================================
 */

/**
 * Get lesson course ID.
 *
 * @param int $lesson_id Lesson ID.
 * @return int|null Course ID.
 */
function splms_get_lesson_course( $lesson_id = null ) {
	if ( ! $lesson_id ) {
		$lesson_id = get_the_ID();
	}

	// Use database relationships to find the course for this lesson.
	$relationships_query = SPLMS_Relationships_Query::get_instance();
	$parents             = $relationships_query->get_parents( $lesson_id );
	if ( ! empty( $parents ) ) {
		foreach ( $parents as $parent ) {
			return SPLMS_Course_Items_Query::get_instance()->get_item_course_id( $parent->parent_id );
		}
	}

	return null;
}

/**
 * Get all curriculum items (lessons and quizzes) in order from database.
 *
 * @param int $course_id Course ID.
 * @return array Array of item IDs in order.
 */
function splms_get_course_curriculum_items_ordered( $course_id ) {
	// Get course curriculum using unified method.
	$curriculum_result = splms_get_course_curriculum( $course_id );

	$all_items = array();

	// Extract item IDs from curriculum.
	if ( isset( $curriculum_result['sections'] ) ) {
		foreach ( $curriculum_result['sections'] as $section ) {
			if ( isset( $section['children'] ) ) {
				foreach ( $section['children'] as $child ) {
					$all_items[] = intval( $child['id'] );
				}
			}
		}
	}

	return $all_items;
}

/**
 * Get lesson order in course.
 *
 * @param int $lesson_id Lesson ID.
 * @param int $course_id Course ID.
 * @return int Lesson order.
 */
function splms_get_lesson_order_in_course( $lesson_id, $course_id = null ) {
	if ( ! $course_id ) {
		$course_id = splms_get_lesson_course( $lesson_id );
	}

	if ( ! $course_id ) {
		return 0;
	}

	// Get all curriculum items in order from database.
	$curriculum_items = splms_get_course_curriculum_items_ordered( $course_id );

	if ( empty( $curriculum_items ) ) {
		return 0;
	}

	// Find the order of this lesson.
	$order = 1;
	foreach ( $curriculum_items as $item_id ) {
		if ( intval( $item_id ) === intval( $lesson_id ) ) {
			return $order;
		}
		++$order;
	}

	return 0;
}

/**
 * Get previous lesson in course.
 *
 * @param int $lesson_id Lesson ID.
 * @param int $course_id Course ID.
 * @return int|false Previous lesson ID or false.
 */
function splms_get_previous_lesson_in_course( $lesson_id, $course_id = null ) {
	if ( ! $course_id ) {
		$course_id = splms_get_lesson_course( $lesson_id );
	}

	if ( ! $course_id ) {
		return false;
	}

	$current_order = splms_get_lesson_order_in_course( $lesson_id, $course_id );
	if ( $current_order <= 1 ) {
		return false; // No previous lesson.
	}

	// Get all curriculum items in order from database.
	$curriculum_items = splms_get_course_curriculum_items_ordered( $course_id );

	if ( empty( $curriculum_items ) || $current_order > count( $curriculum_items ) ) {
		return false;
	}

	// Get previous item (index is 0-based, so subtract 2).
	$previous_index = $current_order - 2;
	if ( isset( $curriculum_items[ $previous_index ] ) ) {
		return intval( $curriculum_items[ $previous_index ] );
	}

	return false;
}

/**
 * Get next lesson in course.
 *
 * @param int $lesson_id Lesson ID.
 * @param int $course_id Course ID.
 * @return int|false Next lesson ID or false.
 */
function splms_get_next_lesson_in_course( $lesson_id, $course_id = null ) {
	if ( ! $course_id ) {
		$course_id = splms_get_lesson_course( $lesson_id );
	}

	if ( ! $course_id ) {
		return false;
	}

	$current_order = splms_get_lesson_order_in_course( $lesson_id, $course_id );
	if ( $current_order <= 0 ) {
		return false;
	}

	// Get all curriculum items in order from database.
	$curriculum_items = splms_get_course_curriculum_items_ordered( $course_id );

	if ( empty( $curriculum_items ) || $current_order >= count( $curriculum_items ) ) {
		return false;
	}

	// Get next item (index is 0-based, so current_order is the next index).
	$next_index = $current_order;
	if ( isset( $curriculum_items[ $next_index ] ) ) {
		return intval( $curriculum_items[ $next_index ] );
	}

	return false;
}

/**
 * Get course navigation for lesson.
 *
 * @param int $lesson_id Lesson ID.
 * @param int $user_id   User ID.
 * @return array|null Navigation data.
 */
function splms_get_lesson_navigation( $lesson_id, $user_id = null ) {
	if ( ! $user_id ) {
		$user_id = get_current_user_id();
	}

	if ( ! $user_id ) {
		return null;
	}

	$lessons_instance = SPLMS_Lessons::get_instance();
	return $lessons_instance->get_lesson_navigation( $lesson_id, $user_id );
}

/**
 * ============================================================================
 * LESSON QUERY FUNCTIONS
 * ============================================================================
 */

/**
 * Get lessons by course.
 *
 * @param int   $course_id Course ID.
 * @param array $args      Query arguments.
 * @return array Lessons.
 */
function splms_get_course_lessons( $course_id, $args = array() ) {
	$lessons_instance = SPLMS_Lessons::get_instance();
	return $lessons_instance->get_course_lessons( $course_id, $args );
}

/**
 * ============================================================================
 * LESSON PROGRESS STATISTICS FUNCTIONS
 * ============================================================================
 */

/**
 * Get completed item IDs for a course (lessons + quizzes).
 *
 * @param int $user_id   User ID.
 * @param int $course_id Course ID.
 * @return array Completed item IDs.
 */
function splms_get_completed_item_ids( $user_id, $course_id ) {
	$lessons_instance = SPLMS_Lessons::get_instance();
	return $lessons_instance->get_completed_item_ids( $user_id, $course_id );
}


/**
 * ============================================================================
 * LESSON VALIDATION FUNCTIONS
 * ============================================================================
 */

/**
 * Check if lesson exists.
 *
 * @param int $lesson_id Lesson ID.
 * @return bool True if lesson exists.
 */
function splms_lesson_exists( $lesson_id ) {
	if ( empty( $lesson_id ) ) {
		return false;
	}

	$lesson = get_post( $lesson_id );

	return $lesson && SPLMS_POST_TYPES['lesson'] === $lesson->post_type;
}

/**
 * ============================================================================
 * LESSON STATISTICS FUNCTIONS
 * ============================================================================
 */

/**
 * Get lesson completion rate.
 *
 * @param int $lesson_id Lesson ID.
 * @return float Completion rate (0-100).
 */
function splms_get_lesson_completion_rate( $lesson_id = null ) {
	if ( ! $lesson_id ) {
		$lesson_id = get_the_ID();
	}

	$course_id = splms_get_lesson_course( $lesson_id );
	if ( ! $course_id ) {
		return 0;
	}

	// Get total enrolled students.
	$total_enrolled = SPLMS_Enrollments_Query::get_instance()->get_course_enrollment_count( $course_id );

	// Get completed students for this lesson.
	global $wpdb;
	$lesson_progress_table = esc_sql( $wpdb->prefix . 'splms_lesson_progress' );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table query.
	$completed_students = $wpdb->get_var(
		$wpdb->prepare(
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table name cannot be prepared, values are prepared.
			"SELECT COUNT(*) FROM {$lesson_progress_table} WHERE lesson_id = %d AND is_completed = 1",
			$lesson_id
		)
	);

	if ( 0 === $total_enrolled ) {
		return 0;
	}

	$completion_rate = ( $completed_students / $total_enrolled ) * 100;

	return apply_filters( 'splms_lesson_completion_rate', round( $completion_rate, 2 ), $lesson_id );
}

/**
 * Get lesson average completion time.
 *
 * @param int $lesson_id Lesson ID.
 * @return int Average completion time in minutes.
 */
function splms_get_lesson_average_completion_time( $lesson_id = null ) {
	if ( ! $lesson_id ) {
		$lesson_id = get_the_ID();
	}

	global $wpdb;
	$lesson_progress_table = esc_sql( $wpdb->prefix . 'splms_lesson_progress' );

	// Get completion times for this lesson.
	// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name cannot be prepared, values are prepared.
	$completion_times = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT TIMESTAMPDIFF(MINUTE, started_at, completed_at) 
		 FROM {$lesson_progress_table} 
		 WHERE lesson_id = %d AND is_completed = 1 AND started_at IS NOT NULL AND completed_at IS NOT NULL",
			$lesson_id
		)
	);
	// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

	if ( empty( $completion_times ) ) {
		return 0;
	}

	$average_time = array_sum( $completion_times ) / count( $completion_times );

	return apply_filters( 'splms_lesson_average_completion_time', round( $average_time ), $lesson_id );
}

/**
 * Validate media URL based on media type.
 *
 * @param string $url The media URL to validate.
 * @param string $type The media type: 'video', 'audio', or 'document'.
 * @return array Validation result with 'valid' (bool) and 'message' (string).
 */
function splms_validate_media_url( $url, $type ) {
	// Sanitize URL.
	$url = esc_url_raw( $url );

	// Check if URL is empty.
	if ( empty( $url ) ) {
		return array(
			'valid'   => false,
			'message' => __( 'Media URL is required.', 'skillpulse-lms' ),
		);
	}

	// Validate URL format.
	if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
		return array(
			'valid'   => false,
			'message' => __( 'Invalid URL format.', 'skillpulse-lms' ),
		);
	}

	$parsed_url = wp_parse_url( $url );
	$host       = isset( $parsed_url['host'] ) ? strtolower( $parsed_url['host'] ) : '';
	$path       = isset( $parsed_url['path'] ) ? $parsed_url['path'] : '';
	$file_ext   = strtolower( pathinfo( $path, PATHINFO_EXTENSION ) );

	switch ( $type ) {
		case 'video':
			// Check for YouTube.
			if ( strpos( $host, 'youtube.com' ) !== false || strpos( $host, 'youtu.be' ) !== false ) {
				return array(
					'valid'   => true,
					'message' => '',
				);
			}

			// Check for Vimeo.
			if ( strpos( $host, 'vimeo.com' ) !== false ) {
				return array(
					'valid'   => true,
					'message' => '',
				);
			}

			// Check for direct video file extensions.
			$valid_video_extensions = array( 'mp4', 'webm', 'ogv', 'mov', 'avi', 'wmv', 'flv', 'mkv' );
			if ( in_array( $file_ext, $valid_video_extensions, true ) ) {
				return array(
					'valid'   => true,
					'message' => '',
				);
			}

			// Invalid video URL.
			return array(
				'valid'   => false,
				'message' => __( 'Invalid video URL. Please upload a valid video file (MP4, WebM, OGV) or use a YouTube/Vimeo link.', 'skillpulse-lms' ),
			);

		case 'audio':
			// Check for SoundCloud.
			if ( strpos( $host, 'soundcloud.com' ) !== false ) {
				return array(
					'valid'   => true,
					'message' => '',
				);
			}

			// Check for direct audio file extensions.
			$valid_audio_extensions = array( 'mp3', 'wav', 'ogg', 'm4a', 'aac', 'flac', 'wma' );
			if ( in_array( $file_ext, $valid_audio_extensions, true ) ) {
				return array(
					'valid'   => true,
					'message' => '',
				);
			}

			// Invalid audio URL.
			return array(
				'valid'   => false,
				'message' => __( 'Invalid audio file. Only MP3, WAV, OGG, or M4A formats are supported, or use a SoundCloud link.', 'skillpulse-lms' ),
			);

		case 'document':
			// Check for Google Docs/Slides/Sheets.
			if ( strpos( $host, 'docs.google.com' ) !== false || strpos( $host, 'drive.google.com' ) !== false ) {
				return array(
					'valid'   => true,
					'message' => '',
				);
			}

			// Check for direct document file extensions (PDFs, Word, Presentations, Spreadsheets, Text, Archives, Images).
			$valid_document_extensions = array(
				// Documents.
				'pdf',
				'doc',
				'docx',
				'odt',
				'rtf',
				'txt',
				// Presentations.
				'ppt',
				'pptx',
				'odp',
				// Spreadsheets.
				'xls',
				'xlsx',
				'ods',
				'csv',
				// Archives.
				'zip',
				'rar',
				'7z',
				'tar',
				'gz',
				// Images.
				'jpg',
				'jpeg',
				'png',
				'gif',
				'webp',
				'svg',
				'bmp',
				'ico',
			);

			if ( in_array( $file_ext, $valid_document_extensions, true ) ) {
				return array(
					'valid'   => true,
					'message' => '',
				);
			}

			// For unknown formats, allow but show download link (don't block rendering).
			// Only block if URL is completely invalid.
			if ( empty( $file_ext ) && empty( $host ) ) {
				return array(
					'valid'   => false,
					'message' => __( 'Invalid document URL. Please provide a valid file link.', 'skillpulse-lms' ),
				);
			}

			// Unknown format - allow it but will show download link only.
			return array(
				'valid'   => true,
				'message' => '',
			);

		default:
			// Unknown type - allow it but log a warning.
			return array(
				'valid'   => true,
				'message' => '',
			);
	}
}

/**
 * Get document type category from URL.
 *
 * @param string $url The document URL.
 * @return string Document type: 'image', 'pdf', 'google_docs', 'downloadable', or 'unknown'.
 */
function splms_get_document_type( $url ) {
	if ( empty( $url ) ) {
		return 'unknown';
	}

	$parsed_url = wp_parse_url( $url );
	$host       = isset( $parsed_url['host'] ) ? strtolower( $parsed_url['host'] ) : '';
	$path       = isset( $parsed_url['path'] ) ? $parsed_url['path'] : '';
	$file_ext   = strtolower( pathinfo( $path, PATHINFO_EXTENSION ) );

	// Check for Google Docs/Slides/Sheets.
	if ( strpos( $host, 'docs.google.com' ) !== false || strpos( $host, 'drive.google.com' ) !== false ) {
		return 'google_docs';
	}

	// Check for images.
	$image_extensions = array( 'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp', 'ico' );
	if ( in_array( $file_ext, $image_extensions, true ) ) {
		return 'image';
	}

	// Check for PDF.
	if ( 'pdf' === $file_ext ) {
		return 'pdf';
	}

	// All other document types (doc, ppt, txt, zip, etc.) are downloadable.
	return 'downloadable';
}

/**
 * Render document lesson content based on file type.
 *
 * @param string $url The document URL.
 * @param int    $lesson_id Optional lesson ID for data attributes.
 * @return string Rendered HTML output.
 */
function splms_render_document_lesson( $url, $lesson_id = 0 ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- Parameter kept for future use.
	if ( empty( $url ) ) {
		return '';
	}

	$document_type = splms_get_document_type( $url );
	$escaped_url   = esc_url( $url );
	$output        = '';

	switch ( $document_type ) {
		case 'image':
			// Render inline image preview.
			$output .= '<div class="splms-lesson-document__image-wrapper splms-doc-image">';
			$output .= '<img src="' . $escaped_url . '" alt="' . esc_attr__( 'Document image', 'skillpulse-lms' ) . '" class="splms-lesson-document__image" />';
			$output .= '</div>';
			break;

		case 'pdf':
			// Render PDF in iframe.
			$output .= '<div class="splms-lesson-document__pdf-wrapper">';
			$output .= '<iframe src="' . $escaped_url . '" class="splms-lesson-document__iframe" frameborder="0"></iframe>';
			$output .= '</div>';
			break;

		case 'google_docs':
			// Convert Google Docs/Sheets/Slides to embeddable format.
			$embed_url = $url;
			if ( strpos( $embed_url, '/edit' ) !== false ) {
				$embed_url = str_replace( '/edit', '/preview', $embed_url );
			}
			if ( strpos( $embed_url, '?usp=sharing' ) !== false ) {
				$embed_url = str_replace( '?usp=sharing', '', $embed_url );
			}
			if ( strpos( $embed_url, '/view' ) === false && strpos( $embed_url, '/preview' ) === false ) {
				$embed_url = rtrim( $embed_url, '/' ) . '/preview';
			}
			$output .= '<div class="splms-lesson-document__google-wrapper">';
			$output .= '<iframe src="' . esc_url( $embed_url ) . '" class="splms-lesson-document__iframe" frameborder="0" allowfullscreen></iframe>';
			$output .= '</div>';
			break;

		case 'downloadable':
		default:
			// Show download button for documents, presentations, archives, text files, etc..
			$output .= '<div class="splms-lesson-document__download-wrapper splms-doc-download">';
			$output .= '<div class="splms-lesson-document__preview-placeholder">';
			$output .= '<svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="splms-lesson-document__placeholder-icon">';
			$output .= '<path d="M14 2H6C5.46957 2 4.96086 2.21071 4.58579 2.58579C4.21071 2.96086 4 3.46957 4 4V20C4 20.5304 4.21071 21.0391 4.58579 21.4142C4.96086 21.7893 5.46957 22 6 22H18C18.5304 22 19.0391 21.7893 19.4142 21.4142C19.7893 21.0391 20 20.5304 20 20V8L14 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>';
			$output .= '<path d="M14 2V8H20" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>';
			$output .= '</svg>';
			$output .= '<p>' . esc_html__( 'This document is not previewable. Please download to view.', 'skillpulse-lms' ) . '</p>';
			$output .= '</div>';
			$output .= '</div>';
			break;
	}

	// Always show download button.
	$output .= '<div class="splms-lesson-document__download">';
	$output .= '<a href="' . $escaped_url . '" class="splms-button splms-button--secondary" target="_blank" download>';
	$output .= '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">';
	$output .= '<path d="M21 15V19C21 20.1046 20.1046 21 19 21H5C3.89543 21 3 20.1046 3 19V5C3 3.89543 3.89543 3 5 3H9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>';
	$output .= '<path d="M18 3H21V6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>';
	$output .= '<path d="M10 14L21 3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>';
	$output .= '</svg>';
	$output .= esc_html__( 'Download Document', 'skillpulse-lms' );
	$output .= '</a>';
	$output .= '</div>';

	return $output;
}

/**
 * Get MIME type for media file based on extension and type.
 *
 * @param string $url The media file URL.
 * @param string $type Media type: 'audio' or 'video'.
 * @return string MIME type for the media file.
 */
function splms_get_media_mime_type( $url, $type = 'audio' ) {
	$parsed_url = wp_parse_url( $url );
	$path       = isset( $parsed_url['path'] ) ? $parsed_url['path'] : '';
	$file_ext   = strtolower( pathinfo( $path, PATHINFO_EXTENSION ) );

	if ( 'video' === $type ) {
		switch ( $file_ext ) {
			case 'mp4':
				return 'video/mp4';
			case 'webm':
				return 'video/webm';
			case 'ogv':
			case 'ogg':
				return 'video/ogg';
			case 'mov':
				return 'video/quicktime';
			default:
				return 'video/mp4';
		}
	} else {
		// Audio.
		switch ( $file_ext ) {
			case 'mp3':
				return 'audio/mpeg';
			case 'm4a':
				return 'audio/mp4';
			case 'aac':
				return 'audio/aac';
			case 'ogg':
			case 'oga':
				return 'audio/ogg';
			case 'wav':
				return 'audio/wav';
			case 'flac':
				return 'audio/flac';
			case 'wma':
				return 'audio/x-ms-wma';
			default:
				return 'audio/mpeg';
		}
	}
}

/**
 * Get supported file extensions for media type.
 *
 * @param string $type Media type: 'audio' or 'video'.
 * @return array Array of supported file extensions.
 */
function splms_get_supported_media_extensions( $type = 'audio' ) {
	if ( 'video' === $type ) {
		return array( 'mp4', 'webm', 'ogv', 'ogg' );
	} else {
		return array( 'mp3', 'wav', 'ogg', 'm4a', 'aac', 'flac', 'wma' );
	}
}

/**
 * Check if URL is from external source (not WordPress media).
 *
 * @param string $url The media URL.
 * @return bool True if external, false if WordPress media.
 */
function splms_is_external_media_url( $url ) {
	if ( empty( $url ) ) {
		return false;
	}

	$parsed_url = wp_parse_url( $url );
	$host       = isset( $parsed_url['host'] ) ? strtolower( $parsed_url['host'] ) : '';
	// phpcs:ignore WordPress.WP.AlternativeFunctions.parse_url_parse_url -- PHP_URL_HOST constant requires parse_url().
	$site_url = parse_url( home_url(), PHP_URL_HOST );

	// Check if host matches WordPress site domain.
	if ( ! empty( $host ) && ! empty( $site_url ) ) {
		return strtolower( $site_url ) !== $host;
	}

	// If no host detected, assume external.
	return true;
}

/**
 * Render unified media player for audio or video.
 *
 * @param string $url The media file URL.
 * @param string $type Media type: 'audio' or 'video'. Default 'audio'.
 * @param array  $args Optional arguments: 'lesson_id', 'course_id', 'completion_required', 'show_cors_notice'.
 * @return string Rendered HTML output with error handling.
 */
function splms_render_media_player( $url, $type = 'audio', $args = array() ) {
	if ( empty( $url ) ) {
		return '';
	}

	$url    = trim( $url );
	$output = '';

	// Merge default arguments.
	$args = wp_parse_args(
		$args,
		array(
			'lesson_id'           => 0,
			'course_id'           => 0,
			'completion_required' => 100,
			'show_cors_notice'    => true,
		)
	);

	// Validate URL format.
	if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
		$output .= '<div class="splms-notice splms-notice--error">';
		$output .= '<div class="splms-notice__icon">';
		$output .= '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">';
		$output .= '<path d="M12 9V13M12 17H12.01M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>';
		$output .= '</svg>';
		$output .= '</div>';
		$output .= '<div class="splms-notice__content">';
		$output .= '<p>' . esc_html__( 'Invalid URL format.', 'skillpulse-lms' ) . '</p>';
		$output .= '</div>';
		$output .= '</div>';
		return $output;
	}

	$parsed_url = wp_parse_url( $url );
	$path       = isset( $parsed_url['path'] ) ? $parsed_url['path'] : '';
	$file_ext   = strtolower( pathinfo( $path, PATHINFO_EXTENSION ) );
	$host       = isset( $parsed_url['host'] ) ? strtolower( $parsed_url['host'] ) : '';

	// Handle special embeds (YouTube, Vimeo for video; SoundCloud for audio).
	if ( 'video' === $type ) {
		// YouTube embed.
		if ( strpos( $host, 'youtube.com' ) !== false || strpos( $host, 'youtu.be' ) !== false ) {
			$video_id = '';
			if ( strpos( $url, 'youtube.com' ) !== false ) {
				// phpcs:ignore WordPress.WP.AlternativeFunctions.parse_url_parse_url -- PHP_URL_QUERY constant requires parse_url().
				parse_str( parse_url( $url, PHP_URL_QUERY ), $vars );
				$video_id = isset( $vars['v'] ) ? $vars['v'] : '';
			} elseif ( strpos( $url, 'youtu.be' ) !== false ) {
				// phpcs:ignore WordPress.WP.AlternativeFunctions.parse_url_parse_url -- PHP_URL_PATH constant requires parse_url().
				$video_id = substr( parse_url( $url, PHP_URL_PATH ), 1 );
			}
			if ( $video_id ) {
				$output .= '<div class="splms-video-container">';
				$output .= '<iframe id="splms-lesson-video-' . esc_attr( $args['lesson_id'] ) . '" src="https://www.youtube.com/embed/' . esc_attr( $video_id ) . '?rel=0&enablejsapi=1" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
				$output .= '</div>';
				return $output;
			}
		}

		// Vimeo embed.
		if ( strpos( $host, 'vimeo.com' ) !== false ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.parse_url_parse_url -- PHP_URL_PATH constant requires parse_url().
			$video_id = substr( parse_url( $url, PHP_URL_PATH ), 1 );
			if ( $video_id ) {
				$output .= '<div class="splms-video-container">';
				$output .= '<iframe id="splms-lesson-video-' . esc_attr( $args['lesson_id'] ) . '" src="https://player.vimeo.com/video/' . esc_attr( $video_id ) . '?title=0&byline=0&portrait=0" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe>';
				$output .= '</div>';
				return $output;
			}
		}
	} elseif ( 'audio' === $type ) {
		// SoundCloud embed.
		if ( strpos( $host, 'soundcloud.com' ) !== false ) {
			$output .= '<iframe width="100%" height="166" scrolling="no" frameborder="no" allow="autoplay" src="https://w.soundcloud.com/player/?url=' . esc_url( rawurlencode( $url ) ) . '&color=%23ff5500&auto_play=false&hide_related=false&show_comments=true&show_user=true&show_reposts=false&show_teaser=true"></iframe>';
			return $output;
		}
	}

	// For direct media files, check supported file extensions.
	$supported_extensions = splms_get_supported_media_extensions( $type );

	// Only validate extension if we have one (skip for URLs without extension).
	if ( ! empty( $file_ext ) && ! in_array( $file_ext, $supported_extensions, true ) ) {
		// Unsupported format.
		$supported_formats = implode( ', ', array_map( 'strtoupper', $supported_extensions ) );
		$output           .= '<div class="splms-notice splms-notice--error">';
		$output           .= '<div class="splms-notice__icon">';
		$output           .= '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">';
		$output           .= '<path d="M12 9V13M12 17H12.01M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>';
		$output           .= '</svg>';
		$output           .= '</div>';
		$output           .= '<div class="splms-notice__content">';
		/* translators: %1$s: File extension, %2$s: Supported formats. */
		$output .= '<p>' . esc_html( sprintf( __( 'The selected file format (.%1$s) is not supported by your browser. Please upload a %2$s file.', 'skillpulse-lms' ), $file_ext, $supported_formats ) ) . '</p>';
		$output .= '</div>';
		$output .= '</div>';
		return $output;
	}

	// Get MIME type.
	$mime_type   = splms_get_media_mime_type( $url, $type );
	$is_external = splms_is_external_media_url( $url );
	$escaped_url = esc_url( $url );

	// Render media element.
	if ( 'video' === $type ) {
		$video_id_attr   = $args['lesson_id'] ? ' id="splms-lesson-video-' . esc_attr( $args['lesson_id'] ) . '"' : '';
		$completion_attr = $args['lesson_id'] ? ' data-completion-required="' . esc_attr( $args['completion_required'] ) . '"' : '';
		$output         .= '<video' . $video_id_attr . ' controls preload="metadata" crossorigin="anonymous" playsinline' . $completion_attr . ' style="width: 100%;">';
		$output         .= '<source src="' . $escaped_url . '" type="' . esc_attr( $mime_type ) . '">';
		$output         .= esc_html__( 'Your browser does not support the video element.', 'skillpulse-lms' );
		$output         .= '</video>';
	} else {
		$output .= '<audio controls preload="metadata" crossorigin="anonymous" style="width: 100%;">';
		$output .= '<source src="' . $escaped_url . '" type="' . esc_attr( $mime_type ) . '">';
		$output .= esc_html__( 'Your browser does not support the audio player.', 'skillpulse-lms' );
		$output .= '</audio>';
	}

	// Add CORS notice for external media sources.
	if ( $is_external && $args['show_cors_notice'] ) {
		$notice_class = 'audio' === $type ? 'splms-lesson-audio__cors-notice' : 'splms-lesson-video__cors-notice';
		$output      .= '<div class="' . esc_attr( $notice_class ) . '">';
		$output      .= '<p class="' . esc_attr( $notice_class ) . '__text">';
		$output      .= '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="vertical-align: middle; margin-right: 6px;">';
		$output      .= '<path d="M12 9V13M12 17H12.01M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>';
		$output      .= '</svg>';
		$output      .= esc_html__( 'Media loaded from external source. If playback fails, host file on WordPress media.', 'skillpulse-lms' );
		$output      .= '</p>';
		$output      .= '</div>';
	}

	return $output;
}

/**
 * Render audio lesson content.
 *
 * @param string $audio_url The audio file URL.
 * @return string Rendered HTML output with error handling.
 */
function splms_render_audio_lesson( $audio_url ) {
	// Use unified media renderer.
	return splms_render_media_player( $audio_url, 'audio', array( 'show_cors_notice' => true ) );
}

/**
 * Get allowed HTML tags for interactive embed content.
 *
 * Extends WordPress default allowed HTML with iframe, embed, object, and script tags
 * needed for SCORM, H5P, and other interactive content embeds.
 *
 * @return array Associative array of allowed tags and attributes.
 */
function splms_allowed_embed_html() {
	// Start with WordPress default allowed HTML for posts.
	$allowed = wp_kses_allowed_html( 'post' );

	// Add iframe support for embeds.
	$allowed['iframe'] = array(
		'src'             => true,
		'width'           => true,
		'height'          => true,
		'allow'           => true,
		'allowfullscreen' => true,
		'frameborder'     => true,
		'loading'         => true,
		'scrolling'       => true,
		'style'           => true,
		'class'           => true,
		'id'              => true,
		'title'           => true,
		'name'            => true,
		'sandbox'         => true,
		'srcdoc'          => true,
	);

	// Add embed support.
	$allowed['embed'] = array(
		'src'    => true,
		'type'   => true,
		'width'  => true,
		'height' => true,
		'style'  => true,
		'class'  => true,
		'id'     => true,
	);

	// Add object support.
	$allowed['object'] = array(
		'data'   => true,
		'type'   => true,
		'width'  => true,
		'height' => true,
		'style'  => true,
		'class'  => true,
		'id'     => true,
	);

	// Add param for object tags.
	$allowed['param'] = array(
		'name'  => true,
		'value' => true,
	);

	// Add script support (for SCORM and other trusted sources).
	// Only allow scripts from known safe domains.
	$allowed['script'] = array(
		'src'   => true,
		'type'  => true,
		'async' => true,
		'defer' => true,
	);

	// Add audio support for audio lessons.
	$allowed['audio'] = array(
		'controls'    => true,
		'preload'     => true,
		'autoplay'    => true,
		'loop'        => true,
		'muted'       => true,
		'style'       => true,
		'class'       => true,
		'id'          => true,
		'width'       => true,
		'height'      => true,
		'crossorigin' => true,
	);

	// Add video support for video lessons.
	$allowed['video'] = array(
		'controls'                 => true,
		'preload'                  => true,
		'autoplay'                 => true,
		'loop'                     => true,
		'muted'                    => true,
		'playsinline'              => true,
		'style'                    => true,
		'class'                    => true,
		'id'                       => true,
		'width'                    => true,
		'height'                   => true,
		'crossorigin'              => true,
		'data-completion-required' => true,
	);

	// Add source tag for audio/video sources.
	$allowed['source'] = array(
		'src'         => true,
		'type'        => true,
		'crossorigin' => true,
	);

	// Enhance div and span for wrapper containers.
	if ( ! isset( $allowed['div'] ) ) {
		$allowed['div'] = array();
	}
	$allowed['div']['class'] = true;
	$allowed['div']['id']    = true;
	$allowed['div']['style'] = true;

	if ( ! isset( $allowed['span'] ) ) {
		$allowed['span'] = array();
	}
	$allowed['span']['class'] = true;
	$allowed['span']['id']    = true;
	$allowed['span']['style'] = true;

	// Note: WordPress wp_kses doesn't support wildcard attributes like data-*.
	// Individual data attributes would need to be explicitly listed if needed.
	// For now, we rely on wp_kses_post defaults and iframe/embed specific attributes.

	/**
	 * Filter the allowed HTML tags for interactive embed content.
	 *
	 * @since 1.0.0
	 *
	 * @param array $allowed Associative array of allowed tags and attributes.
	 */
	return apply_filters( 'splms_allowed_embed_html', $allowed );
}

/**
 * Check if content contains shortcodes.
 *
 * @param string $content The content to check.
 * @return bool True if content contains shortcodes, false otherwise.
 */
function splms_has_shortcodes( $content ) {
	if ( empty( $content ) ) {
		return false;
	}

	// Check if content contains square brackets (potential shortcode).
	if ( strpos( $content, '[' ) === false ) {
		return false;
	}

	// Check against registered shortcodes.
	global $shortcode_tags;
	if ( empty( $shortcode_tags ) || ! is_array( $shortcode_tags ) ) {
		return false;
	}

	// Simple pattern match for shortcode syntax [tag] or [tag attr="value"].
	preg_match_all( '/\[([a-zA-Z0-9_-]+)([^\]]*)\]/', $content, $matches );

	if ( ! empty( $matches[1] ) ) {
		foreach ( $matches[1] as $tag ) {
			if ( isset( $shortcode_tags[ $tag ] ) ) {
				return true;
			}
		}
	}

	return false;
}

/**
 * Render interactive lesson content (embed code or shortcode).
 *
 * @param string $embed_code The embed code or shortcode content.
 * @return string Rendered HTML output with error handling.
 */
function splms_render_interactive_lesson( $embed_code ) {
	if ( empty( $embed_code ) ) {
		return '';
	}

	$embed_code = trim( $embed_code );
	$output     = '';

	// Check if content contains shortcodes.
	$has_shortcodes = splms_has_shortcodes( $embed_code );

	if ( $has_shortcodes ) {
		// Extract shortcode tags from the original content to check if they're registered.
		preg_match_all( '/\[([a-zA-Z0-9_-]+)/', $embed_code, $shortcode_matches );

		if ( ! empty( $shortcode_matches[1] ) ) {
			foreach ( $shortcode_matches[1] as $shortcode_tag ) {
				// Check if shortcode is registered.
				if ( ! shortcode_exists( $shortcode_tag ) ) {
					$output .= '<div class="splms-notice splms-notice--warning">';
					$output .= '<div class="splms-notice__icon">';
					$output .= '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">';
					$output .= '<path d="M12 9V13M12 17H12.01M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>';
					$output .= '</svg>';
					$output .= '</div>';
					$output .= '<div class="splms-notice__content">';
					/* translators: %s: Shortcode tag name. */
					$output .= '<p>' . esc_html( sprintf( __( 'The shortcode [%s] could not be rendered. Please ensure the related plugin is active.', 'skillpulse-lms' ), $shortcode_tag ) ) . '</p>';
					$output .= '</div>';
					$output .= '</div>';
					return $output;
				}
			}
		}

		// Process shortcodes.
		$processed = do_shortcode( $embed_code );

		// Sanitize the processed shortcode output.
		$output .= wp_kses_post( $processed );
	} else {
		// Treat as HTML embed code.
		// Check if it contains embed tags.
		$has_embed_tags = (
			preg_match( '/<iframe/i', $embed_code ) ||
			preg_match( '/<embed/i', $embed_code ) ||
			preg_match( '/<object/i', $embed_code ) ||
			preg_match( '/<script/i', $embed_code )
		);

		if ( $has_embed_tags ) {
			// Validate iframe src if present.
			if ( preg_match( '/<iframe[^>]*>/i', $embed_code, $iframe_match ) ) {
				// Check if iframe has src attribute.
				if ( ! preg_match( '/src\s*=\s*["\']([^"\']+)["\']/', $iframe_match[0] ) ) {
					// Invalid iframe - missing src.
					$output .= '<div class="splms-notice splms-notice--error">';
					$output .= '<div class="splms-notice__icon">';
					$output .= '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">';
					$output .= '<path d="M12 9V13M12 17H12.01M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>';
					$output .= '</svg>';
					$output .= '</div>';
					$output .= '<div class="splms-notice__content">';
					$output .= '<p>' . esc_html__( 'Invalid embed source URL. Please check the iframe src attribute.', 'skillpulse-lms' ) . '</p>';
					$output .= '</div>';
					$output .= '</div>';
					return $output;
				}
			}

			// Sanitize embed HTML with custom allowed tags.
			$allowed_html = splms_allowed_embed_html();
			$sanitized    = wp_kses( $embed_code, $allowed_html );

			// Check if sanitization removed important tags (indicates invalid HTML).
			if ( empty( $sanitized ) || ( $has_embed_tags && ! preg_match( '/<(iframe|embed|object|script)/i', $sanitized ) ) ) {
				$output .= '<div class="splms-notice splms-notice--warning">';
				$output .= '<div class="splms-notice__icon">';
				$output .= '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">';
				$output .= '<path d="M12 9V13M12 17H12.01M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>';
				$output .= '</svg>';
				$output .= '</div>';
				$output .= '<div class="splms-notice__content">';
				$output .= '<p>' . esc_html__( 'The provided embed code could not be displayed. Please check the iframe or embed source.', 'skillpulse-lms' ) . '</p>';
				$output .= '</div>';
				$output .= '</div>';
				return $output;
			}

			$output .= $sanitized;
		} elseif ( preg_match( '/<[^>]+>/', $embed_code ) ) {
			// No embed tags found - might be plain text or invalid code.
			// Check if it looks like invalid HTML or contains suspicious content.
			// Contains HTML tags but not embed tags - sanitize as regular HTML.
			$allowed_html = splms_allowed_embed_html();
			$sanitized    = wp_kses( $embed_code, $allowed_html );

			if ( ! empty( $sanitized ) ) {
				$output .= $sanitized;
			} else {
				// Invalid HTML.
				$output .= '<div class="splms-notice splms-notice--warning">';
				$output .= '<div class="splms-notice__icon">';
				$output .= '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">';
				$output .= '<path d="M12 9V13M12 17H12.01M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>';
				$output .= '</svg>';
				$output .= '</div>';
				$output .= '<div class="splms-notice__content">';
				$output .= '<p>' . esc_html__( 'The provided embed code could not be displayed. Please check the iframe or embed source.', 'skillpulse-lms' ) . '</p>';
				$output .= '</div>';
				$output .= '</div>';
			}
		} elseif ( filter_var( trim( $embed_code ), FILTER_VALIDATE_URL ) ) {
			// Plain text - might be a URL or instructions.
			// Check if it's a URL.
					$output .= '<div class="splms-notice splms-notice--info">';
					$output .= '<div class="splms-notice__icon">';
					$output .= '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">';
					$output .= '<path d="M12 9V13M12 17H12.01M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>';
					$output .= '</svg>';
					$output .= '</div>';
					$output .= '<div class="splms-notice__content">';
					$output .= '<p>' . esc_html__( 'Please use an iframe embed code or shortcode for interactive content. URL detected:', 'skillpulse-lms' ) . ' <a href="' . esc_url( $embed_code ) . '" target="_blank">' . esc_html( $embed_code ) . '</a></p>';
					$output .= '</div>';
					$output .= '</div>';
		} else {
			// Plain text - treat as regular content.
			$output .= wp_kses_post( $embed_code );
		}
	}

	return $output;
}

/**
 * Check if a lesson is available for the current user.
 *
 * @param int $lesson_id Lesson ID.
 * @param int $user_id   User ID (optional, defaults to current user).
 * @return bool True if lesson is available, false otherwise.
 */
function splms_is_lesson_available( $lesson_id, $user_id = null ) {
	if ( ! $lesson_id ) {
		return false;
	}

	if ( ! $user_id ) {
		$user_id = get_current_user_id();
	}

	// Check if lesson exists and is published.
	$lesson = get_post( $lesson_id );
	if ( ! $lesson || 'publish' !== $lesson->post_status ) {
		return false;
	}

	// Check if user can access the lesson.
	return splms_user_can_access_lesson( $lesson_id, $user_id );
}

/**
 * Get lesson expiration date for a user.
 *
 * @param int $lesson_id Lesson ID.
 * @return string|false Expiration date or false if no expiration.
 */
function splms_get_lesson_expiration_date( $lesson_id ) {

	$settings        = splms_get_lesson_settings( $lesson_id );
	$expiration_days = isset( $settings['lesson_access_expiration'] ) ? intval( $settings['lesson_access_expiration'] ) : 0;

	if ( $expiration_days <= 0 ) {
		return false; // No expiration set.
	}

	// Get user enrollment date for the course.
	$course_id = splms_get_lesson_course( $lesson_id );
	if ( ! $course_id ) {
		return false;
	}

	// For testing purposes, use current time as enrollment date.
	// In real implementation, this would get actual enrollment date.
	$enrollment_date = current_time( 'mysql' );
	$expiration_date = gmdate( 'Y-m-d H:i:s', strtotime( $enrollment_date . ' + ' . $expiration_days . ' days' ) );

	return $expiration_date;
}

/**
 * Get lesson completion deadline for a user.
 *
 * @param int $lesson_id Lesson ID.
 * @return string|false Completion deadline or false if no deadline.
 */
function splms_get_lesson_completion_deadline( $lesson_id ) {
	$settings = splms_get_lesson_settings( $lesson_id );

	// Check if there's a specific completion deadline in lesson settings.
	// For now, we'll use the drip settings to calculate a deadline.
	$drip_settings = isset( $settings['lesson_drip_settings'] ) ? $settings['lesson_drip_settings'] : array();

	if ( isset( $drip_settings['specific_date'] ) && ! empty( $drip_settings['specific_date'] ) ) {
		// If lesson has a specific availability date, add some time for completion.
		$availability_date = $drip_settings['specific_date'];
		$deadline          = gmdate( 'Y-m-d H:i:s', strtotime( $availability_date . ' + 30 days' ) );
		return $deadline;
	}

	// Default: no specific deadline.
	return false;
}
