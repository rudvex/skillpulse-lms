/**
 * PayPal Payment Integration
 *
 * Handles PayPal Checkout integration for course purchases.
 *
 * @package SkillPulse_LMS
 * @subpackage Frontend
 * @since 1.0.0
 */

/**
 * PayPal Payment Handler Class
 *
 * @since 1.0.0
 */
class SPLMSPayPal {
	
	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 * @param {Object} checkoutInstance Checkout class instance for callbacks
	 */
	constructor(checkoutInstance) {
		this.checkout = checkoutInstance;
	}

	/**
	 * Initialize PayPal integration
	 *
	 * @since 1.0.0
	 */
	initialize() {
		if (typeof paypal === 'undefined') {
			console.error('PayPal SDK not loaded');
			return;
		}

		const container = document.getElementById('splms-paypal-button-container');
		if (!container) {
			console.error('PayPal container not found');
			return;
		}

		// Clear container to prevent duplicates when switching payment methods
		container.innerHTML = '';

		// Render PayPal button
		paypal.Buttons({
			style: {
				layout: 'vertical',
				color: 'blue',
				shape: 'rect',
				label: 'paypal'
			},
			createOrder: (data, actions) => {
				return this.createOrder(actions);
			},
			onApprove: (data, actions) => {
				return this.approveOrder(data, actions);
			},
			onError: (err) => {
				this.handleError(err);
			},
			onCancel: (data) => {
				this.handleCancel(data);
			}
		}).render('#splms-paypal-button-container');
	}

	/**
	 * Create PayPal order
	 *
	 * @since 1.0.0
	 * @param {Object} actions PayPal actions object
	 * @return {Promise} Promise that resolves to order ID
	 */
	createOrder(actions) {
		return new Promise((resolve, reject) => {
			this.checkout.showProcessingState();
			const frontend = window.splms_frontend;

			// Get course_id from splmsPurchaseData (consistent with all payment gateways).
			const courseId = window.splmsPurchaseData ? window.splmsPurchaseData.courseId : null;

			const formData = {
				action: 'splms_create_paypal_order',
				nonce: frontend.nonces.splms_nonce,
				course_id: courseId
			};

			// Check for section-based purchase data.
			if (window.splmsPurchaseData) {
				const purchaseData = window.splmsPurchaseData;

				if (purchaseData.type === 'sections' && purchaseData.selectedSections && purchaseData.selectedSections.length > 0) {
					// Section-based purchase.
					formData.purchase_type = 'sections';
					formData.selected_sections = JSON.stringify(purchaseData.selectedSections);
				} else {
					// Full course purchase.
					formData.purchase_type = 'full_course';
				}
			}

			jQuery.ajax({
				url: frontend.ajax_url,
				type: 'POST',
				data: formData,
				success: (response) => {
					if (response.success) {
						resolve(response.data.order_id);
					} else {
						this.checkout.showError(response.data || 'Failed to create PayPal order');
						reject(new Error(response.data || 'Failed to create PayPal order'));
					}
				},
				error: (xhr, status, error) => {
					const errorMessage = 'Failed to create PayPal order. Please try again.';
					this.checkout.showError(errorMessage);
					reject(new Error(errorMessage));
				}
			});
		});
	}

	/**
	 * Approve PayPal order
	 *
	 * @since 1.0.0
	 * @param {Object} data PayPal order data
	 * @param {Object} actions PayPal actions object
	 * @return {Promise} Promise that resolves when order is approved
	 */
	approveOrder(data, actions) {
		return new Promise((resolve, reject) => {
			this.checkout.showProcessingState();

			// Get course_id from splmsPurchaseData (consistent with all payment gateways).
			const course_id = window.splmsPurchaseData ? window.splmsPurchaseData.courseId : null;

			console.log('PayPal Approved - Order ID:', data.orderID);
			console.log('PayPal Approved - Course ID:', course_id);
			
			// Use PayPal SDK to capture the order directly
			return actions.order.capture().then((details) => {
				console.log('PayPal Capture Success:', details);
				
				// Now process the captured order via our backend
				this.processCapturedOrder(details, course_id).then(() => {
					resolve();
				}).catch((error) => {
					console.error('Process captured order error:', error);
					reject(error);
				});
			}).catch((error) => {
				console.error('PayPal capture error:', error);
				this.checkout.showError('Payment failed. Please try again.');
				reject(error);
			});
		});
	}

	/**
	 * Process captured PayPal order
	 *
	 * @since 1.0.0
	 * @param {Object} details PayPal capture details
	 * @param {string} courseId Course ID
	 * @returns {Promise} jQuery AJAX Promise
	 */
	processCapturedOrder(details, courseId) {
		const frontend = window.splms_frontend;
		const formData = {
			action: 'splms_process_captured_order',
			nonce: frontend.nonces.splms_nonce,
			paypal_order_id: details.id,
			course_id: courseId,
			capture_details: details
		};

		return jQuery.ajax({
			url: frontend.ajax_url,
			type: 'POST',
			data: formData,
			success: (response) => {
				console.log('Process Captured Order Response:', response);
				
				if (response.success) {
					this.checkout.showSuccessState(response.data.redirect_url);
				} else {
					const errorMessage = response.data && response.data.message ? response.data.message : 'Payment processing failed. Please try again.';
					this.checkout.showError(errorMessage);
				}
			},
			error: (xhr, status, error) => {
				console.error('Process Captured Order Error:', error);
				this.checkout.showError('An error occurred while processing your payment. Please try again.');
			}
		});
	}

	/**
	 * Handle PayPal error
	 *
	 * @since 1.0.0
	 * @param {Object} err Error object
	 */
	handleError(err) {
		console.error('PayPal Error:', err);
		this.checkout.showError('PayPal payment failed. Please try again.');
	}

	/**
	 * Handle PayPal cancellation
	 *
	 * @since 1.0.0
	 * @param {Object} data Cancellation data
	 */
	handleCancel(data) {
		console.log('PayPal Cancelled:', data);
		this.checkout.showError('Payment was cancelled. Please try again.');
	}

	/**
	 * Handle PayPal return from redirect
	 *
	 * @since 1.0.0
	 */
	handleReturn() {
		const urlParams = new URLSearchParams(window.location.search);
		const paymentStatus = urlParams.get('splms_payment');

		// Get course_id from splmsPurchaseData (consistent with all payment gateways).
		const purchaseDataCourseId = window.splmsPurchaseData ? window.splmsPurchaseData.courseId : null;

		// Debug logging
		console.log('PayPal Return Debug - Current URL:', window.location.href);
		console.log('PayPal Return Debug - URL Params:', Object.fromEntries(urlParams));
		console.log('PayPal Return Debug - course_id:', purchaseDataCourseId);

		if (paymentStatus === 'success' || paymentStatus === 'cancelled') {
			const paypalOrderId = urlParams.get('token');
			const payerId = urlParams.get('PayerID');
			const courseId = urlParams.get('course_id') || purchaseDataCourseId;
			
			console.log('PayPal Return - Status:', paymentStatus);
			console.log('PayPal Return - Order ID:', paypalOrderId);
			console.log('PayPal Return - Payer ID:', payerId);
			console.log('PayPal Return - Course ID:', courseId);
			
			if (paymentStatus === 'success' && paypalOrderId) {
				this.captureOrder(paypalOrderId, courseId);
			} else if (paymentStatus === 'cancelled') {
				this.checkout.showError('Payment was cancelled. Please try again.');
			}
		}
	}

	/**
	 * Capture PayPal order
	 *
	 * @since 1.0.0
	 * @param {string} paypalOrderId PayPal order ID
	 * @param {string} courseId Course ID
	 * @returns {Promise} jQuery AJAX Promise
	 */
	captureOrder(paypalOrderId, courseId) {
		this.checkout.showProcessingState();
		const frontend = window.splms_frontend;
		
		const formData = {
			action: 'splms_capture_paypal_order',
			nonce: frontend.nonces.splms_nonce,
			order_id: paypalOrderId,
			course_id: courseId
		};

		return jQuery.ajax({
			url: frontend.ajax_url,
			type: 'POST',
			data: formData,
			success: (response) => {
				console.log('Capture Response:', response);
				
				if (response.success) {
					this.checkout.showSuccessState(response.data.redirect_url);
				} else {
					// Handle different error types
					if (response.data && response.data.status === 'PAYER_ACTION_REQUIRED' && response.data.approve_url) {
						console.log('PAYER_ACTION_REQUIRED - Redirecting to:', response.data.approve_url);
						window.location.href = response.data.approve_url;
					} else if (response.data && response.data.status === 'ORDER_NOT_APPROVED' && response.data.approve_url) {
						console.log('ORDER_NOT_APPROVED - Redirecting to:', response.data.approve_url);
						window.location.href = response.data.approve_url;
					} else {
						const errorMessage = response.data && response.data.message ? response.data.message : 'Payment failed. Please try again.';
						this.checkout.showError(errorMessage);
					}
				}
			},
			error: (xhr, status, error) => {
				console.error('Capture Error:', error);
				this.checkout.showError('An error occurred. Please try again.');
			}
		});
	}
}

// Export for use in checkout.js
export default SPLMSPayPal;

