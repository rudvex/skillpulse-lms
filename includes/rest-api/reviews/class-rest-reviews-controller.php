<?php
/**
 * REST API: Reviews Controller
 *
 * Handles all review-related REST API endpoints.
 *
 * @package SkillPulse_LMS
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class SPLMS_REST_Reviews_Controller
 *
 * REST API controller for course reviews.
 */
class SPLMS_REST_Reviews_Controller extends WP_REST_Controller {

	/**
	 * Namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'splms/v1';

	/**
	 * Constructor.
	 */
	public function __construct() {
		// Routes are registered via SkillPulse_LMS_Rest_API::register_routes().
	}

	/**
	 * Register routes.
	 *
	 * @since 1.0.0
	 */
	public function register_routes() {
		// Get reviews for a course.
		register_rest_route(
			$this->namespace,
			'/courses/(?P<course_id>\d+)/reviews',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_reviews' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'course_id' => array(
						'required'          => true,
						'validate_callback' => function ( $param ) {
							return is_numeric( $param );
						},
					),
					'page'      => array(
						'default'           => 1,
						'sanitize_callback' => 'absint',
					),
					'per_page'  => array(
						'default'           => 10,
						'sanitize_callback' => 'absint',
					),
					'orderby'   => array(
						'default'           => 'date',
						'sanitize_callback' => 'sanitize_text_field',
					),
					'order'     => array(
						'default'           => 'desc',
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			)
		);

		// Submit a review.
		register_rest_route(
			$this->namespace,
			'/courses/(?P<course_id>\d+)/reviews',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'submit_review' ),
				'permission_callback' => array( $this, 'check_submit_permission' ),
				'args'                => array(
					'course_id'   => array(
						'required'          => true,
						'validate_callback' => function ( $param ) {
							return is_numeric( $param );
						},
					),
					'rating'      => array(
						'required'          => true,
						'validate_callback' => function ( $param ) {
							return is_numeric( $param ) && $param >= 1 && $param <= 5;
						},
					),
					'review_text' => array(
						'required'          => true,
						'sanitize_callback' => 'sanitize_textarea_field',
					),
				),
			)
		);

		// Update a review.
		register_rest_route(
			$this->namespace,
			'/reviews/(?P<review_id>\d+)',
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'update_review' ),
				'permission_callback' => array( $this, 'check_edit_permission' ),
				'args'                => array(
					'review_id'   => array(
						'required'          => true,
						'validate_callback' => function ( $param ) {
							return is_numeric( $param );
						},
					),
					'rating'      => array(
						'required'          => true,
						'validate_callback' => function ( $param ) {
							return is_numeric( $param ) && $param >= 1 && $param <= 5;
						},
					),
					'review_text' => array(
						'required'          => true,
						'sanitize_callback' => 'sanitize_textarea_field',
					),
				),
			)
		);

		// Delete a review.
		register_rest_route(
			$this->namespace,
			'/reviews/(?P<review_id>\d+)',
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( $this, 'delete_review' ),
				'permission_callback' => array( $this, 'check_delete_permission' ),
				'args'                => array(
					'review_id' => array(
						'required'          => true,
						'validate_callback' => function ( $param ) {
							return is_numeric( $param );
						},
					),
				),
			)
		);

		// Vote helpful on a review.
		register_rest_route(
			$this->namespace,
			'/reviews/(?P<review_id>\d+)/vote',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'vote_helpful' ),
				'permission_callback' => array( $this, 'check_vote_permission' ),
				'args'                => array(
					'review_id' => array(
						'required'          => true,
						'validate_callback' => function ( $param ) {
							return is_numeric( $param );
						},
					),
				),
			)
		);

		// Reply to a review (instructor/admin).
		register_rest_route(
			$this->namespace,
			'/reviews/(?P<review_id>\d+)/reply',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'add_reply' ),
				'permission_callback' => array( $this, 'check_reply_permission' ),
				'args'                => array(
					'review_id'  => array(
						'required'          => true,
						'validate_callback' => function ( $param ) {
							return is_numeric( $param );
						},
					),
					'reply_text' => array(
						'required'          => true,
						'sanitize_callback' => 'sanitize_textarea_field',
					),
				),
			)
		);
	}

	/**
	 * Get reviews for a course.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response object.
	 */
	public function get_reviews( $request ) {
		$course_id = $request->get_param( 'course_id' );
		$page      = $request->get_param( 'page' );
		$per_page  = $request->get_param( 'per_page' );
		$orderby   = $request->get_param( 'orderby' );
		$order     = $request->get_param( 'order' );

		// Build comment query args.
		$args = array(
			'post_id' => $course_id,
			'type'    => 'splms_course_review',
			'status'  => 'approve',
			'parent'  => 0,
			'number'  => $per_page,
			'offset'  => ( $page - 1 ) * $per_page,
			'orderby' => 'comment_date',
			'order'   => $order,
		);

		// Handle different sort options.
		if ( 'rating' === $orderby ) {
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Necessary for review sorting functionality.
			$args['meta_key'] = 'rating';
			$args['orderby']  = 'meta_value_num';
		} elseif ( 'helpful' === $orderby ) {
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Necessary for review sorting functionality.
			$args['meta_key'] = 'helpful_votes';
			$args['orderby']  = 'meta_value_num';
		}

		// Get reviews.
		$comments = get_comments( $args );

		// Get total count.
		$total_args          = $args;
		$total_args['count'] = true;
		unset( $total_args['number'], $total_args['offset'] );
		$total = get_comments( $total_args );

		// Format reviews.
		$reviews = array();
		foreach ( $comments as $comment ) {
			$reviews[] = $this->format_review( $comment );
		}

		// Get rating summary.
		$summary = SPLMS_Review_Manager::get_rating_summary( $course_id );

		return new WP_REST_Response(
			array(
				'success' => true,
				'data'    => array(
					'reviews'    => $reviews,
					'pagination' => array(
						'total'        => (int) $total,
						'total_pages'  => ceil( $total / $per_page ),
						'current_page' => $page,
						'per_page'     => $per_page,
					),
					'summary'    => $summary,
				),
			),
			200
		);
	}

	/**
	 * Submit a new review.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response object.
	 */
	public function submit_review( $request ) {
		$course_id   = $request->get_param( 'course_id' );
		$rating      = $request->get_param( 'rating' );
		$review_text = $request->get_param( 'review_text' );
		$user_id     = get_current_user_id();

		// Use the manager to submit review.
		$result = SPLMS_Review_Manager::submit_review( $course_id, $user_id, $rating, $review_text );

		if ( ! $result['success'] ) {
			return new WP_REST_Response(
				array(
					'success' => false,
					'error'   => array(
						'code'    => $result['code'],
						'message' => $result['message'],
					),
				),
				403
			);
		}

		return new WP_REST_Response(
			array(
				'success' => true,
				'data'    => array(
					'review_id' => $result['review_id'],
					'status'    => isset( $result['status'] ) ? $result['status'] : 'approved',
					'message'   => $result['message'],
				),
			),
			201
		);
	}

	/**
	 * Update an existing review.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response object.
	 */
	public function update_review( $request ) {
		$review_id   = $request->get_param( 'review_id' );
		$rating      = $request->get_param( 'rating' );
		$review_text = $request->get_param( 'review_text' );
		$user_id     = get_current_user_id();

		// Get the comment.
		$comment = get_comment( $review_id );

		if ( ! $comment || 'splms_course_review' !== $comment->comment_type ) {
			return new WP_REST_Response(
				array(
					'success' => false,
					'error'   => array(
						'code'    => 'review_not_found',
						'message' => __( 'Review not found.', 'skillpulse-lms' ),
					),
				),
				404
			);
		}

		// Validate review text length.
		$min_length = splms_get_setting( 'reviews_min_length', 10 );
		$max_length = splms_get_setting( 'reviews_max_length', 500 );

		if ( strlen( $review_text ) < $min_length ) {
			return new WP_REST_Response(
				array(
					'success' => false,
					'error'   => array(
						'code'    => 'review_too_short',
						/* translators: %d: Minimum character count. */
						'message' => sprintf( __( 'Review must be at least %d characters.', 'skillpulse-lms' ), $min_length ),
					),
				),
				400
			);
		}

		if ( strlen( $review_text ) > $max_length ) {
			return new WP_REST_Response(
				array(
					'success' => false,
					'error'   => array(
						'code'    => 'review_too_long',
						/* translators: %d: Maximum character count. */
						'message' => sprintf( __( 'Review cannot exceed %d characters.', 'skillpulse-lms' ), $max_length ),
					),
				),
				400
			);
		}

		// Update comment.
		$updated = wp_update_comment(
			array(
				'comment_ID'      => $review_id,
				'comment_content' => $review_text,
			)
		);

		if ( is_wp_error( $updated ) ) {
			return new WP_REST_Response(
				array(
					'success' => false,
					'error'   => array(
						'code'    => 'update_failed',
						'message' => __( 'Failed to update review.', 'skillpulse-lms' ),
					),
				),
				500
			);
		}

		// Update rating.
		update_comment_meta( $review_id, 'rating', $rating );

		return new WP_REST_Response(
			array(
				'success' => true,
				'data'    => array(
					'review_id' => $review_id,
					'message'   => __( 'Review updated successfully.', 'skillpulse-lms' ),
				),
			),
			200
		);
	}

	/**
	 * Delete a review.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response object.
	 */
	public function delete_review( $request ) {
		$review_id = $request->get_param( 'review_id' );

		// Get the comment.
		$comment = get_comment( $review_id );

		if ( ! $comment || 'splms_course_review' !== $comment->comment_type ) {
			return new WP_REST_Response(
				array(
					'success' => false,
					'error'   => array(
						'code'    => 'review_not_found',
						'message' => __( 'Review not found.', 'skillpulse-lms' ),
					),
				),
				404
			);
		}

		// Delete the comment.
		$deleted = wp_delete_comment( $review_id, true );

		if ( ! $deleted ) {
			return new WP_REST_Response(
				array(
					'success' => false,
					'error'   => array(
						'code'    => 'delete_failed',
						'message' => __( 'Failed to delete review.', 'skillpulse-lms' ),
					),
				),
				500
			);
		}

		return new WP_REST_Response(
			array(
				'success' => true,
				'data'    => array(
					'message' => __( 'Review deleted successfully.', 'skillpulse-lms' ),
				),
			),
			200
		);
	}

	/**
	 * Vote helpful on a review.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response object.
	 */
	public function vote_helpful( $request ) {
		$review_id = $request->get_param( 'review_id' );
		$user_id   = get_current_user_id();

		// Check if user already voted.
		$voted_reviews = get_user_meta( $user_id, 'voted_reviews', true );
		if ( ! is_array( $voted_reviews ) ) {
			$voted_reviews = array();
		}

		if ( in_array( $review_id, $voted_reviews, true ) ) {
			// Remove vote.
			$voted_reviews = array_diff( $voted_reviews, array( $review_id ) );
			update_user_meta( $user_id, 'voted_reviews', $voted_reviews );

			// Decrease count.
			$current_votes = get_comment_meta( $review_id, 'helpful_votes', true );
			$new_votes     = max( 0, (int) $current_votes - 1 );
			update_comment_meta( $review_id, 'helpful_votes', $new_votes );

			return new WP_REST_Response(
				array(
					'success' => true,
					'data'    => array(
						'helpful_votes' => $new_votes,
						'user_voted'    => false,
					),
				),
				200
			);
		} else {
			// Add vote.
			$voted_reviews[] = $review_id;
			update_user_meta( $user_id, 'voted_reviews', $voted_reviews );

			// Increase count.
			$current_votes = get_comment_meta( $review_id, 'helpful_votes', true );
			$new_votes     = (int) $current_votes + 1;
			update_comment_meta( $review_id, 'helpful_votes', $new_votes );

			return new WP_REST_Response(
				array(
					'success' => true,
					'data'    => array(
						'helpful_votes' => $new_votes,
						'user_voted'    => true,
					),
				),
				200
			);
		}
	}

	/**
	 * Add reply to a review.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response object.
	 */
	public function add_reply( $request ) {
		$review_id  = $request->get_param( 'review_id' );
		$reply_text = $request->get_param( 'reply_text' );
		$user_id    = get_current_user_id();

		// Get the parent review.
		$parent_comment = get_comment( $review_id );

		if ( ! $parent_comment || 'splms_course_review' !== $parent_comment->comment_type ) {
			return new WP_REST_Response(
				array(
					'success' => false,
					'error'   => array(
						'code'    => 'review_not_found',
						'message' => __( 'Review not found.', 'skillpulse-lms' ),
					),
				),
				404
			);
		}

		// Get user data.
		$user = get_userdata( $user_id );

		// Insert reply as a child comment.
		$reply_data = array(
			'comment_post_ID'      => $parent_comment->comment_post_ID,
			'comment_author'       => $user->display_name,
			'comment_author_email' => $user->user_email,
			'comment_content'      => $reply_text,
			'comment_type'         => 'splms_course_review',
			'comment_parent'       => $review_id,
			'comment_approved'     => 'approve',
			'user_id'              => $user_id,
		);

		$reply_id = wp_insert_comment( $reply_data );

		if ( ! $reply_id ) {
			return new WP_REST_Response(
				array(
					'success' => false,
					'error'   => array(
						'code'    => 'reply_failed',
						'message' => __( 'Failed to add reply.', 'skillpulse-lms' ),
					),
				),
				500
			);
		}

		return new WP_REST_Response(
			array(
				'success' => true,
				'data'    => array(
					'reply_id' => $reply_id,
					'message'  => __( 'Reply added successfully.', 'skillpulse-lms' ),
				),
			),
			201
		);
	}

	/**
	 * Format review for API response.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Comment $comment Comment object.
	 * @return array Formatted review data.
	 */
	private function format_review( $comment ) {
		$rating        = get_comment_meta( $comment->comment_ID, 'rating', true );
		$helpful_votes = get_comment_meta( $comment->comment_ID, 'helpful_votes', true );

		// Get user info.
		$user_data = array(
			'id'     => $comment->user_id,
			'name'   => $comment->comment_author,
			'avatar' => get_avatar_url( $comment->user_id, array( 'size' => 96 ) ),
			'role'   => 'student',
		);

		// Get replies.
		$replies = get_comments(
			array(
				'parent' => $comment->comment_ID,
				'status' => 'approve',
			)
		);

		$formatted_replies = array();
		foreach ( $replies as $reply ) {
			$formatted_replies[] = array(
				'reply_id'   => $reply->comment_ID,
				'user'       => array(
					'id'     => $reply->user_id,
					'name'   => $reply->comment_author,
					'avatar' => get_avatar_url( $reply->user_id, array( 'size' => 96 ) ),
					'role'   => 'instructor',
				),
				'reply_text' => $reply->comment_content,
				'created_at' => mysql2date( 'c', $reply->comment_date_gmt, false ),
			);
		}

		// Check if current user voted.
		$user_id       = get_current_user_id();
		$voted_reviews = get_user_meta( $user_id, 'voted_reviews', true );
		$user_voted    = is_array( $voted_reviews ) && in_array( $comment->comment_ID, $voted_reviews, true );

		return array(
			'review_id'     => $comment->comment_ID,
			'course_id'     => $comment->comment_post_ID,
			'user'          => $user_data,
			'rating'        => (int) $rating,
			'review_text'   => $comment->comment_content,
			'helpful_votes' => (int) $helpful_votes,
			'user_voted'    => $user_voted,
			'created_at'    => mysql2date( 'c', $comment->comment_date_gmt, false ),
			'updated_at'    => null,
			'replies'       => $formatted_replies,
		);
	}

	/**
	 * Check if user can submit a review.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return bool True if user can submit.
	 */
	public function check_submit_permission( $request ) {
		return is_user_logged_in();
	}

	/**
	 * Check if user can edit a review.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return bool True if user can edit.
	 */
	public function check_edit_permission( $request ) {
		$review_id = $request->get_param( 'review_id' );
		$user_id   = get_current_user_id();

		if ( ! $user_id ) {
			return false;
		}

		return SPLMS_Review_Permissions::can_user_edit_review( $user_id, $review_id );
	}

	/**
	 * Check if user can delete a review.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return bool True if user can delete.
	 */
	public function check_delete_permission( $request ) {
		$review_id = $request->get_param( 'review_id' );
		$user_id   = get_current_user_id();

		if ( ! $user_id ) {
			return false;
		}

		return SPLMS_Review_Permissions::can_user_delete_review( $user_id, $review_id );
	}

	/**
	 * Check if user can vote.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return bool True if user can vote.
	 */
	public function check_vote_permission( $request ) {
		return is_user_logged_in();
	}

	/**
	 * Check if user can reply to a review.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return bool True if user can reply.
	 */
	public function check_reply_permission( $request ) {
		$review_id = $request->get_param( 'review_id' );
		$user_id   = get_current_user_id();

		if ( ! $user_id ) {
			return false;
		}

		// Get the parent review.
		$comment = get_comment( $review_id );
		if ( ! $comment ) {
			return false;
		}

		$course_id = $comment->comment_post_ID;

		return SPLMS_Review_Permissions::can_user_reply( $user_id, $course_id );
	}
}
