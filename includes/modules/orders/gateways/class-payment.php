<?php
/**
 * Payment Integration
 *
 * @since      1.0.0
 * @subpackage Modules\Orders\Gateways
 * @package SPLMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Payment Integration Class
 *
 * Handles payment processing for course purchases.
 *
 * @since 1.0.0
 */
class SPLMS_Payment {

	/**
	 * Instance of this class.
	 *
	 * @since 1.0.0
	 * @var SPLMS_Payment
	 */
	private static $instance = null;

	/**
	 * Available payment gateways.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private $gateways = array();

	/**
	 * Get instance of this class.
	 *
	 * @since 1.0.0
	 * @return SPLMS_Payment
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
		$this->init();
	}

	/**
	 * Initialize the payment system.
	 *
	 * @since 1.0.0
	 */
	private function init() {
		add_action( 'init', array( $this, 'register_gateways' ) );
		$this->setup_hooks();
	}

	/**
	 * Register available payment gateways.
	 *
	 * @since 1.0.0
	 */
	public function register_gateways() {
		// If paid courses are disabled globally, don't register any gateways.
		if ( ! splms_is_paid_courses_enabled() ) {
			$this->gateways = array();

			return;
		}

		// Register PayPal gateway.
		$this->gateways['paypal'] = array(
			'name'        => __( 'PayPal', 'skillpulse-lms' ),
			'class'       => 'SPLMS_PayPal',
			'enabled'     => splms_get_setting( 'enable_paypal', false ),
			'configured'  => $this->is_paypal_configured(),
			'icon'        => 'paypal',
			'description' => __( 'Pay securely with PayPal', 'skillpulse-lms' ),
		);

		// Register Stripe gateway.
		$this->gateways['stripe'] = array(
			'name'        => __( 'Stripe', 'skillpulse-lms' ),
			'class'       => 'SPLMS_Stripe',
			'enabled'     => splms_get_setting( 'enable_stripe', false ),
			'configured'  => $this->is_stripe_configured(),
			'icon'        => 'stripe',
			'description' => __( 'Pay with credit card via Stripe', 'skillpulse-lms' ),
		);

		// Register Razorpay gateway.
		$this->gateways['razorpay'] = array(
			'name'        => __( 'Razorpay', 'skillpulse-lms' ),
			'class'       => 'SPLMS_Razorpay',
			'enabled'     => splms_get_setting( 'enable_razorpay', false ),
			'configured'  => $this->is_razorpay_configured(),
			'icon'        => 'razorpay',
			'description' => __( 'Pay securely with Razorpay', 'skillpulse-lms' ),
		);

		// Allow other plugins to register gateways.
		$this->gateways = apply_filters( 'splms_payment_gateways', $this->gateways );
	}

	/**
	 * Setup WordPress hooks and filters.
	 *
	 * @since 1.0.0
	 */
	private function setup_hooks() {
		// All deprecated AJAX handlers removed - gateways handle payment processing directly.
	}


	/**
	 * Get all registered gateways.
	 *
	 * @since 1.0.0
	 * @return array Registered gateways.
	 */
	public function get_gateways() {
		return $this->gateways;
	}

	/**
	 * Get available payment methods for a specific course.
	 *
	 * @param int $course_id Course ID (unused).
	 *
	 * @since 1.0.0
	 * @return array Available payment methods.
	 */
	public function get_available_payment_methods( $course_id ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found -- Parameter kept for interface compatibility.
		$available_methods = array();

		foreach ( $this->gateways as $gateway_id => $gateway ) {
			if ( $gateway['enabled'] && $gateway['configured'] ) {
				$available_methods[ $gateway_id ] = $gateway;
			}
		}

		return $available_methods;
	}

	/**
	 * Check if PayPal is properly configured.
	 *
	 * @since 1.0.0
	 * @return bool True if PayPal is configured.
	 */
	private function is_paypal_configured() {
		$client_id     = splms_get_setting( 'paypal_client_id' );
		$client_secret = splms_get_setting( 'paypal_client_secret' );

		return ! empty( $client_id ) && ! empty( $client_secret );
	}

	/**
	 * Check if Stripe is properly configured.
	 *
	 * @since 1.0.0
	 * @return bool True if Stripe is configured.
	 */
	private function is_stripe_configured() {
		$test_mode = splms_get_setting( 'stripe_test_mode', true );

		if ( $test_mode ) {
			$publishable_key = splms_get_setting( 'stripe_test_publishable_key' );
			$secret_key      = splms_get_setting( 'stripe_test_secret_key' );
		} else {
			$publishable_key = splms_get_setting( 'stripe_live_publishable_key' );
			$secret_key      = splms_get_setting( 'stripe_live_secret_key' );
		}

		return ! empty( $publishable_key ) && ! empty( $secret_key );
	}

	/**
	 * Check if Razorpay is properly configured.
	 *
	 * @since 1.0.0
	 * @return bool True if Razorpay is configured.
	 */
	private function is_razorpay_configured() {
		$sandbox_mode = splms_get_setting( 'razorpay_sandbox_mode', true );

		if ( $sandbox_mode ) {
			$key_id     = splms_get_setting( 'razorpay_test_key_id' );
			$key_secret = splms_get_setting( 'razorpay_test_key_secret' );
		} else {
			$key_id     = splms_get_setting( 'razorpay_live_key_id' );
			$key_secret = splms_get_setting( 'razorpay_live_key_secret' );
		}

		return ! empty( $key_id ) && ! empty( $key_secret );
	}

	/**
	 * Handle payment success callbacks.
	 *
	 * @since 1.0.0
	 * @return bool True if payment was processed, false otherwise.
	 */
	public function handle_payment_success() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Payment callback, nonce handled by gateway.
		$splms_payment   = isset( $_GET['splms_payment'] ) ? sanitize_text_field( wp_unslash( $_GET['splms_payment'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only parameter.
		$payment_id      = isset( $_GET['payment_id'] ) ? sanitize_text_field( wp_unslash( $_GET['payment_id'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only parameter.
		$payment_intent  = isset( $_GET['payment_intent'] ) ? sanitize_text_field( wp_unslash( $_GET['payment_intent'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only parameter.
		$redirect_status = isset( $_GET['redirect_status'] ) ? sanitize_text_field( wp_unslash( $_GET['redirect_status'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only parameter.
		$course_id       = isset( $_GET['course_id'] ) ? intval( wp_unslash( $_GET['course_id'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only parameter.

		// Handle Stripe payment success.
		if ( 'success' === $splms_payment && ! empty( $payment_id ) && ! empty( $payment_intent ) && 'succeeded' === $redirect_status ) {

			// Process Stripe payment success.
			if ( class_exists( 'SPLMS_Stripe' ) ) {
				$stripe = SPLMS_Stripe::get_instance();
				$result = $stripe->process_successful_payment_from_callback( $payment_intent, $course_id );

				if ( $result['success'] ) {
					// Redirect to success page.
					wp_safe_redirect( $result['data']['redirect_url'] );
					exit;
				} else {
					// Redirect to purchase with error.
					$error_message = isset( $result['data']['message'] ) ? $result['data']['message'] : __( 'Payment processing failed.', 'skillpulse-lms' );
					$error_url     = add_query_arg(
						array(
							'course_id'     => $course_id,
							'splms_payment' => 'error',
							'message'       => rawurlencode( $error_message ),
						),
						home_url( '/purchase/' )
					);
					wp_safe_redirect( $error_url );
					exit;
				}
			}
		}

		return false; // No payment success to handle.
	}
}
