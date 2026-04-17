<?php
/**
 * Order Details Template
 *
 * This template can be overridden by copying it to:
 * your-theme/skillpulse-lms/dashboard/order-details.php
 *
 * @package SkillPulse_LMS
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Extract variables from args.
// phpcs:ignore WordPress.PHP.DontExtract.extract_extract -- Template file uses extract for convenience.
extract( $args );

// Get order details.
$order_details = array();
$order_id      = isset( $order_id ) ? $order_id : 0;

if ( $order_id && class_exists( 'SkillPulse_LMS_Orders_Query' ) ) {
	$orders_query = SkillPulse_LMS_Orders_Query::get_instance();
	$order_data   = $orders_query->get_order_by_id( $order_id );

	if ( $order_data && ( $order_data->user_id === $user_id || current_user_can( 'manage_options' ) ) ) {
		// Format order data.
		$order_details = array(
			'order_id'   => $order_data->id,
			'course_id'  => $order_data->course_id,
			'amount'     => $order_data->amount,
			'status'     => str_replace( 'sp_order_', '', $order_data->status ),
			'created_at' => $order_data->created_at,
			'updated_at' => $order_data->updated_at,
		);

		// Get course information.
		$course_post = get_post( $order_data->course_id );
		if ( $course_post ) {
			$order_details['course_title'] = $course_post->post_title;
			$order_details['course_link']  = get_permalink( $course_post );
		} elseif ( isset( $order_data->meta['course_snapshot'] ) ) {
			$order_details['course_title'] = $order_data->meta['course_snapshot']['course_title'];
		}

		// Get payment method from meta.
		if ( isset( $order_data->meta['payment_method'] ) ) {
			$order_details['payment_method'] = $order_data->meta['payment_method'];
		}

		// Get user information.
		$user_data = get_userdata( $order_data->user_id );
		if ( $user_data ) {
			$order_details['billing_name']  = $user_data->display_name;
			$order_details['billing_email'] = $user_data->user_email;
		}
	}
}

if ( empty( $order_details ) ) {
	wp_die( esc_html__( 'Order not found or access denied.', 'skillpulse-lms' ) );
}

// Format data for display.
$status_colors = array(
	'pending'   => '#F59E0B',
	'completed' => '#10B981',
	'cancelled' => '#EF4444',
	'refunded'  => '#8B5CF6',
);

$status_labels = array(
	'pending'   => __( 'Pending', 'skillpulse-lms' ),
	'completed' => __( 'Completed', 'skillpulse-lms' ),
	'cancelled' => __( 'Cancelled', 'skillpulse-lms' ),
	'refunded'  => __( 'Refunded', 'skillpulse-lms' ),
);

$order_status = $order_details['status'];
$color        = isset( $status_colors[ $order_status ] ) ? $status_colors[ $order_status ] : '#6B7280';
$status_label = isset( $status_labels[ $order_status ] ) ? $status_labels[ $order_status ] : ucfirst( $order_status );

$payment_method_labels = array(
	'stripe'        => 'Stripe',
	'paypal'        => 'PayPal',
	'razorpay'      => 'Razorpay',
	'manual'        => 'Manual Payment',
	'bank_transfer' => 'Bank Transfer',
	'free'          => 'Free',
);

$payment_method         = isset( $order_details['payment_method'] ) ? $order_details['payment_method'] : 'Unknown';
$payment_method_display = isset( $payment_method_labels[ $payment_method ] )
	? $payment_method_labels[ $payment_method ]
	: ucfirst( str_replace( '_', ' ', $payment_method ) );

$order_date = $order_details['created_at'] ? gmdate( 'F j, Y g:i A', strtotime( $order_details['created_at'] ) ) : '';
?>
<div class="splms-dashboard-tab splms-order-details-tab">

	<!-- Page Header -->
	<div class="splms-section-title" style="margin-bottom: 1.5rem;">
		<i class="hgi-stroke hgi-receipt"></i>
		<?php
		/* translators: %s: Order ID */
		printf( esc_html__( 'Order #%s', 'skillpulse-lms' ), esc_html( $order_details['order_id'] ) );
		?>
	</div>

	<!-- Back to Orders Button -->
	<div class="splms-back-navigation" style="margin-bottom: 2rem;">
		<a href="<?php echo esc_url( home_url( '/dashboard/orders/' ) ); ?>" class="splms-btn splms-btn-secondary splms-btn-sm">
			<i class="hgi-stroke hgi-arrow-left-01"></i>
			<?php esc_html_e( 'Back to Orders', 'skillpulse-lms' ); ?>
		</a>
	</div>

	<!-- Order Details Content -->
	<div class="splms-order-details-content">

		<!-- Order Summary Section -->
		<div class="splms-order-detail-section">
			<div class="splms-order-summary-card">
				<div class="splms-order-summary-header">
					<div class="splms-order-status-info">
						<span class="splms-status-badge splms-status-<?php echo esc_attr( $status ); ?>" style="background: <?php echo esc_attr( $color ); ?>20; color: <?php echo esc_attr( $color ); ?>;">
							<?php echo esc_html( $status_label ); ?>
						</span>
						<span class="splms-order-date"><?php echo esc_html( $order_date ); ?></span>
					</div>
					<div class="splms-order-total-display">
						<span class="splms-order-total-label"><?php esc_html_e( 'Total', 'skillpulse-lms' ); ?></span>
						<span class="splms-order-total-amount"><?php echo esc_html( '$' . number_format( (float) $order_details['amount'], 2 ) ); ?></span>
					</div>
				</div>

				<div class="splms-order-summary-grid">
					<div class="splms-order-summary-item">
						<label><?php esc_html_e( 'Order Number:', 'skillpulse-lms' ); ?></label>
						<span><strong>#<?php echo esc_html( $order_details['order_id'] ); ?></strong></span>
					</div>
					<div class="splms-order-summary-item">
						<label><?php esc_html_e( 'Payment Method:', 'skillpulse-lms' ); ?></label>
						<span><?php echo esc_html( $payment_method_display ); ?></span>
					</div>
				</div>
			</div>
		</div>

		<!-- Course Information Section -->
		<div class="splms-order-detail-section">
			<h3><?php esc_html_e( 'Course Information', 'skillpulse-lms' ); ?></h3>
			<div class="splms-order-course-card">
				<div class="splms-course-icon" style="color: <?php echo esc_attr( $color ); ?>; background-color: <?php echo esc_attr( $color ); ?>20;">
					<?php if ( 'completed' === $order_status ) : ?>
						<i class="hgi-stroke hgi-tick-02"></i>
					<?php elseif ( 'pending' === $order_status ) : ?>
						<i class="hgi-stroke hgi-time-04"></i>
					<?php elseif ( 'cancelled' === $order_status || 'refunded' === $order_status ) : ?>
						<i class="hgi-stroke hgi-cancel-01"></i>
					<?php else : ?>
						<i class="hgi-stroke hgi-book-open-01"></i>
					<?php endif; ?>
				</div>
				<div class="splms-course-info">
					<h4 class="splms-course-title">
						<?php if ( isset( $order_details['course_link'] ) ) : ?>
							<a href="<?php echo esc_url( $order_details['course_link'] ); ?>">
								<?php echo esc_html( $order_details['course_title'] ?? __( 'Unknown Course', 'skillpulse-lms' ) ); ?>
							</a>
						<?php else : ?>
							<?php echo esc_html( isset( $order_details['course_title'] ) ? $order_details['course_title'] : __( 'Unknown Course', 'skillpulse-lms' ) ); ?>
						<?php endif; ?>
					</h4>
					<div class="splms-course-meta">
						<span>
							<?php
							/* translators: %d: Course ID number */
							printf( esc_html__( 'Course ID: %d', 'skillpulse-lms' ), esc_html( $order_details['course_id'] ) );
							?>
						</span>
					</div>
				</div>
				<?php if ( isset( $order_details['course_link'] ) && 'completed' === $order_status ) : ?>
					<div class="splms-course-actions">
						<a href="<?php echo esc_url( $order_details['course_link'] ); ?>" class="splms-btn splms-btn-primary">
							<?php esc_html_e( 'Access Course', 'skillpulse-lms' ); ?>
							<i class="hgi-stroke hgi-arrow-right-01"></i>
						</a>
					</div>
				<?php endif; ?>
			</div>
		</div>

		<!-- Billing Details Section -->
		<div class="splms-order-detail-section">
			<h3><?php esc_html_e( 'Billing Details', 'skillpulse-lms' ); ?></h3>
			<div class="splms-billing-details-card">
				<div class="splms-billing-item">
					<label><?php esc_html_e( 'Name:', 'skillpulse-lms' ); ?></label>
					<span><?php echo esc_html( $order_details['billing_name'] ?? '' ); ?></span>
				</div>
				<div class="splms-billing-item">
					<label><?php esc_html_e( 'Email:', 'skillpulse-lms' ); ?></label>
					<span><?php echo esc_html( $order_details['billing_email'] ?? '' ); ?></span>
				</div>
			</div>
		</div>

		<!-- Order Actions Section -->
		<?php if ( 'completed' === $order_status ) : ?>
			<div class="splms-order-detail-section">
				<h3><?php esc_html_e( 'Order Actions', 'skillpulse-lms' ); ?></h3>
				<div class="splms-order-actions">
					<button type="button" class="splms-btn splms-btn-primary" onclick="window.print();">
						<i class="hgi-stroke hgi-printer"></i>
						<?php esc_html_e( 'Print Receipt', 'skillpulse-lms' ); ?>
					</button>
				</div>
			</div>
		<?php endif; ?>

		<?php if ( 'pending' === $order_status ) : ?>
			<div class="splms-order-detail-section">
				<h3><?php esc_html_e( 'Payment Information', 'skillpulse-lms' ); ?></h3>
				<div class="splms-alert splms-alert-warning">
					<p><?php esc_html_e( 'Your payment is still being processed. You will receive course access once payment is confirmed.', 'skillpulse-lms' ); ?></p>
				</div>
			</div>
		<?php endif; ?>

		<?php if ( 'cancelled' === $order_status || 'refunded' === $order_status ) : ?>
			<div class="splms-order-detail-section">
				<h3><?php echo 'refunded' === $order_status ? esc_html__( 'Refund Information', 'skillpulse-lms' ) : esc_html__( 'Cancellation Information', 'skillpulse-lms' ); ?></h3>
				<div class="splms-alert splms-alert-info">
					<p>
						<?php if ( 'refunded' === $order_status ) : ?>
							<?php esc_html_e( 'This order has been refunded. If you have any questions, please contact support.', 'skillpulse-lms' ); ?>
						<?php else : ?>
							<?php esc_html_e( 'This order has been cancelled. If you have any questions, please contact support.', 'skillpulse-lms' ); ?>
						<?php endif; ?>
					</p>
				</div>
			</div>
		<?php endif; ?>

	</div>
</div>