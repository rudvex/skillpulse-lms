<?php
/**
 * Login Required Template
 *
 * Reusable template for pages that require user authentication
 * Modern, styled template that matches the SkillPulse LMS dashboard design
 *
 * This template can be overridden by copying it to:
 * your-theme/skillpulse-lms/login-required.php
 *
 * @package SkillPulse_LMS
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

// Get signup page URL if configured.
$signup_page_id = splms_get_setting( 'signup_page_id', 0 );
$signup_url     = $signup_page_id ? get_permalink( $signup_page_id ) : wp_registration_url();
?>

<div class="splms-login-required-wrapper">
	<div class="splms-login-required-card">
		<div class="splms-login-icon">
			<svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M6 10V8C6 5.79086 7.79086 4 10 4H14C16.2091 4 18 5.79086 18 8V10M5 10H19C20.1046 10 21 10.8954 21 12V18C21 19.1046 20.1046 20 19 20H5C3.89543 20 3 19.1046 3 18V12C3 10.8954 3.89543 10 5 10Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				<circle cx="12" cy="15" r="1.5" fill="currentColor"/>
			</svg>
		</div>

		<h2><?php esc_html_e( 'Access Your Learning Dashboard', 'skillpulse-lms' ); ?></h2>
		<p><?php esc_html_e( 'Please log in to access your personalized learning dashboard with courses, progress tracking, and certificates.', 'skillpulse-lms' ); ?></p>

		<div class="splms-login-actions">
			<a href="<?php echo esc_url( wp_login_url( get_permalink() ) ); ?>" class="splms-btn splms-btn-primary">
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M15 3H19C20.1046 3 21 3.89543 21 5V19C21 20.1046 20.1046 21 19 21H15M10 17L15 12L10 7M15 12H3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				<?php esc_html_e( 'Login', 'skillpulse-lms' ); ?>
			</a>

			<?php if ( get_option( 'users_can_register' ) || $signup_page_id ) : ?>
				<a href="<?php echo esc_url( $signup_url ); ?>" class="splms-btn splms-btn-secondary">
					<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M16 21V19C16 17.8954 15.1046 17 14 17H6C4.89543 17 4 17.8954 4 19V21M20 8V14M23 11H17M12 11C14.2091 11 16 9.20914 16 7C16 4.79086 14.2091 3 12 3C9.79086 3 8 4.79086 8 7C8 9.20914 9.79086 11 12 11Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
					<?php esc_html_e( 'Sign Up', 'skillpulse-lms' ); ?>
				</a>
			<?php endif; ?>
		</div>

		<div class="splms-features-preview">
			<h3 style="font-size: 1.1rem; color: #374151; margin-bottom: 1rem; font-weight: 600;">
				<?php esc_html_e( 'What you\'ll get access to:', 'skillpulse-lms' ); ?>
			</h3>

			<div class="splms-features-list">
				<div class="splms-feature-item">
					<div class="splms-feature-icon">
						<svg width="12" height="12" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M9 12L11 14L15 10M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
					</div>
					<span class="splms-feature-text"><?php esc_html_e( 'Personal learning dashboard', 'skillpulse-lms' ); ?></span>
				</div>

				<div class="splms-feature-item">
					<div class="splms-feature-icon">
						<svg width="12" height="12" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M12 6.253V10L15 11L12 6.253ZM12 6.253C11.83 5.706 11.415 5.268 10.94 4.991C10.465 4.714 9.95 4.616 9.455 4.714C8.96 4.812 8.514 5.1 8.197 5.524C7.88 5.948 7.714 6.479 7.714 7.024V10.5M19 10.5C19.5304 10.5 20.0391 10.7107 20.4142 11.0858C20.7893 11.4609 21 11.9696 21 12.5V18.5C21 19.0304 20.7893 19.5391 20.4142 19.9142C20.0391 20.2893 19.5304 20.5 19 20.5H5C4.46957 20.5 3.96086 20.2893 3.58579 19.9142C3.21071 19.5391 3 19.0304 3 18.5V12.5C3 11.9696 3.21071 11.4609 3.58579 11.0858C3.96086 10.7107 4.46957 10.5 5 10.5H19Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
					</div>
					<span class="splms-feature-text"><?php esc_html_e( 'Track your course progress', 'skillpulse-lms' ); ?></span>
				</div>

				<div class="splms-feature-item">
					<div class="splms-feature-icon">
						<svg width="12" height="12" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M4.26 11.02V15.99C4.26 17.81 5.11 19.08 6.86 19.59C8.16 19.93 9.34 20.01 10.5 20.01C11.66 20.01 12.84 19.93 14.14 19.59C15.89 19.08 16.74 17.81 16.74 15.99V11.02C16.74 9.21004 15.89 7.94004 14.14 7.43004C12.84 7.09004 11.66 7.01004 10.5 7.01004C9.34 7.01004 8.16 7.09004 6.86 7.43004C5.11 7.94004 4.26 9.21004 4.26 11.02Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
							<path d="M4.26 11.02C4.26 9.21004 5.11 7.94004 6.86 7.43004C8.16 7.09004 9.34 7.01004 10.5 7.01004C11.66 7.01004 12.84 7.09004 14.14 7.43004C15.89 7.94004 16.74 9.21004 16.74 11.02" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
					</div>
					<span class="splms-feature-text"><?php esc_html_e( 'Earn certificates and achievements', 'skillpulse-lms' ); ?></span>
				</div>

				<div class="splms-feature-item">
					<div class="splms-feature-icon">
						<svg width="12" height="12" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M13 16H21L19 18L21 20H13V16ZM3 16C3 14.8954 3.89543 14 5 14H9C10.1046 14 11 14.8954 11 16V20C11 21.1046 10.1046 22 9 22H5C3.89543 22 3 21.1046 3 20V16ZM3 4C3 2.89543 3.89543 2 5 2H9C10.1046 2 11 2.89543 11 4V8C11 9.10457 10.1046 10 9 10H5C3.89543 10 3 9.10457 3 8V4ZM13 4C13 2.89543 13.8954 2 15 2H19C20.1046 2 21 2.89543 21 4V8C21 9.10457 20.1046 10 19 10H15C13.8954 10 13 9.10457 13 8V4Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
					</div>
					<span class="splms-feature-text"><?php esc_html_e( 'Personalized recommendations', 'skillpulse-lms' ); ?></span>
				</div>
			</div>
		</div>
	</div>
</div>

<?php
get_footer();