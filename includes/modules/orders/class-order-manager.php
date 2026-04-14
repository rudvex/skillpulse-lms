<?php
/**
 * Order Manager
 *
 * Centralized order creation and management for all payment gateways.
 *
 * @since      1.0.0
 * @subpackage Modules
 * @package    SkillPulse_LMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Order Manager Class
 *
 * Provides standardized order creation and management across all payment gateways.
 *
 * @since 1.0.0
 */
class SkillPulse_LMS_Order_Manager {

	/**
	 * Class instance.
	 *
	 * @since 1.0.0
	 * @var SkillPulse_LMS_Order_Manager|null
	 */
	private static $instance = null;

	/**
	 * Get the instance of this class.
	 *
	 * @since 1.0.0
	 * @return SkillPulse_LMS_Order_Manager
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
		// Hook admin notices for test mode warnings.
		add_action( 'admin_notices', array( $this, 'display_test_mode_warning' ) );
	}

	/**
	 * Create order from payment gateway.
	 *
	 * @param string $gateway Gateway name (stripe, paypal, razorpay).
	 * @param array  $data    Order data (user_id, course_id, amount).
	 *
	 * @since 1.0.0
	 * @return int|false Numeric order ID on success, false on failure.
	 */
	public function create_order_from_payment( $gateway, $data ) {
		// Check if paid courses are enabled globally.
		if ( ! splms_is_paid_courses_enabled() ) {
			return false;
		}

		$defaults = array(
			'user_id'   => 0,
			'course_id' => 0,
			'amount'    => 0.00,
		);

		$data = wp_parse_args( $data, $defaults );

		// Validate required fields.
		if ( ! $data['user_id'] || ! $data['course_id'] || $data['amount'] <= 0 ) {
			return false;
		}

		global $wpdb;
		$orders_query = SkillPulse_LMS_Orders_Query::get_instance();

		// Use database transaction with row locking to prevent race condition.
		// This ensures only one order is created even if multiple requests come simultaneously.
		$wpdb->query( 'START TRANSACTION' );

		try {
			// Lock rows for this user+course to prevent concurrent order creation.
			// FOR UPDATE prevents other transactions from reading or modifying these rows.
			$table_name = $wpdb->prefix . 'splms_orders';
			$sql        = "SELECT id FROM {$table_name}
				WHERE user_id = %d AND course_id = %d
				AND status IN ('pending', 'processing')
				ORDER BY id DESC
				FOR UPDATE";

			$existing_id = $wpdb->get_var(
				// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- SQL is safely constructed and prepared above.
				$wpdb->prepare( $sql, $data['user_id'], $data['course_id'] )
			);

			if ( $existing_id ) {
				// Existing order found - reuse it.
				$existing_order = $orders_query->get_order_by_id( $existing_id );

				// Update order amount if it has changed.
				if ( $existing_order && abs( floatval( $existing_order->amount ) - floatval( $data['amount'] ) ) > 0.01 ) {
					$orders_query->update_order_status(
						$existing_id,
						$existing_order->status,
						array( 'amount' => floatval( $data['amount'] ) )
					);
				}

				// Ensure payment method meta matches the current gateway.
				$meta_updates = array(
					'payment_method' => $gateway,
					'gateway'        => $gateway,
				);
				$orders_query->add_order_meta( $existing_id, $meta_updates );

				$wpdb->query( 'COMMIT' );

				return $existing_id;
			}

			// No existing order - create new one.
			// Prepare standardized order data.
			$order_data = $this->get_standardized_order_data(
				$data['user_id'],
				$data['course_id'],
				$data['amount'],
				$gateway
			);

			// Prepare standardized meta data.
			$meta_data = $this->get_standardized_meta_data( $gateway );

			// Capture course snapshot at time of purchase (important: course may be deleted later).
			$course_snapshot = $this->get_course_snapshot( $data['course_id'] );
			if ( ! empty( $course_snapshot ) ) {
				$meta_data['course_snapshot'] = $course_snapshot;
			}

			// Capture customer snapshot at time of purchase (important: customer info may change later).
			$customer_snapshot = $this->get_customer_snapshot( $data['user_id'] );
			if ( ! empty( $customer_snapshot ) ) {
				$meta_data['customer_snapshot'] = $customer_snapshot;
			}

			// Create new order.
			$order_id = $orders_query->create_order( $order_data, $meta_data );

			if ( ! $order_id ) {
				$wpdb->query( 'ROLLBACK' );

				return false;
			}

			$wpdb->query( 'COMMIT' );

			return $order_id;
		} catch ( Exception $e ) {
			$wpdb->query( 'ROLLBACK' );
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( 'Order creation transaction failed: ' . $e->getMessage() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			}

			return false;
		}
	}

	/**
	 * Create order with items (for section-based purchases).
	 *
	 * @param string $gateway Gateway name (stripe, paypal, razorpay).
	 * @param array  $data    Order data (user_id, course_id, amount, items).
	 *
	 * @since 1.0.0
	 * @return int|false Numeric order ID on success, false on failure.
	 */
	public function create_order_with_items( $gateway, $data ) {
		// Check if paid courses are enabled globally.
		if ( ! splms_is_paid_courses_enabled() ) {
			return false;
		}

		$defaults = array(
			'user_id'   => 0,
			'course_id' => 0,
			'amount'    => 0.00,
			'items'     => array(),
		);

		$data = wp_parse_args( $data, $defaults );

		// Validate required fields.
		if ( ! $data['user_id'] || ! $data['course_id'] || $data['amount'] <= 0 || empty( $data['items'] ) ) {
			return false;
		}

		global $wpdb;
		$orders_query = SkillPulse_LMS_Orders_Query::get_instance();

		// Use database transaction.
		$wpdb->query( 'START TRANSACTION' );

		try {
			// Prepare standardized order data.
			$order_data = $this->get_standardized_order_data(
				$data['user_id'],
				$data['course_id'],
				$data['amount'],
				$gateway
			);

			// Prepare standardized meta data.
			$meta_data = $this->get_standardized_meta_data( $gateway );

			// Capture customer snapshot.
			$customer_snapshot = $this->get_customer_snapshot( $data['user_id'] );
			if ( ! empty( $customer_snapshot ) ) {
				$meta_data['customer_snapshot'] = $customer_snapshot;
			}

			// Mark this as a section-based purchase.
			$meta_data['purchase_type'] = 'section';

			// Create new order.
			$order_id = $orders_query->create_order( $order_data, $meta_data );

			if ( ! $order_id ) {
				$wpdb->query( 'ROLLBACK' );

				return false;
			}

			// Add items to order_items table.
			$items_table = $wpdb->prefix . 'splms_order_items';

			foreach ( $data['items'] as $item ) {
				$item_defaults = array(
					'item_type'   => 'section',
					'item_id'     => 0,
					'item_name'   => '',
					'unit_price'  => 0.00,
					'total_price' => 0.00,
				);

				$item = wp_parse_args( $item, $item_defaults );

				$insert_result = $wpdb->insert(
					$items_table,
					array(
						'order_id'    => $order_id,
						'item_type'   => $item['item_type'],
						'item_id'     => absint( $item['item_id'] ),
						'item_name'   => sanitize_text_field( $item['item_name'] ),
						'unit_price'  => floatval( $item['unit_price'] ),
						'total_price' => floatval( $item['total_price'] ),
						'created_at'  => current_time( 'mysql' ),
					),
					array( '%s', '%s', '%d', '%s', '%f', '%f', '%s' )
				);

				if ( ! $insert_result ) {
					$wpdb->query( 'ROLLBACK' );

					return false;
				}
			}

			$wpdb->query( 'COMMIT' );

			return $order_id;
		} catch ( Exception $e ) {
			$wpdb->query( 'ROLLBACK' );
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( 'Order creation with items transaction failed: ' . $e->getMessage() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			}

			return false;
		}
	}

	/**
	 * Update order status and gateway data.
	 *
	 * @param int    $order_id     Numeric order ID.
	 * @param string $status       New status (pending, completed, failed, refunded).
	 * @param array  $gateway_data Gateway-specific data (transaction_id, etc.).
	 *
	 * @since 1.0.0
	 * @return bool Success status.
	 */
	public function update_order_status( $order_id, $status, $gateway_data = array() ) {
		$orders_query = SkillPulse_LMS_Orders_Query::get_instance();

		// Update order status (this will fire hooks automatically).
		$result = $orders_query->update_order_status( $order_id, $status, array() );

		if ( $result && ! empty( $gateway_data ) ) {
			// Update gateway-specific meta data.
			foreach ( $gateway_data as $key => $value ) {
				$orders_query->update_order_meta( $order_id, $key, $value );
			}
		}

		return $result;
	}

	/**
	 * Complete order after successful payment.
	 *
	 * @param int   $order_id     Numeric order ID.
	 * @param array $gateway_data Gateway response data.
	 *
	 * @since 1.0.0
	 * @return bool Success status.
	 */
	public function complete_order( $order_id, $gateway_data = array() ) {
		$orders_query = SkillPulse_LMS_Orders_Query::get_instance();

		// Update order status to completed (this will fire hooks and grant access automatically).
		$result = $orders_query->update_order_status( $order_id, 'completed', array() );

		if ( $result && ! empty( $gateway_data ) ) {
			// Update gateway-specific meta data.
			$meta_updates = array();
			if ( isset( $gateway_data['gateway_order_id'] ) ) {
				$meta_updates['gateway_order_id'] = $gateway_data['gateway_order_id'];
			}
			if ( isset( $gateway_data['gateway_transaction_id'] ) ) {
				$meta_updates['gateway_transaction_id'] = $gateway_data['gateway_transaction_id'];
			}
			if ( isset( $gateway_data['gateway_response'] ) ) {
				$meta_updates['gateway_response'] = $gateway_data['gateway_response'];
			}
			if ( isset( $gateway_data['gateway_capture_id'] ) ) {
				$meta_updates['gateway_capture_id'] = $gateway_data['gateway_capture_id'];
			}
			if ( isset( $gateway_data['gateway_payment_id'] ) ) {
				$meta_updates['gateway_payment_id'] = $gateway_data['gateway_payment_id'];
			}
			if ( isset( $gateway_data['gateway_signature'] ) ) {
				$meta_updates['gateway_signature'] = $gateway_data['gateway_signature'];
			}
			if ( isset( $gateway_data['capture_status'] ) ) {
				$meta_updates['capture_status'] = $gateway_data['capture_status'];
			}

			// Update all meta fields (add_order_meta supports array of meta data).
			if ( ! empty( $meta_updates ) ) {
				$orders_query->add_order_meta( $order_id, $meta_updates );
			}
		}

		return $result;
	}

	/**
	 * Mark order as failed.
	 *
	 * @param int    $order_id Numeric order ID.
	 * @param string $reason   Failure reason.
	 *
	 * @since 1.0.0
	 * @return bool Success status.
	 */
	public function fail_order( $order_id, $reason = '' ) {
		$orders_query = SkillPulse_LMS_Orders_Query::get_instance();

		// Update order status to failed.
		$result = $orders_query->update_order_status( $order_id, 'failed', array() );

		if ( $result && $reason ) {
			// Save failure reason.
			$orders_query->update_order_meta( $order_id, 'failure_reason', sanitize_text_field( $reason ) );
		}

		return $result;
	}

	/**
	 * Get standardized order data structure.
	 *
	 * @param int    $user_id   User ID.
	 * @param int    $course_id Course ID.
	 * @param float  $amount    Order amount.
	 * @param string $gateway   Gateway name.
	 *
	 * @since 1.0.0
	 * @return array Standardized order data.
	 */
	private function get_standardized_order_data(
		$user_id,
		$course_id,
		$amount,
		$gateway
	) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- $gateway may be used in future implementations.
		return array(
			'user_id'   => absint( $user_id ),
			'course_id' => absint( $course_id ),
			'amount'    => floatval( $amount ),
			'currency'  => splms_get_setting( 'currency', 'USD' ),
			'status'    => 'pending',
		);
	}

	/**
	 * Get course snapshot data at time of purchase.
	 *
	 * @param int $course_id Course ID.
	 *
	 * @since 1.0.0
	 * @return array Course snapshot data.
	 */
	private function get_course_snapshot( $course_id ) {
		$course = get_post( $course_id );

		// Validate course exists and is not trashed.
		if ( ! $course || SPLMS_POST_TYPES['course'] !== $course->post_type || 'trash' === $course->post_status ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( sprintf( 'Order snapshot: Course %d not available for snapshot', $course_id ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			}

			return array();
		}

		// Get course pricing info.
		$course_info = splms_get_course_access_info( $course_id );

		// Get course meta.
		$course_meta = get_post_meta( $course_id );

		// Build snapshot.
		$snapshot = array(
			'course_id'      => $course_id,
			'course_title'   => $course->post_title,
			'course_slug'    => $course->post_name,
			'course_excerpt' => $course->post_excerpt,
			'course_price'   => $course_info['course_price'] ?? 0,
			'final_price'    => $course_info['final_price'] ?? $course_info['course_price'] ?? 0,
			'currency'       => splms_get_setting( 'currency', 'USD' ),
			'snapshot_date'  => current_time( 'mysql' ),
		);

		// Add course thumbnail if available.
		$thumbnail_id = get_post_thumbnail_id( $course_id );
		if ( $thumbnail_id ) {
			$snapshot['course_thumbnail_url'] = wp_get_attachment_image_url( $thumbnail_id, 'full' );
		}

		// Add course categories and tags.
		$categories = wp_get_post_terms( $course_id, SPLMS_TAXONOMIES['course_category'], array( 'fields' => 'names' ) );
		if ( ! is_wp_error( $categories ) && ! empty( $categories ) ) {
			$snapshot['course_categories'] = $categories;
		}

		$tags = wp_get_post_terms( $course_id, SPLMS_TAXONOMIES['course_tag'], array( 'fields' => 'names' ) );
		if ( ! is_wp_error( $tags ) && ! empty( $tags ) ) {
			$snapshot['course_tags'] = $tags;
		}

		// Validate required fields before returning snapshot.
		$final_price = isset( $snapshot['final_price'] ) ? floatval( $snapshot['final_price'] ) : 0;
		if ( empty( $snapshot['course_title'] ) || $final_price <= 0 ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
					sprintf(
						'Order snapshot: Incomplete course snapshot for course %d - missing title or price',
						$course_id
					)
				);
			}

			return array();
		}

		return $snapshot;
	}

	/**
	 * Get customer snapshot data at time of purchase.
	 *
	 * @param int $user_id User ID.
	 *
	 * @since 1.0.0
	 * @return array Customer snapshot data.
	 */
	private function get_customer_snapshot( $user_id ) {
		$user = get_userdata( $user_id );

		if ( ! $user ) {
			return array();
		}

		// Get user meta.
		$first_name      = get_user_meta( $user_id, 'first_name', true );
		$last_name       = get_user_meta( $user_id, 'last_name', true );
		$billing_address = get_user_meta( $user_id, 'billing_address', true );

		// Build snapshot.
		$first_name_value      = ! empty( $first_name ) ? $first_name : '';
		$last_name_value       = ! empty( $last_name ) ? $last_name : '';
		$full_name_value       = trim( $first_name_value . ' ' . $last_name_value );
		$full_name_value       = ! empty( $full_name_value ) ? $full_name_value : $user->display_name;
		$billing_address_value = ! empty( $billing_address ) ? $billing_address : '';

		$snapshot = array(
			'user_id'         => $user_id,
			'username'        => $user->user_login,
			'email'           => $user->user_email,
			'display_name'    => $user->display_name,
			'first_name'      => $first_name_value,
			'last_name'       => $last_name_value,
			'full_name'       => $full_name_value,
			'billing_address' => $billing_address_value,
			'snapshot_date'   => current_time( 'mysql' ),
		);

		// Add additional user meta if available.
		$phone = get_user_meta( $user_id, 'billing_phone', true );
		if ( $phone ) {
			$snapshot['phone'] = $phone;
		}

		$company = get_user_meta( $user_id, 'billing_company', true );
		if ( $company ) {
			$snapshot['company'] = $company;
		}

		return $snapshot;
	}

	/**
	 * Get standardized meta data structure.
	 *
	 * @param string $gateway Gateway name.
	 *
	 * @since 1.0.0
	 * @return array Standardized meta data.
	 */
	private function get_standardized_meta_data( $gateway ) {
		$meta_data = array(
			'payment_method' => $gateway,
			'gateway'        => $gateway,
		);

		// Track which environment/mode was used when the payment was created so that
		// we can later see if it was a test/sandbox or live payment.
		$environment = 'live';

		if ( function_exists( 'splms_get_setting' ) ) {
			switch ( $gateway ) {
				case 'paypal':
					// PayPal uses sandbox vs live.
					$environment = splms_get_setting( 'paypal_sandbox_mode', true ) ? 'sandbox' : 'live';
					break;
				case 'stripe':
					// Stripe uses test vs live.
					$environment = splms_get_setting( 'stripe_test_mode', true ) ? 'test' : 'live';
					break;
				case 'razorpay':
					// Razorpay uses sandbox vs live.
					$environment = splms_get_setting( 'razorpay_sandbox_mode', true ) ? 'sandbox' : 'live';
					break;
				default:
					$environment = 'live';
					break;
			}
		}

		$meta_data['payment_environment'] = $environment;

		// Add IP address and user agent (standardized across all gateways).
		$ip_address = $this->get_client_ip();
		if ( $ip_address ) {
			$meta_data['ip_address'] = $ip_address;
		}

		if ( isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
			$meta_data['user_agent'] = sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) );
		}

		return $meta_data;
	}

	/**
	 * Get client IP address.
	 *
	 * @since 1.0.0
	 * @return string|false IP address or false on failure.
	 */
	private function get_client_ip() {
		$ip_keys = array(
			'HTTP_CF_CONNECTING_IP', // Cloudflare.
			'HTTP_CLIENT_IP',
			'HTTP_X_FORWARDED_FOR',
			'HTTP_X_FORWARDED',
			'HTTP_X_CLUSTER_CLIENT_IP',
			'HTTP_FORWARDED_FOR',
			'HTTP_FORWARDED',
			'REMOTE_ADDR',
		);

		foreach ( $ip_keys as $key ) {
			if ( array_key_exists( $key, $_SERVER ) === true ) {
				// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Will sanitize IP address.
				$server_value = wp_unslash( $_SERVER[ $key ] );
				$server_value = sanitize_text_field( $server_value );
				foreach ( explode( ',', $server_value ) as $ip ) {
					$ip = trim( $ip );
					if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) !== false ) {
						return $ip;
					}
				}
			}
		}

		// Fallback to REMOTE_ADDR.
		return isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : false;
	}

	/**
	 * Display test mode warning in admin.
	 *
	 * Shows a warning notice when any payment gateway is in test/sandbox mode.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function display_test_mode_warning() {
		// Only show on admin pages.
		if ( ! is_admin() ) {
			return;
		}

		$test_gateways = array();

		// Check each gateway's test mode setting.
		if ( splms_get_setting( 'paypal_sandbox_mode', false ) ) {
			$test_gateways[] = 'PayPal';
		}
		if ( splms_get_setting( 'stripe_test_mode', false ) ) {
			$test_gateways[] = 'Stripe';
		}
		if ( splms_get_setting( 'razorpay_sandbox_mode', false ) ) {
			$test_gateways[] = 'Razorpay';
		}

		// Display warning if any gateways are in test mode.
		if ( ! empty( $test_gateways ) ) {
			?>
			<div class="notice notice-warning" style="border-left: 4px solid #ffba00;">
				<p>
					<strong><?php esc_html_e( '⚠️ TEST MODE ACTIVE', 'skillpulse-lms' ); ?></strong><br>
					<?php
					/* translators: %s: Comma-separated list of gateway names */
					printf(
						/* translators: %s: Comma-separated list of gateway names */
						esc_html__( 'Payment gateways in test mode: %s. Real payments will not be processed.', 'skillpulse-lms' ),
						esc_html( implode( ', ', $test_gateways ) )
					);
					?>
				</p>
			</div>
			<?php
		}
	}
}
