<?php
/**
 * Core Access Control System
 *
 * Centralized access control for courses and lessons with integration support
 *
 * @since   1.0.0
 * @package SkillPulse_LMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Access Control Class
 *
 * Centralized access control for courses and lessons with integration support.
 *
 * @since 1.0.0
 */
class SkillPulse_LMS_Access_Control {

	/**
	 * Class instance.
	 *
	 * @var null $instance
	 */
	private static $instance = null;

	/**
	 * Get the instance of this class.
	 *
	 * @since 1.0.0
	 *
	 * @return SkillPulse_LMS_Access_Control
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
	 * @since 1.0.0
	 */
	private function __construct() {
		$this->setup_hooks();
	}

	/**
	 * Setup hooks.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	private function setup_hooks() {
		// Frontend access control.
		add_action( 'template_redirect', array( $this, 'check_lesson_access' ) );
		add_action( 'template_redirect', array( $this, 'check_quiz_access' ) );

		// Content filters.
		add_filter( 'the_content', array( $this, 'maybe_restrict_content' ), 5 );

		// Enrollment checks.
		add_filter( 'splms_can_enroll_course', array( $this, 'check_enrollment_access' ), 10, 3 );
	}

	/**
	 * Check if user can access a course.
	 *
	 * @since 1.0.0
	 *
	 * @param int $user_id   User ID.
	 * @param int $course_id Course ID.
	 * @return bool True if user can access the course.
	 */
	public function user_can_access_course( $user_id, $course_id ) {
		// Start with basic permission (true by default).
		$can_access = true;

		// Check if course exists and is published.
		$course = get_post( $course_id );
		if ( ! $course || SPLMS_POST_TYPES['course'] !== $course->post_type || 'publish' !== $course->post_status ) {
			$can_access = false;
		}

		// If basic checks pass, check course-specific access rules.
		if ( $can_access ) {
			$can_access = $this->check_course_access_rules( $user_id, $course_id );
		}

		// Apply the integration filters.
		$can_access = apply_filters( 'splms_can_user_access_course', $can_access, $user_id, $course_id );

		return $can_access;
	}

	/**
	 * Check course-specific access rules based on access type.
	 *
	 * @since 1.0.0
	 *
	 * @param int $user_id   User ID.
	 * @param int $course_id Course ID.
	 * @return bool True if user can access based on rules.
	 */
	private function check_course_access_rules( $user_id, $course_id ) {
		$course_settings = splms_get_course_settings( $course_id );

		// Get access settings.
		$access_settings = isset( $course_settings['course_access_settings'] ) ? $course_settings['course_access_settings'] : array();

		// Check if course is restricted for guests.
		$restrict_for_guests = isset( $access_settings['restrict_course_for_guests'] ) ? (bool) $access_settings['restrict_course_for_guests'] : false;

		if ( $restrict_for_guests && ! is_user_logged_in() ) {
			return false;
		}

		// Check course start date for cohort-based courses.
		if ( ! $this->check_course_start_date_access( $course_id ) ) {
			return false;
		}

		// Get the course access type setting with default.
		$course_access_type = isset( $access_settings['course_access_type'] ) ? $access_settings['course_access_type'] : 'public_free';

		// If paid courses are disabled globally, force all courses to be free.
		if ( 'public_paid' === $course_access_type && ! splms_is_paid_courses_enabled() ) {
			$course_access_type = 'public_free';
		}

		// Check course access type rules (store result, don't return immediately).
		$access_type_allowed = false;

		// Course access type controls who can access the content.
		switch ( $course_access_type ) {
			case 'public_free':
				// Anyone can access for free.
				$access_type_allowed = true;
				break;

			case 'public_paid':
				// Anyone can access but must pay (payment check is handled separately).
				$access_type_allowed = true;
				break;

			case 'invitation_only':
				// Only invited users can access.
				$invited_users       = isset( $access_settings['invited_users'] ) ? $access_settings['invited_users'] : array();
				$access_type_allowed = in_array( $user_id, $invited_users, true );
				break;

			case 'prerequisite_required':
				// User must complete prerequisite course first.
				$prerequisite_course = isset( $access_settings['prerequisite_course'] ) ? $access_settings['prerequisite_course'] : 0;
				if ( $prerequisite_course ) {
					// Validate prerequisite course exists and is published.
					$prereq_post = get_post( $prerequisite_course );
					if ( ! $prereq_post || 'publish' !== $prereq_post->post_status || SPLMS_POST_TYPES['course'] !== $prereq_post->post_type ) {
						// Invalid prerequisite - deny access.
						$access_type_allowed = false;
					} else {
						// Check if user completed prerequisite.
						$access_type_allowed = SkillPulse_LMS_Enrollment::get_instance()->has_user_completed_course( $user_id, $prerequisite_course );
					}
				} else {
					// No prerequisite configured - this shouldn't happen with prerequisite_required type.
					$access_type_allowed = false;
				}
				break;

			default:
				// For unknown access types, fall back to public_free.
				$access_type_allowed = true;
				break;
		}

		// If access type check failed, deny access immediately.
		if ( ! $access_type_allowed ) {
			return false;
		}

		// Check membership requirements (only for public_free and public_paid access types).
		$membership_applicable_types = array( 'public_free', 'public_paid' );
		if ( in_array( $course_access_type, $membership_applicable_types, true ) ) {
			if ( ! splms_user_has_required_membership( $user_id, $course_id ) ) {
				return false;
			}
		}

				return true;
	}

	/**
	 * Check if user can access a lesson.
	 *
	 * @since 1.0.0
	 *
	 * @param int $user_id   User ID.
	 * @param int $lesson_id Lesson ID.
	 * @return bool True if user can access the lesson.
	 */
	public function user_can_access_lesson( $user_id, $lesson_id ) {
		// Start with basic permission (true by default).
		$can_access = true;

		// Check if lesson exists and is published.
		$lesson = get_post( $lesson_id );
		if ( ! $lesson || SPLMS_POST_TYPES['lesson'] !== $lesson->post_type || 'publish' !== $lesson->post_status ) {
			$can_access = false;
		}

		// If basic checks pass, check lesson-specific access rules.
		if ( $can_access ) {
			$can_access = $this->check_lesson_access_rules( $user_id, $lesson_id );
		}

		// Apply the integration filters.
		$can_access = apply_filters( 'splms_can_user_access_lesson', $can_access, $user_id, $lesson_id );

		return $can_access;
	}

	/**
	 * Check if course start date allows access.
	 *
	 * @since 1.0.0
	 *
	 * @param int $course_id Course ID.
	 * @return bool True if course start date allows access.
	 */
	private function check_course_start_date_access( $course_id ) {
		// Get course delivery info.
		$delivery_info = splms_get_course_delivery_info( $course_id );

		// Only check start date for cohort-based courses.
		if ( 'cohort' !== $delivery_info['delivery_mode'] ) {
			return true;
		}

		// If no start date is set, allow access.
		if ( empty( $delivery_info['course_start_date'] ) ) {
			return true;
		}

		// Parse the start date.
		$start_timestamp = strtotime( $delivery_info['course_start_date'] );
		if ( false === $start_timestamp ) {
			// Invalid date format, allow access.
			return true;
		}

		// Check if current time is before start date.
		// phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested -- Timestamp needed for comparison.
		$current_timestamp = current_time( 'timestamp' );

		return $current_timestamp >= $start_timestamp;
	}

	/**
	 * Check lesson-specific access rules including guest preview.
	 *
	 * @since 1.0.0
	 *
	 * @param int $user_id   User ID.
	 * @param int $lesson_id Lesson ID.
	 * @return bool True if user can access based on rules.
	 */
	private function check_lesson_access_rules( $user_id, $lesson_id ) {
		// Get course ID for this lesson.
		$course_id = splms_get_lesson_course( $lesson_id );

		// If user is logged in, check enrollment first.
		if ( $user_id ) {
			if ( $course_id ) {
				$is_enrolled = splms_is_user_enrolled( $course_id, $user_id );
				if ( $is_enrolled ) {
					// Check if enrollment has expired.
					if ( splms_is_enrollment_expired( $user_id, $course_id ) ) {
						return false;
					}
					// Enrolled users: Check course start date (cohort courses).
					if ( ! $this->check_course_start_date_access( $course_id ) ) {
						return false;
					}
					// Check if lesson access has expired (lesson-specific expiration).
					if ( ! $this->check_lesson_access_expiration( $user_id, $lesson_id ) ) {
						return false;
					}
					// Check if lesson is available based on drip content settings.
					if ( ! splms_is_lesson_drip_available( $lesson_id, $user_id ) ) {
						return false;
					}
					return true; // Enrolled users can access all lessons.
				}

				// Check section-based pricing access.
				if ( splms_get_setting( 'enable_section_based_pricing', false ) ) {
					$section_id = splms_get_item_section( $lesson_id );
					if ( $section_id && splms_user_has_section_access( $section_id, $user_id ) ) {
						// User has purchased this section - grant access.
						return true;
					}
				}
			}
		}

		// For non-enrolled users (logged in or not): Check guest preview.
		// Guest preview bypasses course start date (marketing feature).
		return $this->check_guest_preview_access( $lesson_id );
	}

	/**
	 * Check if guest preview is allowed for this lesson.
	 *
	 * @since 1.0.0
	 *
	 * @param int $lesson_id Lesson ID.
	 * @return bool True if guest preview is allowed.
	 */
	private function check_guest_preview_access( $lesson_id ) {
		$course_id = splms_get_lesson_course( $lesson_id );
		if ( ! $course_id ) {
			return false;
		}

		// Get the effective preview limit for this course.
		$effective_limit = splms_get_effective_guest_preview_limit( $course_id );

		// If disabled or 0, no preview allowed.
		if ( 0 === $effective_limit ) {
			return false;
		}

		// If unlimited, all lessons are available.
		if ( 'unlimited' === $effective_limit ) {
			return true;
		}

		// Check if this lesson is within the preview limit.
		return $this->is_lesson_within_preview_limit( $lesson_id, $course_id, $effective_limit );
	}


	/**
	 * Check if lesson is within the preview limit using direct database query.
	 *
	 * @since 1.0.0
	 *
	 * @param int $lesson_id     Lesson ID.
	 * @param int $course_id     Course ID.
	 * @param int $preview_limit Preview limit.
	 * @return bool True if lesson is within preview limit.
	 */
	private function is_lesson_within_preview_limit( $lesson_id, $course_id, $preview_limit ) {
		global $wpdb;

		$relationships_table = esc_sql( $wpdb->prefix . 'splms_relationships' );
		$course_items_table  = esc_sql( $wpdb->prefix . 'splms_course_items' );

		// Get all lessons for this course ordered by section and lesson order.
		$query = "
			SELECT r.child_id
			FROM {$relationships_table} r
			INNER JOIN {$course_items_table} ci ON r.parent_id = ci.item_id
			WHERE ci.course_id = %d
			AND r.child_type = %s
			AND ci.item_type = %s
			ORDER BY ci.order_index ASC, r.order_index ASC
		";

		$all_lessons = $wpdb->get_col(
			$wpdb->prepare(
				$query, // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Query is prepared with placeholders, table names are safe.
				$course_id,
				SPLMS_POST_TYPES['lesson'],
				SPLMS_POST_TYPES['section']
			)
		);

		if ( empty( $all_lessons ) ) {
			return false;
		}

		// Normalize lesson_id to integer for comparison.
		$lesson_id = intval( $lesson_id );

		// Find the position of the current lesson.
		$lesson_position = array_search( $lesson_id, array_map( 'intval', $all_lessons ), true );

		// If lesson not found, return false.
		if ( false === $lesson_position ) {
			return false;
		}

		// Convert to 1-based position and check if within limit.
		++$lesson_position;

		return $lesson_position <= $preview_limit;
	}

	/**
	 * Check guest quiz preview access.
	 *
	 * @since 1.0.0
	 *
	 * @param int $quiz_id Quiz ID.
	 * @return bool True if guest preview is allowed.
	 */
	private function check_guest_quiz_preview_access( $quiz_id ) {
		$course_id = splms_get_quiz_course( $quiz_id );
		if ( ! $course_id ) {
			return false;
		}

		// Get the effective preview limit for this course.
		$effective_limit = splms_get_effective_guest_quiz_preview_limit( $course_id );

		// If disabled or 0, no preview allowed.
		if ( 0 === $effective_limit ) {
			return false;
		}

		// If unlimited, all quizzes are available.
		if ( 'unlimited' === $effective_limit ) {
			return true;
		}

		// Check if this quiz is within the preview limit.
		return $this->is_quiz_within_preview_limit( $quiz_id, $course_id, $effective_limit );
	}

	/**
	 * Check if quiz is within the preview limit using direct database query.
	 *
	 * @since 1.0.0
	 *
	 * @param int $quiz_id       Quiz ID.
	 * @param int $course_id     Course ID.
	 * @param int $preview_limit Preview limit.
	 * @return bool True if quiz is within preview limit.
	 */
	private function is_quiz_within_preview_limit( $quiz_id, $course_id, $preview_limit ) {
		global $wpdb;

		$relationships_table = esc_sql( $wpdb->prefix . 'splms_relationships' );
		$course_items_table  = esc_sql( $wpdb->prefix . 'splms_course_items' );

		// Get all quizzes for this course ordered by section and quiz order.
		$query = "
			SELECT r.child_id
			FROM {$relationships_table} r
			INNER JOIN {$course_items_table} ci ON r.parent_id = ci.item_id
			WHERE ci.course_id = %d
			AND r.child_type = %s
			AND ci.item_type = %s
			ORDER BY ci.order_index ASC, r.order_index ASC
		";

		$all_quizzes = $wpdb->get_col(
			$wpdb->prepare(
				$query, // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Query is prepared with placeholders, table names are safe.
				$course_id,
				SPLMS_POST_TYPES['quiz'],
				SPLMS_POST_TYPES['section']
			)
		);

		if ( empty( $all_quizzes ) ) {
			return false;
		}

		// Normalize quiz_id to integer for comparison.
		$quiz_id = intval( $quiz_id );

		// Find the position of the current quiz.
		$quiz_position = array_search( $quiz_id, array_map( 'intval', $all_quizzes ), true );

		// If quiz not found, return false.
		if ( false === $quiz_position ) {
			return false;
		}

		// Convert to 1-based position and check if within limit.
		++$quiz_position;

		return $quiz_position <= $preview_limit;
	}

	/**
	 * Check if lesson access has expired based on lesson-specific expiration settings.
	 *
	 * @since 1.0.0
	 *
	 * @param int $user_id   User ID.
	 * @param int $lesson_id Lesson ID.
	 * @return bool True if lesson access is still valid, false if expired.
	 */
	private function check_lesson_access_expiration( $user_id, $lesson_id ) {
		$lessons_instance = SkillPulse_LMS_Lessons::get_instance();
		$expiration_info  = $lessons_instance->get_lesson_access_expiration_info( $lesson_id, $user_id );

		// If expiration is not set, access is valid.
		if ( null === $expiration_info['expiration_date'] ) {
			return true;
		}

		// Return whether access is still valid.
		return $expiration_info['is_valid'];
	}

	/**
	 * Check lesson access on frontend.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function check_lesson_access() {

		if ( ! is_singular( SPLMS_POST_TYPES['lesson'] ) ) {
			return;
		}

		$lesson_id = get_the_ID();
		$user_id   = get_current_user_id();

		// Check if user can access the lesson.
		$can_access = $this->user_can_access_lesson( $user_id, $lesson_id );

		if ( ! $can_access ) {
			// Check if this is a guest preview scenario.
			$is_guest_preview = $this->is_guest_preview_lesson( $lesson_id );

			if ( $is_guest_preview ) {
				// Allow access but mark as guest preview.
				$this->handle_guest_preview_access( $lesson_id );
			} else {
				$this->handle_access_denied( 'lesson', $lesson_id );
			}
		}
	}

	/**
	 * Check quiz access on frontend.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function check_quiz_access() {
		if ( ! is_singular( SPLMS_POST_TYPES['quiz'] ) ) {
			return;
		}

		$quiz_id = get_the_ID();
		$user_id = get_current_user_id();

		// Check if user can access the quiz.
		$can_access = $this->user_can_access_quiz( $user_id, $quiz_id );

		if ( ! $can_access ) {
			// Check if this is a guest preview scenario.
			$is_guest_preview = $this->is_guest_preview_quiz( $quiz_id );

			if ( $is_guest_preview ) {
				// Allow access but mark as guest preview.
				$this->handle_guest_preview_access( $quiz_id, 'quiz' );
			} else {
				$this->handle_access_denied( 'quiz', $quiz_id );
			}
		}
	}

	/**
	 * Check if user can access a quiz.
	 *
	 * @since 1.0.0
	 *
	 * @param int $user_id User ID.
	 * @param int $quiz_id Quiz ID.
	 * @return bool True if user can access the quiz.
	 */
	public function user_can_access_quiz( $user_id, $quiz_id ) {
		// Start with basic permission (true by default).
		$can_access = true;

		// Check if quiz exists and is published.
		$quiz = get_post( $quiz_id );
		if ( ! $quiz || SPLMS_POST_TYPES['quiz'] !== $quiz->post_type || 'publish' !== $quiz->post_status ) {
			$can_access = false;
		}

		// If basic checks pass, check quiz-specific access rules.
		if ( $can_access ) {
			$can_access = $this->check_quiz_access_rules( $user_id, $quiz_id );
		}

		// Apply the integration filters.
		$can_access = apply_filters( 'splms_can_user_access_quiz', $can_access, $user_id, $quiz_id );

		return $can_access;
	}

	/**
	 * Check quiz-specific access rules including guest preview.
	 *
	 * @since 1.0.0
	 *
	 * @param int $user_id User ID.
	 * @param int $quiz_id Quiz ID.
	 * @return bool True if user can access based on rules.
	 */
	private function check_quiz_access_rules( $user_id, $quiz_id ) {
		// Get course ID for this quiz.
		$course_id = splms_get_quiz_course( $quiz_id );

		// If user is logged in, check enrollment first.
		if ( $user_id ) {
			if ( $course_id ) {
				$is_enrolled = splms_is_user_enrolled( $course_id, $user_id );
				if ( $is_enrolled ) {
					// Check if enrollment has expired.
					if ( splms_is_enrollment_expired( $user_id, $course_id ) ) {
						return false;
					}
					// Enrolled users: Check course start date (cohort courses).
					if ( ! $this->check_course_start_date_access( $course_id ) ) {
						return false;
					}
					return true; // Enrolled users can access all quizzes.
				}

				// Check section-based pricing access.
				if ( splms_get_setting( 'enable_section_based_pricing', false ) ) {
					$section_id = splms_get_item_section( $quiz_id );
					if ( $section_id && splms_user_has_section_access( $section_id, $user_id ) ) {
						// User has purchased this section - grant access.
						return true;
					}
				}
			}
		}

		// For non-enrolled users (logged in or not): Check guest preview.
		// Guest preview bypasses course start date (marketing feature).
		return $this->check_guest_quiz_preview_access( $quiz_id );
	}

	/**
	 * Check if this lesson is available for guest preview.
	 *
	 * @since 1.0.0
	 *
	 * @param int $lesson_id Lesson ID.
	 * @return bool True if available for guest preview.
	 */
	private function is_guest_preview_lesson( $lesson_id ) {
		return $this->check_guest_preview_access( $lesson_id );
	}

	/**
	 * Check if this quiz is available for guest preview.
	 *
	 * @since 1.0.0
	 *
	 * @param int $quiz_id Quiz ID.
	 * @return bool True if available for guest preview.
	 */
	public function is_guest_preview_quiz( $quiz_id ) {
		return $this->check_guest_quiz_preview_access( $quiz_id );
	}

	/**
	 * Handle guest preview access.
	 *
	 * @since 1.0.0
	 *
	 * @param int    $item_id Item ID (lesson or quiz).
	 * @param string $type    Item type ('lesson' or 'quiz').
	 * @return void
	 */
	private function handle_guest_preview_access( $item_id, $type = 'lesson' ) {
		// Add guest preview notice to the content.
		add_filter(
			'the_content',
			function ( $content ) use ( $item_id, $type ) {
				return $this->add_guest_preview_notice( $content, $item_id, $type );
			},
			15
		);

		// Add guest preview class to body.
		add_filter(
			'body_class',
			function ( $classes ) {
				$classes[] = 'splms-guest-preview';

				return $classes;
			}
		);
	}

	/**
	 * Handle access denied scenarios.
	 *
	 * @since 1.0.0
	 *
	 * @param string $content_type Content type (course or lesson).
	 * @param int    $content_id   Content ID.
	 * @return void
	 */
	private function handle_access_denied( $content_type, $content_id ) {
		// Default behavior - show access denied message.
		$this->show_access_denied_content( $content_type, $content_id );
	}

	/**
	 * Show access denied content.
	 *
	 * @since 1.0.0
	 *
	 * @param string $content_type Content type.
	 * @param int    $content_id   Content ID.
	 * @return void
	 */
	private function show_access_denied_content( $content_type, $content_id ) {
		// Remove default content filters to prevent infinite loops.
		remove_filter( 'the_content', array( $this, 'maybe_restrict_content' ), 5 );

		// Add custom access denied content.
		add_filter(
			'the_content',
			function ( $content ) use ( $content_type, $content_id ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found -- Required by filter signature.
				return $this->get_access_denied_message( $content_type, $content_id );
			},
			10
		);
	}

	/**
	 * Add guest preview notice to lesson content.
	 *
	 * @since 1.0.0
	 *
	 * @param string $content   Original content.
	 * @param int    $lesson_id Lesson ID.
	 * @param string $type      Item type ('lesson' or 'quiz').
	 * @return string Content with notice.
	 */
	private function add_guest_preview_notice( $content, $lesson_id, $type = 'lesson' ) {
		// Get course ID based on content type.
		if ( 'quiz' === $type ) {
			$course_id = splms_get_quiz_course( $lesson_id );
		} else {
			$course_id = splms_get_lesson_course( $lesson_id );
		}

		$course_title = $course_id ? get_the_title( $course_id ) : '';
		$course_url   = $course_id ? get_permalink( $course_id ) : '';

		// Get appropriate message based on content type.
		if ( 'quiz' === $type ) {
			$preview_message = esc_html__( 'You are viewing this quiz in preview mode. Enroll in the course to access all quizzes and features.', 'skillpulse-lms' );
		} else {
			$preview_message = esc_html__( 'You are viewing this lesson in preview mode. Enroll in the course to access all lessons and features.', 'skillpulse-lms' );
		}

		$notice  = '<div class="splms-guest-preview-notice">';
		$notice .= '<div class="notice-content">';
		$notice .= '<div class="notice-icon">👁️</div>';
		$notice .= '<div class="notice-text">';
		$notice .= '<h3>' . esc_html__( 'Preview Mode', 'skillpulse-lms' ) . '</h3>';
		$notice .= '<p>' . $preview_message . '</p>';
		if ( $course_url ) {
			$notice .= '<a href="' . esc_url( $course_url ) . '" class="btn btn-primary">' . esc_html__( 'View Course', 'skillpulse-lms' ) . '</a>';
		}
		$notice .= '</div>';
		$notice .= '</div>';
		$notice .= '</div>';

		return $notice . $content;
	}

	/**
	 * Get access denied message.
	 *
	 * @since 1.0.0
	 *
	 * @param string $content_type Content type.
	 * @param int    $content_id   Content ID.
	 * @return string Access denied message HTML.
	 */
	private function get_access_denied_message( $content_type, $content_id ) {
		$message = sprintf(
			/* translators: %s: Content type. */
			__( 'Access Denied: You do not have permission to view this %s.', 'skillpulse-lms' ),
			$content_type
		);

		// Check if this is a course start date restriction or membership requirement.
		if ( 'course' === $content_type || 'lesson' === $content_type ) {
			$course_id = $content_id;
			if ( 'lesson' === $content_type ) {
				$course_id = splms_get_lesson_course( $content_id );
			}

			if ( $course_id ) {
				// Check membership requirements.
				$user_id = get_current_user_id();
				if ( ! splms_user_has_required_membership( $user_id, $course_id ) ) {
					// If function returns false, it means memberships are required and user doesn't have them.
					$message = __( 'This course requires a membership. Please purchase a membership to access this course.', 'skillpulse-lms' );
				}

				// Check course start date.
				$start_date_info = splms_get_course_start_date_info( $course_id );
				if ( ! $start_date_info['is_available'] && ! empty( $start_date_info['message'] ) ) {
					$message = $start_date_info['message'];
				}
			}
		}

		$content  = '<div class="splms-access-denied">';
		$content .= '<h2>' . esc_html__( 'Access Restricted', 'skillpulse-lms' ) . '</h2>';
		$content .= '<p>' . esc_html( $message ) . '</p>';

		// Add login prompt if user is not logged in.
		if ( ! is_user_logged_in() ) {
			$login_url = wp_login_url( get_permalink() );
			$content  .= '<p><a href="' . esc_url( $login_url ) . '" class="button">' . esc_html__( 'Login to Access', 'skillpulse-lms' ) . '</a></p>';
		}

		$content .= '</div>';

		return apply_filters( 'splms_access_denied_content', $content, $content_type, $content_id );
	}

	/**
	 * Maybe restrict content based on access control.
	 *
	 * @since 1.0.0
	 *
	 * @param string $content Post content.
	 * @return string Filtered content.
	 */
	public function maybe_restrict_content( $content ) {
		global $post;

		if ( ! $post ) {
			return $content;
		}

		$user_id = get_current_user_id();

		// Check course content.
		if ( SPLMS_POST_TYPES['course'] === $post->post_type ) {
			if ( ! $this->user_can_access_course( $user_id, $post->ID ) ) {
				return $this->get_access_denied_message( 'course', $post->ID );
			}
		}

		// Check lesson content.
		if ( SPLMS_POST_TYPES['lesson'] === $post->post_type ) {
			if ( ! $this->user_can_access_lesson( $user_id, $post->ID ) ) {
				return $this->get_access_denied_message( 'lesson', $post->ID );
			}
		}

		return $content;
	}

	/**
	 * Check enrollment access for courses.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $can_enroll Current enrollment permission.
	 * @param int  $user_id    User ID.
	 * @param int  $course_id  Course ID.
	 * @return bool True if user can enroll.
	 */
	public function check_enrollment_access( $can_enroll, $user_id, $course_id ) {
		// If already denied, don't override.
		if ( ! $can_enroll ) {
			return $can_enroll;
		}

		// Check capacity - prevent enrollment if course is full.
		$capacity_info = splms_get_course_max_enrollment_info( $course_id );
		if ( $capacity_info['has_limit'] && $capacity_info['is_full'] ) {
			return false;
		}

		// Check if user can access the course.
		return $this->user_can_access_course( $user_id, $course_id );
	}
}
