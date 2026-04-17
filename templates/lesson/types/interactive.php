<?php
/**
 * Lesson Type: Interactive
 *
 * This template can be overridden by copying it to yourtheme/skillpulse-lms/lesson/types/interactive.php
 *
 * @package SkillPulse_LMS
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get passed variables.
$lesson_embed_code = isset( $args['lesson_embed_code'] ) ? $args['lesson_embed_code'] : '';

if ( ! $lesson_embed_code ) {
	return;
}

$lesson_embed_code = trim( $lesson_embed_code );

// Check if content contains shortcodes.
$has_shortcodes = splms_has_shortcodes( $lesson_embed_code );
?>

<div class="splms-interactive-content">
	<?php if ( $has_shortcodes ) : ?>
		<?php
		// Extract shortcode tags from the original content to check if they're registered.
		preg_match_all( '/\[([a-zA-Z0-9_-]+)/', $lesson_embed_code, $shortcode_matches );

		$shortcode_error = false;
		if ( ! empty( $shortcode_matches[1] ) ) {
			foreach ( $shortcode_matches[1] as $shortcode_tag ) {
				// Check if shortcode is registered.
				if ( ! shortcode_exists( $shortcode_tag ) ) {
					$shortcode_error = $shortcode_tag;
					break;
				}
			}
		}

		if ( $shortcode_error ) :
			?>
			<div class="splms-notice splms-notice--warning">
				<div class="splms-notice__icon">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M12 9V13M12 17H12.01M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
				</div>
				<div class="splms-notice__content">
					<p>
						<?php
						/* translators: %s: Shortcode tag name. */
						echo esc_html( sprintf( __( 'The shortcode [%s] could not be rendered. Please ensure the related plugin is active.', 'skillpulse-lms' ), $shortcode_error ) );
						?>
					</p>
				</div>
			</div>
		<?php else : ?>
			<?php
			// Process shortcodes and sanitize output.
			$processed = do_shortcode( $lesson_embed_code );
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Output sanitized with wp_kses_post.
			echo wp_kses_post( $processed );
			?>
		<?php endif; ?>

	<?php else : ?>
		<?php
		// Treat as HTML embed code.
		// Check if it contains embed tags.
		$has_embed_tags = (
			false !== strpos( $lesson_embed_code, '<iframe' ) ||
			false !== strpos( $lesson_embed_code, '<embed' ) ||
			false !== strpos( $lesson_embed_code, '<object' ) ||
			false !== strpos( $lesson_embed_code, '<script' )
		);

		if ( $has_embed_tags ) :
			// Validate iframe src if present.
			$iframe_error = false;
			if ( preg_match( '/<iframe[^>]*>/i', $lesson_embed_code, $iframe_match ) ) {
				// Check if iframe has src attribute.
				if ( ! preg_match( '/src\s*=\s*["\']([^"\']+)["\']/', $iframe_match[0] ) ) {
					$iframe_error = true;
				}
			}

			if ( $iframe_error ) :
				?>
				<div class="splms-notice splms-notice--error">
					<div class="splms-notice__icon">
						<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M12 9V13M12 17H12.01M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
					</div>
					<div class="splms-notice__content">
						<p><?php esc_html_e( 'Invalid embed source URL. Please check the iframe src attribute.', 'skillpulse-lms' ); ?></p>
					</div>
				</div>
			<?php else : ?>
				<?php
				// Sanitize embed HTML with custom allowed tags.
				$allowed_html = splms_allowed_embed_html();
				$sanitized    = wp_kses( $lesson_embed_code, $allowed_html );

				// Check if sanitization removed important tags (indicates invalid HTML).
				if ( empty( $sanitized ) || ( $has_embed_tags && ! preg_match( '/<(iframe|embed|object|script)/i', $sanitized ) ) ) :
					?>
					<div class="splms-notice splms-notice--warning">
						<div class="splms-notice__icon">
							<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M12 9V13M12 17H12.01M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
							</svg>
						</div>
						<div class="splms-notice__content">
							<p><?php esc_html_e( 'The provided embed code could not be displayed. Please check the iframe or embed source.', 'skillpulse-lms' ); ?></p>
						</div>
					</div>
				<?php else : ?>
					<?php
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Output sanitized with wp_kses.
					echo $sanitized;
					?>
				<?php endif; ?>
			<?php endif; ?>

		<?php elseif ( preg_match( '/<[^>]+>/', $lesson_embed_code ) ) : ?>
			<?php
			// Contains HTML tags but not embed tags - sanitize as regular HTML.
			$allowed_html = splms_allowed_embed_html();
			$sanitized    = wp_kses( $lesson_embed_code, $allowed_html );

			if ( ! empty( $sanitized ) ) :
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Output sanitized with wp_kses.
				echo $sanitized;
			else :
				?>
				<div class="splms-notice splms-notice--warning">
					<div class="splms-notice__icon">
						<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M12 9V13M12 17H12.01M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
					</div>
					<div class="splms-notice__content">
						<p><?php esc_html_e( 'The provided embed code could not be displayed. Please check the iframe or embed source.', 'skillpulse-lms' ); ?></p>
					</div>
				</div>
			<?php endif; ?>

		<?php elseif ( filter_var( trim( $lesson_embed_code ), FILTER_VALIDATE_URL ) ) : ?>
			<!-- Plain URL detected - show info notice. -->
			<div class="splms-notice splms-notice--info">
				<div class="splms-notice__icon">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M12 9V13M12 17H12.01M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
				</div>
				<div class="splms-notice__content">
					<p>
						<?php esc_html_e( 'Please use an iframe embed code or shortcode for interactive content. URL detected:', 'skillpulse-lms' ); ?>
						<a href="<?php echo esc_url( $lesson_embed_code ); ?>" target="_blank"><?php echo esc_html( $lesson_embed_code ); ?></a>
					</p>
				</div>
			</div>

		<?php else : ?>
			<?php
			// Plain text - treat as regular content.
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Output sanitized with wp_kses_post.
			echo wp_kses_post( $lesson_embed_code );
			?>
		<?php endif; ?>
	<?php endif; ?>
</div>
