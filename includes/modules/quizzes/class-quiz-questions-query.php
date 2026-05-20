<?php
/**
 * Quiz Questions Query Class
 *
 * Handles quiz questions database operations.
 *
 * @package SkillPulse_LMS
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

if ( ! class_exists( 'SPLMS_Base_Query' ) ) {
	require_once SPLMS_DIR_PATH . 'includes/modules/core/base/class-base-query.php';
}

/**
 * Class SPLMS_Quiz_Questions_Query
 *
 * Handles quiz questions database operations
 *
 * @since 1.0.0
 */
class SPLMS_Quiz_Questions_Query extends SPLMS_Base_Query {

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param string $table_name The table name.
	 */
	protected function __construct( $table_name ) { // phpcs:ignore Generic.CodeAnalysis.UselessOverridingMethod.Found -- Valid pattern for base query classes.
		parent::__construct( $table_name );
	}

	/**
	 * Get the instance of this class.
	 *
	 * @since 1.0.0
	 *
	 * @return SPLMS_Quiz_Questions_Query
	 */
	public static function get_instance() {
		return parent::get_base_instance( __CLASS__, 'splms_quiz_questions' );
	}

	/**
	 * Get quiz questions.
	 *
	 * @since 1.0.0
	 *
	 * @param int   $quiz_id Quiz ID.
	 * @param array $args    Additional arguments.
	 *
	 * @return array
	 */
	public function get_quiz_questions( $quiz_id, $args = array() ) {
		$defaults = array(
			'orderby' => 'order_index',
			'order'   => 'ASC',
			'limit'   => null,
			'offset'  => 0,
		);

		$args = wp_parse_args( $args, $defaults );

		$sql  = "SELECT * FROM {$this->table_name} WHERE quiz_id = %d";
		$sql .= " ORDER BY {$args['orderby']} {$args['order']}";

		if ( $args['limit'] ) {
			$sql .= " LIMIT {$args['limit']}";
		}

		if ( $args['offset'] ) {
			$sql .= " OFFSET {$args['offset']}";
		}

		$results = $this->get_results( $sql, array( $quiz_id ) );

		// Decode options_json and correct_answer_json for each question.
		foreach ( $results as &$question ) {
			$question->options        = $this->decode_options_json( $question->options_json );
			$question->settings       = maybe_unserialize( $question->settings );
			$question->correct_answer = $this->decode_correct_answer_json( $question->correct_answer_json );
		}
		return $results;
	}

	/**
	 * Get single question.
	 *
	 * @since 1.0.0
	 *
	 * @param int $question_id Question ID.
	 *
	 * @return object|null
	 */
	public function get_question( $question_id ) {
		$sql      = "SELECT * FROM {$this->table_name} WHERE id = %d";
		$question = $this->get_row( $sql, array( $question_id ) );

		if ( $question ) {
			$question->options        = $this->decode_options_json( $question->options_json );
			$question->settings       = maybe_unserialize( $question->settings );
			$question->correct_answer = $this->decode_correct_answer_json( $question->correct_answer_json );
		}

		return $question;
	}

	/**
	 * Add new question.
	 *
	 * @since 1.0.0
	 *
	 * @param array $data Question data.
	 *
	 * @return int|false Question ID on success, false on failure.
	 */
	public function add_question( $data ) {
		$defaults = array(
			'quiz_id'              => 0,
			'question_type'        => '',
			'question_text'        => '',
			'question_description' => '',
			'explanation'          => '',
			'points'               => 1.00,
			'order_index'          => 0,
			'is_required'          => 1,
			'settings'             => array(),
			'options_json'         => null,
			'correct_answer_json'  => null,
			'media_type'           => 'none',
			'media_url'            => '',
		);

		$data = wp_parse_args( $data, $defaults );

		// Serialize settings.
		$data['settings'] = maybe_serialize( $data['settings'] );

		// Encode options_json if provided as array.
		if ( isset( $data['options_json'] ) && is_array( $data['options_json'] ) ) {
			$data['options_json'] = wp_json_encode( $data['options_json'] );
		} elseif ( ! isset( $data['options_json'] ) || null === $data['options_json'] ) {
			$data['options_json'] = null;
		}

		// Encode correct_answer_json if provided as array.
		if ( isset( $data['correct_answer_json'] ) && is_array( $data['correct_answer_json'] ) ) {
			$data['correct_answer_json'] = wp_json_encode( $data['correct_answer_json'] );
		} elseif ( ! isset( $data['correct_answer_json'] ) || null === $data['correct_answer_json'] ) {
			$data['correct_answer_json'] = null;
		}

		// Set order index if not provided.
		if ( ! $data['order_index'] ) {
			$data['order_index'] = $this->get_next_order_index( $data['quiz_id'] );
		}

		return $this->insert(
			$data,
			array( '%d', '%s', '%s', '%s', '%s', '%f', '%d', '%d', '%s', '%s', '%s', '%s', '%s' )
		);
	}

	/**
	 * Update question.
	 *
	 * @since 1.0.0
	 *
	 * @param int   $question_id Question ID.
	 * @param array $data        Question data.
	 *
	 * @return bool
	 */
	public function update_question( $question_id, $data ) {
		// Serialize settings if provided.
		if ( isset( $data['settings'] ) ) {
			$data['settings'] = maybe_serialize( $data['settings'] );
		}

		// Encode options_json if provided as array.
		if ( isset( $data['options_json'] ) && is_array( $data['options_json'] ) ) {
			$data['options_json'] = wp_json_encode( $data['options_json'] );
		}

		// Encode correct_answer_json if provided as array.
		if ( isset( $data['correct_answer_json'] ) && is_array( $data['correct_answer_json'] ) ) {
			$data['correct_answer_json'] = wp_json_encode( $data['correct_answer_json'] );
		}

		return $this->update(
			$data,
			array( 'id' => $question_id ),
			null,
			array( '%d' )
		);
	}

	/**
	 * Delete question.
	 *
	 * @since 1.0.0
	 *
	 * @param int $question_id Question ID.
	 *
	 * @return bool
	 */
	public function delete_question( $question_id ) {
		// Delete question (options are stored in options_json, no separate deletion needed).
		return $this->delete(
			array( 'id' => $question_id ),
			array( '%d' )
		);
	}

	/**
	 * Delete all questions for a quiz.
	 *
	 * @since 1.0.0
	 *
	 * @param int $quiz_id Quiz ID.
	 *
	 * @return bool
	 */
	public function delete_quiz_questions( $quiz_id ) {
		// Delete all questions (options are stored in options_json, no separate deletion needed).
		return $this->delete(
			array( 'quiz_id' => $quiz_id ),
			array( '%d' )
		);
	}

	/**
	 * Decode options_json to array.
	 *
	 * @since 1.0.0
	 *
	 * @param string|null $options_json JSON string or null.
	 *
	 * @return array Decoded options array.
	 */
	private function decode_options_json( $options_json ) {
		if ( empty( $options_json ) ) {
			return array();
		}

		$decoded = json_decode( $options_json, true );
		return is_array( $decoded ) ? $decoded : array();
	}

	/**
	 * Decode correct_answer_json to array.
	 *
	 * @since 1.0.0
	 *
	 * @param string|null $correct_answer_json JSON string or null.
	 *
	 * @return array Decoded correct answer structure.
	 */
	private function decode_correct_answer_json( $correct_answer_json ) {
		if ( empty( $correct_answer_json ) ) {
			return array();
		}

		$decoded = json_decode( $correct_answer_json, true );
		return is_array( $decoded ) ? $decoded : array();
	}

	/**
	 * Get next order index for questions.
	 *
	 * @since 1.0.0
	 *
	 * @param int $quiz_id Quiz ID.
	 *
	 * @return int
	 */
	private function get_next_order_index( $quiz_id ) {
		$sql       = "SELECT MAX(order_index) FROM {$this->table_name} WHERE quiz_id = %d";
		$result    = $this->get_row( $sql, array( $quiz_id ) );
		$max_order = $result ? array_values( (array) $result )[0] : 0;

		return intval( $max_order ) + 1;
	}


	/**
	 * Get questions count for a quiz.
	 *
	 * @since 1.0.0
	 *
	 * @param int $quiz_id Quiz ID.
	 *
	 * @return int
	 */
	public function get_questions_count( $quiz_id ) {
		$sql    = "SELECT COUNT(*) FROM {$this->table_name} WHERE quiz_id = %d";
		$result = $this->get_row( $sql, array( $quiz_id ) );

		return $result ? intval( array_values( (array) $result )[0] ) : 0;
	}
}
