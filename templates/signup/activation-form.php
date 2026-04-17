<?php
/**
 * Activation Form Template
 *
 * @package SkillPulse_LMS
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}



$splms_activation_key = get_query_var( 'activation_key' );
$splms_signup         = null;

if ( ! empty( $splms_activation_key ) ) {
	$splms_signup = SkillPulse_LMS_Signup::get_instance()->get_signup_by_key( $splms_activation_key );
}

// Check if we have an activation key in the URL.
$splms_has_activation_key = ! empty( $splms_activation_key );
?>

<div class="splms-signup-container">
	<div class="splms-signup-wrapper">
		<div class="splms-auth-header">
			<h2><?php esc_html_e( 'Account Activation', 'skillpulse-lms' ); ?></h2>
			<p><?php esc_html_e( 'Activate your account to start learning', 'skillpulse-lms' ); ?></p>
		</div>

		<?php
		if ( $splms_has_activation_key ) {
			if ( $splms_signup ) {
				if ( 'pending' === $splms_signup->status ) {
					?>
					<div class="splms-account-details-header">
						<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M20 21V19C20 17.9391 19.5786 16.9217 18.8284 16.1716C18.0783 15.4214 17.0609 15 16 15H8C6.93913 15 5.92172 15.4214 5.17157 16.1716C4.42143 16.9217 4 17.9391 4 19V21"
									stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
							<circle cx="12" cy="7" r="4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
						<h3><?php esc_html_e( 'Account Details', 'skillpulse-lms' ); ?></h3>
					</div>

					<div class="splms-form-group">
						<label class="splms-form-label"><?php esc_html_e( 'Name:', 'skillpulse-lms' ); ?></label>
						<div class="splms-form-control splms-readonly"><?php echo esc_html( $splms_signup->user_name ); ?></div>
					</div>

					<div class="splms-form-group">
						<label class="splms-form-label"><?php esc_html_e( 'Email:', 'skillpulse-lms' ); ?></label>
						<div class="splms-form-control splms-readonly"><?php echo esc_html( $splms_signup->user_email ); ?></div>
					</div>

					<div class="splms-form-group">
						<label class="splms-form-label"><?php esc_html_e( 'User Type:', 'skillpulse-lms' ); ?></label>
						<div class="splms-form-control splms-readonly"><?php echo esc_html( ucfirst( $splms_signup->user_type ) ); ?></div>
					</div>

					<div class="splms-form-group">
						<label class="splms-form-label"><?php esc_html_e( 'Signed up:', 'skillpulse-lms' ); ?></label>
						<div class="splms-form-control splms-readonly">
							<?php
							echo esc_html(
								date_i18n(
									get_option( 'date_format' ),
									strtotime( $splms_signup->registered )
								)
							);
							?>
						</div>
					</div>

					<form id="splms-activation-form" class="splms-auth-form" method="post">
						<?php wp_nonce_field( 'splms_signup_nonce', 'splms_nonce' ); ?>
						<input type="hidden" name="splms_activate_submit" value="1"/>
						<input type="hidden" name="activation_key" value="<?php echo esc_attr( $splms_activation_key ); ?>"/>

						<div class="splms-form-group">
							<button type="submit" class="splms-auth-btn splms-auth-btn-primary">
								<span class="splms-btn-text"><?php esc_html_e( 'Activate Account', 'skillpulse-lms' ); ?></span>
								<span class="splms-btn-loader" style="display: none;">
										<svg class="splms-spinner" viewBox="0 0 50 50">
											<circle class="splms-spinner-path" cx="25" cy="25" r="20" fill="none" stroke="currentColor" stroke-width="2"
													stroke-miterlimit="10"/>
										</svg>
										<?php esc_html_e( 'Activating...', 'skillpulse-lms' ); ?>
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
					<?php
				} else {
					?>
					<div class="splms-message-box splms-error">
						<h3><?php esc_html_e( 'Invalid Activation', 'skillpulse-lms' ); ?></h3>
						<p><?php esc_html_e( 'This activation link is no longer valid.', 'skillpulse-lms' ); ?></p>
					</div>
					<?php
				}
			} else {
				?>
				<div class="splms-message-box splms-error">
					<h3><?php esc_html_e( 'Invalid Activation Link', 'skillpulse-lms' ); ?></h3>
					<p><?php esc_html_e( 'The activation link you provided is invalid or has expired.', 'skillpulse-lms' ); ?></p>
					<p><?php esc_html_e( 'Please check your email for the correct activation link, or contact support if you need assistance.', 'skillpulse-lms' ); ?></p>
				</div>
				<?php
			}
		} else {
			?>
			<!-- No activation key provided - show helpful information -->
			<div class="splms-message-box splms-info">
				<h3><?php esc_html_e( 'Activation Link Required', 'skillpulse-lms' ); ?></h3>
				<p><?php esc_html_e( 'To activate your account, you need to click the activation link that was sent to your email address.', 'skillpulse-lms' ); ?></p>
				<p><?php esc_html_e( 'The activation link should look like this:', 'skillpulse-lms' ); ?></p>
				<div class="splms-example-code">
					<code><?php echo esc_url( home_url( '/activate/your-activation-key-here/' ) ); ?></code>
				</div>
				<p><?php esc_html_e( 'If you haven\'t received the activation email, you can request a new one below.', 'skillpulse-lms' ); ?></p>
			</div>
			<?php
		}
		if ( ! $splms_has_activation_key || ! $splms_signup || 'pending' !== $splms_signup->status ) {
			?>
			<div class="splms-resend-activation">
				<h3><?php esc_html_e( 'Need Help?', 'skillpulse-lms' ); ?></h3>
				<p><?php esc_html_e( 'If you haven\'t received your activation email, you can request a new one:', 'skillpulse-lms' ); ?></p>

				<form id="splms-resend-form" class="splms-auth-form">
					<?php wp_nonce_field( 'splms_resend_activation', 'nonce' ); ?>

					<div class="splms-form-group">
						<label for="resend_email" class="splms-form-label">
							<?php esc_html_e( 'Email Address', 'skillpulse-lms' ); ?>
							<span class="splms-required">*</span>
						</label>
						<input type="email" id="resend_email" name="email" class="splms-form-control" required>
						<div class="splms-form-error" id="resend_email_error"></div>
					</div>

					<div class="splms-form-group">
						<button type="submit" class="splms-auth-btn splms-auth-btn-secondary">
							<span class="splms-btn-text"><?php esc_html_e( 'Resend Activation Email', 'skillpulse-lms' ); ?></span>
							<span class="splms-btn-loader" style="display: none;">
								<svg class="splms-spinner" viewBox="0 0 50 50">
									<circle class="splms-spinner-path" cx="25" cy="25" r="20" fill="none" stroke="currentColor" stroke-width="2" stroke-miterlimit="10"/>
								</svg>
								<?php esc_html_e( 'Sending...', 'skillpulse-lms' ); ?>
							</span>
						</button>
					</div>

					<!-- Resend Form Messages -->
					<div class="splms-form-messages">
						<div class="splms-form-message splms-form-success" id="resend_success_message" style="display: none;">
							<div class="splms-message-content">
								<span class="splms-message-icon">✅</span>
								<span class="splms-message-text"></span>
							</div>
						</div>
						<div class="splms-form-message splms-form-error" id="resend_error_message" style="display: none;">
							<div class="splms-message-content">
								<span class="splms-message-icon">❌</span>
								<span class="splms-message-text"></span>
							</div>
						</div>
					</div>
				</form>
			</div>
		<?php } ?>

		<!-- Signin Link -->
		<div class="splms-auth-footer">
			<p>
				<?php esc_html_e( 'Already have an account?', 'skillpulse-lms' ); ?>
				<a href="<?php echo esc_url( wp_login_url() ); ?>" class="splms-auth-link">
					<?php esc_html_e( 'Login here', 'skillpulse-lms' ); ?>
				</a>
			</p>
		</div>
	</div>
</div>
