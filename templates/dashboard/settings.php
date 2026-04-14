<?php
/**
 * Settings Template
 *
 * This template can be overridden by copying it to:
 * your-theme/skillpulse-lms/dashboard/settings.php
 *
 * @package SkillPulse_LMS
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Extract variables from args.
if ( is_array( $args ) ) {
	// phpcs:ignore WordPress.PHP.DontExtract.extract_extract -- Template file uses extract for convenience.
	extract( $args );
}
$current_user_obj  = wp_get_current_user();
$profile_picture   = SkillPulse_LMS_Profile::get_instance()->get_profile_picture_url( $current_user_obj->ID, 150 );
$has_custom_avatar = SkillPulse_LMS_Profile::get_instance()->has_profile_picture( $current_user_obj->ID );

// Get notification preferences.
$notification_prefs = SkillPulse_LMS_Notification_Preferences::get_instance()->get_user_preferences( $current_user_obj->ID );
$prefs_instance     = SkillPulse_LMS_Notification_Preferences::get_instance();

// Check if notification types are enabled globally.
$email_enabled          = function_exists( 'splms_is_email_notifications_enabled' ) && splms_is_email_notifications_enabled();
$in_app_enabled         = function_exists( 'splms_is_in_app_notifications_enabled' ) && splms_is_in_app_notifications_enabled();
$show_notifications_tab = $email_enabled || $in_app_enabled;

// Get dashboard instance and current settings tab.
$dashboard    = SkillPulse_LMS_Dashboard::get_instance();
$settings_tab = $dashboard->get_current_settings_tab();
?>
<div class="splms-dashboard-tab splms-settings-tab">

	<!-- Page Header -->
	<div class="splms-section-title" style="margin-bottom: 2rem;">
		<i class="hgi-stroke hgi-settings-02"></i>
		<?php esc_html_e( 'Account Settings', 'skillpulse-lms' ); ?>
	</div>

	<div id="splms-dashboard-message"></div>

	<!-- Settings Tabs Navigation -->
	<div class="splms-settings-nav-card">
		<div class="splms-settings-tabs-nav">
			<a href="<?php echo esc_url( $dashboard->get_settings_tab_url( 'profile' ) ); ?>" class="splms-settings-tab-btn <?php echo 'profile' === $settings_tab ? 'is-active' : ''; ?>">
				<i class="hgi-stroke hgi-user-01"></i>
				<?php esc_html_e( 'Profile', 'skillpulse-lms' ); ?>
			</a>
			<a href="<?php echo esc_url( $dashboard->get_settings_tab_url( 'password' ) ); ?>" class="splms-settings-tab-btn <?php echo 'password' === $settings_tab ? 'is-active' : ''; ?>">
				<i class="hgi-stroke hgi-lock-password"></i>
				<?php esc_html_e( 'Password', 'skillpulse-lms' ); ?>
			</a>
			<?php if ( $show_notifications_tab ) { ?>
			<a href="<?php echo esc_url( $dashboard->get_settings_tab_url( 'notifications' ) ); ?>" class="splms-settings-tab-btn <?php echo 'notifications' === $settings_tab ? 'is-active' : ''; ?>">
				<i class="hgi-stroke hgi-notification-03"></i>
				<?php esc_html_e( 'Notifications', 'skillpulse-lms' ); ?>
			</a>
			<?php } ?>
		</div>
	</div>

	<!-- Settings Tab Content -->
	<div class="splms-settings-tabs-content">

		<!-- Profile Tab -->
		<div class="splms-settings-tab-panel <?php echo 'profile' === $settings_tab ? 'is-active' : ''; ?>" data-settings-tab="profile">

			<!-- Profile Information Section -->
			<div class="splms-settings-card">
				<div class="splms-card-header">
					<h3>
						<i class="hgi-stroke hgi-user-01"></i>
						<?php esc_html_e( 'Profile Information', 'skillpulse-lms' ); ?>
					</h3>
				</div>
				<div class="splms-card-body">
					<form id="splms-profile-form" class="splms-settings-form">

		<!-- Profile Picture and Basic Info Section -->
		<div class="splms-profile-header-section">
			<div class="splms-avatar-upload-container">
				<label for="avatar-upload" style="display: none;"><?php esc_html_e( 'Profile Picture Upload', 'skillpulse-lms' ); ?></label>
				<input type="file" id="avatar-upload" name="avatar" accept="image/jpeg,image/jpg,image/png,image/gif" style="display: none;">
				<div class="splms-avatar-preview">
					<img src="<?php echo esc_url( $profile_picture ); ?>" alt="<?php echo esc_attr( $current_user_obj->display_name ); ?>" class="splms-avatar-image">
					<div class="splms-avatar-overlay">
						<span class="camera-icon" title="<?php esc_attr_e( 'Change Avatar', 'skillpulse-lms' ); ?>">
							<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M23 19C23 19.5304 22.7893 20.0391 22.4142 20.4142C22.0391 20.7893 21.5304 21 21 21H3C2.46957 21 1.96086 20.7893 1.58579 20.4142C1.21071 20.0391 1 19.5304 1 19V8C1 7.46957 1.21071 6.96086 1.58579 6.58579C1.96086 6.21071 2.46957 6 3 6H7L9 4H15L17 6H21C21.5304 6 22.0391 6.21071 22.4142 6.58579C22.7893 6.96086 23 7.46957 23 8V19Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
								<circle cx="12" cy="13" r="4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
							</svg>
						</span>
					</div>
				</div>
				<?php if ( $has_custom_avatar ) { ?>
					<button type="button" class="splms-avatar-remove-btn splms-btn splms-btn-secondary splms-btn-small">
						<i class="hgi-stroke hgi-delete-02"></i>
						<?php esc_html_e( 'Remove', 'skillpulse-lms' ); ?>
					</button>
				<?php } ?>
			</div>

			<div class="splms-profile-basic-fields">
				<div class="splms-form-row">
					<div class="splms-form-group">
						<label for="display_name"><?php esc_html_e( 'Display Name', 'skillpulse-lms' ); ?></label>
						<input type="text" id="display_name" name="display_name" value="<?php echo esc_attr( $current_user_obj->display_name ); ?>" required>
					</div>
					<div class="splms-form-group">
						<label for="user_email"><?php esc_html_e( 'Email', 'skillpulse-lms' ); ?></label>
						<input type="email" id="user_email" name="email" value="<?php echo esc_attr( $current_user_obj->user_email ); ?>" required>
					</div>
				</div>

				<div class="splms-form-row">
					<div class="splms-form-group">
						<label for="first_name"><?php esc_html_e( 'First Name', 'skillpulse-lms' ); ?></label>
						<input type="text" id="first_name" name="first_name" value="<?php echo esc_attr( get_user_meta( $current_user_obj->ID, 'first_name', true ) ); ?>">
					</div>
					<div class="splms-form-group">
						<label for="last_name"><?php esc_html_e( 'Last Name', 'skillpulse-lms' ); ?></label>
						<input type="text" id="last_name" name="last_name" value="<?php echo esc_attr( get_user_meta( $current_user_obj->ID, 'last_name', true ) ); ?>">
					</div>
				</div>
			</div>
		</div>

		<div class="splms-form-group">
			<label for="user_bio"><?php esc_html_e( 'Bio', 'skillpulse-lms' ); ?></label>
			<textarea id="user_bio" name="bio" rows="4"><?php echo esc_textarea( get_user_meta( $current_user_obj->ID, 'description', true ) ); ?></textarea>
		</div>

						<div class="splms-form-section">
							<h4>
								<i class="hgi-stroke hgi-credit-card-01"></i>
								<?php esc_html_e( 'Billing Information', 'skillpulse-lms' ); ?>
							</h4>
							<p class="splms-form-help"><?php esc_html_e( 'Billing information is used for orders and invoices.', 'skillpulse-lms' ); ?></p>

		<?php
		$billing_address = get_user_meta( $current_user_obj->ID, 'billing_address', true );
		$billing_address = is_array( $billing_address ) ? $billing_address : array();
		?>
		<div class="splms-form-group">
			<label for="billing_address_1"><?php esc_html_e( 'Address Line 1', 'skillpulse-lms' ); ?></label>
			<input type="text" id="billing_address_1" name="billing_address_1" value="<?php echo esc_attr( $billing_address['address_1'] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'Street address', 'skillpulse-lms' ); ?>">
		</div>

		<div class="splms-form-group">
			<label for="billing_address_2"><?php esc_html_e( 'Address Line 2', 'skillpulse-lms' ); ?></label>
			<input type="text" id="billing_address_2" name="billing_address_2" value="<?php echo esc_attr( $billing_address['address_2'] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'Apartment, suite, etc. (optional)', 'skillpulse-lms' ); ?>">
		</div>

		<div class="splms-form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
			<div class="splms-form-group">
				<label for="billing_city"><?php esc_html_e( 'City', 'skillpulse-lms' ); ?></label>
				<input type="text" id="billing_city" name="billing_city" value="<?php echo esc_attr( $billing_address['city'] ?? '' ); ?>">
			</div>

			<div class="splms-form-group">
				<label for="billing_state"><?php esc_html_e( 'State/Province', 'skillpulse-lms' ); ?></label>
				<input type="text" id="billing_state" name="billing_state" value="<?php echo esc_attr( $billing_address['state'] ?? '' ); ?>">
			</div>
		</div>

		<div class="splms-form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
			<div class="splms-form-group">
				<label for="billing_postcode"><?php esc_html_e( 'Postal Code', 'skillpulse-lms' ); ?></label>
				<input type="text" id="billing_postcode" name="billing_postcode" value="<?php echo esc_attr( $billing_address['postcode'] ?? '' ); ?>">
			</div>

			<div class="splms-form-group">
				<label for="billing_country"><?php esc_html_e( 'Country', 'skillpulse-lms' ); ?></label>
				<input type="text" id="billing_country" name="billing_country" value="<?php echo esc_attr( $billing_address['country'] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'e.g., US, UK', 'skillpulse-lms' ); ?>">
			</div>
		</div>

		<div class="splms-form-group">
			<label for="billing_phone"><?php esc_html_e( 'Phone', 'skillpulse-lms' ); ?></label>
			<input type="tel" id="billing_phone" name="billing_phone" value="<?php echo esc_attr( get_user_meta( $current_user_obj->ID, 'billing_phone', true ) ); ?>" placeholder="<?php esc_attr_e( '+1 234 567 8900', 'skillpulse-lms' ); ?>">
		</div>

		<div class="splms-form-group">
			<label for="billing_company"><?php esc_html_e( 'Company', 'skillpulse-lms' ); ?></label>
			<input type="text" id="billing_company" name="billing_company" value="<?php echo esc_attr( get_user_meta( $current_user_obj->ID, 'billing_company', true ) ); ?>" placeholder="<?php esc_attr_e( 'Company name (optional)', 'skillpulse-lms' ); ?>">
		</div>
						</div>

						<div class="splms-form-actions">
							<button type="submit" class="splms-btn splms-btn-primary">
								<i class="hgi-stroke hgi-checkmark-01"></i>
								<?php esc_html_e( 'Save Changes', 'skillpulse-lms' ); ?>
							</button>
						</div>
					</form>
				</div>
			</div>
		</div>

		<!-- Password Tab -->
		<div class="splms-settings-tab-panel <?php echo 'password' === $settings_tab ? 'is-active' : ''; ?>" data-settings-tab="password">
			<div class="splms-settings-card">
				<div class="splms-card-header">
					<h3>
						<i class="hgi-stroke hgi-lock-password"></i>
						<?php esc_html_e( 'Change Password', 'skillpulse-lms' ); ?>
					</h3>
					<p class="splms-card-description"><?php esc_html_e( 'Update your account password to keep your account secure.', 'skillpulse-lms' ); ?></p>
				</div>
				<div class="splms-card-body">
					<form id="splms-password-form" class="splms-settings-form">
		
		<div class="splms-form-group">
			<label for="current_password"><?php esc_html_e( 'Current Password', 'skillpulse-lms' ); ?></label>
			<input type="password" id="current_password" name="current_password" required>
		</div>

		<div class="splms-form-group">
			<label for="new_password"><?php esc_html_e( 'New Password', 'skillpulse-lms' ); ?></label>
			<input type="password" id="new_password" name="new_password" required>
		</div>

		<div class="splms-form-group">
			<label for="confirm_password"><?php esc_html_e( 'Confirm New Password', 'skillpulse-lms' ); ?></label>
			<input type="password" id="confirm_password" name="confirm_password" required>
		</div>

						<div class="splms-form-actions">
							<button type="submit" class="splms-btn splms-btn-primary">
								<i class="hgi-stroke hgi-checkmark-01"></i>
								<?php esc_html_e( 'Change Password', 'skillpulse-lms' ); ?>
							</button>
						</div>
					</form>
				</div>
			</div>
		</div>

		<!-- Notifications Tab -->
		<?php if ( $show_notifications_tab ) { ?>
		<div class="splms-settings-tab-panel <?php echo 'notifications' === $settings_tab ? 'is-active' : ''; ?>" data-settings-tab="notifications">

			<div class="splms-settings-card">
				<div class="splms-card-header">
					<h3>
						<i class="hgi-stroke hgi-notification-03"></i>
						<?php esc_html_e( 'Notification Preferences', 'skillpulse-lms' ); ?>
					</h3>
					<p class="splms-card-description"><?php esc_html_e( 'Manage how you receive notifications for different events', 'skillpulse-lms' ); ?></p>
				</div>
				<div class="splms-card-body">
					<form id="splms-notification-preferences-form" class="splms-settings-form">


						<!-- Master Toggles Section -->
						<div class="splms-form-section">
							<h4>
								<i class="hgi-stroke hgi-settings-02"></i>
								<?php esc_html_e( 'Notification Controls', 'skillpulse-lms' ); ?>
							</h4>
							<p class="splms-form-help"><?php esc_html_e( 'Master controls for all notification types', 'skillpulse-lms' ); ?></p>

							<div class="splms-notification-master-toggles">
								<?php if ( $email_enabled ) { ?>
								<div class="splms-master-toggle">
									<label class="splms-checkbox-label" for="all_email_notifications">
										<input type="checkbox" id="all_email_notifications" name="email_enabled" value="1" <?php checked( $notification_prefs['email_enabled'], true ); ?>>
										<span class="splms-checkbox-mark"></span>
										<span class="splms-master-toggle-label">
											<i class="hgi-stroke hgi-mail-01"></i>
											<span class="splms-toggle-text">
												<strong><?php esc_html_e( 'All Email Notifications', 'skillpulse-lms' ); ?></strong>
												<small><?php esc_html_e( 'Enable/disable all email notifications at once', 'skillpulse-lms' ); ?></small>
											</span>
										</span>
									</label>
								</div>
								<?php } ?>

								<?php if ( function_exists( 'splms_is_in_app_notifications_enabled' ) && splms_is_in_app_notifications_enabled() ) { ?>
								<div class="splms-master-toggle">
									<label class="splms-checkbox-label" for="all_in_app_notifications">
										<input type="checkbox" id="all_in_app_notifications" name="in_app_enabled" value="1" <?php checked( $notification_prefs['in_app_enabled'], true ); ?>>
										<span class="splms-checkbox-mark"></span>
										<span class="splms-master-toggle-label">
											<i class="hgi-stroke hgi-notification-03"></i>
											<span class="splms-toggle-text">
												<strong><?php esc_html_e( 'All In-App Notifications', 'skillpulse-lms' ); ?></strong>
												<small><?php esc_html_e( 'Enable/disable all in-app notifications at once', 'skillpulse-lms' ); ?></small>
											</span>
										</span>
									</label>
								</div>
								<?php } ?>
							</div>
						</div>

						<!-- Event Notifications Section -->
						<div class="splms-form-section">
							<h4>
								<i class="hgi-stroke hgi-bell-01"></i>
								<?php esc_html_e( 'Event Notifications', 'skillpulse-lms' ); ?>
							</h4>
							<p class="splms-form-help"><?php esc_html_e( 'Choose which events you want to be notified about', 'skillpulse-lms' ); ?></p>

							<div class="splms-notification-events-grid">
								<?php
								$event_keys = array(
									'course_enrollment',
									'enrollment_reminder',
									'course_completion',
									'lesson_completion',
									'quiz_completion',
									'certificate_generated',
									'certificate_awarded',
									'signup_created',
									'signup_activated',
									'review_reply',
									'review_new',
									'review_moderation',
									'review_moderation_result',
									'order_completed',
									'order_refunded',
									'order_cancelled',
								);

								foreach ( $event_keys as $event_key ) :
									$event_prefs = isset( $notification_prefs[ $event_key ] ) ? $notification_prefs[ $event_key ] : array(
										'email'  => true,
										'in_app' => true,
									);
									?>
									<div class="splms-notification-event-card">
										<div class="splms-event-header">
											<h5><?php echo esc_html( $prefs_instance->get_event_display_name( $event_key ) ); ?></h5>
											<?php if ( $prefs_instance->get_event_description( $event_key ) ) : ?>
												<p class="splms-event-description"><?php echo esc_html( $prefs_instance->get_event_description( $event_key ) ); ?></p>
											<?php endif; ?>
										</div>
										<div class="splms-event-options">
											<?php if ( $email_enabled ) { ?>
											<div class="splms-checkbox-option">
												<label class="splms-checkbox-label" for="event_<?php echo esc_attr( $event_key ); ?>_email">
													<input type="checkbox" id="event_<?php echo esc_attr( $event_key ); ?>_email" name="events[<?php echo esc_attr( $event_key ); ?>][email]" value="1" <?php checked( $event_prefs['email'], true ); ?>>
													<span class="splms-checkbox-mark"></span>
													<span class="splms-option-label">
														<i class="hgi-stroke hgi-mail-01"></i>
														<?php esc_html_e( 'Email', 'skillpulse-lms' ); ?>
													</span>
												</label>
											</div>
											<?php } ?>
											<?php if ( $in_app_enabled ) { ?>
											<div class="splms-checkbox-option">
												<label class="splms-checkbox-label" for="event_<?php echo esc_attr( $event_key ); ?>_in_app">
													<input type="checkbox" id="event_<?php echo esc_attr( $event_key ); ?>_in_app" name="events[<?php echo esc_attr( $event_key ); ?>][in_app]" value="1" <?php checked( $event_prefs['in_app'], true ); ?>>
													<span class="splms-checkbox-mark"></span>
													<span class="splms-option-label">
														<i class="hgi-stroke hgi-notification-03"></i>
														<?php esc_html_e( 'In-App', 'skillpulse-lms' ); ?>
													</span>
												</label>
											</div>
											<?php } ?>
										</div>
									</div>
								<?php endforeach; ?>
							</div>
						</div>

						<div class="splms-form-actions">
							<button type="submit" class="splms-btn splms-btn-primary">
								<i class="hgi-stroke hgi-checkmark-01"></i>
								<?php esc_html_e( 'Save Notification Preferences', 'skillpulse-lms' ); ?>
							</button>
						</div>
					</form>
				</div>
			</div>
		</div>
		<?php } ?>
	</div>
</div>

