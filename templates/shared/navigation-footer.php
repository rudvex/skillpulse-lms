<?php
/**
 * Shared Navigation Footer Component
 *
 * Provides consistent navigation footer UI for lessons, quizzes, and other content types.
 * This template can be overridden by copying it to yourtheme/skillpulse-lms/shared/navigation-footer.php
 *
 * @package SkillPulse_LMS
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
$context_class   = isset( $context_class ) ? $context_class : '';
$center_content  = isset( $center_content ) ? $center_content : '';
$navigation      = isset( $navigation ) ? $navigation : array();
$current_item_id = isset( $current_item_id ) ? $current_item_id : 0;
$user_id         = isset( $user_id ) ? $user_id : 0;

// Extract navigation data.
$has_prev = ! empty( $navigation['previous'] );
$has_next = ! empty( $navigation['next'] );
$can_next = ! empty( $navigation['can_navigate_next'] );

// Calculate navigation state.
$next_disabled = $has_next && ! $can_next;

// Get navigation URLs and labels.
$prev_url   = $has_prev ? $navigation['previous']['url'] : '';
$prev_title = $has_prev ? $navigation['previous']['title'] : '';
$prev_type  = $has_prev && isset( $navigation['previous']['type'] ) ? $navigation['previous']['type'] : 'lesson';
$next_url   = $has_next ? $navigation['next']['url'] : '';
$next_title = $has_next ? $navigation['next']['title'] : '';
$next_type  = $has_next && isset( $navigation['next']['type'] ) ? $navigation['next']['type'] : 'lesson';

// Generate button labels.
$prev_label = 'quiz' === $prev_type ? __( 'Previous Quiz', 'skillpulse-lms' ) : __( 'Previous Lesson', 'skillpulse-lms' );
$next_label = 'quiz' === $next_type ? __( 'Next Quiz', 'skillpulse-lms' ) : __( 'Next Lesson', 'skillpulse-lms' );

?>

<footer class="splms-fullscreen-footer <?php echo esc_attr( $context_class ); ?>" role="contentinfo">
	<div class="splms-footer-content">

		<!-- Footer Navigation -->
		<div class="splms-footer-navigation">

			<!-- Previous Item Button -->
			<?php if ( $has_prev ) : ?>
				<a href="<?php echo esc_url( $prev_url ); ?>" class="splms-btn splms-btn-secondary splms-btn-prev" aria-label="<?php echo esc_attr( $prev_label ); ?>">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M19 12H5M5 12L12 19M5 12L12 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
					<span class="splms-btn-text">
						<?php echo esc_html( $prev_label ); ?>
					</span>
				</a>
			<?php else : ?>
				<div class="splms-btn-placeholder"></div>
			<?php endif; ?>

			<!-- Center Content (Dynamic) -->
			<div class="splms-footer-center">
				<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Content is expected to be pre-escaped HTML from calling template
				echo $center_content;
				?>
			</div>

			<!-- Next Item Button -->
			<?php if ( $has_next ) : ?>
				<?php if ( $next_disabled ) : ?>
					<button type="button" class="splms-btn splms-btn-primary splms-btn-next is-disabled" disabled aria-label="<?php echo esc_attr( $next_label ); ?>" aria-disabled="true" data-url="<?php echo esc_url( $next_url ); ?>">
						<span class="splms-btn-text">
							<span class="splms-btn-label"><?php echo esc_html( $next_label ); ?></span>
							<span class="splms-locked-badge">(<?php esc_html_e( 'Locked', 'skillpulse-lms' ); ?>)</span>
						</span>
						<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<rect x="3" y="11" width="18" height="11" rx="2" stroke="currentColor" stroke-width="2"/>
							<path d="M7 11V7C7 5.67392 7.52678 4.40215 8.46447 3.46447C9.40215 2.52678 10.6739 2 12 2C13.3261 2 14.5979 2.52678 15.5355 3.46447C16.4732 4.40215 17 5.67392 17 7V11" stroke="currentColor" stroke-width="2"/>
						</svg>
					</button>
				<?php else : ?>
					<a href="<?php echo esc_url( $next_url ); ?>" class="splms-btn splms-btn-primary splms-btn-next" aria-label="<?php echo esc_attr( $next_label ); ?>">
						<span class="splms-btn-text">
							<?php echo esc_html( $next_label ); ?>
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