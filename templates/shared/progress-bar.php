<?php
/**
 * Unified Progress Bar Component
 *
 * This template can be overridden by copying it to yourtheme/skillpulse-lms/shared/progress-bar.php
 *
 * @package SkillPulse_LMS
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get the progress data passed to this template.
$progress_data    = isset( $args['progress_data'] ) ? $args['progress_data'] : array();
$show_info        = isset( $args['show_info'] ) ? $args['show_info'] : true;
$show_label       = isset( $args['show_label'] ) ? $args['show_label'] : true;
$show_percentage  = isset( $args['show_percentage'] ) ? $args['show_percentage'] : true;
$show_items_count = isset( $args['show_items_count'] ) ? $args['show_items_count'] : false;
$custom_label     = isset( $args['label'] ) ? $args['label'] : __( 'Progress', 'skillpulse-lms' );
$css_class        = isset( $args['css_class'] ) ? $args['css_class'] : '';

// Extract progress data.
$percentage      = isset( $progress_data['percentage'] ) ? floatval( $progress_data['percentage'] ) : 0;
$completed_items = isset( $progress_data['completed'] ) ? intval( $progress_data['completed'] ) : 0;
$total_items     = isset( $progress_data['total'] ) ? intval( $progress_data['total'] ) : 0;

// Don't render if no meaningful data.
if ( $total_items <= 0 && $percentage <= 0 ) {
	return;
}

// Ensure percentage is within bounds.
$percentage = max( 0, min( 100, $percentage ) );
?>

<div class="splms-content-progress <?php echo esc_attr( $css_class ); ?>">
	<?php if ( $show_info ) : ?>
		<div class="splms-progress-info">
			<?php if ( $show_label ) : ?>
				<span class="splms-progress-label"><?php echo esc_html( $custom_label ); ?></span>
			<?php endif; ?>

			<div class="splms-progress-stats">
				<?php if ( $show_items_count && $total_items > 0 ) : ?>
					<span class="splms-progress-count">
						<?php
						printf(
						/* translators: 1: Completed items count, 2: Total items count */
							esc_html__( '%1$s of %2$s completed', 'skillpulse-lms' ),
							'<span class="completed">' . esc_html( $completed_items ) . '</span>',
							'<span class="total">' . esc_html( $total_items ) . '</span>'
						);
						?>
					</span>
				<?php endif; ?>

				<?php if ( $show_percentage ) : ?>
					<span class="splms-progress-percentage"><?php echo esc_html( round( $percentage, 1 ) ); ?>%</span>
				<?php endif; ?>
			</div>
		</div>
	<?php endif; ?>

	<?php
	/* translators: 1: Progress  */
	$progress_label = sprintf( __( 'Progress: %s%%', 'skillpulse-lms' ), round( $percentage, 1 ) );
	?>

	<div class="splms-progress-bar">
		<div
			class="splms-progress-fill"
			style="--progress-width: <?php echo esc_attr( $percentage ); ?>%; width: <?php echo esc_attr( $percentage ); ?>%;"
			aria-valuenow="<?php echo esc_attr( round( $percentage, 1 ) ); ?>"
			aria-valuemin="0"
			aria-valuemax="100"
			role="progressbar"
			aria-label="<?php echo esc_attr( $progress_label ); ?>"
		></div>
	</div>
</div>