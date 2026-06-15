<?php
/**
 * Quizzes Configuration
 *
 * Replaces quiz-settings-config.json with PHP configuration for better performance,
 * translation support, and dynamic capabilities.
 *
 * @package SPLMS
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Determine quiz ID for configuration loading.
$splms_quiz_id = 0;

// Method 1: Admin post edit context via GET parameter.
if ( isset( $_GET['post'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$splms_post_id_raw = sanitize_text_field( wp_unslash( $_GET['post'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( is_numeric( $splms_post_id_raw ) ) {
		$splms_current_post_id = intval( $splms_post_id_raw );
		$splms_quiz_post       = get_post( $splms_current_post_id );
		if ( $splms_quiz_post && SPLMS_POST_TYPES['quiz'] === $splms_quiz_post->post_type ) {
			$splms_quiz_id = $splms_current_post_id;
		}
	}
} elseif ( isset( $_REQUEST['item_id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	// Method 2: Dynamic config loading via item_id parameter.
	$splms_item_id_raw = sanitize_text_field( wp_unslash( $_REQUEST['item_id'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( is_numeric( $splms_item_id_raw ) ) {
		$splms_item_id   = intval( $splms_item_id_raw );
		$splms_quiz_post = get_post( $splms_item_id );
		if ( $splms_quiz_post && SPLMS_POST_TYPES['quiz'] === $splms_quiz_post->post_type ) {
			$splms_quiz_id = $splms_item_id;
		}
	}
} elseif ( isset( $GLOBALS['post'] ) && SPLMS_POST_TYPES['quiz'] === $GLOBALS['post']->post_type ) {
	// Method 3: Global post context.
	$splms_quiz_id = $GLOBALS['post']->ID;
}

/**
 * Helper function to get quiz meta value with fallback to default.
 * Handles both grouped and individual meta storage patterns.
 * Returns default value for new posts (quiz_id = 0).
 *
 * @param int    $splms_quiz_id     Quiz post ID.
 * @param string $field_id    Meta field identifier.
 * @param string $group       Optional group name for grouped meta.
 * @param mixed  $default_val Default value to return if meta not found.
 * @return mixed Meta value or default value.
 */
function splms_get_quiz_field_value( $splms_quiz_id, $field_id, $group = '', $default_val = '' ) {
	// For new posts or invalid quiz ID, return default.
	if ( ! $splms_quiz_id || $splms_quiz_id <= 0 ) {
		return $default_val;
	}

	// Verify the post exists and is a quiz.
	$post = get_post( $splms_quiz_id );
	if ( ! $post || SPLMS_POST_TYPES['quiz'] !== $post->post_type ) {
		return $default_val;
	}

	// Handle grouped meta storage.
	if ( ! empty( $group ) ) {
		$meta_key   = '_splms_' . $group;
		$group_data = get_post_meta( $splms_quiz_id, $meta_key, true );

		if ( is_array( $group_data ) && isset( $group_data[ $field_id ] ) ) {
			return $group_data[ $field_id ];
		}
	} else {
		// Handle individual meta storage.
		$meta_key = '_splms_' . $field_id;
		$value    = get_post_meta( $splms_quiz_id, $meta_key, true );

		if ( '' !== $value ) {
			return $value;
		}
	}

	return $default_val;
}


// FREE: Quiz Basic Settings.
$splms_quiz_basic_settings = array(
	'id'     => 'basic_settings',
	'title'  => __( 'Basic Settings', 'skillpulse-lms' ),
	'icon'   => 'admin-settings',
	'fields' => array(
		array(
			'id'      => 'quiz_type',
			'type'    => 'select',
			'label'   => __( 'Quiz Type', 'skillpulse-lms' ),
			'help'    => __( 'Choose the type of quiz assessment', 'skillpulse-lms' ),
			'default' => 'graded',
			'value'   => splms_get_quiz_field_value( $splms_quiz_id, 'quiz_type', 'quiz_basic_settings', 'graded' ),
			'column'  => 'half',
			'icon'    => 'category',
			'group'   => 'quiz_basic_settings',
			'options' => array(
				array(
					'label' => __( 'Graded Quiz', 'skillpulse-lms' ),
					'value' => 'graded',
				),
				array(
					'label' => __( 'Practice Quiz', 'skillpulse-lms' ),
					'value' => 'practice',
				),
				array(
					'label' => __( 'Survey', 'skillpulse-lms' ),
					'value' => 'survey',
				),
			),
		),
		array(
			'id'          => 'passing_grade',
			'type'        => 'number',
			'label'       => __( 'Passing Grade (%)', 'skillpulse-lms' ),
			'help'        => __( 'Minimum percentage score required to pass', 'skillpulse-lms' ),
			'default'     => 70,
			'column'      => 'half',
			'icon'        => 'star-filled',
			'min'         => 0,
			'max'         => 100,
			'placeholder' => '70',
			'group'       => 'quiz_grading_settings',
		),
	),
);


// FREE: Quiz Display Settings.
$splms_quiz_display_settings = array(
	'id'     => 'display_behavior',
	'title'  => __( 'Display & Behavior', 'skillpulse-lms' ),
	'icon'   => 'visibility',
	'fields' => array(
		array(
			'id'      => 'show_correct_answers',
			'type'    => 'toggle',
			'label'   => __( 'Show Correct Answers', 'skillpulse-lms' ),
			'help'    => __( 'Display correct answers after quiz completion', 'skillpulse-lms' ),
			'default' => true,
			'column'  => 'half',
			'icon'    => 'visibility',
			'group'   => 'quiz_display_settings',
		),
		array(
			'id'          => 'show_correct_answers_timing',
			'type'        => 'select',
			'label'       => __( 'Show Answers Timing', 'skillpulse-lms' ),
			'help'        => __( 'When should correct answers be shown?', 'skillpulse-lms' ),
			'default'     => 'after_completion',
			'column'      => 'half',
			'icon'        => 'schedule',
			'group'       => 'quiz_display_settings',
			'options'     => array(
				array(
					'label' => __( 'After Completion', 'skillpulse-lms' ),
					'value' => 'after_completion',
				),
				array(
					'label' => __( 'After Passing', 'skillpulse-lms' ),
					'value' => 'after_passing',
				),
				array(
					'label' => __( 'After All Attempts', 'skillpulse-lms' ),
					'value' => 'after_all_attempts',
				),
			),
			'conditional' => array(
				'key'   => 'show_correct_answers',
				'value' => true,
			),
		),
		array(
			'id'          => 'question_per_page',
			'type'        => 'number',
			'label'       => __( 'Questions Per Page', 'skillpulse-lms' ),
			'help'        => __( 'Number of questions to display per page (0 = all on one page)', 'skillpulse-lms' ),
			'default'     => 1,
			'column'      => 'half',
			'icon'        => 'format-aside',
			'min'         => 0,
			'max'         => 50,
			'placeholder' => '1',
			'group'       => 'quiz_behavior_settings',
		),
		array(
			'id'      => 'allow_navigation',
			'type'    => 'toggle',
			'label'   => __( 'Allow Navigation', 'skillpulse-lms' ),
			'help'    => __( 'Allow students to navigate between questions', 'skillpulse-lms' ),
			'default' => true,
			'column'  => 'half',
			'icon'    => 'controls-forward',
			'group'   => 'quiz_behavior_settings',
		),
	),
);



// FREE: Quiz Feedback.
$splms_quiz_feedback_settings = array(
	'id'     => 'feedback_results',
	'title'  => __( 'Feedback & Results', 'skillpulse-lms' ),
	'icon'   => 'feedback',
	'fields' => array(
		array(
			'id'      => 'enable_feedback',
			'type'    => 'toggle',
			'label'   => __( 'Enable Feedback', 'skillpulse-lms' ),
			'help'    => __( 'Show feedback messages for answers', 'skillpulse-lms' ),
			'default' => true,
			'column'  => 'half',
			'icon'    => 'feedback',
			'group'   => 'quiz_feedback_settings',
		),
		array(
			'id'          => 'feedback_correct',
			'type'        => 'textarea',
			'label'       => __( 'Correct Answer Feedback', 'skillpulse-lms' ),
			'help'        => __( 'Message shown for correct answers', 'skillpulse-lms' ),
			'default'     => __( 'Correct! Well done.', 'skillpulse-lms' ),
			'column'      => 'half',
			'icon'        => 'yes-alt',
			'placeholder' => __( 'Enter feedback for correct answers', 'skillpulse-lms' ),
			'rows'        => 3,
			'group'       => 'quiz_feedback_settings',
			'conditional' => array(
				'key'   => 'enable_feedback',
				'value' => true,
			),
		),
		array(
			'id'          => 'feedback_incorrect',
			'type'        => 'textarea',
			'label'       => __( 'Incorrect Answer Feedback', 'skillpulse-lms' ),
			'help'        => __( 'Message shown for incorrect answers', 'skillpulse-lms' ),
			'default'     => __( 'Incorrect. Please review the material and try again.', 'skillpulse-lms' ),
			'column'      => 'half',
			'icon'        => 'dismiss',
			'placeholder' => __( 'Enter feedback for incorrect answers', 'skillpulse-lms' ),
			'rows'        => 3,
			'group'       => 'quiz_feedback_settings',
			'conditional' => array(
				'key'   => 'enable_feedback',
				'value' => true,
			),
		),
	),
);


// Build CLI removes Pro variables from this array for free version.
return array(
	'sections' => array(
		$splms_quiz_basic_settings,
		$splms_quiz_display_settings,
		$splms_quiz_feedback_settings,
	),
);
