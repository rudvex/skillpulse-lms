<?php
/**
 * Quizzes Class
 *
 * Handles quiz-related functionality and operations.
 *
 * @since   1.0.0
 * @package SkillPulse_LMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class SkillPulse_LMS_Quizzes
 *
 * @since 1.0.0
 */
class SkillPulse_LMS_Quizzes {

	/**
	 * Class instance.
	 *
	 * @var SkillPulse_LMS_Quizzes|null $instance
	 */
	private static $instance;

	/**
	 * Get the instance of this class.
	 *
	 * @since 1.0.0
	 *
	 * @return SkillPulse_LMS_Quizzes
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			$class_name     = __CLASS__;
			self::$instance = new $class_name();
		}

		return self::$instance;
	}

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
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
		// Load quiz-specific query classes and general functions.
		$files = array(
			'includes/modules/quizzes/class-quiz-questions-query',
			'includes/modules/quizzes/class-quiz-attempts-query',
			'includes/modules/quizzes/class-quiz-evaluator',
			'includes/modules/quizzes/class-quiz-service',
			'includes/modules/quizzes/class-guest-quiz-manager',
			'includes/modules/quizzes/class-quiz-navigation',
			'includes/modules/quizzes/general-functions',
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
	 */
	protected function load_classes() {
		// Quiz classes are loaded via the files array above.
	}

	/**
	 * Action hooks.
	 *
	 * @since 1.0.0
	 */
	protected function setup_actions() {
		// Quiz state management AJAX handlers.
		add_action( 'wp_ajax_splms_save_quiz_state', array( $this, 'handle_save_quiz_state' ) );
		add_action( 'wp_ajax_nopriv_splms_save_quiz_state', array( $this, 'handle_save_quiz_state' ) );
		add_action( 'wp_ajax_splms_get_quiz_state', array( $this, 'handle_get_quiz_state' ) );
		add_action( 'wp_ajax_nopriv_splms_get_quiz_state', array( $this, 'handle_get_quiz_state' ) );
		add_action( 'wp_ajax_splms_clear_quiz_state', array( $this, 'handle_clear_quiz_state' ) );
		add_action( 'wp_ajax_nopriv_splms_clear_quiz_state', array( $this, 'handle_clear_quiz_state' ) );

		// Quiz data loading AJAX handlers.
		add_action( 'wp_ajax_splms_start_quiz', array( $this, 'handle_start_quiz' ) );
		add_action( 'wp_ajax_nopriv_splms_start_quiz', array( $this, 'handle_start_quiz' ) );
		add_action( 'wp_ajax_splms_get_quiz_questions', array( $this, 'handle_get_quiz_questions' ) );

		// REST API registration.
		add_action( 'rest_api_init', array( $this, 'register_quiz_attempts_api' ) );
		add_action( 'wp_ajax_splms_get_quiz_settings', array( $this, 'handle_get_quiz_settings' ) );
		add_action( 'wp_ajax_splms_submit_quiz_final', array( $this, 'handle_submit_quiz' ) );
		add_action( 'wp_ajax_splms_submit_guest_quiz_attempt', array( $this, 'handle_submit_guest_attempt' ) );
		add_action( 'wp_ajax_nopriv_splms_submit_guest_quiz_attempt', array( $this, 'handle_submit_guest_quiz_attempt' ) ); // Allow non-logged-in users.
		add_action( 'wp_ajax_splms_get_quiz_attempts', array( $this, 'handle_get_quiz_attempts' ) );
		add_action( 'wp_ajax_nopriv_splms_get_quiz_attempts', array( $this, 'handle_get_quiz_attempts' ) ); // Allow non-logged-in users.

		// Quiz navigation and progress AJAX handlers.
		add_action( 'wp_ajax_splms_get_quiz_progress', array( $this, 'handle_get_quiz_progress' ) );
		add_action( 'wp_ajax_nopriv_splms_get_quiz_progress', array( $this, 'handle_get_quiz_progress' ) );
		add_action( 'wp_ajax_splms_toggle_bookmark', array( $this, 'handle_toggle_bookmark' ) );
		add_action( 'wp_ajax_nopriv_splms_toggle_bookmark', array( $this, 'handle_toggle_bookmark' ) );
		add_action( 'wp_ajax_splms_get_quiz_navigation', array( $this, 'handle_get_quiz_navigation' ) );
		add_action( 'wp_ajax_nopriv_splms_get_quiz_navigation', array( $this, 'handle_get_quiz_navigation' ) );
		add_action( 'wp_ajax_splms_get_review_summary', array( $this, 'handle_get_review_summary' ) );
		add_action( 'wp_ajax_nopriv_splms_get_review_summary', array( $this, 'handle_get_review_summary' ) );

		// Register cron job for cleanup.
		add_action( 'wp', array( $this, 'schedule_cleanup_cron' ) );
		add_action( 'splms_cleanup_abandoned_attempts', array( $this, 'run_cleanup_abandoned_attempts' ) );
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
	 * Get quiz navigation (previous/next items in course).
	 *
	 * @param int $quiz_id Quiz ID.
	 * @param int $user_id User ID.
	 *
	 * @since 1.0.0
	 *
	 * @return array|null Navigation data.
	 */
	public function get_quiz_navigation( $quiz_id, $user_id ) {
		$course_id = splms_get_quiz_course( $quiz_id );
		if ( ! $course_id ) {
			return null;
		}

		// Get all course items (lessons and quizzes) in order.
		$course_items  = $this->get_course_items_ordered( $course_id );
		$current_index = - 1;

		// Find current quiz index.
		foreach ( $course_items as $index => $item ) {
			if ( intval( $item['id'] ) === intval( $quiz_id ) ) {
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
				$prev_item_course_id = splms_get_lesson_course( $prev_item['id'] );
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
				$next_item_course_id = splms_get_lesson_course( $next_item['id'] );
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

				// Check if current quiz prevents skip (if it has completion requirements).
				$quiz_settings = $this->get_quiz_settings( $quiz_id );
				$prevent_skip  = $this->get_setting_value( $quiz_settings, 'prevent_skip', false );

				if ( $prevent_skip ) {
					$attempts_query = SkillPulse_LMS_Quiz_Attempts_Query::get_instance();
					$has_passed     = $attempts_query->has_user_passed( $user_id, $quiz_id );
					if ( ! $has_passed ) {
						$navigation['can_navigate_next'] = false;
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
	 * @return array Course items.
	 */
	public function get_course_items_ordered( $course_id ) {
		// Get course curriculum using unified method.
		$curriculum_result = splms_get_course_curriculum( $course_id );

		$all_items = array();

		// Extract all items from curriculum.
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
	 * Get a specific quiz setting value
	 * Handles both grouped and non-grouped field access
	 *
	 * @param array  $settings    Full settings array.
	 * @param string $key         Setting key (e.g., 'passing_grade' or 'quiz_grading_settings.passing_grade').
	 * @param mixed  $default_val Default value if not found.
	 *
	 * @return mixed Setting value or default
	 */
	private function get_setting_value( $settings, $key, $default_val = null ) {
		// Try direct access first.
		if ( isset( $settings[ $key ] ) ) {
			return $settings[ $key ];
		}

		// Try grouped access (group.field format).
		if ( false !== strpos( $key, '.' ) ) {
			list( $group, $field ) = explode( '.', $key, 2 );
			if ( isset( $settings[ $group ][ $field ] ) ) {
				return $settings[ $group ][ $field ];
			}
		}

		// Try finding in any group.
		foreach ( $settings as $group_key => $group_value ) {
			if ( is_array( $group_value ) && isset( $group_value[ $key ] ) ) {
				return $group_value[ $key ];
			}
		}

		return $default_val;
	}

	/**
	 * Get flattened quiz settings for frontend
	 * Flattens grouped settings to flat structure
	 *
	 * @param array $settings Full settings array with groups.
	 *
	 * @return array Flattened settings array.
	 */
	public function flatten_quiz_settings( $settings ) {
		$flattened = array();

		foreach ( $settings as $key => $value ) {
			if ( is_array( $value ) && ! isset( $value[0] ) ) {
				// This is a group, flatten it.
				foreach ( $value as $field_key => $field_value ) {
					$flattened[ $field_key ] = $field_value;
				}
			} else {
				// This is a regular field.
				$flattened[ $key ] = $value;
			}
		}

		return $flattened;
	}

	/**
	 * Get quiz settings (configuration-driven).
	 * Handles both grouped and non-grouped fields from JSON config.
	 *
	 * @param int $quiz_id Quiz ID.
	 *
	 * @return array Quiz settings.
	 */
	public function get_quiz_settings( $quiz_id ) {
		$all_meta = get_post_meta( $quiz_id );

		// Get configuration data and extract defaults.
		$config_data       = SkillPulse_LMS_Config_Loader::get_config( 'quizzes', 'admin' );
		$defaults_settings = $this->extract_defaults_from_config( $config_data );
		$quiz_settings     = array();

		foreach ( $defaults_settings as $key => $default_value ) {
			$meta_key   = "_splms_{$key}";
			$meta_value = isset( $all_meta[ $meta_key ][0] ) ? maybe_unserialize( $all_meta[ $meta_key ][0] ) : null;

			// Handle different data types properly.
			if ( is_array( $default_value ) ) {
				// For array defaults (groups or multi-select/repeater fields), use wp_parse_args.
				// wp_parse_args merges meta_value into default_value (meta values override defaults).
				$quiz_settings[ $key ] = wp_parse_args( (array) $meta_value, $default_value );
			} else {
				// For scalar defaults, use the meta value or fall back to default.
				$quiz_settings[ $key ] = null !== $meta_value ? $meta_value : $default_value;
			}
		}

		return $quiz_settings;
	}

	/**
	 * Update quiz settings.
	 *
	 * @param int   $quiz_id      Quiz ID.
	 * @param array $new_settings Settings.
	 *
	 * @return array|WP_Error
	 */
	public function update_quiz_settings( $quiz_id, $new_settings = array() ) {
		if ( empty( $quiz_id ) || ! is_array( $new_settings ) ) {
			return new WP_Error( 'splms_invalid_quiz_settings', __( 'Invalid quiz ID or settings.', 'skillpulse-lms' ), array( 'status' => 400 ) );
		}

		// Get defaults from configuration.
		$config_data       = SkillPulse_LMS_Config_Loader::get_config( 'quizzes', 'admin' );
		$defaults_settings = $this->extract_defaults_from_config( $config_data );

		foreach ( $defaults_settings as $key => $default_value ) {
			$meta_key = "_splms_{$key}";

			// New values for this group (if provided).
			// Use array_key_exists to properly check for false/0 values (same as course and lesson settings).
			$new_value = ( is_array( $new_settings ) && array_key_exists( $key, $new_settings ) ) ? $new_settings[ $key ] : null;

			// Handle different data types properly.
			if ( is_array( $default_value ) ) {
				// For array defaults, use wp_parse_args.
				// wp_parse_args merges $new_value into $default_value (new values override defaults).
				$final_value = wp_parse_args( (array) $new_value, $default_value );
			} else {
				// For scalar defaults, use the new value or fall back to default.
				$final_value = null !== $new_value ? $new_value : $default_value;
			}

			// Update meta.
			update_post_meta( $quiz_id, $meta_key, $final_value );
		}

		return $this->get_quiz_settings( $quiz_id );
	}

	/**
	 * Get quiz questions.
	 * Centralized method for web, API, and frontend.
	 *
	 * @param int  $quiz_id  Quiz ID.
	 * @param bool $is_admin Whether this is an admin request (default: false).
	 *
	 * @return array Quiz questions.
	 */
	public function get_quiz_questions( $quiz_id, $is_admin = false ) {
		$questions_query = SkillPulse_LMS_Quiz_Questions_Query::get_instance();
		$questions       = $questions_query->get_quiz_questions( $quiz_id );

		// Get quiz settings for randomization.
		$quiz_settings       = $this->get_quiz_settings( $quiz_id );
		$randomize_questions = $this->get_setting_value( $quiz_settings, 'randomize_questions', false );
		$randomize_options   = $this->get_setting_value( $quiz_settings, 'randomize_options', false );

		// Randomize questions order if enabled (only for frontend, not admin).
		if ( $randomize_questions && ! $is_admin && ! empty( $questions ) ) {
			shuffle( $questions );
		}

		// Convert database objects to arrays.
		$questions_array = array();
		foreach ( $questions as $question ) {
			// Get base question data.
			$question_data = $this->get_base_question_data( $question, $is_admin );

			// Normalize settings.
			$settings = maybe_unserialize( $question->settings );
			if ( ! is_array( $settings ) ) {
				$settings = array();
			}
			// Remove correct_answer from settings if it exists (we don't want duplication).
			unset( $settings['correct_answer'] );
			$question_data['settings'] = $settings;

			// Get correct_answer from correct_answer_json.
			$correct_answer_data      = null;
			$correct_answer_from_json = null;
			if ( isset( $question->correct_answer ) && is_array( $question->correct_answer ) && ! empty( $question->correct_answer ) ) {
				$correct_answer_data = $question->correct_answer;
				// Add correct_answer_json to response for admin.
				if ( $is_admin ) {
					$question_data['correct_answer_json'] = $correct_answer_data;
				}
				// Extract answers array.
				if ( isset( $correct_answer_data['answers'] ) ) {
					$correct_answer_from_json = $correct_answer_data['answers'];
				} elseif ( 'matching' === $question->question_type && ! isset( $correct_answer_data['type'] ) ) {
					// For matching, it might already be in {left_id: right_id} format.
					$correct_answer_from_json = $correct_answer_data;
				}
			}

			// Convert options from options_json.
			$options = is_array( $question->options ) ? $question->options : array();

			// Format question based on type.
			switch ( $question->question_type ) {
				case 'multiple_choice':
					$question_data = $this->format_multiple_choice_question( $question_data, $options, $correct_answer_from_json, $randomize_options, $is_admin );
					break;
				case 'multiple_select':
					$question_data = $this->format_multiple_select_question( $question_data, $options, $correct_answer_from_json, $is_admin );
					break;
				case 'true_false':
					$question_data = $this->format_true_false_question( $question_data, $options, $correct_answer_from_json, $is_admin );
					break;
				case 'matching':
					$question_data = $this->format_matching_question( $question_data, $options, $settings, $correct_answer_from_json, $is_admin );
					break;
				case 'ordering':
					$question_data = $this->format_ordering_question( $question_data, $options, $correct_answer_from_json, $is_admin );
					break;
				case 'short_answer':
					$question_data = $this->format_short_answer_question( $question_data, $correct_answer_from_json, $is_admin );
					break;
				case 'fill_blank':
					$question_data = $this->format_fill_blank_question( $question_data, $correct_answer_from_json, $is_admin );
					break;
				case 'essay':
					$question_data = $this->format_essay_question( $question_data, $is_admin );
					break;
				case 'file_upload':
					$question_data = $this->format_file_upload_question( $question_data, $settings, $is_admin );
					break;
			}

			$questions_array[] = $question_data;
		}

		return $questions_array;
	}

	/**
	 * Get base question data (common fields for all question types).
	 *
	 * @param object $question Question object from database.
	 * @param bool   $is_admin Whether this is an admin request.
	 *
	 * @return array Base question data.
	 */
	private function get_base_question_data( $question, $is_admin = false ) {
		$question_data = array(
			'type'        => $question->question_type,
			'question'    => $question->question_text,
			'description' => $question->question_description,
			'explanation' => $question->explanation,
			'points'      => $question->points,
			'required'    => (bool) $question->is_required,
			'media'       => array(
				'type' => $question->media_type,
				'url'  => $question->media_url,
			),
		);

		// Add admin-specific fields.
		if ( $is_admin ) {
			$question_data['question_id'] = $question->id;
			$question_data['order_index'] = (int) $question->order_index;
		} else {
			$question_data['id'] = $question->id;
		}

		return $question_data;
	}

	/**
	 * Format multiple choice question.
	 *
	 * @param array $question_data            Base question data.
	 * @param array $options                  Options array.
	 * @param array $correct_answer_from_json Correct answer from JSON.
	 * @param bool  $randomize_options        Whether to randomize options.
	 * @param bool  $is_admin                 Whether this is an admin request.
	 *
	 * @return array Formatted question data.
	 */
	private function format_multiple_choice_question( $question_data, $options, $correct_answer_from_json, $randomize_options, $is_admin = false ) {
		// Derive correct answer texts.
		$correct_answer_texts = array();
		if ( is_array( $correct_answer_from_json ) && ! empty( $correct_answer_from_json ) ) {
			$correct_answer_texts = array( $correct_answer_from_json[0] );
		}

		$question_data['options'] = array();
		$options_to_process       = $options;

		// Randomize options if enabled (only for frontend, not admin).
		if ( $randomize_options && ! $is_admin && ! empty( $options_to_process ) ) {
			shuffle( $options_to_process );
		}

		foreach ( $options_to_process as $option ) {
			$option_text = isset( $option['text'] ) ? trim( (string) $option['text'] ) : '';

			// Determine is_correct ONLY by checking if option text is in correct_answer_json.
			$is_correct = in_array( $option_text, $correct_answer_texts, true );

			$option_data = array(
				'text' => $option_text,
			);

			// Add is_correct only for admin.
			if ( $is_admin ) {
				$option_data['is_correct'] = $is_correct;
			} elseif ( isset( $option['id'] ) ) {
				$option_data['id'] = $option['id'];
			}

			$question_data['options'][] = $option_data;
		}

		// Set correct_answer from correct_answer_json (only for admin).
		if ( $is_admin ) {
			$correct_text                         = ! empty( $correct_answer_texts ) ? $correct_answer_texts[0] : '';
			$question_data['correct_answer']      = $correct_text;
			$question_data['correct_answer_text'] = $correct_text;
		}

		return $question_data;
	}

	/**
	 * Format multiple select question.
	 *
	 * @param array $question_data            Base question data.
	 * @param array $options                  Options array.
	 * @param array $correct_answer_from_json Correct answer from JSON.
	 * @param bool  $is_admin                 Whether this is an admin request.
	 *
	 * @return array Formatted question data.
	 */
	private function format_multiple_select_question( $question_data, $options, $correct_answer_from_json, $is_admin = false ) {
		// Derive correct answer texts.
		$correct_answer_texts = is_array( $correct_answer_from_json ) ? $correct_answer_from_json : array();

		$question_data['options'] = array();

		foreach ( $options as $option ) {
			$option_text = isset( $option['text'] ) ? trim( (string) $option['text'] ) : '';

			// Determine is_correct ONLY by checking if option text is in correct_answer_json.
			$is_correct = in_array( $option_text, $correct_answer_texts, true );

			$option_data = array(
				'text' => $option_text,
			);

			// Add is_correct only for admin.
			if ( $is_admin ) {
				$option_data['is_correct'] = $is_correct;
			}

			$question_data['options'][] = $option_data;
		}

		// Set correct_answer from correct_answer_json (only for admin).
		if ( $is_admin ) {
			$question_data['correct_answer'] = $correct_answer_texts;
		}

		return $question_data;
	}

	/**
	 * Format true/false question.
	 *
	 * @param array $question_data            Base question data.
	 * @param array $options                  Options array.
	 * @param array $correct_answer_from_json Correct answer from JSON.
	 * @param bool  $is_admin                 Whether this is an admin request.
	 *
	 * @return array Formatted question data.
	 */
	private function format_true_false_question( $question_data, $options, $correct_answer_from_json, $is_admin = false ) {
		// Derive correct answer text.
		$correct_text = ( is_array( $correct_answer_from_json ) && ! empty( $correct_answer_from_json ) ) ? $correct_answer_from_json[0] : '';

		$question_data['options'] = array();

		foreach ( $options as $option ) {
			$option_text = isset( $option['text'] ) ? trim( (string) $option['text'] ) : '';

			// Determine is_correct ONLY by checking if option text matches correct_answer_json.
			$is_correct = ( $option_text === $correct_text );

			$option_data = array(
				'text' => $option_text,
			);

			// Add is_correct only for admin.
			if ( $is_admin ) {
				$option_data['is_correct'] = $is_correct;
			} elseif ( isset( $option['id'] ) ) {
				$option_data['id'] = $option['id'];
			}

			$question_data['options'][] = $option_data;
		}

		// Set correct_answer from correct_answer_json (only for admin).
		if ( $is_admin ) {
			$question_data['correct_answer'] = $correct_text;
		}

		return $question_data;
	}

	/**
	 * Format matching question.
	 *
	 * @param array $question_data            Base question data.
	 * @param array $options                  Options array.
	 * @param array $settings                 Settings array.
	 * @param array $correct_answer_from_json Correct answer from JSON.
	 * @param bool  $is_admin                 Whether this is an admin request.
	 *
	 * @return array Formatted question data.
	 */
	private function format_matching_question( $question_data, $options, $settings, $correct_answer_from_json, $is_admin = false ) {
		// Get pairs from settings.
		if ( isset( $settings['pairs'] ) && is_array( $settings['pairs'] ) ) {
			$question_data['pairs'] = $settings['pairs'];
		} else {
			$question_data['pairs'] = array();
		}

		// Include options for evaluator to derive correct pairs (only for frontend).
		if ( ! $is_admin ) {
			$question_data['options'] = $options;
		}

		// Set correct_answer from correct_answer_json (only for admin).
		if ( $is_admin ) {
			$question_data['correct_answer'] = is_array( $correct_answer_from_json ) ? $correct_answer_from_json : array();
		}

		return $question_data;
	}

	/**
	 * Format ordering question.
	 *
	 * @param array $question_data            Base question data.
	 * @param array $options                  Options array.
	 * @param array $correct_answer_from_json Correct answer from JSON.
	 * @param bool  $is_admin                 Whether this is an admin request.
	 *
	 * @return array Formatted question data.
	 */
	private function format_ordering_question( $question_data, $options, $correct_answer_from_json, $is_admin = false ) {
		$question_data['items'] = array();

		// Sort options by order_index to get correct order.
		usort(
			$options,
			function ( $a, $b ) {
				$a_order = isset( $a['order_index'] ) ? intval( $a['order_index'] ) : 0;
				$b_order = isset( $b['order_index'] ) ? intval( $b['order_index'] ) : 0;

				return $a_order - $b_order;
			}
		);

		// Build items array with text values.
		foreach ( $options as $option ) {
			$option_text = isset( $option['text'] ) ? trim( (string) $option['text'] ) : '';

			if ( ! empty( $option_text ) ) {
				$question_data['items'][] = array(
					'id'   => $option_text, // Use text as id since we're using text values.
					'text' => $option_text,
				);
			}
		}

		// Set correct_answer from correct_answer_json (only for admin).
		if ( $is_admin ) {
			$question_data['correct_answer'] = is_array( $correct_answer_from_json ) ? $correct_answer_from_json : array();
		}

		return $question_data;
	}

	/**
	 * Format short answer question.
	 *
	 * @param array $question_data            Base question data.
	 * @param array $correct_answer_from_json Correct answer from JSON.
	 * @param bool  $is_admin                 Whether this is an admin request.
	 *
	 * @return array Formatted question data.
	 */
	private function format_short_answer_question( $question_data, $correct_answer_from_json, $is_admin = false ) {
		// Set correct_answer from correct_answer_json (only for admin).
		if ( $is_admin ) {
			$question_data['correct_answer'] = ( is_array( $correct_answer_from_json ) && ! empty( $correct_answer_from_json ) ) ? $correct_answer_from_json[0] : '';
		}

		return $question_data;
	}

	/**
	 * Format fill blank question.
	 *
	 * @param array $question_data            Base question data.
	 * @param array $correct_answer_from_json Correct answer from JSON.
	 * @param bool  $is_admin                 Whether this is an admin request.
	 *
	 * @return array Formatted question data.
	 */
	private function format_fill_blank_question( $question_data, $correct_answer_from_json, $is_admin = false ) {
		// Set correct_answer from correct_answer_json (only for admin).
		if ( $is_admin ) {
			$question_data['correct_answer'] = ( is_array( $correct_answer_from_json ) && ! empty( $correct_answer_from_json ) ) ? $correct_answer_from_json[0] : '';
		}

		return $question_data;
	}

	/**
	 * Format essay question.
	 *
	 * @param array $question_data Base question data.
	 * @param bool  $is_admin      Whether this is an admin request.
	 *
	 * @return array Formatted question data.
	 */
	private function format_essay_question( $question_data, $is_admin = false ) {
		// Essay always has empty correct_answer (only for admin).
		if ( $is_admin ) {
			$question_data['correct_answer'] = '';
		}

		return $question_data;
	}

	/**
	 * Format file upload question.
	 *
	 * @param array $question_data Base question data.
	 * @param array $settings      Settings array.
	 * @param bool  $is_admin      Whether this is an admin request.
	 *
	 * @return array Formatted question data.
	 */
	private function format_file_upload_question( $question_data, $settings, $is_admin = false ) {
		$question_data['allowed_types'] = isset( $settings['allowed_types'] )
			? $settings['allowed_types']
			: 'pdf,docx,jpg,png';
		$question_data['max_file_size'] = isset( $settings['max_file_size'] )
			? $settings['max_file_size']
			: 10;

		// File upload has no auto-correct answer (only for admin).
		if ( $is_admin ) {
			$question_data['correct_answer'] = 'pending_review';
		}

		return $question_data;
	}

	/**
	 * Get user quiz attempts.
	 *
	 * @param int      $user_id User ID.
	 * @param int|null $quiz_id Quiz ID.
	 *
	 * @return array Quiz attempts.
	 */
	public function get_user_quiz_attempts( $user_id, $quiz_id = null ) {
		global $wpdb;

		$table_name = esc_sql( $wpdb->prefix . 'splms_quiz_attempts' );
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table name cannot be prepared.
		$query  = "SELECT * FROM $table_name WHERE user_id = %d";
		$params = array( $user_id );

		if ( $quiz_id ) {
			$query   .= ' AND quiz_id = %d';
			$params[] = $quiz_id;
		}

		$query .= ' ORDER BY attempt_time DESC';

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Query is prepared with $wpdb->prepare().
		return $wpdb->get_results( $wpdb->prepare( $query, $params ) );
	}

	/**
	 * Handle save quiz state AJAX request
	 */
	public function handle_save_quiz_state() {
		// Verify nonce.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Nonce verification and sanitization handled by wp_verify_nonce().
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'splms_nonce' ) ) {
			wp_send_json_error( 'Security check failed' );
		}

		$user_id  = get_current_user_id();
		$is_guest = 0 === $user_id;

		// For logged-in users, apply rate limiting.
		if ( ! $is_guest && ! $this->check_rate_limit( 'save_quiz', $user_id, 10, 60 ) ) {
			wp_send_json_error( 'Rate limit exceeded. Please wait before saving again.' );
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce already verified above.
		$quiz_id    = isset( $_POST['quiz_id'] ) ? intval( wp_unslash( $_POST['quiz_id'] ) ) : 0;
		$attempt_id = isset( $_POST['attempt_id'] ) ? intval( wp_unslash( $_POST['attempt_id'] ) ) : 0;
		// phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Nonce verified above, answers will be sanitized during processing.
		$answers          = isset( $_POST['answers'] ) ? wp_unslash( $_POST['answers'] ) : array();
		$time_taken       = isset( $_POST['time_taken'] ) ? intval( wp_unslash( $_POST['time_taken'] ) ) : 0;
		$current_question = isset( $_POST['current_question'] ) ? intval( wp_unslash( $_POST['current_question'] ) ) : 0;

		if ( ! $quiz_id ) {
			wp_send_json_error( 'Invalid quiz ID' );
		}

		// For regular users, require attempt_id.
		if ( ! $is_guest && ! $attempt_id ) {
			wp_send_json_error( 'Invalid attempt ID' );
		}

		// Use Quiz Service for unified state management.
		$quiz_service = SkillPulse_LMS_Quiz_Service::get_instance();
		$identifier   = $is_guest ? $quiz_id : $attempt_id;
		$result       = $quiz_service->save_quiz_state( $identifier, $answers, $time_taken, $current_question, $is_guest );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( $result->get_error_message() );
		} else {
			wp_send_json_success( $result );
		}
	}

	/**
	 * Handle get quiz state AJAX request.
	 */
	public function handle_get_quiz_state() {
		// Verify nonce.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Nonce verification and sanitization handled by wp_verify_nonce().
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'splms_nonce' ) ) {
			wp_send_json_error( 'Security check failed' );
		}

		$user_id  = get_current_user_id();
		$is_guest = 0 === $user_id;
		$quiz_id  = isset( $_POST['quiz_id'] ) ? intval( wp_unslash( $_POST['quiz_id'] ) ) : 0;

		if ( ! $quiz_id ) {
			wp_send_json_error( 'Invalid quiz ID' );
		}

		// Use Quiz Service for unified state management.
		$quiz_service = SkillPulse_LMS_Quiz_Service::get_instance();
		$identifier   = $is_guest ? $quiz_id : $user_id;
		$result       = $quiz_service->get_quiz_state( $identifier, $quiz_id, $is_guest );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( $result->get_error_message() );
		} else {
			wp_send_json_success( $result );
		}
	}

	/**
	 * Handle clear quiz state AJAX request.
	 * IMPORTANT: This should ONLY be called when abandoning a quiz, NOT after successful submission.
	 * It only clears truly in-progress attempts (score=0, passed=0, time_taken=0, no answers).
	 */
	public function handle_clear_quiz_state() {
		// Verify nonce.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Nonce verification and sanitization handled by wp_verify_nonce().
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'splms_nonce' ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Security check failed.', 'skillpulse-lms' ),
				)
			);
		}

		$user_id  = get_current_user_id();
		$is_guest = 0 === $user_id;
		$quiz_id  = isset( $_POST['quiz_id'] ) ? intval( wp_unslash( $_POST['quiz_id'] ) ) : 0;

		if ( ! $quiz_id ) {
			wp_send_json_error(
				array(
					'message' => __( 'Invalid quiz ID.', 'skillpulse-lms' ),
				)
			);
		}

		if ( $is_guest ) {
			// For guest users, use Guest Quiz Manager.
			$guest_manager = SkillPulse_LMS_Guest_Quiz_Manager::get_instance();
			$result        = $guest_manager->clear_guest_quiz_state( $quiz_id );
		} else {
			// For logged-in users, use Quiz Service.
			$quiz_service = SkillPulse_LMS_Quiz_Service::get_instance();
			$result       = $quiz_service->clear_quiz_state( $quiz_id, $user_id );
		}

		if ( is_wp_error( $result ) ) {
			wp_send_json_error(
				array(
					'message' => $result->get_error_message(),
				)
			);
		} else {
			wp_send_json_success( $result );
		}
	}

	/**
	 * Handle start quiz AJAX request.
	 */
	public function handle_start_quiz() {
		// Verify nonce.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Nonce verification and sanitization handled by wp_verify_nonce().
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'splms_nonce' ) ) {
			wp_send_json_error( 'Security check failed' );
		}

		$user_id = get_current_user_id();
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce already verified above.
		$quiz_id   = isset( $_POST['quiz_id'] ) ? intval( wp_unslash( $_POST['quiz_id'] ) ) : 0;
		$course_id = isset( $_POST['course_id'] ) ? intval( wp_unslash( $_POST['course_id'] ) ) : 0;
		// phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Nonce verified above, preview_mode is sanitized.
		$preview_mode_value = isset( $_POST['preview_mode'] ) ? wp_unslash( $_POST['preview_mode'] ) : '';
		$is_preview_mode    = ( 'true' === $preview_mode_value || true === $preview_mode_value || '1' === $preview_mode_value || 1 === $preview_mode_value );

		// Use Quiz Service for unified business logic.
		$quiz_service = SkillPulse_LMS_Quiz_Service::get_instance();
		$result       = $quiz_service->start_quiz(
			$quiz_id,
			$user_id,
			array(
				'course_id'    => $course_id,
				'preview_mode' => $is_preview_mode,
			)
		);

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( $result->get_error_message() );
		}

		wp_send_json_success( $result );
	}

	/**
	 * Handle get quiz questions AJAX request.
	 */
	public function handle_get_quiz_questions() {
		// Verify nonce.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Nonce verification and sanitization handled by wp_verify_nonce().
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'splms_nonce' ) ) {
			wp_send_json_error( 'Security check failed' );
		}

		$quiz_id = isset( $_POST['quiz_id'] ) ? intval( wp_unslash( $_POST['quiz_id'] ) ) : 0;
		if ( ! $quiz_id ) {
			wp_send_json_error( 'Invalid quiz ID' );
		}

		// Check if user can edit this quiz (admin request).
		$is_admin = current_user_can( 'edit_post', $quiz_id );

		// Get questions using centralized method (is_admin=false for frontend).
		$questions = $this->get_quiz_questions( $quiz_id, $is_admin );

		wp_send_json_success( $questions );
	}

	/**
	 * Handle get quiz settings AJAX request.
	 */
	public function handle_get_quiz_settings() {
		// Verify nonce.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Nonce verification and sanitization handled by wp_verify_nonce().
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'splms_nonce' ) ) {
			wp_send_json_error( 'Security check failed' );
		}

		$quiz_id = isset( $_POST['quiz_id'] ) ? intval( wp_unslash( $_POST['quiz_id'] ) ) : 0;
		if ( ! $quiz_id ) {
			wp_send_json_error( 'Invalid quiz ID' );
		}

		$settings = $this->get_quiz_settings( $quiz_id );
		// Flatten settings for frontend.
		$flattened_settings = $this->flatten_quiz_settings( $settings );
		wp_send_json_success( $flattened_settings );
	}

	/**
	 * Handle submit quiz AJAX request (updated version).
	 */
	public function handle_submit_quiz() {
		// Verify nonce.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Nonce verification and sanitization handled by wp_verify_nonce().
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'splms_nonce' ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Security check failed. Please refresh the page and try again.', 'skillpulse-lms' ),
				)
			);
		}

		$user_id = get_current_user_id();
		if ( 0 === $user_id ) {
			wp_send_json_error(
				array(
					'message' => __( 'You must be logged in to submit a quiz.', 'skillpulse-lms' ),
				)
			);
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce already verified above.
		$quiz_id    = isset( $_POST['quiz_id'] ) ? intval( wp_unslash( $_POST['quiz_id'] ) ) : 0; // phpcs:ignore Generic.Formatting.MultipleStatementAlignment.NotSameWarning -- Variable assignments don't need alignment.
		$attempt_id = isset( $_POST['attempt_id'] ) ? intval( wp_unslash( $_POST['attempt_id'] ) ) : 0; // phpcs:ignore Generic.Formatting.MultipleStatementAlignment.NotSameWarning -- Variable assignments don't need alignment.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,Generic.Formatting.MultipleStatementAlignment.NotSameWarning -- Nonce verified above, answers will be sanitized during evaluation.
		$answers = isset( $_POST['answers'] ) ? json_decode( wp_unslash( $_POST['answers'] ), true ) : array();
		// phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Nonce verified above, file_uploads will be validated.
		$file_uploads = isset( $_POST['file_uploads'] ) ? json_decode( wp_unslash( $_POST['file_uploads'] ), true ) : array();
		$time_taken   = isset( $_POST['time_taken'] ) ? intval( wp_unslash( $_POST['time_taken'] ) ) : 0; // phpcs:ignore Generic.Formatting.MultipleStatementAlignment.NotSameWarning -- Variable assignments don't need alignment.

		if ( ! $quiz_id || ! $attempt_id ) {
			wp_send_json_error(
				array(
					'message' => __( 'Invalid quiz ID or attempt ID. Please start a new attempt.', 'skillpulse-lms' ),
				)
			);
		}

		// Start transaction for atomic quiz submission.
		global $wpdb;
		$wpdb->query( 'START TRANSACTION' );

		// Handle file uploads using File Manager.
		$uploaded_files = array();
		if ( ! empty( $file_uploads ) ) {
			// Get quiz questions to retrieve file upload settings.
			// Need admin data for file upload settings.
			$questions         = $this->get_quiz_questions( $quiz_id, true );
			$question_settings = array();
			foreach ( $questions as $q ) {
				if ( 'file_upload' === $q['type'] ) {
					// Validate file upload settings.
					$allowed_types = isset( $q['allowed_types'] ) && ! empty( $q['allowed_types'] )
						? $q['allowed_types']
						: 'pdf,docx,jpg,png';

					$max_file_size = isset( $q['max_file_size'] ) ? intval( $q['max_file_size'] ) : 10;
					$max_file_size = max( 1, min( $max_file_size, 50 ) ); // Clamp between 1-50 MB.

					$question_settings[ $q['id'] ] = array(
						'allowed_types' => $allowed_types,
						'max_file_size' => $max_file_size,
					);
				}
			}

			foreach ( $file_uploads as $question_id => $file_name ) {
				$file_key = 'file_' . $question_id;
				// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- $_FILES is validated and sanitized during file upload processing.
				if ( isset( $_FILES[ $file_key ] ) && isset( $_FILES[ $file_key ]['error'] ) && UPLOAD_ERR_OK === $_FILES[ $file_key ]['error'] ) {
					// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- $_FILES is validated and sanitized during file upload processing.
					$file = $_FILES[ $file_key ];

					// Get question-specific settings.
					$allowed_types_str = isset( $question_settings[ $question_id ]['allowed_types'] )
						? $question_settings[ $question_id ]['allowed_types']
						: 'pdf,docx,jpg,png';
					$max_file_size     = isset( $question_settings[ $question_id ]['max_file_size'] )
						? $question_settings[ $question_id ]['max_file_size']
						: 10;

					// Parse allowed types.
					$allowed_types = array_map( 'trim', explode( ',', strtolower( $allowed_types_str ) ) );
					$file_ext      = strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );

					// Validate file type.
					if ( in_array( $file_ext, $allowed_types, true ) ) {
						// Validate file size (convert MB to bytes).
						$max_size_bytes = $max_file_size * 1024 * 1024;
						if ( $file['size'] <= $max_size_bytes ) {
							// Upload file using File Manager.
							$upload_result = $this->upload_quiz_file( $file, $quiz_id, $attempt_id, $question_id );

							if ( ! is_wp_error( $upload_result ) ) {
								$uploaded_files[ $question_id ] = array(
									'url'       => $upload_result['url'],
									'file_name' => $upload_result['filename'],
									'filepath'  => $upload_result['filepath'],
								);

								// Update answer with file URL.
								$answers[ $question_id ] = $upload_result['url'];
							}
						}
					}
				}
			}
		}

		// Get quiz questions and settings.
		// IMPORTANT: For evaluation, we need questions WITH correct answer data.
		// get_quiz_questions() returns questions with correct_answer field (flattened format).
		// The evaluator's normalize_question_data() will handle both nested and flattened formats.
		$questions = $this->get_quiz_questions( $quiz_id, true );
		$settings  = $this->get_quiz_settings( $quiz_id );

		// Get quiz type (get it early, before evaluation adjustments).
		$quiz_type = $this->get_setting_value( $settings, 'quiz_type', 'graded' );

		// Adjust default behavior based on quiz type.
		if ( 'practice' === $quiz_type ) {
			// Practice quizzes: Show answers immediately, allow unlimited attempts.
			if ( ! isset( $settings['show_correct_answers'] ) ) {
				$settings['show_correct_answers'] = true;
			}
			if ( ! isset( $settings['show_correct_answers_timing'] ) ) {
				$settings['show_correct_answers_timing'] = 'after_completion';
			}
			if ( ! isset( $settings['max_attempts'] ) || 0 === $settings['max_attempts'] ) {
				$settings['max_attempts'] = 0; // Unlimited for practice.
			}
		} elseif ( 'survey' === $quiz_type ) {
			// Surveys: No pass/fail, no scoring.
			if ( ! isset( $settings['passing_grade'] ) ) {
				$settings['passing_grade'] = 0; // No passing grade for surveys.
			}
			if ( ! isset( $settings['show_correct_answers'] ) ) {
				$settings['show_correct_answers'] = false; // No "correct" answers in surveys.
			}
		}

		if ( empty( $questions ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'No questions found for this quiz. Please contact the instructor.', 'skillpulse-lms' ),
				)
			);
		}

		// Calculate server-side time taken (SECURITY: Don't trust client time).
		$attempts_query    = SkillPulse_LMS_Quiz_Attempts_Query::get_instance();
		$attempt           = $attempts_query->get_attempt_by_id( $attempt_id );
		$start_time        = isset( $attempt->attempt_time ) ? strtotime( $attempt->attempt_time ) : time();
		$current_time      = time();
		$actual_time_taken = $current_time - $start_time;

		// Validate time limit if enabled.
		$time_limit_enabled = $this->get_setting_value( $settings, 'time_limit_enabled', false );
		$time_limit         = $this->get_setting_value( $settings, 'time_limit', 0 );
		if ( $time_limit_enabled && $time_limit > 0 ) {
			$time_limit_seconds = $time_limit * 60; // Convert minutes to seconds.

			// Allow 30 seconds buffer for network latency.
			if ( $actual_time_taken > ( $time_limit_seconds + 30 ) ) {
				wp_send_json_error(
					array(
						'message'             => 'Time limit exceeded. Quiz submission rejected.',
						'time_limit_exceeded' => true,
						'time_taken'          => $actual_time_taken,
						'time_limit'          => $time_limit_seconds,
					)
				);
			}
		}

		// Use centralized evaluation function.
		$passing_grade = $this->get_setting_value( $settings, 'passing_grade', 70 );
		$evaluation    = SkillPulse_LMS_Quiz_Evaluator::evaluate_attempt( $quiz_id, $questions, $answers, $passing_grade );

		$correct_answers = $evaluation['correct_answers'];
		$total_questions = $evaluation['total_questions'];
		$points_earned   = $evaluation['points_earned'] ?? 0;
		$total_points    = $evaluation['total_points'] ?? 0;
		$percentage      = $evaluation['percentage'];
		$passed          = $evaluation['passed'];

		$detailed_results     = $evaluation['detailed_results'];
		$pending_review       = $evaluation['pending_review'] ?? false;
		$has_manual_review    = $evaluation['has_manual_review'] ?? false;
		$manual_review_points = $evaluation['manual_review_points'] ?? 0.0;

		// Get quiz type (get it early, before evaluation adjustments).
		$quiz_type = $this->get_setting_value( $settings, 'quiz_type', 'graded' );

		// After evaluation, adjust for survey quizzes.
		if ( 'survey' === $quiz_type ) {
			$passed     = true; // Always "passed" for surveys (no evaluation).
			$percentage = 100; // Set to 100% for display purposes.
		}

		// Add file URLs for file upload questions in results.
		foreach ( $detailed_results as &$result ) {
			if ( 'file_upload' === $result['question_type'] && ! empty( $result['user_answer'] ) ) {
				// File URL from File Manager.
				$result['user_answer_url'] = $result['user_answer'];
			}
		}
		unset( $result ); // Break reference.

		// Verify attempt belongs to current user.
		if ( intval( $attempt->user_id ) !== $user_id ) {
			wp_send_json_error(
				array(
					'message' => __( 'Security check failed: Attempt ownership mismatch.', 'skillpulse-lms' ),
				)
			);
		}

		// Determine the correct status based on evaluation.
		$new_status = 'graded'; // Default for fully auto-graded quizzes.
		if ( $has_manual_review ) {
			$new_status = 'pending_review'; // Has questions needing manual review.
		}

		// For surveys, always mark as graded (no scoring).
		if ( 'survey' === $quiz_type ) {
			$new_status = 'graded';
		}

		// Complete the attempt in database.
		// Use server-calculated time, not client-provided time (security).
		$completed = $attempts_query->complete_attempt(
			$attempt_id,
			$answers,
			$points_earned,
			$total_points,
			$actual_time_taken,
			$new_status
		);

		if ( ! $completed ) {
			// Rollback transaction on failure.
			$wpdb->query( 'ROLLBACK' );

			// Clean up uploaded files.
			foreach ( $uploaded_files as $file_data ) {
				if ( isset( $file_data['filepath'] ) && file_exists( $file_data['filepath'] ) ) {
					wp_delete_file( $file_data['filepath'] );
				}
			}

			wp_send_json_error(
				array(
					'message' => __( 'Failed to save quiz attempt. Please try again or contact support.', 'skillpulse-lms' ),
				)
			);
		}

		// Verify the attempt was actually saved by fetching it again.
		// Check if status was updated to confirm submission was recorded.
		$saved_attempt = $attempts_query->get_attempt_by_id( $attempt_id );
		if ( ! $saved_attempt || ! in_array( $saved_attempt->status, array( 'graded', 'pending_review' ), true ) ) {
			// Rollback transaction on verification failure.
			$wpdb->query( 'ROLLBACK' );

			// Clean up uploaded files.
			foreach ( $uploaded_files as $file_data ) {
				if ( isset( $file_data['filepath'] ) && file_exists( $file_data['filepath'] ) ) {
					wp_delete_file( $file_data['filepath'] );
				}
			}

			wp_send_json_error(
				array(
					'message' => __( 'Quiz attempt was not saved correctly. Please try again.', 'skillpulse-lms' ),
				)
			);
		}

		// Fire quiz completion action (for both passed and failed).
		// Use percentage from evaluation which is already calculated correctly.
		do_action( 'splms_quiz_completed', $user_id, $quiz_id, $percentage );

		// Get quiz type.
		$quiz_type = $this->get_setting_value( $settings, 'quiz_type', 'graded' );

		// If quiz is passed, update course progress (only for graded quizzes).
		// Practice and survey quizzes do not affect course progress.
		if ( $passed && 'graded' === $quiz_type ) {
			$course_id = splms_get_quiz_course( $quiz_id );
			if ( $course_id ) {
				$progress_data = $this->calculate_course_progress( $user_id, $course_id );

				// Sync progress to enrollment database table.
				$database   = SkillPulse_LMS_Database::get_instance();
				$enrollment = SkillPulse_LMS_Enrollment::get_instance();
				$enrollment->update_enrollment_progress( $user_id, $course_id, $progress_data['percentage'] );

				// Log activity.
				SkillPulse_LMS_User_Activity_Query::get_instance()->log_activity( $user_id, 'quiz_completed', $course_id, $quiz_id, 'quiz' );
			}
		} elseif ( 'practice' === $quiz_type || 'survey' === $quiz_type ) {
			// Log activity for practice/survey quizzes (for tracking, but doesn't affect progress).
			$course_id = splms_get_quiz_course( $quiz_id );
			if ( $course_id ) {
				SkillPulse_LMS_User_Activity_Query::get_instance()->log_activity( $user_id, 'quiz_completed', $course_id, $quiz_id, 'quiz' );
			}
		}

		// Get attempts count and remaining attempts.
		// Count ALL completed attempts (including the one just completed).
		$attempts_used      = $attempts_query->count_completed_attempts( $user_id, $quiz_id );
		$max_attempts       = $this->get_setting_value( $settings, 'max_attempts', 3 );
		$attempts_remaining = max( 0, intval( $max_attempts ) - $attempts_used );

		// Get results timing setting and check if results should be shown.
		$results_timing      = $this->get_setting_value( $settings, 'results_timing', 'after_passing' );
		$should_show_results = false;
		if ( 'immediately' === $results_timing ) {
			$should_show_results = true;
		} elseif ( 'after_passing' === $results_timing ) {
			// For status-based logic: show results if graded and passed, or if pending review (partial results).
			$should_show_results = ( 'graded' === $new_status && $passed ) || 'pending_review' === $new_status;
		} elseif ( 'manual' === $results_timing ) {
			// Check if instructor has manually released results.
			$should_show_results = get_post_meta( $quiz_id, '_splms_results_released', true ) === 'yes';
		}

		// Prepare response.
		$response = array(
			'success'              => true,
			'attempt_id'           => $attempt_id, // Include attempt_id for verification.
			'status'               => $new_status, // Current attempt status.
			'passed'               => $passed,
			'percentage'           => $percentage,
			'correct_answers'      => $correct_answers,
			'total_questions'      => $total_questions,
			'points_earned'        => $points_earned,
			'total_points'         => $total_points,
			'time_taken'           => $time_taken,
			'passing_grade'        => $passing_grade,
			'attempts_remaining'   => $attempts_remaining,
			'attempts_used'        => $attempts_used,
			'max_attempts'         => intval( $max_attempts ),
			'quiz_type'            => $quiz_type, // Add quiz type to response.
			'show_answers'         => $this->get_setting_value( $settings, 'show_correct_answers', true ),
			'show_answers_timing'  => $this->get_setting_value( $settings, 'show_correct_answers_timing', 'after_completion' ),
			'results_timing'       => $results_timing,
			'should_show_results'  => $should_show_results,
			'feedback_enabled'     => $this->get_setting_value( $settings, 'enable_feedback', false ),
			'feedback_correct'     => $this->get_setting_value( $settings, 'feedback_correct', 'Correct! Well done.' ),
			'feedback_incorrect'   => $this->get_setting_value( $settings, 'feedback_incorrect', 'Incorrect. Please review the material and try again.' ),
			'detailed_results'     => $detailed_results,
			// Essay/File Upload question handling.
			'pending_review'       => $pending_review,
			'has_manual_review'    => $has_manual_review,
			'manual_review_points' => $manual_review_points,
		);

		// Add progress data if quiz passed.
		if ( $passed && isset( $progress_data ) ) {
			$response['progress'] = $progress_data;
		}

		// Commit transaction on success.
		$wpdb->query( 'COMMIT' );

		wp_send_json_success( $response );
	}

	/**
	 * Handle guest quiz attempt submission.
	 */
	public function handle_submit_guest_quiz_attempt() {
		// Verify nonce.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Nonce verification and sanitization handled by wp_verify_nonce().
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'splms_nonce' ) ) {
			wp_send_json_error( 'Security check failed' );
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce already verified above.
		$quiz_id   = isset( $_POST['quiz_id'] ) ? intval( wp_unslash( $_POST['quiz_id'] ) ) : 0; // phpcs:ignore Generic.Formatting.MultipleStatementAlignment.NotSameWarning -- Variable assignments don't need alignment.
		$course_id = isset( $_POST['course_id'] ) ? intval( wp_unslash( $_POST['course_id'] ) ) : 0; // phpcs:ignore Generic.Formatting.MultipleStatementAlignment.NotSameWarning -- Variable assignments don't need alignment.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,Generic.Formatting.MultipleStatementAlignment.NotSameWarning -- Nonce verified above, answers will be sanitized during processing.
		$answers_raw = isset( $_POST['answers'] ) ? wp_unslash( $_POST['answers'] ) : '';
		// Answers can be sent as JSON string or array - decode if needed.
		if ( is_string( $answers_raw ) ) {
			$answers = json_decode( $answers_raw, true );
			if ( ! is_array( $answers ) ) {
				$answers = array();
			}
		} else {
			$answers = is_array( $answers_raw ) ? $answers_raw : array();
		}
		$time_taken = isset( $_POST['time_taken'] ) ? intval( wp_unslash( $_POST['time_taken'] ) ) : 0; // phpcs:ignore Generic.Formatting.MultipleStatementAlignment.NotSameWarning -- Variable assignments don't need alignment.

		if ( ! $quiz_id ) {
			wp_send_json_error( 'Invalid quiz ID' );
		}

		// Check if quiz is available for preview mode.
		$access_control = SkillPulse_LMS_Access_Control::get_instance();
		if ( ! $access_control->is_guest_preview_quiz( $quiz_id ) ) {
			wp_send_json_error( 'Quiz not available for preview' );
		}

		// Get quiz questions and settings for server-side evaluation.
		$questions = $this->get_quiz_questions( $quiz_id );
		$settings  = $this->get_quiz_settings( $quiz_id );

		// Get quiz type (get it early, before evaluation adjustments).
		$quiz_type = $this->get_setting_value( $settings, 'quiz_type', 'graded' );

		// Adjust default behavior based on quiz type.
		if ( 'practice' === $quiz_type ) {
			// Practice quizzes: Show answers immediately, allow unlimited attempts.
			if ( ! isset( $settings['show_correct_answers'] ) ) {
				$settings['show_correct_answers'] = true;
			}
			if ( ! isset( $settings['show_correct_answers_timing'] ) ) {
				$settings['show_correct_answers_timing'] = 'after_completion';
			}
			if ( ! isset( $settings['max_attempts'] ) || 0 === $settings['max_attempts'] ) {
				$settings['max_attempts'] = 0; // Unlimited for practice.
			}
		} elseif ( 'survey' === $quiz_type ) {
			// Surveys: No pass/fail, no scoring.
			if ( ! isset( $settings['passing_grade'] ) ) {
				$settings['passing_grade'] = 0; // No passing grade for surveys.
			}
			if ( ! isset( $settings['show_correct_answers'] ) ) {
				$settings['show_correct_answers'] = false; // No "correct" answers in surveys.
			}
		}

		if ( empty( $questions ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'No questions found for this quiz. Please contact the instructor.', 'skillpulse-lms' ),
				)
			);
		}

		// Evaluate answers server-side using the same evaluator as logged-in users.
		$passing_grade = $this->get_setting_value( $settings, 'passing_grade', 70 );

		$evaluation = SkillPulse_LMS_Quiz_Evaluator::evaluate_attempt( $quiz_id, $questions, $answers, $passing_grade );

		$correct_answers      = $evaluation['correct_answers'];
		$total_questions      = $evaluation['total_questions'];
		$points_earned        = isset( $evaluation['points_earned'] ) ? $evaluation['points_earned'] : 0;
		$total_points         = isset( $evaluation['total_points'] ) ? $evaluation['total_points'] : $total_questions;
		$percentage           = $evaluation['percentage'];
		$passed               = $evaluation['passed'];
		$detailed_results     = $evaluation['detailed_results'];
		$pending_review       = isset( $evaluation['pending_review'] ) ? $evaluation['pending_review'] : false;
		$has_manual_review    = isset( $evaluation['has_manual_review'] ) ? $evaluation['has_manual_review'] : false;
		$manual_review_points = isset( $evaluation['manual_review_points'] ) ? $evaluation['manual_review_points'] : 0.0;

		// Get quiz type (get it early, before evaluation adjustments).
		$quiz_type = $this->get_setting_value( $settings, 'quiz_type', 'graded' );

		// After evaluation, adjust for survey quizzes.
		if ( 'survey' === $quiz_type ) {
			// Surveys don't have pass/fail - all responses are accepted.
			$passed     = true; // Always "passed" for surveys (no evaluation).
			$percentage = 100; // Set to 100% for display purposes.
		}

		// Add feedback to detailed_results if enabled.
		$enable_feedback    = $this->get_setting_value( $settings, 'enable_feedback', false );
		$feedback_correct   = $this->get_setting_value( $settings, 'feedback_correct', 'Correct! Well done.' );
		$feedback_incorrect = $this->get_setting_value( $settings, 'feedback_incorrect', 'Incorrect. Please review the material and try again.' );
		if ( $enable_feedback && is_array( $detailed_results ) ) {
			foreach ( $detailed_results as &$result ) {
				if ( ! isset( $result['feedback'] ) ) {
					$result['feedback'] = isset( $result['is_correct'] ) && $result['is_correct'] ? $feedback_correct : $feedback_incorrect;
				}
			}
			unset( $result ); // Break reference.
		}

		// Get results timing setting and check if results should be shown.
		$results_timing      = $this->get_setting_value( $settings, 'results_timing', 'after_passing' );
		$should_show_results = false;
		if ( 'immediately' === $results_timing ) {
			$should_show_results = true;
		} elseif ( 'after_passing' === $results_timing ) {
			// For guest attempts: show results if passed OR if has manual review questions (partial results).
			$should_show_results = $passed || $pending_review;
		} elseif ( 'manual' === $results_timing ) {
			// Check if instructor has manually released results.
			$should_show_results = get_post_meta( $quiz_id, '_splms_results_released', true ) === 'yes';
		}

		// Check Guest Quiz Storage Mode setting.
		$course_settings = splms_get_course_settings( $course_id );
		$storage_mode    = 'save_as_guest'; // Default.
		if ( isset( $course_settings['course_access_settings']['guest_quiz_storage_mode'] ) ) {
			$storage_mode = $course_settings['course_access_settings']['guest_quiz_storage_mode'];
		} elseif ( isset( $course_settings['guest_quiz_storage_mode'] ) ) {
			$storage_mode = $course_settings['guest_quiz_storage_mode'];
		}

		// If storage mode is 'no_storage', return results without saving to database.
		if ( 'no_storage' === $storage_mode ) {
			$response = array(
				'success'              => true,
				'attempt_id'           => null,
				'passed'               => $passed,
				'percentage'           => $percentage,
				'correct_answers'      => $correct_answers,
				'total_questions'      => $total_questions,
				'points_earned'        => $points_earned,
				'total_points'         => $total_points,
				'time_taken'           => $time_taken,
				'is_guest_attempt'     => true,
				'is_preview_mode'      => true,
				'passing_grade'        => $passing_grade,
				'quiz_type'            => $quiz_type, // Add quiz type to response.
				'show_answers'         => $this->get_setting_value( $settings, 'show_correct_answers', true ),
				'show_answers_timing'  => $this->get_setting_value( $settings, 'show_correct_answers_timing', 'after_completion' ),
				'results_timing'       => $results_timing,
				'should_show_results'  => $should_show_results,
				'feedback_enabled'     => $enable_feedback,
				'feedback_correct'     => $feedback_correct,
				'feedback_incorrect'   => $feedback_incorrect,
				'detailed_results'     => $detailed_results,
				'pending_review'       => $pending_review,
				'has_manual_review'    => $has_manual_review,
				'manual_review_points' => $manual_review_points,
				'message'              => __( 'This was a preview attempt. Results are not saved.', 'skillpulse-lms' ),
			);

			wp_send_json_success( $response );
		}

		// Storage mode is 'save_as_guest' - save to database.
		// Create guest attempt record.
		$attempts_query = SkillPulse_LMS_Quiz_Attempts_Query::get_instance();
		$attempt_id     = $attempts_query->start_attempt( 0, $quiz_id, $course_id ); // User ID 0 for guest.

		if ( ! $attempt_id ) {
			wp_send_json_error( 'Failed to create guest attempt' );
		}

		// Complete the guest attempt with server-evaluated results.
		$completed = $attempts_query->complete_attempt(
			$attempt_id,
			$answers,
			$points_earned,
			$total_points,
			$passed,
			$time_taken
		);

		if ( ! $completed ) {
			wp_send_json_error( 'Failed to save guest attempt' );
		}

		// Prepare response.
		$response = array(
			'success'              => true,
			'attempt_id'           => $attempt_id,
			'passed'               => $passed,
			'percentage'           => $percentage,
			'correct_answers'      => $correct_answers,
			'total_questions'      => $total_questions,
			'points_earned'        => $points_earned,
			'total_points'         => $total_points,
			'time_taken'           => $time_taken,
			'is_guest_attempt'     => true,
			'is_preview_mode'      => true,
			'passing_grade'        => $passing_grade,
			'quiz_type'            => $quiz_type, // Add quiz type to response.
			'show_answers'         => $this->get_setting_value( $settings, 'show_correct_answers', true ),
			'show_answers_timing'  => $this->get_setting_value( $settings, 'show_correct_answers_timing', 'after_completion' ),
			'results_timing'       => $results_timing,
			'should_show_results'  => $should_show_results,
			'feedback_enabled'     => $enable_feedback,
			'feedback_correct'     => $feedback_correct,
			'feedback_incorrect'   => $feedback_incorrect,
			'detailed_results'     => $detailed_results,
			'pending_review'       => $pending_review,
			'has_manual_review'    => $has_manual_review,
			'manual_review_points' => $manual_review_points,
			'message'              => __( 'This was a preview attempt. Results have been saved as guest attempt.', 'skillpulse-lms' ),
		);

		wp_send_json_success( $response );
	}

	/**
	 * Handle get quiz attempts AJAX request.
	 */
	public function handle_get_quiz_attempts() {
		// Verify nonce.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Nonce verification and sanitization handled by wp_verify_nonce().
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'splms_nonce' ) ) {
			wp_send_json_error( 'Security check failed' );
		}

		$user_id = get_current_user_id();
		$quiz_id = isset( $_POST['quiz_id'] ) ? intval( wp_unslash( $_POST['quiz_id'] ) ) : 0;
		if ( ! $quiz_id ) {
			wp_send_json_error( 'Invalid quiz ID' );
		}

		// Check if quiz is available for preview mode (for guest users).
		$access_control = SkillPulse_LMS_Access_Control::get_instance();
		if ( ! $user_id ) {
			// Guest user - check if quiz is available for preview.
			if ( ! $access_control->is_guest_preview_quiz( $quiz_id ) ) {
				wp_send_json_error( 'Quiz not available for preview' );
			}
			// Set user_id to 0 for guest attempts.
			$user_id = 0;
		}

		// Use the same consistent formatting method as REST API and templates.
		$formatted_attempts = $this->get_formatted_quiz_attempts( $user_id, $quiz_id, true );

		wp_send_json_success( $formatted_attempts );
	}

	/**
	 * Calculate course progress using database tables only.
	 *
	 * @param int $user_id   User ID.
	 * @param int $course_id Course ID.
	 *
	 * @return array Course progress data.
	 */
	public function calculate_course_progress( $user_id, $course_id ) {
		global $wpdb;

		// Get completed lessons from lesson_progress table.
		$lesson_progress_table = esc_sql( $wpdb->prefix . 'splms_lesson_progress' );
		$completed_lessons     = $wpdb->get_var(
			$wpdb->prepare(
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table name cannot be prepared.
				"SELECT COUNT(*) FROM {$lesson_progress_table} WHERE user_id = %d AND course_id = %d AND is_completed = 1",
				$user_id,
				$course_id
			)
		);

		// Get passed quizzes from quiz_attempts table (only graded quizzes).
		$quiz_attempts_table = esc_sql( $wpdb->prefix . 'splms_quiz_attempts' );
		// Get all passed quiz IDs first.
		$passed_quiz_ids = $wpdb->get_col(
			$wpdb->prepare(
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table name cannot be prepared.
				"SELECT DISTINCT quiz_id FROM {$quiz_attempts_table} WHERE user_id = %d AND course_id = %d AND passed = 1",
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
	 * @return int Total course items count.
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

				// Count lessons and quizzes.
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
	 * Upload quiz file using File Manager.
	 *
	 * @param array $file        $_FILES array for the uploaded file.
	 * @param int   $quiz_id     Quiz ID.
	 * @param int   $attempt_id  Attempt ID.
	 * @param int   $question_id Question ID.
	 *
	 * @since 1.0.0
	 * @return array|WP_Error File information on success, WP_Error on failure
	 */
	private function upload_quiz_file( $file, $quiz_id, $attempt_id, $question_id ) {
		// Security validation first.
		$security_check = $this->validate_upload_security( $file );
		if ( is_wp_error( $security_check ) ) {
			return $security_check;
		}

		// Create directory structure: quiz-attempt/quiz_id/attempt_id/question_id/.
		$feature_name = 'quiz-attempt';
		$sub_dir      = sanitize_file_name( $quiz_id ) . '/' . sanitize_file_name( $attempt_id ) . '/' . sanitize_file_name( $question_id );

		// Get feature directory.
		$dir_info = SkillPulse_LMS_File_Manager::get_feature_dir( $feature_name, true );
		if ( is_wp_error( $dir_info ) ) {
			return $dir_info;
		}

		// Create subdirectory structure.
		$full_sub_dir = trailingslashit( $dir_info['path'] ) . $sub_dir;
		if ( ! file_exists( $full_sub_dir ) ) {
			if ( ! wp_mkdir_p( $full_sub_dir ) ) {
				return new WP_Error(
					'dir_creation_failed',
					/* translators: %s: Directory path. */
					sprintf( __( 'Failed to create directory: %s', 'skillpulse-lms' ), $full_sub_dir ),
					array( 'path' => $full_sub_dir )
				);
			}

			// Create index.php for security.
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents -- Creating security file in uploads directory.
			file_put_contents( trailingslashit( $full_sub_dir ) . 'index.php', "<?php\n// Silence is golden\n" );
		}

		// Generate unique filename to avoid conflicts.
		$original_name   = sanitize_file_name( $file['name'] );
		$file_ext        = strtolower( pathinfo( $original_name, PATHINFO_EXTENSION ) );
		$file_basename   = pathinfo( $original_name, PATHINFO_FILENAME );
		$unique_filename = $file_basename . '_' . time() . '_' . wp_generate_password( 8, false ) . '.' . $file_ext;
		$target_path     = trailingslashit( $full_sub_dir ) . $unique_filename;

		// Move uploaded file to custom quiz uploads directory using WP Filesystem.
		global $wp_filesystem;
		if ( ! function_exists( 'WP_Filesystem' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}
		if ( ! WP_Filesystem() ) {
			return new WP_Error(
				'filesystem_error',
				__( 'Could not initialize WordPress filesystem.', 'skillpulse-lms' )
			);
		}

		$file_contents = $wp_filesystem->get_contents( $file['tmp_name'] );
		if ( false === $file_contents || ! $wp_filesystem->put_contents( $target_path, $file_contents, 0644 ) ) {
			return new WP_Error(
				'file_upload_failed',
				__( 'Failed to move uploaded file.', 'skillpulse-lms' ),
				array( 'file' => $file['name'] )
			);
		}
		wp_delete_file( $file['tmp_name'] );

		// Build file URL.
		$file_url = trailingslashit( $dir_info['url'] ) . $sub_dir . '/' . $unique_filename;

		return array(
			'filepath'          => $target_path,
			'fileurl'           => $file_url,
			'url'               => $file_url, // Alias for consistency.
			'filename'          => $unique_filename,
			'original_filename' => $original_name,
			'size'              => filesize( $target_path ),
			'feature'           => $feature_name,
		);
	}

	/**
	 * Recalculate attempt score from scratch.
	 *
	 * This method recalculates the total score by:
	 * 1. Getting all quiz questions
	 * 2. Re-evaluating auto-graded questions
	 * 3. Adding manually graded points from _graded_scores
	 * 4. Calculating percentage and pass/fail status
	 *
	 * @param int   $attempt_id      Attempt ID.
	 * @param array $updated_answers Optional. Updated answers array (if already modified). If not provided, reads from database.
	 *
	 * @since 1.0.0
	 *
	 * @return array|WP_Error Score calculation result or error.
	 */
	public function recalculate_attempt_score( $attempt_id, $updated_answers = null ) {
		$attempts_query = SkillPulse_LMS_Quiz_Attempts_Query::get_instance();
		$attempt        = $attempts_query->get_attempt_by_id( $attempt_id );

		if ( ! $attempt ) {
			return new WP_Error( 'attempt_not_found', __( 'Quiz attempt not found.', 'skillpulse-lms' ), array( 'status' => 404 ) );
		}

		// Get quiz questions and settings.
		$questions     = $this->get_quiz_questions( $attempt->quiz_id, true );
		$quiz_settings = $this->get_quiz_settings( $attempt->quiz_id );
		$passing_grade = floatval( $this->get_setting_value( $quiz_settings, 'passing_grade', 70 ) );

		// Get user answers and graded scores.
		if ( null !== $updated_answers && is_array( $updated_answers ) ) {
			$user_answers = $updated_answers;
		} else {
			$user_answers = json_decode( $attempt->answers, true );
			if ( ! is_array( $user_answers ) ) {
				$user_answers = array();
			}
		}
		$graded_scores = isset( $user_answers['_graded_scores'] ) && is_array( $user_answers['_graded_scores'] )
			? $user_answers['_graded_scores']
			: array();

		// Calculate total score from scratch.
		// IMPORTANT: max_score should always include ALL questions (total possible points).
		// Pending questions (not yet graded) contribute 0 to total_score but are included in max_score.
		$total_score     = 0.0;
		$total_max_score = 0.0;

		foreach ( $questions as $question ) {
			// Use question_id if available, otherwise fall back to id.
			$question_id     = isset( $question['question_id'] ) ? (string) $question['question_id'] : (string) $question['id'];
			$question_points = floatval( $question['points'] );
			$question_type   = $question['type'];

			// Always include all questions in max_score (total possible points).
			$total_max_score += $question_points;

			// Check if this is a manually graded question.
			$is_manual_review = in_array( $question_type, array( 'essay', 'long_answer', 'file_upload' ), true );

			if ( $is_manual_review ) {
				// Use graded score if available, otherwise 0 (pending review).
				// Check both string and integer keys for compatibility.
				$graded_score = null;
				if ( isset( $graded_scores[ $question_id ] ) ) {
					$graded_score = $graded_scores[ $question_id ];
				} elseif ( isset( $graded_scores[ intval( $question_id ) ] ) ) {
					$graded_score = $graded_scores[ intval( $question_id ) ];
				}

				if ( null !== $graded_score ) {
					// Question has been graded - add points to total_score.
					$points_awarded = floatval( $graded_score );
					$points_awarded = min( max( $points_awarded, 0 ), $question_points ); // Clamp between 0 and max.
					$total_score   += $points_awarded;
				}
				// If not graded yet, it contributes 0 to total_score (pending review).
			} else {
				// Auto-graded question - re-evaluate it.
				$user_answer = isset( $user_answers[ $question_id ] ) ? $user_answers[ $question_id ] : '';
				$result      = SkillPulse_LMS_Quiz_Evaluator::evaluate_question( $question, $user_answer );

				if ( isset( $result['score'] ) ) {
					$total_score += floatval( $result['score'] );
				}
			}
		}

		// Calculate percentage and pass/fail.
		$percentage = $total_max_score > 0 ? round( ( $total_score / $total_max_score ) * 100, 2 ) : 0;
		$passed     = $percentage >= $passing_grade ? 1 : 0;

		return array(
			'score'      => $total_score,
			'max_score'  => $total_max_score,
			'percentage' => $percentage,
			'passed'     => $passed,
		);
	}

	/**
	 * Check rate limit before processing request.
	 *
	 * @param string $action Action name.
	 * @param int    $user_id User ID.
	 * @param int    $limit Maximum requests allowed.
	 * @param int    $window Time window in seconds.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if within limit, false if exceeded.
	 */
	public function check_rate_limit( $action, $user_id, $limit = 10, $window = 60 ) {
		$transient_key = 'splms_rate_limit_' . $action . '_' . $user_id;
		$requests      = get_transient( $transient_key );

		if ( false === $requests ) {
			// First request in window.
			set_transient( $transient_key, 1, $window );
			return true;
		} elseif ( $requests < $limit ) {
			// Within limit.
			set_transient( $transient_key, $requests + 1, $window );
			return true;
		} else {
			// Exceeded limit.
			return false;
		}
	}

	/**
	 * Schedule cron job for cleaning up abandoned quiz attempts.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function schedule_cleanup_cron() {
		if ( ! wp_next_scheduled( 'splms_cleanup_abandoned_attempts' ) ) {
			wp_schedule_event( time(), 'daily', 'splms_cleanup_abandoned_attempts' );
		}
	}

	/**
	 * Run cleanup of abandoned quiz attempts.
	 * Deletes in-progress attempts older than 7 days.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function run_cleanup_abandoned_attempts() {
		$attempts_query = SkillPulse_LMS_Quiz_Attempts_Query::get_instance();
		$attempts_query->cleanup_abandoned_attempts( 7 );
	}

	/**
	 * Get essay question IDs for a quiz.
	 * Returns questions that require manual grading.
	 *
	 * @param int $quiz_id Quiz ID.
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of question IDs (as strings for consistency).
	 */
	private function get_quiz_essay_questions( $quiz_id ) {
		static $cache = array();

		if ( isset( $cache[ $quiz_id ] ) ) {
			return $cache[ $quiz_id ];
		}

		$questions = $this->get_quiz_questions( $quiz_id, false );
		$essay_ids = array();

		foreach ( $questions as $question ) {

			// Only essay, long_answer, and file_upload require manual grading.
			if ( in_array( $question['type'], array( 'essay', 'long_answer', 'file_upload' ), true ) ) {
				$essay_ids[] = (string) $question['id'];
			}
		}

		$cache[ $quiz_id ] = $essay_ids;

		return $essay_ids;
	}

	/**
	 * Check if an answer value is considered empty.
	 *
	 * @param mixed $answer_value Answer value to check.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if empty, false otherwise.
	 */
	private function is_answer_empty( $answer_value ) {
		if ( is_array( $answer_value ) ) {
			return empty( $answer_value ) || 0 === count( $answer_value );
		}

		return empty( $answer_value )
			|| '' === trim( (string) $answer_value )
			|| '[]' === $answer_value
			|| '{}' === $answer_value;
	}

	/**
	 * Calculate attempt status (pending review, has essay answers).
	 *
	 * @param object $attempt           Attempt object from database.
	 * @param array  $essay_question_ids Essay question IDs.
	 *
	 * @since 1.0.0
	 *
	 * @return array Status data with 'pending_review' and 'has_essay_answers' keys.
	 */
	private function calculate_attempt_status( $attempt, $essay_question_ids ) {
		$pending_review    = false;
		$has_essay_answers = false;

		// No essay questions = cannot be pending review.
		if ( empty( $essay_question_ids ) ) {
			return array(
				'pending_review'    => false,
				'has_essay_answers' => false,
			);
		}

		$answers       = is_array( $attempt->answers ) ? $attempt->answers : array();
		$graded_scores = isset( $answers['_graded_scores'] ) && is_array( $answers['_graded_scores'] )
			? $answers['_graded_scores']
			: array();

		// Check which manual-grading questions were NOT answered.
		$unanswered_manual_questions = array();
		foreach ( $essay_question_ids as $essay_qid ) {
			$essay_qid_int = intval( $essay_qid );
			if ( ! isset( $answers[ $essay_qid ] ) && ! isset( $answers[ $essay_qid_int ] ) ) {
				$unanswered_manual_questions[] = $essay_qid;
			}
		}

		foreach ( $answers as $question_id => $answer_value ) {
			// Skip meta keys.
			if ( '_graded_scores' === $question_id ) {
				continue;
			}

			$normalized_qid  = (string) $question_id;
			$question_id_int = intval( $question_id );

			// Check if this is an essay question.
			if ( in_array( $normalized_qid, $essay_question_ids, true ) ) {
				// Check if answer is not empty.
				$is_empty = $this->is_answer_empty( $answer_value );

				if ( ! $is_empty ) {
					$has_essay_answers = true;

					// Check if graded.
					$is_graded = isset( $graded_scores[ $normalized_qid ] )
						|| isset( $graded_scores[ $question_id_int ] );

					if ( ! $is_graded ) {
						$pending_review = true;
						break;
					}
				}
			}
		}

		return array(
			'pending_review'    => $pending_review,
			'has_essay_answers' => $has_essay_answers,
		);
	}

	/**
	 * Get formatted quiz attempts for a user.
	 * Returns attempts with calculated status, ready for template or API.
	 *
	 * This method centralizes all attempt formatting logic so templates and
	 * REST API endpoints can use the same data structure and business rules.
	 *
	 * @param int  $user_id         User ID.
	 * @param int  $quiz_id         Quiz ID.
	 * @param bool $include_answers Whether to include full answers (default: false).
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of formatted attempt objects.
	 */
	public function get_formatted_quiz_attempts( $user_id, $quiz_id, $include_answers = false ) {
		// Get completed attempts from query class.
		$attempts_query     = SkillPulse_LMS_Quiz_Attempts_Query::get_instance();
		$completed_attempts = $attempts_query->get_completed_attempts( $user_id, $quiz_id );

		if ( empty( $completed_attempts ) ) {
			return array();
		}

		// Get essay question IDs (cached).
		$essay_question_ids = $this->get_quiz_essay_questions( $quiz_id );

		// Format each attempt.
		$formatted_attempts = array();

		foreach ( $completed_attempts as $attempt ) {
			// Calculate percentage.
			$max_score  = floatval( $attempt->max_score );
			$score      = floatval( $attempt->score );
			$percentage = $max_score > 0 ? round( ( $score / $max_score ) * 100, 2 ) : 0;

			// Use status field directly.
			$status = ! empty( $attempt->status ) ? $attempt->status : 'draft';

			// Determine legacy fields for backward compatibility in API responses.
			$pending_review = 'pending_review' === $status;
			$passed         = false;
			if ( 'graded' === $status ) {
				// For graded attempts, determine pass/fail based on percentage.
				$passing_grade = isset( $attempt->passing_grade ) ? floatval( $attempt->passing_grade ) : 70;
				$passed        = $percentage >= $passing_grade;
			}

			// Build formatted attempt.
			$formatted_attempt = array(
				'id'             => $attempt->id,
				'date'           => $attempt->attempt_time,
				'score'          => $score,
				'max_score'      => $max_score,
				'percentage'     => $percentage,
				'passed'         => $passed,
				'pending_review' => $pending_review,
				'status_label'   => $status,
				'status'         => $status,
				'time_taken'     => intval( $attempt->time_taken ),
			);

			// Optionally include answers.
			if ( $include_answers ) {
				$formatted_attempt['answers'] = is_array( $attempt->answers ) ? $attempt->answers : array();
			}

			$formatted_attempts[] = $formatted_attempt;
		}

		return $formatted_attempts;
	}


	/**
	 * Register quiz attempts REST API endpoint for frontend (user-specific).
	 *
	 * NOTE: This endpoint is for FRONTEND use to get quiz attempts for the current user for a specific quiz.
	 * For ADMIN use (getting all quiz attempts with filtering), use the REST controller at /splms/v1/quiz-attempts.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register_quiz_attempts_api() {
		// Changed path from '/quiz-attempts' to '/user/quiz-attempts' to avoid conflict with admin REST controller.
		register_rest_route(
			'splms/v1',
			'/user/quiz-attempts',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'handle_get_quiz_attempts_rest' ),
				'permission_callback' => function () {
					return is_user_logged_in();
				},
				'args'                => array(
					'quiz_id'         => array(
						'required'          => true,
						'validate_callback' => function ( $param ) {
							return is_numeric( $param );
						},
						'sanitize_callback' => 'absint',
					),
					'include_answers' => array(
						'required'          => false,
						'default'           => false,
						'validate_callback' => function ( $param ) {
							return is_bool( $param ) || in_array( $param, array( 'true', 'false', '0', '1' ), true );
						},
						'sanitize_callback' => 'rest_sanitize_boolean',
					),
				),
			)
		);
	}

	/**
	 * Handle REST API request for quiz attempts.
	 *
	 * @param WP_REST_Request $request REST request.
	 *
	 * @since 1.0.0
	 *
	 * @return WP_REST_Response
	 */
	public function handle_get_quiz_attempts_rest( $request ) {
		$quiz_id         = $request->get_param( 'quiz_id' );
		$include_answers = $request->get_param( 'include_answers' );
		$user_id         = get_current_user_id();

		// Use the SAME method as template.
		$attempts = $this->get_formatted_quiz_attempts( $user_id, $quiz_id, $include_answers );

		return rest_ensure_response(
			array(
				'success'  => true,
				'attempts' => $attempts,
				'count'    => count( $attempts ),
			)
		);
	}

	/**
	 * Extract default values from quiz config data.
	 *
	 * @since 1.0.0
	 *
	 * @param array $config_data Quiz configuration data.
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

	/**
	 * Handle get quiz progress AJAX request.
	 */
	public function handle_get_quiz_progress() {
		// Verify nonce.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Nonce verification and sanitization handled by wp_verify_nonce().
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'splms_nonce' ) ) {
			wp_send_json_error( 'Security check failed' );
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce already verified above.
		$quiz_id = isset( $_POST['quiz_id'] ) ? intval( wp_unslash( $_POST['quiz_id'] ) ) : 0;
		// phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Nonce verified above, answers will be sanitized during processing.
		$answers = isset( $_POST['answers'] ) ? wp_unslash( $_POST['answers'] ) : array();

		if ( ! $quiz_id ) {
			wp_send_json_error( 'Invalid quiz ID' );
		}

		// Get quiz questions.
		$questions = $this->get_quiz_questions( $quiz_id, false );
		if ( empty( $questions ) ) {
			wp_send_json_error( 'No questions found for quiz' );
		}

		// Calculate progress.
		$navigation = SkillPulse_LMS_Quiz_Navigation::get_instance();
		$progress   = $navigation->calculate_progress( $questions, $answers );

		wp_send_json_success( $progress );
	}

	/**
	 * Handle toggle bookmark AJAX request.
	 */
	public function handle_toggle_bookmark() {
		// Verify nonce.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Nonce verification and sanitization handled by wp_verify_nonce().
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'splms_nonce' ) ) {
			wp_send_json_error( 'Security check failed' );
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce already verified above.
		$question_id = isset( $_POST['question_id'] ) ? intval( wp_unslash( $_POST['question_id'] ) ) : 0;
		// phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Nonce verified above, bookmarks will be processed.
		$bookmarks = isset( $_POST['bookmarks'] ) ? wp_unslash( $_POST['bookmarks'] ) : array();

		if ( ! $question_id ) {
			wp_send_json_error( 'Invalid question ID' );
		}

		// Ensure bookmarks is an array.
		if ( ! is_array( $bookmarks ) ) {
			$bookmarks = array();
		}

		// Toggle bookmark.
		$navigation = SkillPulse_LMS_Quiz_Navigation::get_instance();
		$result     = $navigation->toggle_bookmark( $question_id, $bookmarks );

		wp_send_json_success( $result );
	}

	/**
	 * Handle get quiz navigation AJAX request.
	 */
	public function handle_get_quiz_navigation() {
		// Verify nonce.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Nonce verification and sanitization handled by wp_verify_nonce().
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'splms_nonce' ) ) {
			wp_send_json_error( 'Security check failed' );
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce already verified above.
		$quiz_id          = isset( $_POST['quiz_id'] ) ? intval( wp_unslash( $_POST['quiz_id'] ) ) : 0;
		$current_question = isset( $_POST['current_question'] ) ? intval( wp_unslash( $_POST['current_question'] ) ) : 0;
		// phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Nonce verified above, bookmarks will be processed.
		$bookmarks = isset( $_POST['bookmarks'] ) ? wp_unslash( $_POST['bookmarks'] ) : array();

		if ( ! $quiz_id ) {
			wp_send_json_error( 'Invalid quiz ID' );
		}

		// Ensure bookmarks is an array.
		if ( ! is_array( $bookmarks ) ) {
			$bookmarks = array();
		}

		// Get quiz questions.
		$questions = $this->get_quiz_questions( $quiz_id, false );
		if ( empty( $questions ) ) {
			wp_send_json_error( 'No questions found for quiz' );
		}

		// Get navigation data.
		$navigation      = SkillPulse_LMS_Quiz_Navigation::get_instance();
		$navigation_data = $navigation->get_navigation_data( $questions, $current_question, $bookmarks );

		wp_send_json_success( $navigation_data );
	}

	/**
	 * Handle get review summary AJAX request.
	 */
	public function handle_get_review_summary() {
		// Verify nonce.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Nonce verification and sanitization handled by wp_verify_nonce().
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'splms_nonce' ) ) {
			wp_send_json_error( 'Security check failed' );
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce already verified above.
		$quiz_id = isset( $_POST['quiz_id'] ) ? intval( wp_unslash( $_POST['quiz_id'] ) ) : 0;
		// phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Nonce verified above, answers and bookmarks will be processed.
		$answers = isset( $_POST['answers'] ) ? wp_unslash( $_POST['answers'] ) : array();
		// phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Nonce verified above, bookmarks will be processed.
		$bookmarks = isset( $_POST['bookmarks'] ) ? wp_unslash( $_POST['bookmarks'] ) : array();

		if ( ! $quiz_id ) {
			wp_send_json_error( 'Invalid quiz ID' );
		}

		// Ensure arrays are proper format.
		if ( ! is_array( $answers ) ) {
			$answers = array();
		}
		if ( ! is_array( $bookmarks ) ) {
			$bookmarks = array();
		}

		// Get quiz questions.
		$questions = $this->get_quiz_questions( $quiz_id, false );
		if ( empty( $questions ) ) {
			wp_send_json_error( 'No questions found for quiz' );
		}

		// Get review summary.
		$navigation = SkillPulse_LMS_Quiz_Navigation::get_instance();
		$summary    = $navigation->get_review_summary( $questions, $answers, $bookmarks );

		wp_send_json_success( $summary );
	}

	/**
	 * Validate file upload security
	 *
	 * @param array $file File data from $_FILES.
	 * @return true|WP_Error True on success, WP_Error on failure.
	 */
	private function validate_upload_security( $file ) {
		// Check file extension.
		$file_ext           = strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );
		$blocked_extensions = array( 'php', 'php3', 'php4', 'php5', 'phtml', 'exe', 'bat', 'cmd', 'com', 'pif', 'scr', 'js', 'vbs', 'jar', 'app', 'sh' );

		if ( in_array( $file_ext, $blocked_extensions, true ) ) {
			return new WP_Error(
				'dangerous_file_type',
				sprintf(
					/* translators: %s: File extension */
					__( 'File type .%s is not allowed for security reasons.', 'skillpulse-lms' ),
					$file_ext
				)
			);
		}

		// Check file size (10MB limit).
		$max_size = 10485760; // 10MB
		if ( $file['size'] > $max_size ) {
			return new WP_Error(
				'file_too_large',
				sprintf(
					/* translators: %s: Maximum file size */
					__( 'File size exceeds maximum limit of %s.', 'skillpulse-lms' ),
					size_format( $max_size )
				)
			);
		}

		// Check for suspicious content in filename.
		$suspicious_patterns = array( '../', '\\', '<script', '<?php', 'eval(', 'base64_decode', 'system(', 'exec(' );
		$filename_lower      = strtolower( $file['name'] );

		foreach ( $suspicious_patterns as $pattern ) {
			if ( false !== strpos( $filename_lower, $pattern ) ) {
				return new WP_Error(
					'suspicious_filename',
					__( 'Filename contains suspicious content.', 'skillpulse-lms' )
				);
			}
		}

		// Basic MIME type validation.
		$allowed_types = array(
			'pdf'  => array( 'application/pdf' ),
			'doc'  => array( 'application/msword' ),
			'docx' => array( 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' ),
			'jpg'  => array( 'image/jpeg', 'image/jpg' ),
			'jpeg' => array( 'image/jpeg', 'image/jpg' ),
			'png'  => array( 'image/png' ),
			'gif'  => array( 'image/gif' ),
			'txt'  => array( 'text/plain' ),
		);

		if ( isset( $allowed_types[ $file_ext ] ) ) {
			$mime_type = $file['type'];
			if ( ! in_array( $mime_type, $allowed_types[ $file_ext ], true ) ) {
				return new WP_Error(
					'mime_type_mismatch',
					__( 'File type does not match file extension.', 'skillpulse-lms' )
				);
			}
		}

		return true;
	}

	/**
	 * Valid quiz attempt statuses.
	 *
	 * @since 1.0.0
	 */
	const VALID_STATUSES = array(
		'draft',
		'in_progress',
		'submitted',
		'pending_review',
		'graded',
		'expired',
		'requires_resubmission',
	);

	/**
	 * Allowed status transitions.
	 *
	 * @since 1.0.0
	 */
	const ALLOWED_TRANSITIONS = array(
		'draft'                 => array( 'in_progress', 'expired' ),
		'in_progress'           => array( 'submitted', 'expired', 'draft' ),
		'submitted'             => array( 'pending_review', 'graded', 'requires_resubmission' ),
		'pending_review'        => array( 'graded', 'requires_resubmission' ),
		'graded'                => array(),
		'expired'               => array( 'draft' ),
		'requires_resubmission' => array( 'in_progress', 'submitted' ),
	);

	/**
	 * Status display messages for users.
	 *
	 * @since 1.0.0
	 */
	const STATUS_MESSAGES = array(
		'draft'                 => 'Quiz not started',
		'in_progress'           => 'Quiz in progress - your answers are being saved automatically',
		'submitted'             => 'Quiz submitted - results will be available shortly',
		'pending_review'        => 'Submitted for instructor review - expect results within 3-5 days',
		'graded'                => 'Quiz completed - view your results below',
		'expired'               => 'Quiz time expired - contact instructor if needed',
		'requires_resubmission' => 'Resubmission required - please review instructor feedback',
	);

	/**
	 * Check if status is valid.
	 *
	 * @param string $status Status to validate.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if status is valid.
	 */
	public function is_valid_status( $status ) {
		return in_array( $status, self::VALID_STATUSES, true );
	}

	/**
	 * Validate status transition.
	 *
	 * @param string $from_status Current status.
	 * @param string $to_status   Target status.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if transition is allowed.
	 */
	public function can_transition( $from_status, $to_status ) {
		if ( ! $this->is_valid_status( $from_status ) || ! $this->is_valid_status( $to_status ) ) {
			return false;
		}

		if ( ! isset( self::ALLOWED_TRANSITIONS[ $from_status ] ) ) {
			return false;
		}

		return in_array( $to_status, self::ALLOWED_TRANSITIONS[ $from_status ], true );
	}

	/**
	 * Get user-friendly status message.
	 *
	 * @param string $status Quiz attempt status.
	 *
	 * @since 1.0.0
	 *
	 * @return string Status message for display.
	 */
	public function get_status_message( $status ) {
		if ( isset( self::STATUS_MESSAGES[ $status ] ) ) {
			return self::STATUS_MESSAGES[ $status ];
		}

		return __( 'Unknown status', 'skillpulse-lms' );
	}

	/**
	 * Update attempt status with validation.
	 *
	 * @param int    $attempt_id Attempt ID.
	 * @param string $new_status New status.
	 * @param array  $meta_data  Additional data (graded_by, feedback, etc.).
	 *
	 * @since 1.0.0
	 *
	 * @return bool|WP_Error True on success, WP_Error on failure.
	 */
	public function update_attempt_status( $attempt_id, $new_status, $meta_data = array() ) {
		global $wpdb;

		if ( ! $this->is_valid_status( $new_status ) ) {
			return new WP_Error(
				'invalid_status',
				sprintf(
					/* translators: %s: Invalid status value */
					__( 'Invalid status: %s', 'skillpulse-lms' ),
					$new_status
				)
			);
		}

		$table_name = esc_sql( $wpdb->prefix . 'splms_quiz_attempts' );

		$attempt = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table_name} WHERE id = %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
				$attempt_id
			)
		);

		if ( ! $attempt ) {
			return new WP_Error(
				'attempt_not_found',
				__( 'Quiz attempt not found.', 'skillpulse-lms' )
			);
		}

		if ( ! $this->can_transition( $attempt->status, $new_status ) ) {
			return new WP_Error(
				'invalid_transition',
				sprintf(
					/* translators: 1: Current status, 2: New status */
					__( 'Cannot transition from %1$s to %2$s.', 'skillpulse-lms' ),
					$attempt->status,
					$new_status
				)
			);
		}

		$update_data = array( 'status' => $new_status );
		$format      = array( '%s' );

		switch ( $new_status ) {
			case 'submitted':
				if ( empty( $attempt->submitted_time ) ) {
					$update_data['submitted_time'] = current_time( 'mysql' );
					$format[]                      = '%s';
				}
				break;

			case 'graded':
				if ( empty( $attempt->graded_time ) ) {
					$update_data['graded_time'] = current_time( 'mysql' );
					$format[]                   = '%s';
				}
				if ( ! empty( $meta_data['graded_by'] ) ) {
					$update_data['graded_by'] = absint( $meta_data['graded_by'] );
					$format[]                 = '%d';
				}
				break;
		}

		if ( ! empty( $meta_data['feedback'] ) ) {
			$update_data['feedback'] = wp_kses_post( $meta_data['feedback'] );
			$format[]                = '%s';
		}

		$result = $wpdb->update(
			$table_name,
			$update_data,
			array( 'id' => $attempt_id ),
			$format,
			array( '%d' )
		);

		if ( false === $result ) {
			return new WP_Error(
				'update_failed',
				__( 'Failed to update quiz attempt status.', 'skillpulse-lms' )
			);
		}

		do_action( 'splms_quiz_status_updated', $attempt_id, $new_status, $attempt->status, $meta_data );

		return true;
	}

	/**
	 * Get attempt status.
	 *
	 * @param int $attempt_id Attempt ID.
	 *
	 * @since 1.0.0
	 *
	 * @return string|false Attempt status or false if not found.
	 */
	public function get_attempt_status( $attempt_id ) {
		global $wpdb;

		$table_name = esc_sql( $wpdb->prefix . 'splms_quiz_attempts' );

		$status = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT status FROM {$table_name} WHERE id = %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
				$attempt_id
			)
		);

		return $status ? $status : false;
	}

	/**
	 * Get attempts by status.
	 *
	 * @param string|array $status   Status or array of statuses.
	 * @param array        $args     Query arguments.
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of attempt objects.
	 */
	public function get_attempts_by_status( $status, $args = array() ) {
		global $wpdb;

		$table_name = esc_sql( $wpdb->prefix . 'splms_quiz_attempts' );

		if ( is_array( $status ) ) {
			$status_placeholders = implode( ',', array_fill( 0, count( $status ), '%s' ) );
			$status_condition    = "status IN ({$status_placeholders})";
			$status_values       = $status;
		} else {
			$status_condition = 'status = %s';
			$status_values    = array( $status );
		}

		$query = "SELECT * FROM {$table_name} WHERE {$status_condition}"; // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter

		$query_values = $status_values;

		if ( ! empty( $args['quiz_id'] ) ) {
			$query         .= ' AND quiz_id = %d';
			$query_values[] = absint( $args['quiz_id'] );
		}

		if ( ! empty( $args['user_id'] ) ) {
			$query         .= ' AND user_id = %d';
			$query_values[] = absint( $args['user_id'] );
		}

		if ( ! empty( $args['course_id'] ) ) {
			$query         .= ' AND course_id = %d';
			$query_values[] = absint( $args['course_id'] );
		}

		$order_by = ! empty( $args['orderby'] ) ? sanitize_sql_orderby( $args['orderby'] ) : 'attempt_time';
		$order    = ! empty( $args['order'] ) && in_array( strtoupper( $args['order'] ), array( 'ASC', 'DESC' ), true ) ? strtoupper( $args['order'] ) : 'DESC';
		$query   .= " ORDER BY {$order_by} {$order}";

		if ( ! empty( $args['limit'] ) ) {
			$query         .= ' LIMIT %d';
			$query_values[] = absint( $args['limit'] );

			if ( ! empty( $args['offset'] ) ) {
				$query         .= ' OFFSET %d';
				$query_values[] = absint( $args['offset'] );
			}
		}

		if ( empty( $query_values ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Query has no placeholders when values is empty.
			return $wpdb->get_results( $query );
		}

		return $wpdb->get_results(
			$wpdb->prepare( $query, ...$query_values ) // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
		);
	}

	/**
	 * Get next allowed statuses for current status.
	 *
	 * @param string $current_status Current status.
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of allowed next statuses.
	 */
	public function get_next_statuses( $current_status ) {
		if ( ! isset( self::ALLOWED_TRANSITIONS[ $current_status ] ) ) {
			return array();
		}

		return self::ALLOWED_TRANSITIONS[ $current_status ];
	}

	/**
	 * Check if status is final (no further transitions allowed).
	 *
	 * @param string $status Status to check.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if status is final.
	 */
	public function is_final_status( $status ) {
		return empty( self::ALLOWED_TRANSITIONS[ $status ] );
	}

	/**
	 * Get all valid statuses.
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of valid statuses.
	 */
	public function get_valid_statuses() {
		return self::VALID_STATUSES;
	}
}
