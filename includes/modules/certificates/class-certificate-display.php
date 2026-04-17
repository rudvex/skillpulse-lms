<?php
/**
 * Certificate Display Frontend
 *
 * Handles frontend certificate display functionality.
 *
 * @since      1.0.0
 * @package    SkillPulse_LMS
 * @subpackage Frontend
 * @author     SkillPulseLMS Team <support@skillpulselms.com>
 * @license    GPL-2.0+
 * @link       https://skillpulselms.com
 * @copyright  2025 SkillPulseLMS Team
 */

/**
 * Certificate Display Class
 *
 * Handles certificate display functionality on the frontend.
 *
 * @since   1.0.0
 * @package SkillPulse_LMS
 */
class SkillPulse_LMS_Certificate_Display {

	/**
	 * Class instance.
	 *
	 * @since 1.0.0
	 *
	 * @var SkillPulse_LMS_Certificate_Display|null $instance
	 */
	private static $instance;

	/**
	 * Get the instance of this class.
	 *
	 * @since 1.0.0
	 *
	 * @return SkillPulse_LMS_Certificate_Display The class instance.
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	private function __construct() {
		$this->setup_hooks();
	}

	/**
	 * Setup hooks.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	private function setup_hooks() {
		// AJAX handlers.
		add_action( 'wp_ajax_splms_get_user_certificate', array( $this, 'ajax_get_user_certificate' ) );
		add_action( 'wp_ajax_nopriv_splms_get_user_certificate', array( $this, 'ajax_get_user_certificate' ) );
	}

	/**
	 * AJAX handler to get user certificate.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function ajax_get_user_certificate() {
		// Security check.
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ?? '' ) ), 'splms_certificate_nonce' ) ) {
			wp_send_json_error( esc_html__( 'Security verification failed', 'skillpulse-lms' ) );
		}

		if ( ! is_user_logged_in() ) {
			wp_send_json_error( esc_html__( 'You must be logged in to access certificates', 'skillpulse-lms' ) );
		}

		// Get user and course IDs from request.
		$user_id   = isset( $_POST['user_id'] ) ? absint( wp_unslash( $_POST['user_id'] ) ) : 0;
		$course_id = isset( $_POST['course_id'] ) ? absint( wp_unslash( $_POST['course_id'] ) ) : 0;

		if ( ! $user_id || ! $course_id ) {
			wp_send_json_error( esc_html__( 'Invalid user or course ID', 'skillpulse-lms' ) );
		}

		// Security check: Ensure user can only access their own certificates.
		$current_user_id = get_current_user_id();
		if ( ! $current_user_id || $current_user_id !== $user_id ) {
			wp_send_json_error( esc_html__( 'You can only access your own certificates', 'skillpulse-lms' ) );
		}

		// Check if user has certificate.
		$simple_certificates = SkillPulse_LMS_Certificates::get_instance();
		if ( ! $simple_certificates->user_has_certificate( $user_id, $course_id ) ) {
			wp_send_json_error( esc_html__( 'Certificate not found', 'skillpulse-lms' ) );
		}

		$certificate_link = $simple_certificates->get_certificate_link( $user_id, $course_id );

		if ( $certificate_link ) {
			wp_send_json_success(
				array(
					'certificate_url' => $certificate_link,
					'message'         => esc_html__( 'Certificate loaded successfully', 'skillpulse-lms' ),
				)
			);
		} else {
			wp_send_json_error( esc_html__( 'Certificate not found or access denied', 'skillpulse-lms' ) );
		}
	}
}
