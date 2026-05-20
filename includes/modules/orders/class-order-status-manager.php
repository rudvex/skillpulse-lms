<?php
/**
 * Order Status Management System
 *
 * @package SkillPulse_LMS
 * @subpackage Modules
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Order Status Manager Class
 *
 * Manages order statuses, transitions, and access control.
 *
 * @since 1.0.0
 */
class SPLMS_Order_Status_Manager {

	/**
	 * Order statuses
	 */
	const STATUS_PENDING    = 'pending';
	const STATUS_COMPLETED  = 'completed';
	const STATUS_FAILED     = 'failed';
	const STATUS_CANCELLED  = 'cancelled';
	const STATUS_REFUNDED   = 'refunded';
	const STATUS_PROCESSING = 'processing';

	/**
	 * Get all available order statuses.
	 *
	 * @since 1.0.0
	 * @return array Array of order statuses with their configurations.
	 */
	public static function get_order_statuses() {
		return array(
			self::STATUS_PENDING    => array(
				'label'       => __( 'Pending', 'skillpulse-lms' ),
				'description' => __( 'Order is waiting for payment', 'skillpulse-lms' ),
				'color'       => '#ffc107',
				'icon'        => '⏳',
			),
			self::STATUS_PROCESSING => array(
				'label'       => __( 'Processing', 'skillpulse-lms' ),
				'description' => __( 'Payment is being processed', 'skillpulse-lms' ),
				'color'       => '#17a2b8',
				'icon'        => '🔄',
			),
			self::STATUS_COMPLETED  => array(
				'label'       => __( 'Completed', 'skillpulse-lms' ),
				'description' => __( 'Payment completed, course access granted', 'skillpulse-lms' ),
				'color'       => '#28a745',
				'icon'        => '✅',
			),
			self::STATUS_FAILED     => array(
				'label'       => __( 'Failed', 'skillpulse-lms' ),
				'description' => __( 'Payment failed or was declined', 'skillpulse-lms' ),
				'color'       => '#dc3545',
				'icon'        => '❌',
			),
			self::STATUS_CANCELLED  => array(
				'label'       => __( 'Cancelled', 'skillpulse-lms' ),
				'description' => __( 'Order was cancelled by user or admin', 'skillpulse-lms' ),
				'color'       => '#6c757d',
				'icon'        => '🚫',
			),
			self::STATUS_REFUNDED   => array(
				'label'       => __( 'Refunded', 'skillpulse-lms' ),
				'description' => __( 'Payment was refunded, access revoked', 'skillpulse-lms' ),
				'color'       => '#fd7e14',
				'icon'        => '💰',
			),
		);
	}

	/**
	 * Get next possible statuses.
	 *
	 * @since 1.0.0
	 * @param string $current_status Current order status.
	 * @return array Array of possible next statuses.
	 */
	public static function get_next_statuses( $current_status ) {
		$transitions = array(
			self::STATUS_PENDING    => array( self::STATUS_PROCESSING, self::STATUS_COMPLETED, self::STATUS_FAILED, self::STATUS_CANCELLED ),
			self::STATUS_PROCESSING => array( self::STATUS_COMPLETED, self::STATUS_FAILED, self::STATUS_CANCELLED ),
			self::STATUS_COMPLETED  => array( self::STATUS_REFUNDED ),
			self::STATUS_FAILED     => array( self::STATUS_PENDING, self::STATUS_CANCELLED ),
			self::STATUS_CANCELLED  => array( self::STATUS_PENDING ),
			self::STATUS_REFUNDED   => array(), // No transitions from refunded.
		);

		return isset( $transitions[ $current_status ] ) ? $transitions[ $current_status ] : array();
	}

	/**
	 * Validate status transition.
	 *
	 * @since 1.0.0
	 * @param string $from_status Current order status.
	 * @param string $to_status Target order status.
	 * @return bool True if transition is valid, false otherwise.
	 */
	public static function can_transition_to( $from_status, $to_status ) {
		$next_statuses = self::get_next_statuses( $from_status );
		return in_array( $to_status, $next_statuses, true );
	}

	/**
	 * Get user-friendly message for order status.
	 *
	 * @since 1.0.0
	 * @param string $status Order status.
	 * @return array|null Array with title, text, and action keys, or null if status not found.
	 */
	public static function get_user_message( $status ) {
		$messages = array(
			self::STATUS_PENDING    => array(
				'title'  => __( 'Payment Pending', 'skillpulse-lms' ),
				'text'   => __( 'Please complete your payment to access this course.', 'skillpulse-lms' ),
				'action' => __( 'Complete Payment', 'skillpulse-lms' ),
			),
			self::STATUS_PROCESSING => array(
				'title'  => __( 'Processing Payment', 'skillpulse-lms' ),
				'text'   => __( 'Your payment is being processed. This usually takes a few minutes.', 'skillpulse-lms' ),
				'action' => null,
			),
			self::STATUS_COMPLETED  => array(
				'title'  => __( 'Payment Complete', 'skillpulse-lms' ),
				'text'   => __( 'You now have access to this course!', 'skillpulse-lms' ),
				'action' => __( 'Start Learning', 'skillpulse-lms' ),
			),
			self::STATUS_FAILED     => array(
				'title'  => __( 'Payment Failed', 'skillpulse-lms' ),
				'text'   => __( 'Your payment could not be processed. Please try again.', 'skillpulse-lms' ),
				'action' => __( 'Try Again', 'skillpulse-lms' ),
			),
			self::STATUS_CANCELLED  => array(
				'title'  => __( 'Order Cancelled', 'skillpulse-lms' ),
				'text'   => __( 'This order has been cancelled.', 'skillpulse-lms' ),
				'action' => null,
			),
			self::STATUS_REFUNDED   => array(
				'title'  => __( 'Refunded', 'skillpulse-lms' ),
				'text'   => __( 'This order has been refunded.', 'skillpulse-lms' ),
				'action' => null,
			),
		);

		return isset( $messages[ $status ] ) ? $messages[ $status ] : null;
	}
}
