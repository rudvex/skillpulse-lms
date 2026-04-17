<?php
/**
 * Membership Display Helper
 *
 * Handles membership-related display logic for course type data.
 * This keeps all membership display logic within the integrations module.
 *
 * @package SkillPulse_LMS
 * @subpackage Integrations
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Filter course type data to handle membership requirements.
 *
 * This function is hooked to 'splms_course_type_data' filter and modifies
 * the course type display when membership requirements are present.
 *
 * @since 1.0.0
 *
 * @param array $course_type_data Course type data array.
 * @param array $access_info      Course access information.
 * @return array Modified course type data.
 */
function splms_filter_course_type_data_for_membership( $course_type_data, $access_info ) {
	// Only process if we have valid access info.
	if ( empty( $access_info ) || empty( $access_info['course_access_type'] ) ) {
		return $course_type_data;
	}

	$course_access_type = $access_info['course_access_type'];

	// Membership requirements only apply to public_free and public_paid.
	$membership_applicable_types = array( 'public_free', 'public_paid' );
	if ( ! in_array( $course_access_type, $membership_applicable_types, true ) ) {
		return $course_type_data;
	}

	if ( ! splms_has_membership_integration() ) {
		return $course_type_data;
	}

	// Check if course has required memberships.
	$required_memberships = isset( $access_info['required_memberships'] ) ? $access_info['required_memberships'] : array();
	if ( empty( $required_memberships ) ) {
		return $course_type_data;
	}

	// If membership is required, override the display to show membership requirement.
	return array(
		'type'          => 'membership',
		'label'         => __( 'Membership Required', 'skillpulse-lms' ),
		'display'       => __( 'Membership Required', 'skillpulse-lms' ),
		'icon'          => 'groups',
		'class'         => 'course-type-membership',
		'price_display' => '<span class="price-membership">' . __( 'Membership Required', 'skillpulse-lms' ) . '</span>',
	);
}
add_filter( 'splms_course_type_data', 'splms_filter_course_type_data_for_membership', 10, 2 );
