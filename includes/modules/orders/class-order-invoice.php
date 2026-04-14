<?php
/**
 * Order Invoice Generator
 *
 * Handles invoice PDF generation and HTML rendering for orders.
 *
 * @package SkillPulse_LMS
 * @subpackage Modules
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Order Invoice Generator Class
 *
 * @since 1.0.0
 */
class SkillPulse_LMS_Order_Invoice {

	/**
	 * Class instance.
	 *
	 * @since 1.0.0
	 * @var SkillPulse_LMS_Order_Invoice|null
	 */
	private static $instance = null;

	/**
	 * Get the instance of this class.
	 *
	 * @since 1.0.0
	 * @return SkillPulse_LMS_Order_Invoice
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
	 * Generate invoice PDF for an order.
	 *
	 * @since 1.0.0
	 * @param int $order_id Numeric order ID.
	 * @return array|WP_Error Array with 'download_url' on success, WP_Error on failure.
	 */
	public function generate_pdf( $order_id ) {
		$order_id = absint( $order_id );
		if ( ! $order_id ) {
			return new WP_Error( 'invalid_order_id', __( 'Invalid order ID.', 'skillpulse-lms' ) );
		}

		// Check if DomPDF is available.
		if ( ! class_exists( 'SkillPulseLMS\Vendor\Dompdf\Dompdf' ) ) {
			return new WP_Error( 'dompdf_not_available', __( 'PDF library (Dompdf) is not available.', 'skillpulse-lms' ) );
		}

		// Get order details.
		$order_details = $this->get_order_details_for_invoice( $order_id );
		if ( is_wp_error( $order_details ) ) {
			return $order_details;
		}

		try {
			// Generate invoice HTML.
			$invoice_html = $this->get_invoice_html( $order_details );
			if ( is_wp_error( $invoice_html ) ) {
				return $invoice_html;
			}

			// Generate PDF.
			$options = new \SkillPulseLMS\Vendor\Dompdf\Options();
			$options->set( 'defaultFont', 'Helvetica' );
			$options->set( 'isHtml5ParserEnabled', true );
			$options->set( 'isPhpEnabled', false ); // Disable PHP for security.
			$options->set( 'isRemoteEnabled', false ); // Disable remote loading for security.
			$options->set( 'chroot', ABSPATH ); // Set chroot to WordPress root for security.

			try {
				$dompdf = new \SkillPulseLMS\Vendor\Dompdf\Dompdf( $options );
			} catch ( \Exception $e ) {
				if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
					error_log( 'SkillPulse LMS: DomPDF initialization failed: ' . $e->getMessage() ); // phpcs:ignore
				}
				return new WP_Error( 'pdf_init_failed', __( 'PDF generation initialization failed.', 'skillpulse-lms' ) );
			}

			$dompdf->loadHtml( $invoice_html );
			$dompdf->setPaper( 'A4', 'portrait' );
			$dompdf->render();

			// Generate unique filename.
			$filename = 'invoice-' . $order_id . '-' . time() . '.pdf';

			// Save PDF file.
			$pdf_content = $dompdf->output();

			if ( strlen( $pdf_content ) > 1000 ) {
				$file_result = SkillPulse_LMS_File_Manager::write_file( 'invoices', $filename, $pdf_content );

				if ( ! is_wp_error( $file_result ) ) {
					return array(
						'download_url' => $file_result['fileurl'],
						'filepath'     => $file_result['filepath'],
						'filename'     => $filename,
					);
				}
			}

			return new WP_Error( 'pdf_generation_failed', __( 'Failed to save PDF file.', 'skillpulse-lms' ) );
		} catch ( Exception $e ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Error logging is necessary for debugging invoice generation failures.
			error_log( 'Invoice PDF generation error: ' . $e->getMessage() );
			return new WP_Error( 'pdf_generation_error', __( 'Failed to generate invoice PDF.', 'skillpulse-lms' ) );
		}
	}

	/**
	 * Get invoice HTML for display/print.
	 *
	 * @since 1.0.0
	 * @param int|array $order_id_or_details Numeric order ID or order details array.
	 * @return string|WP_Error Invoice HTML on success, WP_Error on failure.
	 */
	public function get_invoice_html( $order_id_or_details = null ) {
		// If array is passed, use it as order_details.
		if ( is_array( $order_id_or_details ) ) {
			$order_details = $order_id_or_details;
		} else {
			// Otherwise, treat as order_id and fetch details.
			$order_id = absint( $order_id_or_details );
			if ( ! $order_id ) {
				return new WP_Error( 'invalid_order_id', __( 'Invalid order ID.', 'skillpulse-lms' ) );
			}

			$order_details = $this->get_order_details_for_invoice( $order_id );
			if ( is_wp_error( $order_details ) ) {
				return $order_details;
			}
		}

		$site_name   = get_bloginfo( 'name' );
		$site_url    = home_url();
		$admin_email = get_option( 'admin_email' );

		ob_start();
		?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<title><?php echo esc_html__( 'Invoice', 'skillpulse-lms' ); ?> - <?php echo esc_html( $order_details['order_id'] ); ?></title>
	<style>
		body { font-family: Arial, sans-serif; margin: 40px; color: #333; }
		.invoice-header { border-bottom: 2px solid #2c5aa0; padding-bottom: 20px; margin-bottom: 30px; }
		.invoice-header h1 { color: #2c5aa0; margin: 0; }
		.invoice-info { display: flex; justify-content: space-between; margin-bottom: 30px; }
		.invoice-section { margin-bottom: 30px; }
		.invoice-section h2 { color: #2c5aa0; border-bottom: 1px solid #ddd; padding-bottom: 10px; }
		table { width: 100%; border-collapse: collapse; margin: 20px 0; }
		table th, table td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
		table th { background-color: #f5f5f5; font-weight: bold; }
		.invoice-total { text-align: right; font-size: 18px; font-weight: bold; margin-top: 20px; }
		.invoice-footer { margin-top: 40px; padding-top: 20px; border-top: 1px solid #ddd; color: #666; font-size: 12px; }
	</style>
</head>
<body>
	<div class="invoice-header">
		<h1><?php echo esc_html__( 'INVOICE', 'skillpulse-lms' ); ?></h1>
		<p><strong><?php echo esc_html( $site_name ); ?></strong><br><?php echo esc_html( $site_url ); ?></p>
	</div>
	
	<div class="invoice-info">
		<div>
			<p><strong><?php esc_html_e( 'Invoice Number:', 'skillpulse-lms' ); ?></strong> #<?php echo esc_html( $order_details['order_id'] ); ?></p>
			<p><strong><?php esc_html_e( 'Invoice Date:', 'skillpulse-lms' ); ?></strong> <?php echo esc_html( $order_details['order_date'] ); ?></p>
			<p><strong><?php esc_html_e( 'Payment Method:', 'skillpulse-lms' ); ?></strong> <?php echo esc_html( $order_details['payment_method_label'] ); ?></p>
			<?php if ( ! empty( $order_details['transaction_id'] ) ) { ?>
				<p><strong><?php esc_html_e( 'Transaction ID:', 'skillpulse-lms' ); ?></strong> <?php echo esc_html( $order_details['transaction_id'] ); ?></p>
			<?php } ?>
		</div>
		<div>
			<p><strong><?php esc_html_e( 'Bill To:', 'skillpulse-lms' ); ?></strong></p>
			<p><?php echo esc_html( $order_details['billing_name'] ); ?><br>
			<?php if ( ! empty( $order_details['billing_email'] ) ) { ?>
				<?php echo esc_html( $order_details['billing_email'] ); ?><br>
			<?php } ?>
			<?php if ( ! empty( $order_details['billing_address'] ) ) { ?>
				<?php echo esc_html( $order_details['billing_address'] ); ?><br>
			<?php } ?>
			</p>
		</div>
	</div>
	
	<div class="invoice-section">
		<h2><?php esc_html_e( 'Items Purchased', 'skillpulse-lms' ); ?></h2>
		<table>
			<thead>
				<tr>
					<th><?php esc_html_e( 'Course', 'skillpulse-lms' ); ?></th>
					<th><?php esc_html_e( 'Amount', 'skillpulse-lms' ); ?></th>
				</tr>
			</thead>
			<tbody>
			<?php foreach ( $order_details['courses'] as $course ) { ?>
				<tr>
					<td><?php echo esc_html( $course['title'] ); ?></td>
					<td><?php echo esc_html( $order_details['formatted_amount'] ); ?></td>
				</tr>
			<?php } ?>
			</tbody>
		</table>
		<div class="invoice-total">
			<p><?php esc_html_e( 'Total:', 'skillpulse-lms' ); ?> <?php echo esc_html( $order_details['formatted_amount'] ); ?></p>
		</div>
	</div>
	
	<div class="invoice-footer">
		<p><?php esc_html_e( 'Thank you for your purchase!', 'skillpulse-lms' ); ?></p>
		<p><?php esc_html_e( 'For support, please contact us at', 'skillpulse-lms' ); ?> <?php echo esc_html( $admin_email ); ?></p>
	</div>
</body>
</html>
		<?php
		return ob_get_clean();
	}

	/**
	 * Get order details formatted for invoice generation.
	 *
	 * @since 1.0.0
	 * @param int $order_id Numeric order ID.
	 * @return array|WP_Error Order details array on success, WP_Error on failure.
	 */
	public function get_order_details_for_invoice( $order_id ) {
		$order_id = absint( $order_id );
		if ( ! $order_id ) {
			return new WP_Error( 'invalid_order_id', __( 'Invalid order ID.', 'skillpulse-lms' ) );
		}

		if ( ! class_exists( 'SkillPulse_LMS_Orders_Query' ) ) {
			return new WP_Error( 'class_not_found', __( 'Orders query class not found.', 'skillpulse-lms' ) );
		}

		$orders_query = SkillPulse_LMS_Orders_Query::get_instance();
		$order        = $orders_query->get_order_by_id( $order_id );

		if ( ! $order ) {
			return new WP_Error( 'order_not_found', __( 'Order not found.', 'skillpulse-lms' ) );
		}

		// Get all order meta.
		$meta = $orders_query->get_order_meta( $order_id );

		// Get payment method.
		$payment_method       = $meta['payment_method'] ?? 'unknown';
		$payment_method_label = $this->format_payment_method( $payment_method );

		// Get transaction ID.
		$transaction_id = $meta['gateway_transaction_id'] ?? $meta['gateway_order_id'] ?? '';

		// Get user data.
		$user          = get_userdata( $order->user_id );
		$billing_name  = $user ? $user->display_name : '';
		$billing_email = $user ? $user->user_email : '';

		// Get billing address from meta if available.
		$billing_address = $meta['billing_address'] ?? '';

		// Format amount.
		$currency_raw     = $order->currency;
		$currency         = ! empty( $currency_raw ) ? $currency_raw : splms_get_setting( 'currency', 'USD' );
		$currency_symbol  = splms_get_currency_symbol( $currency );
		$formatted_amount = $currency_symbol . number_format( floatval( $order->amount ), 2 );

		// Format date.
		$order_date = $order->created_at ? date_i18n( 'M j, Y', strtotime( $order->created_at ) ) : '';

		// Get course data.
		$course  = get_post( $order->course_id );
		$courses = array();
		if ( $course ) {
			$courses[] = array(
				'course_id' => $course->ID,
				'title'     => $course->post_title,
			);
		}

		return array(
			'order_id'             => $order->id, // Use numeric id.
			'order_date'           => $order_date,
			'created_at'           => $order->created_at,
			'payment_method'       => $payment_method,
			'payment_method_label' => $payment_method_label,
			'transaction_id'       => $transaction_id,
			'status'               => $order->status,
			'amount'               => floatval( $order->amount ),
			'formatted_amount'     => $formatted_amount,
			'currency'             => $currency,
			'billing_name'         => $billing_name,
			'billing_email'        => $billing_email,
			'billing_address'      => $billing_address,
			'courses'              => $courses,
		);
	}

	/**
	 * Format payment method label.
	 *
	 * @since 1.0.0
	 * @param string $payment_method Payment method.
	 * @return string Formatted label.
	 */
	private function format_payment_method( $payment_method ) {
		$methods = array(
			'paypal'        => __( 'PayPal', 'skillpulse-lms' ),
			'stripe'        => __( 'Stripe', 'skillpulse-lms' ),
			'razorpay'      => __( 'Razorpay', 'skillpulse-lms' ),
			'credit_card'   => __( 'Credit Card', 'skillpulse-lms' ),
			'bank_transfer' => __( 'Bank Transfer', 'skillpulse-lms' ),
			'offline'       => __( 'Offline Payment', 'skillpulse-lms' ),
			'free'          => __( 'Free', 'skillpulse-lms' ),
		);

		return isset( $methods[ $payment_method ] ) ? $methods[ $payment_method ] : ucfirst( $payment_method );
	}
}

