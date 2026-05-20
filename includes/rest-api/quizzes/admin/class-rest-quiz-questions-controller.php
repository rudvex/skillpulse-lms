<?php
/**
 * Quiz Questions REST API Controller
 *
 * Handles REST API endpoints for quiz question management.
 * Provides endpoints for CRUD operations on questions, reordering, and importing questions.
 *
 * @package SkillPulse_LMS
 * @since 1.0.0
 *
 * @api
 * Available endpoints:
 * GET    /splms/v1/quizzes/{id}/questions              - Get quiz questions
 * PUT    /splms/v1/quizzes/{id}/questions              - Update quiz questions
 * POST   /splms/v1/quizzes/{id}/questions/add         - Add question
 * PUT    /splms/v1/quizzes/{id}/questions/{question_id} - Update question
 * DELETE /splms/v1/quizzes/{id}/questions/{question_id} - Delete question
 * POST   /splms/v1/quizzes/{id}/questions/{question_id}/duplicate - Duplicate question
 * PUT    /splms/v1/quizzes/{id}/questions/reorder     - Reorder questions
 * POST   /splms/v1/quizzes/{id}/questions/import      - Import questions
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Quiz Questions REST API Controller class.
 *
 * @since 1.0.0
 */
class SPLMS_REST_Quiz_Questions_Controller extends WP_REST_Controller {

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function __construct() {
		$this->namespace = splms_rest_namespace() . '/' . splms_rest_version();
		$this->rest_base = 'quizzes';
	}

	/**
	 * Register the quiz questions routes.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register_routes() {
		// Get quiz questions (handles both admin and public access).
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)/questions',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_quiz_questions' ),
				'permission_callback' => array( $this, 'get_quiz_questions_permissions_check' ),
				'args'                => array(
					'id' => array(
						'description' => __( 'Quiz ID', 'skillpulse-lms' ),
						'type'        => 'integer',
						'required'    => true,
					),
				),
			)
		);

		// Update quiz questions.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)/questions',
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'update_quiz_questions' ),
				'permission_callback' => array( $this, 'update_quiz_questions_permissions_check' ),
				'args'                => array(
					'id'        => array(
						'description' => __( 'Quiz ID', 'skillpulse-lms' ),
						'type'        => 'integer',
						'required'    => true,
					),
					'questions' => array(
						'description' => __( 'Quiz questions array', 'skillpulse-lms' ),
						'type'        => 'array',
						'items'       => array( 'type' => 'object' ),
						'required'    => true,
					),
				),
			)
		);

		// Add single question.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)/questions/add',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'add_quiz_question' ),
				'permission_callback' => array( $this, 'add_quiz_question_permissions_check' ),
				'args'                => array(
					'id'       => array(
						'description' => __( 'Quiz ID', 'skillpulse-lms' ),
						'type'        => 'integer',
						'required'    => true,
					),
					'question' => array(
						'description' => __( 'Question object', 'skillpulse-lms' ),
						'type'        => 'object',
						'required'    => true,
					),
				),
			)
		);

		// Update single question.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)/questions/(?P<question_id>[\d]+)',
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'update_quiz_question' ),
				'permission_callback' => array( $this, 'update_quiz_question_permissions_check' ),
				'args'                => array(
					'id'          => array(
						'description' => __( 'Quiz ID', 'skillpulse-lms' ),
						'type'        => 'integer',
						'required'    => true,
					),
					'question_id' => array(
						'description' => __( 'Question ID', 'skillpulse-lms' ),
						'type'        => 'integer',
						'required'    => true,
					),
					'question'    => array(
						'description' => __( 'Question object', 'skillpulse-lms' ),
						'type'        => 'object',
						'required'    => true,
					),
				),
			)
		);

		// Delete single question.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)/questions/(?P<question_id>[\d]+)',
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( $this, 'delete_quiz_question' ),
				'permission_callback' => array( $this, 'delete_quiz_question_permissions_check' ),
				'args'                => array(
					'id'          => array(
						'description' => __( 'Quiz ID', 'skillpulse-lms' ),
						'type'        => 'integer',
						'required'    => true,
					),
					'question_id' => array(
						'description' => __( 'Question ID', 'skillpulse-lms' ),
						'type'        => 'integer',
						'required'    => true,
					),
				),
			)
		);

		// Duplicate question.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)/questions/(?P<question_id>[\d]+)/duplicate',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'duplicate_quiz_question' ),
				'permission_callback' => array( $this, 'duplicate_quiz_question_permissions_check' ),
				'args'                => array(
					'id'          => array(
						'description' => __( 'Quiz ID', 'skillpulse-lms' ),
						'type'        => 'integer',
						'required'    => true,
					),
					'question_id' => array(
						'description' => __( 'Question ID', 'skillpulse-lms' ),
						'type'        => 'integer',
						'required'    => true,
					),
				),
			)
		);

		// Reorder questions.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)/questions/reorder',
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'reorder_quiz_questions' ),
				'permission_callback' => array( $this, 'reorder_quiz_questions_permissions_check' ),
				'args'                => array(
					'id'             => array(
						'description' => __( 'Quiz ID', 'skillpulse-lms' ),
						'type'        => 'integer',
						'required'    => true,
					),
					'question_order' => array(
						'description' => __( 'Array of question IDs in new order', 'skillpulse-lms' ),
						'type'        => 'array',
						'items'       => array( 'type' => 'integer' ),
						'required'    => true,
					),
				),
			)
		);

		// Import questions from bank.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)/questions/import',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'import_quiz_questions' ),
				'permission_callback' => array( $this, 'import_quiz_questions_permissions_check' ),
				'args'                => array(
					'id'          => array(
						'description' => __( 'Quiz ID', 'skillpulse-lms' ),
						'type'        => 'integer',
						'required'    => true,
					),
					'import_data' => array(
						'description' => __( 'Import data', 'skillpulse-lms' ),
						'type'        => 'object',
						'required'    => true,
					),
				),
			)
		);
	}

	/**
	 * Get quiz questions.
	 *
	 * Retrieves all questions for a specific quiz with their options, correct answers,
	 * and metadata. Questions are returned in their configured order.
	 *
	 * For admin users (with edit_post capability): Returns full data including correct answers.
	 * For public users: Returns sanitized data without correct answers.
	 *
	 * @since 1.0.0
	 *
	 * @api {get} /splms/v1/quizzes/:id/questions Get Quiz Questions
	 * @apiName GetQuizQuestions
	 * @apiGroup Quizzes
	 * @apiVersion 1.0.0
	 *
	 * @apiDescription Retrieve all questions for a quiz. Returns questions with options,
	 * points, and settings. For admin users, includes correct answers. For public users,
	 * correct answers are stripped for security.
	 *
	 * @apiParam {Number} id Quiz unique identifier.
	 *
	 * @apiError (Error 404) quiz_not_found Quiz not found.
	 * @apiError (Error 403) rest_forbidden Insufficient permissions to access quiz questions.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_quiz_questions( $request ) {
		$quiz_id = $request->get_param( 'id' );
		$quiz    = get_post( $quiz_id );

		if ( ! $quiz || SPLMS_POST_TYPES['quiz'] !== $quiz->post_type ) {
			return new WP_Error( 'quiz_not_found', __( 'Quiz not found.', 'skillpulse-lms' ), array( 'status' => 404 ) );
		}

		// Check if user can edit this quiz (admin request).
		$is_admin = current_user_can( 'edit_post', $quiz_id );

		// Get questions using centralized method.
		$quizzes_class = SPLMS_Quizzes::get_instance();
		$questions     = $quizzes_class->get_quiz_questions( $quiz_id, $is_admin );

		return rest_ensure_response( $questions );
	}

	/**
	 * Update quiz questions.
	 *
	 * Updates all questions for a quiz. Performs upsert operations: updates existing
	 * questions, inserts new ones, and deletes removed ones. Maintains question order.
	 *
	 * @since 1.0.0
	 *
	 * @api {put} /splms/v1/quizzes/:id/questions Update Quiz Questions
	 * @apiName UpdateQuizQuestions
	 * @apiGroup Quizzes
	 * @apiVersion 1.0.0
	 *
	 * @apiDescription Update all questions for a quiz. Performs upsert operations:
	 * updates existing questions (by question_id), inserts new ones, and deletes
	 * questions not in the provided array. Requires edit_posts capability.
	 *
	 * @apiParam {Number} id Quiz unique identifier.
	 * @apiParam {Array} questions Array of question objects.
	 * @apiParam {Number} [questions.question_id] Question ID (for updates, omit for new questions).
	 * @apiParam {String} questions.type Question type.
	 * @apiParam {String} questions.question Question text.
	 * @apiParam {Number} questions.points Points for correct answer.
	 * @apiParam {Array} [questions.options] Answer options (for multiple choice questions).
	 * @apiParam {String|Array} [questions.correct_answer] Correct answer(s).
	 *
	 * @apiError (Error 404) quiz_not_found Quiz not found.
	 * @apiError (Error 400) invalid_questions Questions must be an array.
	 * @apiError (Error 400) missing_field Missing required field in question.
	 * @apiError (Error 403) rest_forbidden Insufficient permissions to update quiz questions.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function update_quiz_questions( $request ) {
		$quiz_id   = $request->get_param( 'id' );
		$questions = $request->get_param( 'questions' );

		$quiz = get_post( $quiz_id );
		if ( ! $quiz || SPLMS_POST_TYPES['quiz'] !== $quiz->post_type ) {
			return new WP_Error( 'quiz_not_found', __( 'Quiz not found.', 'skillpulse-lms' ), array( 'status' => 404 ) );
		}

		// Validate and sanitize questions.
		$sanitized_questions = $this->sanitize_questions( $questions );

		if ( is_wp_error( $sanitized_questions ) ) {
			return $sanitized_questions;
		}

		$questions_query = SPLMS_Quiz_Questions_Query::get_instance();

		// 1) Get existing questions and index them by ID.
		$existing       = $questions_query->get_quiz_questions( $quiz_id );
		$existing_by_id = array();
		foreach ( $existing as $q ) {
			$existing_by_id[ $q->id ] = $q;
		}

		$active_question_ids = array();

		// 2) Loop incoming questions maintaining array order.
		foreach ( $sanitized_questions as $order_index => $question ) {
			// Convert question_id to integer.
			$qid = isset( $question['question_id'] ) ? intval( $question['question_id'] ) : 0;

			// Determine if this is an update or insert.
			$is_update = ( $qid > 0 && isset( $existing_by_id[ $qid ] ) );

			// 3) Build settings cleanly.
			$settings = isset( $question['settings'] ) ? $question['settings'] : array();

			// Remove any stray correct_answer from settings (will be handled per type).
			unset( $settings['correct_answer'] );

			// Type-specific settings handling.
			if ( 'matching' === $question['type'] && isset( $question['pairs'] ) ) {
				$settings['pairs'] = $question['pairs'];
			} elseif ( 'ordering' === $question['type'] && isset( $question['items'] ) ) {
				$settings['items'] = $question['items'];
			} elseif ( 'file_upload' === $question['type'] ) {
				if ( isset( $question['allowed_types'] ) ) {
					$settings['allowed_types'] = $question['allowed_types'];
				}
				if ( isset( $question['max_file_size'] ) ) {
					$settings['max_file_size'] = $question['max_file_size'];
				}
			}
			// Note: correct_answer is now stored in correct_answer_json, not in settings.

			// 4) Build options_json based on question type.
			$options_json = $this->build_options_json( $question );

			// 5) Build normalized correct_answer_json.
			$options_json_param  = $options_json ? $options_json : array();
			$correct_answer_json = $this->build_correct_answer_json( $question, $options_json_param );

			// Prepare question data.
			$question_data = array(
				'quiz_id'              => $quiz_id,
				'question_type'        => $question['type'],
				'question_text'        => $question['question'],
				'question_description' => isset( $question['description'] ) ? $question['description'] : '',
				'explanation'          => isset( $question['explanation'] ) ? $question['explanation'] : '',
				'points'               => floatval( $question['points'] ),
				'order_index'          => $order_index,
				'is_required'          => isset( $question['required'] ) ? ( $question['required'] ? 1 : 0 ) : 1,
				'settings'             => maybe_serialize( $settings ),
				'options_json'         => $options_json,
				'correct_answer_json'  => wp_json_encode( $correct_answer_json ),
				'media_type'           => isset( $question['media']['type'] ) ? $question['media']['type'] : 'none',
				'media_url'            => isset( $question['media']['url'] ) ? $question['media']['url'] : '',
			);

			// 5) Update or insert question.
			if ( $is_update ) {
				// Update existing question.
				$questions_query->update_question( $qid, $question_data );
				$active_question_ids[] = $qid;
			} else {
				// Insert new question.
				$qid = $questions_query->add_question( $question_data );
				if ( $qid ) {
					$active_question_ids[] = $qid;
				} else {
					continue; // Skip if insert failed.
				}
			}
		}

		// 6) Delete questions not in active list.
		$questions_to_delete = array_diff( array_keys( $existing_by_id ), $active_question_ids );
		foreach ( $questions_to_delete as $question_id_to_delete ) {
			$questions_query->delete_question( $question_id_to_delete );
		}

		// 7) Return final formatted questions.
		$final_questions     = $questions_query->get_quiz_questions( $quiz_id );
		$formatted_questions = array();
		foreach ( $final_questions as $final_question ) {
			$formatted_questions[] = $this->format_question_for_response( $final_question );
		}

		$question_count = count( $formatted_questions );
		return rest_ensure_response(
			array(
				'success'   => true,
				'questions' => $formatted_questions,
				// translators: %d: The number of questions updated.
				'message'   => sprintf( __( 'Updated %d questions.', 'skillpulse-lms' ), $question_count ),
			)
		);
	}

	/**
	 * Build normalized correct_answer_json structure.
	 *
	 * @since 1.0.0
	 *
	 * @param array $question Question data.
	 * @param array $options   Options array (from build_options_json).
	 *
	 * @return array Normalized correct answer structure.
	 */
	public function build_correct_answer_json( $question, $options = array() ) {
		$question_type            = $question['type'];
		$correct_answer_structure = array(
			'type'    => $question_type,
			'answers' => array(),
		);

		switch ( $question_type ) {
			case 'multiple_choice':
			case 'true_false':
				// Get correct answer directly from correct_answer field (source of truth).
				if ( isset( $question['correct_answer'] ) && ! empty( $question['correct_answer'] ) ) {
					$correct_text = is_array( $question['correct_answer'] )
						? ( ! empty( $question['correct_answer'] ) ? trim( (string) $question['correct_answer'][0] ) : '' )
						: trim( (string) $question['correct_answer'] );

					if ( ! empty( $correct_text ) ) {
						// For true_false, normalize to 'True' or 'False'.
						if ( 'true_false' === $question_type ) {
							$correct_text = 'true' === strtolower( $correct_text ) ? 'True' : 'False';
						}
						$correct_answer_structure['answers'] = array( $correct_text );
					}
				}
				break;

			case 'multiple_select':
				// Get correct answers directly from correct_answer field (source of truth).
				if ( isset( $question['correct_answer'] ) && is_array( $question['correct_answer'] ) && ! empty( $question['correct_answer'] ) ) {
					$correct_texts = array();
					foreach ( $question['correct_answer'] as $answer ) {
						$answer_text = trim( (string) $answer );
						if ( ! empty( $answer_text ) ) {
							$correct_texts[] = $answer_text;
						}
					}
					$correct_answer_structure['answers'] = $correct_texts;
				}
				break;

			case 'short_answer':
			case 'fill_blank':
				// Text answer - stored as string in array.
				$correct_text                        = isset( $question['correct_answer'] ) ? trim( (string) $question['correct_answer'] ) : '';
				$correct_answer_structure['answers'] = array( $correct_text );
				break;

			case 'matching':
				// Matching pairs - associative array {left_text => right_text}.
				$pairs = array();
				if ( isset( $question['pairs'] ) && is_array( $question['pairs'] ) ) {
					foreach ( $question['pairs'] as $pair ) {
						$left_text  = isset( $pair['left'] ) ? trim( (string) $pair['left'] ) : '';
						$right_text = isset( $pair['right'] ) ? trim( (string) $pair['right'] ) : '';

						if ( ! empty( $left_text ) && ! empty( $right_text ) ) {
							$pairs[ $left_text ] = $right_text;
						}
					}
				}
				$correct_answer_structure['answers'] = $pairs;
				break;

			case 'ordering':
				// Ordering - array of option text values in correct order.
				// Source of truth: items array (in order they appear).
				$order_texts = array();

				// Derive from items array - items define the correct order.
				if ( isset( $question['items'] ) && is_array( $question['items'] ) && ! empty( $question['items'] ) ) {
					foreach ( $question['items'] as $item_index => $item ) {
						$item_text = '';

						if ( is_array( $item ) && isset( $item['text'] ) ) {
							$item_text = trim( (string) $item['text'] );
						} elseif ( is_string( $item ) ) {
							$item_text = trim( (string) $item );
						}

						if ( ! empty( $item_text ) ) {
							$order_texts[] = $item_text;
						}
					}
				}

				$correct_answer_structure['answers'] = $order_texts;
				break;

			case 'essay':
			case 'file_upload':
				// No correct answer - empty array.
				$correct_answer_structure['answers'] = array();
				break;

			default:
				$correct_answer_structure['answers'] = array();
				break;
		}

		return $correct_answer_structure;
	}

	/**
	 * Build options_json array based on question type.
	 *
	 * @since 1.0.0
	 *
	 * @param array $question Question data.
	 *
	 * @return array|null Options array or null if no options needed.
	 */
	private function build_options_json( $question ) {
		$question_type = $question['type'];
		$options       = array();

		if ( in_array( $question_type, array( 'multiple_choice', 'multiple_select' ), true ) ) {
			if ( isset( $question['options'] ) && is_array( $question['options'] ) ) {
				// Get correct answers ONLY from correct_answer_json (source of truth).
				$correct_answer_texts = array();
				if ( isset( $question['correct_answer'] ) ) {
					if ( is_array( $question['correct_answer'] ) && ! empty( $question['correct_answer'] ) ) {
						if ( 'multiple_select' === $question_type ) {
							$correct_answer_texts = $question['correct_answer'];
						} else {
							$correct_answer_texts = array( $question['correct_answer'] );
						}
					} elseif ( ! empty( $question['correct_answer'] ) ) {
						// Handle string value for single select.
						$correct_answer_texts = array( trim( (string) $question['correct_answer'] ) );
					}
				}

				foreach ( $question['options'] as $option_index => $option ) {
					$option_text = isset( $option['text'] ) ? trim( (string) $option['text'] ) : '';

					// Sync is_correct ONLY from correct_answer_json.
					$is_correct = in_array( $option_text, $correct_answer_texts, true ) ? 1 : 0;

					$options[] = array(
						'text'        => $option_text,
						'is_correct'  => $is_correct,
						'order_index' => $option_index,
						'option_data' => array(),
					);
				}
			}
		} elseif ( 'true_false' === $question_type ) {
			$correct_answer = isset( $question['correct_answer'] ) ? $question['correct_answer'] : 'false';
			$true_correct   = ( 'true' === $correct_answer || true === $correct_answer ) ? 1 : 0;
			$options        = array(
				array(
					'text'        => 'True',
					'is_correct'  => $true_correct,
					'order_index' => 0,
					'option_data' => array(),
				),
				array(
					'text'        => 'False',
					'is_correct'  => $true_correct ? 0 : 1,
					'order_index' => 1,
					'option_data' => array(),
				),
			);
		} elseif ( 'matching' === $question_type ) {
			if ( isset( $question['pairs'] ) && is_array( $question['pairs'] ) ) {
				foreach ( $question['pairs'] as $pair_index => $pair ) {
					$left      = isset( $pair['left'] ) ? wp_kses_post( $pair['left'] ) : '';
					$right     = isset( $pair['right'] ) ? wp_kses_post( $pair['right'] ) : '';
					$options[] = array(
						'text'        => $left . '|' . $right,
						'is_correct'  => 0,
						'order_index' => $pair_index,
						'option_data' => array(
							'left'  => $left,
							'right' => $right,
						),
					);
				}
			}
		} elseif ( 'ordering' === $question_type ) {
			if ( isset( $question['items'] ) && is_array( $question['items'] ) ) {
				foreach ( $question['items'] as $item_index => $item ) {
					$item_text = '';
					if ( is_array( $item ) && isset( $item['text'] ) ) {
						$item_text = wp_kses_post( $item['text'] );
					} elseif ( is_string( $item ) ) {
						$item_text = wp_kses_post( $item );
					}

					if ( ! empty( $item_text ) ) {
						$options[] = array(
							'text'        => $item_text,
							'is_correct'  => 0,
							'order_index' => $item_index,
							'option_data' => array(
								'text' => $item_text,
							),
						);
					}
				}
			}
		}

		return ! empty( $options ) ? $options : null;
	}


	/**
	 * Format question object for API response.
	 *
	 * @since 1.0.0
	 *
	 * @param object $question Question object from database.
	 *
	 * @return array Formatted question array.
	 */
	private function format_question_for_response( $question ) {
		$settings = maybe_unserialize( $question->settings );
		if ( ! is_array( $settings ) ) {
			$settings = array();
		}

		$question_data = array(
			'question_id' => $question->id,
			'order_index' => (int) $question->order_index,
			'type'        => $question->question_type,
			'question'    => $question->question_text,
			'description' => $question->question_description,
			'explanation' => $question->explanation,
			'points'      => $question->points,
			'required'    => (bool) $question->is_required,
			'settings'    => $settings,
			'media'       => array(
				'type' => $question->media_type,
				'url'  => $question->media_url,
			),
		);

		// Add normalized correct_answer_json if available.
		if ( isset( $question->correct_answer ) && is_array( $question->correct_answer ) && ! empty( $question->correct_answer ) ) {
			$question_data['correct_answer_json'] = $question->correct_answer;
		}

		// Convert options from options_json for different question types.
		$options = is_array( $question->options ) ? $question->options : array();

		// Get correct_answer from correct_answer_json (source of truth).
		$correct_answer_data  = isset( $question->correct_answer ) && is_array( $question->correct_answer )
			? $question->correct_answer
			: array();
		$correct_answer_texts = array();
		if ( isset( $correct_answer_data['answers'] ) && is_array( $correct_answer_data['answers'] ) ) {
			$correct_answer_texts = $correct_answer_data['answers'];
		}

		if ( in_array( $question->question_type, array( 'multiple_choice', 'multiple_select' ), true ) ) {
			$question_data['options'] = array();

			// Derive is_correct ONLY from correct_answer_json (source of truth).
			$correct_texts = array();
			if ( 'multiple_select' === $question->question_type ) {
				$correct_texts = $correct_answer_texts;
			} else {
				$correct_texts = ! empty( $correct_answer_texts ) ? array( $correct_answer_texts[0] ) : array();
			}

			foreach ( $options as $option ) {
				$option_text = isset( $option['text'] ) ? trim( (string) $option['text'] ) : '';

				// Determine is_correct ONLY by checking if option text is in correct_answer_json.
				$is_correct = in_array( $option_text, $correct_texts, true );

				$question_data['options'][] = array(
					'text'       => $option_text,
					'is_correct' => $is_correct,
				);
			}

			// Set correct_answer from correct_answer_json.
			if ( 'multiple_select' === $question->question_type ) {
				$question_data['correct_answer'] = is_array( $correct_answer_texts ) ? $correct_answer_texts : array();
			} else {
				$question_data['correct_answer'] = is_array( $correct_answer_texts ) && ! empty( $correct_answer_texts ) ? $correct_answer_texts[0] : '';
			}
		} elseif ( 'true_false' === $question->question_type ) {
			$question_data['options'] = array();

			// Derive is_correct ONLY from correct_answer_json (source of truth).
			$correct_text = ! empty( $correct_answer_texts ) ? $correct_answer_texts[0] : '';

			foreach ( $options as $option ) {
				$option_text = isset( $option['text'] ) ? trim( (string) $option['text'] ) : '';

				// Determine is_correct ONLY by checking if option text matches correct_answer_json.
				$is_correct = ( $option_text === $correct_text );

				$question_data['options'][] = array(
					'text'       => $option_text,
					'is_correct' => $is_correct,
				);
			}

			// Set correct_answer from correct_answer_json.
			$question_data['correct_answer'] = ! empty( $correct_text ) ? $correct_text : '';
		} elseif ( 'matching' === $question->question_type ) {
			// Get pairs from settings.
			if ( isset( $settings['pairs'] ) && is_array( $settings['pairs'] ) ) {
				$question_data['pairs'] = $settings['pairs'];
			} else {
				$question_data['pairs'] = array();
			}
		} elseif ( 'ordering' === $question->question_type ) {
			// Get items from settings.
			if ( isset( $settings['items'] ) && is_array( $settings['items'] ) ) {
				$question_data['items'] = $settings['items'];
			} else {
				$question_data['items'] = array();
			}
		} elseif ( in_array( $question->question_type, array( 'short_answer', 'fill_blank' ), true ) ) {
			// correct_answer is now read from correct_answer_json above (line 635-647).
			// No need to read from settings anymore.
			// No additional processing needed for these question types.
			$question_data['correct_answer'] = '';
		} elseif ( 'file_upload' === $question->question_type ) {
			if ( isset( $settings['allowed_types'] ) ) {
				$question_data['allowed_types'] = $settings['allowed_types'];
			}
			if ( isset( $settings['max_file_size'] ) ) {
				$question_data['max_file_size'] = $settings['max_file_size'];
			}
		}

		return $question_data;
	}


	/**
	 * Add single quiz question.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function add_quiz_question( $request ) {
		$quiz_id  = $request->get_param( 'id' );
		$question = $request->get_param( 'question' );

		$quiz = get_post( $quiz_id );
		if ( ! $quiz || SPLMS_POST_TYPES['quiz'] !== $quiz->post_type ) {
			return new WP_Error( 'quiz_not_found', __( 'Quiz not found.', 'skillpulse-lms' ), array( 'status' => 404 ) );
		}

		// Free version: enforce maximum 5 questions per quiz.
		$questions_query_limit = SPLMS_Quiz_Questions_Query::get_instance();
		$existing_questions    = $questions_query_limit->get_quiz_questions( $quiz_id );
		if ( is_array( $existing_questions ) && count( $existing_questions ) >= 5 ) {
			return new WP_Error(
				'splms_question_limit',
				__( 'Maximum of 5 questions per quiz. Upgrade to SkillPulse LMS Pro for unlimited questions.', 'skillpulse-lms' ),
				array( 'status' => 403 )
			);
		}


		// Validate and sanitize new question.
		$sanitized_question = $this->sanitize_question( $question );

		if ( is_wp_error( $sanitized_question ) ) {
			return $sanitized_question;
		}

		// Build options_json.
		$options_json = $this->build_options_json( $sanitized_question );

		// Build normalized correct_answer_json.
		$options_json_param  = $options_json ? $options_json : array();
		$correct_answer_json = $this->build_correct_answer_json( $sanitized_question, $options_json_param );

		// Prepare question data for database.
		$question_data = array(
			'quiz_id'              => $quiz_id,
			'question_type'        => $sanitized_question['type'],
			'question_text'        => $sanitized_question['question'],
			'question_description' => $sanitized_question['description'],
			'explanation'          => $sanitized_question['explanation'],
			'points'               => $sanitized_question['points'],
			'is_required'          => $sanitized_question['required'] ? 1 : 0,
			'settings'             => $sanitized_question['settings'] ?? array(),
			'options_json'         => $options_json,
			'correct_answer_json'  => wp_json_encode( $correct_answer_json ),
			'media_type'           => $sanitized_question['media']['type'] ?? 'none',
			'media_url'            => $sanitized_question['media']['url'] ?? '',
		);

		// Set order_index if provided, otherwise let query class auto-set.
		if ( isset( $sanitized_question['order_index'] ) ) {
			$question_data['order_index'] = intval( $sanitized_question['order_index'] );
		}

		// Add question to database.
		$questions_query = SPLMS_Quiz_Questions_Query::get_instance();
		$question_id     = $questions_query->add_question( $question_data );

		if ( ! $question_id ) {
			return new WP_Error( 'question_add_failed', __( 'Failed to add question.', 'skillpulse-lms' ), array( 'status' => 500 ) );
		}

		// Get the saved question with options.
		$saved_question = $questions_query->get_question( $question_id );

		return rest_ensure_response(
			array(
				'success'  => true,
				'question' => $this->format_question_for_response( $saved_question ),
				'message'  => __( 'Question added successfully.', 'skillpulse-lms' ),
			)
		);
	}

	/**
	 * Update single quiz question.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function update_quiz_question( $request ) {
		$quiz_id     = $request->get_param( 'id' );
		$question_id = $request->get_param( 'question_id' );
		$question    = $request->get_param( 'question' );

		$quiz = get_post( $quiz_id );
		if ( ! $quiz || SPLMS_POST_TYPES['quiz'] !== $quiz->post_type ) {
			return new WP_Error( 'quiz_not_found', __( 'Quiz not found.', 'skillpulse-lms' ), array( 'status' => 404 ) );
		}

		$questions_query = SPLMS_Quiz_Questions_Query::get_instance();

		// Check if question exists.
		$existing_question = $questions_query->get_question( $question_id );
		if ( ! $existing_question || $existing_question->quiz_id !== $quiz_id ) {
			return new WP_Error( 'question_not_found', __( 'Question not found.', 'skillpulse-lms' ), array( 'status' => 404 ) );
		}

		// Validate and sanitize updated question.
		$sanitized_question = $this->sanitize_question( $question );

		if ( is_wp_error( $sanitized_question ) ) {
			return $sanitized_question;
		}

		// Build options_json.
		$options_json = $this->build_options_json( $sanitized_question );

		// Build normalized correct_answer_json.
		$options_json_param  = $options_json ? $options_json : array();
		$correct_answer_json = $this->build_correct_answer_json( $sanitized_question, $options_json_param );

		// Update question in database.
		$question_data = array(
			'question_type'        => $sanitized_question['type'],
			'question_text'        => $sanitized_question['question'],
			'question_description' => $sanitized_question['description'],
			'explanation'          => $sanitized_question['explanation'],
			'points'               => $sanitized_question['points'],
			'is_required'          => $sanitized_question['required'] ? 1 : 0,
			'settings'             => isset( $sanitized_question['settings'] ) ? maybe_serialize( $sanitized_question['settings'] ) : '',
			'options_json'         => $options_json,
			'correct_answer_json'  => wp_json_encode( $correct_answer_json ),
			'media_type'           => $sanitized_question['media']['type'] ?? 'none',
			'media_url'            => $sanitized_question['media']['url'] ?? '',
		);

		// Update order_index if provided.
		if ( isset( $sanitized_question['order_index'] ) ) {
			$question_data['order_index'] = intval( $sanitized_question['order_index'] );
		}

		$updated = $questions_query->update_question( $question_id, $question_data );

		if ( ! $updated ) {
			return new WP_Error( 'question_update_failed', __( 'Failed to update question.', 'skillpulse-lms' ), array( 'status' => 500 ) );
		}

		// Get updated question with options.
		$updated_question = $questions_query->get_question( $question_id );

		return rest_ensure_response(
			array(
				'success'  => true,
				'question' => $this->format_question_for_response( $updated_question ),
				'message'  => __( 'Question updated successfully.', 'skillpulse-lms' ),
			)
		);
	}

	/**
	 * Delete single quiz question.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function delete_quiz_question( $request ) {
		$quiz_id     = $request->get_param( 'id' );
		$question_id = $request->get_param( 'question_id' );

		$quiz = get_post( $quiz_id );
		if ( ! $quiz || SPLMS_POST_TYPES['quiz'] !== $quiz->post_type ) {
			return new WP_Error( 'quiz_not_found', __( 'Quiz not found.', 'skillpulse-lms' ), array( 'status' => 404 ) );
		}

		$questions_query = SPLMS_Quiz_Questions_Query::get_instance();

		// Check if question exists.
		$existing_question = $questions_query->get_question( $question_id );
		if ( ! $existing_question || $existing_question->quiz_id !== $quiz_id ) {
			return new WP_Error( 'question_not_found', __( 'Question not found.', 'skillpulse-lms' ), array( 'status' => 404 ) );
		}

		// Delete question and its options.
		$deleted = $questions_query->delete_question( $question_id );

		if ( ! $deleted ) {
			return new WP_Error( 'question_delete_failed', __( 'Failed to delete question.', 'skillpulse-lms' ), array( 'status' => 500 ) );
		}

		return rest_ensure_response(
			array(
				'success' => true,
				'message' => __( 'Question deleted successfully.', 'skillpulse-lms' ),
			)
		);
	}

	/**
	 * Duplicate quiz question.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function duplicate_quiz_question( $request ) {
		$quiz_id     = $request->get_param( 'id' );
		$question_id = $request->get_param( 'question_id' );

		$quiz = get_post( $quiz_id );
		if ( ! $quiz || SPLMS_POST_TYPES['quiz'] !== $quiz->post_type ) {
			return new WP_Error( 'quiz_not_found', __( 'Quiz not found.', 'skillpulse-lms' ), array( 'status' => 404 ) );
		}

		$questions_query = SPLMS_Quiz_Questions_Query::get_instance();

		// Get existing question.
		$existing_question = $questions_query->get_question( $question_id );
		if ( ! $existing_question || $existing_question->quiz_id !== $quiz_id ) {
			return new WP_Error( 'question_not_found', __( 'Question not found.', 'skillpulse-lms' ), array( 'status' => 404 ) );
		}

		// Get the highest order index for this quiz.
		$quiz_questions = $questions_query->get_quiz_questions( $quiz_id );
		$max_order      = 0;
		foreach ( $quiz_questions as $q ) {
			if ( $q->order_index > $max_order ) {
				$max_order = $q->order_index;
			}
		}

		// Create duplicate question data.
		$duplicate_data = array(
			'quiz_id'              => $quiz_id,
			'question_type'        => $existing_question->question_type,
			'question_text'        => $existing_question->question_text . ' (Copy)',
			'question_description' => $existing_question->question_description,
			'explanation'          => $existing_question->explanation,
			'points'               => $existing_question->points,
			'order_index'          => $max_order + 1,
			'is_required'          => $existing_question->is_required,
			'settings'             => $existing_question->settings,
			'options_json'         => isset( $existing_question->options_json ) ? $existing_question->options_json : null,
			'correct_answer_json'  => isset( $existing_question->correct_answer_json ) ? $existing_question->correct_answer_json : null,
			'media_type'           => $existing_question->media_type,
			'media_url'            => $existing_question->media_url,
		);

		// Add duplicate question.
		$duplicate_id = $questions_query->add_question( $duplicate_data );

		if ( ! $duplicate_id ) {
			return new WP_Error( 'question_duplicate_failed', __( 'Failed to duplicate question.', 'skillpulse-lms' ), array( 'status' => 500 ) );
		}

		// Get the duplicated question with options.
		$duplicated_question = $questions_query->get_question( $duplicate_id );

		return rest_ensure_response(
			array(
				'success'  => true,
				'question' => $this->format_question_for_response( $duplicated_question ),
				'message'  => __( 'Question duplicated successfully.', 'skillpulse-lms' ),
			)
		);
	}

	/**
	 * Reorder quiz questions.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function reorder_quiz_questions( $request ) {
		$quiz_id        = $request->get_param( 'id' );
		$question_order = $request->get_param( 'question_order' );

		$quiz = get_post( $quiz_id );
		if ( ! $quiz || SPLMS_POST_TYPES['quiz'] !== $quiz->post_type ) {
			return new WP_Error( 'quiz_not_found', __( 'Quiz not found.', 'skillpulse-lms' ), array( 'status' => 404 ) );
		}

		$questions_query = SPLMS_Quiz_Questions_Query::get_instance();

		// Get existing questions.
		$existing_questions    = $questions_query->get_quiz_questions( $quiz_id );
		$existing_question_ids = array();
		foreach ( $existing_questions as $question ) {
			$existing_question_ids[] = $question->id;
		}

		// Validate that all provided question IDs exist.
		foreach ( $question_order as $question_id ) {
			if ( ! in_array( $question_id, $existing_question_ids, true ) ) {
				return new WP_Error( 'invalid_question_id', __( 'Invalid question ID provided.', 'skillpulse-lms' ), array( 'status' => 400 ) );
			}
		}

		// Update order for each question.
		foreach ( $question_order as $index => $question_id ) {
			$questions_query->update_question(
				$question_id,
				array(
					'order_index' => $index,
				)
			);
		}

		// Get reordered questions.
		$reordered_questions = $questions_query->get_quiz_questions( $quiz_id );
		$response_questions  = array();
		foreach ( $reordered_questions as $question ) {
			$response_questions[] = $this->format_question_for_response( $question );
		}

		return rest_ensure_response(
			array(
				'success'   => true,
				'questions' => $response_questions,
				'message'   => __( 'Questions reordered successfully.', 'skillpulse-lms' ),
			)
		);
	}

	/**
	 * Import quiz questions from various sources.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function import_quiz_questions( $request ) {
		$quiz_id     = $request->get_param( 'id' );
		$import_data = $request->get_param( 'import_data' );

		$quiz = get_post( $quiz_id );
		if ( ! $quiz || SPLMS_POST_TYPES['quiz'] !== $quiz->post_type ) {
			return new WP_Error( 'quiz_not_found', __( 'Quiz not found.', 'skillpulse-lms' ), array( 'status' => 404 ) );
		}

		$questions_query = SPLMS_Quiz_Questions_Query::get_instance();

		// Get existing questions to determine order.
		$existing_questions = $questions_query->get_quiz_questions( $quiz_id );
		$max_order          = 0;
		foreach ( $existing_questions as $question ) {
			if ( $question->order_index > $max_order ) {
				$max_order = $question->order_index;
			}
		}

		// Process import based on type.
		$import_type        = $import_data['type'] ?? 'json';
		$imported_questions = array();

		switch ( $import_type ) {
			case 'json':
				$imported_questions = $this->import_from_json( $import_data );
				break;
			case 'csv':
				$imported_questions = $this->import_from_csv( $import_data );
				break;
			case 'quiz_copy':
				$imported_questions = $this->import_from_quiz( $import_data );
				break;
			default:
				return new WP_Error( 'invalid_import_type', __( 'Invalid import type.', 'skillpulse-lms' ), array( 'status' => 400 ) );
		}

		if ( is_wp_error( $imported_questions ) ) {
			return $imported_questions;
		}

		$imported_count = 0;

		// Add imported questions to database.
		foreach ( $imported_questions as $question ) {
			++$max_order;

			// Build options_json.
			$options_json = $this->build_options_json( $question );

			// Build normalized correct_answer_json.
			$options_json_param  = $options_json ? $options_json : array();
			$correct_answer_json = $this->build_correct_answer_json( $question, $options_json_param );

			$question_data = array(
				'quiz_id'              => $quiz_id,
				'question_type'        => $question['type'],
				'question_text'        => $question['question'],
				'question_description' => $question['description'],
				'explanation'          => $question['explanation'],
				'points'               => $question['points'],
				'order_index'          => $max_order,
				'is_required'          => $question['required'] ? 1 : 0,
				'settings'             => isset( $question['settings'] ) ? maybe_serialize( $question['settings'] ) : '',
				'options_json'         => $options_json,
				'correct_answer_json'  => wp_json_encode( $correct_answer_json ),
				'media_type'           => $question['media']['type'] ?? 'none',
				'media_url'            => $question['media']['url'] ?? '',
			);

			$question_id = $questions_query->add_question( $question_data );

			if ( $question_id ) {
				++$imported_count;
			}
		}

		return rest_ensure_response(
			array(
				'success'        => true,
				'imported_count' => $imported_count,
				// translators: %d: The number of questions imported.
				'message'        => sprintf( __( 'Imported %d questions successfully.', 'skillpulse-lms' ), $imported_count ),
			)
		);
	}


	/**
	 * Sanitize questions array.
	 *
	 * @since 1.0.0
	 *
	 * @param array $questions Questions array.
	 *
	 * @return array|WP_Error Sanitized questions or error.
	 */
	private function sanitize_questions( $questions ) {
		if ( ! is_array( $questions ) ) {
			return new WP_Error( 'invalid_questions', __( 'Questions must be an array.', 'skillpulse-lms' ), array( 'status' => 400 ) );
		}

		$sanitized_questions = array();

		foreach ( $questions as $question ) {
			$sanitized_question = $this->sanitize_question( $question );

			if ( is_wp_error( $sanitized_question ) ) {
				return $sanitized_question;
			}

			$sanitized_questions[] = $sanitized_question;
		}

		return $sanitized_questions;
	}

	/**
	 * Sanitize single question.
	 *
	 * @since 1.0.0
	 *
	 * @param array $question Question data.
	 *
	 * @return array|WP_Error Sanitized question or error.
	 */
	private function sanitize_question( $question ) {
		if ( ! is_array( $question ) ) {
			return new WP_Error( 'invalid_question', __( 'Question must be an object.', 'skillpulse-lms' ), array( 'status' => 400 ) );
		}

		$required_fields = array( 'type', 'question' );
		foreach ( $required_fields as $field ) {
			if ( ! isset( $question[ $field ] ) || empty( $question[ $field ] ) ) {
				// translators: %s: The missing field name.
				return new WP_Error( 'missing_field', sprintf( __( 'Missing required field: %s', 'skillpulse-lms' ), $field ), array( 'status' => 400 ) );
			}
		}

		// Handle question_id - preserve it as-is (can be string or number, will be converted later).
		$sanitized_question_id = null;
		if ( isset( $question['question_id'] ) && null !== $question['question_id'] && '' !== $question['question_id'] ) {
			// Keep as-is for now - will be converted to int when checking.
			$sanitized_question_id = $question['question_id'];
		}

		$sanitized = array(
			'question_id' => $sanitized_question_id,
			'type'        => sanitize_text_field( $question['type'] ),
			'question'    => wp_kses_post( $question['question'] ),
			'description' => isset( $question['description'] ) ? wp_kses_post( $question['description'] ) : '',
			'points'      => isset( $question['points'] ) ? floatval( $question['points'] ) : 1, // Use floatval for "1.00" strings.
			'required'    => isset( $question['required'] ) ? (bool) $question['required'] : true,
			'explanation' => isset( $question['explanation'] ) ? wp_kses_post( $question['explanation'] ) : '',
		);

		// Type-specific sanitization.
		switch ( $sanitized['type'] ) {
			case 'multiple_choice':
			case 'multiple_select':
				$sanitized['options'] = isset( $question['options'] ) ? $this->sanitize_options( $question['options'] ) : array();
				if ( isset( $question['correct_answer'] ) ) {
					if ( is_array( $question['correct_answer'] ) ) {
						// Preserve HTML tags in correct_answer (e.g., "<a>" should remain "<a>").
						$sanitized['correct_answer'] = array_map( 'wp_kses_post', $question['correct_answer'] );
					} else {
						// Preserve HTML tags in correct_answer (e.g., "<a>" should remain "<a>").
						$sanitized['correct_answer'] = wp_kses_post( $question['correct_answer'] );
					}
				}
				break;

			case 'true_false':
				if ( isset( $question['correct_answer'] ) ) {
					if ( is_string( $question['correct_answer'] ) ) {
						$sanitized['correct_answer'] = 'true' === strtolower( trim( $question['correct_answer'] ) ) ? 'true' : 'false';
					} else {
						$sanitized['correct_answer'] = (bool) $question['correct_answer'] ? 'true' : 'false';
					}
				} else {
					$sanitized['correct_answer'] = 'false';
				}
				break;

			case 'short_answer':
			case 'fill_blank':
				$sanitized['correct_answer'] = isset( $question['correct_answer'] ) ? sanitize_text_field( $question['correct_answer'] ) : '';
				break;

			case 'essay':
				$sanitized['correct_answer'] = isset( $question['correct_answer'] ) ? wp_kses_post( $question['correct_answer'] ) : '';
				break;

			case 'matching':
				$sanitized['pairs'] = isset( $question['pairs'] ) ? $this->sanitize_matching_pairs( $question['pairs'] ) : array();
				if ( isset( $question['correct_answer'] ) ) {
					if ( is_array( $question['correct_answer'] ) ) {
						$sanitized['correct_answer'] = array_map( 'sanitize_text_field', $question['correct_answer'] );
					} else {
						$sanitized['correct_answer'] = sanitize_text_field( $question['correct_answer'] );
					}
				}
				break;

			case 'ordering':
				$sanitized['items'] = isset( $question['items'] ) ? $this->sanitize_ordering_items( $question['items'] ) : array();
				if ( isset( $question['correct_answer'] ) && is_array( $question['correct_answer'] ) ) {
					$sanitized['correct_answer'] = array_map( 'intval', $question['correct_answer'] );
				}
				break;

			case 'file_upload':
				if ( isset( $question['allowed_types'] ) ) {
					$sanitized['allowed_types'] = sanitize_text_field( $question['allowed_types'] );
				}
				if ( isset( $question['max_file_size'] ) ) {
					$sanitized['max_file_size'] = intval( $question['max_file_size'] );
				}
				$sanitized['correct_answer'] = 'pending_review';
				break;
		}

		// Media attachments.
		if ( isset( $question['media'] ) ) {
			$sanitized['media'] = array(
				'type' => isset( $question['media']['type'] ) ? sanitize_text_field( $question['media']['type'] ) : 'none',
				'url'  => isset( $question['media']['url'] ) ? esc_url_raw( $question['media']['url'] ) : '',
			);
		}

		// Question settings.
		if ( isset( $question['settings'] ) ) {
			$sanitized['settings'] = array(
				'randomize_options' => isset( $question['settings']['randomize_options'] ) ? (bool) $question['settings']['randomize_options'] : false,
				'partial_credit'    => isset( $question['settings']['partial_credit'] ) ? (bool) $question['settings']['partial_credit'] : false,
				'case_sensitive'    => isset( $question['settings']['case_sensitive'] ) ? (bool) $question['settings']['case_sensitive'] : false,
			);
		}

		return $sanitized;
	}

	/**
	 * Sanitize multiple choice options.
	 *
	 * @since 1.0.0
	 *
	 * @param array $options Options array.
	 *
	 * @return array Sanitized options array.
	 */
	private function sanitize_options( $options ) {
		if ( ! is_array( $options ) ) {
			return array();
		}

		$sanitized_options = array();

		foreach ( $options as $option ) {
			if ( is_array( $option ) ) {
				$sanitized_options[] = array(
					'id'         => isset( $option['id'] ) && ! empty( $option['id'] ) ? $option['id'] : null, // Preserve option ID.
					'text'       => isset( $option['text'] ) ? wp_kses_post( $option['text'] ) : '', // Preserve HTML tags.
					'is_correct' => isset( $option['is_correct'] ) ? (bool) $option['is_correct'] : false,
				);
			}
		}

		return $sanitized_options;
	}

	/**
	 * Sanitize matching pairs.
	 *
	 * @since 1.0.0
	 *
	 * @param array $pairs Matching pairs array.
	 *
	 * @return array Sanitized pairs array.
	 */
	private function sanitize_matching_pairs( $pairs ) {
		if ( ! is_array( $pairs ) ) {
			return array();
		}

		$sanitized_pairs = array();

		foreach ( $pairs as $pair ) {
			if ( is_array( $pair ) ) {
				$sanitized_pairs[] = array(
					'left'  => isset( $pair['left'] ) ? sanitize_text_field( $pair['left'] ) : '',
					'right' => isset( $pair['right'] ) ? sanitize_text_field( $pair['right'] ) : '',
				);
			}
		}

		return $sanitized_pairs;
	}

	/**
	 * Sanitize ordering items.
	 *
	 * @since 1.0.0
	 *
	 * @param array $items Ordering items array.
	 *
	 * @return array Sanitized items array.
	 */
	private function sanitize_ordering_items( $items ) {
		if ( ! is_array( $items ) ) {
			return array();
		}

		$sanitized_items = array();

		foreach ( $items as $item ) {
			if ( is_array( $item ) ) {
				$sanitized_items[] = array(
					'text'  => isset( $item['text'] ) ? sanitize_text_field( $item['text'] ) : '',
					'order' => isset( $item['order'] ) ? intval( $item['order'] ) : 0,
				);
			}
		}

		return $sanitized_items;
	}

	/**
	 * Import questions from JSON.
	 *
	 * @since 1.0.0
	 *
	 * @param array $import_data Import data.
	 *
	 * @return array|WP_Error Imported questions or error.
	 */
	private function import_from_json( $import_data ) {
		$json_data = isset( $import_data['data'] ) ? $import_data['data'] : '';

		if ( empty( $json_data ) ) {
			return new WP_Error( 'empty_json', __( 'JSON data is empty.', 'skillpulse-lms' ), array( 'status' => 400 ) );
		}

		$questions = json_decode( $json_data, true );

		if ( json_last_error() !== JSON_ERROR_NONE ) {
			return new WP_Error( 'invalid_json', __( 'Invalid JSON format.', 'skillpulse-lms' ), array( 'status' => 400 ) );
		}

		return $this->sanitize_questions( $questions );
	}

	/**
	 * Import questions from CSV.
	 *
	 * @since 1.0.0
	 *
	 * @param array $import_data Import data.
	 *
	 * @return array|WP_Error Imported questions or error.
	 */
	private function import_from_csv( $import_data ) {
		// Basic CSV import - can be extended.
		return new WP_Error( 'not_implemented', __( 'CSV import not yet implemented.', 'skillpulse-lms' ), array( 'status' => 501 ) );
	}

	/**
	 * Import questions from another quiz.
	 *
	 * @since 1.0.0
	 *
	 * @param array $import_data Import data.
	 *
	 * @return array|WP_Error Imported questions or error.
	 */
	private function import_from_quiz( $import_data ) {
		$source_quiz_id = isset( $import_data['source_quiz_id'] ) ? intval( $import_data['source_quiz_id'] ) : 0;

		if ( ! $source_quiz_id ) {
			return new WP_Error( 'invalid_source_quiz', __( 'Invalid source quiz ID.', 'skillpulse-lms' ), array( 'status' => 400 ) );
		}

		$source_quiz = get_post( $source_quiz_id );
		if ( ! $source_quiz || SPLMS_POST_TYPES['quiz'] !== $source_quiz->post_type ) {
			return new WP_Error( 'source_quiz_not_found', __( 'Source quiz not found.', 'skillpulse-lms' ), array( 'status' => 404 ) );
		}

		$questions_query  = SPLMS_Quiz_Questions_Query::get_instance();
		$source_questions = $questions_query->get_quiz_questions( $source_quiz_id );

		$questions = array();
		foreach ( $source_questions as $question ) {
			$question_data = array(
				'type'        => $question->question_type,
				'question'    => $question->question_text,
				'description' => $question->question_description,
				'explanation' => $question->explanation,
				'points'      => $question->points,
				'required'    => (bool) $question->is_required,
				'settings'    => maybe_unserialize( $question->settings ),
				'media'       => array(
					'type' => $question->media_type,
					'url'  => $question->media_url,
				),
			);

			// Add options if they exist - derive is_correct from correct_answer_json.
			if ( isset( $question->options ) && is_array( $question->options ) ) {
				// Get correct answers from correct_answer_json (source of truth).
				$correct_answer_texts = array();
				if ( isset( $question->correct_answer ) && is_array( $question->correct_answer ) ) {
					$correct_answer_data = $question->correct_answer;
					if ( isset( $correct_answer_data['answers'] ) && is_array( $correct_answer_data['answers'] ) ) {
						if ( 'multiple_select' === $question->question_type ) {
							$correct_answer_texts = $correct_answer_data['answers'];
						} else {
							$correct_answer_texts = ! empty( $correct_answer_data['answers'] )
								? array( $correct_answer_data['answers'][0] )
								: array();
						}
					}
				}

				$question_data['options'] = array();
				foreach ( $question->options as $option ) {
					$option_text = isset( $option['text'] ) ? trim( (string) $option['text'] ) : '';

					// Determine is_correct ONLY by checking if option text is in correct_answer_json.
					$is_correct = in_array( $option_text, $correct_answer_texts, true );

					$question_data['options'][] = array(
						'id'         => isset( $option['id'] ) ? $option['id'] : '',
						'text'       => $option_text,
						'is_correct' => $is_correct,
					);
				}
			}

			$questions[] = $question_data;
		}

		return $questions;
	}

	/**
	 * Check permissions for getting quiz questions.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return bool True if user has edit_posts capability, false otherwise.
	 */
	public function get_quiz_questions_permissions_check( $request ) {
		$quiz_id = $request->get_param( 'id' );
		$user_id = get_current_user_id();

		if ( ! $quiz_id ) {
			return new WP_Error( 'invalid_quiz_id', __( 'Invalid quiz ID.', 'skillpulse-lms' ), array( 'status' => 400 ) );
		}

		// Allow admins/editors who can edit the quiz.
		if ( $user_id && current_user_can( 'edit_post', $quiz_id ) ) {
			return true;
		}

		// For other users, check access control (same as public controller).
		$access_control = SPLMS_Access_Control::get_instance();

		// If user is logged in, check full access control.
		if ( $user_id ) {
			if ( ! $access_control->user_can_access_quiz( $user_id, $quiz_id ) ) {
				return new WP_Error( 'rest_forbidden', __( 'Sorry, you are not allowed to access this quiz.', 'skillpulse-lms' ), array( 'status' => rest_authorization_required_code() ) );
			}
			return true;
		}

		// For non-logged-in users, only allow if guest preview is enabled.
		if ( ! $access_control->is_guest_preview_quiz( $quiz_id ) ) {
			return new WP_Error( 'rest_forbidden', __( 'Sorry, you must be logged in to access this quiz.', 'skillpulse-lms' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}

	/**
	 * Check permissions for updating quiz questions.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return bool True if user has edit_posts capability, false otherwise.
	 */
	public function update_quiz_questions_permissions_check( $request ) {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return new WP_Error(
				'rest_forbidden',
				__( 'Sorry, you are not allowed to perform this action.', 'skillpulse-lms' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * Check permissions for adding quiz question.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return bool True if user has edit_posts capability, false otherwise.
	 */
	public function add_quiz_question_permissions_check( $request ) {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return new WP_Error(
				'rest_forbidden',
				__( 'Sorry, you are not allowed to perform this action.', 'skillpulse-lms' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * Check permissions for updating single quiz question.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return bool True if user has edit_posts capability, false otherwise.
	 */
	public function update_quiz_question_permissions_check( $request ) {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return new WP_Error(
				'rest_forbidden',
				__( 'Sorry, you are not allowed to perform this action.', 'skillpulse-lms' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * Check permissions for deleting quiz question.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return bool True if user has edit_posts capability, false otherwise.
	 */
	public function delete_quiz_question_permissions_check( $request ) {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return new WP_Error(
				'rest_forbidden',
				__( 'Sorry, you are not allowed to perform this action.', 'skillpulse-lms' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * Check permissions for duplicating quiz question.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return bool|WP_Error True if user has edit_posts capability, WP_Error otherwise.
	 */
	public function duplicate_quiz_question_permissions_check( $request ) {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return new WP_Error(
				'rest_forbidden',
				__( 'Sorry, you are not allowed to perform this action.', 'skillpulse-lms' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * Check permissions for reordering quiz questions.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return bool|WP_Error True if user has edit_posts capability, WP_Error otherwise.
	 */
	public function reorder_quiz_questions_permissions_check( $request ) {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return new WP_Error(
				'rest_forbidden',
				__( 'Sorry, you are not allowed to perform this action.', 'skillpulse-lms' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * Check permissions for importing quiz questions.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return bool|WP_Error True if user has edit_posts capability, WP_Error otherwise.
	 */
	public function import_quiz_questions_permissions_check( $request ) {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return new WP_Error(
				'rest_forbidden',
				__( 'Sorry, you are not allowed to perform this action.', 'skillpulse-lms' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}
}
