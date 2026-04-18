<?php
/**
 * Course Reviews Controls
 *
 * Displays review sorting and filtering controls, plus write review button.
 *
 * This template can be overridden by copying it to yourtheme/skillpulse-lms/course/partials/tabs/reviews/controls.php.
 *
 * @package SkillPulse_LMS
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$splms_course_id  = isset( $args['splms_course_id'] ) ? $args['splms_course_id'] : get_the_ID();
$splms_user_id    = isset( $args['splms_user_id'] ) ? $args['splms_user_id'] : get_current_user_id();
$splms_permission = isset( $args['splms_permission'] ) ? $args['splms_permission'] : array();
?>

<div class="reviews-controls">
	<div class="reviews-header">
		<h3><?php esc_html_e( 'Reviews', 'skillpulse-lms' ); ?></h3>

		<div class="reviews-actions">
			<?php if ( $splms_user_id ) { ?>
				<?php
				// Check if user can review.
				if ( empty( $splms_permission ) ) {
					$splms_permission = SPLMS_Review_Permissions::can_user_review( $splms_user_id, $splms_course_id );
				}
				?>
				<?php if ( $splms_permission['can_review'] ) { ?>
					<button class="btn btn-primary write-review-btn" data-course-id="<?php echo esc_attr( $splms_course_id ); ?>">
						<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M12 4V20M20 12H4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
						</svg>
						<?php esc_html_e( 'Write a Review', 'skillpulse-lms' ); ?>
					</button>
				<?php } else { ?>
					<button class="btn btn-secondary" disabled title="<?php echo esc_attr( $splms_permission['message'] ); ?>">
						<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M12 2L3 7L12 12L21 7L12 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
						<?php esc_html_e( 'Review Unavailable', 'skillpulse-lms' ); ?>
					</button>
				<?php } ?>
			<?php } ?>

			<div class="reviews-filter">
				<label for="reviews-sort-<?php echo esc_attr( $splms_course_id ); ?>" class="visually-hidden"><?php esc_html_e( 'Sort reviews by', 'skillpulse-lms' ); ?></label>
				<select id="reviews-sort-<?php echo esc_attr( $splms_course_id ); ?>" class="reviews-sort" data-course-id="<?php echo esc_attr( $splms_course_id ); ?>">
					<option value="newest"><?php esc_html_e( 'Newest first', 'skillpulse-lms' ); ?></option>
					<option value="oldest"><?php esc_html_e( 'Oldest first', 'skillpulse-lms' ); ?></option>
					<option value="highest"><?php esc_html_e( 'Highest rated', 'skillpulse-lms' ); ?></option>
					<option value="lowest"><?php esc_html_e( 'Lowest rated', 'skillpulse-lms' ); ?></option>
					<option value="helpful"><?php esc_html_e( 'Most helpful', 'skillpulse-lms' ); ?></option>
				</select>
			</div>
		</div>
	</div>
</div>
