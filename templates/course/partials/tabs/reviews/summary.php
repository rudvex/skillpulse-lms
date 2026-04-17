<?php
/**
 * Course Reviews Summary
 *
 * Displays overall rating score and rating breakdown.
 *
 * This template can be overridden by copying it to yourtheme/skillpulse-lms/course/partials/tabs/reviews/summary.php.
 *
 * @package SkillPulse_LMS
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$course_id = isset( $args['course_id'] ) ? $args['course_id'] : get_the_ID();

// Get rating summary using the new Review Manager.
$rating_summary = SPLMS_Review_Manager::get_rating_summary( $course_id );

// Set default values if keys are missing.
$rating_summary = array_merge(
	array(
		'average_rating'   => 0,
		'total_reviews'    => 0,
		'rating_breakdown' => array(
			'5' => 0,
			'4' => 0,
			'3' => 0,
			'2' => 0,
			'1' => 0,
		),
	),
	$rating_summary
);
?>

<!-- Reviews Summary -->
<div class="reviews-summary">
	<div class="overall-rating">
		<div class="rating-score">
			<span class="score-number"><?php echo number_format( $rating_summary['average_rating'], 1 ); ?></span>
			<div class="rating-stars">
				<?php for ( $i = 1; $i <= 5; $i++ ) { ?>
					<span class="star <?php echo esc_attr( $i <= $rating_summary['average_rating'] ? 'filled' : '' ); ?>">★</span>
				<?php } ?>
			</div>
			<p class="rating-text">
				<?php
				/* translators: %d: Total reviews. */
				$reviews_text = _n( 'Based on %d review', 'Based on %d reviews', $rating_summary['total_reviews'], 'skillpulse-lms' );
				/* translators: %d: Total reviews. */
				$reviews_text = sprintf( $reviews_text, (int) $rating_summary['total_reviews'] );
				echo esc_html( $reviews_text );
				?>
			</p>
		</div>
	</div>

	<div class="rating-breakdown">
		<?php foreach ( array( 5, 4, 3, 2, 1 ) as $stars ) { ?>
			<?php
			$count      = $rating_summary['rating_breakdown'][ (string) $stars ];
			$percentage = $rating_summary['total_reviews'] > 0 ? round( ( $count / $rating_summary['total_reviews'] ) * 100 ) : 0;
			?>
			<div class="rating-bar">
				<span class="rating-label"><?php echo esc_html( $stars ); ?> <?php esc_html_e( 'stars', 'skillpulse-lms' ); ?></span>
				<div class="progress-bar">
					<div class="progress-fill" style="width: <?php echo esc_attr( $percentage ); ?>%"></div>
				</div>
				<span class="rating-percentage"><?php echo esc_html( $percentage ); ?>%</span>
			</div>
		<?php } ?>
	</div>
</div>
