/**
 * Razorpay Payment Integration
 *
 * Handles Razorpay Checkout integration for course purchases.
 *
 * @package SkillPulse_LMS
 * @subpackage Frontend
 * @since 1.0.0
 */

/**
 * Razorpay Payment Handler Class
 *
 * @since 1.0.0
 */
class SPLMSRazorpay {
	
	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 * @param {Object} checkoutInstance Checkout class instance for callbacks
	 */
	constructor(checkoutInstance) {
		this.checkout = checkoutInstance;
		this.razorpay = null;
		this.orderData = null;
		this.init();
	}

	/**
	 * Initialize the Razorpay integration
	 *
	 * @since 1.0.0
	 */
	init() {
		// Load Razorpay Checkout script
		this.loadRazorpayScript();
	}

	/**
	 * Load Razorpay Checkout script
	 *
	 * @since 1.0.0
	 */
	loadRazorpayScript() {
		if (typeof Razorpay !== 'undefined') {
			return;
		}

		const script = document.createElement('script');
		script.src = 'https://checkout.razorpay.com/v1/checkout.js';
		script.async = true;
		script.onload = () => {
			console.log('Razorpay Checkout script loaded');
		};
		script.onerror = () => {
			console.error('Failed to load Razorpay Checkout script');
		};
		document.head.appendChild(script);
	}

	/**
	 * Create Razorpay order
	 *
	 * @since 1.0.0
	 * @param {number} courseId Course ID
	 * @returns {Promise} jQuery AJAX Promise
	 */
	createOrder(courseId) {
		const frontend = window.splms_frontend;
		const formData = {
			action: 'splms_create_razorpay_order',
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

		return jQuery.ajax({
			url: frontend.ajax_url,
			type: 'POST',
			data: formData
		});
	}

	/**
	 * Verify Razorpay payment
	 *
	 * @since 1.0.0
	 * @param {string} paymentId Internal payment ID
	 * @param {string} razorpayOrderId Razorpay order ID
	 * @param {string} razorpayPaymentId Razorpay payment ID
	 * @param {string} razorpaySignature Razorpay signature
	 * @param {number} courseId Course ID
	 * @returns {Promise} jQuery AJAX Promise
	 */
	verifyPayment(paymentId, razorpayOrderId, razorpayPaymentId, razorpaySignature, courseId) {
		const frontend = window.splms_frontend;
		const formData = {
			action: 'splms_verify_razorpay_payment',
			nonce: frontend.nonces.splms_nonce,
			payment_id: paymentId,
			razorpay_order_id: razorpayOrderId,
			razorpay_payment_id: razorpayPaymentId,
			razorpay_signature: razorpaySignature,
			course_id: courseId
		};

		return jQuery.ajax({
			url: frontend.ajax_url,
			type: 'POST',
			data: formData
		});
	}

	/**
	 * Initialize Razorpay checkout
	 *
	 * @since 1.0.0
	 * @param {Object} orderData Order data from backend
	 */
	initializeCheckout(orderData) {
		if (typeof Razorpay === 'undefined') {
			console.error('Razorpay Checkout script not loaded');
			return;
		}

		this.orderData = orderData;

		const options = {
			key: orderData.key_id,
			amount: orderData.amount,
			currency: orderData.currency,
			name: jQuery('meta[name="site-name"]').attr('content') || 'SkillPulse LMS',
			description: 'Course Purchase',
			order_id: orderData.order_id,
			handler: (response) => {
				this.handlePaymentSuccess(response);
			},
			prefill: {
				name: '',
				email: '',
				contact: ''
			},
			theme: {
				color: '#0073aa'
			},
			modal: {
				ondismiss: () => {
					this.handlePaymentCancel();
				}
			}
		};

		this.razorpay = new Razorpay(options);
		this.razorpay.open();
	}

	/**
	 * Handle payment success
	 *
	 * @since 1.0.0
	 * @param {Object} response Razorpay payment response
	 */
	handlePaymentSuccess(response) {
		console.log('Razorpay Payment Success:', response);

		// Show processing state
		if (this.checkout) {
			this.checkout.showProcessingState();
		}

		// Get course_id from splmsPurchaseData (consistent with all payment gateways).
		const courseId = window.splmsPurchaseData ? window.splmsPurchaseData.courseId : null;

		// Verify payment with backend
		this.verifyPayment(
			this.orderData.payment_id,
			response.razorpay_order_id,
			response.razorpay_payment_id,
			response.razorpay_signature,
			courseId
		).then((result) => {
			console.log('Razorpay Verification Response:', result);

			if (result.success) {
				// Show success state and redirect
				if (this.checkout) {
					this.checkout.showSuccessState(result.data.redirect_url);
				} else {
					// If checkout class not available
					window.location.href = result.data.redirect_url;
				}
			} else {
				const errorMessage = result.data && result.data.message 
					? result.data.message 
					: 'Payment verification failed. Please contact support.';
				this.showError(errorMessage);
			}
		}).catch((error) => {
			console.error('Razorpay Verification Error:', error);
			this.showError('An error occurred while verifying your payment. Please contact support.');
		});
	}

	/**
	 * Handle payment cancellation
	 *
	 * @since 1.0.0
	 */
	handlePaymentCancel() {
		console.log('Razorpay Payment Cancelled');
		this.showError('Payment was cancelled. Please try again.');
	}

	/**
	 * Show error message
	 *
	 * @since 1.0.0
	 * @param {string} message Error message
	 */
	showError(message) {
		if (this.checkout) {
			this.checkout.showError(message);
		} else {
			// Error display
			alert(message);
		}
	}

	/**
	 * Process payment (called from checkout page)
	 *
	 * @since 1.0.0
	 * @param {number} courseId Course ID
	 */
	processPayment(courseId) {
		// Show processing state
		if (this.checkout) {
			this.checkout.showProcessingState();
		}

		// Create order
		this.createOrder(courseId).then((response) => {
			console.log('Razorpay Order Response:', response);

			if (response.success) {
				// Initialize Razorpay checkout
				this.initializeCheckout(response.data);
			} else {
				const errorMessage = response.data && response.data.message 
					? response.data.message 
					: 'Failed to create payment order. Please try again.';
				this.showError(errorMessage);
			}
		}).catch((error) => {
			console.error('Razorpay Order Creation Error:', error);
			this.showError('An error occurred while creating the payment order. Please try again.');
		});
	}

	/**
	 * Initialize Razorpay (called from checkout page)
	 *
	 * @since 1.0.0
	 */
	initialize() {
		const container = document.getElementById('splms-razorpay-button-container');
		if (!container) {
			console.error('Razorpay container not found');
			return;
		}

		// Create purchase button
		const purchaseButton = document.createElement('button');
		purchaseButton.id = 'splms-razorpay-purchase-btn';
		purchaseButton.className = 'splms-btn splms-btn-primary';

		// Customize button text for upgrades
		const isUpgrade = window.splmsPurchaseData && window.splmsPurchaseData.isUpgrade;
		purchaseButton.textContent = isUpgrade ? 'Upgrade to Full Course' : 'Purchase Now';

		purchaseButton.style.width = '100%';
		purchaseButton.style.marginTop = '20px';

		// Clear container and add button
		container.innerHTML = '';
		container.appendChild(purchaseButton);

		// Handle button click
		purchaseButton.addEventListener('click', (event) => {
			event.preventDefault();
			// Get course_id from splmsPurchaseData (consistent with all payment gateways).
			const courseId = window.splmsPurchaseData ? window.splmsPurchaseData.courseId : null;
			this.processPayment(courseId);
		});
	}
}

// Export for use in checkout.js
export default SPLMSRazorpay;

