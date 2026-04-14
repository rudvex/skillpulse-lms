<?php
/**
 * Reviews Module - Main Class
 *
 * Orchestrates the course reviews system by loading and coordinating all review sub-modules.
 *
 * @package SkillPulse_LMS
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Reviews Module Class
 *
 * Main class for the reviews system that loads and coordinates all review components.
 *
 * @since 1.0.0
 */
class SkillPulse_LMS_Reviews {

	/**
	 * Instance of this class.
	 *
	 * @var SkillPulse_LMS_Reviews
	 */
	private static $instance = null;

	/**
	 * Review Manager instance.
	 *
	 * @var SPLMS_Review_Manager
	 */
	private $review_manager;

	/**
	 * Get instance of this class.
	 *
	 * @return SkillPulse_LMS_Reviews
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 *
	 * Loads all review sub-modules and sets up the review system.
	 */
	private function __construct() {
		$this->load_dependencies();
		$this->init_components();
	}

	/**
	 * Load review module dependencies.
	 *
	 * @since 1.0.0
	 */
	private function load_dependencies() {
		$review_path = SKILLPULSE_LMS_DIR_PATH . 'includes/modules/reviews/';

		// Load review sub-modules.
		require_once $review_path . 'class-review-manager.php';
		require_once $review_path . 'class-review-permissions.php';
		require_once $review_path . 'class-reviews-helpers.php';
	}

	/**
	 * Initialize review components.
	 *
	 * @since 1.0.0
	 */
	private function init_components() {
		// Initialize Review Manager.
		$this->review_manager = SPLMS_Review_Manager::get_instance();

		// Initialize WordPress comment system hooks.
		$this->init_comment_hooks();
	}

	/**
	 * Initialize WordPress comment system hooks.
	 *
	 * @since 1.0.0
	 */
	private function init_comment_hooks() {
		// Include reviews in WordPress admin comments list.
		add_action( 'pre_get_comments', array( $this, 'include_reviews_in_admin' ) );

		// Add custom comment type label in WordPress admin.
		add_filter( 'admin_comment_types_dropdown', array( $this, 'add_comment_type_filter' ) );

		// Exclude reviews from frontend comment queries.
		add_filter( 'comments_clauses', array( $this, 'exclude_reviews_from_frontend' ), 10, 2 );
	}


	/**
	 * Add course review to comment type filter dropdown in WordPress admin.
	 *
	 * @since 1.0.0
	 *
	 * @param array $comment_types Existing comment types.
	 * @return array Modified comment types.
	 */
	public function add_comment_type_filter( $comment_types ) {
		$comment_types['splms_course_review'] = __( 'Course Reviews', 'skillpulse-lms' );
		return $comment_types;
	}

	/**
	 * Include course reviews in WordPress admin comments list.
	 *
	 * Modifies the comment query to include both standard comments and course reviews
	 * when viewing "All" comments in the admin area.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Comment_Query $query Comment query object.
	 * @return void
	 */
	public function include_reviews_in_admin( $query ) {
		// Only modify admin queries.
		if ( ! is_admin() ) {
			return;
		}

		// Check if we're on the comments screen.
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || 'edit-comments' !== $screen->id ) {
			return;
		}

		// Get current query vars.
		$type = isset( $query->query_vars['type'] ) ? $query->query_vars['type'] : '';

		// If viewing all comments (type is 'comment', 'all', or empty), include course reviews.
		if ( '' === $type || 'all' === $type || 'comment' === $type ) {
			// Include both standard comments and course reviews.
			$query->query_vars['type__in'] = array( '', 'splms_course_review' );
		}
	}

	/**
	 * Exclude course reviews from frontend comment queries.
	 *
	 * Prevents course reviews from appearing in standard WordPress comment lists
	 * on the frontend. Reviews are only shown via dedicated review templates.
	 *
	 * @since 1.0.0
	 *
	 * @param array            $clauses Comment query clauses.
	 * @param WP_Comment_Query $query   Comment query object.
	 * @return array Modified clauses.
	 */
	public function exclude_reviews_from_frontend( $clauses, $query ) {
		global $wpdb;

		// Only apply on frontend.
		if ( is_admin() ) {
			return $clauses;
		}

		$type = isset( $query->query_vars['type'] ) ? $query->query_vars['type'] : '';

		// If specifically querying for course reviews, allow it.
		if ( 'splms_course_review' === $type || ( is_array( $type ) && in_array( 'splms_course_review', $type, true ) ) ) {
			return $clauses;
		}

		// Exclude reviews from regular frontend comment queries.
		if ( empty( $type ) ) {
			$clauses['where'] .= $wpdb->prepare( ' AND comment_type != %s', 'splms_course_review' );
		}

		return $clauses;
	}

	/**
	 * Get Review Manager instance.
	 *
	 * @since 1.0.0
	 *
	 * @return SPLMS_Review_Manager
	 */
	public function get_review_manager() {
		return $this->review_manager;
	}

	/**
	 * Proxy method: Get course reviews.
	 *
	 * @since 1.0.0
	 *
	 * @param int   $course_id Course ID.
	 * @param array $args      Query arguments.
	 * @return array Reviews data.
	 */
	public function get_course_reviews( $course_id, $args = array() ) {
		return SPLMS_Review_Manager::get_course_reviews( $course_id, $args );
	}

	/**
	 * Proxy method: Get course rating summary.
	 *
	 * @since 1.0.0
	 *
	 * @param int $course_id Course ID.
	 * @return array Rating summary.
	 */
	public function get_course_rating_summary( $course_id ) {
		return SPLMS_Review_Manager::get_rating_summary( $course_id );
	}

	/**
	 * Proxy method: Get single review.
	 *
	 * @since 1.0.0
	 *
	 * @param int $review_id Review ID.
	 * @return array|null Review data or null.
	 */
	public function get_review( $review_id ) {
		return SPLMS_Review_Manager::get_review( $review_id );
	}

	/**
	 * Proxy method: Submit review.
	 *
	 * @since 1.0.0
	 *
	 * @param int    $course_id   Course ID.
	 * @param int    $user_id     User ID.
	 * @param int    $rating      Rating (1-5).
	 * @param string $review_text Review text.
	 * @return array Submission result.
	 */
	public function submit_review( $course_id, $user_id, $rating, $review_text ) {
		return SPLMS_Review_Manager::submit_review( $course_id, $user_id, $rating, $review_text );
	}

	/**
	 * Proxy method: Check if user can review.
	 *
	 * @since 1.0.0
	 *
	 * @param int $user_id  User ID.
	 * @param int $course_id Course ID.
	 * @return array Permission check result.
	 */
	public function can_user_review( $user_id, $course_id ) {
		return SPLMS_Review_Permissions::can_user_review( $user_id, $course_id );
	}

	/**
	 * Proxy method: Check if user has reviewed course.
	 *
	 * @since 1.0.0
	 *
	 * @param int $user_id  User ID.
	 * @param int $course_id Course ID.
	 * @return bool True if user has reviewed.
	 */
	public function user_has_reviewed_course( $user_id, $course_id ) {
		return SPLMS_Review_Permissions::user_has_reviewed( $user_id, $course_id );
	}
}
