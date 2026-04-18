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
$splms_progress_data    = isset( $args['splms_progress_data'] ) ? $args['splms_progress_data'] : array();
$splms_show_info        = isset( $args['splms_show_info'] ) ? $args['splms_show_info'] : true;
$splms_show_label       = isset( $args['splms_show_label'] ) ? $args['splms_show_label'] : true;
$splms_show_percentage  = isset( $args['splms_show_percentage'] ) ? $args['splms_show_percentage'] : true;
$splms_show_items_count = isset( $args['splms_show_items_count'] ) ? $args['splms_show_items_count'] : false;
$splms_custom_label     = isset( $args['label'] ) ? $args['label'] : __( 'Progress', 'skillpulse-lms' );
$splms_css_class        = isset( $args['splms_css_class'] ) ? $args['splms_css_class'] : '';

// Extract progress data.
$splms_percentage      = isset( $splms_progress_data['percentage'] ) ? floatval( $splms_progress_data['percentage'] ) : 0;
$splms_completed_items = isset( $splms_progress_data['completed'] ) ? intval( $splms_progress_data['completed'] ) : 0;
$splms_total_items     = isset( $splms_progress_data['total'] ) ? intval( $splms_progress_data['total'] ) : 0;

// Don't render if no meaningful data.
if ( $splms_total_items <= 0 && $splms_percentage <= 0 ) {
	return;
}

// Ensure percentage is within bounds.
$splms_percentage = max( 0, min( 100, $splms_percentage ) );
?>

<div class="splms-content-progress <?php echo esc_attr( $splms_css_class ); ?>">
	<?php if ( $splms_show_info ) : ?>
		<div class="splms-progress-info">
			<?php if ( $splms_show_label ) : ?>
				<span class="splms-progress-label"><?php echo esc_html( $splms_custom_label ); ?></span>
			<?php endif; ?>

			<div class="splms-progress-stats">
				<?php if ( $splms_show_items_count && $splms_total_items > 0 ) : ?>
					<span class="splms-progress-count">
						<?php
						printf(
						/* translators: 1: Completed items count, 2: Total items count */
							esc_html__( '%1$s of %2$s completed', 'skillpulse-lms' ),
							'<span class="completed">' . esc_html( $splms_completed_items ) . '</span>',
							'<span class="total">' . esc_html( $splms_total_items ) . '</span>'
						);
						?>
					</span>
				<?php endif; ?>

				<?php if ( $splms_show_percentage ) : ?>
					<span class="splms-progress-percentage"><?php echo esc_html( round( $splms_percentage, 1 ) ); ?>%</span>
				<?php endif; ?>
			</div>
		</div>
	<?php endif; ?>

	<?php
	/* translators: 1: Progress  */
	$splms_progress_label = sprintf( __( 'Progress: %s%%', 'skillpulse-lms' ), round( $splms_percentage, 1 ) );
	?>

	<div class="splms-progress-bar">
		<div
			class="splms-progress-fill"
			style="--progress-width: <?php echo esc_attr( $splms_percentage ); ?>%; width: <?php echo esc_attr( $splms_percentage ); ?>%;"
			aria-valuenow="<?php echo esc_attr( round( $splms_percentage, 1 ) ); ?>"
			aria-valuemin="0"
			aria-valuemax="100"
			role="progressbar"
			aria-label="<?php echo esc_attr( $splms_progress_label ); ?>"
		></div>
	</div>
</div>