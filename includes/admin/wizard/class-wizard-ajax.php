<?php
/**
 * Wizard AJAX Handler
 *
 * Handles AJAX requests for the setup wizard. This is minimal because
 * we reuse existing license and trial AJAX handlers.
 *
 * @package SkillPulse_LMS
 * @since [SPLMS_VERSION]
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class SkillPulse_LMS_Wizard_Ajax
 *
 * Minimal AJAX handler for wizard-specific functionality.
 * License and trial operations use existing AJAX handlers.
 */
class SkillPulse_LMS_Wizard_Ajax {

	/**
	 * Single instance of the class.
	 *
	 * @var SkillPulse_LMS_Wizard_Ajax
	 */
	private static $instance;

	/**
	 * Get single instance of the class.
	 *
	 * @return SkillPulse_LMS_Wizard_Ajax
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
			self::$instance->setup_hooks();
		}
		return self::$instance;
	}

	/**
	 * Setup WordPress action hooks.
	 */
	private function setup_hooks() {
		add_action( 'wp_ajax_splms_wizard_save_step', array( $this, 'save_step' ) );
		add_action( 'wp_ajax_splms_wizard_complete', array( $this, 'complete_wizard' ) );
		add_action( 'wp_ajax_splms_wizard_skip', array( $this, 'skip_wizard' ) );
		add_action( 'wp_ajax_splms_wizard_get_status', array( $this, 'get_wizard_status' ) );
	}

	/**
	 * Save wizard step data.
	 *
	 * Note: License and trial operations use existing AJAX handlers:
	 * - splms_activate_license (from License Manager)
	 * - splms_get_license_info (from License Manager)
	 * - Trial operations via trial manager
	 */
	public function save_step() {
		// Verify nonce.
		check_ajax_referer( 'splms_wizard_nonce', 'nonce' );

		// Verify capabilities.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array(
				'message' => __( 'Insufficient permissions.', 'skillpulse-lms' ),
			) );
		}

		// Get and sanitize input.
		$step = sanitize_text_field( $_POST['step'] ?? '' );
		$data = $_POST['data'] ?? array();

		// Validate step.
		if ( empty( $step ) ) {
			wp_send_json_error( array(
				'message' => __( 'Invalid step.', 'skillpulse-lms' ),
			) );
		}

		// Sanitize data based on step.
		$sanitized_data = $this->sanitize_step_data( $step, $data );

		// Get wizard instance and save data.
		$wizard = SkillPulse_LMS_Setup_Wizard::get_instance();
		$result = $wizard->save_step_data( $step, $sanitized_data );

		if ( $result['success'] ) {
			wp_send_json_success( $result );
		} else {
			wp_send_json_error( $result );
		}
	}

	/**
	 * Complete wizard.
	 */
	public function complete_wizard() {
		// Verify nonce.
		check_ajax_referer( 'splms_wizard_nonce', 'nonce' );

		// Verify capabilities.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array(
				'message' => __( 'Insufficient permissions.', 'skillpulse-lms' ),
			) );
		}

		// Get wizard instance and complete.
		$wizard = SkillPulse_LMS_Setup_Wizard::get_instance();
		$result = $wizard->complete_wizard();

		if ( $result['success'] ) {
			wp_send_json_success( $result );
		} else {
			wp_send_json_error( $result );
		}
	}

	/**
	 * Skip wizard (complete with minimal setup).
	 */
	public function skip_wizard() {
		// Verify nonce.
		check_ajax_referer( 'splms_wizard_nonce', 'nonce' );

		// Verify capabilities.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array(
				'message' => __( 'Insufficient permissions.', 'skillpulse-lms' ),
			) );
		}

		// Mark wizard as completed but skipped.
		update_option( 'splms_wizard_completed', true );
		update_option( 'splms_wizard_data', array(
			'completed'    => true,
			'skipped'      => true,
			'completed_at' => time(),
		) );

		wp_send_json_success( array(
			'message'      => __( 'Wizard skipped successfully.', 'skillpulse-lms' ),
			'redirect_url' => admin_url( 'admin.php?page=skillpulse-lms' ),
		) );
	}

	/**
	 * Get wizard status.
	 */
	public function get_wizard_status() {
		// Verify nonce.
		check_ajax_referer( 'splms_wizard_nonce', 'nonce' );

		// Get wizard data.
		$wizard_data = get_option( 'splms_wizard_data', array() );
		$is_completed = get_option( 'splms_wizard_completed', false );

		wp_send_json_success( array(
			'completed'    => $is_completed,
			'wizard_data'  => $wizard_data,
			'current_step' => $wizard_data['current_step'] ?? 'welcome',
		) );
	}

	/**
	 * Sanitize step data based on step type.
	 *
	 * @param string $step Step identifier.
	 * @param array  $data Raw data.
	 * @return array Sanitized data.
	 */
	private function sanitize_step_data( $step, $data ) {
		$sanitized = array();

		switch ( $step ) {
			case 'welcome':
				$sanitized['viewed'] = true;
				$sanitized['viewed_at'] = time();
				break;

			case 'license':
				$sanitized['method'] = sanitize_text_field( $data['method'] ?? '' );
				$sanitized['completed_at'] = time();

				// License key (if provided).
				if ( ! empty( $data['license_key'] ) ) {
					$sanitized['license_key'] = sanitize_text_field( $data['license_key'] );
				}

				// Email (if starting trial).
				if ( ! empty( $data['email'] ) ) {
					$sanitized['email'] = sanitize_email( $data['email'] );
				}
				break;

			case 'basic-setup':
				$sanitized['site_name'] = sanitize_text_field( $data['site_name'] ?? '' );
				$sanitized['admin_email'] = sanitize_email( $data['admin_email'] ?? get_option( 'admin_email' ) );
				$sanitized['timezone'] = sanitize_text_field( $data['timezone'] ?? 'UTC' );
				$sanitized['completed_at'] = time();

				// Save basic settings to WordPress core options.
				if ( ! empty( $sanitized['site_name'] ) ) {
					update_option( 'blogname', $sanitized['site_name'] );
				}
				if ( ! empty( $sanitized['admin_email'] ) && is_email( $sanitized['admin_email'] ) ) {
					update_option( 'admin_email', $sanitized['admin_email'] );
				}
				if ( ! empty( $sanitized['timezone'] ) ) {
					update_option( 'timezone_string', $sanitized['timezone'] );
				}
				break;

			case 'finish':
				$sanitized['completed'] = true;
				$sanitized['completed_at'] = time();
				break;

			default:
				// Generic sanitization for unknown steps.
				foreach ( $data as $key => $value ) {
					if ( is_string( $value ) ) {
						$sanitized[ sanitize_key( $key ) ] = sanitize_text_field( $value );
					} elseif ( is_array( $value ) ) {
						$sanitized[ sanitize_key( $key ) ] = array_map( 'sanitize_text_field', $value );
					} elseif ( is_numeric( $value ) ) {
						$sanitized[ sanitize_key( $key ) ] = intval( $value );
					} elseif ( is_bool( $value ) ) {
						$sanitized[ sanitize_key( $key ) ] = $value;
					}
				}
				break;
		}

		return $sanitized;
	}
}