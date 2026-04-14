/**
 * Checkout Page JavaScript
 *
 * Handles payment method integration and payment processing on the checkout page.
 *
 * @package SkillPulse_LMS
 * @subpackage Templates
 * @since 1.0.0
 */

import SPLMSRazorpay from './payments/razorpay.js';
import SPLMSStripe from './payments/stripe.js';
import SPLMSPayPal from './payments/paypal.js';
import SPLMSCheckoutSteps from './checkout-steps.js';

/**
 * Checkout Page Handler Class
 *
 * @since 1.0.0
 */
class SPLMSCheckout {

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	constructor() {
		this.paymentHandlers = {};
		this.stepNavigation = null;
		this.init();
	}

	/**
	 * Initialize the checkout page
	 *
	 * @since 1.0.0
	 */
	init() {
		// Only initialize on checkout pages
		if (!jQuery('.splms-checkout-container').length) {
			return;
		}

		// Initialize step navigation system
		this.stepNavigation = new SPLMSCheckoutSteps();

		// Initialize payment handlers
		this.paymentHandlers.razorpay = new SPLMSRazorpay(this);
		this.paymentHandlers.stripe = new SPLMSStripe(this);
		this.paymentHandlers.paypal = new SPLMSPayPal(this);

		this.bindEvents();
		this.initializePaymentMethods();
		this.handlePayPalReturn();
	}

	/**
	 * Bind event handlers
	 *
	 * @since 1.0.0
	 */
	bindEvents() {
		// Payment method change
		jQuery(document).on('change', 'input[name="payment_method"]', this.handlePaymentMethodChange.bind(this));

		// Try again button
		jQuery(document).on('click', '.splms-try-again-btn', this.handleTryAgain.bind(this));

		// Form submission
		jQuery(document).on('submit', '#splms-checkout-form', this.handleFormSubmit.bind(this));

		// Billing info auto-save
		jQuery(document).on('change', '.splms-billing-fields input, .splms-billing-fields select', this.saveBillingInfo.bind(this));
	}

	/**
	 * Save billing information
	 *
	 * @since 1.0.0
	 */
	saveBillingInfo() {
		const billingData = {
			first_name: jQuery('#billing_first_name').val(),
			last_name: jQuery('#billing_last_name').val(),
			email: jQuery('#billing_email').val(),
			phone: jQuery('#billing_phone').val(),
			address_1: jQuery('#billing_address_1').val(),
			address_2: jQuery('#billing_address_2').val(),
			city: jQuery('#billing_city').val(),
			state: jQuery('#billing_state').val(),
			postcode: jQuery('#billing_postcode').val(),
			country: jQuery('#billing_country').val()
		};

		// Store in session/window for payment gateways to access
		window.splmsBillingData = billingData;

		// Optionally save to server via AJAX (for persistent storage)
		this.saveBillingToServer(billingData);
	}

	/**
	 * Save billing info to server
	 *
	 * @since 1.0.0
	 * @param {Object} billingData Billing data
	 */
	saveBillingToServer(billingData) {
		const frontend = window.splms_frontend;

		jQuery.ajax({
			url: frontend.ajax_url,
			type: 'POST',
			data: {
				action: 'splms_save_billing_info',
				nonce: frontend.nonces.splms_nonce,
				billing_data: billingData
			},
			success: (response) => {
				if (response.success) {
					console.log('Billing info saved successfully');
				}
			},
			error: (xhr, status, error) => {
				console.error('Failed to save billing info:', error);
			}
		});
	}

	/**
	 * Get billing data
	 *
	 * @since 1.0.0
	 * @returns {Object} Billing data
	 */
	getBillingData() {
		return window.splmsBillingData || {
			first_name: jQuery('#billing_first_name').val(),
			last_name: jQuery('#billing_last_name').val(),
			email: jQuery('#billing_email').val(),
			phone: jQuery('#billing_phone').val(),
			address_1: jQuery('#billing_address_1').val(),
			address_2: jQuery('#billing_address_2').val(),
			city: jQuery('#billing_city').val(),
			state: jQuery('#billing_state').val(),
			postcode: jQuery('#billing_postcode').val(),
			country: jQuery('#billing_country').val()
		};
	}


	/**
	 * Initialize payment methods
	 *
	 * @since 1.0.0
	 */
	initializePaymentMethods() {
		// Show the first available payment method
		const firstMethod = jQuery('input[name="payment_method"]:checked').val();
		if (firstMethod) {
			this.showPaymentMethod(firstMethod);
		}
	}

	/**
	 * Show payment method interface
	 *
	 * @since 1.0.0
	 * @param {string} methodId Payment method ID
	 */
	showPaymentMethod(methodId) {
		// Clean up any existing Stripe elements before switching
		if (this.paymentHandlers.stripe && methodId !== 'stripe') {
			this.paymentHandlers.stripe.cleanup();
		}
		
		// Hide all payment button containers
		jQuery('.splms-payment-container > div').hide();
		
		// Show the selected payment method container
		jQuery(`#splms-${methodId}-button-container`).show();
		
		// Initialize the specific payment method
		if (this.paymentHandlers[methodId]) {
			this.paymentHandlers[methodId].initialize();
		} else {
			console.log(`Payment method ${methodId} not implemented yet`);
		}
	}


	/**
	 * Handle payment method change
	 *
	 * @since 1.0.0
	 * @param {Event} e Event object
	 */
	handlePaymentMethodChange(e) {
		const method = jQuery(e.target).val();
		console.log('Payment method changed to:', method);
		
		// Show the selected payment method
		this.showPaymentMethod(method);
		
		// Hide any existing error states
		this.hideErrorState();
	}

	/**
	 * Handle form submission
	 *
	 * @since 1.0.0
	 * @param {Event} e Event object
	 */
	handleFormSubmit(e) {
		e.preventDefault();
		console.log('Form submitted');
	}

	/**
	 * Handle try again button click
	 *
	 * @since 1.0.0
	 * @param {Event} e Event object
	 */
	handleTryAgain(e) {
		e.preventDefault();
		this.hideErrorState();
		this.showPaymentForm();
	}

	/**
	 * Show processing state
	 *
	 * @since 1.0.0
	 */
	showProcessingState() {
		jQuery('#splms-payment-form').hide();
		jQuery('#splms-payment-processing').show();
		jQuery('#splms-payment-error').hide();
		jQuery('#splms-payment-success').hide();
	}

	/**
	 * Show success state
	 *
	 * @since 1.0.0
	 * @param {string} redirectUrl URL to redirect to
	 */
	showSuccessState(redirectUrl) {
		jQuery('#splms-payment-processing').hide();
		jQuery('#splms-payment-success').show();
		
		// Redirect after 2 seconds
		setTimeout(() => {
			window.location.href = redirectUrl;
		}, 2000);
	}

	/**
	 * Show error state
	 *
	 * @since 1.0.0
	 * @param {string} message Error message
	 */
	showError(message) {
		jQuery('#splms-payment-processing').hide();
		jQuery('#splms-payment-form').show();
		
		jQuery('.splms-error-message').text(message);
		jQuery('#splms-payment-error').show();
		
		// Hide error after 5 seconds
		setTimeout(() => {
			this.hideErrorState();
		}, 5000);
	}

	/**
	 * Hide error state
	 *
	 * @since 1.0.0
	 */
	hideErrorState() {
		jQuery('#splms-payment-error').hide();
	}

	/**
	 * Show payment form
	 *
	 * @since 1.0.0
	 */
	showPaymentForm() {
		jQuery('#splms-payment-form').show();
		jQuery('#splms-payment-processing').hide();
		jQuery('#splms-payment-error').hide();
		jQuery('#splms-payment-success').hide();
	}

	/**
	 * Handle PayPal return from redirect
	 *
	 * @since 1.0.0
	 */
	handlePayPalReturn() {
		if (this.paymentHandlers.paypal) {
			this.paymentHandlers.paypal.handleReturn();
		}
	}
}

// Initialize checkout when DOM is ready
jQuery(document).ready(function() {
	new SPLMSCheckout();
});

// Export for potential external use
export default SPLMSCheckout;
