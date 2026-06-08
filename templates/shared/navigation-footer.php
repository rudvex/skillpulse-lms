<?php
/**
 * Shared Navigation Footer Component
 *
 * Provides consistent navigation footer UI for lessons, quizzes, and other content types.
 * This template can be overridden by copying it to yourtheme/skillpulse-lms/shared/navigation-footer.php
 *
 * @package SPLMS
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}



// Required variables that must be passed to this template via splms_get_template_part():
// $context_class - Additional CSS class for context (e.g., 'splms-lesson-footer', 'splms-quiz-footer')
// $navigation - Navigation array with previous/next data and can_navigate_next
// $center_content - HTML content for the center area
// $current_item_id - Current lesson/quiz ID
// $user_id - Current user ID.

// Extract variables passed through splms_get_template_part() args.
// Note: extract() is called by splms_get_template() function before including this template.
extract( $args ); // phpcs:ignore.

// Set defaults for optional variables.
$splms_context_class   = isset( $splms_context_class ) ? $splms_context_class : '';
$splms_center_content  = isset( $splms_center_content ) ? $splms_center_content : '';
$splms_navigation      = isset( $splms_navigation ) ? $splms_navigation : array();
$splms_current_item_id = isset( $splms_current_item_id ) ? $splms_current_item_id : 0;
$splms_user_id         = isset( $splms_user_id ) ? $splms_user_id : 0;

// Extract navigation data.
$splms_has_prev = ! empty( $splms_navigation['previous'] );
$splms_has_next = ! empty( $splms_navigation['next'] );
$splms_can_next = ! empty( $splms_navigation['can_navigate_next'] );

// Calculate navigation state.
$splms_next_disabled = $splms_has_next && ! $splms_can_next;

// Get navigation URLs and labels.
$splms_prev_url   = $splms_has_prev ? $splms_navigation['previous']['url'] : '';
$splms_prev_title = $splms_has_prev ? $splms_navigation['previous']['title'] : '';
$splms_prev_type  = $splms_has_prev && isset( $splms_navigation['previous']['type'] ) ? $splms_navigation['previous']['type'] : 'lesson';
$splms_next_url   = $splms_has_next ? $splms_navigation['next']['url'] : '';
$splms_next_title = $splms_has_next ? $splms_navigation['next']['title'] : '';
$splms_next_type  = $splms_has_next && isset( $splms_navigation['next']['type'] ) ? $splms_navigation['next']['type'] : 'lesson';

// Generate button labels.
$splms_prev_label = 'quiz' === $splms_prev_type ? __( 'Previous Quiz', 'skillpulse-lms' ) : __( 'Previous Lesson', 'skillpulse-lms' );
$splms_next_label = 'quiz' === $splms_next_type ? __( 'Next Quiz', 'skillpulse-lms' ) : __( 'Next Lesson', 'skillpulse-lms' );

?>

<footer class="splms-fullscreen-footer <?php echo esc_attr( $splms_context_class ); ?>" role="contentinfo">
	<div class="splms-footer-content">

		<!-- Footer Navigation -->
		<div class="splms-footer-navigation">

			<!-- Previous Item Button -->
			<?php if ( $splms_has_prev ) : ?>
				<a href="<?php echo esc_url( $splms_prev_url ); ?>" class="splms-btn splms-btn-secondary splms-btn-prev" aria-label="<?php echo esc_attr( $splms_prev_label ); ?>">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M19 12H5M5 12L12 19M5 12L12 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
					<span class="splms-btn-text">
						<?php echo esc_html( $splms_prev_label ); ?>
					</span>
				</a>
			<?php else : ?>
				<div class="splms-btn-placeholder"></div>
			<?php endif; ?>

			<!-- Center Content (Dynamic) -->
			<div class="splms-footer-center">
				<?php
				echo wp_kses_post( $splms_center_content );
				?>
			</div>

			<!-- Next Item Button -->
			<?php if ( $splms_has_next ) : ?>
				<?php if ( $splms_next_disabled ) : ?>
					<button type="button" class="splms-btn splms-btn-primary splms-btn-next is-disabled" disabled aria-label="<?php echo esc_attr( $splms_next_label ); ?>" aria-disabled="true" data-url="<?php echo esc_url( $splms_next_url ); ?>">
						<span class="splms-btn-text">
							<span class="splms-btn-label"><?php echo esc_html( $splms_next_label ); ?></span>
							<span class="splms-locked-badge">(<?php esc_html_e( 'Locked', 'skillpulse-lms' ); ?>)</span>
						</span>
						<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<rect x="3" y="11" width="18" height="11" rx="2" stroke="currentColor" stroke-width="2"/>
							<path d="M7 11V7C7 5.67392 7.52678 4.40215 8.46447 3.46447C9.40215 2.52678 10.6739 2 12 2C13.3261 2 14.5979 2.52678 15.5355 3.46447C16.4732 4.40215 17 5.67392 17 7V11" stroke="currentColor" stroke-width="2"/>
						</svg>
					</button>
				<?php else : ?>
					<a href="<?php echo esc_url( $splms_next_url ); ?>" class="splms-btn splms-btn-primary splms-btn-next" aria-label="<?php echo esc_attr( $splms_next_label ); ?>">
						<span class="splms-btn-text">
							<?php echo esc_html( $splms_next_label ); ?>
						</span>
						<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M5 12H19M19 12L12 5M19 12L12 19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
					</a>
				<?php endif; ?>
			<?php else : ?>
				<div class="splms-btn-placeholder"></div>
			<?php endif; ?>

		</div>

	</div>
</footer>