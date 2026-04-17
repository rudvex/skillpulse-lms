<?php
/**
 * Review Manager
 *
 * Handles course review functionality.
 *
 * @package SkillPulse_LMS
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class SPLMS_Review_Manager
 *
 * Main class for managing course reviews.
 */
class SPLMS_Review_Manager {

	/**
	 * Instance of this class.
	 *
	 * @var SPLMS_Review_Manager
	 */
	private static $instance = null;

	/**
	 * Get instance.
	 *
	 * @return SPLMS_Review_Manager
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
		// No hooks needed here - handled by main reviews class.
	}

	/**
	 * Get rating summary for a course.
	 *
	 * @since 1.0.0
	 *
	 * @param int $course_id Course ID.
	 * @return array {
	 *     Rating summary data.
	 *
	 *     @type float $average_rating  Average rating (0-5).
	 *     @type int   $total_reviews   Total number of reviews.
	 *     @type array $rating_breakdown Breakdown by star rating.
	 * }
	 */
	public static function get_rating_summary( $course_id ) {
		// Get all approved reviews.
		$args = array(
			'post_id' => $course_id,
			'type'    => 'splms_course_review',
			'status'  => 'approve',
			'parent'  => 0,
		);

		$reviews = get_comments( $args );

		$total_reviews = count( $reviews );
		$rating_sum    = 0;
		$breakdown     = array(
			'5' => 0,
			'4' => 0,
			'3' => 0,
			'2' => 0,
			'1' => 0,
		);

		foreach ( $reviews as $review ) {
			$rating = get_comment_meta( $review->comment_ID, 'rating', true );
			$rating = (int) $rating;

			if ( $rating >= 1 && $rating <= 5 ) {
				$rating_sum += $rating;
				++$breakdown[ (string) $rating ];
			}
		}

		$average_rating = $total_reviews > 0 ? round( $rating_sum / $total_reviews, 1 ) : 0;

		return array(
			'average_rating'   => $average_rating,
			'total_reviews'    => $total_reviews,
			'rating_breakdown' => $breakdown,
		);
	}

	/**
	 * Get course reviews with pagination.
	 *
	 * @since 1.0.0
	 *
	 * @param int   $course_id Course ID.
	 * @param array $args {
	 *     Optional. Query arguments.
	 *
	 *     @type string $status   Review status. Default 'published'.
	 *     @type int    $page     Page number. Default 1.
	 *     @type int    $per_page Reviews per page. Default 10.
	 *     @type string $orderby  Order by field. Default 'comment_date'.
	 *     @type string $order    Order direction. Default 'DESC'.
	 * }
	 * @return array {
	 *     Reviews data.
	 *
	 *     @type array $reviews List of review objects.
	 *     @type int   $total   Total number of reviews.
	 *     @type int   $pages   Total number of pages.
	 * }
	 */
	public static function get_course_reviews( $course_id, $args = array() ) {
		$defaults = array(
			'status'   => 'published',
			'page'     => 1,
			'per_page' => 10,
			'orderby'  => 'comment_date',
			'order'    => 'DESC',
		);

		$args = wp_parse_args( $args, $defaults );

		$comment_args = array(
			'post_id' => $course_id,
			'type'    => 'splms_course_review',
			'status'  => ( 'published' === $args['status'] ) ? 'approve' : 'hold',
			'number'  => $args['per_page'],
			'offset'  => ( $args['page'] - 1 ) * $args['per_page'],
			'orderby' => $args['orderby'],
			'order'   => $args['order'],
			'parent'  => 0,
		);

		$comments = get_comments( $comment_args );
		$total    = get_comments(
			array_merge(
				$comment_args,
				array( 'count' => true )
			)
		);

		$reviews = array();
		foreach ( $comments as $comment ) {
			$review = self::format_review( $comment );
			if ( $review ) {
				$reviews[] = $review;
			}
		}

		return array(
			'reviews' => $reviews,
			'total'   => $total,
			'pages'   => ceil( $total / $args['per_page'] ),
		);
	}

	/**
	 * Get a single review by ID.
	 *
	 * @since 1.0.0
	 *
	 * @param int $review_id Review (comment) ID.
	 * @return array|null Review data or null if not found.
	 */
	public static function get_review( $review_id ) {
		$comment = get_comment( $review_id );

		if ( ! $comment ) {
			return null;
		}

		return self::format_review( $comment );
	}

	/**
	 * Format review comment into review object.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Comment $comment Comment object.
	 * @return array|null Formatted review or null if invalid.
	 */
	private static function format_review( $comment ) {
		if ( ! $comment || 'splms_course_review' !== $comment->comment_type ) {
			return null;
		}

		$rating        = get_comment_meta( $comment->comment_ID, 'rating', true );
		$helpful_votes = get_comment_meta( $comment->comment_ID, 'helpful_votes', true );
		$helpful_count = is_numeric( $helpful_votes ) ? (int) $helpful_votes : 0;

		return array(
			'id'            => $comment->comment_ID,
			'course_id'     => $comment->comment_post_ID,
			'user_id'       => $comment->user_id,
			'author_name'   => $comment->comment_author,
			'author_email'  => $comment->comment_author_email,
			'content'       => $comment->comment_content,
			'rating'        => (int) $rating,
			'status'        => ( '1' === $comment->comment_approved || 1 === $comment->comment_approved ) ? 'published' : 'pending',
			'created_at'    => $comment->comment_date,
			'author_avatar' => get_avatar_url( $comment->user_id, array( 'size' => 48 ) ),
			'helpful_count' => $helpful_count,
		);
	}

	/**
	 * Submit a new review.
	 *
	 * @since 1.0.0
	 *
	 * @param int    $course_id   Course ID.
	 * @param int    $user_id     User ID.
	 * @param int    $rating      Rating (1-5).
	 * @param string $review_text Review text.
	 * @return array {
	 *     Submission result.
	 *
	 *     @type bool   $success    Whether submission was successful.
	 *     @type int    $review_id  Review ID if successful.
	 *     @type string $message    Success or error message.
	 *     @type string $code       Error code if failed.
	 * }
	 */
	public static function submit_review( $course_id, $user_id, $rating, $review_text ) {
		// Check permissions.
		$permission = SPLMS_Review_Permissions::can_user_review( $user_id, $course_id );

		if ( ! $permission['can_review'] ) {
			return array(
				'success' => false,
				'message' => $permission['message'],
				'code'    => $permission['code'],
			);
		}

		// Validate rating.
		if ( $rating < 1 || $rating > 5 ) {
			return array(
				'success' => false,
				'message' => __( 'Rating must be between 1 and 5.', 'skillpulse-lms' ),
				'code'    => 'invalid_rating',
			);
		}

		// Validate review text.
		$min_length = splms_get_setting( 'reviews_min_length', 10 );
		$max_length = splms_get_setting( 'reviews_max_length', 500 );

		if ( strlen( $review_text ) < $min_length ) {
			return array(
				'success' => false,
				/* translators: %d: Minimum character count. */
				'message' => sprintf( __( 'Review must be at least %d characters.', 'skillpulse-lms' ), $min_length ),
				'code'    => 'review_too_short',
			);
		}

		if ( strlen( $review_text ) > $max_length ) {
			return array(
				'success' => false,
				/* translators: %d: Maximum character count. */
				'message' => sprintf( __( 'Review cannot exceed %d characters.', 'skillpulse-lms' ), $max_length ),
				'code'    => 'review_too_long',
			);
		}

		// Check if moderation is enabled.
		$require_moderation = splms_get_setting( 'reviews_require_moderation', false );
		$comment_status     = $require_moderation ? 'hold' : 1;

		// Get user data.
		$user = get_userdata( $user_id );

		// Validate user exists.
		if ( ! $user ) {
			return array(
				'success' => false,
				'message' => __( 'Invalid user.', 'skillpulse-lms' ),
				'code'    => 'invalid_user',
			);
		}

		// Prepare comment data for wp_insert_comment.
		$comment_data = array(
			'comment_post_ID'      => (int) $course_id,
			'comment_author'       => $user->display_name,
			'comment_author_email' => $user->user_email,
			'comment_author_url'   => '',
			'comment_content'      => sanitize_textarea_field( $review_text ),
			'comment_type'         => 'splms_course_review',
			'comment_parent'       => 0,
			'user_id'              => (int) $user_id,
			'comment_approved'     => $comment_status,
			'comment_author_IP'    => isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '',
			'comment_agent'        => isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '',
		);

		$comment_id = wp_insert_comment( $comment_data );

		if ( ! $comment_id ) {
			return array(
				'success' => false,
				'message' => __( 'Failed to submit review. Please try again.', 'skillpulse-lms' ),
				'code'    => 'insert_failed',
			);
		}

		// Add metadata.
		add_comment_meta( $comment_id, 'rating', $rating );
		add_comment_meta( $comment_id, 'helpful_votes', 0 );

		return array(
			'success'   => true,
			'review_id' => $comment_id,
			'message'   => $require_moderation
				? __( 'Review submitted successfully. It will be visible after moderation.', 'skillpulse-lms' )
				: __( 'Review published successfully!', 'skillpulse-lms' ),
			'status'    => $require_moderation ? 'pending' : 'approved',
		);
	}
}
