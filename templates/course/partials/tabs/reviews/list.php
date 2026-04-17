<?php
/**
 * Reviews List
 *
 * Displays list of reviews with Load More functionality.
 *
 * This template can be overridden by copying it to yourtheme/skillpulse-lms/course/partials/tabs/reviews/list.php.
 *
 * @package SkillPulse_LMS
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}



$splms_reviews       = isset( $args['splms_reviews'] ) ? $args['splms_reviews'] : array();
$splms_course_id     = isset( $args['splms_course_id'] ) ? $args['splms_course_id'] : get_the_ID();
$splms_total_reviews = isset( $args['splms_total_reviews'] ) ? $args['splms_total_reviews'] : 0;
$splms_total_pages   = isset( $args['splms_total_pages'] ) ? $args['splms_total_pages'] : 1;

if ( empty( $splms_reviews ) || ! is_array( $splms_reviews ) ) {
	return;
}
?>

<div class="reviews-list-container">
	<div class="reviews-list course-reviews-list">
		<?php
		foreach ( $splms_reviews as $splms_review ) {
			splms_get_template_part(
				'course/partials/tabs/reviews/card',
				'',
				array(
					'splms_review'    => $splms_review,
					'splms_course_id' => $splms_course_id,
				)
			);
		}
		?>
	</div>

	<?php
	// Only show Load More button if there are more pages available.
	if ( $splms_total_pages > 1 ) {
		?>
		<div class="load-more-reviews">
			<button class="splms-btn splms-btn-outline load-more-btn" data-course-id="<?php echo esc_attr( $splms_course_id ); ?>" data-page="2" data-total-pages="<?php echo esc_attr( $splms_total_pages ); ?>">
				<?php esc_html_e( 'Load More Reviews', 'skillpulse-lms' ); ?>
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M19 14L12 21L5 14M12 21V3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
			</button>
		</div>
	<?php } ?>
</div>
