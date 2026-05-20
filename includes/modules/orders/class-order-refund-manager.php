<?php
/**
 * Order Refund Manager
 *
 * Handles refund and cancellation processing for orders.
 *
 * @since      1.0.0
 * @subpackage Modules
 * @package    SkillPulse_LMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Order Refund Manager Class
 *
 * @since 1.0.0
 */
class SPLMS_Order_Refund_Manager {

	/**
	 * Class instance.
	 *
	 * @since 1.0.0
	 * @var SPLMS_Order_Refund_Manager|null
	 */
	private static $instance = null;

	/**
	 * Get the instance of this class.
	 *
	 * @since 1.0.0
	 * @return SPLMS_Order_Refund_Manager
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
	}

	/**
	 * Process refund for a completed order.
	 *
	 * @param int    $order_id Numeric order ID.
	 * @param float  $amount   Refund amount (null for full refund).
	 * @param string $reason   Refund reason.
	 *
	 * @since 1.0.0
	 * @return array|WP_Error Result array or WP_Error on failure.
	 */
	public function process_refund( $order_id, $amount = null, $reason = '' ) {
		$order_id = absint( $order_id );
		if ( ! $order_id ) {
			return new WP_Error( 'invalid_order_id', __( 'Invalid order ID.', 'skillpulse-lms' ) );
		}

		// Validate order can be refunded.
		$can_refund = $this->can_refund_order( $order_id );
		if ( is_wp_error( $can_refund ) ) {
			return $can_refund;
		}

		$orders_query = SPLMS_Orders_Query::get_instance();
		$order        = $orders_query->get_order_by_id( $order_id );

		if ( ! $order ) {
			return new WP_Error( 'order_not_found', __( 'Order not found.', 'skillpulse-lms' ) );
		}

		// Determine refund amount (full refund if not specified).
		$refund_amount = null !== $amount ? floatval( $amount ) : floatval( $order->amount );

		// Validate refund amount.
		if ( $refund_amount <= 0 || $refund_amount > floatval( $order->amount ) ) {
			return new WP_Error( 'invalid_refund_amount', __( 'Invalid refund amount.', 'skillpulse-lms' ) );
		}

		// Process gateway refund.
		$gateway_result = $this->process_gateway_refund( $order, $refund_amount );

		if ( is_wp_error( $gateway_result ) ) {
			return $gateway_result;
		}

		// Revoke access and update order status.
		$revoke_result = $this->revoke_access_and_update_status( $order_id, 'refunded', $reason, $refund_amount );

		if ( ! $revoke_result ) {
			return new WP_Error( 'refund_failed', __( 'Failed to update order status after refund.', 'skillpulse-lms' ) );
		}

		// Send refund notification email.
		$this->send_refund_notification( $order_id, $refund_amount, $reason );

		return array(
			'success'       => true,
			'order_id'      => $order_id,
			'refund_amount' => $refund_amount,
			'message'       => __( 'Refund processed successfully.', 'skillpulse-lms' ),
		);
	}

	/**
	 * Process cancellation for a pending order.
	 *
	 * @param int    $order_id Numeric order ID.
	 * @param string $reason   Cancellation reason.
	 *
	 * @since 1.0.0
	 * @return array|WP_Error Result array or WP_Error on failure.
	 */
	public function process_cancellation( $order_id, $reason = '' ) {
		$order_id = absint( $order_id );
		if ( ! $order_id ) {
			return new WP_Error( 'invalid_order_id', __( 'Invalid order ID.', 'skillpulse-lms' ) );
		}

		// Validate order can be cancelled.
		$can_cancel = $this->can_cancel_order( $order_id );
		if ( is_wp_error( $can_cancel ) ) {
			return $can_cancel;
		}

		$orders_query = SPLMS_Orders_Query::get_instance();

		// Update order status to cancelled (no gateway interaction needed for pending orders).
		$update_result = $orders_query->update_order_status( $order_id, 'cancelled', array() );

		if ( ! $update_result ) {
			return new WP_Error( 'cancellation_failed', __( 'Failed to cancel order.', 'skillpulse-lms' ) );
		}

		// Save cancellation reason and history.
		$orders_query->update_order_meta( $order_id, 'cancellation_reason', sanitize_text_field( $reason ) );
		$orders_query->update_order_meta( $order_id, 'cancelled_at', current_time( 'mysql' ) );

		// Send cancellation notification email.
		$this->send_cancellation_notification( $order_id, $reason );

		return array(
			'success'  => true,
			'order_id' => $order_id,
			'message'  => __( 'Order cancelled successfully.', 'skillpulse-lms' ),
		);
	}

	/**
	 * Validate if order can be refunded.
	 *
	 * @param int $order_id Numeric order ID.
	 *
	 * @since 1.0.0
	 * @return bool|WP_Error True if refundable, WP_Error otherwise.
	 */
	private function can_refund_order( $order_id ) {
		$orders_query = SPLMS_Orders_Query::get_instance();
		$order        = $orders_query->get_order_by_id( $order_id );

		if ( ! $order ) {
			return new WP_Error( 'order_not_found', __( 'Order not found.', 'skillpulse-lms' ) );
		}

		// Only completed orders can be refunded.
		if ( 'completed' !== $order->status ) {
			return new WP_Error( 'invalid_order_status', __( 'Only completed orders can be refunded.', 'skillpulse-lms' ) );
		}

		// Check if already refunded.
		if ( 'refunded' === $order->status ) {
			return new WP_Error( 'already_refunded', __( 'Order has already been refunded.', 'skillpulse-lms' ) );
		}

		return true;
	}

	/**
	 * Validate if order can be cancelled.
	 *
	 * @param int $order_id Numeric order ID.
	 *
	 * @since 1.0.0
	 * @return bool|WP_Error True if cancellable, WP_Error otherwise.
	 */
	private function can_cancel_order( $order_id ) {
		$orders_query = SPLMS_Orders_Query::get_instance();
		$order        = $orders_query->get_order_by_id( $order_id );

		if ( ! $order ) {
			return new WP_Error( 'order_not_found', __( 'Order not found.', 'skillpulse-lms' ) );
		}

		// Only pending orders can be cancelled.
		if ( 'pending' !== $order->status ) {
			return new WP_Error( 'invalid_order_status', __( 'Only pending orders can be cancelled.', 'skillpulse-lms' ) );
		}

		return true;
	}

	/**
	 * Process gateway refund.
	 *
	 * @param object $order  Order object.
	 * @param float  $amount Refund amount.
	 *
	 * @since 1.0.0
	 * @return array|WP_Error Gateway response or WP_Error.
	 */
	private function process_gateway_refund( $order, $amount ) {
		$orders_query   = SPLMS_Orders_Query::get_instance();
		$payment_method = $orders_query->get_order_meta( $order->id, 'payment_method' );

		if ( ! $payment_method ) {
			return new WP_Error( 'no_payment_method', __( 'Payment method not found for this order.', 'skillpulse-lms' ) );
		}

		// Get gateway instance.
		$gateway = null;
		switch ( $payment_method ) {
			case 'stripe':
				if ( class_exists( 'SPLMS_Stripe' ) ) {
					$gateway = SPLMS_Stripe::get_instance();
				}
				break;
			case 'paypal':
				if ( class_exists( 'SPLMS_PayPal' ) ) {
					$gateway = SPLMS_PayPal::get_instance();
				}
				break;
			case 'razorpay':
				if ( class_exists( 'SPLMS_Razorpay' ) ) {
					$gateway = SPLMS_Razorpay::get_instance();
				}
				break;
		}

		if ( ! $gateway ) {
			return new WP_Error( 'gateway_not_found', __( 'Payment gateway not available.', 'skillpulse-lms' ) );
		}

		// Check if gateway supports refunds.
		if ( ! method_exists( $gateway, 'process_refund' ) ) {
			return new WP_Error( 'refund_not_supported', __( 'This payment gateway does not support refunds.', 'skillpulse-lms' ) );
		}

		// Process refund via gateway.
		$result = $gateway->process_refund( $order->id, $amount );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		// Save refund details to order meta.
		$orders_query->update_order_meta( $order->id, 'refund_amount', $amount );
		$orders_query->update_order_meta( $order->id, 'refunded_at', current_time( 'mysql' ) );
		$orders_query->update_order_meta( $order->id, 'gateway_refund_response', $result );

		return $result;
	}

	/**
	 * Revoke course access and update order status.
	 *
	 * @param int    $order_id      Numeric order ID.
	 * @param string $status        New order status.
	 * @param string $reason        Reason for status change.
	 * @param float  $refund_amount Refund amount (for refunds only).
	 *
	 * @since 1.0.0
	 * @return bool Success status.
	 */
	private function revoke_access_and_update_status( $order_id, $status, $reason, $refund_amount = null ) {
		$orders_query = SPLMS_Orders_Query::get_instance();

		// Save refund/cancellation reason and timestamp before status update.
		if ( 'refunded' === $status ) {
			$orders_query->update_order_meta( $order_id, 'refund_reason', sanitize_text_field( $reason ) );
		} elseif ( 'cancelled' === $status ) {
			$orders_query->update_order_meta( $order_id, 'cancellation_reason', sanitize_text_field( $reason ) );
		}

		// Update order status (this will fire hooks and automatically handle access control via hooks).
		$update_data = array();
		if ( $refund_amount ) {
			$orders_query->update_order_meta( $order_id, 'refund_amount', $refund_amount );
		}

		$result = $orders_query->update_order_status( $order_id, $status, $update_data );

		if ( $result ) {
			// Save timestamp after successful status update.
			if ( 'refunded' === $status ) {
				$orders_query->update_order_meta( $order_id, 'refunded_at', current_time( 'mysql' ) );
			} elseif ( 'cancelled' === $status ) {
				$orders_query->update_order_meta( $order_id, 'cancelled_at', current_time( 'mysql' ) );
			}
		}

		return $result;
	}

	/**
	 * Send refund notification email.
	 *
	 * @param int    $order_id Numeric order ID.
	 * @param float  $amount   Refund amount.
	 * @param string $reason   Refund reason.
	 *
	 * @since 1.0.0
	 * @return bool Success status.
	 */
	private function send_refund_notification( $order_id, $amount, $reason ) {
		$orders_query = SPLMS_Orders_Query::get_instance();
		$order        = $orders_query->get_order_by_id( $order_id );

		if ( ! $order ) {
			return false;
		}

		// Check if notification dispatcher is available.
		if ( ! class_exists( 'SPLMS_Notification_Dispatcher' ) ) {
			return false;
		}

		$dispatcher = SPLMS_Notification_Dispatcher::get_instance();

		// Get user email.
		$user = get_userdata( $order->user_id );
		if ( ! $user || ! $user->user_email ) {
			return false;
		}

		// Get course title.
		$course       = get_post( $order->course_id );
		$course_title = $course ? $course->post_title : __( 'Course', 'skillpulse-lms' );

		// Format amounts.
		$currency_symbol         = splms_get_currency_symbol( $order->currency );
		$refund_amount_formatted = $currency_symbol . number_format( $amount, 2 );
		$order_amount_formatted  = $currency_symbol . number_format( floatval( $order->amount ), 2 );

		// Send refund notification.
		$dispatcher->send_notification(
			$user->user_email,
			'order_refunded',
			array(
				'user_name'     => $user->display_name,
				'site_name'     => get_bloginfo( 'name' ),
				'order_id'      => $order_id,
				'course_title'  => $course_title,
				'refund_amount' => $refund_amount_formatted,
				'order_amount'  => $order_amount_formatted,
				'reason'        => $reason,
			),
			$order->user_id
		);

		return true;
	}

	/**
	 * Send cancellation notification email.
	 *
	 * @param int    $order_id Numeric order ID.
	 * @param string $reason   Cancellation reason.
	 *
	 * @since 1.0.0
	 * @return bool Success status.
	 */
	private function send_cancellation_notification( $order_id, $reason ) {
		$orders_query = SPLMS_Orders_Query::get_instance();
		$order        = $orders_query->get_order_by_id( $order_id );

		if ( ! $order ) {
			return false;
		}

		// Check if notification dispatcher is available.
		if ( ! class_exists( 'SPLMS_Notification_Dispatcher' ) ) {
			return false;
		}

		$dispatcher = SPLMS_Notification_Dispatcher::get_instance();

		// Get user email.
		$user = get_userdata( $order->user_id );
		if ( ! $user || ! $user->user_email ) {
			return false;
		}

		// Get course title.
		$course       = get_post( $order->course_id );
		$course_title = $course ? $course->post_title : __( 'Course', 'skillpulse-lms' );

		// Format amount.
		$currency_symbol        = splms_get_currency_symbol( $order->currency );
		$order_amount_formatted = $currency_symbol . number_format( floatval( $order->amount ), 2 );

		// Send cancellation notification.
		$dispatcher->send_notification(
			$user->user_email,
			'order_cancelled',
			array(
				'user_name'    => $user->display_name,
				'site_name'    => get_bloginfo( 'name' ),
				'site_url'     => home_url(),
				'order_id'     => $order_id,
				'course_title' => $course_title,
				'order_amount' => $order_amount_formatted,
				'reason'       => $reason,
			),
			$order->user_id
		);

		return true;
	}
}
