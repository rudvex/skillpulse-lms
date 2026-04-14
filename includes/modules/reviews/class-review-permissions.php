<?php
/**
 * Review Permissions Handler
 *
 * Handles permission checks for course reviews.
 *
 * @package SkillPulse_LMS
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class SPLMS_Review_Permissions
 *
 * Manages review permission checks and validation.
 */
class SPLMS_Review_Permissions {

	/**
	 * Check if user can review a course.
	 *
	 * @since 1.0.0
	 *
	 * @param int $user_id  User ID.
	 * @param int $course_id Course ID.
	 * @return array {
	 *     Permission check result.
	 *
	 *     @type bool   $can_review Whether user can review.
	 *     @type string $message    Message if cannot review.
	 *     @type string $code       Error code if cannot review.
	 * }
	 */
	public static function can_user_review( $user_id, $course_id ) {
		// 1. Must be logged in.
		if ( ! $user_id ) {
			return array(
				'can_review' => false,
				'message'    => __( 'You must be logged in to leave a review.', 'skillpulse-lms' ),
				'code'       => 'not_logged_in',
			);
		}

		// 2. Cannot review own course.
		$course_author = get_post_field( 'post_author', $course_id );
		if ( (int) $user_id === (int) $course_author ) {
			return array(
				'can_review' => false,
				'message'    => __( 'You cannot review your own course.', 'skillpulse-lms' ),
				'code'       => 'own_course',
			);
		}

		// 3. Check if user already reviewed.
		if ( self::user_has_reviewed( $user_id, $course_id ) ) {
			return array(
				'can_review' => false,
				'message'    => __( 'You have already reviewed this course.', 'skillpulse-lms' ),
				'code'       => 'already_reviewed',
			);
		}

		// 4. Must be enrolled or have access.
		$is_enrolled = splms_is_user_enrolled( $course_id, $user_id );
		if ( ! $is_enrolled ) {
			return array(
				'can_review' => false,
				'message'    => __( 'You must be enrolled in this course to leave a review.', 'skillpulse-lms' ),
				'code'       => 'not_enrolled',
			);
		}

		// 5. Check if completion is required.
		$require_completion = splms_get_setting( 'reviews_require_completion', false );
		if ( $require_completion ) {
			$enrollment   = SkillPulse_LMS_Enrollment::get_instance();
			$is_completed = $enrollment->has_user_completed_course( $user_id, $course_id );
			if ( ! $is_completed ) {
				return array(
					'can_review' => false,
					'message'    => __( 'You must complete the course before leaving a review.', 'skillpulse-lms' ),
					'code'       => 'not_completed',
				);
			}
		}

		// 6. Allow admins to review (optional setting).
		if ( user_can( $user_id, 'manage_options' ) ) {
			$allow_admin = splms_get_setting( 'reviews_allow_admin', true );
			if ( $allow_admin ) {
				return array(
					'can_review' => true,
					'message'    => '',
					'code'       => '',
				);
			}
		}

		return array(
			'can_review' => true,
			'message'    => '',
			'code'       => '',
		);
	}

	/**
	 * Check if user has already reviewed the course.
	 *
	 * @since 1.0.0
	 *
	 * @param int $user_id  User ID.
	 * @param int $course_id Course ID.
	 * @return bool True if user has reviewed.
	 */
	public static function user_has_reviewed( $user_id, $course_id ) {
		$args = array(
			'post_id' => $course_id,
			'user_id' => $user_id,
			'type'    => 'splms_course_review',
			'status'  => 'all',
			'count'   => true,
		);

		$count = get_comments( $args );

		return $count > 0;
	}

	/**
	 * Check if user can edit a review.
	 *
	 * @since 1.0.0
	 *
	 * @param int $user_id    User ID.
	 * @param int $comment_id Comment ID.
	 * @return bool True if user can edit.
	 */
	public static function can_user_edit_review( $user_id, $comment_id ) {
		$comment = get_comment( $comment_id );

		if ( ! $comment ) {
			return false;
		}

		// Must be the comment author.
		if ( (int) $comment->user_id !== (int) $user_id ) {
			return false;
		}

		// Check edit window.
		$edit_window = splms_get_setting( 'reviews_edit_window', 24 ); // Hours.

		// -1 means always allow editing.
		if ( -1 === (int) $edit_window ) {
			return true;
		}

		// 0 means never allow editing.
		if ( 0 === (int) $edit_window ) {
			return false;
		}

		// Check if within time window.
		$comment_time = strtotime( $comment->comment_date_gmt );
		$current_time = time();
		$hours_passed = ( $current_time - $comment_time ) / HOUR_IN_SECONDS;

		return $hours_passed <= $edit_window;
	}

	/**
	 * Check if user can delete a review.
	 *
	 * @since 1.0.0
	 *
	 * @param int $user_id    User ID.
	 * @param int $comment_id Comment ID.
	 * @return bool True if user can delete.
	 */
	public static function can_user_delete_review( $user_id, $comment_id ) {
		$comment = get_comment( $comment_id );

		if ( ! $comment ) {
			return false;
		}

		// Must be the comment author or admin.
		if ( (int) $comment->user_id === (int) $user_id ) {
			return true;
		}

		// Allow admins to delete any review.
		if ( user_can( $user_id, 'manage_options' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if user can reply to a review.
	 *
	 * @since 1.0.0
	 *
	 * @param int $user_id    User ID.
	 * @param int $course_id  Course ID.
	 * @return bool True if user can reply.
	 */
	public static function can_user_reply( $user_id, $course_id ) {
		if ( ! $user_id ) {
			return false;
		}

		// Allow course instructor to reply.
		$course_author = get_post_field( 'post_author', $course_id );
		if ( (int) $user_id === (int) $course_author ) {
			return true;
		}

		// Allow admins to reply.
		if ( user_can( $user_id, 'manage_options' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Get user's review for a course.
	 *
	 * @since 1.0.0
	 *
	 * @param int $user_id  User ID.
	 * @param int $course_id Course ID.
	 * @return WP_Comment|null Comment object or null if not found.
	 */
	public static function get_user_review( $user_id, $course_id ) {
		$args = array(
			'post_id' => $course_id,
			'user_id' => $user_id,
			'type'    => 'splms_course_review',
			'status'  => 'all',
			'number'  => 1,
		);

		$comments = get_comments( $args );

		return ! empty( $comments ) ? $comments[0] : null;
	}
}
