<?php
/**
 * Settings Template
 *
 * This template can be overridden by copying it to:
 * your-theme/skillpulse-lms/dashboard/settings.php
 *
 * @package SPLMS
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
$splms_current_user_obj  = wp_get_current_user();
$splms_profile_picture   = SPLMS_Profile::get_instance()->get_profile_picture_url( $splms_current_user_obj->ID, 150 );
$splms_has_custom_avatar = SPLMS_Profile::get_instance()->has_profile_picture( $splms_current_user_obj->ID );

// Get dashboard instance and current settings tab.
$splms_dashboard    = SPLMS_Dashboard::get_instance();
$splms_settings_tab = $splms_dashboard->get_current_settings_tab();
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
			<a href="<?php echo esc_url( $splms_dashboard->get_settings_tab_url( 'profile' ) ); ?>" class="splms-settings-tab-btn <?php echo 'profile' === $splms_settings_tab ? 'is-active' : ''; ?>">
				<i class="hgi-stroke hgi-user-01"></i>
				<?php esc_html_e( 'Profile', 'skillpulse-lms' ); ?>
			</a>
			<a href="<?php echo esc_url( $splms_dashboard->get_settings_tab_url( 'password' ) ); ?>" class="splms-settings-tab-btn <?php echo 'password' === $splms_settings_tab ? 'is-active' : ''; ?>">
				<i class="hgi-stroke hgi-lock-password"></i>
				<?php esc_html_e( 'Password', 'skillpulse-lms' ); ?>
			</a>
			<?php
			// Allow modules to add settings tab navigation items.
			do_action( 'splms_settings_tabs_nav', $splms_dashboard, $splms_settings_tab );
			?>
		</div>
	</div>

	<!-- Settings Tab Content -->
	<div class="splms-settings-tabs-content">

		<!-- Profile Tab -->
		<div class="splms-settings-tab-panel <?php echo 'profile' === $splms_settings_tab ? 'is-active' : ''; ?>" data-settings-tab="profile">

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
					<img src="<?php echo esc_url( $splms_profile_picture ); ?>" alt="<?php echo esc_attr( $splms_current_user_obj->display_name ); ?>" class="splms-avatar-image">
					<div class="splms-avatar-overlay">
						<span class="camera-icon" title="<?php esc_attr_e( 'Change Avatar', 'skillpulse-lms' ); ?>">
							<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M23 19C23 19.5304 22.7893 20.0391 22.4142 20.4142C22.0391 20.7893 21.5304 21 21 21H3C2.46957 21 1.96086 20.7893 1.58579 20.4142C1.21071 20.0391 1 19.5304 1 19V8C1 7.46957 1.21071 6.96086 1.58579 6.58579C1.96086 6.21071 2.46957 6 3 6H7L9 4H15L17 6H21C21.5304 6 22.0391 6.21071 22.4142 6.58579C22.7893 6.96086 23 7.46957 23 8V19Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
								<circle cx="12" cy="13" r="4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
							</svg>
						</span>
					</div>
				</div>
				<?php if ( $splms_has_custom_avatar ) { ?>
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
						<input type="text" id="display_name" name="display_name" value="<?php echo esc_attr( $splms_current_user_obj->display_name ); ?>" required>
					</div>
					<div class="splms-form-group">
						<label for="user_email"><?php esc_html_e( 'Email', 'skillpulse-lms' ); ?></label>
						<input type="email" id="user_email" name="email" value="<?php echo esc_attr( $splms_current_user_obj->user_email ); ?>" required>
					</div>
				</div>

				<div class="splms-form-row">
					<div class="splms-form-group">
						<label for="first_name"><?php esc_html_e( 'First Name', 'skillpulse-lms' ); ?></label>
						<input type="text" id="first_name" name="first_name" value="<?php echo esc_attr( get_user_meta( $splms_current_user_obj->ID, 'first_name', true ) ); ?>">
					</div>
					<div class="splms-form-group">
						<label for="last_name"><?php esc_html_e( 'Last Name', 'skillpulse-lms' ); ?></label>
						<input type="text" id="last_name" name="last_name" value="<?php echo esc_attr( get_user_meta( $splms_current_user_obj->ID, 'last_name', true ) ); ?>">
					</div>
				</div>
			</div>
		</div>

		<div class="splms-form-group">
			<label for="user_bio"><?php esc_html_e( 'Bio', 'skillpulse-lms' ); ?></label>
			<textarea id="user_bio" name="bio" rows="4"><?php echo esc_textarea( get_user_meta( $splms_current_user_obj->ID, 'description', true ) ); ?></textarea>
		</div>

						<div class="splms-form-section">
							<h4>
								<i class="hgi-stroke hgi-credit-card-01"></i>
								<?php esc_html_e( 'Billing Information', 'skillpulse-lms' ); ?>
							</h4>
							<p class="splms-form-help"><?php esc_html_e( 'Billing information is used for orders and invoices.', 'skillpulse-lms' ); ?></p>

		<?php
		$splms_billing_address = get_user_meta( $splms_current_user_obj->ID, 'billing_address', true );
		$splms_billing_address = is_array( $splms_billing_address ) ? $splms_billing_address : array();
		?>
		<div class="splms-form-group">
			<label for="billing_address_1"><?php esc_html_e( 'Address Line 1', 'skillpulse-lms' ); ?></label>
			<input type="text" id="billing_address_1" name="billing_address_1" value="<?php echo esc_attr( $splms_billing_address['address_1'] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'Street address', 'skillpulse-lms' ); ?>">
		</div>

		<div class="splms-form-group">
			<label for="billing_address_2"><?php esc_html_e( 'Address Line 2', 'skillpulse-lms' ); ?></label>
			<input type="text" id="billing_address_2" name="billing_address_2" value="<?php echo esc_attr( $splms_billing_address['address_2'] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'Apartment, suite, etc. (optional)', 'skillpulse-lms' ); ?>">
		</div>

		<div class="splms-form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
			<div class="splms-form-group">
				<label for="billing_city"><?php esc_html_e( 'City', 'skillpulse-lms' ); ?></label>
				<input type="text" id="billing_city" name="billing_city" value="<?php echo esc_attr( $splms_billing_address['city'] ?? '' ); ?>">
			</div>

			<div class="splms-form-group">
				<label for="billing_state"><?php esc_html_e( 'State/Province', 'skillpulse-lms' ); ?></label>
				<input type="text" id="billing_state" name="billing_state" value="<?php echo esc_attr( $splms_billing_address['state'] ?? '' ); ?>">
			</div>
		</div>

		<div class="splms-form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
			<div class="splms-form-group">
				<label for="billing_postcode"><?php esc_html_e( 'Postal Code', 'skillpulse-lms' ); ?></label>
				<input type="text" id="billing_postcode" name="billing_postcode" value="<?php echo esc_attr( $splms_billing_address['postcode'] ?? '' ); ?>">
			</div>

			<div class="splms-form-group">
				<label for="billing_country"><?php esc_html_e( 'Country', 'skillpulse-lms' ); ?></label>
				<input type="text" id="billing_country" name="billing_country" value="<?php echo esc_attr( $splms_billing_address['country'] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'e.g., US, UK', 'skillpulse-lms' ); ?>">
			</div>
		</div>

		<div class="splms-form-group">
			<label for="billing_phone"><?php esc_html_e( 'Phone', 'skillpulse-lms' ); ?></label>
			<input type="tel" id="billing_phone" name="billing_phone" value="<?php echo esc_attr( get_user_meta( $splms_current_user_obj->ID, 'billing_phone', true ) ); ?>" placeholder="<?php esc_attr_e( '+1 234 567 8900', 'skillpulse-lms' ); ?>">
		</div>

		<div class="splms-form-group">
			<label for="billing_company"><?php esc_html_e( 'Company', 'skillpulse-lms' ); ?></label>
			<input type="text" id="billing_company" name="billing_company" value="<?php echo esc_attr( get_user_meta( $splms_current_user_obj->ID, 'billing_company', true ) ); ?>" placeholder="<?php esc_attr_e( 'Company name (optional)', 'skillpulse-lms' ); ?>">
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
		<div class="splms-settings-tab-panel <?php echo 'password' === $splms_settings_tab ? 'is-active' : ''; ?>" data-settings-tab="password">
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

		<?php
		// Allow modules to add settings tab panels (e.g., notification preferences).
		do_action( 'splms_settings_tabs_content', $splms_settings_tab, $splms_current_user_obj );
		?>
	</div>
</div>

