<?php
/**
 * Orders Template
 *
 * This template can be overridden by copying it to:
 * your-theme/skillpulse-lms/dashboard/orders.php
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

// Get user orders.
$splms_user_orders = array();
if ( class_exists( 'SkillPulse_LMS_Orders_Query' ) ) {
	$splms_orders_query = SkillPulse_LMS_Orders_Query::get_instance();
	$splms_user_orders  = $splms_orders_query->get_user_orders( $splms_user_id );
}
?>
<div class="splms-dashboard-tab splms-orders-tab">

	<!-- Page Header -->
	<div class="splms-section-title" style="margin-bottom: 2rem;">
		<i class="hgi-stroke hgi-shopping-cart-01"></i>
		<?php esc_html_e( 'My Orders', 'skillpulse-lms' ); ?>
	</div>

	<!-- Orders Content -->
	<div class="splms-orders-content">
		<?php if ( ! empty( $splms_user_orders ) ) : ?>
			<!-- Simple summary -->
			<div class="splms-courses-summary">
				<p class="splms-summary-text">
					<?php
					printf(
						/* translators: %d: number of orders */
						esc_html__( 'You have placed %d orders', 'skillpulse-lms' ),
						count( $splms_user_orders )
					);
					?>
				</p>
			</div>

			<div class="splms-course-list">
				<?php
				$splms_status_colors = array(
					'pending'   => '#F59E0B',
					'completed' => '#10B981',
					'cancelled' => '#EF4444',
					'refunded'  => '#8B5CF6',
				);
				?>
				<?php
				foreach ( $splms_user_orders as $splms_order_data ) :
					// Handle both object and array format.
					if ( is_object( $splms_order_data ) ) {
						$splms_order_id     = isset( $splms_order_data->id ) ? $splms_order_data->id : 0;
						$splms_order_status = isset( $splms_order_data->status ) ? $splms_order_data->status : 'pending';
						$splms_course_id    = isset( $splms_order_data->course_id ) ? $splms_order_data->course_id : 0;
						$splms_amount       = isset( $splms_order_data->amount ) ? $splms_order_data->amount : 0;
						$splms_created_at   = isset( $splms_order_data->created_at ) ? $splms_order_data->created_at : '';
					} else {
						$splms_order_id     = isset( $splms_order_data['id'] ) ? $splms_order_data['id'] : ( isset( $splms_order_data['order_id'] ) ? $splms_order_data['order_id'] : 0 );
						$splms_order_status = isset( $splms_order_data['status'] ) ? $splms_order_data['status'] : 'pending';
						$splms_course_id    = isset( $splms_order_data['course_id'] ) ? $splms_order_data['course_id'] : 0;
						$splms_amount       = isset( $splms_order_data['amount'] ) ? $splms_order_data['amount'] : 0;
						$splms_created_at   = isset( $splms_order_data['created_at'] ) ? $splms_order_data['created_at'] : '';
					}

					$splms_order_status = str_replace( 'sp_order_', '', $splms_order_status );
					$splms_color        = isset( $splms_status_colors[ $splms_order_status ] ) ? $splms_status_colors[ $splms_order_status ] : '#6B7280';

					// Get payment method from order meta if available.
					$splms_payment_method = 'Unknown';
					if ( is_object( $splms_order_data ) && isset( $splms_order_data->meta['payment_method'] ) ) {
						$splms_payment_method = $splms_order_data->meta['payment_method'];
					}

					// Get course title - try multiple approaches.
					$splms_course_title = __( 'Unknown Course', 'skillpulse-lms' );
					if ( $splms_course_id > 0 ) {
						$splms_course_post = get_post( $splms_course_id );
						if ( $splms_course_post && 'publish' === $splms_course_post->post_status ) {
							$splms_course_title = $splms_course_post->post_title;
						} elseif ( isset( $splms_order_data->meta['course_snapshot'] ) ) {
							$splms_course_title = $splms_order_data->meta['course_snapshot']['course_title'];
						}
					}

					// Format date.
					$splms_order_date = $splms_created_at ? gmdate( 'M j, Y', strtotime( $splms_created_at ) ) : gmdate( 'M j, Y' );

					// Beautify payment method names.
					$splms_payment_method_labels  = array(
						'stripe'        => 'Stripe',
						'paypal'        => 'PayPal',
						'razorpay'      => 'Razorpay',
						'manual'        => 'Manual Payment',
						'bank_transfer' => 'Bank Transfer',
						'free'          => 'Free',
					);
					$splms_payment_method_display = isset( $splms_payment_method_labels[ $splms_payment_method ] )
						? $splms_payment_method_labels[ $splms_payment_method ]
						: ucfirst( str_replace( '_', ' ', $splms_payment_method ) );

					$splms_status_labels = array(
						'pending'   => __( 'Pending', 'skillpulse-lms' ),
						'completed' => __( 'Completed', 'skillpulse-lms' ),
						'cancelled' => __( 'Cancelled', 'skillpulse-lms' ),
						'refunded'  => __( 'Refunded', 'skillpulse-lms' ),
					);
					$splms_status_label  = isset( $splms_status_labels[ $splms_order_status ] ) ? $splms_status_labels[ $splms_order_status ] : ucfirst( $splms_order_status );
					?>
					<div class="splms-course-card splms-order-card">
						<div class="splms-course-info splms-order-info-full">
							<div class="splms-course-header">
								<h3 class="splms-course-title"><?php echo esc_html( $splms_course_title ); ?></h3>
								<span class="splms-enrollment-status-badge" style="background: <?php echo esc_attr( $splms_color ); ?>20; color: <?php echo esc_attr( $splms_color ); ?>;">
									<?php echo esc_html( $splms_status_label ); ?>
								</span>
							</div>
							<div class="splms-order-meta">
								<div class="splms-order-details">
									<span class="splms-order-id">
										<strong>
											<?php
											/* translators: %s: Order ID number */
											printf( esc_html__( 'Order #%s', 'skillpulse-lms' ), esc_html( $splms_order_id ) );
											?>
										</strong>
									</span>
									<span class="splms-order-amount">
										<?php echo esc_html( '$' . number_format( (float) $splms_amount, 2 ) ); ?>
									</span>
									<span class="splms-order-payment">
										<?php echo esc_html( $splms_payment_method_display ); ?>
									</span>
									<span class="splms-order-date">
										<?php echo esc_html( $splms_order_date ); ?>
									</span>
								</div>
							</div>
						</div>
						<div class="splms-course-actions">
							<a href="<?php echo esc_url( home_url( '/dashboard/orders/view/' . $splms_order_id ) ); ?>"
								class="splms-btn splms-btn-primary"
								title="<?php esc_attr_e( 'View Order Details', 'skillpulse-lms' ); ?>">
								<?php esc_html_e( 'View Details', 'skillpulse-lms' ); ?>
								<i class="hgi-stroke hgi-arrow-right-01"></i>
							</a>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		<?php else : ?>
			<!-- Empty State -->
			<div class="splms-dashboard-empty-state">
				<svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
					<circle cx="9" cy="21" r="1" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
					<circle cx="20" cy="21" r="1" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
					<path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				<h3><?php esc_html_e( 'No Orders Yet', 'skillpulse-lms' ); ?></h3>
				<p><?php esc_html_e( 'Your order history will appear here once you purchase a course.', 'skillpulse-lms' ); ?></p>
			</div>
		<?php endif; ?>
	</div>
</div>


<script type="text/html" id="tmpl-splms-dashboard-order-details">
	<# console.log('data', data); #>
	<div class="splms-order-detail-header">
		<button type="button" class="splms-btn splms-btn-secondary splms-btn-sm order-back-btn">
			<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M19 12H5M12 19l-7-7 7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
			</svg>
			<?php esc_html_e( 'Back to Orders', 'skillpulse-lms' ); ?>
		</button>
		<h2><?php esc_html_e( 'Order Details', 'skillpulse-lms' ); ?></h2>
	</div>

	<div class="splms-order-detail-section">
		<h3><?php esc_html_e( 'Order Summary', 'skillpulse-lms' ); ?></h3>
		<div class="splms-order-summary-list">
			<div class="splms-order-summary-item">
				<label><?php esc_html_e( 'Order Number:', 'skillpulse-lms' ); ?></label>
				<span>#{{{ data.order_id }}}</span>
			</div>
			<div class="splms-order-summary-item">
				<label><?php esc_html_e( 'Order Date:', 'skillpulse-lms' ); ?></label>
				<span>{{{ data.order_date }}}</span>
			</div>
			<div class="splms-order-summary-item">
				<label><?php esc_html_e( 'Payment Method:', 'skillpulse-lms' ); ?></label>
				<span>{{{ data.payment_method_label }}}</span>
			</div>
			<# if ( data.transaction_id ) { #>
				<div class="splms-order-summary-item">
					<label><?php esc_html_e( 'Transaction ID:', 'skillpulse-lms' ); ?></label>
					<span>{{{ data.transaction_id }}}</span>
				</div>
			<# } #>
			<div class="splms-order-summary-item">
				<label><?php esc_html_e( 'Order Status:', 'skillpulse-lms' ); ?></label>
				<span class="splms-status-badge splms-status-{{{ data.status }}}">{{{ data.status_label }}}</span>
			</div>
			<div class="splms-order-summary-item">
				<label><?php esc_html_e( 'Total Amount:', 'skillpulse-lms' ); ?></label>
				<span class="splms-order-total">{{{ data.formatted_amount }}}</span>
			</div>
		</div>
	</div>

	<div class="splms-order-detail-section">
		<h3><?php esc_html_e( 'Billing Details', 'skillpulse-lms' ); ?></h3>
		<div class="splms-billing-details">
			<p><strong><?php esc_html_e( 'Name:', 'skillpulse-lms' ); ?></strong> {{{ data.billing_name }}}</p>
			<p><strong><?php esc_html_e( 'Email:', 'skillpulse-lms' ); ?></strong> {{{ data.billing_email }}}</p>
			<# if ( data.billing_address ) { #>
				<p><strong><?php esc_html_e( 'Address:', 'skillpulse-lms' ); ?></strong> {{{ data.billing_address }}}</p>
			<# } #>
		</div>
	</div>

	<div class="splms-order-detail-section">
		<h3><?php esc_html_e( 'Purchased Course(s)', 'skillpulse-lms' ); ?></h3>
		<div class="splms-order-courses">
			<# if ( data.courses && data.courses.length > 0 ) { #>
				<# _.each( data.courses, function( course ) { #>
					<div class="splms-order-course-item">
						<# if ( course.thumbnail ) { #>
							<div class="splms-order-course-thumbnail">
								<img src="{{{ course.thumbnail }}}" alt="{{{ course.title }}}" />
							</div>
						<# } #>
						<div class="splms-order-course-info">
							<h4>
								<# if ( course.link ) { #>
									<a href="{{{ course.link }}}">{{{ course.title }}}</a>
								<# } else { #>
									{{{ course.title }}}
								<# } #>
							</h4>
							<# if ( course.access_status ) { #>
								<span class="splms-course-access-status splms-status-{{{ course.access_status }}}">{{{ course.access_status_label }}}</span>
							<# } #>
						</div>
						<# if ( course.link ) { #>
							<div class="splms-order-course-action">
								<a href="{{{ course.link }}}" class="splms-btn splms-btn-primary splms-btn-sm"><?php esc_html_e( 'View Course', 'skillpulse-lms' ); ?></a>
							</div>
						<# } #>
					</div>
				<# }); #>
			<# } else { #>
				<p><?php esc_html_e( 'No course information available.', 'skillpulse-lms' ); ?></p>
			<# } #>
		</div>
	</div>

	<div class="splms-order-detail-section">
		<h3><?php esc_html_e( 'Invoice / Receipt', 'skillpulse-lms' ); ?></h3>
		<div class="splms-order-invoice-actions">
			<button type="button" class="splms-btn splms-btn-primary splms-btn-sm order-download-invoice" data-order-id="{{{ data.order_id }}}">
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				<?php esc_html_e( 'Download Invoice', 'skillpulse-lms' ); ?>
			</button>
			<button type="button" class="splms-btn splms-btn-outline splms-btn-sm order-print-invoice" data-order-id="{{{ data.order_id }}}">
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2M6 18h12M6 18v-5h12v5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				<?php esc_html_e( 'Print Order', 'skillpulse-lms' ); ?>
			</button>
		</div>
	</div>

	<# if ( data.status === 'completed' && data.status !== 'refunded' ) { #>
	<div class="splms-order-detail-section">
		<h3><?php esc_html_e( 'Need Help?', 'skillpulse-lms' ); ?></h3>
		<div class="splms-order-help-info">
			<p><?php esc_html_e( 'For refund requests or any questions about this order, please contact our support team via email or contact form.', 'skillpulse-lms' ); ?></p>
			<# if ( data.support_email ) { #>
				<p style="margin-top: 10px;">
					<strong><?php esc_html_e( 'Support Email:', 'skillpulse-lms' ); ?></strong> 
					<?php
					/* translators: %s: Order ID */
					$splms_refund_subject = sprintf( __( 'Refund Request - Order #%s', 'skillpulse-lms' ), '{{{ data.order_id }}}' );
					?>
					<a href="mailto:{{{ data.support_email }}}?subject=<?php echo esc_attr( $splms_refund_subject ); ?>">{{{ data.support_email }}}</a>
				</p>
			<# } #>
		</div>
	</div>
	<# } #>

	<# if ( data.status === 'pending' ) { #>
	<div class="splms-order-detail-section">
		<h3><?php esc_html_e( 'Order Actions', 'skillpulse-lms' ); ?></h3>
		<div class="splms-order-actions">
			<button type="button" class="splms-btn splms-btn-secondary splms-btn-sm order-request-cancel" data-order-id="{{{ data.order_id }}}">
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
					<circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
					<path d="M15 9l-6 6M9 9l6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
				</svg>
				<?php esc_html_e( 'Cancel Order', 'skillpulse-lms' ); ?>
			</button>
		</div>
	</div>
	<# } #>

	<# if ( data.status === 'refunded' && data.refunded_at ) { #>
	<div class="splms-order-detail-section">
		<h3><?php esc_html_e( 'Refund Information', 'skillpulse-lms' ); ?></h3>
		<div class="splms-refund-details">
			<div class="splms-refund-item">
				<label><?php esc_html_e( 'Refund Date:', 'skillpulse-lms' ); ?></label>
				<span class="splms-refund-date">{{{ data.refunded_at }}}</span>
			</div>
			<# if ( data.formatted_refund_amount ) { #>
			<div class="splms-refund-item">
				<label><?php esc_html_e( 'Refund Amount:', 'skillpulse-lms' ); ?></label>
				<span class="splms-refund-amount">{{{ data.formatted_refund_amount }}}</span>
			</div>
			<# } #>
			<# if ( data.refund_reason ) { #>
			<div class="splms-refund-item full-width">
				<label><?php esc_html_e( 'Refund Reason:', 'skillpulse-lms' ); ?></label>
				<p class="splms-refund-reason">{{{ data.refund_reason }}}</p>
			</div>
			<# } #>
		</div>
	</div>
	<# } #>

	<# if ( data.status === 'cancelled' && data.cancelled_at ) { #>
	<div class="splms-order-detail-section">
		<h3><?php esc_html_e( 'Cancellation Information', 'skillpulse-lms' ); ?></h3>
		<div class="splms-cancellation-details">
			<div class="splms-cancellation-item">
				<label><?php esc_html_e( 'Cancellation Date:', 'skillpulse-lms' ); ?></label>
				<span class="splms-cancellation-date">{{{ data.cancelled_at }}}</span>
			</div>
			<# if ( data.cancellation_reason ) { #>
			<div class="splms-cancellation-item full-width">
				<label><?php esc_html_e( 'Cancellation Reason:', 'skillpulse-lms' ); ?></label>
				<p class="splms-cancellation-reason">{{{ data.cancellation_reason }}}</p>
			</div>
			<# } #>
		</div>
	</div>
	<# } #>
</script>

<script type="text/html" id="tmpl-splms-dashboard-empty-state">
	<div class="splms-dashboard-empty-state">
		<svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
			<path d="{{{ data.svgPath }}}" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
		</svg>
		<h3>{{{ data.title }}}</h3>
		<p>{{{ data.message }}}</p>
	</div>
</script>


<!-- Cancellation Request Modal -->
<div id="splms-cancel-modal" class="splms-modal" style="display: none;">
	<div class="splms-modal-overlay"></div>
	<div class="splms-modal-content">
		<div class="splms-modal-header">
			<h3><?php esc_html_e( 'Cancel Order', 'skillpulse-lms' ); ?></h3>
			<button type="button" class="splms-modal-close">&times;</button>
		</div>
		<div class="splms-modal-body">
			<form id="splms-cancel-form">
				<input type="hidden" id="cancel-order-id" name="order_id" value="">
				<div class="splms-form-group">
					<label for="cancel-reason"><?php esc_html_e( 'Cancellation Reason', 'skillpulse-lms' ); ?> <span class="required">*</span></label>
					<textarea id="cancel-reason" name="reason" rows="4" required placeholder="<?php esc_attr_e( 'Please provide a reason for cancelling this order...', 'skillpulse-lms' ); ?>"></textarea>
				</div>
				<div class="splms-form-actions">
					<button type="button" class="splms-btn splms-btn-secondary cancel-modal-cancel"><?php esc_html_e( 'Cancel', 'skillpulse-lms' ); ?></button>
					<button type="submit" class="splms-btn splms-btn-primary"><?php esc_html_e( 'Cancel Order', 'skillpulse-lms' ); ?></button>
				</div>
			</form>
		</div>
	</div>
</div>


