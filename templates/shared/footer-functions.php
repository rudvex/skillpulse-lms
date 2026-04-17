<?php
/**
 * Footer Template Functions
 *
 * @package SkillPulse_LMS
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Render footer content
 */
function splms_render_footer_content() {
	?>
	<div class="splms-footer-container">

		<!-- Footer Bottom -->
		<div class="splms-footer-bottom">
			<div class="splms-footer-bottom-content">
				<div class="splms-footer-copyright">
					<p>
						<?php
						printf(
						/* translators: %1$s: Current year, %2$s: Site name. */
							esc_html__( '© %1$s %2$s. All rights reserved.', 'skillpulse-lms' ),
							esc_html( gmdate( 'Y' ) ),
							esc_html( get_bloginfo( 'name' ) )
						);
						?>
					</p>
				</div>

				<div class="splms-footer-legal">
					<?php splms_render_footer_legal_links(); ?>
					<?php splms_render_social_links(); ?>
				</div>
			</div>
		</div>
	</div>
	<?php
}

/**
 * Render social media links
 */
function splms_render_social_links() {
	$splms_social_links = apply_filters(
		'splms_footer_social_links',
		array(
			'facebook'  => 'facebook.com',
			'twitter'   => '',
			'linkedin'  => 'linkedin.com',
			'instagram' => 'instagram.com',
			'youtube'   => 'youtube.com',
		)
	);

	$splms_social_icons = array(
		'facebook'  => '<path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>',
		'twitter'   => '<path d="M23 3a10.9 10.9 0 0 1-3.14 1.53 4.48 4.48 0 0 0-7.86 3v1A10.66 10.66 0 0 1 3 4s-4 9 5 13a11.64 11.64 0 0 1-7 2c9 5 20 0 20-11.5a4.5 4.5 0 0 0-.08-.83A7.72 7.72 0 0 0 23 3z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>',
		'linkedin'  => '<path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><rect x="2" y="9" width="4" height="12" stroke="currentColor" stroke-width="2"/><circle cx="4" cy="4" r="2" stroke="currentColor" stroke-width="2"/>',
		'instagram' => '<rect x="2" y="2" width="20" height="20" rx="5" ry="5" stroke="currentColor" stroke-width="2"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z" stroke="currentColor" stroke-width="2"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>',
		'youtube'   => '<path d="M22.54 6.42a2.78 2.78 0 0 0-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 0 0-1.94 2A29 29 0 0 0 1 11.75a29 29 0 0 0 .46 5.33A2.78 2.78 0 0 0 3.4 19c1.72.46 8.6.46 8.6.46s6.88 0 8.6-.46a2.78 2.78 0 0 0 1.94-2 29 29 0 0 0 .46-5.25 29 29 0 0 0-.46-5.33z" stroke="currentColor" stroke-width="2"/><polygon points="9.75,15.02 15.5,11.75 9.75,8.48" stroke="currentColor" stroke-width="2"/>',
	);

	$splms_social_labels = array(
		'facebook'  => __( 'Facebook', 'skillpulse-lms' ),
		'twitter'   => __( 'Twitter', 'skillpulse-lms' ),
		'linkedin'  => __( 'LinkedIn', 'skillpulse-lms' ),
		'instagram' => __( 'Instagram', 'skillpulse-lms' ),
		'youtube'   => __( 'YouTube', 'skillpulse-lms' ),
	);

	$splms_has_social_links = false;
	foreach ( $splms_social_links as $splms_link ) {
		if ( ! empty( $splms_link ) ) {
			$splms_has_social_links = true;
			break;
		}
	}

	if ( $splms_has_social_links ) {
		?>
		<div class="splms-social-links">
			<?php foreach ( $splms_social_links as $splms_platform => $splms_url ) { ?>
				<?php if ( ! empty( $splms_url ) ) { ?>
					<a href="<?php echo esc_url( $splms_url ); ?>"
						class="splms-social-link splms-social-<?php echo esc_attr( $splms_platform ); ?>"
						target="_blank"
						rel="noopener noreferrer"
						aria-label="<?php echo esc_attr( $splms_social_labels[ $splms_platform ] ); ?>">
						<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<?php echo wp_kses_post( $splms_social_icons[ $splms_platform ] ); ?>
						</svg>
					</a>
				<?php } ?>
			<?php } ?>
		</div>
		<?php
	}
}

/**
 * Render footer legal links
 */
function splms_render_footer_legal_links() {
	// Get privacy policy URL - check our setting first, then WordPress default.
	$splms_privacy_page_id = splms_get_setting( 'privacy_policy_page_id', 0 );
	$splms_privacy_url     = '';
	if ( $splms_privacy_page_id ) {
		$splms_privacy_page = get_post( $splms_privacy_page_id );
		if ( $splms_privacy_page && 'publish' === $splms_privacy_page->post_status ) {
			$splms_privacy_url = get_permalink( $splms_privacy_page_id );
		}
	}
	if ( empty( $splms_privacy_url ) ) {
		$splms_privacy_url = get_privacy_policy_url();
	}

	// Get terms and conditions URL from our setting.
	$splms_terms_page_id = splms_get_setting( 'terms_conditions_page_id', 0 );
	$splms_terms_url     = '';
	if ( $splms_terms_page_id ) {
		$splms_terms_page = get_post( $splms_terms_page_id );
		if ( $splms_terms_page && 'publish' === $splms_terms_page->post_status ) {
			$splms_terms_url = get_permalink( $splms_terms_page_id );
		}
	}

	$splms_legal_links = apply_filters(
		'splms_footer_legal_links',
		array(
			array(
				'title' => __( 'Privacy Policy', 'skillpulse-lms' ),
				'url'   => $splms_privacy_url,
			),
			array(
				'title' => __( 'Terms of Service', 'skillpulse-lms' ),
				'url'   => $splms_terms_url,
			),
			array(
				'title' => __( 'Cookie Policy', 'skillpulse-lms' ),
				'url'   => '#',
			),
		)
	);

	// Filter out links with empty URLs.
	$splms_legal_links = array_filter(
		$splms_legal_links,
		function ( $splms_link ) {
			return ! empty( $splms_link['url'] ) && '#' !== $splms_link['url'];
		}
	);

	if ( ! empty( $splms_legal_links ) ) {
		?>
		<ul class="splms-legal-links">
			<?php foreach ( $splms_legal_links as $splms_index => $splms_link ) { ?>
				<li class="splms-legal-link-item">
					<a href="<?php echo esc_url( $splms_link['url'] ); ?>" class="splms-legal-link">
						<?php echo esc_html( $splms_link['title'] ); ?>
					</a>
					<?php if ( $splms_index < count( $splms_legal_links ) - 1 ) { ?>
						<span class="splms-legal-separator">|</span>
					<?php } ?>
				</li>
			<?php } ?>
		</ul>
		<?php
	}
}

?>
