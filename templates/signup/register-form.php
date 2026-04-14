<?php
/**
 * Signup Form Template (Page-based, not shortcode)
 *
 * @package SkillPulse_LMS
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

global $splms_signup;

// Get settings (same as shortcode template).
$user_type           = splms_get_setting( 'default_user_role', 'student' );
$user_signup_enabled = splms_get_setting( 'user_signup_enabled', false );

// Check if there are any errors.
$has_errors = ! empty( $splms_signup->errors );
?>

<div class="splms-signup-container">
		<div class="splms-signup-wrapper">
			<div class="splms-auth-header">
				<h2><?php esc_html_e( 'Create Your Account', 'skillpulse-lms' ); ?></h2>
				<p><?php esc_html_e( 'Join our learning community and start your journey today!', 'skillpulse-lms' ); ?></p>
			</div>

			<form id="splms-signup-form" class="splms-auth-form splms-signup-form" novalidate>
				<?php wp_nonce_field( 'splms_auth_nonce', 'nonce' ); ?>
				<input type="hidden" name="user_type" value="<?php echo esc_attr( $user_type ); ?>">
				<!-- Name Fields -->
				<div class="splms-form-row">
					<div class="splms-form-group">
						<label for="first_name" class="splms-form-label">
							<?php esc_html_e( 'First Name', 'skillpulse-lms' ); ?>
							<span class="splms-required">*</span>
						</label>
						<?php
						// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Template file, nonce verification handled at higher level.
						$first_name = isset( $_POST['first_name'] ) ? sanitize_text_field( wp_unslash( $_POST['first_name'] ) ) : '';
						?>
						<input type="text" id="first_name" name="first_name" class="splms-form-control" required
								value="<?php echo esc_attr( $first_name ); ?>">
						<div class="splms-form-error" id="first_name_error"></div>
					</div>
					<div class="splms-form-group">
						<label for="last_name" class="splms-form-label">
							<?php esc_html_e( 'Last Name', 'skillpulse-lms' ); ?>
							<span class="splms-required">*</span>
						</label>
						<?php
						// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Template file, nonce verification handled at higher level.
						$last_name = isset( $_POST['last_name'] ) ? sanitize_text_field( wp_unslash( $_POST['last_name'] ) ) : '';
						?>
						<input type="text" id="last_name" name="last_name" class="splms-form-control" required
								value="<?php echo esc_attr( $last_name ); ?>">
						<div class="splms-form-error" id="last_name_error"></div>
					</div>
				</div>

				<!-- Username Field -->
				<div class="splms-form-group">
					<label for="username" class="splms-form-label">
						<?php esc_html_e( 'Username', 'skillpulse-lms' ); ?>
						<span class="splms-required">*</span>
					</label>
					<?php
					// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Template file, nonce verification handled at higher level.
					$username = isset( $_POST['username'] ) ? sanitize_user( wp_unslash( $_POST['username'] ) ) : '';
					?>
					<input type="text" id="username" name="username" class="splms-form-control" required
							value="<?php echo esc_attr( $username ); ?>">
					<small class="splms-form-help">
						<?php esc_html_e( 'Only letters, numbers, and underscores allowed', 'skillpulse-lms' ); ?>
					</small>
					<div class="splms-form-error" id="username_error"></div>
				</div>

				<!-- Email Field -->
				<div class="splms-form-group">
					<label for="email" class="splms-form-label">
						<?php esc_html_e( 'Email Address', 'skillpulse-lms' ); ?>
						<span class="splms-required">*</span>
					</label>
					<?php
					// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Template file, nonce verification handled at higher level.
					$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
					?>
					<input type="email" id="email" name="email" class="splms-form-control" required
							value="<?php echo esc_attr( $email ); ?>">
					<small class="splms-form-help">
						<?php esc_html_e( 'We\'ll send an activation link to this email', 'skillpulse-lms' ); ?>
					</small>
					<div class="splms-form-error" id="email_error"></div>
				</div>

				<!-- Password Fields -->
				<div class="splms-form-row">
					<div class="splms-form-group">
						<label for="password" class="splms-form-label">
							<?php esc_html_e( 'Password', 'skillpulse-lms' ); ?>
							<span class="splms-required">*</span>
						</label>
						<div class="splms-password-field">
							<input type="password" id="password" name="password" class="splms-form-control" required>
							<button type="button" class="splms-password-toggle" data-target="password" aria-label="<?php esc_attr_e( 'Toggle password visibility', 'skillpulse-lms' ); ?>">
								<svg class="splms-toggle-show" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
									<circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
								</svg>
								<svg class="splms-toggle-hide" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="display: none;">
									<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
									<line x1="1" y1="1" x2="23" y2="23" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
								</svg>
							</button>
						</div>
						<div class="splms-password-strength" id="password_strength"></div>
						<div class="splms-form-error" id="password_error"></div>
					</div>
					<div class="splms-form-group">
						<label for="confirm_password" class="splms-form-label">
							<?php esc_html_e( 'Confirm Password', 'skillpulse-lms' ); ?>
							<span class="splms-required">*</span>
						</label>
						<div class="splms-password-field">
							<input type="password" id="confirm_password" name="confirm_password" class="splms-form-control" required>
							<button type="button" class="splms-password-toggle" data-target="confirm_password" aria-label="<?php esc_attr_e( 'Toggle password visibility', 'skillpulse-lms' ); ?>">
								<svg class="splms-toggle-show" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
									<circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
								</svg>
								<svg class="splms-toggle-hide" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="display: none;">
									<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
									<line x1="1" y1="1" x2="23" y2="23" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
								</svg>
							</button>
						</div>
						<div class="splms-form-error" id="confirm_password_error"></div>
					</div>
				</div>

				<!-- Terms and Conditions -->
				<div class="splms-form-group splms-terms-group">
					<label class="splms-checkbox-label">
						<input type="checkbox" name="terms_accepted" id="terms_accepted" required>
						<span class="splms-checkbox-mark"></span>
						<span class="splms-checkbox-text">
							<?php
							// Get terms and privacy URLs.
							$terms_page_id = splms_get_setting( 'terms_conditions_page_id', 0 );
							$terms_url     = '';
							if ( $terms_page_id ) {
								$terms_page = get_post( $terms_page_id );
								if ( $terms_page && 'publish' === $terms_page->post_status ) {
									$terms_url = get_permalink( $terms_page_id );
								}
							}

							$privacy_page_id = splms_get_setting( 'privacy_policy_page_id', 0 );
							$privacy_url     = '';
							if ( $privacy_page_id ) {
								$privacy_page = get_post( $privacy_page_id );
								if ( $privacy_page && 'publish' === $privacy_page->post_status ) {
									$privacy_url = get_permalink( $privacy_page_id );
								}
							}
							if ( empty( $privacy_url ) ) {
								$privacy_url = get_privacy_policy_url();
							}

							esc_html_e( 'I agree to the', 'skillpulse-lms' );
							?>
							<?php if ( $terms_url ) : ?>
								<a href="<?php echo esc_url( $terms_url ); ?>" class="splms-terms-link" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Terms and Conditions', 'skillpulse-lms' ); ?></a>
							<?php else : ?>
								<span class="splms-terms-link"><?php esc_html_e( 'Terms and Conditions', 'skillpulse-lms' ); ?></span>
							<?php endif; ?>
							<?php esc_html_e( 'and', 'skillpulse-lms' ); ?>
							<?php if ( $privacy_url ) : ?>
								<a href="<?php echo esc_url( $privacy_url ); ?>" class="splms-privacy-link" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Privacy Policy', 'skillpulse-lms' ); ?></a>
							<?php else : ?>
								<span class="splms-privacy-link"><?php esc_html_e( 'Privacy Policy', 'skillpulse-lms' ); ?></span>
							<?php endif; ?>
						</span>
					</label>
					<div class="splms-form-error" id="terms_accepted_error"></div>
				</div>

				<!-- Submit Button -->
				<div class="splms-form-group">
					<button type="submit" class="splms-auth-btn splms-auth-btn-primary">
						<span class="splms-btn-text"><?php esc_html_e( 'Create Account', 'skillpulse-lms' ); ?></span>
						<span class="splms-btn-loader" style="display: none;">
							<svg class="splms-spinner" viewBox="0 0 50 50">
								<circle class="splms-spinner-path" cx="25" cy="25" r="20" fill="none" stroke="currentColor" stroke-width="2" stroke-miterlimit="10"/>
							</svg>
							<?php esc_html_e( 'Creating...', 'skillpulse-lms' ); ?>
						</span>
					</button>
				</div>
				<!-- Form Messages -->
				<div class="splms-form-messages">
					<div class="splms-form-message splms-form-success" id="success_message" style="display: none;">
						<div class="splms-message-content">
							<span class="splms-message-icon">✅</span>
							<span class="splms-message-text"></span>
						</div>
					</div>
					<div class="splms-form-message splms-form-error" id="error_message" style="display: none;">
						<div class="splms-message-content">
							<span class="splms-message-icon">❌</span>
							<span class="splms-message-text"></span>
						</div>
					</div>
				</div>
			</form>

			<!-- Signin Link -->
			<div class="splms-auth-footer">
				<p>
					<?php esc_html_e( 'Already have an account?', 'skillpulse-lms' ); ?>
					<a href="<?php echo esc_url( wp_login_url() ); ?>" class="splms-auth-link">
						<?php esc_html_e( 'Login here', 'skillpulse-lms' ); ?>
					</a>
				</p>
			</div>

			<div class="splms-auth-footer">
				<p>
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="splms-auth-link">← <?php esc_html_e( 'Go to ', 'skillpulse-lms' ); ?> <?php echo esc_html( get_bloginfo( 'name' ) ); ?></a>		
				</p>
			</div>
		</div>
	</div>
