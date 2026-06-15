<?php
/**
 * Quiz Question Builder Configuration
 *
 * Configuration for quiz question types and their fields.
 * Replaces question-builder-config.json with PHP configuration for better performance,
 * translation support, and dynamic capabilities.
 *
 * @package SPLMS
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'types' => array(
		array(
			'type'   => 'multiple_choice',
			'label'  => __( 'Multiple Choice', 'skillpulse-lms' ),
			'fields' => array(
				array(
					'id'       => 'question',
					'type'     => 'textarea',
					'label'    => __( 'Question Text', 'skillpulse-lms' ),
					'required' => true,
					'rows'     => 3,
					'help'     => __( 'Enter your question text', 'skillpulse-lms' ),
				),
				array(
					'id'       => 'description',
					'type'     => 'textarea',
					'label'    => __( 'Description (Optional)', 'skillpulse-lms' ),
					'optional' => true,
					'rows'     => 2,
					'help'     => __( 'Additional context or instructions for this question', 'skillpulse-lms' ),
				),
				array(
					'id'      => 'points',
					'type'    => 'number',
					'label'   => __( 'Points', 'skillpulse-lms' ),
					'default' => 1,
					'min'     => 1,
					'max'     => 100,
					'help'    => __( 'Point value for this question', 'skillpulse-lms' ),
				),
				array(
					'id'          => 'options',
					'type'        => 'repeatable-group',
					'label'       => __( 'Answer Options', 'skillpulse-lms' ),
					'min'         => 2,
					'max'         => 6,
					'itemName'    => __( 'Option', 'skillpulse-lms' ),
					'item_fields' => array(
						array(
							'id'          => 'text',
							'type'        => 'text',
							'label'       => __( 'Option Text', 'skillpulse-lms' ),
							'required'    => true,
							'placeholder' => __( 'Enter option text', 'skillpulse-lms' ),
						),
						array(
							'id'    => 'is_correct',
							'type'  => 'radio',
							'label' => __( 'Correct Answer', 'skillpulse-lms' ),
						),
					),
				),
				array(
					'id'       => 'explanation',
					'type'     => 'textarea',
					'label'    => __( 'Explanation (Optional)', 'skillpulse-lms' ),
					'optional' => true,
					'rows'     => 2,
					'help'     => __( 'Explanation shown to students after answering', 'skillpulse-lms' ),
				),
				array(
					'id'      => 'required',
					'type'    => 'toggle',
					'label'   => __( 'Required Question', 'skillpulse-lms' ),
					'default' => true,
					'help'    => __( 'Students must answer this question', 'skillpulse-lms' ),
				),
				array(
					'id'      => 'randomize_options',
					'type'    => 'toggle',
					'label'   => __( 'Randomize Options', 'skillpulse-lms' ),
					'default' => false,
					'help'    => __( 'Show options in random order', 'skillpulse-lms' ),
				),
			),
		),
		array(
			'type'   => 'true_false',
			'label'  => __( 'True / False', 'skillpulse-lms' ),
			'fields' => array(
				array(
					'id'       => 'question',
					'type'     => 'textarea',
					'label'    => __( 'Question Text', 'skillpulse-lms' ),
					'required' => true,
					'rows'     => 3,
					'help'     => __( 'Enter your question text', 'skillpulse-lms' ),
				),
				array(
					'id'       => 'description',
					'type'     => 'textarea',
					'label'    => __( 'Description (Optional)', 'skillpulse-lms' ),
					'optional' => true,
					'rows'     => 2,
					'help'     => __( 'Additional context or instructions for this question', 'skillpulse-lms' ),
				),
				array(
					'id'      => 'points',
					'type'    => 'number',
					'label'   => __( 'Points', 'skillpulse-lms' ),
					'default' => 1,
					'min'     => 1,
					'max'     => 100,
					'help'    => __( 'Point value for this question', 'skillpulse-lms' ),
				),
				array(
					'id'       => 'correct_answer',
					'type'     => 'radio',
					'label'    => __( 'Correct Answer', 'skillpulse-lms' ),
					'required' => true,
					'options'  => array(
						array(
							'label' => __( 'True', 'skillpulse-lms' ),
							'value' => 'true',
						),
						array(
							'label' => __( 'False', 'skillpulse-lms' ),
							'value' => 'false',
						),
					),
				),
				array(
					'id'       => 'explanation',
					'type'     => 'textarea',
					'label'    => __( 'Explanation (Optional)', 'skillpulse-lms' ),
					'optional' => true,
					'rows'     => 2,
					'help'     => __( 'Explanation shown to students after answering', 'skillpulse-lms' ),
				),
				array(
					'id'      => 'required',
					'type'    => 'toggle',
					'label'   => __( 'Required Question', 'skillpulse-lms' ),
					'default' => true,
					'help'    => __( 'Students must answer this question', 'skillpulse-lms' ),
				),
			),
		),
	),
);
