<?php
/**
 * Course Configuration
 *
 * Replaces course-settings-config.json with PHP configuration for better performance,
 * translation support, and dynamic capabilities.
 *
 * @package SPLMS
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get current course ID - Priority: API item_id parameter > $_GET['post'] > $GLOBALS['post'].
$splms_course_id = 0;
// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Configuration file for admin context, no data processing.
if ( isset( $_REQUEST['item_id'] ) && is_numeric( $_REQUEST['item_id'] ) ) {
	// API parameter takes highest priority - verify it's a course.
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Configuration file for admin context, no data processing.
	$splms_item_id     = intval( $_REQUEST['item_id'] );
	$splms_course_post = get_post( $splms_item_id );
	if ( $splms_course_post && SPLMS_POST_TYPES['course'] === $splms_course_post->post_type ) {
		$splms_course_id = $splms_item_id;
	}
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Configuration file for admin context, no data processing.
} elseif ( isset( $_GET['post'] ) && is_numeric( $_GET['post'] ) ) {
	// WordPress admin edit post context.
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Configuration file for admin context, no data processing.
	$splms_current_post_id = intval( $_GET['post'] );
	$splms_course_post     = get_post( $splms_current_post_id );
	if ( $splms_course_post && SPLMS_POST_TYPES['course'] === $splms_course_post->post_type ) {
		$splms_course_id = $splms_current_post_id;
	}
} elseif ( isset( $GLOBALS['post'] ) && SPLMS_POST_TYPES['course'] === $GLOBALS['post']->post_type ) {
	// Global post context.
	$splms_course_id = $GLOBALS['post']->ID;
}

/**
 * Helper function to get course meta value with fallback to default.
 * Handles both grouped and individual meta storage patterns.
 * Returns default value for new posts (course_id = 0).
 *
 * @param int    $splms_course_id   Course post ID.
 * @param string $field_id    Meta field identifier.
 * @param string $group       Optional group name for grouped meta.
 * @param mixed  $default_val Default value to return if meta not found.
 * @return mixed Meta value or default value.
 */
function splms_get_course_field_value( $splms_course_id, $field_id, $group = '', $default_val = '' ) {
	// For new posts or invalid course ID, return default.
	if ( ! $splms_course_id || $splms_course_id <= 0 ) {
		return $default_val;
	}

	// Verify the post exists and is a course.
	$post = get_post( $splms_course_id );
	if ( ! $post || SPLMS_POST_TYPES['course'] !== $post->post_type ) {
		return $default_val;
	}

	// Handle grouped meta storage.
	if ( ! empty( $group ) ) {
		$meta_key   = '_splms_' . $group;
		$group_data = get_post_meta( $splms_course_id, $meta_key, true );

		if ( is_array( $group_data ) && isset( $group_data[ $field_id ] ) ) {
			return $group_data[ $field_id ];
		}
	} else {
		// Handle individual meta storage.
		$meta_key = '_splms_' . $field_id;
		$value    = get_post_meta( $splms_course_id, $meta_key, true );

		if ( '' !== $value ) {
			return $value;
		}
	}

	return $default_val;
}


// FREE: Course Access.
$splms_course_access = array(
	'id'          => 'course_access',
	'title'       => __( 'Course Access', 'skillpulse-lms' ),
	'icon'        => 'unlock',
	'description' => __( 'Configure how students can access this course.', 'skillpulse-lms' ),
	'fields'      => array(
		array(
			'id'      => 'course_access_type',
			'type'    => 'select',
			'label'   => __( 'Course Access Type', 'skillpulse-lms' ),
			'help'    => __( 'Define how students can discover and enroll in this course.', 'skillpulse-lms' ),
			'default' => 'public_free',
			'value'   => splms_get_course_field_value( $splms_course_id, 'course_access_type', 'course_access_settings', 'public_free' ),
			'column'  => 'full',
			'icon'    => 'admin-settings',
			'group'   => 'course_access_settings',
			'options' => array(
				array(
					'label'       => __( 'Public & Free - Anyone can enroll for free', 'skillpulse-lms' ),
					'value'       => 'public_free',
					'description' => __( 'Course is publicly visible and free to enroll', 'skillpulse-lms' ),
				),
			),
		),
	),
);



// FREE: Course Details.
$splms_course_details = array(
	'id'          => 'course_details',
	'title'       => __( 'Course Details', 'skillpulse-lms' ),
	'icon'        => 'book-alt',
	'description' => __( 'Configure course metadata and completion settings.', 'skillpulse-lms' ),
	'fields'      => array(
		array(
			'id'      => 'learning_method',
			'type'    => 'select',
			'label'   => __( 'Primary Learning Method', 'skillpulse-lms' ),
			'help'    => __( 'Select the main type of learning materials used.', 'skillpulse-lms' ),
			'default' => 'text',
			'value'   => splms_get_course_field_value( $splms_course_id, 'learning_method', 'course_content_settings', 'text' ),
			'column'  => 'half',
			'icon'    => 'text-page',
			'group'   => 'course_content_settings',
			'options' => array(
				array(
					'label' => __( 'Text & Reading Materials', 'skillpulse-lms' ),
					'value' => 'text',
				),
				array(
					'label' => __( 'Video-Based Learning', 'skillpulse-lms' ),
					'value' => 'video',
				),
				array(
					'label' => __( 'Interactive Content', 'skillpulse-lms' ),
					'value' => 'interactive',
				),
				array(
					'label' => __( 'Project-Based Learning', 'skillpulse-lms' ),
					'value' => 'project',
				),
			),
		),
		array(
			'id'      => 'difficulty_level',
			'type'    => 'radio',
			'label'   => __( 'Difficulty Level', 'skillpulse-lms' ),
			'help'    => __( 'Set the difficulty level to help students choose appropriately.', 'skillpulse-lms' ),
			'default' => 'all',
			'value'   => splms_get_course_field_value( $splms_course_id, 'difficulty_level', 'course_content_settings', 'all' ),
			'column'  => 'half',
			'icon'    => 'star-filled',
			'group'   => 'course_content_settings',
			'options' => array(
				array(
					'label' => __( 'All Levels', 'skillpulse-lms' ),
					'value' => 'all',
					'icon'  => 'star-filled',
					'color' => '#059669',
				),
				array(
					'label' => __( 'Beginner', 'skillpulse-lms' ),
					'value' => 'beginner',
					'icon'  => 'star-empty',
					'color' => '#3b82f6',
				),
				array(
					'label' => __( 'Intermediate', 'skillpulse-lms' ),
					'value' => 'intermediate',
					'icon'  => 'star-half',
					'color' => '#f59e0b',
				),
				array(
					'label' => __( 'Advanced', 'skillpulse-lms' ),
					'value' => 'advanced',
					'icon'  => 'star-filled',
					'color' => '#dc2626',
				),
			),
		),
		array(
			'id'      => 'course_duration_value',
			'type'    => 'number',
			'label'   => __( 'Course Duration', 'skillpulse-lms' ),
			'help'    => __( 'Total time to complete the course.', 'skillpulse-lms' ),
			'default' => 4,
			'value'   => splms_get_course_field_value( $splms_course_id, 'course_duration_value', 'course_content_settings', 4 ),
			'column'  => 'half',
			'group'   => 'course_content_settings',
		),
		array(
			'id'      => 'course_duration_unit',
			'type'    => 'select',
			'label'   => __( 'Duration Unit', 'skillpulse-lms' ),
			'help'    => __( 'Select the unit of time for the course duration.', 'skillpulse-lms' ),
			'default' => 'weeks',
			'value'   => splms_get_course_field_value( $splms_course_id, 'course_duration_unit', 'course_content_settings', 'weeks' ),
			'column'  => 'half',
			'group'   => 'course_content_settings',
			'options' => array(
				array(
					'label' => __( 'Hours', 'skillpulse-lms' ),
					'value' => 'hours',
				),
				array(
					'label' => __( 'Days', 'skillpulse-lms' ),
					'value' => 'days',
				),
				array(
					'label' => __( 'Weeks', 'skillpulse-lms' ),
					'value' => 'weeks',
				),
				array(
					'label' => __( 'Months', 'skillpulse-lms' ),
					'value' => 'months',
				),
			),
		),
		array(
			'id'      => 'course_language',
			'type'    => 'select',
			'label'   => __( 'Course Language', 'skillpulse-lms' ),
			'help'    => __( 'Primary language used in course content.', 'skillpulse-lms' ),
			'default' => 'en',
			'value'   => splms_get_course_field_value( $splms_course_id, 'course_language', 'course_content_settings', 'en' ),
			'column'  => 'half',
			'icon'    => 'language',
			'group'   => 'course_content_settings',
			'options' => array(
				array(
					'label' => __( 'English', 'skillpulse-lms' ),
					'value' => 'en',
				),
				array(
					'label' => __( 'Spanish', 'skillpulse-lms' ),
					'value' => 'es',
				),
				array(
					'label' => __( 'French', 'skillpulse-lms' ),
					'value' => 'fr',
				),
				array(
					'label' => __( 'German', 'skillpulse-lms' ),
					'value' => 'de',
				),
				array(
					'label' => __( 'Italian', 'skillpulse-lms' ),
					'value' => 'it',
				),
				array(
					'label' => __( 'Portuguese', 'skillpulse-lms' ),
					'value' => 'pt',
				),
				array(
					'label' => __( 'Chinese (Simplified)', 'skillpulse-lms' ),
					'value' => 'zh',
				),
				array(
					'label' => __( 'Japanese', 'skillpulse-lms' ),
					'value' => 'ja',
				),
			),
		),
		array(
			'id'          => 'certificate_enabled',
			'type'        => 'toggle',
			'label'       => __( 'Certificate of Completion', 'skillpulse-lms' ),
			'help'        => __( 'Award a certificate when students complete the course.', 'skillpulse-lms' ),
			'default'     => true,
			'value'       => splms_get_course_field_value( $splms_course_id, 'certificate_enabled', 'course_completion_settings', true ),
			'column'      => 'half',
			'icon'        => 'awards',
			'group'       => 'course_completion_settings',
			'conditional' => array(
				'operator'   => 'AND',
				'conditions' => array(
					array(
						'key'   => 'global:certificates.certificate_settings.enable_certificates',
						'value' => true,
					),
				),
			),
		),
		array(
			'id'          => 'certificate_template_id',
			'type'        => 'select',
			'label'       => __( 'Certificate Template', 'skillpulse-lms' ),
			'help'        => __( 'Choose which certificate template to award. The default certificate (marked in certificate settings) will be automatically selected if none is chosen.', 'skillpulse-lms' ),
			'default'     => '',
			'value'       => splms_get_course_field_value( $splms_course_id, 'certificate_template_id', 'course_completion_settings', '' ),
			'column'      => 'half',
			'icon'        => 'admin-customizer',
			'group'       => 'course_completion_settings',
			'api'         => array(
				'endpoint'      => '/splms/v1/certificate',
				'method'        => 'GET',
				'params'        => array(
					'per_page' => 100,
					'status'   => 'publish',
				),
				'useProperties' => array( 'id', 'title' ),
			),
			'options'     => array(
				array(
					'label' => __( 'Select a Certificate Template', 'skillpulse-lms' ),
					'value' => '',
				),
			),
			'conditional' => array(
				'operator'   => 'AND',
				'conditions' => array(
					array(
						'key'   => 'certificate_enabled',
						'value' => true,
					),
					array(
						'key'   => 'global:certificates.certificate_settings.enable_certificates',
						'value' => true,
					),
				),
			),
		),
	),
);

// FREE: Prerequisites & Learning Outcomes.
$splms_prerequisites_outcomes = array(
	'id'          => 'prerequisites_outcomes',
	'title'       => __( 'Prerequisites & Learning Outcomes', 'skillpulse-lms' ),
	'icon'        => 'clipboard',
	'description' => __( 'Define course prerequisites and learning outcomes.', 'skillpulse-lms' ),
	'fields'      => array(
		array(
			'id'          => 'prerequisites_description',
			'type'        => 'textarea',
			'label'       => __( 'Prerequisites & Requirements', 'skillpulse-lms' ),
			'help'        => __( 'Describe general skills or knowledge students need. Note: For course-specific prerequisites, use Course Access Type setting instead.', 'skillpulse-lms' ),
			'placeholder' => __( 'e.g., Basic computer skills, high school math, familiarity with email, etc.', 'skillpulse-lms' ),
			'value'       => splms_get_course_field_value( $splms_course_id, 'prerequisites_description', '', '' ),
			'column'      => 'full',
			'icon'        => 'clipboard',
		),
		array(
			'id'          => 'learning_outcomes',
			'type'        => 'textarea',
			'label'       => __( 'Learning Outcomes', 'skillpulse-lms' ),
			'help'        => __( 'List what students will be able to do after completing this course.', 'skillpulse-lms' ),
			'placeholder' => __( 'After completing this course, students will be able to:\n• Outcome 1\n• Outcome 2\n• Outcome 3', 'skillpulse-lms' ),
			'value'       => splms_get_course_field_value( $splms_course_id, 'learning_outcomes', '', '' ),
			'column'      => 'full',
			'icon'        => 'clipboard',
		),
	),
);

// FREE: Completion & Assessment.
$splms_completion_settings = array(
	'id'          => 'completion_settings',
	'title'       => __( 'Completion & Assessment', 'skillpulse-lms' ),
	'icon'        => 'awards',
	'description' => __( 'Configure course completion criteria and assessment settings.', 'skillpulse-lms' ),
	'fields'      => array(
		array(
			'id'      => 'completion_criteria',
			'type'    => 'select',
			'label'   => __( 'Course Completion Criteria', 'skillpulse-lms' ),
			'help'    => __( 'What students must do to complete the course.', 'skillpulse-lms' ),
			'default' => 'all_lessons',
			'value'   => splms_get_course_field_value( $splms_course_id, 'completion_criteria', 'course_completion_settings', 'all_lessons' ),
			'column'  => 'full',
			'icon'    => 'awards',
			'group'   => 'course_completion_settings',
			'options' => array(
				array(
					'label' => __( 'Complete All Lessons', 'skillpulse-lms' ),
					'value' => 'all_lessons',
				),
				array(
					'label' => __( 'Complete All Lessons + Pass Quizzes', 'skillpulse-lms' ),
					'value' => 'lessons_and_quiz',
				),
			),
		),
		array(
			'id'          => 'passing_grade',
			'type'        => 'number',
			'label'       => __( 'Minimum Passing Score (%)', 'skillpulse-lms' ),
			'help'        => __( 'Minimum percentage score required to pass quizzes.', 'skillpulse-lms' ),
			'default'     => 70,
			'value'       => splms_get_course_field_value( $splms_course_id, 'passing_grade', 'course_completion_settings', 70 ),
			'column'      => 'full',
			'icon'        => 'awards',
			'group'       => 'course_completion_settings',
			'conditional' => array(
				'key'   => 'completion_criteria',
				'value' => 'lessons_and_quiz',
			),
		),
	),
);



// Build CLI removes Pro variables from this array for free version.
return array(
	'sections' => array(
		$splms_course_access,
		$splms_course_details,
		$splms_prerequisites_outcomes,
		$splms_completion_settings,
	),
	'metadata' => array(
		'version'      => '1.0.0',
		'last_updated' => time(),
		'supports'     => array( 'editor', 'api', 'frontend' ),
	),
);
