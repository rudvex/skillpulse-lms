<?php
/**
 * Course Reviews Helper Functions
 *
 * Utility functions for the reviews system.
 *
 * @since   1.0.0
 * @package SkillPulse_LMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Reviews Helpers Class
 *
 * Utility functions for the reviews system.
 *
 * @since   1.0.0
 * @package SkillPulse_LMS
 */
class SkillPulse_LMS_Reviews_Helpers {

	/**
	 * Render star rating HTML.
	 *
	 * @param float $rating Rating value (0-5).
	 * @param array $args   Additional arguments.
	 *
	 * @return string HTML for star rating.
	 */
	public static function render_star_rating( $rating, $args = array() ) {
		$defaults = array(
			'size'        => 'medium', // small, medium, large.
			'show_text'   => false,
			'show_count'  => false,
			'count'       => 0,
			'interactive' => false,
		);

		$args   = wp_parse_args( $args, $defaults );
		$rating = floatval( $rating );
		$rating = max( 0, min( 5, $rating ) ); // Ensure rating is between 0 and 5.

		$size_classes = array(
			'small'  => 'star-rating-sm',
			'medium' => 'star-rating-md',
			'large'  => 'star-rating-lg',
		);

		$size_class        = $size_classes[ $args['size'] ] ?? 'star-rating-md';
		$interactive_class = $args['interactive'] ? 'star-rating-interactive' : '';

		$html = '<div class="splms-star-rating ' . esc_attr( $size_class ) . ' ' . esc_attr( $interactive_class ) . '" data-rating="' . esc_attr( $rating ) . '">';

		// Generate stars.
		for ( $i = 1; $i <= 5; $i++ ) {
			$star_class = 'star';

			if ( $rating >= $i ) {
				$star_class .= ' star-full';
			} elseif ( $rating >= ( $i - 0.5 ) ) {
				$star_class .= ' star-half';
			} else {
				$star_class .= ' star-empty';
			}

			$html .= '<span class="' . esc_attr( $star_class ) . '">★</span>';
		}

		// Add rating text.
		if ( $args['show_text'] ) {
			$html .= '<span class="rating-text">' . number_format( $rating, 1 ) . '</span>';
		}

		// Add review count.
		if ( $args['show_count'] && $args['count'] > 0 ) {
			$html .= '<span class="rating-count">(' . number_format( $args['count'] ) . ')</span>';
		}

		$html .= '</div>';

		return $html;
	}

	/**
	 * Determine review status based on moderation settings.
	 *
	 * @param string $content    Review content.
	 * @param string $moderation Moderation setting.
	 *
	 * @return string Review status.
	 */
	public static function determine_review_status( $content, $moderation ) {
		switch ( $moderation ) {
			case 'none':
				return 'published';

			case 'manual':
				return 'pending';

			case 'auto':
			default:
				// Run auto-moderation checks.
				if ( self::passes_auto_moderation( $content ) ) {
					return 'published';
				} else {
					return 'pending';
				}
		}
	}

	/**
	 * Check if content passes auto-moderation.
	 *
	 * @param string $content Review content.
	 *
	 * @return bool Whether content passes moderation.
	 */
	public static function passes_auto_moderation( $content ) {
		// Check minimum length.
		if ( strlen( trim( $content ) ) < 10 ) {
			return false;
		}

		// Check for spam patterns.
		if ( self::is_spam( $content ) ) {
			return false;
		}

		// Check for profanity.
		if ( self::contains_profanity( $content ) ) {
			return false;
		}

		// Check for excessive caps.
		if ( self::has_excessive_caps( $content ) ) {
			return false;
		}

		// Check for excessive repeated characters.
		if ( self::has_excessive_repetition( $content ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Check if content is spam.
	 *
	 * @param string $content Content to check.
	 *
	 * @return bool Whether content is spam.
	 */
	private static function is_spam( $content ) {
		$spam_patterns = array(
			// URLs.
			'/https?:\/\/[^\s]+/i',
			// Email addresses.
			'/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/i',
			// Phone numbers.
			'/\b\d{3}[-.]?\d{3}[-.]?\d{4}\b/',
			// Common spam phrases.
			'/\b(buy now|click here|limited time|act now|free trial|guarantee|risk free)\b/i',
			// Excessive punctuation.
			'/[!?]{3,}/',
		);

		foreach ( $spam_patterns as $pattern ) {
			if ( preg_match( $pattern, $content ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if content contains profanity.
	 *
	 * @param string $content Content to check.
	 *
	 * @return bool Whether content contains profanity.
	 */
	private static function contains_profanity( $content ) {
		// Basic profanity filter - you should expand this list.
		$profanity_words = array(
			'damn',
			'hell',
			'crap',
			'stupid',
			'idiot',
			'moron',
			// Add more words as needed, but be careful not to be overly restrictive.
		);

		$content_lower = strtolower( $content );

		foreach ( $profanity_words as $word ) {
			if ( strpos( $content_lower, $word ) !== false ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if content has excessive capital letters.
	 *
	 * @param string $content Content to check.
	 *
	 * @return bool Whether content has excessive caps.
	 */
	private static function has_excessive_caps( $content ) {
		$content_length = strlen( $content );

		if ( $content_length < 20 ) {
			return false; // Too short to judge.
		}

		$caps_count      = strlen( preg_replace( '/[^A-Z]/', '', $content ) );
		$caps_percentage = ( $caps_count / $content_length ) * 100;

		return $caps_percentage > 50; // More than 50% caps.
	}

	/**
	 * Check if content has excessive character repetition.
	 *
	 * @param string $content Content to check.
	 *
	 * @return bool Whether content has excessive repetition.
	 */
	private static function has_excessive_repetition( $content ) {
		// Check for more than 3 consecutive identical characters.
		return preg_match( '/(.)\1{3,}/', $content );
	}

	/**
	 * Validate review data.
	 *
	 * @param int    $course_id Course ID.
	 * @param int    $rating    Rating value.
	 * @param string $content   Review content.
	 * @param int    $user_id   User ID (optional).
	 *
	 * @return true|WP_Error True if valid, WP_Error if not.
	 */
	public static function validate_review_data( $course_id, $rating, $content, $user_id = null ) {
		// Validate course exists.
		$post_type = get_post_type( $course_id );
		if ( ! get_post( $course_id ) || SPLMS_POST_TYPES['course'] !== $post_type ) {
			return new WP_Error( 'invalid_course', __( 'Invalid course.', 'skillpulse-lms' ) );
		}

		// Validate rating.
		if ( ! is_numeric( $rating ) || $rating < 1 || $rating > 5 ) {
			return new WP_Error( 'invalid_rating', __( 'Rating must be between 1 and 5.', 'skillpulse-lms' ) );
		}

		// Validate content.
		$content = trim( $content );
		if ( empty( $content ) ) {
			return new WP_Error( 'empty_content', __( 'Review content is required.', 'skillpulse-lms' ) );
		}

		if ( strlen( $content ) < 10 ) {
			return new WP_Error( 'content_too_short', __( 'Review must be at least 10 characters long.', 'skillpulse-lms' ) );
		}

		if ( strlen( $content ) > 5000 ) {
			return new WP_Error( 'content_too_long', __( 'Review must be less than 5000 characters.', 'skillpulse-lms' ) );
		}

		// Check if user is enrolled (if user_id provided).
		if ( $user_id && ! splms_is_user_enrolled( $course_id, $user_id ) ) {
			return new WP_Error( 'not_enrolled', __( 'You must be enrolled in this course to review it.', 'skillpulse-lms' ) );
		}

		return true;
	}

	/**
	 * Send review notifications.
	 *
	 * @param int    $review_id Review ID.
	 * @param string $status    Review status.
	 */
	public static function send_review_notifications( $review_id, $status ) {
		$review = SPLMS_Review_Manager::get_review( $review_id );

		if ( ! $review ) {
			return;
		}

		// Notify course author.
		$course_author_id = get_post_field( 'post_author', $review['course_id'] );
		if ( $course_author_id && 'published' === $status ) {
			self::send_author_notification( $review, $course_author_id );
		}

		// Notify admin if review is pending.
		if ( 'pending' === $status ) {
			self::send_admin_moderation_notification( $review );
		}

		/**
		 * Action hook for custom review notifications.
		 *
		 * @param array  $review Review data.
		 * @param string $status Review status.
		 */
		do_action( 'splms_review_notification', $review, $status );
	}

	/**
	 * Send notification to course author.
	 *
	 * @param array $review    Review data.
	 * @param int   $author_id User ID.
	 */
	private static function send_author_notification( $review, $author_id ) {
		$course_author = get_userdata( $author_id );
		$course_title  = get_the_title( $review['course_id'] );

		if ( ! $course_author ) {
			return;
		}

		// Use email template system.
		$replacements = array(
			'author_name'    => $course_author->display_name,
			'course_title'   => $course_title,
			'reviewer_name'  => $review['author_name'],
			'rating'         => $review['rating'],
			'review_content' => wp_strip_all_tags( $review['content'] ),
			'course_url'     => get_permalink( $review['course_id'] ),
			'site_name'      => get_bloginfo( 'name' ),
		);

		// Send notification using unified system.
		SkillPulse_LMS_Notification_Dispatcher::get_instance()->send_notification(
			$course_author->user_email,
			'review_new',
			$replacements,
			$author_id
		);
	}

	/**
	 * Send moderation notification to admin.
	 *
	 * @param array $review Review data.
	 */
	private static function send_admin_moderation_notification( $review ) {
		$admin_email  = get_option( 'admin_email' );
		$course_title = get_the_title( $review['course_id'] );

		// Use email template system.
		$replacements = array(
			'course_title'   => $course_title,
			'reviewer_name'  => $review['author_name'],
			'rating'         => $review['rating'],
			'review_content' => wp_strip_all_tags( $review['content'] ),
			'admin_url'      => admin_url( 'admin.php?page=splms-reviews' ),
			'site_name'      => get_bloginfo( 'name' ),
		);

		// Send notification using unified system (admin doesn't need in-app notification).
		SkillPulse_LMS_Notification_Dispatcher::get_instance()->send_notification(
			$admin_email,
			'review_moderation',
			$replacements
		);
	}

	/**
	 * Send moderation result notification to review author.
	 *
	 * @param int    $review_id Review ID.
	 * @param string $status    New status.
	 */
	public static function send_moderation_notification( $review_id, $status ) {
		$review = SPLMS_Review_Manager::get_review( $review_id );

		if ( ! $review ) {
			return;
		}

		$user         = get_userdata( $review['user_id'] );
		$course_title = get_the_title( $review['course_id'] );

		if ( ! $user ) {
			return;
		}

		// Define status-specific styling and messages.
		$status_config = array(
			'published' => array(
				'message'         => sprintf(
				/* translators: %s: Course title. */
					__( "Good news! Your review for \"%s\" has been approved and is now published.\n\nThank you for sharing your feedback!", 'skillpulse-lms' ),
					$course_title
				),
				'background'      => '#d4edda',
				'color'           => '#28a745',
				'text_color'      => '#155724',
				'additional_text' => __( 'Your review is now visible to all students and can help others make informed decisions.', 'skillpulse-lms' ),
			),
			'rejected'  => array(
				'message'         => sprintf(
				/* translators: %s: Course title. */
					__( "Your review for \"%s\" has been rejected as it didn't meet our community guidelines.", 'skillpulse-lms' ),
					$course_title
				),
				'background'      => '#f8d7da',
				'color'           => '#dc3545',
				'text_color'      => '#721c24',
				'additional_text' => __( 'If you have questions, please contact us.', 'skillpulse-lms' ),
			),
			'flagged'   => array(
				'message'         => sprintf(
				/* translators: %s: Course title. */
					__( 'Your review for "%s" has been flagged for further review.', 'skillpulse-lms' ),
					$course_title
				),
				'background'      => '#fff3cd',
				'color'           => '#ffc107',
				'text_color'      => '#856404',
				'additional_text' => __( "We'll notify you once the review is complete.", 'skillpulse-lms' ),
			),
		);

		$config = $status_config[ $status ] ?? null;
		if ( ! $config ) {
			return;
		}

		// Use email template system.
		$replacements = array(
			'user_name'          => $user->display_name,
			'course_title'       => $course_title,
			'status'             => $status,
			'status_message'     => $config['message'],
			'status_background'  => $config['background'],
			'status_color'       => $config['color'],
			'status_text_color'  => $config['text_color'],
			'additional_message' => $config['additional_text'],
			'course_url'         => get_permalink( $review['course_id'] ),
			'site_name'          => get_bloginfo( 'name' ),
		);

		// Send notification using unified system.
		SkillPulse_LMS_Notification_Dispatcher::get_instance()->send_notification(
			$user->user_email,
			'review_moderation_result',
			$replacements,
			$user->ID
		);
	}
}
