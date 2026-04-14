<?php
/**
 * Feature Control for SkillPulse LMS
 *
 * Manages feature availability based on license status and provides upgrade prompts
 *
 * @since   1.0.0
 * @package SkillPulse_LMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Feature Control Class
 *
 * Manages feature availability based on license status and provides upgrade prompts.
 *
 * @since 1.0.0
 */
class SkillPulse_LMS_Feature_Control {

	/**
	 * Class instance.
	 *
	 * @var null $instance
	 */
	private static $instance = null;

	/**
	 * License manager instance.
	 *
	 * @var SkillPulse_LMS_License_Manager
	 */
	private $license_manager;

	/**
	 * Get the instance of this class.
	 *
	 * @since 1.0.0
	 *
	 * @return SkillPulse_LMS_Feature_Control
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
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
		$this->license_manager = SkillPulse_LMS_License_Manager::get_instance();
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
		// Filter settings to disable premium features.
		add_filter( 'splms_settings_config', array( $this, 'filter_settings_config' ) );
		add_filter( 'splms_course_settings_config', array( $this, 'filter_course_settings_config' ) );
	}

	/**
	 * Filter settings configuration to disable premium features.
	 *
	 * @since 1.0.0
	 *
	 * @param array $config Settings configuration.
	 * @return array Filtered configuration.
	 */
	public function filter_settings_config( $config ) {
		foreach ( $config as &$section ) {
			if ( isset( $section['sections'] ) ) {
				foreach ( $section['sections'] as &$subsection ) {
					if ( isset( $subsection['fields'] ) ) {
						foreach ( $subsection['fields'] as &$field ) {
							$field = $this->filter_field_for_license( $field );
						}
					}
				}
			}
		}

		return $config;
	}

	/**
	 * Filter course settings configuration.
	 *
	 * @since 1.0.0
	 *
	 * @param array $config Course settings configuration.
	 * @return array Filtered configuration.
	 */
	public function filter_course_settings_config( $config ) {
		foreach ( $config as &$section ) {
			if ( isset( $section['fields'] ) ) {
				foreach ( $section['fields'] as &$field ) {
					$field = $this->filter_field_for_license( $field );
				}
			}
		}

		return $config;
	}

	/**
	 * Filter individual field for license restrictions.
	 *
	 * @since 1.0.0
	 *
	 * @param array $field Field configuration.
	 * @return array Filtered field configuration.
	 */
	private function filter_field_for_license( $field ) {
		// Check if field requires premium feature.
		// Premium features are identified by field IDs containing these keywords.
		$premium_features = array(
			'certificate',      // Certificate-related settings.
			'enable_paid',      // Paid courses feature.
			'paypal',           // PayPal payment gateway.
			'stripe',           // Stripe payment gateway.
			'razorpay',         // Razorpay payment gateway.
			'currency',         // Currency settings for payments.
		);

		$field_id   = isset( $field['id'] ) ? $field['id'] : '';
		$is_premium = false;

		foreach ( $premium_features as $feature ) {
			if ( false !== strpos( $field_id, $feature ) ) {
				$is_premium = true;
				break;
			}
		}

		// If premium field, check if feature is available.
		if ( $is_premium && ! $this->is_feature_available( $field_id ) ) {
			$field['disabled']    = true;
			$field['description'] = $this->get_field_upgrade_message();
		}

		return $field;
	}

	/**
	 * Get upgrade message for disabled fields.
	 *
	 * @since 1.0.0
	 *
	 * @return string Upgrade message HTML.
	 */
	private function get_field_upgrade_message() {
		$license_info     = $this->license_manager->get_license_info();
		$license_page_url = admin_url( 'admin.php?page=splms-license' );

		$status           = isset( $license_info['status'] ) ? $license_info['status'] : 'inactive';
		$domain_activated = isset( $license_info['domain_activated'] ) ? $license_info['domain_activated'] : false;

		// Check different license states and provide appropriate message.
		if ( empty( $license_info['license_key'] ) ) {
			// No license key entered.
			return sprintf(
				'%s <a href="%s">%s</a>',
				__( 'This feature requires an active subscription.', 'skillpulse-lms' ),
				esc_url( $license_page_url ),
				__( 'Activate License', 'skillpulse-lms' )
			);
		}

		if ( 'failed' === $status ) {
			// License check failed.
			$error = isset( $license_info['error'] ) ? $license_info['error'] : __( 'License validation failed', 'skillpulse-lms' );
			return sprintf(
				'%s <a href="%s">%s</a>',
				esc_html( $error ),
				esc_url( $license_page_url ),
				__( 'Check License', 'skillpulse-lms' )
			);
		}

		if ( 'cancelled' === $status ) {
			// License cancelled.
			return sprintf(
				'%s <a href="%s">%s</a>',
				__( 'Your subscription has been cancelled.', 'skillpulse-lms' ),
				esc_url( $license_page_url ),
				__( 'Contact Support', 'skillpulse-lms' )
			);
		}

		if ( 'expired' === $status ) {
			// License expired.
			return sprintf(
				'%s <a href="%s">%s</a>',
				__( 'Your subscription has expired. Renew to continue using this feature.', 'skillpulse-lms' ),
				esc_url( $license_page_url ),
				__( 'Renew Subscription', 'skillpulse-lms' )
			);
		}

		if ( ! $domain_activated ) {
			// License not activated for this domain.
			return sprintf(
				'%s <a href="%s">%s</a>',
				__( 'Please activate your license for this domain to use this feature.', 'skillpulse-lms' ),
				esc_url( $license_page_url ),
				__( 'Activate License', 'skillpulse-lms' )
			);
		}

		if ( ! in_array( $status, array( 'active', 'grace_period' ), true ) || ! $domain_activated ) {
			// License not valid.
			return sprintf(
				'%s <a href="%s">%s</a>',
				__( 'This feature requires a valid subscription.', 'skillpulse-lms' ),
				esc_url( $license_page_url ),
				__( 'Check License', 'skillpulse-lms' )
			);
		}

		// Default message.
		return sprintf(
			'%s <a href="%s">%s</a>',
			__( 'This feature requires an active subscription.', 'skillpulse-lms' ),
			esc_url( $license_page_url ),
			__( 'Upgrade Now', 'skillpulse-lms' )
		);
	}

	/**
	 * Check if feature is available.
	 *
	 * @since 1.0.0
	 *
	 * @param string $feature Feature name.
	 * @return bool True if feature is available.
	 */
	public function is_feature_available( $feature ) {
		// Use License Manager's method which checks both validity and activation.
		return $this->license_manager->is_feature_available( $feature );
	}
}
