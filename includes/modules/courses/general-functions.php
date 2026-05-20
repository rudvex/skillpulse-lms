<?php
/**
 * General Course Functions
 *
 * This file contains all general course-related functions that can be reused
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
 * COURSE SETTINGS FUNCTIONS
 * ============================================================================
 */

/**
 * Get course settings with defaults.
 *
 * @param int $course_id Course ID.
 *
 * @since 1.0.0
 *
 * @return array Course settings.
 */
function splms_get_course_settings( $course_id = null ) {
	if ( ! $course_id ) {
		$course_id = get_the_ID();
	}

	return SPLMS_Courses::get_instance()->get_course_settings( $course_id );
}

/**
 * Update course settings.
 *
 * @param int   $course_id Course ID.
 * @param array $settings  New settings.
 *
 * @since 1.0.0
 *
 * @return array|WP_Error Updated settings or error.
 */
function splms_update_course_settings( $course_id, $settings ) {
	// Check if user has permission to edit the course.
	if ( ! current_user_can( 'edit_post', $course_id ) ) {
		return new WP_Error(
			'splms_permission_denied',
			__( 'You do not have permission to update course settings.', 'skillpulse-lms' ),
			array( 'status' => 403 )
		);
	}

	return SPLMS_Courses::get_instance()->update_course_settings( $course_id, $settings );
}


/**
 * Get course difficulty level.
 *
 * @param int $course_id Course ID.
 *
 * @since 1.0.0
 *
 * @return string Course level.
 */
function splms_get_course_level( $course_id ) {
	return SPLMS_Courses::get_instance()->get_course_level( $course_id );
}

/**
 * Get course price.
 *
 * @param int $course_id Course ID.
 *
 * @since 1.0.0
 *
 * @return float Course price.
 */
function splms_get_course_price( $course_id ) {
	return SPLMS_Courses::get_instance()->get_course_price( $course_id );
}

/**
 * Get course access type and pricing information.
 *
 * @param int   $course_id Course ID.
 * @param int   $user_id   User ID.
 * @param array $args      Additional arguments.
 *
 * @since 1.0.0
 *
 * @return array Course access information.
 */
function splms_get_course_access_info( $course_id = null, $user_id = null, $args = array() ) {
	if ( ! $course_id ) {
		$course_id = get_the_ID();
	}

	if ( ! $user_id ) {
		$user_id = get_current_user_id();
	}

	$settings = splms_get_course_settings( $course_id );

	// Get access and pricing settings from grouped arrays.
	$access_settings     = isset( $settings['course_access_settings'] ) ? $settings['course_access_settings'] : array();
	$pricing_settings    = isset( $settings['course_pricing_settings'] ) ? $settings['course_pricing_settings'] : array();
	$scheduling_settings = isset( $settings['course_scheduling_settings'] ) ? $settings['course_scheduling_settings'] : array();

	// Get the course access type setting.
	$course_access_type = isset( $access_settings['course_access_type'] ) ? $access_settings['course_access_type'] : 'public_free';

	// If paid courses are disabled globally, force all courses to be free.
	if ( 'public_paid' === $course_access_type && ! splms_is_paid_courses_enabled() ) {
		$course_access_type = 'public_free';
	}

	// Determine course mode from access type.
	$course_mode = 'free';
	if ( 'public_paid' === $course_access_type && splms_is_paid_courses_enabled() ) {
		$course_mode = 'paid';
	} elseif ( 'prerequisite_required' === $course_access_type ) {
		$course_mode = 'prerequisite';
	} elseif ( 'invitation_only' === $course_access_type ) {
		$course_mode = 'invitation';
	}

	$access_info = array(
		// Course ID for membership checks.
		'course_id'            => $course_id,
		// Course Access & Pricing.
		'course_access_type'   => $course_access_type, // public_free, public_paid, invitation_only, prerequisite_required.
		'course_mode'          => $course_mode, // free, paid, prerequisite.
		'price'                => floatval( isset( $pricing_settings['course_price'] ) ? $pricing_settings['course_price'] : 0 ),
		'discount_type'        => isset( $pricing_settings['course_discount_type'] ) ? $pricing_settings['course_discount_type'] : 'percentage',
		'discount_value'       => floatval( isset( $pricing_settings['course_discount'] ) ? $pricing_settings['course_discount'] : 0 ),
		'invited_users'        => isset( $access_settings['invited_users'] ) ? $access_settings['invited_users'] : array(),
		'prerequisite_course'  => isset( $access_settings['prerequisite_course'] ) ? $access_settings['prerequisite_course'] : '',
		'required_memberships' => isset( $access_settings['required_memberships'] ) ? $access_settings['required_memberships'] : array(),

		// Enrollment & Scheduling.
		'enrollment_start'     => isset( $scheduling_settings['enrollment_start_date'] ) ? $scheduling_settings['enrollment_start_date'] : '',
		'enrollment_end'       => isset( $scheduling_settings['enrollment_end_date'] ) ? $scheduling_settings['enrollment_end_date'] : '',
		'course_delivery'      => isset( $scheduling_settings['course_delivery'] ) ? $scheduling_settings['course_delivery'] : 'self_paced',
		'live_class_schedule'  => isset( $scheduling_settings['live_class_schedule'] ) ? $scheduling_settings['live_class_schedule'] : '',
		'course_start_date'    => isset( $scheduling_settings['course_start_date'] ) ? $scheduling_settings['course_start_date'] : '',
		'max_enrollment'       => intval( isset( $scheduling_settings['max_enrollment'] ) ? $scheduling_settings['max_enrollment'] : 0 ),
	);

	// Calculate final price.
	$price          = $access_info['price'];
	$discount_value = $access_info['discount_value'];
	$discount_type  = $access_info['discount_type'];

	if ( $discount_value > 0 ) {
		if ( 'percentage' === $discount_type ) {
			$access_info['final_price'] = $price - ( $price * ( $discount_value / 100 ) );
		} else {
			$access_info['final_price'] = $price - $discount_value;
		}
	} else {
		$access_info['final_price'] = $price;
	}

	// Ensure final price is not negative.
	$access_info['final_price'] = max( 0, $access_info['final_price'] );

	// Apply smart pricing for section-based pricing courses (optional filter).
	$access_info = apply_filters( 'splms_course_access_info_with_smart_pricing', $access_info, $course_id, $user_id, $args );

	return apply_filters( 'splms_course_access_info', $access_info, $course_id, $user_id, $args );
}

/**
 * Get course delivery information.
 *
 * @param int $course_id Course ID.
 *
 * @since 1.0.0
 *
 * @return array Course delivery information.
 */
function splms_get_course_delivery_info( $course_id = null ) {
	if ( ! $course_id ) {
		$course_id = get_the_ID();
	}

	$settings = splms_get_course_settings( $course_id );

	// Get scheduling settings.
	$scheduling_settings = isset( $settings['course_scheduling_settings'] ) ? $settings['course_scheduling_settings'] : array();

	return array(
		'delivery_mode'     => isset( $scheduling_settings['course_delivery'] ) ? $scheduling_settings['course_delivery'] : 'self_paced',
		'live_schedule'     => isset( $scheduling_settings['live_class_schedule'] ) ? $scheduling_settings['live_class_schedule'] : '',
		'course_start_date' => isset( $scheduling_settings['course_start_date'] ) ? $scheduling_settings['course_start_date'] : '',
		'max_enrollment'    => intval( isset( $scheduling_settings['max_enrollment'] ) ? $scheduling_settings['max_enrollment'] : 0 ),
	);
}

/**
 * Get course content information.
 *
 * @param int $course_id Course ID.
 *
 * @since 1.0.0
 *
 * @return array Course content information.
 */
function splms_get_course_content_info( $course_id = null ) {
	if ( ! $course_id ) {
		$course_id = get_the_ID();
	}

	$settings = splms_get_course_settings( $course_id );

	// Get content settings.
	$content_settings          = isset( $settings['course_content_settings'] ) ? $settings['course_content_settings'] : array();
	$completion_settings       = isset( $settings['course_completion_settings'] ) ? $settings['course_completion_settings'] : array();
	$learning_outcomes         = isset( $settings['learning_outcomes'] ) ? $settings['learning_outcomes'] : array();
	$prerequisites_description = isset( $settings['prerequisites_description'] ) ? $settings['prerequisites_description'] : array();

	return array(
		'difficulty_level'          => isset( $content_settings['difficulty_level'] ) ? $content_settings['difficulty_level'] : 'beginner',
		'course_duration_value'     => intval( isset( $content_settings['course_duration_value'] ) ? $content_settings['course_duration_value'] : 1 ),
		'course_duration_unit'      => isset( $content_settings['course_duration_unit'] ) ? $content_settings['course_duration_unit'] : 'months',
		'learning_outcomes'         => $learning_outcomes,
		'prerequisites_description' => $prerequisites_description,
		'course_language'           => isset( $content_settings['course_language'] ) ? $content_settings['course_language'] : 'en',
		'course_level'              => isset( $content_settings['course_level'] ) ? $content_settings['course_level'] : 'beginner',
		'learning_method'           => isset( $content_settings['learning_method'] ) ? $content_settings['learning_method'] : 'text',
		'certificate_enabled'       => isset( $completion_settings['certificate_enabled'] ) ? $completion_settings['certificate_enabled'] : false,
		'certificate_template_id'   => isset( $completion_settings['certificate_template_id'] ) ? $completion_settings['certificate_template_id'] : '',
		'completion_criteria'       => isset( $completion_settings['completion_criteria'] ) ? $completion_settings['completion_criteria'] : 'all_lessons',
		'passing_grade'             => intval( isset( $completion_settings['passing_grade'] ) ? $completion_settings['passing_grade'] : 70 ),
	);
}

/**
 * ============================================================================
 * COURSE METADATA FUNCTIONS
 * ============================================================================
 */

/**
 * Get course duration.
 *
 * @param int $course_id Course ID.
 *
 * @since 1.0.0
 *
 * @return string Course duration.
 */
function splms_get_course_duration( $course_id = null ) {
	if ( ! $course_id ) {
		$course_id = get_the_ID();
	}

	$formatted_duration = splms_get_formatted_course_duration( $course_id );

	if ( ! $formatted_duration ) {
		$formatted_duration = __( 'Self-paced', 'skillpulse-lms' );
	}

	return apply_filters( 'splms_course_duration', $formatted_duration, $course_id );
}

/**
 * Get formatted course duration.
 *
 * @param int $course_id Course ID.
 *
 * @since 1.0.0
 *
 * @return string Formatted duration.
 */
function splms_get_formatted_course_duration( $course_id = null ) {
	if ( ! $course_id ) {
		$course_id = get_the_ID();
	}

	$content_info = splms_get_course_content_info( $course_id );
	$value        = $content_info['course_duration_value'];
	$unit         = $content_info['course_duration_unit'];

	if ( $value <= 0 ) {
		return __( 'Self-paced', 'skillpulse-lms' );
	}

	$unit_labels = array(
		'days'   => _n( 'day', 'days', $value, 'skillpulse-lms' ),
		'weeks'  => _n( 'week', 'weeks', $value, 'skillpulse-lms' ),
		'months' => _n( 'month', 'months', $value, 'skillpulse-lms' ),
		'years'  => _n( 'year', 'years', $value, 'skillpulse-lms' ),
	);

	$unit_label = isset( $unit_labels[ $unit ] ) ? $unit_labels[ $unit ] : $unit;

	return sprintf( '%d %s', $value, $unit_label );
}

/**
 * Get course students count.
 *
 * @param int $course_id Course ID.
 *
 * @since 1.0.0
 *
 * @return int Students count.
 */
function splms_get_course_enrollment_count( $course_id = null ) {
	if ( ! $course_id ) {
		$course_id = get_the_ID();
	}

	// Get actual count from enrollments table.
	$count = SPLMS_Enrollments_Query::get_instance()->get_course_enrollment_count( $course_id );

	return apply_filters( 'splms_course_enrollment_count', (int) $count, $course_id );
}

/**
 * Get course rating summary.
 *
 * @param int $course_id Course ID.
 *
 * @since 1.0.0
 *
 * @return array Complete rating summary with breakdown.
 */
function splms_get_course_rating( $course_id = null ) {
	if ( ! $course_id ) {
		$course_id = get_the_ID();
	}

	// Default empty rating structure. Reviews module populates via splms_course_rating filter.
	$default_summary = array(
		'average_rating'   => 0,
		'total_reviews'    => 0,
		'rating_breakdown' => array(
			5 => 0,
			4 => 0,
			3 => 0,
			2 => 0,
			1 => 0,
		),
	);

	return apply_filters( 'splms_course_rating', $default_summary, $course_id );
}


/**
 * Get course reviews with pagination data.
 *
 * @param int   $course_id Course ID.
 * @param array $args      Query arguments.
 *
 * @since 1.0.0
 *
 * @return array Reviews with pagination.
 */
function splms_get_course_reviews( $course_id = null, $args = array() ) {
	if ( ! $course_id ) {
		$course_id = get_the_ID();
	}

	// Return empty result if reviews module is not loaded.
	if ( ! class_exists( 'SPLMS_Review_Manager' ) ) {
		return array(
			'reviews' => array(),
			'total'   => 0,
			'pages'   => 0,
		);
	}

	return SPLMS_Review_Manager::get_course_reviews( $course_id, $args );
}

/**
 * ============================================================================
 * COURSE VALIDATION FUNCTIONS
 * ============================================================================
 */

/**
 * Check if course exists.
 *
 * @param int $course_id Course ID.
 *
 * @since 1.0.0
 *
 * @return bool True if course exists.
 */
function splms_course_exists( $course_id ) {
	if ( empty( $course_id ) ) {
		return false;
	}

	$course = get_post( $course_id );

	return $course && SPLMS_POST_TYPES['course'] === $course->post_type;
}

/**
 * ============================================================================
 * UTILITY FUNCTIONS
 * ============================================================================
 */

/**
 * Format a date string using WordPress settings.
 *
 * @param string|int $date   Date string or timestamp.
 * @param string     $format Optional. Date format. Defaults to site's date format setting.
 *
 * @since 1.0.0
 *
 * @return string Formatted date string, or empty string if date is invalid.
 */
function splms_format_date( $date, $format = '' ) {
	if ( empty( $date ) ) {
		return '';
	}

	// Get timestamp from date.
	if ( is_numeric( $date ) ) {
		$timestamp = (int) $date;
	} else {
		$timestamp = strtotime( $date );
	}

	// Return empty string if date is invalid.
	if ( false === $timestamp ) {
		return '';
	}

	// Use site's date format if not specified.
	if ( empty( $format ) ) {
		$format = get_option( 'date_format' );
	}

	return date_i18n( $format, $timestamp );
}

/**
 * ============================================================================
 * COURSE PRICING FUNCTIONS (SECTION-BASED PRICING)
 * ============================================================================
 */

/**
 * Calculate recommended course price based on section pricing.
 *
 * When a course uses section-based pricing, this function calculates
 * a smart course price that makes sense relative to individual sections.
 *
 * @param int    $course_id        Course ID.
 * @param string $pricing_strategy Pricing strategy: 'match' (same as sections), 'discount' (15% less), 'premium' (15% more).
 *
 * @since 1.0.0
 *
 * @return float Recommended course price.
 */
function splms_calculate_smart_course_price( $course_id, $pricing_strategy = 'discount' ) {
	// Check if course uses section pricing.
	if ( ! splms_course_uses_section_pricing( $course_id ) ) {
		return 0;
	}

	// Get section pricing summary.
	$summary = splms_get_course_section_pricing_summary( $course_id );

	$sections_total = floatval( $summary['total_price'] );

	if ( $sections_total <= 0 ) {
		return 0;
	}

	// Calculate based on strategy.
	switch ( $pricing_strategy ) {
		case 'match':
			// Course price = sum of sections.
			return $sections_total;

		case 'discount':
			// Course price = 85% of sections (15% bundle discount).
			return $sections_total * 0.85;

		case 'premium':
			// Course price = 115% of sections (premium features).
			return $sections_total * 1.15;

		default:
			return $sections_total * 0.85;
	}
}

/**
 * Validate course pricing against section pricing.
 *
 * Checks if the course price makes business sense relative to
 * the sum of individual section prices.
 *
 * @param int $course_id Course ID.
 *
 * @since 1.0.0
 *
 * @return true|WP_Error True if valid, WP_Error if invalid.
 */
function splms_validate_course_pricing( $course_id ) {
	// Only validate if course uses section pricing.
	if ( ! splms_course_uses_section_pricing( $course_id ) ) {
		return true;
	}

	$course_info = splms_get_course_access_info( $course_id );
	$summary     = splms_get_course_section_pricing_summary( $course_id );

	$course_price   = floatval( $course_info['final_price'] );
	$sections_total = floatval( $summary['total_price'] );

	// If no sections have pricing, course can be any price.
	if ( $sections_total <= 0 ) {
		return true;
	}

	// Course price should not exceed sections total by more than 20%.
	$max_allowed_price = $sections_total * 1.2;

	if ( $course_price > $max_allowed_price ) {
		return new WP_Error(
			'invalid_course_pricing',
			sprintf(
			/* translators: 1: Course price, 2: Sections total, 3: Percentage difference */
				__(
					'Course price (%1$s) is significantly higher than individual sections total (%2$s). This discourages users from buying the complete course. Consider setting the course price to match or be less than the sections total.',
					'skillpulse-lms'
				),
				splms_get_price_format( $course_price ),
				splms_get_price_format( $sections_total ),
				round( ( ( $course_price - $sections_total ) / $sections_total ) * 100 )
			)
		);
	}

	return true;
}

/**
 * Get section content statistics.
 *
 * Returns counts of lessons, quizzes, and other content types within a section.
 *
 * @param int $section_id Section ID.
 *
 * @since 1.0.0
 *
 * @return array Section content statistics.
 */
function splms_get_section_content_stats( $section_id ) {
	$stats = array(
		'lessons'     => 0,
		'quizzes'     => 0,
		'videos'      => 0,
		'total_items' => 0,
	);

	// Use the more efficient section-specific function instead.
	$section_result = splms_get_section_curriculum( $section_id );

	// Convert to expected format for backward compatibility.
	$curriculum_result = array(
		'sections' => array(
			array(
				'children' => isset( $section_result['items'] ) ? $section_result['items'] : array(),
			),
		),
	);

	if ( ! isset( $curriculum_result['sections'][0]['children'] ) ) {
		return $stats;
	}

	$children = $curriculum_result['sections'][0]['children'];

	foreach ( $children as $child ) {
		++$stats['total_items'];

		if ( SPLMS_POST_TYPES['lesson'] === $child['type'] ) {
			++$stats['lessons'];

			// Check if lesson has video content.
			$lesson_data = get_post_meta( $child['id'], '_splms_lesson_data', true );
			if ( isset( $lesson_data['lesson_type'] ) && 'video' === $lesson_data['lesson_type'] ) {
				++$stats['videos'];
			}
		} elseif ( SPLMS_POST_TYPES['quiz'] === $child['type'] ) {
			++$stats['quizzes'];
		}
	}

	return apply_filters( 'splms_section_content_stats', $stats, $section_id );
}

/**
 * Get section duration formatted as human-readable string.
 *
 * Calculates the total duration of all lessons within a section.
 *
 * @param int $section_id Section ID.
 *
 * @since 1.0.0
 *
 * @return string Formatted duration (e.g., "2hr 47min").
 */
function splms_get_section_duration( $section_id ) {
	$total_minutes = 0;

	// Use the more efficient section-specific function instead.
	$section_result = splms_get_section_curriculum( $section_id );

	// Convert to expected format for backward compatibility.
	$curriculum_result = array(
		'sections' => array(
			array(
				'children' => isset( $section_result['items'] ) ? $section_result['items'] : array(),
			),
		),
	);

	if ( ! isset( $curriculum_result['sections'][0]['children'] ) ) {
		return '';
	}

	$children = $curriculum_result['sections'][0]['children'];

	foreach ( $children as $child ) {
		if ( SPLMS_POST_TYPES['lesson'] === $child['type'] ) {
			$lesson_data = SPLMS_Lessons::get_instance()->get_lesson_settings( $child['id'] );

			// Get lesson duration in minutes.
			if ( isset( $lesson_data['lesson_duration'] ) && $lesson_data['lesson_duration'] > 0 ) {
				$total_minutes += (int) $lesson_data['lesson_duration'];
			}
		} elseif ( SPLMS_POST_TYPES['quiz'] === $child['type'] ) {
			// Estimate quiz duration.
			$quiz_data = SPLMS_Quizzes::get_instance()->get_quiz_settings( $child['id'] );
			if (
				isset( $quiz_data['quiz_timing_settings']['time_limit_enabled'] ) &&
				1 === (int) $quiz_data['quiz_timing_settings']['time_limit_enabled'] &&
				! empty( $quiz_data['quiz_timing_settings']['time_limit'] )
			) {
				// time_limit is already in minutes.
				$total_minutes += (int) $quiz_data['quiz_timing_settings']['time_limit'];
			}
		}
	}

	if ( $total_minutes <= 0 ) {
		return '';
	}

	// Convert to hours and minutes.
	$hours   = floor( $total_minutes / 60 );
	$minutes = $total_minutes % 60;

	$duration_parts = array();

	if ( $hours > 0 ) {
		$duration_parts[] = sprintf(
		/* translators: %d: Number of hours. */
			_n( '%dhr', '%dhr', $hours, 'skillpulse-lms' ),
			$hours
		);
	}

	if ( $minutes > 0 ) {
		$duration_parts[] = sprintf(
		/* translators: %d: Number of minutes. */
			__( '%dmin', 'skillpulse-lms' ),
			$minutes
		);
	}

	$formatted_duration = implode( ' ', $duration_parts );

	return apply_filters( 'splms_section_duration', $formatted_duration, $section_id, $total_minutes );
}

/**
 * ============================================================================
 * COURSE ARCHIVE & CONDITIONAL FUNCTIONS
 * ============================================================================
 */

/**
 * Get course page ID (WooCommerce-style helper function).
 * Similar to WooCommerce's wc_get_page_id('shop').
 *
 * @since 1.0.0
 *
 * @return int Course page ID or 0 if not set.
 */
function splms_get_course_page_id() {
	$page_id = splms_get_setting( 'courses_page_id', 0 );

	return apply_filters( 'splms_course_page_id', absint( $page_id ) );
}

/**
 * Check if current page is the designated course page.
 * Safely handles empty/invalid course page IDs.
 *
 * @since 1.0.0
 *
 * @return bool True if on the designated course page.
 */
function splms_is_course_page() {
	$course_page_id = splms_get_course_page_id();

	return ( $course_page_id > 0 && is_page( $course_page_id ) );
}

/**
 * Check if current page is a course archive page (WooCommerce-style).
 * Similar to WooCommerce's is_shop() function.
 *
 * @since 1.0.0
 *
 * @return bool True if on course archive page.
 */
function splms_is_course_archive() {
	return ( is_post_type_archive( SPLMS_POST_TYPES['course'] ) || splms_is_course_page() );
}

/**
 * Check if current page is a course category page.
 *
 * @since 1.0.0
 *
 * @return bool True if on course category page.
 */
function splms_is_course_category_page() {
	return is_tax( SPLMS_TAXONOMIES['course_category'] );
}

/**
 * Check if current page is a course tag page.
 *
 * @since 1.0.0
 *
 * @return bool True if on course tag page.
 */
function splms_is_course_tag_page() {
	return is_tax( SPLMS_TAXONOMIES['course_tag'] );
}

/**
 * Check is current page is a Dashboard page.
 *
 * @since 1.0.0
 *
 * @return bool True if on dashboard tag page.
 */
function splms_is_dashboard_page() {
	$name = get_query_var( 'name' );

	return 'dashboard' === $name;
}

/**
 * ============================================================================
 * COURSE LOOP & ARCHIVE DISPLAY FUNCTIONS
 * ============================================================================
 */

/**
 * Setup loop properties for course archives.
 *
 * @param array $args Loop arguments.
 *
 * @since 1.0.0
 *
 * @return void
 */
function splms_setup_loop( $args = array() ) {
	$default_args = array(
		'columns'           => splms_get_loop_prop( 'columns', 3 ),
		'rows'              => 1,
		'per_page'          => splms_get_loop_prop( 'per_page', splms_get_setting( 'course_item_per_page', 12 ) ),
		'total'             => splms_get_loop_prop( 'total', 0 ),
		'total_pages'       => splms_get_loop_prop( 'total_pages', 1 ),
		'current_page'      => splms_get_loop_prop( 'current_page', 1 ),
		'orderby'           => splms_get_loop_prop( 'orderby', '' ),
		'order'             => splms_get_loop_prop( 'order', '' ),
		'is_search'         => false,
		'is_filtered'       => false,
		'show_pagination'   => true,
		'show_result_count' => true,
	);

	$loop_args = wp_parse_args( $args, $default_args );

	foreach ( $loop_args as $key => $value ) {
		splms_set_loop_prop( $key, $value );
	}
}

/**
 * Get a loop property.
 *
 * @param string $prop        Property name.
 * @param mixed  $default_val Default value.
 *
 * @since 1.0.0
 *
 * @return mixed
 */
function splms_get_loop_prop( $prop, $default_val = '' ) {
	splms_setup_loop_globals();

	return isset( $GLOBALS['splms_loop'][ $prop ] ) ? $GLOBALS['splms_loop'][ $prop ] : $default_val;
}

/**
 * Set a loop property.
 *
 * @param string $prop  Property name.
 * @param mixed  $value Property value.
 *
 * @since 1.0.0
 *
 * @return void
 */
function splms_set_loop_prop( $prop, $value ) {
	splms_setup_loop_globals();

	$GLOBALS['splms_loop'][ $prop ] = $value;
}

/**
 * Initialize the loop global if it doesn't exist.
 *
 * @since 1.0.0
 *
 * @return void
 */
function splms_setup_loop_globals() {
	if ( ! isset( $GLOBALS['splms_loop'] ) ) {
		$GLOBALS['splms_loop'] = array();
	}
}

/**
 * Reset the loop properties.
 *
 * @since 1.0.0
 *
 * @return void
 */
function splms_reset_loop() {
	unset( $GLOBALS['splms_loop'] );
}

/**
 * Check if courses are available in the loop.
 * Similar to WooCommerce's woocommerce_product_loop() function.
 *
 * @since 1.0.0
 *
 * @return bool
 */
function splms_course_loop() {
	return have_posts();
}

/**
 * Auto-populate loop props from main query when splms_query is detected.
 *
 * @param WP_Query $query The WordPress query object.
 *
 * @since 1.0.0
 *
 * @return void
 */
function splms_auto_populate_loop_props( $query = null ) {
	if ( ! $query ) {
		global $wp_query;
		$query = $wp_query;
	}

	$is_splms_query = $query->get( 'splms_query' );

	if ( 'course_query' === $is_splms_query ) {
		$current_page = max( 1, absint( $query->get( 'paged' ) ) );
		$total        = absint( $query->found_posts );
		$max_pages    = absint( $query->max_num_pages );

		// Get per_page from our setting instead of relying on query value.
		$per_page = splms_get_setting( 'course_item_per_page', 12 );
		$per_page = absint( $per_page );
		if ( $per_page < 1 ) {
			$per_page = 12;
		}

		// Apply same filters as get_courses_per_page.
		$per_page = apply_filters( 'splms_courses_per_page', $per_page, $query );
		$per_page = absint( $per_page );
		if ( $per_page < 1 ) {
			$per_page = 12;
		}

		// Allow URL parameter to override.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- GET parameter for pagination only.
		if ( ! empty( $_GET['per_page'] ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- GET parameter for pagination only.
			$url_per_page = absint( $_GET['per_page'] );
			if ( $url_per_page >= 1 && $url_per_page <= 100 ) {
				$per_page = $url_per_page;
			}
		}

		// Recalculate total_pages based on our per_page setting.
		$calculated_total_pages = $total > 0 ? ceil( $total / $per_page ) : 1;

		splms_set_loop_prop( 'current_page', $current_page );
		splms_set_loop_prop( 'per_page', $per_page );
		splms_set_loop_prop( 'total', $total );
		splms_set_loop_prop( 'total_pages', $calculated_total_pages );
		splms_set_loop_prop( 'is_search', $query->is_search() );
	}
}

/**
 * Output course loop start wrapper.
 *
 * @since 1.0.0
 *
 * @return void
 */
function splms_course_loop_start() {
	$classes = array( 'splms-courses-grid' );
	$layout  = splms_get_loop_prop( 'layout', 'grid' );

	echo '<div class="' . esc_attr( implode( ' ', $classes ) ) . '" data-layout="' . esc_attr( $layout ) . '">';
}

/**
 * Output course loop end wrapper.
 *
 * @since 1.0.0
 *
 * @return void
 */
function splms_course_loop_end() {
	echo '</div>';
}

/**
 * Output result count for course archives.
 * Similar to WooCommerce's woocommerce_result_count() function.
 *
 * @since 1.0.0
 *
 * @return void
 */
function splms_output_result_count() {
	$total        = splms_get_loop_prop( 'total' );
	$per_page     = splms_get_loop_prop( 'per_page' );
	$current_page = splms_get_loop_prop( 'current_page' );

	if ( $total <= 0 ) {
		return;
	}

	$start = ( ( $current_page - 1 ) * $per_page ) + 1;
	$end   = min( $current_page * $per_page, $total );

	$pagination_text = sprintf(
	/* translators: %1$d: Start number, %2$d: End number, %3$d: Total courses. */
		esc_html__( 'Showing %1$d-%2$d of %3$d courses', 'skillpulse-lms' ),
		(int) $start,
		(int) $end,
		(int) $total
	);

	echo '<span class="splms-result-count">' . esc_html( $pagination_text ) . '</span>';
}

/**
 * Output pagination for course archives.
 * Similar to WooCommerce's woocommerce_pagination() function.
 *
 * @since 1.0.0
 *
 * @return void
 */
function splms_output_pagination() {
	$total_pages  = splms_get_loop_prop( 'total_pages' );
	$current_page = splms_get_loop_prop( 'current_page' );

	if ( $total_pages <= 1 ) {
		return;
	}

	echo '<nav class="splms-pagination" role="navigation" aria-label="' . esc_attr__( 'Courses pagination', 'skillpulse-lms' ) . '">';
	echo '<div class="pagination-info">';
	splms_output_result_count();
	echo '</div>';
	echo '<div class="pagination-links">';

	$prev_label = esc_attr__( 'Previous', 'skillpulse-lms' );
	$next_label = esc_attr__( 'Next', 'skillpulse-lms' );

	$prev_text = '<span class="page-prev" aria-label="' . $prev_label . '">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <path d="M5 12H19" stroke="#374151" stroke-width="1.5" stroke-linecap="round"/>
                    <path d="M5 12L9 16" stroke="#374151" stroke-width="1.5" stroke-linecap="round"/>
                    <path d="M5 12L9 8" stroke="#374151" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
            </span>';

	$next_text = '<span class="page-next" aria-label="' . $next_label . '">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <path d="M5 12H19" stroke="#374151" stroke-width="1.5" stroke-linecap="round"/>
                    <path d="M15 16L19 12" stroke="#374151" stroke-width="1.5" stroke-linecap="round"/>
                    <path d="M15 8L19 12" stroke="#374151" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
            </span>';

	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Function returns escaped HTML.
	echo paginate_links(
		array(
			'current'   => $current_page,
			'total'     => $total_pages,
			'prev_text' => $prev_text,
			'next_text' => $next_text,
			'type'      => 'list',
			'end_size'  => 2,
			'mid_size'  => 2,
		)
	);

	echo '</div>';
	echo '</nav>';
}

/**
 * Output course count badge.
 * Shows total found courses with proper plural handling.
 *
 * @since 1.0.0
 *
 * @return void
 */
function splms_output_course_count() {
	$total = splms_get_loop_prop( 'total' );

	if ( $total <= 0 ) {
		return;
	}

	/* translators: %d: Number of courses. */
	$course_count_text = _n( '%d course found', '%d courses found', $total, 'skillpulse-lms' );
	$course_count_text = sprintf( $course_count_text, (int) $total );

	echo '<span class="course-count-badge">' . esc_html( $course_count_text ) . '</span>';
}

/**
 * ============================================================================
 * COURSE CURRICULUM FUNCTIONS
 * ============================================================================
 */

/**
 * DEPRECATED: splms_get_course_curriculum() function moved to functions.php
 *
 * This function has been moved to functions.php with enhanced functionality.
 * The enhanced version includes simplified parameter handling and centralized
 * access control logic. This comment remains for reference.
 *
 * @see splms_get_course_curriculum() in functions.php
 */
