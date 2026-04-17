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

$reviews       = isset( $args['reviews'] ) ? $args['reviews'] : array();
$course_id     = isset( $args['course_id'] ) ? $args['course_id'] : get_the_ID();
$total_reviews = isset( $args['total_reviews'] ) ? $args['total_reviews'] : 0;
$total_pages   = isset( $args['total_pages'] ) ? $args['total_pages'] : 1;

if ( empty( $reviews ) || ! is_array( $reviews ) ) {
	return;
}
?>

<div class="reviews-list-container">
	<div class="reviews-list course-reviews-list">
		<?php
		foreach ( $reviews as $review ) {
			splms_get_template_part(
				'course/partials/tabs/reviews/card',
				'',
				array(
					'review'    => $review,
					'course_id' => $course_id,
				)
			);
		}
		?>
	</div>

	<?php
	// Only show Load More button if there are more pages available.
	if ( $total_pages > 1 ) {
		?>
		<div class="load-more-reviews">
			<button class="splms-btn splms-btn-outline load-more-btn" data-course-id="<?php echo esc_attr( $course_id ); ?>" data-page="2" data-total-pages="<?php echo esc_attr( $total_pages ); ?>">
				<?php esc_html_e( 'Load More Reviews', 'skillpulse-lms' ); ?>
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M19 14L12 21L5 14M12 21V3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
			</button>
		</div>
	<?php } ?>
</div>
