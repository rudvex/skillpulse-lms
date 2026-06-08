<?php
/**
 * Orders Module
 *
 * Handles Orders-related functionality
 *
 * @since      1.0.0
 * @subpackage Orders
 * @package SPLMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Orders Class
 *
 * @since 1.0.0
 */
class SPLMS_Orders {

	/**
	 * Class instance.
	 *
	 * @var SPLMS_Orders|null $instance
	 */
	private static $instance;

	/**
	 * Get the instance of this class.
	 *
	 * @since 1.0.0
	 *
	 * @return SPLMS_Orders Class instance.
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
	 *
	 * @return void
	 */
	private function __construct() {
		$this->setup_globals();
		$this->load_classes();
		$this->setup_actions();
		$this->setup_filters();
	}

	/**
	 * Setup Globals.
	 *
	 * @since 1.0.0
	 */
	protected function setup_globals() {
		$files = array(
			'includes/modules/orders/class-orders-query',
			'includes/modules/orders/class-order-status-manager',
			'includes/modules/orders/class-order-access-control',
			'includes/modules/orders/class-order-manager',
			'includes/modules/orders/class-order-refund-manager',
			'includes/modules/orders/class-purchase-token',
			'includes/modules/orders/gateways/class-payment',
		);

		foreach ( $files as $file ) {
			if ( file_exists( SPLMS_DIR_PATH . $file . '.php' ) ) {
				require_once SPLMS_DIR_PATH . $file . '.php';
			}
		}
	}

	/**
	 * Initiate the required classes.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	protected function load_classes() {
		SPLMS_Purchase_Token::get_instance();
		SPLMS_Payment::get_instance();
	}

	/**
	 * Action hooks.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	protected function setup_actions() {
		// Save billing information.
		add_action( 'wp_ajax_splms_save_billing_info', array( $this, 'save_billing_info' ) );
	}

	/**
	 * Save billing information via AJAX.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function save_billing_info() {
		// Verify nonce.
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'splms_nonce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid security token.', 'skillpulse-lms' ) ) );
		}

		// Check if user is logged in.
		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			wp_send_json_error( array( 'message' => __( 'User not logged in.', 'skillpulse-lms' ) ) );
		}

		// Get billing data.
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized below.
		$billing_data = isset( $_POST['billing_data'] ) ? wp_unslash( $_POST['billing_data'] ) : array();

		if ( empty( $billing_data ) ) {
			wp_send_json_error( array( 'message' => __( 'No billing data provided.', 'skillpulse-lms' ) ) );
		}

		// Sanitize and save billing data.
		$fields = array(
			'first_name' => 'first_name',
			'last_name'  => 'last_name',
			'phone'      => 'billing_phone',
			'address_1'  => 'billing_address_1',
			'address_2'  => 'billing_address_2',
			'city'       => 'billing_city',
			'state'      => 'billing_state',
			'postcode'   => 'billing_postcode',
			'country'    => 'billing_country',
		);

		foreach ( $fields as $key => $meta_key ) {
			if ( isset( $billing_data[ $key ] ) ) {
				$value = sanitize_text_field( $billing_data[ $key ] );
				update_user_meta( $user_id, $meta_key, $value );
			}
		}

		// Update email if provided.
		if ( isset( $billing_data['email'] ) && is_email( $billing_data['email'] ) ) {
			wp_update_user(
				array(
					'ID'         => $user_id,
					'user_email' => sanitize_email( $billing_data['email'] ),
				)
			);
		}

		wp_send_json_success( array( 'message' => __( 'Billing information saved successfully.', 'skillpulse-lms' ) ) );
	}

	/**
	 * Define all filter hooks.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	protected function setup_filters() {
	}
}
