<?php
/**
 * Sections Configuration
 *
 * Replaces section-settings-config.json with PHP configuration for better performance,
 * translation support, and dynamic capabilities.
 *
 * @package SkillPulse_LMS
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Determine section ID for configuration loading.
$section_id = 0;

// Method 1: Admin post edit context via GET parameter.
if ( isset( $_GET['post'] ) && is_numeric( $_GET['post'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Configuration file for admin context, no data processing.
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Configuration file for admin context, no data processing.
	$current_post_id = intval( $_GET['post'] );
	$section_post    = get_post( $current_post_id );
	if ( $section_post && SPLMS_POST_TYPES['section'] === $section_post->post_type ) {
		$section_id = $current_post_id;
	}
} elseif ( isset( $_REQUEST['item_id'] ) && is_numeric( $_REQUEST['item_id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Configuration file for admin context, no data processing.
	// Method 2: Dynamic config loading via item_id parameter.
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Configuration file for admin context, no data processing.
	$item_id      = intval( $_REQUEST['item_id'] );
	$section_post = get_post( $item_id );
	if ( $section_post && SPLMS_POST_TYPES['section'] === $section_post->post_type ) {
		$section_id = $item_id;
	}
} elseif ( isset( $GLOBALS['post'] ) && SPLMS_POST_TYPES['section'] === $GLOBALS['post']->post_type ) {
	// Method 3: Global post context.
	$section_id = $GLOBALS['post']->ID;
}

/**
 * Helper function to get section meta value with fallback to default.
 * Handles both grouped and individual meta storage patterns.
 * Returns default value for new posts (section_id = 0).
 *
 * @param int    $section_id  Section post ID.
 * @param string $field_id    Meta field identifier.
 * @param string $group       Optional group name for grouped meta.
 * @param mixed  $default_val Default value to return if meta not found.
 * @return mixed Meta value or default value.
 */
function splms_get_section_field_value( $section_id, $field_id, $group = '', $default_val = '' ) {
	// For new posts or invalid section ID, return default.
	if ( ! $section_id || $section_id <= 0 ) {
		return $default_val;
	}

	// Verify the post exists and is a section.
	$post = get_post( $section_id );
	if ( ! $post || SPLMS_POST_TYPES['section'] !== $post->post_type ) {
		return $default_val;
	}

	// Handle grouped meta storage.
	if ( ! empty( $group ) ) {
		$meta_key   = '_splms_' . $group;
		$group_data = get_post_meta( $section_id, $meta_key, true );

		if ( is_array( $group_data ) && isset( $group_data[ $field_id ] ) ) {
			return $group_data[ $field_id ];
		}
	} else {
		// Handle individual meta storage.
		$meta_key = '_splms_' . $field_id;
		$value    = get_post_meta( $section_id, $meta_key, true );

		if ( '' !== $value ) {
			return $value;
		}
	}

	return $default_val;
}


$sections_config = array(
	array(
		'id'     => 'general_settings',
		'title'  => __( 'General Settings', 'skillpulse-lms' ),
		'icon'   => 'admin-settings',
		'fields' => array(
			array(
				'id'          => 'duration',
				'type'        => 'text',
				'label'       => __( 'Duration', 'skillpulse-lms' ),
				'help'        => __( 'Estimated time to complete this section (e.g., 2 hours, 90 minutes)', 'skillpulse-lms' ),
				'default'     => '',
				'value'       => splms_get_section_field_value( $section_id, 'duration', 'section_settings', '' ),
				'placeholder' => __( '2 hours', 'skillpulse-lms' ),
				'column'      => 'half',
				'icon'        => 'clock',
				'group'       => 'section_settings',
			),
			array(
				'id'      => 'difficulty_level',
				'type'    => 'select',
				'label'   => __( 'Difficulty Level', 'skillpulse-lms' ),
				'help'    => __( 'Select the difficulty level for this section', 'skillpulse-lms' ),
				'default' => 'beginner',
				'value'   => splms_get_section_field_value( $section_id, 'difficulty_level', 'section_settings', 'beginner' ),
				'column'  => 'half',
				'icon'    => 'star-filled',
				'group'   => 'section_settings',
				'options' => array(
					array(
						'label' => __( 'Beginner', 'skillpulse-lms' ),
						'value' => 'beginner',
					),
					array(
						'label' => __( 'Intermediate', 'skillpulse-lms' ),
						'value' => 'intermediate',
					),
					array(
						'label' => __( 'Advanced', 'skillpulse-lms' ),
						'value' => 'advanced',
					),
					array(
						'label' => __( 'Expert', 'skillpulse-lms' ),
						'value' => 'expert',
					),
				),
			),
		),
	),
);

return array(
	'sections' => $sections_config,
);
