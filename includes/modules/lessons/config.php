<?php
/**
 * Lessons Configuration
 *
 * Replaces lesson-settings-config.json with PHP configuration for better performance,
 * translation support, and dynamic capabilities.
 *
 * @package SkillPulse_LMS
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Determine lesson ID for configuration loading.
$splms_lesson_id = 0;

// Method 1: Admin post edit context via GET parameter.
if ( isset( $_GET['post'] ) && is_numeric( $_GET['post'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Configuration file for admin context, no data processing.
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Configuration file for admin context, no data processing.
	$splms_current_post_id = intval( $_GET['post'] );
	$splms_lesson_post     = get_post( $splms_current_post_id );
	if ( $splms_lesson_post && SPLMS_POST_TYPES['lesson'] === $splms_lesson_post->post_type ) {
		$splms_lesson_id = $splms_current_post_id;
	}
} elseif ( isset( $_REQUEST['item_id'] ) && is_numeric( $_REQUEST['item_id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Configuration file for admin context, no data processing.
	// Method 2: Dynamic config loading via item_id parameter.
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Configuration file for admin context, no data processing.
	$splms_item_id     = intval( $_REQUEST['item_id'] );
	$splms_lesson_post = get_post( $splms_item_id );
	if ( $splms_lesson_post && SPLMS_POST_TYPES['lesson'] === $splms_lesson_post->post_type ) {
		$splms_lesson_id = $splms_item_id;
	}
} elseif ( isset( $GLOBALS['post'] ) && SPLMS_POST_TYPES['lesson'] === $GLOBALS['post']->post_type ) {
	// Method 3: Global post context.
	$splms_lesson_id = $GLOBALS['post']->ID;
}

/**
 * Helper function to get lesson meta value with fallback to default.
 * Handles both grouped and individual meta storage patterns.
 * Returns default value for new posts (lesson_id = 0).
 *
 * @param int    $splms_lesson_id   Lesson post ID.
 * @param string $field_id    Meta field identifier.
 * @param string $group       Optional group name for grouped meta.
 * @param mixed  $default_val Default value to return if meta not found.
 * @return mixed Meta value or default value.
 */
function splms_get_lesson_field_value( $splms_lesson_id, $field_id, $group = '', $default_val = '' ) {
	// For new posts or invalid lesson ID, return default.
	if ( ! $splms_lesson_id || $splms_lesson_id <= 0 ) {
		return $default_val;
	}

	// Verify the post exists and is a lesson.
	$post = get_post( $splms_lesson_id );
	if ( ! $post || SPLMS_POST_TYPES['lesson'] !== $post->post_type ) {
		return $default_val;
	}

	// Handle grouped meta storage.
	if ( ! empty( $group ) ) {
		$meta_key   = '_splms_' . $group;
		$group_data = get_post_meta( $splms_lesson_id, $meta_key, true );

		if ( is_array( $group_data ) && isset( $group_data[ $field_id ] ) ) {
			return $group_data[ $field_id ];
		}
	} else {
		// Handle individual meta storage.
		$meta_key = '_splms_' . $field_id;
		$value    = get_post_meta( $splms_lesson_id, $meta_key, true );

		if ( '' !== $value ) {
			return $value;
		}
	}

	return $default_val;
}


// FREE: Content Settings (text/video lesson type, duration, video URL).
$splms_content_settings = array(
	'id'     => 'content_settings',
	'title'  => __( 'Content Settings', 'skillpulse-lms' ),
	'icon'   => 'media-document',
	'fields' => array(
		array(
			'id'      => 'lesson_type',
			'type'    => 'select',
			'label'   => __( 'Lesson Type', 'skillpulse-lms' ),
			'help'    => __( 'Choose the primary content type for this lesson', 'skillpulse-lms' ),
			'default' => 'text',
			'value'   => splms_get_lesson_field_value( $splms_lesson_id, 'lesson_type', '', 'text' ),
			'column'  => 'half',
			'icon'    => 'media-document',
			'options' => array(
				array(
					'label' => __( 'Text Lesson', 'skillpulse-lms' ),
					'value' => 'text',
				),
				array(
					'label' => __( 'Video Lesson', 'skillpulse-lms' ),
					'value' => 'video',
				),
			),
		),
		array(
			'id'          => 'lesson_duration',
			'type'        => 'number',
			'label'       => __( 'Lesson Duration (minutes)', 'skillpulse-lms' ),
			'help'        => __( 'Expected time to complete this lesson', 'skillpulse-lms' ),
			'default'     => 30,
			'value'       => splms_get_lesson_field_value( $splms_lesson_id, 'lesson_duration', '', 30 ),
			'column'      => 'half',
			'icon'        => 'clock',
			'min'         => 1,
			'max'         => 999,
			'placeholder' => '30',
		),
		array(
			'id'          => 'lesson_video_url',
			'type'        => 'media-url',
			'label'       => __( 'Video URL', 'skillpulse-lms' ),
			'help'        => __( 'Select a video from Media Library or paste an external URL (YouTube, Vimeo, MP4)', 'skillpulse-lms' ),
			'default'     => '',
			'column'      => 'half',
			'icon'        => 'video-alt3',
			'placeholder' => __( 'https://youtube.com/watch?v=...', 'skillpulse-lms' ),
			'value'       => splms_get_lesson_field_value( $splms_lesson_id, 'lesson_video_url', '' ),
			'conditional' => array(
				'key'   => 'lesson_type',
				'value' => 'video',
			),
		),
		array(
			'id'          => 'lesson_completion_required',
			'type'        => 'number',
			'label'       => __( 'Completion Requirement (%)', 'skillpulse-lms' ),
			'help'        => __( 'Students must complete this percentage of content before marking as complete', 'skillpulse-lms' ),
			'default'     => 100,
			'placeholder' => __( '100', 'skillpulse-lms' ),
			'min'         => 1,
			'max'         => 100,
			'icon'        => 'chart-pie',
			'column'      => 'half',
			'value'       => splms_get_lesson_field_value( $splms_lesson_id, 'lesson_completion_required', '' ),
			'conditional' => array(
				'key'   => 'lesson_type',
				'value' => 'video',
			),
		),
	),
);


// FREE: Completion Settings (completion type, required time).
$splms_completion_settings = array(
	'id'     => 'completion_settings',
	'title'  => __( 'Completion Settings', 'skillpulse-lms' ),
	'icon'   => 'yes-alt',
	'fields' => array(
		array(
			'id'      => 'completion_type',
			'type'    => 'select',
			'label'   => __( 'Completion Type', 'skillpulse-lms' ),
			'help'    => __( 'How should lesson completion be tracked?', 'skillpulse-lms' ),
			'default' => 'manual',
			'value'   => splms_get_lesson_field_value( $splms_lesson_id, 'completion_type', 'lesson_completion_settings', 'manual' ),
			'column'  => 'half',
			'icon'    => 'yes-alt',
			'group'   => 'lesson_completion_settings',
			'options' => array(
				array(
					'label' => __( 'Manual Completion', 'skillpulse-lms' ),
					'value' => 'manual',
				),
				array(
					'label' => __( 'Auto Complete', 'skillpulse-lms' ),
					'value' => 'auto',
				),
				array(
					'label' => __( 'Time Based', 'skillpulse-lms' ),
					'value' => 'time_based',
				),
			),
		),
		array(
			'id'          => 'required_time',
			'type'        => 'number',
			'label'       => __( 'Required Time (seconds)', 'skillpulse-lms' ),
			'help'        => __( 'Minimum time student must spend on lesson', 'skillpulse-lms' ),
			'default'     => 300,
			'column'      => 'half',
			'icon'        => 'clock',
			'min'         => 1,
			'max'         => 3600,
			'placeholder' => '300',
			'group'       => 'lesson_completion_settings',
			'conditional' => array(
				'key'   => 'completion_type',
				'value' => 'time_based',
			),
		),
	),
);





// Build CLI removes Pro variables from this array for free version.
return array(
	'sections' => array(
		$splms_content_settings,
		$splms_completion_settings,
	),
);
