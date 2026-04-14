<?php
/**
 * SkillPulse LMS Maintenance Mode Template
 *
 * Modern, LMS-focused maintenance page displayed when maintenance mode is enabled.
 *
 * @package SkillPulse_LMS
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Get maintenance mode settings.
$site_name   = get_bloginfo( 'name' );
$admin_email = get_option( 'admin_email' );

// Get customizable maintenance settings from maintenance_settings section.
$all_settings = SkillPulse_LMS_Settings::get_instance()->get_all_settings();

$maintenance_settings = isset( $all_settings['general']['maintenance_settings'] ) ? $all_settings['general']['maintenance_settings'] : array();

$maintenance_title         = isset( $maintenance_settings['maintenance_title'] ) ? $maintenance_settings['maintenance_title'] : __( 'Learning Platform Upgrade in Progress', 'skillpulse-lms' );
$maintenance_message       = isset( $maintenance_settings['maintenance_message'] ) ? $maintenance_settings['maintenance_message'] : __( 'We\'re enhancing your learning experience! Our platform is temporarily offline while we upgrade our systems to serve you better.', 'skillpulse-lms' );
$maintenance_features_html = isset( $maintenance_settings['maintenance_features'] ) ? $maintenance_settings['maintenance_features'] : '';
$show_features_section     = isset( $maintenance_settings['maintenance_show_features'] ) ? $maintenance_settings['maintenance_show_features'] : true;

// Default features HTML if none configured.
if ( empty( trim( $maintenance_features_html ) ) ) {
	$maintenance_features_html = '• ' . __( 'Enhanced course performance & loading', 'skillpulse-lms' ) . '<br>' .
								'• ' . __( 'New interactive learning features', 'skillpulse-lms' ) . '<br>' .
								'• ' . __( 'Improved mobile learning experience', 'skillpulse-lms' ) . '<br>' .
								'• ' . __( 'Advanced progress tracking system', 'skillpulse-lms' ) . '<br>' .
								'• ' . __( 'Updated certificate generation', 'skillpulse-lms' ) . '<br>' .
								'• ' . __( 'Enhanced quiz and assessment tools', 'skillpulse-lms' );
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="robots" content="noindex, nofollow">
	<meta name="theme-color" content="#7e75ff">
	<?php
	// translators: %s: Site name.
	$maintenance_title = sprintf( __( '%s - Learning Platform Upgrade', 'skillpulse-lms' ), $site_name );
	?>
	<title><?php echo esc_html( $maintenance_title ); ?></title>
	<?php wp_head(); ?>
</head>
<body class="splms-lms splms-maintenance">
	<?php wp_body_open(); ?>

	<div class="maintenance-container">
		<div class="maintenance-content">
			<!-- LMS Logo/Icon -->
			<div class="maintenance-logo">
				<svg width="80" height="80" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="lms-icon">
					<path d="M12 3L1 9L12 15L21 10.09V17H23V9M5 13.18V17.18L12 21L19 17.18V13.18L12 17L5 13.18Z" fill="currentColor"/>
				</svg>
			</div>

			<!-- Main Title -->
			<h1 class="maintenance-title">
				<span class="upgrade-icon">🔧</span>
				<?php echo esc_html( $maintenance_title ); ?>
			</h1>

			<!-- Main Message -->
			<div class="maintenance-message">
				<p class="lead-text">
					<?php echo esc_html( $maintenance_message ); ?>
				</p>
				<p class="assurance-text">
					<strong><?php esc_html_e( 'All your course progress and certificates are safely preserved.', 'skillpulse-lms' ); ?></strong>
				</p>
			</div>

			<?php if ( $show_features_section && ! empty( trim( $maintenance_features_html ) ) ) : ?>
			<!-- Upgrade Features -->
			<div class="maintenance-upgrades">
				<h3 class="upgrades-title">
					<span class="briefcase-icon">💼</span>
					<?php esc_html_e( 'What We\'re Working On:', 'skillpulse-lms' ); ?>
				</h3>
				<div class="maintenance-features-content">
					<?php echo wp_kses_post( $maintenance_features_html ); ?>
				</div>
			</div>
			<?php endif; ?>


			<!-- Data Safety Assurance -->
			<div class="data-safety">
				<h4 class="safety-title">
					<span class="shield-icon">🛡️</span>
					<?php esc_html_e( 'Your Learning Data is Safe:', 'skillpulse-lms' ); ?>
				</h4>
				<ul class="safety-list">
					<li><?php esc_html_e( 'All course progress automatically saved', 'skillpulse-lms' ); ?></li>
					<li><?php esc_html_e( 'Certificates and achievements preserved', 'skillpulse-lms' ); ?></li>
					<li><?php esc_html_e( 'Enrolled courses remain accessible', 'skillpulse-lms' ); ?></li>
					<li><?php esc_html_e( 'Personal learning analytics maintained', 'skillpulse-lms' ); ?></li>
				</ul>
			</div>

			<!-- Contact Information -->
			<div class="maintenance-contact">
				<p class="contact-text">
					<span class="email-icon">📧</span>
					<?php esc_html_e( 'Need urgent academic support?', 'skillpulse-lms' ); ?>
				</p>
				<p class="contact-details">
					<strong><?php esc_html_e( 'Contact:', 'skillpulse-lms' ); ?></strong>
					<a href="mailto:<?php echo esc_attr( $admin_email ); ?>" class="email-link">
						<?php echo esc_html( $admin_email ); ?>
					</a>
				</p>
			</div>

			<!-- Social Media Links -->
			<div class="maintenance-social">
				<p class="social-text">
					<span class="phone-icon">📱</span>
					<?php esc_html_e( 'Stay Updated:', 'skillpulse-lms' ); ?>
				</p>
				<div class="social-links">
					<?php
					/**
					 * Hook: splms_maintenance_social_links
					 *
					 * Allows plugins/themes to add social media links.
					 */
					do_action( 'splms_maintenance_social_links' );
					?>
				</div>
			</div>

			<!-- Footer -->
			<div class="maintenance-footer">
				<p class="maintenance-timestamp">
					<?php
					$timestamp_text = sprintf(
						/* translators: %s: Date and time. */
						esc_html__( 'Last updated: %s', 'skillpulse-lms' ),
						esc_html( current_time( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) ) )
					);
					echo esc_html( $timestamp_text );
					?>
				</p>

				<?php if ( current_user_can( 'manage_options' ) ) { ?>
					<div class="maintenance-admin-panel">
						<div class="admin-notice">
							<p class="admin-title">
								<span class="tools-icon">🔧</span>
								<strong><?php esc_html_e( 'Administrator Panel', 'skillpulse-lms' ); ?></strong>
							</p>
							<p class="admin-info">
								<?php esc_html_e( 'You can see this page because you\'re an administrator. Students see the maintenance message above.', 'skillpulse-lms' ); ?>
							</p>
							<div class="admin-actions">
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=splms-settings&tab=general' ) ); ?>" class="admin-button">
									<?php esc_html_e( 'Disable Maintenance Mode', 'skillpulse-lms' ); ?>
								</a>
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=splms-settings' ) ); ?>" class="admin-button secondary">
									<?php esc_html_e( 'Settings Panel', 'skillpulse-lms' ); ?>
								</a>
							</div>
						</div>
					</div>
				<?php } ?>
			</div>
		</div>
	</div>

	<?php
	/**
	 * Hook: splms_maintenance_footer
	 *
	 * Allows plugins/themes to add content to maintenance page footer.
	 */
	do_action( 'splms_maintenance_footer' );

	wp_footer();
	?>
</body>
</html> 