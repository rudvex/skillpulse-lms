<?php
/**
 * Dashboard API
 *
 * Handles AJAX and REST API calls for dashboard
 *
 * @since   1.0.0
 * @package SkillPulse_LMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Dashboard API Class
 *
 * Handles AJAX and REST API calls for dashboard functionality.
 *
 * @since   1.0.0
 * @package SkillPulse_LMS
 */
class SkillPulse_LMS_Dashboard_API {

	/**
	 * Class instance.
	 *
	 * @var SkillPulse_LMS_Dashboard_API|null $instance
	 */
	private static $instance = null;

	/**
	 * Get the instance of this class.
	 *
	 * @return SkillPulse_LMS_Dashboard_API
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
			self::$instance->setup_hooks();
		}

		return self::$instance;
	}

	/**
	 * Setup hooks.
	 */
	private function setup_hooks() {
		// AJAX handlers.
		add_action( 'wp_ajax_splms_update_profile', array( $this, 'ajax_update_profile' ) );
		add_action( 'wp_ajax_splms_change_password', array( $this, 'ajax_change_password' ) );
		add_action( 'wp_ajax_splms_save_notification_preferences', array( $this, 'ajax_save_notification_preferences' ) );
	}

	/**
	 * Get student stats
	 *
	 * @param int $user_id User ID.
	 *
	 * @since   1.0.0
	 * @return array
	 */
	public function get_student_stats( $user_id ) {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table query.
		$enrollments = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT course_id FROM {$wpdb->prefix}splms_enrollments WHERE user_id = %d",
				$user_id
			)
		);

		// Get completed courses from enrollment table (not user meta).
		$enrollments_query     = SkillPulse_LMS_Enrollments_Query::get_instance();
		$completed_enrollments = $enrollments_query->get_user_courses(
			$user_id,
			array(
				'status' => array( 'completed' ),
			)
		);
		$completed             = ! empty( $completed_enrollments ) ? array_map(
			function ( $e ) {
				return $e->course_id;
			},
			$completed_enrollments
		) : array();

		return array(
			'courses_enrolled'  => count( $enrollments ),
			'courses_completed' => count( $completed ),
			'ongoing_courses'   => max( 0, count( $enrollments ) - count( $completed ) ),
		);
	}

	/**
	 * AJAX: Update profile.
	 *
	 * @since   1.0.0
	 */
	public function ajax_update_profile() {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verification handled by wp_verify_nonce.
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'splms_nonce' ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Security check failed.', 'skillpulse-lms' ) ) );
		}

		if ( ! is_user_logged_in() ) {
			wp_send_json_error( array( 'message' => esc_html__( 'You must be logged in.', 'skillpulse-lms' ) ) );
		}

		$user_id = get_current_user_id();
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce already verified above.
		$data = wp_unslash( $_POST );

		// Update user data.
		$user_data = array( 'ID' => $user_id );

		if ( isset( $data['display_name'] ) ) {
			$user_data['display_name'] = sanitize_text_field( $data['display_name'] );
		}

		if ( isset( $data['email'] ) ) {
			$user_data['user_email'] = sanitize_email( $data['email'] );
		}

		if ( ! empty( $user_data ) ) {
			wp_update_user( $user_data );
		}

		// Update user meta.
		if ( isset( $data['first_name'] ) ) {
			update_user_meta( $user_id, 'first_name', sanitize_text_field( $data['first_name'] ) );
		}

		if ( isset( $data['last_name'] ) ) {
			update_user_meta( $user_id, 'last_name', sanitize_text_field( $data['last_name'] ) );
		}

		if ( isset( $data['bio'] ) ) {
			update_user_meta( $user_id, 'description', sanitize_textarea_field( $data['bio'] ) );
		}

		// Update billing address (stored as array).
		$billing_address = array();
		if ( isset( $data['billing_address_1'] ) ) {
			$billing_address['address_1'] = sanitize_text_field( $data['billing_address_1'] );
		}
		if ( isset( $data['billing_address_2'] ) ) {
			$billing_address['address_2'] = sanitize_text_field( $data['billing_address_2'] );
		}
		if ( isset( $data['billing_city'] ) ) {
			$billing_address['city'] = sanitize_text_field( $data['billing_city'] );
		}
		if ( isset( $data['billing_state'] ) ) {
			$billing_address['state'] = sanitize_text_field( $data['billing_state'] );
		}
		if ( isset( $data['billing_postcode'] ) ) {
			$billing_address['postcode'] = sanitize_text_field( $data['billing_postcode'] );
		}
		if ( isset( $data['billing_country'] ) ) {
			$billing_address['country'] = sanitize_text_field( $data['billing_country'] );
		}
		if ( ! empty( $billing_address ) ) {
			update_user_meta( $user_id, 'billing_address', $billing_address );
		}

		// Update billing phone.
		if ( isset( $data['billing_phone'] ) ) {
			update_user_meta( $user_id, 'billing_phone', sanitize_text_field( $data['billing_phone'] ) );
		}

		// Update billing company.
		if ( isset( $data['billing_company'] ) ) {
			update_user_meta( $user_id, 'billing_company', sanitize_text_field( $data['billing_company'] ) );
		}

		wp_send_json_success( array( 'message' => esc_html__( 'Profile updated successfully.', 'skillpulse-lms' ) ) );
	}

	/**
	 * AJAX: Change password.
	 *
	 * @since   1.0.0
	 */
	public function ajax_change_password() {
		// Verify nonce (use same nonce as profile update).
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verification handled by wp_verify_nonce.
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'splms_nonce' ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Security check failed.', 'skillpulse-lms' ) ) );
		}

		if ( ! is_user_logged_in() ) {
			wp_send_json_error( array( 'message' => esc_html__( 'You must be logged in.', 'skillpulse-lms' ) ) );
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce already verified above.
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Passwords should not be sanitized.
		$current_password = isset( $_POST['current_password'] ) ? wp_unslash( $_POST['current_password'] ) : '';
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Passwords should not be sanitized.
		$new_password = isset( $_POST['new_password'] ) ? wp_unslash( $_POST['new_password'] ) : '';

		if ( empty( $current_password ) || empty( $new_password ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Missing required fields.', 'skillpulse-lms' ) ) );
		}

		// Validate password strength: minimum 8 characters, uppercase, lowercase, number, special character.
		$password_errors = array();

		if ( strlen( $new_password ) < 8 ) {
			$password_errors[] = __( 'at least 8 characters long', 'skillpulse-lms' );
		}

		if ( ! preg_match( '/[a-z]/', $new_password ) ) {
			$password_errors[] = __( 'one lowercase letter', 'skillpulse-lms' );
		}

		if ( ! preg_match( '/[A-Z]/', $new_password ) ) {
			$password_errors[] = __( 'one uppercase letter', 'skillpulse-lms' );
		}

		if ( ! preg_match( '/[0-9]/', $new_password ) ) {
			$password_errors[] = __( 'one number', 'skillpulse-lms' );
		}

		if ( ! preg_match( '/[^a-zA-Z0-9]/', $new_password ) ) {
			$password_errors[] = __( 'one special character', 'skillpulse-lms' );
		}

		if ( ! empty( $password_errors ) ) {
			/* translators: %s: List of password requirements */
			$error_message = esc_html__( 'Password must contain:', 'skillpulse-lms' ) . ' ' . esc_html( implode( ', ', $password_errors ) ) . '.';
			wp_send_json_error( array( 'message' => $error_message ) );
		}

		$user_id = get_current_user_id();
		$user    = get_user_by( 'ID', $user_id );

		if ( ! $user ) {
			wp_send_json_error( array( 'message' => esc_html__( 'User not found.', 'skillpulse-lms' ) ) );
		}

		// Verify current password.
		if ( ! wp_check_password( $current_password, $user->user_pass, $user_id ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Current password is incorrect.', 'skillpulse-lms' ) ) );
		}

		// Update password.
		wp_set_password( $new_password, $user_id );

		wp_send_json_success( array( 'message' => esc_html__( 'Password changed successfully.', 'skillpulse-lms' ) ) );
	}


	/**
	 * Get student courses
	 *
	 * @param int    $user_id       User ID.
	 * @param string $status_filter Optional. Filter by enrollment status ('active', 'completed', 'cancelled', 'all'). Default 'all'.
	 *
	 * @since   1.0.0
	 * @return array
	 */
	public function get_student_courses( $user_id, $status_filter = 'all' ) {
		// Use enrollment query class to get enrollments with status filter.
		$enrollments_query = SkillPulse_LMS_Enrollments_Query::get_instance();

		// Build status filter array.
		$status_args = array();
		if ( 'all' !== $status_filter ) {
			// Map 'cancelled' to include both 'cancelled' and 'suspended' statuses.
			if ( 'cancelled' === $status_filter ) {
				$status_args['status'] = array( 'cancelled', 'suspended' );
			} else {
				// Validate status filter.
				$valid_statuses = array( 'active', 'completed', 'cancelled', 'suspended' );
				if ( in_array( $status_filter, $valid_statuses, true ) ) {
					$status_args['status'] = array( $status_filter );
				}
				// If invalid status, default to 'all' behavior (no filter).
			}
		}
		// If 'all' or invalid status, $status_args remains empty, which means no status filter.

		// Get enrollments with optional status filter.
		$enrollments = $enrollments_query->get_user_courses( $user_id, $status_args );

		if ( empty( $enrollments ) ) {
			return array();
		}

		// Build course_id => enrollment mapping to include enrollment status.
		$enrollment_map = array();
		foreach ( $enrollments as $enrollment ) {
			$enrollment_map[ $enrollment->course_id ] = $enrollment;
		}

		$course_ids = array_keys( $enrollment_map );

		$args = array(
			'post_type'      => SPLMS_POST_TYPES['course'],
			'post__in'       => $course_ids,
			'posts_per_page' => - 1,
			'post_status'    => 'publish',
		);

		$query = new WP_Query( $args );

		$courses = array();
		foreach ( $query->posts as $course ) {
			$thumbnail_id = get_post_thumbnail_id( $course->ID );
			$thumbnail    = $thumbnail_id ? wp_get_attachment_image_url( $thumbnail_id, 'medium' ) : '';

			$excerpt = ! empty( $course->post_excerpt ) ? $course->post_excerpt : $course->post_content;

			// Get enrollment data for this course.
			$enrollment        = isset( $enrollment_map[ $course->ID ] ) ? $enrollment_map[ $course->ID ] : null;
			$enrollment_status = $enrollment ? $enrollment->status : 'active';
			$enrolled_at       = $enrollment ? $enrollment->enrolled_at : '';

			$courses[] = array(
				'id'                => $course->ID,
				'title'             => $course->post_title,
				'excerpt'           => wp_trim_words( $excerpt, 20 ),
				'thumbnail'         => $thumbnail,
				'link'              => get_permalink( $course->ID ),
				'progress'          => $this->get_course_progress( $course->ID, $user_id ),
				'completed'         => SkillPulse_LMS_Enrollment::get_instance()->has_user_completed_course( $user_id, $course->ID ),
				'enrollment_status' => $enrollment_status,
				'enrolled_at'       => $enrolled_at,
			);
		}

		return $courses;
	}

	/**
	 * Get course progress for student
	 *
	 * @param int $course_id Course ID.
	 * @param int $user_id   User ID.
	 *
	 * @since   1.0.0
	 * @return array
	 */
	public function get_course_progress( $course_id, $user_id ) {
		global $wpdb;

		// Get progress from the lesson_progress table.
		$lesson_progress_table = esc_sql( $wpdb->prefix . 'splms_lesson_progress' );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table query.
		$completed_lessons     = $wpdb->get_var(
			$wpdb->prepare(
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table name cannot be prepared.
				"SELECT COUNT(DISTINCT lesson_id) FROM {$lesson_progress_table}
			WHERE user_id = %d AND course_id = %d AND is_completed = 1",
				$user_id,
				$course_id
			)
		);

		// Get passed quizzes from quiz_attempts table (only graded quizzes).
		$quiz_attempts_table = esc_sql( $wpdb->prefix . 'splms_quiz_attempts' );
		// Get all passed quiz IDs first.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table query.
		$passed_quiz_ids = $wpdb->get_col(
			$wpdb->prepare(
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table name cannot be prepared.
				"SELECT DISTINCT quiz_id FROM {$quiz_attempts_table}
			WHERE user_id = %d AND course_id = %d AND passed = 1",
				$user_id,
				$course_id
			)
		);

		// Filter to only graded quizzes (exclude practice and survey).
		$graded_quiz_ids = array();
		foreach ( $passed_quiz_ids as $quiz_id ) {
			$quiz_type = splms_get_quiz_type( $quiz_id );
			if ( 'graded' === $quiz_type ) {
				$graded_quiz_ids[] = $quiz_id;
			}
		}
		$passed_quizzes = count( $graded_quiz_ids );

		// Get total items (lessons + quizzes) for this course using unified method.
		$curriculum_result = splms_get_course_curriculum( $course_id );
		$total_items       = 0;

		// Count lessons and graded quizzes only.
		if ( isset( $curriculum_result['sections'] ) ) {
			foreach ( $curriculum_result['sections'] as $section ) {
				if ( isset( $section['children'] ) ) {
					foreach ( $section['children'] as $child ) {
						// Count all lessons.
						if ( SPLMS_POST_TYPES['lesson'] === $child['type'] ) {
							++$total_items;
						}
						// Only count graded quizzes (exclude practice and survey).
						if ( SPLMS_POST_TYPES['quiz'] === $child['type'] ) {
							$quiz_type = splms_get_quiz_type( $child['id'] );
							if ( 'graded' === $quiz_type ) {
								++$total_items;
							}
						}
					}
				}
			}
		}

		$total_completed = intval( $completed_lessons ) + intval( $passed_quizzes );
		$percentage      = $total_items > 0 ? round( ( $total_completed / $total_items ) * 100, 1 ) : 0;

		return array(
			'total_items'       => $total_items,
			'completed_lessons' => intval( $completed_lessons ),
			'passed_quizzes'    => intval( $passed_quizzes ),
			'percentage'        => $percentage,
		);
	}

	/**
	 * AJAX: Save notification preferences.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function ajax_save_notification_preferences() {
		// Verify nonce.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verification handled by wp_verify_nonce.
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'splms_nonce' ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Security check failed.', 'skillpulse-lms' ) ) );
		}

		if ( ! is_user_logged_in() ) {
			wp_send_json_error( array( 'message' => esc_html__( 'You must be logged in.', 'skillpulse-lms' ) ) );
		}

		$user_id = get_current_user_id();

		// Get preferences from POST data.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce already verified above.
		$preferences = array();

		// Global settings.
		$preferences['enabled']        = isset( $_POST['enabled'] ) && '1' === $_POST['enabled'];
		$preferences['email_enabled']  = isset( $_POST['email_enabled'] ) && '1' === $_POST['email_enabled'];
		$preferences['in_app_enabled'] = isset( $_POST['in_app_enabled'] ) && '1' === $_POST['in_app_enabled'];

		// Event-specific preferences.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce already verified above.
		if ( isset( $_POST['events'] ) && is_array( $_POST['events'] ) ) {
			$events = wp_unslash( $_POST['events'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Will sanitize in loop.
			foreach ( $events as $event_key => $event_prefs ) {
				$event_key                 = sanitize_key( $event_key );
				$preferences[ $event_key ] = array(
					'email'  => isset( $event_prefs['email'] ) && '1' === $event_prefs['email'],
					'in_app' => isset( $event_prefs['in_app'] ) && '1' === $event_prefs['in_app'],
				);
			}
		}

		// Save preferences.
		$prefs_instance = SkillPulse_LMS_Notification_Preferences::get_instance();
		$saved          = $prefs_instance->save_user_preferences( $user_id, $preferences );

		if ( $saved ) {
			wp_send_json_success( array( 'message' => esc_html__( 'Notification preferences saved successfully.', 'skillpulse-lms' ) ) );
		} else {
			wp_send_json_error( array( 'message' => esc_html__( 'Failed to save notification preferences.', 'skillpulse-lms' ) ) );
		}
	}
}
