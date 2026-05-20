<?php
/**
 * Quiz Evaluator Class
 *
 * Centralized evaluation logic for quiz questions and attempts.
 * Provides reusable methods for evaluating answers, calculating scores,
 * and determining pass/fail status. Used by both AJAX handlers and REST API.
 *
 * @since   1.0.0
 * @package SkillPulse_LMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class SPLMS_Quiz_Evaluator
 */
class SPLMS_Quiz_Evaluator {

	/**
	 * Class instance.
	 *
	 * @var SPLMS_Quiz_Evaluator|null $instance
	 */
	private static $instance = null;

	/**
	 * Get the instance of this class.
	 *
	 * @return SPLMS_Quiz_Evaluator
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Evaluate a complete quiz attempt.
	 *
	 * @param int   $quiz_id           Quiz ID.
	 * @param array $questions         Quiz questions with correct answers.
	 * @param array $submitted_answers User submitted answers (keyed by question ID).
	 * @param int   $passing_grade     Passing grade percentage (default: 70).
	 *
	 * @since 1.0.0
	 *
	 * @return array Evaluation results with score, percentage, passed status, and detailed results.
	 */
	public static function evaluate_attempt( $quiz_id, $questions, $submitted_answers, $passing_grade = 70 ) {
		$correct_answers             = 0;
		$total_questions             = count( $questions );
		$detailed_results            = array();
		$total_points_earned         = 0.0;
		$total_points_possible       = 0.0;
		$has_manual_review_questions = false;
		$manual_review_points        = 0.0; // Points from questions needing manual review.

		foreach ( $questions as $question ) {
			// Handle both 'id' and 'question_id' field names for compatibility.
			// Admin calls use 'question_id', frontend calls use 'id'.
			$question_id = isset( $question['id'] ) ? $question['id'] : ( $question['question_id'] ?? null );

			// Skip questions without required fields.
			if ( ! $question_id || ! isset( $question['type'] ) ) {
				continue;
			}

			// Normalize question data (this may standardize the field to 'id').
			$question = self::normalize_question_data( $question );

			$question_type   = $question['type'];
			$user_answer_raw = isset( $submitted_answers[ $question_id ] ) ? $submitted_answers[ $question_id ] : '';

			// Evaluate individual question.
			$result = self::evaluate_question( $question, $user_answer_raw );

			// Check if this question needs manual review.
			$needs_review = isset( $result['needs_manual_review'] ) && $result['needs_manual_review'];

			if ( $needs_review ) {
				$has_manual_review_questions = true;
				$manual_review_points       += $result['points'];
			}

			// Count correct answers (exclude manual review questions from this count).
			if ( $result['is_correct'] && ! $needs_review ) {
				++$correct_answers;
			}

			// Accumulate points.
			// For manual review questions, we exclude them from pass/fail calculation.
			// They will be graded separately by instructor.
			if ( ! $needs_review ) {
				$total_points_possible += $result['points'];
				$total_points_earned   += $result['score'];
			}

			// Store detailed result.
			$detailed_results[] = array(
				'question_id'         => $question_id,
				'question_type'       => $question_type,
				'user_answer'         => $result['user_answer_display'],
				'correct_answer'      => $result['correct_answer_display'],
				'is_correct'          => $result['is_correct'],
				'earned_points'       => $result['score'],
				'points'              => $result['points'],
				'needs_manual_review' => $needs_review,
			);

			// Fire action hook.
			do_action( 'splms_evaluate_question', $question_id, $result['is_correct'], $user_answer_raw );
		}

		// Calculate score percentage.
		// Only count auto-graded questions for pass/fail determination.
		// Manual review questions are excluded until instructor grades them.
		$percentage = $total_points_possible > 0 ? round( ( $total_points_earned / $total_points_possible ) * 100, 2 ) : 0;

		// Determine pass/fail.
		// If there are manual review questions, the quiz status is "pending review".
		// Otherwise, use the percentage to determine pass/fail.
		$passed         = false;
		$pending_review = false;

		if ( $has_manual_review_questions ) {
			// Quiz has essay/file upload questions that need manual grading.
			// Cannot determine pass/fail until ALL questions are graded.
			// Final pass/fail will be determined after manual review.
			$passed         = false;
			$pending_review = true;
		} else {
			// All questions are auto-graded, use percentage.
			$passed = $percentage >= $passing_grade;
		}

		return array(
			'correct_answers'      => $correct_answers,
			'total_questions'      => $total_questions,
			'points_earned'        => $total_points_earned,
			'total_points'         => $total_points_possible,
			'percentage'           => $percentage,
			'passed'               => $passed,
			'pending_review'       => $pending_review,
			'manual_review_points' => $manual_review_points,
			'has_manual_review'    => $has_manual_review_questions,
			'detailed_results'     => $detailed_results,
		);
	}

	/**
	 * Validate answer format before evaluation.
	 *
	 * @param array $question         Question data.
	 * @param mixed $submitted_answer Raw submitted answer.
	 *
	 * @since 1.0.0
	 *
	 * @return true|WP_Error True if valid, WP_Error if invalid.
	 */
	public static function validate_answer_format( $question, $submitted_answer ) {
		$question_type = $question['type'];

		switch ( $question_type ) {
			case 'multiple_choice':
			case 'true_false':
				// Must be string or single-element array.
				if ( is_array( $submitted_answer ) && count( $submitted_answer ) > 1 ) {
					return new WP_Error( 'invalid_format', 'Multiple choice expects single answer.' );
				}
				break;

			case 'multiple_select':
				// Must be array.
				if ( ! is_array( $submitted_answer ) ) {
					return new WP_Error( 'invalid_format', 'Multiple select expects array.' );
				}
				break;

			case 'matching':
				// Must be associative array.
				if ( ! is_array( $submitted_answer ) || empty( $submitted_answer ) ) {
					return new WP_Error( 'invalid_format', 'Matching expects array of pairs.' );
				}
				break;

			case 'essay':
			case 'short_answer':
			case 'long_answer':
			case 'fill_blank':
				// Must be string.
				if ( is_array( $submitted_answer ) ) {
					return new WP_Error( 'invalid_format', 'Text answer expects string.' );
				}
				break;
		}

		return true;
	}

	/**
	 * Evaluate a single question.
	 *
	 * @param array $question         Question data with correct answer.
	 * @param mixed $submitted_answer User submitted answer.
	 *
	 * @since 1.0.0
	 *
	 * @return array Evaluation result with is_correct, score, correct_answer, user_answer_display.
	 */
	public static function evaluate_question( $question, $submitted_answer ) {
		$question_type   = $question['type'];
		$question_points = isset( $question['points'] ) ? floatval( $question['points'] ) : 1.0;

		// Normalize question data.
		$question = self::normalize_question_data( $question );

		// Validate answer format before processing.
		$validation = self::validate_answer_format( $question, $submitted_answer );
		if ( is_wp_error( $validation ) ) {
			// Invalid format - treat as incorrect answer.
			return array(
				'is_correct'             => false,
				'score'                  => 0.0,
				'points'                 => $question_points,
				'correct_answer'         => '',
				'user_answer_display'    => 'Invalid answer format',
				'correct_answer_display' => 'N/A',
				'needs_manual_review'    => false,
			);
		}

		// Map user answer to option IDs if needed.
		$user_answer_normalized = self::normalize_answer( $question, $submitted_answer );

		// Apply filter before evaluation.
		// IMPORTANT: First parameter is the value to filter ($user_answer_normalized), second is additional context ($question).
		$user_answer_normalized = apply_filters( 'splms_evaluate_answer_before', $user_answer_normalized, $question );

		// Get judge method name.
		$judge_method = 'judge_' . $question_type;

		// Evaluate using question type-specific judge method.
		if ( method_exists( __CLASS__, $judge_method ) ) {
			$result = self::$judge_method( $question, $user_answer_normalized );
		} else {
			// Default evaluation.
			$result = self::judge_default( $question, $user_answer_normalized );
		}

		// Apply filter after evaluation.
		$result['is_correct'] = apply_filters( 'splms_evaluate_answer_result', $result['is_correct'], $question, $user_answer_normalized );

		// Calculate earned points.
		// For manual review questions (essay, file_upload), they get 0 points initially.
		// Points will be assigned after instructor review.
		if ( isset( $result['needs_manual_review'] ) && $result['needs_manual_review'] ) {
			$earned_points = 0.0; // Manual review questions start with 0 points.
		} else {
			$earned_points = $result['is_correct'] ? $question_points : 0.0;

			// Apply partial credit if available.
			if ( ! $result['is_correct'] && isset( $result['partial_score'] ) ) {
				$earned_points = $question_points * floatval( $result['partial_score'] );
			}
		}

		// Build display values.
		$user_answer_display    = self::format_answer_for_display( $question, $submitted_answer, false );
		$correct_answer_display = self::format_answer_for_display( $question, $result['correct_answer'], true );

		return array(
			'is_correct'             => $result['is_correct'],
			'score'                  => $earned_points,
			'points'                 => $question_points,
			'correct_answer'         => $result['correct_answer'],
			'user_answer_display'    => $user_answer_display,
			'correct_answer_display' => $correct_answer_display,
			'needs_manual_review'    => isset( $result['needs_manual_review'] ) ? $result['needs_manual_review'] : false,
		);
	}

	/**
	 * Normalize answer format (map text/index to option IDs).
	 *
	 * @param array $question         Question data with options.
	 * @param mixed $submitted_answer Raw submitted answer.
	 *
	 * @since 1.0.0
	 *
	 * @return mixed Normalized answer (option IDs for select questions, original for others).
	 */
	public static function normalize_answer( $question, $submitted_answer ) {
		$question_type = $question['type'];
		$options       = isset( $question['options'] ) && is_array( $question['options'] ) ? $question['options'] : array();

		if ( in_array( $question_type, array( 'multiple_choice', 'true_false' ), true ) ) {
			// Single select - return single option text value.
			if ( is_array( $submitted_answer ) ) {
				$user_answer = ( ! empty( $submitted_answer ) && isset( $submitted_answer[0] ) ) ? $submitted_answer[0] : '';
			} else {
				$user_answer = $submitted_answer;
			}
			$user_answer = trim( (string) $user_answer );

			// Special handling for True/False questions: normalize "true"/"false" strings to "True"/"False".
			if ( 'true_false' === $question_type ) {
				$user_answer_lower = strtolower( $user_answer );
				if ( 'true' === $user_answer_lower ) {
					return 'True';
				} elseif ( 'false' === $user_answer_lower ) {
					return 'False';
				}
			}

			// Check if it's already an option text value.
			foreach ( $options as $option ) {
				$option_text = isset( $option['text'] ) ? trim( (string) $option['text'] ) : '';
				if ( $option_text === $user_answer ) {
					return $option_text;
				}
			}

			// Check if it's an index - map to option text.
			if ( is_numeric( $user_answer ) && isset( $options[ intval( $user_answer ) ] ) ) {
				$option_text = isset( $options[ intval( $user_answer ) ]['text'] ) ? trim( (string) $options[ intval( $user_answer ) ]['text'] ) : '';

				return $option_text;
			}

			return $user_answer; // Return as-is if no match found.

		} elseif ( 'multiple_select' === $question_type ) {
			// Multiple select - return array of option text values.
			$user_answer_array = is_array( $submitted_answer ) ? $submitted_answer : array( $submitted_answer );
			$mapped_texts      = array();

			foreach ( $user_answer_array as $answer_item ) {
				$answer_item = trim( (string) $answer_item );
				if ( empty( $answer_item ) ) {
					continue;
				}

				// Check if it's already an option text value.
				$found = false;
				foreach ( $options as $option ) {
					$option_text = isset( $option['text'] ) ? trim( (string) $option['text'] ) : '';
					if ( $option_text === $answer_item ) {
						$mapped_texts[] = $option_text;
						$found          = true;
						break;
					}
				}

				if ( ! $found ) {
					// Check if it's an index - map to option text.
					if ( is_numeric( $answer_item ) && isset( $options[ intval( $answer_item ) ] ) ) {
						$option_text = isset( $options[ intval( $answer_item ) ]['text'] ) ? trim( (string) $options[ intval( $answer_item ) ]['text'] ) : '';
						if ( ! empty( $option_text ) ) {
							$mapped_texts[] = $option_text;
							continue;
						}
					}

					// If not found, add as-is (might be a text value that doesn't match exactly).
					$mapped_texts[] = $answer_item;
				}
			}

			return $mapped_texts;
		}

		// For other question types, return as-is.
		return $submitted_answer;
	}

	/**
	 * Normalize question data - extract correct answers from correct_answer_json.
	 *
	 * @param array $question Question data.
	 *
	 * @since 1.0.0
	 *
	 * @return array Normalized question data with correct_answer_ids, expected_text, etc.
	 */
	private static function normalize_question_data( $question ) {
		// Normalize question ID field (question_id vs id).
		if ( isset( $question['question_id'] ) && ! isset( $question['id'] ) ) {
			$question['id'] = $question['question_id'];
		}

		$question_type = $question['type'];

		// Handle correct_answer in two possible formats:.
		// 1. Nested format from correct_answer_json: ['answers' => [...]].
		// 2. Flattened format from get_quiz_questions(): direct ID(s) or array of IDs.

		$answers = array();

		if ( isset( $question['correct_answer'] ) ) {
			// For matching questions, correct_answer is already {left_id: right_id} format.
			if ( 'matching' === $question_type && is_array( $question['correct_answer'] ) ) {
				// Check if it's nested format (from correct_answer_json).
				if ( isset( $question['correct_answer']['answers'] ) ) {
					$answers = $question['correct_answer']['answers'];
				} else {
					// It's already in {left_id: right_id} format - use directly.
					$answers = $question['correct_answer'];
				}
			} elseif ( is_array( $question['correct_answer'] ) && isset( $question['correct_answer']['answers'] ) ) {
				// Check if it's nested format (from correct_answer_json).
				$answers = $question['correct_answer']['answers'];
			} elseif ( is_array( $question['correct_answer'] ) && ! empty( $question['correct_answer'] ) ) {
				// Check if it's flattened format (from get_quiz_questions).
				// It's already an array of IDs (for multiple_select, ordering).
				$answers = $question['correct_answer'];
			} elseif ( ! is_array( $question['correct_answer'] ) && ! empty( $question['correct_answer'] ) ) {
				// Check if it's a string (for multiple_choice, true_false, short_answer).
				// It's a single ID or text string - convert to array.
				$answers = array( $question['correct_answer'] );
			}
		}

		// Now process based on question type.
		if ( ! empty( $answers ) || isset( $question['correct_answer'] ) ) {
			switch ( $question_type ) {
				case 'multiple_choice':
				case 'true_false':
					if ( ! empty( $answers ) && isset( $answers[0] ) ) {
						$question['correct_answer_texts'] = array( trim( (string) $answers[0] ) );
					} else {
						$question['correct_answer_texts'] = array();
					}
					break;

				case 'multiple_select':
					$question['correct_answer_texts'] = array_map(
						function ( $text ) {
							return trim( (string) $text );
						},
						is_array( $answers ) ? $answers : array()
					);
					break;

				case 'short_answer':
				case 'fill_blank':
					$expected_text             = ( ! empty( $answers ) && isset( $answers[0] ) ) ? $answers[0] : '';
					$question['expected_text'] = strtolower( trim( (string) $expected_text ) );
					break;

				case 'ordering':
					$question['correct_order'] = array_map(
						function ( $text ) {
							return trim( (string) $text );
						},
						is_array( $answers ) ? $answers : array()
					);
					break;

				case 'matching':
					// For matching, correct_answer is {left_id: right_id} associative array.
					// Check if it's already in the correct format (associative array).
					if ( is_array( $answers ) && ! empty( $answers ) ) {
						// Check if it's associative (has string keys) or sequential (numeric keys).
						$keys           = array_keys( $answers );
						$is_associative = ! empty( $keys ) && ! is_numeric( $keys[0] );

						if ( $is_associative ) {
							// Already in {left_id: right_id} format.
							$question['correct_pairs'] = $answers;
						} else {
							// Sequential array - this shouldn't happen for correct_answer, but handle it.
							$question['correct_pairs'] = array();
						}
					} else {
						// If correct_answer is empty, derive from options_json.
						// Each option represents a pair: {left: "PHP", right: "Server"}.
						// The correct answer is: {left_text: right_text}.
						$question['correct_pairs'] = self::derive_matching_correct_pairs( $question );
					}
					break;
			}
		}

		// If correct_answer is not available or empty, set empty defaults.
		if ( empty( $answers ) && ! isset( $question['correct_answer_texts'] ) ) {
			switch ( $question_type ) {
				case 'multiple_choice':
				case 'multiple_select':
				case 'true_false':
					if ( ! isset( $question['correct_answer_texts'] ) ) {
						$question['correct_answer_texts'] = array();
					}
					break;

				case 'short_answer':
				case 'fill_blank':
					if ( ! isset( $question['expected_text'] ) ) {
						$question['expected_text'] = '';
					}
					break;

				case 'ordering':
					if ( ! isset( $question['correct_order'] ) ) {
						$question['correct_order'] = array();
					}
					break;

				case 'matching':
					if ( ! isset( $question['correct_pairs'] ) ) {
						$question['correct_pairs'] = array();
					}
					break;
			}
		}

		return $question;
	}

	/**
	 * Judge multiple choice question.
	 *
	 * @param array $question         Question data.
	 * @param mixed $submitted_answer Normalized submitted answer.
	 *
	 * @since 1.0.0
	 *
	 * @return array Evaluation result.
	 */
	private static function judge_multiple_choice( $question, $submitted_answer ) {
		// Extract user answer - handle both array and scalar values.
		if ( is_array( $submitted_answer ) ) {
			$user_text = ( ! empty( $submitted_answer ) && isset( $submitted_answer[0] ) ) ? $submitted_answer[0] : '';
		} else {
			$user_text = $submitted_answer;
		}
		$user_text = trim( (string) $user_text );

		// Get correct answer text.
		$correct_texts = isset( $question['correct_answer_texts'] ) ? $question['correct_answer_texts'] : array();
		$correct_text  = ( ! empty( $correct_texts ) && isset( $correct_texts[0] ) ) ? trim( (string) $correct_texts[0] ) : '';

		$is_correct = ( $user_text === $correct_text && ! empty( $user_text ) && ! empty( $correct_text ) );

		return array(
			'is_correct'     => $is_correct,
			'correct_answer' => $correct_text,
		);
	}

	/**
	 * Judge multiple select question.
	 *
	 * @param array $question         Question data.
	 * @param mixed $submitted_answer Normalized submitted answer.
	 *
	 * @since 1.0.0
	 *
	 * @return array Evaluation result.
	 */
	private static function judge_multiple_select( $question, $submitted_answer ) {
		$user_texts = is_array( $submitted_answer ) ? $submitted_answer : array( $submitted_answer );
		$user_texts = array_map(
			function ( $text ) {
				return trim( (string) $text );
			},
			$user_texts
		);
		$user_texts = array_filter( $user_texts );
		sort( $user_texts );

		$correct_texts = isset( $question['correct_answer_texts'] ) ? $question['correct_answer_texts'] : array();
		$correct_texts = array_map(
			function ( $text ) {
				return trim( (string) $text );
			},
			$correct_texts
		);
		sort( $correct_texts );

		$is_correct = ( $user_texts === $correct_texts );

		return array(
			'is_correct'     => $is_correct,
			'correct_answer' => $correct_texts,
		);
	}

	/**
	 * Judge true/false question.
	 *
	 * @param array $question         Question data.
	 * @param mixed $submitted_answer Normalized submitted answer.
	 *
	 * @since 1.0.0
	 *
	 * @return array Evaluation result.
	 */
	private static function judge_true_false( $question, $submitted_answer ) {
		return self::judge_multiple_choice( $question, $submitted_answer );
	}

	/**
	 * Judge short answer question.
	 *
	 * @param array $question         Question data.
	 * @param mixed $submitted_answer Raw submitted answer.
	 *
	 * @since 1.0.0
	 *
	 * @return array Evaluation result.
	 */
	private static function judge_short_answer( $question, $submitted_answer ) {
		if ( is_array( $submitted_answer ) ) {
			$user_text = ( ! empty( $submitted_answer ) && isset( $submitted_answer[0] ) ) ? $submitted_answer[0] : '';
		} else {
			$user_text = $submitted_answer;
		}
		$user_text_normalized = strtolower( trim( (string) $user_text ) );
		$expected_text        = isset( $question['expected_text'] ) ? $question['expected_text'] : '';

		$is_correct = ( $user_text_normalized === $expected_text && ! empty( $user_text_normalized ) );

		// Get correct_answer from question array (populated from correct_answer_json).
		$correct_answer_display = isset( $question['correct_answer'] ) ? $question['correct_answer'] : '';

		return array(
			'is_correct'     => $is_correct,
			'correct_answer' => $correct_answer_display,
		);
	}

	/**
	 * Judge fill blank question.
	 *
	 * @param array $question         Question data.
	 * @param mixed $submitted_answer Raw submitted answer.
	 *
	 * @since 1.0.0
	 *
	 * Add reference from evaluate_question function.
	 *
	 * @return array Evaluation result.
	 */
	private static function judge_fill_blank( $question, $submitted_answer ) {
		return self::judge_short_answer( $question, $submitted_answer );
	}

	/**
	 * Judge matching question.
	 *
	 * @param array $question         Question data.
	 * @param mixed $submitted_answer Raw submitted answer.
	 *
	 * @since 1.0.0
	 *
	 * @return array Evaluation result.
	 */
	private static function judge_matching( $question, $submitted_answer ) {
		$user_pairs    = is_array( $submitted_answer ) ? $submitted_answer : array();
		$correct_pairs = isset( $question['correct_pairs'] ) ? $question['correct_pairs'] : array();

		// Check if it's a sequential array (numeric keys starting from 0).
		// Sequential array format: [rightValue0, rightValue1, ...] where index = pair index.
		$is_sequential = false;
		if ( ! empty( $user_pairs ) ) {
			$keys          = array_keys( $user_pairs );
			$is_sequential = ( array_keys( $keys ) === $keys ); // Check if keys are 0,1,2,3....
		}

		// If sequential array, convert to associative format using question options.
		if ( $is_sequential ) {
			$options = isset( $question['options'] ) && is_array( $question['options'] ) ? $question['options'] : array();

			// Convert sequential array to associative: {left_id: right_value}.
			// Array index corresponds to option index.
			$converted_pairs = array();
			foreach ( $user_pairs as $index => $right_value ) {
				if ( ! empty( $right_value ) && isset( $options[ $index ] ) ) {
					$option  = $options[ $index ];
					$left_id = isset( $option['id'] ) ? trim( (string) $option['id'] ) : '';

					if ( ! empty( $left_id ) ) {
						$converted_pairs[ $left_id ] = $right_value;
					}
				}
			}
			$user_pairs = $converted_pairs;
		}

		// If correct_pairs is empty, try to derive from options.
		if ( empty( $correct_pairs ) ) {
			$correct_pairs = self::derive_matching_correct_pairs( $question );
		}

		// If still empty, we can't evaluate - return incorrect.
		if ( empty( $correct_pairs ) ) {
			return array(
				'is_correct'     => false,
				'correct_answer' => array(),
				'partial_score'  => null,
			);
		}

		// Normalize user pairs to text values.
		$user_pairs_normalized = array();

		foreach ( $user_pairs as $left_key => $right_value ) {
			// Both left_key and right_value should be text values.
			$left_text  = trim( (string) $left_key );
			$right_text = trim( (string) $right_value );

			if ( ! empty( $left_text ) && ! empty( $right_text ) ) {
				$user_pairs_normalized[ $left_text ] = $right_text;
			}
		}

		$correct_count = 0;
		$total_pairs   = count( $correct_pairs );

		foreach ( $correct_pairs as $left_text => $correct_right ) {
			if ( isset( $user_pairs_normalized[ $left_text ] ) ) {
				$user_right = $user_pairs_normalized[ $left_text ];
				// Compare text values (case-insensitive).
				$is_match = strtolower( trim( $user_right ) ) === strtolower( trim( $correct_right ) );

				if ( $is_match ) {
					++$correct_count;
				}
			}
		}

		$settings       = maybe_unserialize( $question['settings'] );
		$partial_credit = isset( $settings['partial_credit'] ) && $settings['partial_credit'];

		$is_correct    = false;
		$partial_score = null;

		if ( $partial_credit && $total_pairs > 0 ) {
			$is_correct = ( $correct_count === $total_pairs );
			if ( ! $is_correct ) {
				$partial_score = $correct_count / $total_pairs;
			}
		} else {
			$is_correct = ( $correct_count === $total_pairs && $total_pairs > 0 );
		}

		return array(
			'is_correct'     => $is_correct,
			'correct_answer' => $correct_pairs,
			'partial_score'  => $partial_score,
		);
	}

	/**
	 * Derive correct pairs from options_json for matching questions.
	 * Each option in options_json represents a pair: {id: "left_id", option_data: {left: "PHP", right: "Server"}}
	 * The correct answer is: {left_id: right_id} where right_id is the option ID that has matching right text.
	 *
	 * @param array $question Question data.
	 *
	 * @since 1.0.0
	 *
	 * @return array Correct pairs {left_id: right_id}.
	 */
	private static function derive_matching_correct_pairs( $question ) {
		$correct_pairs = array();
		$options       = isset( $question['options'] ) && is_array( $question['options'] ) ? $question['options'] : array();

		// For matching questions stored in options_json:.
		// Each option is a pair: {option_data: {left: "PHP", right: "Server"}}.
		// The correct answer is: {left_text: right_text}.
		// Since user submits right texts like ["Server", "Browser", "Database"],.
		// we need to map left_text -> right_text for comparison.

		// Build correct pairs: each option's left_text maps to its right text.
		// The right text will be matched against user's submitted right texts.
		foreach ( $options as $option ) {
			$option_data = isset( $option['option_data'] ) && is_array( $option['option_data'] ) ? $option['option_data'] : array();
			$left_text   = isset( $option_data['left'] ) ? trim( (string) $option_data['left'] ) : '';
			$right_text  = isset( $option_data['right'] ) ? trim( (string) $option_data['right'] ) : '';

			if ( empty( $left_text ) || empty( $right_text ) ) {
				continue;
			}

			// Map left_text to right_text (the correct answer).
			$correct_pairs[ $left_text ] = $right_text;
		}

		return $correct_pairs;
	}

	/**
	 * Judge ordering question.
	 *
	 * @param array $question         Question data.
	 * @param mixed $submitted_answer Raw submitted answer.
	 *
	 * @since 1.0.0
	 *
	 * @return array Evaluation result.
	 */
	private static function judge_ordering( $question, $submitted_answer ) {
		$user_order_raw = is_array( $submitted_answer ) ? $submitted_answer : array( $submitted_answer );

		// Normalize user order to option text values.
		$user_order = array();
		$options    = isset( $question['options'] ) && is_array( $question['options'] ) ? $question['options'] : array();

		foreach ( $user_order_raw as $item ) {
			$item = trim( (string) $item );
			if ( empty( $item ) ) {
				continue;
			}

			// Check if it's already an option text value.
			foreach ( $options as $option ) {
				$option_text = isset( $option['text'] ) ? trim( (string) $option['text'] ) : '';
				if ( $option_text === $item ) {
					$user_order[] = $option_text;
					break;
				}
			}

			// If not found in options, add as-is (might be a text value).
			if ( ! in_array( $item, $user_order, true ) ) {
				$user_order[] = $item;
			}
		}

		// Get correct order (array of option text values).
		$correct_order = isset( $question['correct_order'] ) ? $question['correct_order'] : array();
		$correct_order = array_map(
			function ( $text ) {
				return trim( (string) $text );
			},
			$correct_order
		);

		// Compare arrays - must match exactly for full credit.
		$is_correct = ( $user_order === $correct_order && count( $user_order ) === count( $correct_order ) );

		// Calculate partial credit if enabled.
		$settings       = maybe_unserialize( $question['settings'] );
		$partial_credit = isset( $settings['partial_credit'] ) && $settings['partial_credit'];
		$partial_score  = null;

		if ( ! $is_correct && $partial_credit && ! empty( $correct_order ) ) {
			// Count how many items are in the correct position.
			$correct_positions = 0;
			$max_length        = max( count( $user_order ), count( $correct_order ) );

			for ( $i = 0; $i < $max_length; $i++ ) {
				if ( isset( $user_order[ $i ] ) && isset( $correct_order[ $i ] ) ) {
					if ( $user_order[ $i ] === $correct_order[ $i ] ) {
						++$correct_positions;
					}
				}
			}

			if ( $correct_positions > 0 ) {
				$partial_score = $correct_positions / count( $correct_order );
			}
		}

		return array(
			'is_correct'     => $is_correct,
			'correct_answer' => $correct_order,
			'partial_score'  => $partial_score,
		);
	}

	/**
	 * Judge essay question.
	 *
	 * Essay questions always require manual review by an instructor.
	 * They are marked as incorrect initially (0 points) and excluded from
	 * automatic pass/fail calculation. The instructor must manually grade
	 * the essay and update the score.
	 *
	 * @param array $question         Question data.
	 * @param mixed $submitted_answer Raw submitted answer (essay text).
	 *
	 * @since 1.0.0
	 *
	 * @return array Evaluation result with needs_manual_review flag.
	 */
	private static function judge_essay(
		$question,
		$submitted_answer
	) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- Parameters kept for consistency with other judge methods.
		// Essay questions always need manual review.
		// They start with 0 points until instructor grades them.
		// They are excluded from automatic pass/fail calculation.
		return array(
			'is_correct'          => false, // Always false until manually graded.
			'correct_answer'      => '', // No correct answer for essays.
			'needs_manual_review' => true, // Flag for manual grading.
		);
	}

	/**
	 * Judge long answer question.
	 *
	 * @param array $question         Question data.
	 * @param mixed $submitted_answer Raw submitted answer.
	 *
	 * @since 1.0.0
	 *
	 * @return array Evaluation result.
	 */
	private static function judge_long_answer( $question, $submitted_answer ) {
		return self::judge_essay( $question, $submitted_answer );
	}

	/**
	 * Judge file upload question.
	 *
	 * @param array $question         Question data.
	 * @param mixed $submitted_answer Raw submitted answer (file URL).
	 *
	 * @since 1.0.0
	 *
	 * @return array Evaluation result.
	 */
	private static function judge_file_upload( $question, $submitted_answer ) {
		return array(
			'is_correct'          => false,
			'correct_answer'      => 'pending_review',
			'needs_manual_review' => true,
		);
	}

	/**
	 * Default judge method for unknown question types.
	 *
	 * @param array $question         Question data.
	 * @param mixed $submitted_answer Raw submitted answer.
	 *
	 * @since 1.0.0
	 *
	 * @return array Evaluation result.
	 */
	private static function judge_default( $question, $submitted_answer ) {
		if ( is_array( $submitted_answer ) ) {
			$user_text = ( ! empty( $submitted_answer ) && isset( $submitted_answer[0] ) ) ? $submitted_answer[0] : '';
		} else {
			$user_text = $submitted_answer;
		}
		$user_text_normalized = strtolower( trim( (string) $user_text ) );
		$expected_text        = isset( $question['expected_text'] ) ? $question['expected_text'] : '';
		$is_correct           = ( $user_text_normalized === $expected_text && ! empty( $user_text_normalized ) );

		$correct_answer_display = isset( $question['correct_answer'] ) ? $question['correct_answer'] : '';

		return array(
			'is_correct'     => $is_correct,
			'correct_answer' => $correct_answer_display,
		);
	}

	/**
	 * Format answer for display.
	 *
	 * @param array $question          Question data.
	 * @param mixed $answer            Answer to format.
	 * @param bool  $is_correct_answer Whether this is the correct answer.
	 *
	 * @since 1.0.0
	 *
	 * @return string Formatted answer text.
	 */
	public static function format_answer_for_display( $question, $answer, $is_correct_answer = false ) {
		$question_type = $question['type'];
		$options       = isset( $question['options'] ) && is_array( $question['options'] ) ? $question['options'] : array();

		if ( ! $answer || '' === $answer || ( is_array( $answer ) && 0 === count( $answer ) ) ) {
			return $is_correct_answer ? 'N/A' : 'No answer';
		}

		// Helper to map option ID to text.
		$get_option_text = function ( $option_id, $options ) {
			if ( ! is_array( $options ) ) {
				return strval( $option_id );
			}
			foreach ( $options as $option ) {
				$opt_id = isset( $option['id'] ) ? trim( (string) $option['id'] ) : '';
				if ( trim( (string) $option_id ) === $opt_id ) {
					return isset( $option['text'] ) ? $option['text'] : strval( $option_id );
				}
			}

			return strval( $option_id );
		};

		if ( in_array( $question_type, array( 'multiple_choice', 'true_false' ), true ) ) {
			$option_ids = is_array( $answer ) ? $answer : array( $answer );
			$texts      = array();
			foreach ( $option_ids as $option_id ) {
				$texts[] = $get_option_text( $option_id, $options );
			}

			return implode( ', ', $texts );
		}

		if ( 'multiple_select' === $question_type ) {
			$option_ids = is_array( $answer ) ? $answer : array( $answer );
			$texts      = array();
			foreach ( $option_ids as $option_id ) {
				$texts[] = $get_option_text( $option_id, $options );
			}

			return implode( ', ', $texts );
		}

		if ( 'ordering' === $question_type ) {
			$option_ids = is_array( $answer ) ? $answer : array( $answer );
			$texts      = array();
			foreach ( $option_ids as $option_id ) {
				$texts[] = $get_option_text( $option_id, $options );
			}

			return implode( ' → ', $texts );
		}

		if ( 'file_upload' === $question_type ) {
			if ( is_string( $answer ) && ( 0 === strpos( $answer, 'http' ) || 0 === strpos( $answer, '/' ) ) ) {
				return $answer;
			}

			return strval( $answer );
		}

		if ( is_array( $answer ) ) {
			return implode( ', ', array_map( 'strval', $answer ) );
		}

		return strval( $answer );
	}

	/**
	 * Calculate score from evaluation results.
	 *
	 * @param array $quiz_settings      Quiz settings.
	 * @param array $evaluation_results Evaluation results from evaluate_attempt().
	 *
	 * @since 1.0.0
	 *
	 * @return array Score calculation with total, earned, percentage, passed.
	 */
	public static function calculate_score( $quiz_settings, $evaluation_results ) {
		$points_earned = isset( $evaluation_results['points_earned'] ) ? $evaluation_results['points_earned'] : 0;
		$total_points  = isset( $evaluation_results['total_points'] ) ? $evaluation_results['total_points'] : 0;
		$passing_grade = isset( $quiz_settings['passing_grade'] ) ? floatval( $quiz_settings['passing_grade'] ) : 70;

		$percentage = $total_points > 0 ? round( ( $points_earned / $total_points ) * 100, 2 ) : 0;
		$passed     = $percentage >= $passing_grade;

		return array(
			'total'      => $total_points,
			'earned'     => $points_earned,
			'percentage' => $percentage,
			'passed'     => $passed,
		);
	}
}
