<?php
/**
 * Pricing Model Badge
 *
 * Displays a badge when the course has section-based pricing enabled.
 *
 * @package SkillPulse_LMS
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$course_id = get_the_ID();

// Check if course uses section-based pricing.
if ( splms_course_uses_section_pricing( $course_id ) ) {
	?>
	<span class="course-pricing-model-badge section-based" style="display: inline-flex; align-items: center; gap: 6px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; padding: 6px 14px; border-radius: 20px; font-size: 13px; font-weight: 600; box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);">
		<svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
			<path d="M12 2L2 7L12 12L22 7L12 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
			<path d="M2 17L12 22L22 17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
			<path d="M2 12L12 17L22 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
		</svg>
		<?php esc_html_e( 'Section-Based Pricing', 'skillpulse-lms' ); ?>
	</span>
	<?php
}
// Only show badge when course has section-based pricing configured.
