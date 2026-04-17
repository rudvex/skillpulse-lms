<?php
/**
 * General Quiz Functions
 *
 * This file contains all general quiz-related functions that can be reused
 * across the SkillPulse LMS plugin.
 *
 * @since   1.0.0
 * @package SkillPulse_LMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * ============================================================================
 * QUIZ SETTINGS FUNCTIONS
 * ============================================================================
 */

/**
 * Get quiz settings with defaults.
 *
 * @param int $quiz_id Quiz ID.
 *
 * @since 1.0.0
 *
 * @return array Quiz settings.
 */
function splms_get_quiz_settings( $quiz_id = null ) {
	if ( ! $quiz_id ) {
		$quiz_id = get_the_ID();
	}

	$settings = SkillPulse_LMS_Quizzes::get_instance()->get_quiz_settings( $quiz_id );

	return apply_filters( 'splms_quiz_settings', $settings, $quiz_id );
}

/**
 * ============================================================================
 * QUIZ METADATA FUNCTIONS
 * ============================================================================
 */

/**
 * Get quiz type.
 *
 * @param int $quiz_id Quiz ID.
 *
 * @return string Quiz type.
 */
function splms_get_quiz_type( $quiz_id = null ) {
	if ( ! $quiz_id ) {
		$quiz_id = get_the_ID();
	}

	$settings = splms_get_quiz_settings( $quiz_id );

	return isset( $settings['quiz_type'] ) ? $settings['quiz_type'] : 'graded';
}


/**
 * Get quiz questions.
 *
 * @param int $quiz_id Quiz ID.
 *
 * @return array Quiz questions.
 */
function splms_get_quiz_questions( $quiz_id = null ) {
	if ( ! $quiz_id ) {
		$quiz_id = get_the_ID();
	}

	$quizzes_instance = SkillPulse_LMS_Quizzes::get_instance();

	return $quizzes_instance->get_quiz_questions( $quiz_id );
}

/**
 * Get quiz questions count.
 *
 * @param int $quiz_id Quiz ID.
 *
 * @return int Questions count.
 */
function splms_get_quiz_questions_count( $quiz_id = null ) {
	if ( ! $quiz_id ) {
		$quiz_id = get_the_ID();
	}

	$questions = splms_get_quiz_questions( $quiz_id );

	return count( $questions );
}


/**
 * ============================================================================
 * QUIZ ATTEMPTS FUNCTIONS
 * ============================================================================
 */

/**
 * Get user quiz attempts.
 *
 * @param int $user_id User ID.
 * @param int $quiz_id Quiz ID.
 *
 * @return array Quiz attempts.
 */
function splms_get_user_quiz_attempts( $user_id, $quiz_id = null ) {
	$quizzes_instance = SkillPulse_LMS_Quizzes::get_instance();

	return $quizzes_instance->get_user_quiz_attempts( $user_id, $quiz_id );
}

/**
 * Get user best quiz score.
 *
 * @param int $user_id User ID.
 * @param int $quiz_id Quiz ID.
 *
 * @return float Best score percentage.
 */
function splms_get_user_best_quiz_score( $user_id, $quiz_id = null ) {
	if ( ! $quiz_id ) {
		$quiz_id = get_the_ID();
	}

	$attempts = splms_get_user_quiz_attempts( $user_id, $quiz_id );

	if ( empty( $attempts ) ) {
		return 0;
	}

	$best_score = 0;
	foreach ( $attempts as $attempt ) {
		if ( $attempt->score > $best_score ) {
			$best_score = $attempt->score;
		}
	}

	return $best_score;
}

/**
 * ============================================================================
 * QUIZ COURSE FUNCTIONS
 * ============================================================================
 */

/**
 * Get quiz course ID.
 *
 * @param int $quiz_id Quiz ID.
 *
 * @return int|null Course ID.
 */
function splms_get_quiz_course( $quiz_id = null ) {
	if ( ! $quiz_id ) {
		$quiz_id = get_the_ID();
	}

	// Use database relationships to find the course for this quiz.
	$relationships_query = SkillPulse_LMS_Relationships_Query::get_instance();
	$parents             = $relationships_query->get_parents( $quiz_id );

	if ( ! empty( $parents ) ) {
		foreach ( $parents as $parent ) {
			return SkillPulse_LMS_Course_Items_Query::get_instance()->get_item_course_id( $parent->parent_id );
		}
	}

	return null;
}

/**
 * ============================================================================
 * QUIZ DISPLAY FUNCTIONS
 * ============================================================================
 */

/**
 * Get quiz navigation (previous/next items in course)
 *
 * @param int $quiz_id Quiz ID.
 * @param int $user_id User ID.
 *
 * @return array|null Navigation data.
 */
function splms_get_quiz_navigation( $quiz_id, $user_id = null ) {
	if ( ! $user_id ) {
		$user_id = get_current_user_id();
	}

	if ( ! $user_id ) {
		return null;
	}

	$quizzes_instance = SkillPulse_LMS_Quizzes::get_instance();

	return $quizzes_instance->get_quiz_navigation( $quiz_id, $user_id );
}
