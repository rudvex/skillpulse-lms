<?php
/**
 * Email Sender Class
 *
 * Handles email sending functionality.
 *
 * @package SkillPulse_LMS
 * @subpackage Email
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * SkillPulse LMS Email Sender Class
 *
 * @since 1.0.0
 */
class SkillPulse_LMS_Email_Sender {

	/**
	 * Class instance.
	 *
	 * @var SkillPulse_LMS_Email_Sender|null $instance
	 */
	private static $instance = null;

	/**
	 * Get the instance of this class.
	 *
	 * @since 1.0.0
	 *
	 * @return SkillPulse_LMS_Email_Sender The class instance.
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			$class_name     = __CLASS__;
			self::$instance = new $class_name();
			self::$instance->setup_actions();
		}

		return self::$instance;
	}

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	private function __construct() {
		// Private constructor to prevent direct instantiation.
	}

	/**
	 * Setup action hooks.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	private function setup_actions() {
		// Hook for processing email queue.
		add_action( 'splms_process_email_queue', array( $this, 'process_email_queue' ) );

		// Setup trial email limit filters (since 1.0.1).
		add_filter( 'splms_before_send_email', array( $this, 'check_trial_email_limits' ), 5, 3 );
		add_action( 'splms_email_sent', array( $this, 'track_email_usage' ), 10, 6 );
	}

	/**
	 * Check trial email limits before sending.
	 *
	 * Prevents email sending when trial limit is exceeded and adds upgrade prompts.
	 *
	 * @since 1.0.1
	 *
	 * @param bool   $send_email  Whether to send email (default true).
	 * @param array  $email_data  Email data including template and recipient.
	 * @param string $context     Email context (optional).
	 * @return bool|WP_Error True to allow sending, WP_Error to block.
	 */
	public function check_trial_email_limits( $send_email, $email_data, $context = '' ) {
		// Only check if trial manager is available.
		if ( ! class_exists( 'SkillPulse_LMS_Trial_Manager' ) ) {
			return $send_email;
		}

		$trial_manager = SkillPulse_LMS_Trial_Manager::get_instance();

		// Allow unlimited emails if license is active.
		if ( ! $trial_manager->is_trial_active() ) {
			return $send_email;
		}

		$usage_tracker = $trial_manager->get_usage_tracker();
		if ( ! $usage_tracker ) {
			return $send_email;
		}

		// Check if email sending is still allowed.
		$allowed = $usage_tracker->is_feature_usage_allowed( 'emails_sent' );

		if ( is_wp_error( $allowed ) ) {
			// Add admin notice for limit reached.
			$this->add_email_limit_notice();

			// Return WP_Error to prevent sending.
			return new WP_Error(
				'trial_email_limit',
				__( 'Trial email limit reached (100 emails). Upgrade to send unlimited emails.', 'skillpulse-lms' )
			);
		}

		// Check for critical email types that should be prioritized.
		if ( isset( $email_data['template']['type'] ) && $this->is_critical_email( $email_data['template']['type'] ) ) {
			// Critical emails can still be sent even if near limit.
			return $send_email;
		}

		return $send_email;
	}

	/**
	 * Track email usage after successful sending.
	 *
	 * Increments email usage counter during trial period.
	 *
	 * @since 1.0.1
	 *
	 * @param bool   $email_sent    Whether email was sent successfully.
	 * @param string $to_email      Recipient email address.
	 * @param string $subject       Email subject.
	 * @param string $message       Email message.
	 * @param array  $template      Template data.
	 * @param array  $replacements  Placeholder replacements.
	 * @return void
	 */
	public function track_email_usage( $email_sent, $to_email, $subject, $message, $template, $replacements ) {
		// Only track if email was actually sent.
		if ( ! $email_sent ) {
			return;
		}

		// Only track during trial period.
		if ( ! class_exists( 'SkillPulse_LMS_Trial_Manager' ) ) {
			return;
		}

		$trial_manager = SkillPulse_LMS_Trial_Manager::get_instance();

		if ( ! $trial_manager->is_trial_active() ) {
			return;
		}

		$usage_tracker = $trial_manager->get_usage_tracker();
		if ( $usage_tracker ) {
			$usage_tracker->increment_usage( 'emails_sent' );
		}
	}

	/**
	 * Add email limit notice to admin.
	 *
	 * Shows upgrade prompt when email limit is reached.
	 *
	 * @since 1.0.1
	 *
	 * @return void
	 */
	private function add_email_limit_notice() {
		add_action(
			'admin_notices',
			function () {
				echo '<div class="notice notice-warning is-dismissible splms-trial-notice">';
				echo '<div class="notice-content">';
				printf(
					'<p>%s</p>',
					sprintf(
						/* translators: %s: License page URL */
						wp_kses_post( __( 'Trial email limit reached (100 emails sent). <a href="%s" class="button button-primary">Upgrade Now</a> for unlimited email notifications.', 'skillpulse-lms' ) ),
						esc_url( admin_url( 'admin.php?page=splms-license&focus=upgrade' ) )
					)
				);
				echo '</div>';
				echo '</div>';
			}
		);
	}

	/**
	 * Check if email type is critical and should be prioritized.
	 *
	 * Critical emails are sent even when approaching limits.
	 *
	 * @since 1.0.1
	 *
	 * @param string $email_type Email type identifier.
	 * @return bool True if critical email type.
	 */
	private function is_critical_email( $email_type ) {
		$critical_types = array(
			'password_reset',
			'account_activation',
			'order_confirmation',
			'license_activation',
			'trial_expiry_warning',
		);

		/**
		 * Filter critical email types that bypass trial limits.
		 *
		 * @since 1.0.1
		 *
		 * @param array $critical_types Array of critical email type identifiers.
		 */
		$critical_types = apply_filters( 'splms_critical_email_types', $critical_types );

		return in_array( $email_type, $critical_types, true );
	}

	/**
	 * Send an email using a template.
	 *
	 * @since 1.0.0
	 *
	 * @param string $to_email     Recipient email address.
	 * @param array  $template     Template data.
	 * @param array  $replacements Placeholder replacements.
	 * @param array  $attachments  Optional. Array of file paths to attach.
	 * @return bool|WP_Error True on success, WP_Error on failure.
	 */
	public function send_email( $to_email, $template, $replacements = array(), $attachments = array() ) {
		// Check if emails are enabled globally.
		if ( ! function_exists( 'splms_is_email_notifications_enabled' ) || ! splms_is_email_notifications_enabled() ) {
			return new WP_Error( 'emails_disabled', __( 'Email notifications are currently disabled.', 'skillpulse-lms' ) );
		}

		// Check trial email limits (since 1.0.1).
		$email_data   = array(
			'to_email'     => $to_email,
			'template'     => $template,
			'replacements' => $replacements,
			'attachments'  => $attachments,
		);
		$send_allowed = apply_filters( 'splms_before_send_email', true, $email_data, 'direct_send' );

		if ( is_wp_error( $send_allowed ) ) {
			return $send_allowed;
		}

		// Check if email queue is enabled.
		$settings            = SkillPulse_LMS_Settings::get_instance()->get_all_settings();
		$email_queue_enabled = isset( $settings['notifications']['email_settings']['email_queue_enabled'] )
			? $settings['notifications']['email_settings']['email_queue_enabled']
			: false;

		if ( $email_queue_enabled && empty( $attachments ) ) {
			// Queue the email instead of sending immediately (only if no attachments).
			// Note: Emails with attachments are sent immediately to avoid file cleanup issues.
			return $this->queue_email( $to_email, $template, $replacements );
		}

		if ( empty( $template['content'] ) ) {
			return new WP_Error( 'no_content', __( 'Email template has no content.', 'skillpulse-lms' ) );
		}

		$subject = $this->replace_placeholders( $template['subject'], $replacements );

		$message = $this->build_email_message( $template, $replacements );

		/**
		 * Filters the email headers.
		 *
		 * @param array  $headers  Email headers.
		 * @param string $to_email Recipient email.
		 * @param array  $template Template data.
		 */
		$headers = $this->get_email_headers( $to_email );

		/**
		 * Filters email attachments.
		 *
		 * @param array  $attachments Email attachments.
		 * @param string $to_email    Recipient email.
		 * @param array  $template     Template data.
		 * @param array  $replacements Placeholder replacements.
		 */
		$attachments = apply_filters( 'splms_email_attachments', $attachments, $to_email, $template, $replacements );

		$sent = wp_mail( $to_email, $subject, $message, $headers, $attachments );

		if ( ! $sent ) {
			return new WP_Error( 'send_failed', __( 'Failed to send email.', 'skillpulse-lms' ) );
		}

		/**
		 * Fires after an email is sent.
		 *
		 * @param bool   $sent         Whether email was sent successfully.
		 * @param string $to_email     Recipient email.
		 * @param string $subject      Email subject.
		 * @param string $message      Email message.
		 * @param array  $template     Template data.
		 * @param array  $replacements Placeholder replacements.
		 */
		do_action( 'splms_email_sent', $sent, $to_email, $subject, $message, $template, $replacements );

		return true;
	}

	/**
	 * Get email headers.
	 *
	 * @since 1.0.0
	 *
	 * @param string $to_email Recipient email address.
	 * @return array Email headers.
	 */
	public function get_email_headers( $to_email ) {
		// Get custom from name and email from settings.
		$from_name  = splms_get_setting( 'from_name', get_bloginfo( 'name' ) );
		$from_email = splms_get_setting( 'from_email', get_option( 'admin_email' ) );

		$headers = array(
			'Content-Type: text/html; charset=UTF-8',
			'From: ' . $from_name . ' <' . $from_email . '>',
		);

		$headers = apply_filters( 'splms_email_headers', $headers, $to_email );

		return $headers;
	}

	/**
	 * Queue an email for later sending.
	 *
	 * @since 1.0.0
	 *
	 * @param string $to_email     Recipient email address.
	 * @param array  $template     Template data.
	 * @param array  $replacements Placeholder replacements.
	 * @return bool|WP_Error True on success, WP_Error on failure.
	 */
	private function queue_email( $to_email, $template, $replacements = array() ) {
		$queued_email = array(
			'id'           => uniqid( 'email_' ),
			'to_email'     => $to_email,
			'template'     => $template,
			'replacements' => $replacements,
			'created_at'   => current_time( 'mysql' ),
			'status'       => 'pending',
			'retry_count'  => 0,
			'max_retries'  => 3,
		);

		$email_queue   = get_option( 'splms_email_queue', array() );
		$email_queue[] = $queued_email;
		update_option( 'splms_email_queue', $email_queue );

		// Schedule processing if not already scheduled.
		$interval = splms_get_setting( 'queue_processing_interval', 60 );
		$interval = intval( $interval );
		if ( ! wp_next_scheduled( 'splms_process_email_queue' ) ) {
			wp_schedule_single_event( time() + $interval, 'splms_process_email_queue' );
		}

		return true;
	}

	/**
	 * Process queued emails in batches.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function process_email_queue() {
		$batch_limit = splms_get_setting( 'batch_email_limit', 50 );
		$batch_limit = intval( $batch_limit );

		$email_queue = get_option( 'splms_email_queue', array() );

		if ( empty( $email_queue ) ) {
			return;
		}

		// Process emails in batches.
		$batch           = array_slice( $email_queue, 0, $batch_limit );
		$remaining       = array_slice( $email_queue, $batch_limit );
		$processed_count = 0;
		$failed_count    = 0;

		foreach ( $batch as $index => $queued_email ) {
			// Skip emails that have exceeded max retries.
			if ( $queued_email['retry_count'] >= $queued_email['max_retries'] ) {
				$batch[ $index ]['status']       = 'failed';
				$batch[ $index ]['processed_at'] = current_time( 'mysql' );
				$batch[ $index ]['error']        = 'Max retries exceeded';
				++$failed_count;
				continue;
			}

			// Send the email.
			$result = $this->send_email_direct( $queued_email['to_email'], $queued_email['template'], $queued_email['replacements'] );

			if ( is_wp_error( $result ) ) {
				++$batch[ $index ]['retry_count'];
				$batch[ $index ]['status'] = $batch[ $index ]['retry_count'] >= $batch[ $index ]['max_retries'] ? 'failed' : 'pending';
				$batch[ $index ]['error']  = $result->get_error_message();
				++$failed_count;
			} else {
				$batch[ $index ]['status']       = 'sent';
				$batch[ $index ]['processed_at'] = current_time( 'mysql' );
				++$processed_count;
			}
		}

		// Move failed emails to failed queue for potential retry.
		$failed_queue = get_option( 'splms_email_failed_queue', array() );
		foreach ( $batch as $email ) {
			if ( 'failed' === $email['status'] ) {
				$failed_queue[] = $email;
			}
		}
		update_option( 'splms_email_failed_queue', $failed_queue );

		// Update main queue with remaining emails.
		update_option( 'splms_email_queue', $remaining );

		// Schedule next batch if there are remaining emails.
		if ( ! empty( $remaining ) ) {
			$interval = splms_get_setting( 'queue_processing_interval', 60 );
			$interval = intval( $interval );
			wp_schedule_single_event( time() + $interval, 'splms_process_email_queue' );
		}

		// Log processing results.
		$this->log_queue_processing( $processed_count, $failed_count, count( $batch ) );
	}

	/**
	 * Retry failed emails.
	 *
	 * @since 1.0.0
	 *
	 * @param int $max_retries Maximum number of retries per email.
	 * @return array Processing results.
	 */
	public function retry_failed_emails( $max_retries = 3 ) {
		$failed_queue       = get_option( 'splms_email_failed_queue', array() );
		$retry_queue        = array();
		$permanently_failed = array();

		foreach ( $failed_queue as $email ) {
			if ( $email['retry_count'] < $max_retries ) {
				++$email['retry_count'];
				$email['status'] = 'pending';
				$email['error']  = '';
				$retry_queue[]   = $email;
			} else {
				$permanently_failed[] = $email;
			}
		}

		// Add retry emails to main queue.
		if ( ! empty( $retry_queue ) ) {
			$email_queue = get_option( 'splms_email_queue', array() );
			$email_queue = array_merge( $retry_queue, $email_queue );
			update_option( 'splms_email_queue', $email_queue );

			// Schedule processing.
			$interval = splms_get_setting( 'queue_processing_interval', 60 );
			$interval = intval( $interval );
			if ( ! wp_next_scheduled( 'splms_process_email_queue' ) ) {
				wp_schedule_single_event( time() + $interval, 'splms_process_email_queue' );
			}
		}

		// Update failed queue with permanently failed emails.
		update_option( 'splms_email_failed_queue', $permanently_failed );

		return array(
			'retried'            => count( $retry_queue ),
			'permanently_failed' => count( $permanently_failed ),
		);
	}

	/**
	 * Get queue statistics.
	 *
	 * @since 1.0.0
	 *
	 * @return array Queue statistics.
	 */
	public function get_queue_stats() {
		$email_queue  = get_option( 'splms_email_queue', array() );
		$failed_queue = get_option( 'splms_email_failed_queue', array() );

		$pending_count = 0;
		$sent_count    = 0;
		$failed_count  = 0;

		foreach ( $email_queue as $email ) {
			switch ( $email['status'] ) {
				case 'pending':
					++$pending_count;
					break;
				case 'sent':
					++$sent_count;
					break;
				case 'failed':
					++$failed_count;
					break;
			}
		}

		$failed_count += count( $failed_queue );

		return array(
			'pending' => $pending_count,
			'sent'    => $sent_count,
			'failed'  => $failed_count,
			'total'   => $pending_count + $sent_count + $failed_count,
		);
	}

	/**
	 * Clear email queue.
	 *
	 * @since 1.0.0
	 *
	 * @param string $status Status to clear (pending, sent, failed, all).
	 * @return bool True on success, false on failure.
	 */
	public function clear_email_queue( $status = 'all' ) {
		if ( 'all' === $status ) {
			delete_option( 'splms_email_queue' );
			delete_option( 'splms_email_failed_queue' );

			return true;
		}

		$email_queue  = get_option( 'splms_email_queue', array() );
		$failed_queue = get_option( 'splms_email_failed_queue', array() );

		// Filter out emails with specified status.
		$email_queue = array_filter(
			$email_queue,
			function ( $email ) use ( $status ) {
				return $status !== $email['status'];
			}
		);

		$failed_queue = array_filter(
			$failed_queue,
			function ( $email ) use ( $status ) {
				return $status !== $email['status'];
			}
		);

		update_option( 'splms_email_queue', array_values( $email_queue ) );
		update_option( 'splms_email_failed_queue', array_values( $failed_queue ) );

		return true;
	}

	/**
	 * Log queue processing results.
	 *
	 * @since 1.0.0
	 *
	 * @param int $processed_count Number of processed emails.
	 * @param int $failed_count    Number of failed emails.
	 * @param int $total_count     Total number of emails.
	 * @return void
	 */
	private function log_queue_processing( $processed_count, $failed_count, $total_count ) {
		$log_entry = array(
			'timestamp'    => current_time( 'mysql' ),
			'processed'    => $processed_count,
			'failed'       => $failed_count,
			'total'        => $total_count,
			'success_rate' => $total_count > 0 ? round( ( $processed_count / $total_count ) * 100, 2 ) : 0,
		);

		$queue_logs   = get_option( 'splms_email_queue_logs', array() );
		$queue_logs[] = $log_entry;

		// Keep only last 100 log entries.
		if ( count( $queue_logs ) > 100 ) {
			$queue_logs = array_slice( $queue_logs, - 100 );
		}

		update_option( 'splms_email_queue_logs', $queue_logs );
	}

	/**
	 * Send email directly without queue check.
	 *
	 * @since 1.0.0
	 *
	 * @param string $to_email     Recipient email address.
	 * @param array  $template     Template data.
	 * @param array  $replacements Placeholder replacements.
	 * @return bool|WP_Error True on success, WP_Error on failure.
	 */
	private function send_email_direct( $to_email, $template, $replacements = array() ) {
		if ( empty( $template['content'] ) ) {
			return new WP_Error( 'no_content', __( 'Email template has no content.', 'skillpulse-lms' ) );
		}

		$subject = $this->replace_placeholders( $template['subject'], $replacements );
		$message = $this->build_email_message( $template, $replacements );

		// Get custom from name and email from settings.
		$from_name  = splms_get_setting( 'from_name', get_bloginfo( 'name' ) );
		$from_email = splms_get_setting( 'from_email', get_option( 'admin_email' ) );
		// Check if SMTP is enabled.
		$use_smtp = splms_get_setting( 'use_smtp', false );
		if ( $use_smtp ) {
			return $this->send_email_via_smtp( $to_email, $subject, $message, $from_name, $from_email );
		} else {
			return $this->send_email_via_wp_mail( $to_email, $subject, $message, $from_name, $from_email );
		}
	}

	/**
	 * Send email via WordPress default mail function.
	 *
	 * @since 1.0.0
	 *
	 * @param string $to_email  Recipient email address.
	 * @param string $subject   Email subject.
	 * @param string $message   Email message.
	 * @param string $from_name From name.
	 * @param string $from_email From email address.
	 * @return bool|WP_Error True on success, WP_Error on failure.
	 */
	private function send_email_via_wp_mail( $to_email, $subject, $message, $from_name, $from_email ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- Parameters kept for consistency with send_email_via_smtp(), handled by get_email_headers().

		$headers = $this->get_email_headers( $to_email );

		$sent = wp_mail( $to_email, $subject, $message, $headers );

		if ( ! $sent ) {
			return new WP_Error( 'send_failed', __( 'Failed to send email via WordPress mail.', 'skillpulse-lms' ) );
		}

		return true;
	}

	/**
	 * Send email via SMTP.
	 *
	 * @since 1.0.0
	 *
	 * @param string $to_email  Recipient email address.
	 * @param string $subject   Email subject.
	 * @param string $message   Email message.
	 * @param string $from_name From name.
	 * @param string $from_email From email address.
	 * @return bool|WP_Error True on success, WP_Error on failure.
	 */
	private function send_email_via_smtp( $to_email, $subject, $message, $from_name, $from_email ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- Parameters kept for consistency with send_email_via_wp_mail(), handled by get_email_headers().
		// Get SMTP settings using correct nested path.
		$smtp_host       = splms_get_setting( 'smtp_host', '' );
		$smtp_port       = intval( splms_get_setting( 'smtp_port', 587 ) );
		$smtp_encryption = splms_get_setting( 'smtp_encryption', 'tls' );
		$smtp_username   = splms_get_setting( 'smtp_username', '' );
		$smtp_password   = splms_get_setting( 'smtp_password', '' );

		// Validate SMTP settings.
		if ( empty( $smtp_host ) ) {
			return new WP_Error( 'smtp_config_error', __( 'SMTP host is required.', 'skillpulse-lms' ) );
		}

		if ( empty( $smtp_username ) ) {
			return new WP_Error( 'smtp_config_error', __( 'SMTP username is required.', 'skillpulse-lms' ) );
		}

		if ( empty( $smtp_password ) ) {
			return new WP_Error( 'smtp_config_error', __( 'SMTP password is required.', 'skillpulse-lms' ) );
		}

		// Configure PHPMailer for SMTP.
		add_action(
			'phpmailer_init',
			function ( $phpmailer ) use ( $smtp_host, $smtp_port, $smtp_encryption, $smtp_username, $smtp_password ) {
				$phpmailer->isSMTP();
				// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- PHPMailer library uses camelCase.
				$phpmailer->Host = $smtp_host;
				// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- PHPMailer library uses camelCase.
				$phpmailer->Port = $smtp_port;
				// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- PHPMailer library uses camelCase.
				$phpmailer->SMTPAuth = true;
				// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- PHPMailer library uses camelCase.
				$phpmailer->Username = $smtp_username;
				// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- PHPMailer library uses camelCase.
				$phpmailer->Password = $smtp_password;

				// Set encryption.
				switch ( $smtp_encryption ) {
					case 'ssl':
						// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- PHPMailer library uses camelCase.
						$phpmailer->SMTPSecure = 'ssl';
						break;
					case 'tls':
						// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- PHPMailer library uses camelCase.
						$phpmailer->SMTPSecure = 'tls';
						break;
					default:
						// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- PHPMailer library uses camelCase.
						$phpmailer->SMTPSecure = '';
						break;
				}
			}
		);

		// Send email using WordPress mail function (now configured for SMTP).
		$headers = $this->get_email_headers( $to_email );

		$sent = wp_mail( $to_email, $subject, $message, $headers );

		if ( ! $sent ) {
			return new WP_Error( 'smtp_send_failed', __( 'Failed to send email via SMTP.', 'skillpulse-lms' ) );
		}

		return true;
	}

	/**
	 * Test SMTP connection.
	 *
	 * @since 1.0.0
	 *
	 * @return bool|WP_Error True on success, WP_Error on failure.
	 */
	public function test_smtp_connection() {
		// Check if SMTP is enabled.
		$use_smtp = splms_get_setting( 'use_smtp', false );
		if ( ! $use_smtp ) {
			return new WP_Error( 'smtp_disabled', __( 'SMTP is not enabled.', 'skillpulse-lms' ) );
		}

		// Get SMTP settings using correct nested path.
		$smtp_host       = splms_get_setting( 'smtp_host', '' );
		$smtp_port       = intval( splms_get_setting( 'smtp_port', 587 ) );
		$smtp_encryption = splms_get_setting( 'smtp_encryption', 'tls' );
		$smtp_username   = splms_get_setting( 'smtp_username', '' );
		$smtp_password   = splms_get_setting( 'smtp_password', '' );

		// Validate SMTP settings.
		if ( empty( $smtp_host ) ) {
			return new WP_Error( 'smtp_config_error', __( 'SMTP host is required.', 'skillpulse-lms' ) );
		}

		if ( empty( $smtp_username ) ) {
			return new WP_Error( 'smtp_config_error', __( 'SMTP username is required.', 'skillpulse-lms' ) );
		}

		if ( empty( $smtp_password ) ) {
			return new WP_Error( 'smtp_config_error', __( 'SMTP password is required.', 'skillpulse-lms' ) );
		}

		try {
			// Create PHPMailer instance for testing.
			require_once ABSPATH . WPINC . '/PHPMailer/PHPMailer.php';
			require_once ABSPATH . WPINC . '/PHPMailer/SMTP.php';
			require_once ABSPATH . WPINC . '/PHPMailer/Exception.php';

			$mail = new PHPMailer\PHPMailer\PHPMailer( true );
			$mail->isSMTP();
			// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- PHPMailer library uses camelCase.
			$mail->Host = $smtp_host;
			// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- PHPMailer library uses camelCase.
			$mail->Port = $smtp_port;
			// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- PHPMailer library uses camelCase.
			$mail->SMTPAuth = true;
			// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- PHPMailer library uses camelCase.
			$mail->Username = $smtp_username;
			// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- PHPMailer library uses camelCase.
			$mail->Password = $smtp_password;

			// Set encryption.
			switch ( $smtp_encryption ) {
				case 'ssl':
					// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- PHPMailer library uses camelCase.
					$mail->SMTPSecure = 'ssl';
					break;
				case 'tls':
					// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- PHPMailer library uses camelCase.
					$mail->SMTPSecure = 'tls';
					break;
				default:
					// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- PHPMailer library uses camelCase.
					$mail->SMTPSecure = '';
					break;
			}

			// Test connection.
			$mail->smtpConnect();
			$mail->smtpClose();

			return true;
		} catch ( Exception $e ) {
			/* translators: %s: Error message. */
			return new WP_Error( 'smtp_connection_failed', sprintf( __( 'SMTP connection failed: %s', 'skillpulse-lms' ), $e->getMessage() ) );
		}
	}

	/**
	 * Test the email queue system by adding a test email.
	 *
	 * @since 1.0.0
	 *
	 * @param string $test_email Test email address.
	 * @return bool|WP_Error True on success, WP_Error on failure.
	 */
	public function test_queue_system( $test_email ) {
		$test_template = array(
			'subject' => 'Test Email from SkillPulse LMS Queue System',
			'content' => '<p>This is a test email to verify the queue system is working properly.</p><p>Sent at: {current_time}</p>',
		);

		$replacements = array(
			'current_time' => current_time( 'mysql' ),
		);

		// Add to queue.
		$result = $this->queue_email( $test_email, $test_template, $replacements );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return true;
	}

	/**
	 * Build email message from template.
	 *
	 * @since 1.0.0
	 *
	 * @param array $template     Template data.
	 * @param array $replacements Placeholder replacements.
	 * @return string Email message.
	 */
	private function build_email_message( $template, $replacements ) {
		$message = '';

		// Add content.
		$message .= '<div class="email-content">' . $this->replace_placeholders( $template['content'], $replacements ) . '</div>';

		return $message;
	}

	/**
	 * Replace placeholders in text.
	 *
	 * @since 1.0.0
	 *
	 * @param string $text         Text with placeholders.
	 * @param array  $replacements Replacement values.
	 * @return string Text with placeholders replaced.
	 */
	public function replace_placeholders( $text, $replacements ) {
		foreach ( $replacements as $placeholder => $value ) {
			// Ensure value is a string before using str_replace.
			if ( is_array( $value ) ) {
				$value = implode( ', ', $value );
			} elseif ( is_object( $value ) ) {
				$value = (string) $value;
			} elseif ( ! is_string( $value ) && ! is_numeric( $value ) ) {
				$value = '';
			}

			$text = str_replace( '{' . $placeholder . '}', $value, $text );
		}

		return $text;
	}
}
