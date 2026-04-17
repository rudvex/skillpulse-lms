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
$splms_lesson_embed_code = isset( $args['splms_lesson_embed_code'] ) ? $args['splms_lesson_embed_code'] : '';

if ( ! $splms_lesson_embed_code ) {
	return;
}

$splms_lesson_embed_code = trim( $splms_lesson_embed_code );

// Check if content contains shortcodes.
$splms_has_shortcodes = splms_has_shortcodes( $splms_lesson_embed_code );
?>

<div class="splms-interactive-content">
	<?php if ( $splms_has_shortcodes ) : ?>
		<?php
		// Extract shortcode tags from the original content to check if they're registered.
		preg_match_all( '/\[([a-zA-Z0-9_-]+)/', $splms_lesson_embed_code, $splms_shortcode_matches );

		$splms_shortcode_error = false;
		if ( ! empty( $splms_shortcode_matches[1] ) ) {
			foreach ( $splms_shortcode_matches[1] as $splms_shortcode_tag ) {
				// Check if shortcode is registered.
				if ( ! shortcode_exists( $splms_shortcode_tag ) ) {
					$splms_shortcode_error = $splms_shortcode_tag;
					break;
				}
			}
		}

		if ( $splms_shortcode_error ) :
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
						echo esc_html( sprintf( __( 'The shortcode [%s] could not be rendered. Please ensure the related plugin is active.', 'skillpulse-lms' ), $splms_shortcode_error ) );
						?>
					</p>
				</div>
			</div>
		<?php else : ?>
			<?php
			// Process shortcodes and sanitize output.
			$splms_processed = do_shortcode( $splms_lesson_embed_code );
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Output sanitized with wp_kses_post.
			echo wp_kses_post( $splms_processed );
			?>
		<?php endif; ?>

	<?php else : ?>
		<?php
		// Treat as HTML embed code.
		// Check if it contains embed tags.
		$splms_has_embed_tags = (
			false !== strpos( $splms_lesson_embed_code, '<iframe' ) ||
			false !== strpos( $splms_lesson_embed_code, '<embed' ) ||
			false !== strpos( $splms_lesson_embed_code, '<object' ) ||
			false !== strpos( $splms_lesson_embed_code, '<script' )
		);

		if ( $splms_has_embed_tags ) :
			// Validate iframe src if present.
			$splms_iframe_error = false;
			if ( preg_match( '/<iframe[^>]*>/i', $splms_lesson_embed_code, $splms_iframe_match ) ) {
				// Check if iframe has src attribute.
				if ( ! preg_match( '/src\s*=\s*["\']([^"\']+)["\']/', $splms_iframe_match[0] ) ) {
					$splms_iframe_error = true;
				}
			}

			if ( $splms_iframe_error ) :
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
				$splms_allowed_html = splms_allowed_embed_html();
				$splms_sanitized    = wp_kses( $splms_lesson_embed_code, $splms_allowed_html );

				// Check if sanitization removed important tags (indicates invalid HTML).
				if ( empty( $splms_sanitized ) || ( $splms_has_embed_tags && ! preg_match( '/<(iframe|embed|object|script)/i', $splms_sanitized ) ) ) :
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
					echo $splms_sanitized; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Content is sanitized via wp_kses above.
					?>
				<?php endif; ?>
			<?php endif; ?>

		<?php elseif ( preg_match( '/<[^>]+>/', $splms_lesson_embed_code ) ) : ?>
			<?php
			// Contains HTML tags but not embed tags - sanitize as regular HTML.
			$splms_allowed_html = splms_allowed_embed_html();
			$splms_sanitized    = wp_kses( $splms_lesson_embed_code, $splms_allowed_html );

			if ( ! empty( $splms_sanitized ) ) :
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Output sanitized with wp_kses.
				echo $splms_sanitized; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Content is sanitized via wp_kses above.
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

		<?php elseif ( filter_var( trim( $splms_lesson_embed_code ), FILTER_VALIDATE_URL ) ) : ?>
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
						<a href="<?php echo esc_url( $splms_lesson_embed_code ); ?>" target="_blank"><?php echo esc_html( $splms_lesson_embed_code ); ?></a>
					</p>
				</div>
			</div>

		<?php else : ?>
			<?php
			// Plain text - treat as regular content.
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Output sanitized with wp_kses_post.
			echo wp_kses_post( $splms_lesson_embed_code );
			?>
		<?php endif; ?>
	<?php endif; ?>
</div>
