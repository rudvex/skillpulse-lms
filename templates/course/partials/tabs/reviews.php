<?php
/**
 * Course Reviews Tab
 *
 * This template can be overridden by copying it to yourtheme/skillpulse-lms/course/partials/tabs/reviews.php.
 *
 * @package SkillPulse_LMS
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}



$splms_course_id = get_the_ID();
$splms_user_id   = get_current_user_id();

// Check if reviews are enabled.
$splms_reviews_enabled = splms_get_setting( 'course_reviews_enabled', true );

if ( ! $splms_reviews_enabled ) {
	return;
}
?>

<div class="course-content-tabs">
	<div class="course-content-section course-reviews">
		<h2 class="section-title">
			<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M11.049 2.927C11.3483 2.00556 12.6517 2.00556 12.951 2.927L14.4697 7.60081C14.6035 8.01284 14.9875 8.29653 15.4207 8.29653H20.4329C21.4016 8.29653 21.8044 9.54169 21.0207 10.1008L17.0205 12.9894C16.6704 13.244 16.5234 13.6982 16.6572 14.1102L18.1759 18.784C18.4752 19.7054 17.4241 20.4649 16.6405 19.9058L12.6402 17.0172C12.2901 16.7626 11.7099 16.7626 11.3598 17.0172L7.35954 19.9058C6.57589 20.4649 5.52481 19.7054 5.82411 18.784L7.34276 14.1102C7.47659 13.6982 7.32961 13.244 6.97946 12.9894L2.97918 10.1008C2.19553 9.54169 2.59832 8.29653 3.56708 8.29653H8.57929C9.01252 8.29653 9.39647 8.01284 9.53029 7.60081L11.049 2.927Z"
						stroke="currentColor" stroke-width="1.5"/>
			</svg>
			<?php esc_html_e( 'Student Reviews', 'skillpulse-lms' ); ?>
		</h2>
		<div class="section-content">
			<?php
			/**
			 * Hook: splms_before_reviews.
			 *
			 * @since 1.0.0
			 *
			 * @param int $course_id Course ID.
			 */
			do_action( 'splms_before_reviews', $splms_course_id );

			// Get course reviews using the new system with pagination.
			$splms_reviews_data  = splms_get_course_reviews( $splms_course_id );
			$splms_reviews       = $splms_reviews_data['reviews'] ?? array();
			$splms_total_reviews = $splms_reviews_data['total'] ?? 0;
			$splms_total_pages   = $splms_reviews_data['pages'] ?? 1;

			// Check if user can review.
			$splms_permission = array();
			if ( $splms_user_id ) {
				$splms_permission = SPLMS_Review_Permissions::can_user_review( $splms_user_id, $splms_course_id );
			}
			?>

			<?php if ( $splms_total_reviews > 0 ) { ?>
				<?php
				splms_get_template_part(
					'course/partials/tabs/reviews/summary',
					'',
					array(
						'splms_course_id' => $splms_course_id,
					)
				);
				?>

				<?php
				splms_get_template_part(
					'course/partials/tabs/reviews/controls',
					'',
					array(
						'splms_course_id'  => $splms_course_id,
						'splms_user_id'    => $splms_user_id,
						'splms_permission' => $splms_permission,
					)
				);
				?>
			<?php } ?>

			<div class="splms-reviews-content">
				<?php if ( ! empty( $splms_reviews ) && is_array( $splms_reviews ) ) { ?>
					<?php
					splms_get_template_part(
						'course/partials/tabs/reviews/list',
						'',
						array(
							'splms_reviews'       => $splms_reviews,
							'splms_course_id'     => $splms_course_id,
							'splms_total_reviews' => $splms_total_reviews,
							'splms_total_pages'   => $splms_total_pages,
						)
					);
					?>
				<?php } else { ?>
					<?php
					splms_get_template_part(
						'course/partials/tabs/reviews/empty-state',
						'',
						array(
							'splms_permission' => $splms_permission,
						)
					);
					?>
				<?php } ?>
			</div>

			<?php
			/**
			 * Hook: splms_after_reviews.
			 *
			 * @since 1.0.0
			 *
			 * @param int $course_id Course ID.
			 */
			do_action( 'splms_after_reviews', $splms_course_id );
			?>

			<?php if ( $splms_user_id ) { ?>
				<?php
				splms_get_template_part(
					'course/partials/tabs/reviews/submission-modal',
					'',
					array(
						'splms_course_id'  => $splms_course_id,
						'splms_permission' => $splms_permission,
					)
				);
				?>
			<?php } else { ?>
				<?php
				splms_get_template_part(
					'course/partials/tabs/reviews/login-prompt'
				);
				?>
			<?php } ?>
		</div>
	</div>
</div>
