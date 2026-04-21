<?php
/**
 * Signup Success Template
 *
 * @package SkillPulse_LMS
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

wp_enqueue_style( 'splms-frontend-style' );
wp_enqueue_script( 'splms-frontend-script' );
?>

<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php echo esc_html( sprintf( '%s - %s', __( 'Registration Successful', 'skillpulse-lms' ), get_bloginfo( 'name' ) ) ); ?></title>
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'splms-signup-page splms-signup-success-page' ); ?>>

<div class="splms-signup-page">
	<div class="splms-signup-container">
		<div class="splms-signup-wrapper splms-success-wrapper">

			<!-- Success Icon -->
			<div class="splms-success-icon">
				<svg width="80" height="80" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
					<circle cx="12" cy="12" r="10" fill="#10b981"/>
					<path d="M16 8L10 14l-4-4" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
			</div>

			<!-- Success Header -->
			<div class="splms-success-header">
				<?php
				// translators: %s: Site name.
				$splms_welcome_text = sprintf( __( 'Welcome to %s', 'skillpulse-lms' ), get_bloginfo( 'name' ) );
				?>
				<h1><?php echo esc_html( $splms_welcome_text ); ?></h1>
				<p><?php esc_html_e( 'Your account has been created successfully. We\'re excited to have you join our learning community!', 'skillpulse-lms' ); ?></p>
			</div>

			<!-- Check Your Email Section -->
			<div class="splms-info-section">
				<div class="splms-section-icon">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M3 8L12 13L21 8M3 8L12 3L21 8M3 8V16L12 21L21 16V8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
				</div>
				<div class="splms-section-content">
					<h2><?php esc_html_e( 'Check Your Email', 'skillpulse-lms' ); ?></h2>
					<p><?php esc_html_e( 'We\'ve sent an activation link to your email address. Please check your inbox (and spam folder) and click the activation link to complete your signup.', 'skillpulse-lms' ); ?></p>
				</div>
			</div>

			<!-- What's Next Section -->
			<div class="splms-info-section">
				<div class="splms-section-icon">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M12 2L2 7L12 12L22 7L12 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
						<path d="M2 17L12 22L22 17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
						<path d="M2 12L12 17L22 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
				</div>
				<div class="splms-section-content">
					<h2><?php esc_html_e( 'What\'s Next?', 'skillpulse-lms' ); ?></h2>
					<p><?php esc_html_e( 'Once you activate your account, you\'ll be able to log in and start exploring our courses, connect with other learners, and begin your learning journey.', 'skillpulse-lms' ); ?></p>
				</div>
			</div>

			<!-- Resend Activation Section -->
			<div class="splms-resend-section">
				<div class="splms-section-icon">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M12 2V6M12 18V22M4.93 4.93L7.76 7.76M16.24 16.24L19.07 19.07M2 12H6M18 12H22M4.93 19.07L7.76 16.24M16.24 7.76L19.07 4.93" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
				</div>
				<div class="splms-section-content">
					<h2><?php esc_html_e( 'Didn\'t receive the email?', 'skillpulse-lms' ); ?></h2>
					<p><?php esc_html_e( 'No worries! Enter your email below and we\'ll send you a new activation link.', 'skillpulse-lms' ); ?></p>

					<form id="splms-resend-form" class="splms-resend-form" method="post">
						<?php wp_nonce_field( 'splms_resend_activation', 'nonce' ); ?>
						<div class="splms-email-input">
							<input type="email" name="email" placeholder="<?php esc_attr_e( 'Enter your email address', 'skillpulse-lms' ); ?>" required>
						</div>
						<button type="submit" class="splms-resend-btn">
							<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M22 2L11 13M22 2L15 22L11 13M22 2L2 9L11 13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
							</svg>
							<?php esc_html_e( 'Resend', 'skillpulse-lms' ); ?>
						</button>
					</form>
				</div>
			</div>

		</div>
	</div>
</div>

<?php wp_footer(); ?>
</body>
</html>
