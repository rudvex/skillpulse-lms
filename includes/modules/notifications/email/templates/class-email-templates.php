<?php
/**
 * Email Templates Class
 *
 * Handles email template management and sending.
 *
 * @package SkillPulse_LMS
 * @subpackage Email
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * SkillPulse LMS Email Templates Class
 *
 * @since 1.0.0
 */
class SkillPulse_LMS_Email_Templates {

	/**
	 * Class instance.
	 *
	 * @var SkillPulse_LMS_Email_Templates|null $instance
	 */
	private static $instance = null;

	/**
	 * Get the instance of this class.
	 *
	 * @since 1.0.0
	 *
	 * @return SkillPulse_LMS_Email_Templates The class instance.
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
	 * Setup action hooks.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	private function setup_actions() {
		// Load template classes.
		add_action( 'init', array( $this, 'load_template_classes' ) );
	}

	/**
	 * Load template classes.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function load_template_classes() {
		// Load abstract class first.
		require_once SKILLPULSE_LMS_DIR_PATH . 'includes/modules/notifications/email/templates/abstract-email-template.php';

		// Load individual template classes.
		$template_files = array(
			'activation-email.php',
			'welcome-email.php',
			'course-enrollment.php',
			'course-completion.php',
			'enrollment-reminder.php',
			'password-reset-email.php',
			'lesson-completion.php',
			'quiz-completion.php',
			'quiz-passed.php',
			'quiz-failed.php',
		);

		foreach ( $template_files as $file ) {
			$file_path = SKILLPULSE_LMS_DIR_PATH . 'includes/modules/notifications/email/templates/templates/' . $file;
			if ( file_exists( $file_path ) ) {
				require_once $file_path;
			}
		}

		// Initialize template instances.
		$this->init_template_instances();
	}

	/**
	 * Initialize template instances.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	private function init_template_instances() {
		$template_classes = array(
			'SkillPulse_LMS_Activation_Email_Template',
			'SkillPulse_LMS_Welcome_Email_Template',
			'SkillPulse_LMS_Course_Enrollment_Email_Template',
			'SkillPulse_LMS_Course_Completion_Email_Template',
			'SkillPulse_LMS_Enrollment_Reminder_Email_Template',
			'SkillPulse_LMS_Password_Reset_Email_Template',
			'SkillPulse_LMS_Lesson_Completion_Email_Template',
			'SkillPulse_LMS_Quiz_Completion_Email_Template',
			'SkillPulse_LMS_Quiz_Passed_Email_Template',
			'SkillPulse_LMS_Quiz_Failed_Email_Template',
		);

		foreach ( $template_classes as $class_name ) {
			if ( class_exists( $class_name ) ) {
				new $class_name();
			}
		}
	}

	/**
	 * Get all email templates.
	 *
	 * @since 1.0.0
	 *
	 * @return array All email templates.
	 */
	public function get_all_templates() {
		$templates = array();

		// Get templates from the filter (registered by template classes).
		$templates = apply_filters( 'splms_email_templates', $templates );

		// Get saved template data and merge with defaults.
		foreach ( $templates as $template_key => $template ) {
			$saved_template = get_option( "splms_email_template_{$template_key}", array() );

			if ( ! empty( $saved_template ) ) {
				$templates[ $template_key ] = wp_parse_args( $saved_template, $template );
			}
		}

		return $templates;
	}

	/**
	 * Get a specific template.
	 *
	 * @since 1.0.0
	 *
	 * @param string $template_key Template key.
	 * @return array Template data.
	 */
	public function get_template( $template_key ) {
		$templates = $this->get_all_templates();

		if ( ! isset( $templates[ $template_key ] ) ) {
			return array();
		}

		return $templates[ $template_key ];
	}

	/**
	 * Save template data.
	 *
	 * @since 1.0.0
	 *
	 * @param array $template_data Template data.
	 * @return bool True on success, false on failure.
	 */
	public function save_template( $template_data ) {
		if ( ! isset( $template_data['template_key'] ) ) {
			return false;
		}

		$template_key = sanitize_text_field( $template_data['template_key'] );
		$option_name  = "splms_email_template_{$template_key}";

		$save_data = array(
			'subject'   => sanitize_text_field( $template_data['subject'] ),
			'content'   => wp_kses_post( $template_data['content'] ),
			'is_active' => (bool) $template_data['is_active'],
		);

		update_option( $option_name, $save_data );

		return true;
	}

	/**
	 * Reset template to default.
	 *
	 * @since 1.0.0
	 *
	 * @param string $template_key Template key.
	 * @return bool True on success, false on failure.
	 */
	public function reset_template( $template_key ) {
		$option_name = "splms_email_template_{$template_key}";

		return delete_option( $option_name );
	}

	/**
	 * Test template by sending a test email.
	 *
	 * @since 1.0.0
	 *
	 * @param array $template_data Template data.
	 * @return WP_Error|bool True on success, WP_Error on failure.
	 */
	public function test_template( $template_data ) {
		if ( ! isset( $template_data['subject'] ) || ! isset( $template_data['content'] ) ) {
			return new WP_Error( 'missing_data', __( 'Missing subject or content in template data.', 'skillpulse-lms' ) );
		}

		$admin_email = get_option( 'admin_email' );
		$site_name   = get_bloginfo( 'name' );

		// Create test template data.
		$test_template = array(
			'subject'   => $template_data['subject'],
			'content'   => $template_data['content'],
			'is_active' => true,
		);

		// Test replacements.
		$replacements = array(
			'site_name'       => $site_name,
			'user_name'       => 'Test User',
			'course_title'    => 'Test Course',
			'activation_link' => 'https://example.com/activate',
			'login_url'       => 'https://example.com/login',
			'certificate_url' => 'https://example.com/certificate',
		);

		// Use the email sender class for consistency.
		$email_sender = SkillPulse_LMS_Email_Sender::get_instance();
		$result       = $email_sender->send_email( $admin_email, $test_template, $replacements );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return true;
	}
}
