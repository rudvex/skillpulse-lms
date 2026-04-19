<?php
/**
 * Lessons Module
 *
 * Handles lesson-related functionality including settings, progress, and access control
 *
 * @since      1.0.0
 * @subpackage Lessons
 * @package    SkillPulse_LMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Lessons Class
 *
 * @since 1.0.0
 */
class SkillPulse_LMS_Lessons {

	/**
	 * Class instance.
	 *
	 * @var SkillPulse_LMS_Lessons|null $instance
	 */
	private static $instance;

	/**
	 * Get the instance of this class.
	 *
	 * @since 1.0.0
	 *
	 * @return SkillPulse_LMS_Lessons Class instance.
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	private function __construct() {
		$this->setup_globals();
		$this->load_classes();
		$this->setup_actions();
		$this->setup_filters();
	}

	/**
	 * Setup Globals.
	 *
	 * @since 1.0.0
	 */
	protected function setup_globals() {
		// Load lesson-specific query classes and general functions.
		$files = array(
			'includes/modules/lessons/class-lesson-progress-query',
			'includes/modules/lessons/general-functions',
		);

		foreach ( $files as $file ) {
			if ( file_exists( SKILLPULSE_LMS_DIR_PATH . $file . '.php' ) ) {
				require_once SKILLPULSE_LMS_DIR_PATH . $file . '.php';
			}
		}
	}

	/**
	 * Initiate the required classes.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	protected function load_classes() {
	}

	/**
	 * Action hooks.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	protected function setup_actions() {
		add_action( 'save_post', array( $this, 'save_lesson_data' ), 10, 3 );
		// Update AJAX action to match JavaScript.
		add_action( 'wp_ajax_splms_mark_lesson_complete', array( $this, 'mark_lesson_complete' ) );
		add_action( 'wp_ajax_nopriv_splms_mark_lesson_complete', array( $this, 'mark_lesson_complete' ) );
	}

	/**
	 * Define all filter hooks.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	protected function setup_filters() {
	}

	/**
	 * Save lesson data.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 * @param bool    $update  Whether this is an existing post being updated.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function save_lesson_data(
		$post_id,
		$post,
		$update
	) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- Parameter kept for hook compatibility.
		if ( SPLMS_POST_TYPES['lesson'] !== $post->post_type ) {
			return;
		}

		// Avoid infinite loop.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Check user permissions.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
	}

	/**
	 * Get lesson settings (configuration-driven).
	 * Handles both grouped and non-grouped fields from JSON config.
	 *
	 * @param int $lesson_id Lesson ID.
	 *
	 * @since 1.0.0
	 *
	 * @return array Lesson settings array.
	 */
	public function get_lesson_settings( $lesson_id ) {
		$all_meta = get_post_meta( $lesson_id );

		// Get configuration data and extract defaults.
		$config_data       = SkillPulse_LMS_Config_Loader::get_config( 'lessons', 'admin' );
		$defaults_settings = $this->extract_defaults_from_config( $config_data );
		$lesson_settings   = array();

		// Build field definitions for proper group detection.
		$field_definitions = array();
		if ( isset( $config_data['sections'] ) && is_array( $config_data['sections'] ) ) {
			foreach ( $config_data['sections'] as $section ) {
				if ( isset( $section['fields'] ) && is_array( $section['fields'] ) ) {
					foreach ( $section['fields'] as $field ) {
						if ( isset( $field['id'] ) ) {
							$field_definitions[ $field['id'] ] = $field;
						}
					}
				}
			}
		}

		// Process defaults: handle grouped fields.
		foreach ( $defaults_settings as $key => $default_value ) {
			// Check if this is a group (like lesson_completion_settings, lesson_drip_settings).
			if ( is_array( $default_value ) && false === isset( $field_definitions[ $key ] ) ) {
				// This is a group - process each field in the group.
				$group_name                     = $key;
				$lesson_settings[ $group_name ] = array();

				$meta_key   = "_splms_{$group_name}";
				$meta_value = isset( $all_meta[ $meta_key ][0] ) ? maybe_unserialize( $all_meta[ $meta_key ][0] ) : null;

				// Ensure meta_value is an array if it's not null.
				if ( ! is_array( $meta_value ) && null !== $meta_value ) {
					$meta_value = null;
				}

				foreach ( $default_value as $field_id => $field_default ) {
					// Handle different data types properly.
					if ( is_array( $field_default ) ) {
						$lesson_settings[ $group_name ][ $field_id ] = wp_parse_args(
							(array) ( isset( $meta_value[ $field_id ] ) ? $meta_value[ $field_id ] : array() ),
							$field_default
						);
					} else {
						$lesson_settings[ $group_name ][ $field_id ] = ( isset( $meta_value[ $field_id ] ) && null !== $meta_value[ $field_id ] ) ? $meta_value[ $field_id ] : $field_default;
					}
				}
			} else {
				// This is a regular (non-grouped) field.
				$meta_key   = "_splms_{$key}";
				$meta_value = isset( $all_meta[ $meta_key ][0] ) ? maybe_unserialize( $all_meta[ $meta_key ][0] ) : null;

				// Handle different data types properly.
				if ( is_array( $default_value ) ) {
					// Special handling for repeater fields (like lesson_attachments) that are indexed arrays.
					if ( 'lesson_attachments' === $key ) {
						// For lesson_attachments, don't use wp_parse_args as it breaks indexed arrays.
						// Use meta value if it's an array, otherwise use default.
						$lesson_settings[ $key ] = is_array( $meta_value ) ? $meta_value : $default_value;
					} else {
						// For array defaults (like repeater, multi-select), use wp_parse_args.
						$lesson_settings[ $key ] = wp_parse_args( (array) $meta_value, $default_value );
					}
				} else {
					// For scalar defaults, use the meta value or fall back to default.
					$lesson_settings[ $key ] = null !== $meta_value ? $meta_value : $default_value;
				}
			}
		}

		return $lesson_settings;
	}


	/**
	 * Update lesson settings.
	 *
	 * @param int   $lesson_id    Lesson ID.
	 * @param array $new_settings Settings array.
	 *
	 * @since 1.0.0
	 *
	 * @return array|WP_Error Updated settings array on success, WP_Error on failure.
	 */
	public function update_lesson_settings( $lesson_id, $new_settings = array() ) {
		if ( empty( $lesson_id ) || ! is_array( $new_settings ) ) {
			return new WP_Error( 'splms_invalid_lesson_settings', __( 'Invalid lesson ID or settings.', 'skillpulse-lms' ), array( 'status' => 400 ) );
		}

		// Get configuration to determine field groups.
		$config_data       = SkillPulse_LMS_Config_Loader::get_config( 'lessons', 'admin' );
		$defaults_settings = $this->extract_defaults_from_config( $config_data );

		// Validate that only known settings keys are being updated.
		foreach ( $new_settings as $key => $value ) {
			if ( ! array_key_exists( $key, $defaults_settings ) ) {
				return new WP_Error(
					'splms_invalid_setting_key',
					/* translators: %s: Invalid setting key name. */
					sprintf( __( 'Invalid setting key: %s', 'skillpulse-lms' ), $key ),
					array( 'status' => 400 )
				);
			}
		}

		foreach ( $new_settings as $key => $value ) {
			// Check if this is a grouped field.
			if ( isset( $defaults_settings[ $key ] ) && is_array( $defaults_settings[ $key ] ) ) {
				// Grouped field - save as single meta key with _splms_ prefix.
				$meta_key = "_splms_{$key}";
				update_post_meta( $lesson_id, $meta_key, $value );
			} else {
				// Individual field - save with _splms_ prefix.
				$meta_key = "_splms_{$key}";
				update_post_meta( $lesson_id, $meta_key, $value );
			}
		}

		return $this->get_lesson_settings( $lesson_id );
	}

	/**
	 * Get lesson duration.
	 *
	 * @param int $lesson_id Lesson ID.
	 *
	 * @since 1.0.0
	 *
	 * @return string Lesson duration.
	 */
	public function get_lesson_duration( $lesson_id ) {
		$settings = $this->get_lesson_settings( $lesson_id );

		return $settings['lesson_duration'];
	}

	/**
	 * Get lesson attachments.
	 *
	 * @param int $lesson_id Lesson ID.
	 *
	 * @since 1.0.0
	 *
	 * @return array Lesson attachments array.
	 */
	public function get_lesson_attachments( $lesson_id ) {
		$settings = $this->get_lesson_settings( $lesson_id );

		return $settings['lesson_attachments'];
	}

	/**
	 * Get lesson drip settings.
	 *
	 * @param int $lesson_id Lesson ID.
	 *
	 * @since 1.0.0
	 *
	 * @return array Lesson drip settings array.
	 */
	public function get_lesson_drip_settings( $lesson_id ) {
		$settings = $this->get_lesson_settings( $lesson_id );

		return $settings['lesson_drip_settings'];
	}

	/**
	 * Get lesson completion settings.
	 *
	 * @param int $lesson_id Lesson ID.
	 *
	 * @since 1.0.0
	 *
	 * @return array Lesson completion settings array.
	 */
	public function get_lesson_completion_settings( $lesson_id ) {
		$settings = $this->get_lesson_settings( $lesson_id );

		return $settings['lesson_completion_settings'];
	}

	/**
	 * Get lesson prerequisites.
	 *
	 * @param int $lesson_id Lesson ID.
	 *
	 * @since 1.0.0
	 *
	 * @return array Lesson prerequisites array.
	 */
	public function get_lesson_prerequisites( $lesson_id ) {
		$settings = $this->get_lesson_settings( $lesson_id );

		return $settings['lesson_prerequisites'];
	}

	/**
	 * Check if lesson has drip access restrictions.
	 *
	 * @param int $lesson_id Lesson ID.
	 * @param int $user_id   User ID.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if lesson is available, false otherwise.
	 */
	public function is_lesson_drip_available( $lesson_id, $user_id ) {
		// If user is not logged in, defer to main access control.
		if ( ! $user_id ) {
			return true; // Let main access control handle guest access.
		}

		$drip_settings = $this->get_lesson_drip_settings( $lesson_id );

		// If drip is not enabled, lesson is available.
		if ( empty( $drip_settings['enable_drip'] ) || ! $drip_settings['enable_drip'] ) {
			return true;
		}

		$course_id = $this->get_lesson_course( $lesson_id );
		if ( ! $course_id ) {
			return true;
		}

		// Get user enrollment date.
		$enrollment_date = $this->get_user_enrollment_date( $user_id, $course_id );
		if ( ! $enrollment_date ) {
			// If not enrolled, don't block - let enrollment check handle it.
			return true;
		}

		$drip_type = isset( $drip_settings['drip_type'] ) ? $drip_settings['drip_type'] : 'days_after_enrollment';
		$drip_days = isset( $drip_settings['drip_days'] ) ? $drip_settings['drip_days'] : 0;

		switch ( $drip_type ) {
			case 'days_after_enrollment':
				// Handle 0 days (immediate unlock).
				if ( $drip_days <= 0 ) {
					return true;
				}

				$available_timestamp = strtotime( $enrollment_date . ' + ' . intval( $drip_days ) . ' days' );
				// phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested -- Need site timezone for drip content comparison.
				$current_timestamp = current_time( 'timestamp' );

				return $current_timestamp >= $available_timestamp;

			case 'days_after_previous':
				// Normalize negative days to 0 (immediate unlock after previous completion).
				if ( $drip_days < 0 ) {
					$drip_days = 0;
				}

				// Get previous lesson in course.
				$previous_lesson = $this->get_previous_lesson( $lesson_id, $course_id );
				if ( ! $previous_lesson ) {
					return true; // If no previous lesson, make available.
				}

				// Check if previous lesson is completed.
				if ( ! $this->is_lesson_completed( $previous_lesson, $user_id ) ) {
					return false;
				}

				// Get completion date of previous lesson.
				$previous_completion = $this->get_lesson_completion_date( $previous_lesson, $user_id );
				if ( ! $previous_completion ) {
					return false;
				}

				// Calculate available date.
				$available_timestamp = strtotime( $previous_completion . ' + ' . intval( $drip_days ) . ' days' );
				// phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested -- Need site timezone for drip content comparison.
				$current_timestamp = current_time( 'timestamp' );

				return $current_timestamp >= $available_timestamp;

			case 'specific_date':
				$specific_date = isset( $drip_settings['specific_date'] ) ? $drip_settings['specific_date'] : '';
				if ( empty( $specific_date ) ) {
					// Fall back to WordPress publish date.
					$lesson_post = get_post( $lesson_id );
					if ( $lesson_post && 'future' === $lesson_post->post_status ) {
						$specific_date = $lesson_post->post_date;
					} else {
						return true; // If no specific date and not scheduled, make available.
					}
				}

				// Ensure date format is correct (should be Y-m-d H:i:s or Y-m-d).
				$specific_timestamp = strtotime( $specific_date );
				if ( ! $specific_timestamp ) {
					return true; // Invalid date, make available.
				}

				// Add time if not present (default to start of day).
				if ( strlen( $specific_date ) === 10 ) {
					$specific_date .= ' 00:00:00';
				}

				// phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested -- Need site timezone for drip content comparison.
				$current_timestamp  = current_time( 'timestamp' );
				$specific_timestamp = strtotime( $specific_date );

				return $current_timestamp >= $specific_timestamp;

			default:
				return true;
		}
	}

	/**
	 * Check if lesson prerequisites are met.
	 *
	 * @param int $lesson_id Lesson ID.
	 * @param int $user_id   User ID.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if prerequisites are met, false otherwise.
	 */
	public function are_prerequisites_met( $lesson_id, $user_id ) {
		$prerequisites = $this->get_lesson_prerequisites( $lesson_id );

		if ( empty( $prerequisites ) || ! is_array( $prerequisites ) ) {
			return true;
		}

		foreach ( $prerequisites as $prereq_id ) {
			if ( ! $this->is_lesson_completed( $prereq_id, $user_id ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Check if user can skip lesson based on prevent_skip setting.
	 *
	 * @param int $lesson_id Lesson ID.
	 * @param int $user_id   User ID.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if user can skip, false otherwise.
	 */
	public function can_skip_lesson( $lesson_id, $user_id ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- Parameter kept for future use.
		$completion_settings = $this->get_lesson_completion_settings( $lesson_id );
		$prevent_skip        = isset( $completion_settings['prevent_skip'] ) ? $completion_settings['prevent_skip'] : false;

		return ! $prevent_skip;
	}

	/**
	 * Get formatted lesson attachments (repeater format with file_url + file_label).
	 *
	 * @param int $lesson_id Lesson ID.
	 *
	 * @since 1.0.0
	 *
	 * @return array Formatted attachments array.
	 */
	public function get_formatted_lesson_attachments( $lesson_id ) {
		$attachments_data      = $this->get_lesson_attachments( $lesson_id );
		$formatted_attachments = array();

		if ( empty( $attachments_data ) ) {
			return $formatted_attachments;
		}

		// Handle repeater format (array of objects with file_url and file_label).
		if ( ! is_array( $attachments_data ) || empty( $attachments_data ) ) {
			return $formatted_attachments;
		}

		foreach ( $attachments_data as $attachment ) {
			if ( ! is_array( $attachment ) || ! isset( $attachment['file_url'] ) || empty( $attachment['file_url'] ) ) {
				continue;
			}

			$file_url   = esc_url_raw( $attachment['file_url'] );
			$file_label = ! empty( $attachment['file_label'] ) ? sanitize_text_field( $attachment['file_label'] ) : '';

			// Try to get attachment ID from URL if it's a WordPress media URL.
			$attachment_id = attachment_url_to_postid( $file_url );

			$formatted_attachments[] = array(
				'id'    => $attachment_id ? $attachment_id : 0,
				'title' => $file_label ? $file_label : basename( $file_url ),
				'url'   => $file_url,
				'type'  => $attachment_id ? get_post_mime_type( $attachment_id ) : wp_check_filetype( $file_url )['type'],
				'label' => $file_label,
			);
		}

		return $formatted_attachments;
	}

	/**
	 * Check if user has completed lesson (database-based).
	 *
	 * @param int $lesson_id Lesson ID.
	 * @param int $user_id   User ID.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if completed, false otherwise.
	 */
	public function is_lesson_completed( $lesson_id, $user_id ) {
		$result = SkillPulse_LMS_Lesson_Progress_Query::get_instance()->is_lesson_completed( $lesson_id, $user_id );

		return (bool) $result;
	}

	/**
	 * Mark lesson as complete (database-based).
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function mark_lesson_complete() {
		// Verify nonce.
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce(
			sanitize_text_field( wp_unslash( $_POST['nonce'] ) ),
			'splms_nonce'
		) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Nonce verification.
			wp_die( 'Security check failed' );
		}

		$lesson_id = isset( $_POST['lesson_id'] ) ? intval( $_POST['lesson_id'] ) : 0;
		$course_id = isset( $_POST['course_id'] ) ? intval( $_POST['course_id'] ) : 0;
		$user_id   = get_current_user_id();

		if ( ! $user_id ) {
			wp_send_json_error( 'User not logged in' );
		}

		if ( ! $lesson_id || ! $course_id ) {
			wp_send_json_error( 'Invalid lesson or course ID' );
		}

		// Check if user has access to this lesson.
		if ( ! $this->user_can_access_lesson( $lesson_id, $user_id ) ) {
			wp_send_json_error( 'Access denied' );
		}

		// Validate video completion requirement for video lessons.
		$lesson_settings = splms_get_lesson_settings( $lesson_id );
		$lesson_type     = isset( $lesson_settings['lesson_type'] ) ? $lesson_settings['lesson_type'] : 'text';

		if ( 'video' === $lesson_type ) {
			$completion_required = isset( $lesson_settings['lesson_completion_required'] ) ? intval( $lesson_settings['lesson_completion_required'] ) : 100;
			$video_progress      = isset( $_POST['video_progress'] ) ? floatval( $_POST['video_progress'] ) : 0.0;

			// Server-side validation: Check if watched percentage meets requirement.
			if ( $video_progress < $completion_required ) {
				wp_send_json_error(
					sprintf(
					/* translators: %1$d: Required completion percentage, %2$.1f: Current progress percentage. */
						__( 'You must watch at least %1$d%% of the video to complete this lesson. Current progress: %2$.1f%%', 'skillpulse-lms' ),
						$completion_required,
						$video_progress
					)
				);
			}
		}

		// Mark lesson complete in database table.
		splms_mark_lesson_complete( $lesson_id, $user_id, $course_id );

		do_action( 'splms_lesson_completed', $lesson_id, $user_id );

		// Calculate updated course progress.
		$progress_data = $this->calculate_course_progress( $user_id, $course_id );

		// Sync progress to enrollment database table.
		$enrollment = SkillPulse_LMS_Enrollment::get_instance();
		$enrollment->update_enrollment_progress( $user_id, $course_id, $progress_data['percentage'] );

		// Log activity.
		SkillPulse_LMS_User_Activity_Query::get_instance()->log_activity( $user_id, 'lesson_completed', $course_id, $lesson_id, 'lesson' );

		// Prepare response data.
		$response_data = array(
			'message'  => 'Lesson marked as complete!',
			'progress' => $progress_data,
		);

		// Check if course is completed and include certificate data.
		if ( isset( $progress_data['percentage'] ) && $progress_data['percentage'] >= 100 ) {
			$certificates_instance = SkillPulse_LMS_Certificates::get_instance();

			// Small delay to ensure certificate generation hooks have completed.
			// The certificate is generated via 'splms_course_completed' action.
			usleep( 100000 ); // 0.1 second delay.

			// Check if user has certificate for this course.
			$has_certificate = $certificates_instance->user_has_certificate( $user_id, $course_id );

			if ( $has_certificate ) {
				$certificate_link = $certificates_instance->get_certificate_link( $user_id, $course_id );

				if ( $certificate_link ) {
					$response_data['certificate_generated'] = true;
					$response_data['certificate_id']        = $course_id; // Using course ID as identifier.
					$response_data['certificate_url']       = $certificate_link;
				} else {
					// Certificate exists but link generation failed.
					$response_data['certificate_generated'] = true;
					$response_data['certificate_id']        = $course_id;
					$response_data['certificate_url']       = false;
				}
			}
		}

		wp_send_json_success( $response_data );
	}

	/**
	 * Calculate course progress using database tables only.
	 *
	 * @param int $user_id   User ID.
	 * @param int $course_id Course ID.
	 *
	 * @since 1.0.0
	 *
	 * @return array Progress data array.
	 */
	public function calculate_course_progress( $user_id, $course_id ) {
		global $wpdb;

		// Get completed lessons from lesson_progress table.
		$lesson_progress_table = esc_sql( $wpdb->prefix . 'splms_lesson_progress' );
		$completed_lessons     = $wpdb->get_var(
			$wpdb->prepare(
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table name cannot be prepared, values are prepared.
				"SELECT COUNT(*) FROM {$lesson_progress_table} WHERE user_id = %d AND course_id = %d AND is_completed = 1",
				$user_id,
				$course_id
			)
		);

		if ( ! empty( $wpdb->last_error ) ) {
			$completed_lessons = 0;
		}

		// Get passed quizzes from quiz_attempts table (only graded quizzes).
		$quiz_attempts_table = esc_sql( $wpdb->prefix . 'splms_quiz_attempts' );
		// Get all passed quiz IDs first.
		$passed_quiz_ids = $wpdb->get_col(
			$wpdb->prepare(
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table name cannot be prepared, values are prepared.
				"SELECT DISTINCT quiz_id FROM {$quiz_attempts_table} WHERE user_id = %d AND course_id = %d AND passed = 1",
				$user_id,
				$course_id
			)
		);

		if ( ! empty( $wpdb->last_error ) ) {
			$passed_quiz_ids = array();
		}

		// Filter to only graded quizzes (exclude practice and survey).
		$graded_quiz_ids = array();
		foreach ( $passed_quiz_ids as $quiz_id ) {
			$quiz_type = splms_get_quiz_type( $quiz_id );
			if ( 'graded' === $quiz_type ) {
				$graded_quiz_ids[] = $quiz_id;
			}
		}
		$passed_quizzes = count( $graded_quiz_ids );

		// Get total items for this course.
		$total_items     = $this->get_course_total_items( $course_id );
		$total_completed = intval( $completed_lessons ) + intval( $passed_quizzes );

		$percentage = $total_items > 0 ? round( ( $total_completed / $total_items ) * 100, 1 ) : 0;

		return array(
			'percentage'        => $percentage,
			'completed_lessons' => intval( $completed_lessons ),
			'passed_quizzes'    => intval( $passed_quizzes ),
			'total_items'       => $total_items,
			'last_accessed'     => current_time( 'mysql' ),
		);
	}

	/**
	 * Get total course items (lessons + quizzes).
	 *
	 * @param int $course_id Course ID.
	 *
	 * @since 1.0.0
	 *
	 * @return int Total number of course items.
	 */
	private function get_course_total_items( $course_id ) {
		// Use direct database queries to avoid recursive calls to get_course_curriculum.
		// This prevents infinite loops when called during access control checks.
		$course_items_query  = SkillPulse_LMS_Course_Items_Query::get_instance();
		$relationships_query = SkillPulse_LMS_Relationships_Query::get_instance();

		// Get all sections for this course.
		$course_items = $course_items_query->get_items( $course_id );
		$item_count   = 0;

		foreach ( $course_items as $course_item ) {
			// Skip if not a section.
			if ( SPLMS_POST_TYPES['section'] !== $course_item->item_type ) {
				continue;
			}

			$section_id = $course_item->item_id;

			// Skip if section post doesn't exist.
			if ( ! get_post( $section_id ) ) {
				continue;
			}

			// Get section children (lessons and quizzes).
			$children = $relationships_query->get_children( $section_id );

			foreach ( $children as $child ) {
				$child_type = $child->child_type;

				// Count lessons and graded quizzes only.
				if ( SPLMS_POST_TYPES['lesson'] === $child_type || SPLMS_POST_TYPES['quiz'] === $child_type ) {
					// Skip if post doesn't exist.
					if ( ! get_post( $child->child_id ) ) {
						continue;
					}
					++$item_count;
				}
			}
		}

		return $item_count;
	}

	/**
	 * Check if user can access lesson.
	 * Includes prerequisites, drip, access expiration, and enrollment checks.
	 * Note: This is for enrolled users. For general access control, use SkillPulse_LMS_Access_Control.
	 *
	 * @param int $lesson_id Lesson ID.
	 * @param int $user_id   User ID.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if user can access, false otherwise.
	 */
	public function user_can_access_lesson( $lesson_id, $user_id ) {
		// Allow admins and editors to always access.
		if ( $user_id && current_user_can( 'edit_post', $lesson_id ) ) {
			return true;
		}

		// If user is not logged in, defer to main access control system.
		if ( ! $user_id ) {
			$access_control = SkillPulse_LMS_Access_Control::get_instance();

			return $access_control->user_can_access_lesson( 0, $lesson_id );
		}

		// Check prerequisites.
		$prerequisites = $this->get_lesson_prerequisites( $lesson_id );

		if ( ! empty( $prerequisites ) && is_array( $prerequisites ) ) {
			foreach ( $prerequisites as $prereq_id ) {
				if ( ! $this->is_lesson_completed( $prereq_id, $user_id ) ) {
					return false;
				}
			}
		}

		// Check prevent_skip from previous lessons using efficient database query.
		$course_id = $this->get_lesson_course( $lesson_id );
		if ( $course_id && ! $this->check_prevent_skip_access( $lesson_id, $user_id, $course_id ) ) {
			return false;
		}

		// Check course enrollment (reuse $course_id from above).
		if ( $course_id && ! $this->is_user_enrolled( $course_id, $user_id ) ) {
			// If not enrolled, check if guest preview is available.
			$access_control = SkillPulse_LMS_Access_Control::get_instance();

			return $access_control->user_can_access_lesson( $user_id, $lesson_id );
		}

		// Check drip availability.
		if ( ! $this->is_lesson_drip_available( $lesson_id, $user_id ) ) {
			return false;
		}

		// Check access expiration.
		if ( ! $this->is_lesson_access_valid( $lesson_id, $user_id ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Check if lesson access is still valid (not expired).
	 *
	 * @param int $lesson_id Lesson ID.
	 * @param int $user_id   User ID.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if access is valid, false otherwise.
	 */
	public function is_lesson_access_valid( $lesson_id, $user_id ) {
		$expiration_info = $this->get_lesson_access_expiration_info( $lesson_id, $user_id );

		return $expiration_info['is_valid'];
	}

	/**
	 * Get lesson access expiration information.
	 *
	 * @param int $lesson_id         Lesson ID.
	 * @param int $user_id           User ID.
	 *
	 * @since 1.0.0
	 *
	 * @return array {
	 * @type bool   $is_valid          Whether access is still valid.
	 * @type string $expiration_date   Expiration date in MySQL format.
	 * @type int    $days_remaining    Days remaining until expiration (0 if expired).
	 * @type int    $access_expiration Original expiration days setting.
	 *                                 }
	 */
	public function get_lesson_access_expiration_info( $lesson_id, $user_id ) {
		$settings          = $this->get_lesson_settings( $lesson_id );
		$access_expiration = isset( $settings['lesson_access_expiration'] ) ? $settings['lesson_access_expiration'] : '';

		// Default return if no expiration set.
		$default = array(
			'is_valid'          => true,
			'expiration_date'   => null,
			'days_remaining'    => null,
			'access_expiration' => null,
		);

		// If no expiration set, access is valid.
		if ( empty( $access_expiration ) || ! is_numeric( $access_expiration ) ) {
			return $default;
		}

		$course_id = $this->get_lesson_course( $lesson_id );
		if ( ! $course_id ) {
			return $default;
		}

		// Get enrollment date.
		$enrollment_date = $this->get_user_enrollment_date( $user_id, $course_id );
		if ( ! $enrollment_date ) {
			return $default; // If not enrolled, let enrollment check handle it.
		}

		// Calculate expiration date.
		$expiration_timestamp = strtotime( $enrollment_date . ' + ' . intval( $access_expiration ) . ' days' );
		$expiration_date      = wp_date(
			'Y-m-d H:i:s',
			$expiration_timestamp
		);

		// Get current time.
		// phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested -- Need site timezone for access expiration comparison.
		$current_timestamp = current_time( 'timestamp' );

		// Check if current time is past expiration.
		$is_valid = $current_timestamp < $expiration_timestamp;

		// Calculate days remaining.
		$days_remaining = 0;
		if ( $is_valid ) {
			$seconds_remaining = $expiration_timestamp - $current_timestamp;
			$days_remaining    = max( 0, ceil( $seconds_remaining / DAY_IN_SECONDS ) );
		}

		return array(
			'is_valid'          => $is_valid,
			'expiration_date'   => $expiration_date,
			'days_remaining'    => $days_remaining,
			'access_expiration' => intval( $access_expiration ),
		);
	}

	/**
	 * Get lesson course.
	 *
	 * @param int $lesson_id Lesson ID.
	 *
	 * @since 1.0.0
	 *
	 * @return int|null Course ID on success, null on failure.
	 */
	private function get_lesson_course( $lesson_id ) {
		// Use database relationships to find the course for this lesson.
		$relationships_query = SkillPulse_LMS_Relationships_Query::get_instance();
		$parents             = $relationships_query->get_parents( $lesson_id );
		if ( ! empty( $parents ) ) {
			foreach ( $parents as $parent ) {
				return SkillPulse_LMS_Course_Items_Query::get_instance()->get_item_course_id( $parent->parent_id );
			}
		}
		return null;
	}

	/**
	 * Get user enrollment date.
	 *
	 * @param int $user_id   User ID.
	 * @param int $course_id Course ID.
	 *
	 * @since 1.0.0
	 *
	 * @return string|null Enrollment date on success, null on failure.
	 */
	public function get_user_enrollment_date( $user_id, $course_id ) {
		// Get enrollment date from database.
		global $wpdb;
		$enrollment_table = esc_sql( $wpdb->prefix . 'splms_enrollments' );
		$result           = $wpdb->get_var(
			$wpdb->prepare(
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table name cannot be prepared, values are prepared.
				"SELECT enrolled_at FROM {$enrollment_table} WHERE user_id = %d AND course_id = %d LIMIT 1",
				$user_id,
				$course_id
			)
		);

		if ( ! empty( $wpdb->last_error ) ) {
			return null;
		}

		return $result;
	}

	/**
	 * Get lesson completion date.
	 *
	 * @param int $lesson_id Lesson ID.
	 * @param int $user_id   User ID.
	 *
	 * @since 1.0.0
	 *
	 * @return string|null Completion date on success, null on failure.
	 */
	public function get_lesson_completion_date( $lesson_id, $user_id ) {
		$progress = SkillPulse_LMS_Lesson_Progress_Query::get_instance()->get_lesson_progress( $user_id, $lesson_id );

		return $progress ? $progress->completed_at : null;
	}

	/**
	 * Get previous lesson in course curriculum using direct database query.
	 *
	 * @param int $lesson_id Lesson ID.
	 * @param int $course_id Course ID.
	 *
	 * @since 1.0.0
	 *
	 * @return int|null Previous lesson ID on success, null on failure.
	 */
	public function get_previous_lesson( $lesson_id, $course_id ) {
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

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table names are safe, using wpdb prepare.
		$lessons = $wpdb->get_col(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Query variable is properly constructed above.
				$query,
				$course_id,
				SPLMS_POST_TYPES['lesson'],
				SPLMS_POST_TYPES['section']
			)
		);

		if ( empty( $lessons ) ) {
			return null;
		}

		$previous_lesson_id = null;
		foreach ( $lessons as $lesson_item_id ) {
			if ( intval( $lesson_item_id ) === intval( $lesson_id ) ) {
				// Found current lesson, return the previous one.
				return $previous_lesson_id;
			}
			// Store this lesson as potential previous for next iteration.
			$previous_lesson_id = intval( $lesson_item_id );
		}

		return null; // Lesson not found in curriculum or is first lesson.
	}

	/**
	 * Check if user is enrolled in course.
	 *
	 * @param int $course_id Course ID.
	 * @param int $user_id   User ID.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if enrolled, false otherwise.
	 */
	private function is_user_enrolled( $course_id, $user_id ) {
		// Use the centralized enrollment check function.
		return splms_is_user_enrolled( $course_id, $user_id );
	}

	/**
	 * Check prevent_skip access using direct database query (avoids circular curriculum loading).
	 *
	 * @param int $lesson_id Lesson ID.
	 * @param int $user_id   User ID.
	 * @param int $course_id Course ID.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if access allowed, false otherwise.
	 */
	private function check_prevent_skip_access( $lesson_id, $user_id, $course_id ) {
		global $wpdb;

		$relationships_table = esc_sql( $wpdb->prefix . 'splms_relationships' );
		$course_items_table  = esc_sql( $wpdb->prefix . 'splms_course_items' );

		// Get all lessons for this course ordered by section and lesson order.
		$query = "
			SELECT r.child_id, r.order_index as lesson_order, ci.order_index as section_order
			FROM {$relationships_table} r
			INNER JOIN {$course_items_table} ci ON r.parent_id = ci.item_id
			WHERE ci.course_id = %d
			AND r.child_type = %s
			AND ci.item_type = %s
			ORDER BY ci.order_index ASC, r.order_index ASC
		";

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table names are safe, using wpdb prepare.
		$lessons = $wpdb->get_results(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Query variable is properly constructed above.
				$query,
				$course_id,
				SPLMS_POST_TYPES['lesson'],
				SPLMS_POST_TYPES['section']
			)
		);

		if ( empty( $lessons ) ) {
			return true;
		}

		$current_index = -1;
		foreach ( $lessons as $index => $lesson ) {
			if ( intval( $lesson->child_id ) === intval( $lesson_id ) ) {
				$current_index = $index;
				break;
			}
		}

		if ( $current_index <= 0 ) {
			return true;
		}

		// Check previous lessons for prevent_skip.
		for ( $i = 0; $i < $current_index; $i++ ) {
			$prev_lesson_id           = $lessons[ $i ]->child_id;
			$prev_completion_settings = $this->get_lesson_completion_settings( $prev_lesson_id );
			$prev_prevent_skip        = isset( $prev_completion_settings['prevent_skip'] ) ? $prev_completion_settings['prevent_skip'] : false;

			if ( $prev_prevent_skip && ! $this->is_lesson_completed( $prev_lesson_id, $user_id ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Get course navigation for lesson (with prevent skip logic).
	 *
	 * @param int $lesson_id Lesson ID.
	 * @param int $user_id   User ID.
	 *
	 * @since 1.0.0
	 *
	 * @return array|null Navigation array on success, null on failure.
	 */
	public function get_lesson_navigation( $lesson_id, $user_id ) {
		$course_id = $this->get_lesson_course( $lesson_id );
		if ( ! $course_id ) {
			return null;
		}

		// Get all course items (lessons and quizzes) in order.
		$course_items  = $this->get_course_items_ordered( $course_id );
		$current_index = - 1;

		// Find current lesson index.
		foreach ( $course_items as $index => $item ) {
			if ( intval( $item['id'] ) === intval( $lesson_id ) ) {
				$current_index = $index;
				break;
			}
		}

		if ( - 1 === $current_index ) {
			return null;
		}

		$navigation = array(
			'previous'          => null,
			'next'              => null,
			'can_navigate_next' => true,
		);

		// Previous item.
		if ( $current_index > 0 ) {
			$prev_item = $course_items[ $current_index - 1 ];

			// Verify previous item belongs to the same course (safety check).
			$prev_item_course_id = null;
			if ( SPLMS_POST_TYPES['lesson'] === $prev_item['type'] || 'lesson' === $prev_item['type'] ) {
				$prev_item_course_id = $this->get_lesson_course( $prev_item['id'] );
			} else {
				$prev_item_course_id = splms_get_quiz_course( $prev_item['id'] );
			}

			// Only set previous navigation if item belongs to same course.
			if ( $prev_item_course_id === $course_id ) {
				$navigation['previous'] = array(
					'id'    => $prev_item['id'],
					'title' => $prev_item['title'],
					'url'   => $prev_item['url'],
					'type'  => $prev_item['type'],
				);
			}
		}

		// Next item.
		if ( $current_index < count( $course_items ) - 1 ) {
			$next_item = $course_items[ $current_index + 1 ];

			// Verify next item belongs to the same course (safety check).
			$next_item_course_id = null;
			if ( SPLMS_POST_TYPES['lesson'] === $next_item['type'] || 'lesson' === $next_item['type'] ) {
				$next_item_course_id = $this->get_lesson_course( $next_item['id'] );
			} else {
				$next_item_course_id = splms_get_quiz_course( $next_item['id'] );
			}

			// Only set next navigation if item belongs to same course.
			if ( $next_item_course_id === $course_id ) {
				$navigation['next'] = array(
					'id'    => $next_item['id'],
					'title' => $next_item['title'],
					'url'   => $next_item['url'],
					'type'  => $next_item['type'],
				);

				// Check if current lesson prevents skip.
				$current_completion_settings = $this->get_lesson_completion_settings( $lesson_id );
				$prevent_skip                = isset( $current_completion_settings['prevent_skip'] ) ? $current_completion_settings['prevent_skip'] : false;

				if ( $prevent_skip && ! $this->is_lesson_completed( $lesson_id, $user_id ) ) {
					$navigation['can_navigate_next'] = false;
				}

				// Also check if next lesson has prevent_skip and previous lessons aren't completed.
				if ( $navigation['can_navigate_next'] && isset( $next_item ) ) {
					$next_completion_settings = $this->get_lesson_completion_settings( $next_item['id'] );
					$next_prevent_skip        = isset( $next_completion_settings['prevent_skip'] ) ? $next_completion_settings['prevent_skip'] : false;

					if ( $next_prevent_skip ) {
						// Check if current lesson is completed (required for next lesson).
						if ( ! $this->is_lesson_completed( $lesson_id, $user_id ) ) {
							$navigation['can_navigate_next'] = false;
						}
					}
				}
			}
		}

		return $navigation;
	}


	/**
	 * Get all course items (lessons and quizzes) in order.
	 *
	 * @param int $course_id Course ID.
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of course items.
	 */
	public function get_course_items_ordered( $course_id ) {
		// Get course curriculum using unified method.
		$curriculum_result = splms_get_course_curriculum( $course_id );

		$all_items = array();

		// Build flat array of all items (lessons and quizzes) in order.
		if ( isset( $curriculum_result['sections'] ) ) {
			foreach ( $curriculum_result['sections'] as $section ) {
				if ( isset( $section['children'] ) ) {
					foreach ( $section['children'] as $child ) {
						$all_items[] = array(
							'id'    => $child['id'],
							'title' => $child['title'],
							'url'   => $child['permalink'],
							'type'  => SPLMS_POST_TYPES['lesson'] === $child['type'] ? 'lesson' : 'quiz',
						);
					}
				}
			}
		}

		return $all_items;
	}

	/**
	 * Get lessons by course.
	 *
	 * @param int   $course_id Course ID.
	 * @param array $args      Additional query arguments.
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of lesson post objects.
	 */
	public function get_course_lessons( $course_id, $args = array() ) {
		// Get course curriculum using unified method.
		$curriculum_result = splms_get_course_curriculum( $course_id );

		$lesson_ids = array();

		// Extract lesson IDs from curriculum.
		if ( isset( $curriculum_result['sections'] ) ) {
			foreach ( $curriculum_result['sections'] as $section ) {
				if ( isset( $section['children'] ) ) {
					foreach ( $section['children'] as $child ) {
						// Only include lessons, not quizzes.
						if ( SPLMS_POST_TYPES['lesson'] === $child['type'] ) {
							$lesson_ids[] = $child['id'];
						}
					}
				}
			}
		}

		if ( empty( $lesson_ids ) ) {
			return array();
		}

		// Get lesson posts.
		$defaults = array(
			'post_type'      => SPLMS_POST_TYPES['lesson'],
			'posts_per_page' => - 1,
			'post__in'       => $lesson_ids,
			'orderby'        => 'post__in', // Maintain order from relationships.
			'order'          => 'ASC',
		);

		$args = wp_parse_args( $args, $defaults );

		return get_posts( $args );
	}

	/**
	 * Get user lesson progress for course.
	 *
	 * @param int $course_id Course ID.
	 * @param int $user_id   User ID.
	 *
	 * @since 1.0.0
	 *
	 * @return array Progress data array.
	 */
	public function get_user_course_progress( $course_id, $user_id ) {
		$lessons           = $this->get_course_lessons( $course_id );
		$total_lessons     = count( $lessons );
		$completed_lessons = 0;

		foreach ( $lessons as $lesson ) {
			if ( $this->is_lesson_completed( $lesson->ID, $user_id ) ) {
				++$completed_lessons;
			}
		}

		return array(
			'total'      => $total_lessons,
			'completed'  => $completed_lessons,
			'percentage' => $total_lessons > 0 ? ( $completed_lessons / $total_lessons ) * 100 : 0,
		);
	}

	/**
	 * Get completed item IDs for a course (lessons + quizzes) from database.
	 *
	 * @param int $user_id   User ID.
	 * @param int $course_id Course ID.
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of completed item IDs.
	 */
	public function get_completed_item_ids( $user_id, $course_id ) {
		global $wpdb;

		// Get completed lessons.
		$lesson_table      = esc_sql( $wpdb->prefix . 'splms_lesson_progress' );
		$completed_lessons = $wpdb->get_col(
			$wpdb->prepare(
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table name cannot be prepared, values are prepared.
				"SELECT lesson_id FROM {$lesson_table} WHERE user_id = %d AND course_id = %d AND is_completed = 1",
				$user_id,
				$course_id
			)
		);

		// Get passed quizzes.
		$quiz_table     = esc_sql( $wpdb->prefix . 'splms_quiz_attempts' );
		$passed_quizzes = $wpdb->get_col(
			$wpdb->prepare(
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table name cannot be prepared, values are prepared.
				"SELECT DISTINCT quiz_id FROM {$quiz_table} WHERE user_id = %d AND course_id = %d AND passed = 1",
				$user_id,
				$course_id
			)
		);

		// Combine and return.
		$completed_items = array_merge(
			array_map( 'intval', $completed_lessons ),
			array_map( 'intval', $passed_quizzes )
		);

		return array_unique( $completed_items );
	}

	/**
	 * Extract default values from lesson config data.
	 *
	 * @since 1.0.0
	 *
	 * @param array $config_data Lesson configuration data.
	 * @return array Default values organized by groups and individual fields.
	 */
	private function extract_defaults_from_config( $config_data ) {
		$defaults          = array();
		$field_definitions = array();

		if ( ! isset( $config_data['sections'] ) || ! is_array( $config_data['sections'] ) ) {
			return $defaults;
		}

		// First, collect all field definitions to identify groups.
		foreach ( $config_data['sections'] as $section ) {
			if ( isset( $section['fields'] ) && is_array( $section['fields'] ) ) {
				foreach ( $section['fields'] as $field ) {
					if ( isset( $field['id'] ) ) {
						$field_definitions[ $field['id'] ] = $field;
					}
				}
			}
		}

		// Group fields by their 'group' attribute.
		$grouped_fields    = array();
		$individual_fields = array();

		foreach ( $field_definitions as $field_id => $field ) {
			if ( isset( $field['group'] ) && ! empty( $field['group'] ) ) {
				$group_name = $field['group'];
				if ( ! isset( $grouped_fields[ $group_name ] ) ) {
					$grouped_fields[ $group_name ] = array();
				}
				$grouped_fields[ $group_name ][ $field_id ] = $field['default'] ?? '';
			} else {
				$individual_fields[ $field_id ] = $field['default'] ?? '';
			}
		}

		// Combine grouped and individual fields.
		$defaults = array_merge( $grouped_fields, $individual_fields );

		return $defaults;
	}
}
