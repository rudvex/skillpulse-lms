/**
 * Stripe Payment Integration
 *
 * Handles Stripe Checkout integration for course purchases.
 *
 * @package SkillPulse_LMS
 * @subpackage Frontend
 * @since 1.0.0
 */

/**
 * Stripe Payment Handler Class
 *
 * @since 1.0.0
 */
class SPLMSStripe {
	
	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 * @param {Object} checkoutInstance Checkout class instance for callbacks
	 */
	constructor(checkoutInstance) {
		this.checkout = checkoutInstance;
		this.stripe = null;
		this.elements = null;
		this.paymentElement = null;
	}

	/**
	 * Initialize Stripe integration
	 *
	 * @since 1.0.0
	 */
	initialize() {
		if (typeof Stripe === 'undefined') {
			console.error('Stripe.js not loaded');
			return;
		}

		const container = document.getElementById('splms-stripe-button-container');
		if (!container) {
			console.error('Stripe container not found');
			return;
		}

		// Create payment intent first
		this.createPaymentIntent().then((response) => {
			console.log('Stripe payment intent response:', response);
			
			if (response.success) {
				this.renderElements(response.data);
			} else {
				const errorMessage = response.data && response.data.message ? response.data.message : 'Failed to initialize Stripe payment';
				console.error('Stripe payment intent failed:', response);
				this.checkout.showError(errorMessage);
			}
		}).catch((error) => {
			console.error('Stripe initialization error:', error);
			const errorMessage = error.message || 'Failed to initialize Stripe payment';
			this.checkout.showError(errorMessage);
		});
	}

	/**
	 * Create Stripe payment intent
	 *
	 * @since 1.0.0
	 * @returns {Promise} jQuery AJAX Promise
	 */
	createPaymentIntent() {
		const frontend = window.splms_frontend;
		const formData = {
			action: 'splms_create_stripe_payment_intent',
			nonce: frontend.nonces.splms_nonce,
		};

		// Check for section-based purchase data.
		if (window.splmsPurchaseData) {
			const purchaseData = window.splmsPurchaseData;
			if (purchaseData.type === 'sections' && purchaseData.selectedSections && purchaseData.selectedSections.length > 0) {
				// Section-based purchase.
				formData.purchase_type = 'sections';
				formData.selected_sections = JSON.stringify(purchaseData.selectedSections);
                formData.course_id = purchaseData.courseId
			} else {
				// Full course purchase.
				formData.purchase_type = 'full_course';
                formData.course_id = purchaseData.courseId
			}
		}

		return jQuery.ajax({
			url: frontend.ajax_url,
			type: 'POST',
			data: formData
		});
	}

	/**
	 * Render Stripe Elements
	 *
	 * @since 1.0.0
	 * @param {Object} paymentIntentData Payment intent data
	 */
	renderElements(paymentIntentData) {
		const container = document.getElementById('splms-stripe-button-container');
		
		// Clear container and unmount existing elements to prevent duplicates
		if (this.paymentElement) {
			try {
				this.paymentElement.unmount();
			} catch (e) {
				// Element may not be mounted, ignore error
				console.log('Stripe element unmount:', e);
			}
		}
		container.innerHTML = '';
		
		// Initialize Stripe
		this.stripe = Stripe(paymentIntentData.publishable_key);
		this.elements = this.stripe.elements({
			clientSecret: paymentIntentData.client_secret
		});

		// Create payment element without billing details collection (we have our own form)
		this.paymentElement = this.elements.create('payment', {
			fields: {
				billingDetails: 'never'
			}
		});
		this.paymentElement.mount('#splms-stripe-button-container');

		// Create submit button
		const submitButton = document.createElement('button');
		submitButton.id = 'splms-stripe-submit';
		submitButton.className = 'splms-btn splms-btn-primary';

		// Customize button text for upgrades
		const isUpgrade = window.splmsPurchaseData && window.splmsPurchaseData.isUpgrade;
		submitButton.textContent = isUpgrade ? 'Upgrade to Full Course' : 'Complete Payment';

		submitButton.style.width = '100%';
		submitButton.style.marginTop = '20px';
		
		container.appendChild(submitButton);

		// Handle form submission
		submitButton.addEventListener('click', (event) => {
			event.preventDefault();
			this.handleSubmit(paymentIntentData);
		});
	}

	/**
	 * Handle Stripe form submission
	 *
	 * @since 1.0.0
	 * @param {Object} paymentIntentData Payment intent data
	 */
	handleSubmit(paymentIntentData) {
		const submitButton = document.getElementById('splms-stripe-submit');
		submitButton.disabled = true;
		submitButton.textContent = 'Processing...';

		// Get billing data if available
		const billingData = this.checkout.getBillingData ? this.checkout.getBillingData() : {};

		// Build confirm params with billing details
		const confirmParams = {
			return_url: this.getReturnUrl(paymentIntentData.payment_id)
		};

		// Add billing details if available
		if (billingData.first_name || billingData.last_name) {
			confirmParams.payment_method_data = {
				billing_details: {
					name: `${billingData.first_name || ''} ${billingData.last_name || ''}`.trim(),
					email: billingData.email || '',
					phone: billingData.phone || '',
					address: {
						line1: billingData.address_1 || '',
						line2: billingData.address_2 || '',
						city: billingData.city || '',
						state: billingData.state || '',
						postal_code: billingData.postcode || '',
						country: billingData.country || 'US'
					}
				}
			};
		}

		this.stripe.confirmPayment({
			elements: this.elements,
			confirmParams: confirmParams
		}).then((result) => {
			console.log('Stripe confirmation result:', result);
			
			if (result.error) {
				// Payment failed - show detailed error
				console.error('Stripe payment error:', result.error);
				const errorMessage = result.error.message || 'Payment processing failed. Please try again.';
				this.checkout.showError(errorMessage);
				submitButton.disabled = false;
				submitButton.textContent = 'Complete Payment';
			} else {
				// Payment succeeded
				const frontend = window.splms_frontend;
				console.log('Stripe payment succeeded:', result);
				this.processPayment(paymentIntentData.payment_intent_id, frontend.course_id);
			}
		}).catch((error) => {
			console.error('Stripe confirmation error:', error);
			const errorMessage = error.message || 'Payment processing failed. Please try again.';
			this.checkout.showError(errorMessage);
			submitButton.disabled = false;
			submitButton.textContent = 'Complete Payment';
		});
	}

	/**
	 * Process Stripe payment
	 *
	 * @since 1.0.0
	 * @param {string} paymentIntentId Payment intent ID
	 * @param {string} courseId Course ID
	 * @returns {Promise} jQuery AJAX Promise
	 */
	processPayment(paymentIntentId, courseId) {
		const frontend = window.splms_frontend;
		const formData = {
			action: 'splms_process_stripe_payment',
			nonce: frontend.nonces.splms_nonce,
			payment_intent_id: paymentIntentId,
			course_id: courseId
		};

		return jQuery.ajax({
			url: frontend.ajax_url,
			type: 'POST',
			data: formData,
			success: (response) => {
				console.log('Stripe Payment Response:', response);
				
				if (response.success) {
					this.checkout.showSuccessState(response.data.redirect_url);
				} else {
					const errorMessage = response.data && response.data.message ? response.data.message : 'Payment processing failed. Please try again.';
					this.checkout.showError(errorMessage);
				}
			},
			error: (xhr, status, error) => {
				console.error('Stripe Payment Error:', error);
				this.checkout.showError('An error occurred while processing your payment. Please try again.');
			}
		});
	}

	/**
	 * Get Stripe return URL
	 *
	 * @since 1.0.0
	 * @param {string} paymentId Payment ID
	 * @returns {string} Return URL
	 */
	getReturnUrl(paymentId) {
		// Extract token from current URL.
		const urlParams = new URLSearchParams(window.location.search);
		const token = urlParams.get('token');

		// Build return URL with token for secure authentication.
		let returnUrl = `${window.location.origin}/purchase/`;
		if (token) {
			returnUrl += `?token=${encodeURIComponent(token)}&splms_payment=success&payment_id=${paymentId}`;
		} else {
			// Fallback (should not happen in normal flow).
			const frontend = window.splms_frontend;
			returnUrl += `?course_id=${frontend.course_id}&splms_payment=success&payment_id=${paymentId}`;
		}

		return returnUrl;
	}

	/**
	 * Cleanup Stripe elements
	 *
	 * @since 1.0.0
	 */
	cleanup() {
		if (this.paymentElement) {
			try {
				this.paymentElement.unmount();
				this.paymentElement = null;
			} catch (e) {
				// Element may not be mounted, ignore error
				console.log('Stripe cleanup:', e);
			}
		}
	}
}

// Export for use in checkout.js
export default SPLMSStripe;

