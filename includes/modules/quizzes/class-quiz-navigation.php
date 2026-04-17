<?php
/**
 * Quiz Navigation
 *
 * Handles quiz navigation features including progress tracking,
 * question bookmarks, and navigation controls.
 *
 * @package SkillPulse_LMS
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Quiz Navigation class.
 *
 * Provides enhanced navigation features for quiz taking including
 * progress indicators, question bookmarking, and navigation controls.
 *
 * @since 1.0.0
 */
class SkillPulse_LMS_Quiz_Navigation {

	/**
	 * Class instance.
	 *
	 * @since 1.0.0
	 * @var SkillPulse_LMS_Quiz_Navigation|null $instance
	 */
	private static $instance = null;

	/**
	 * Get the instance of this class.
	 *
	 * @since 1.0.0
	 *
	 * @return SkillPulse_LMS_Quiz_Navigation The singleton instance.
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
	 */
	private function __construct() {
		// Private constructor for singleton pattern.
	}

	/**
	 * Calculate quiz progress.
	 *
	 * @param array $questions All quiz questions.
	 * @param array $answers   Current answers.
	 *
	 * @since 1.0.0
	 *
	 * @return array Progress information.
	 */
	public function calculate_progress( $questions, $answers ) {
		$total_questions    = count( $questions );
		$answered_questions = 0;
		$progress_items     = array();

		foreach ( $questions as $index => $question ) {
			$question_id = $question['id'];
			$is_answered = isset( $answers[ $question_id ] ) &&
							! empty( $answers[ $question_id ] );

			if ( $is_answered ) {
				++$answered_questions;
			}

			$progress_items[] = array(
				'question_number' => $index + 1,
				'question_id'     => $question_id,
				'is_answered'     => $is_answered,
				'question_type'   => $question['question_type'],
				'question_title'  => wp_trim_words( wp_strip_all_tags( $question['question_text'] ), 8, '...' ),
			);
		}

		$completion_percentage = $total_questions > 0 ? round( ( $answered_questions / $total_questions ) * 100, 1 ) : 0;

		return array(
			'total_questions'       => $total_questions,
			'answered_questions'    => $answered_questions,
			'unanswered_questions'  => $total_questions - $answered_questions,
			'completion_percentage' => $completion_percentage,
			'progress_items'        => $progress_items,
		);
	}

	/**
	 * Get question navigation data.
	 *
	 * @param array $questions All quiz questions.
	 * @param int   $current_question Current question index.
	 * @param array $bookmarks Question bookmarks.
	 *
	 * @since 1.0.0
	 *
	 * @return array Navigation data.
	 */
	public function get_navigation_data( $questions, $current_question, $bookmarks = array() ) {
		$total_questions = count( $questions );
		$navigation_data = array();

		for ( $i = 0; $i < $total_questions; $i++ ) {
			$question          = $questions[ $i ];
			$navigation_data[] = array(
				'index'         => $i,
				'number'        => $i + 1,
				'question_id'   => $question['id'],
				'title'         => wp_trim_words( wp_strip_all_tags( $question['question_text'] ), 6, '...' ),
				'is_current'    => $i === $current_question,
				'is_bookmarked' => in_array( $question['id'], $bookmarks, true ),
				'type'          => $question['question_type'],
			);
		}

		return array(
			'questions'        => $navigation_data,
			'total_questions'  => $total_questions,
			'current_question' => $current_question,
			'has_previous'     => $current_question > 0,
			'has_next'         => $current_question < ( $total_questions - 1 ),
			'previous_index'   => $current_question > 0 ? $current_question - 1 : null,
			'next_index'       => $current_question < ( $total_questions - 1 ) ? $current_question + 1 : null,
		);
	}

	/**
	 * Add question bookmark.
	 *
	 * @param int   $question_id Question ID.
	 * @param array $bookmarks   Current bookmarks array.
	 *
	 * @since 1.0.0
	 *
	 * @return array Updated bookmarks.
	 */
	public function add_bookmark( $question_id, $bookmarks = array() ) {
		$question_id = absint( $question_id );

		if ( ! in_array( $question_id, $bookmarks, true ) ) {
			$bookmarks[] = $question_id;
		}

		return $bookmarks;
	}

	/**
	 * Remove question bookmark.
	 *
	 * @param int   $question_id Question ID.
	 * @param array $bookmarks   Current bookmarks array.
	 *
	 * @since 1.0.0
	 *
	 * @return array Updated bookmarks.
	 */
	public function remove_bookmark( $question_id, $bookmarks = array() ) {
		$question_id = absint( $question_id );
		$key         = array_search( $question_id, $bookmarks, true );

		if ( false !== $key ) {
			unset( $bookmarks[ $key ] );
			$bookmarks = array_values( $bookmarks ); // Re-index array.
		}

		return $bookmarks;
	}

	/**
	 * Toggle question bookmark.
	 *
	 * @param int   $question_id Question ID.
	 * @param array $bookmarks   Current bookmarks array.
	 *
	 * @since 1.0.0
	 *
	 * @return array Updated bookmarks and action taken.
	 */
	public function toggle_bookmark( $question_id, $bookmarks = array() ) {
		$question_id = absint( $question_id );

		if ( in_array( $question_id, $bookmarks, true ) ) {
			$bookmarks = $this->remove_bookmark( $question_id, $bookmarks );
			$action    = 'removed';
		} else {
			$bookmarks = $this->add_bookmark( $question_id, $bookmarks );
			$action    = 'added';
		}

		return array(
			'bookmarks' => $bookmarks,
			'action'    => $action,
		);
	}

	/**
	 * Get review summary data.
	 *
	 * @param array $questions All quiz questions.
	 * @param array $answers   Current answers.
	 * @param array $bookmarks Question bookmarks.
	 *
	 * @since 1.0.0
	 *
	 * @return array Review summary.
	 */
	public function get_review_summary( $questions, $answers, $bookmarks = array() ) {
		$answered   = array();
		$unanswered = array();
		$bookmarked = array();

		foreach ( $questions as $index => $question ) {
			$question_id   = $question['id'];
			$is_answered   = isset( $answers[ $question_id ] ) &&
							! empty( $answers[ $question_id ] );
			$is_bookmarked = in_array( $question_id, $bookmarks, true );

			$question_summary = array(
				'index'         => $index,
				'number'        => $index + 1,
				'id'            => $question_id,
				'title'         => wp_trim_words( wp_strip_all_tags( $question['question_text'] ), 10, '...' ),
				'type'          => $question['question_type'],
				'is_answered'   => $is_answered,
				'is_bookmarked' => $is_bookmarked,
			);

			if ( $is_answered ) {
				$answered[] = $question_summary;
			} else {
				$unanswered[] = $question_summary;
			}

			if ( $is_bookmarked ) {
				$bookmarked[] = $question_summary;
			}
		}

		return array(
			'answered'   => $answered,
			'unanswered' => $unanswered,
			'bookmarked' => $bookmarked,
			'counts'     => array(
				'total'      => count( $questions ),
				'answered'   => count( $answered ),
				'unanswered' => count( $unanswered ),
				'bookmarked' => count( $bookmarked ),
			),
		);
	}

	/**
	 * Validate navigation to specific question.
	 *
	 * @param int   $target_index Target question index.
	 * @param array $questions    All quiz questions.
	 *
	 * @since 1.0.0
	 *
	 * @return bool|WP_Error True if navigation is valid, WP_Error otherwise.
	 */
	public function validate_navigation( $target_index, $questions ) {
		$target_index    = absint( $target_index );
		$total_questions = count( $questions );

		if ( $target_index < 0 ) {
			return new WP_Error(
				'invalid_navigation',
				__( 'Cannot navigate to negative question index.', 'skillpulse-lms' )
			);
		}

		if ( $target_index >= $total_questions ) {
			return new WP_Error(
				'invalid_navigation',
				sprintf(
					/* translators: 1: Target index, 2: Total questions */
					__( 'Cannot navigate to question %1$d. Quiz has only %2$d questions.', 'skillpulse-lms' ),
					$target_index + 1,
					$total_questions
				)
			);
		}

		return true;
	}

	/**
	 * Get navigation shortcuts for quick access.
	 *
	 * @param array $questions All quiz questions.
	 * @param array $answers   Current answers.
	 * @param array $bookmarks Question bookmarks.
	 *
	 * @since 1.0.0
	 *
	 * @return array Navigation shortcuts.
	 */
	public function get_navigation_shortcuts( $questions, $answers, $bookmarks = array() ) {
		$shortcuts = array(
			'first_unanswered' => null,
			'next_unanswered'  => null,
			'first_bookmarked' => null,
			'next_bookmarked'  => null,
		);

		$unanswered_indices = array();
		$bookmarked_indices = array();

		foreach ( $questions as $index => $question ) {
			$question_id = $question['id'];
			$is_answered = isset( $answers[ $question_id ] ) &&
							! empty( $answers[ $question_id ] );

			if ( ! $is_answered ) {
				$unanswered_indices[] = $index;
			}

			if ( in_array( $question_id, $bookmarks, true ) ) {
				$bookmarked_indices[] = $index;
			}
		}

		// Set shortcuts.
		if ( ! empty( $unanswered_indices ) ) {
			$shortcuts['first_unanswered'] = $unanswered_indices[0];
			$shortcuts['next_unanswered']  = $unanswered_indices[0]; // Could be enhanced for current position.
		}

		if ( ! empty( $bookmarked_indices ) ) {
			$shortcuts['first_bookmarked'] = $bookmarked_indices[0];
			$shortcuts['next_bookmarked']  = $bookmarked_indices[0]; // Could be enhanced for current position.
		}

		return $shortcuts;
	}
}
