<?php
/**
 * Notifications Module Class
 *
 * Centralized notification management system.
 *
 * @package SkillPulse_LMS
 * @subpackage Notifications
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * SkillPulse LMS Notification Module Class
 *
 * @since 1.0.0
 */
class SkillPulse_LMS_Notification {

	/**
	 * Class instance.
	 *
	 * @var SkillPulse_LMS_Notification|null $instance
	 */
	private static $instance = null;

	/**
	 * Get the instance of this class.
	 *
	 * @since 1.0.0
	 *
	 * @return SkillPulse_LMS_Notification The class instance.
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			$class_name     = __CLASS__;
			self::$instance = new $class_name();
			self::$instance->setup_globals();
			self::$instance->load_classes();
		}

		return self::$instance;
	}

	/**
	 * Setup globals.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	private function setup_globals() {
		$files = array(
			'includes/modules/notifications/email/class-email-module',
			'includes/modules/notifications/in-app/class-in-app-notifications',
			'includes/modules/notifications/in-app/class-in-app-templates',
			'includes/modules/notifications/dispatcher/class-notification-dispatcher',
			'includes/modules/notifications/class-notification-preferences',
		);

		foreach ( $files as $file ) {
			if ( file_exists( SKILLPULSE_LMS_DIR_PATH . $file . '.php' ) ) {
				require_once SKILLPULSE_LMS_DIR_PATH . $file . '.php';
			}
		}
	}

	/**
	 * Load required classes.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	private function load_classes() {
		// Initialize email module (now under notifications).
		SkillPulse_LMS_Email_Module::get_instance();

		// Initialize in-app notifications.
		SkillPulse_LMS_In_App_Notifications::get_instance();

		// Initialize notification dispatcher.
		SkillPulse_LMS_Notification_Dispatcher::get_instance();

		// Register dashboard settings hooks.
		add_action( 'splms_settings_tabs_nav', array( $this, 'render_settings_tab_nav' ), 10, 2 );
		add_action( 'splms_settings_tabs_content', array( $this, 'render_settings_tab_content' ), 10, 2 );
	}

	/**
	 * Render notifications tab in dashboard settings navigation.
	 *
	 * @since 1.0.1
	 *
	 * @param object $dashboard    Dashboard instance.
	 * @param string $settings_tab Current settings tab.
	 * @return void
	 */
	public function render_settings_tab_nav( $dashboard, $settings_tab ) {
		$email_enabled  = function_exists( 'splms_is_email_notifications_enabled' ) && splms_is_email_notifications_enabled();
		$in_app_enabled = function_exists( 'splms_is_in_app_notifications_enabled' ) && splms_is_in_app_notifications_enabled();

		if ( ! $email_enabled && ! $in_app_enabled ) {
			return;
		}
		?>
		<a href="<?php echo esc_url( $dashboard->get_settings_tab_url( 'notifications' ) ); ?>" class="splms-settings-tab-btn <?php echo 'notifications' === $settings_tab ? 'is-active' : ''; ?>">
			<i class="hgi-stroke hgi-notification-03"></i>
			<?php esc_html_e( 'Notifications', 'skillpulse-lms' ); ?>
		</a>
		<?php
	}

	/**
	 * Render notifications preferences panel in dashboard settings.
	 *
	 * @since 1.0.1
	 *
	 * @param string   $settings_tab    Current settings tab.
	 * @param \WP_User $current_user    Current user object.
	 * @return void
	 */
	public function render_settings_tab_content( $settings_tab, $current_user ) {
		$email_enabled  = function_exists( 'splms_is_email_notifications_enabled' ) && splms_is_email_notifications_enabled();
		$in_app_enabled = function_exists( 'splms_is_in_app_notifications_enabled' ) && splms_is_in_app_notifications_enabled();

		if ( ! $email_enabled && ! $in_app_enabled ) {
			return;
		}

		$prefs_instance     = SkillPulse_LMS_Notification_Preferences::get_instance();
		$notification_prefs = $prefs_instance->get_user_preferences( $current_user->ID );
		?>
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

								<?php if ( $in_app_enabled ) { ?>
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
		<?php
	}
}
