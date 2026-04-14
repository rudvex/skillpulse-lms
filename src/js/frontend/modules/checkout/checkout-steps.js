/**
 * Checkout Step Navigation
 *
 * Handles step-by-step progression through the checkout process.
 *
 * @package SkillPulse_LMS
 * @subpackage Frontend
 * @since 1.0.0
 */

/**
 * Checkout Steps Manager Class
 *
 * @since 1.0.0
 */
class SPLMSCheckoutSteps {

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	constructor() {
		this.currentStep = 1;
		this.totalSteps = 0;
		this.showSectionPricing = false;
		this.init();
	}

	/**
	 * Initialize checkout steps
	 *
	 * @since 1.0.0
	 */
	init() {
		// Prevent re-initialization.
		if (this.initialized) {
			return;
		}
		this.initialized = true;

		// Clean up any existing step containers to prevent duplicates.
		document.querySelectorAll('.splms-checkout-step').forEach(step => {
			// Unwrap the step container but keep its content.
			const parent = step.parentNode;
			while (step.firstChild) {
				parent.insertBefore(step.firstChild, step);
			}
			step.remove();
		});

		// Detect if section pricing is enabled.
		this.showSectionPricing = document.querySelector('.splms-pricing-options') !== null;

		// Calculate total steps.
		// If section pricing: Step 1 = Pricing Options, Step 2 = Payment & Summary.
		// If no section pricing: Step 1 = Payment & Summary.
		this.totalSteps = this.showSectionPricing ? 2 : 1;

		// Wrap existing sections in step containers.
		this.wrapStepsInContainers();

		// Add step navigation buttons.
		this.addNavigationButtons();

		// Setup event listeners.
		this.setupEventListeners();

		// Show first step.
		this.showStep(1);
	}

	/**
	 * Wrap existing sections in step containers
	 *
	 * @since 1.0.0
	 */
	wrapStepsInContainers() {
		let stepNumber = 1;

		// Step 1: Pricing options (if section pricing enabled).
		if (this.showSectionPricing) {
			const pricingSection = document.querySelector('.splms-pricing-options');
			if (pricingSection) {
				this.wrapInStepContainer(pricingSection, stepNumber++);
			}
		}

		// Step: Billing info, payment methods and order summary together in final step.
		const billingSection = document.querySelector('.splms-billing-info');
		const paymentSection = document.querySelector('.splms-payment-methods');
		const summarySection = document.querySelector('.splms-order-summary');
		const paymentContainer = document.querySelector('#splms-payment-button-container');

		if (billingSection && paymentSection && summarySection) {
			// Create a wrapper for payment method step.
			const paymentStepWrapper = document.createElement('div');
			paymentStepWrapper.className = 'splms-step-content-wrapper';

			// Wrap all sections together.
			billingSection.parentNode.insertBefore(paymentStepWrapper, billingSection);
			paymentStepWrapper.appendChild(billingSection);
			paymentStepWrapper.appendChild(paymentSection);
			paymentStepWrapper.appendChild(summarySection);

			// Add payment button container to the same step.
			if (paymentContainer) {
				paymentStepWrapper.appendChild(paymentContainer);
			}

			this.wrapInStepContainer(paymentStepWrapper, stepNumber);
		}
	}

	/**
	 * Wrap a section in a step container
	 *
	 * @since 1.0.0
	 * @param {Element} element Element to wrap
	 * @param {number} stepNum Step number
	 */
	wrapInStepContainer(element, stepNum) {
		const wrapper = document.createElement('div');
		wrapper.className = 'splms-checkout-step';
		wrapper.setAttribute('data-step', stepNum);
		wrapper.style.display = 'none';

		element.parentNode.insertBefore(wrapper, element);
		wrapper.appendChild(element);
	}

	/**
	 * Add navigation buttons to each step
	 *
	 * @since 1.0.0
	 */
	addNavigationButtons() {
		const steps = document.querySelectorAll('.splms-checkout-step');

		steps.forEach((step, index) => {
			const stepNum = parseInt(step.getAttribute('data-step'));

			// Check if actions already exist (avoid duplicates).
			if (step.querySelector('.splms-step-actions')) {
				return;
			}

			const actionsDiv = document.createElement('div');
			actionsDiv.className = 'splms-step-actions';

			// Back button (not for first step).
			if (stepNum > 1) {
				const backBtn = document.createElement('button');
				backBtn.type = 'button';
				backBtn.className = 'splms-btn splms-btn-secondary splms-back-btn';
				backBtn.setAttribute('data-target-step', stepNum - 1);
				backBtn.innerHTML = '<span class="splms-btn-arrow">←</span> Back';
				actionsDiv.appendChild(backBtn);
			}

			// Continue button (only for first step if there are multiple steps).
			if (stepNum < this.totalSteps) {
				const continueBtn = document.createElement('button');
				continueBtn.type = 'button';
				continueBtn.className = 'splms-btn splms-btn-primary splms-continue-btn';
				continueBtn.setAttribute('data-current-step', stepNum);

				// Button text.
				if (stepNum === 1 && this.showSectionPricing) {
					continueBtn.innerHTML = 'Continue to Payment <span class="splms-btn-arrow">→</span>';
				} else {
					continueBtn.innerHTML = 'Continue <span class="splms-btn-arrow">→</span>';
				}

				actionsDiv.appendChild(continueBtn);
			}

			// Only add actions div if it has buttons.
			if (actionsDiv.children.length > 0) {
				step.appendChild(actionsDiv);
			}
		});
	}

	/**
	 * Setup event listeners
	 *
	 * @since 1.0.0
	 */
	setupEventListeners() {
		// Continue buttons.
		document.querySelectorAll('.splms-continue-btn').forEach(btn => {
			btn.addEventListener('click', (e) => {
				e.preventDefault();
				const currentStep = parseInt(btn.getAttribute('data-current-step'));

				if (this.validateStep(currentStep)) {
					this.goToStep(currentStep + 1);
				}
			});
		});

		// Back buttons.
		document.querySelectorAll('.splms-back-btn').forEach(btn => {
			btn.addEventListener('click', (e) => {
				e.preventDefault();
				const targetStep = parseInt(btn.getAttribute('data-target-step'));
				this.goToStep(targetStep);
			});
		});

		// Update step header numbers dynamically.
		this.updateStepHeaders();
	}

	/**
	 * Update step header numbers to be clickable for completed steps
	 *
	 * @since 1.0.0
	 */
	updateStepHeaders() {
		// Only update headers in pricing options step.
		const pricingStep = document.querySelector('.splms-pricing-options .splms-step-header');
		if (pricingStep) {
			const stepNum = pricingStep.querySelector('.splms-step-number');
			if (stepNum) {
				stepNum.style.cursor = 'pointer';
				stepNum.addEventListener('click', (e) => {
					if (this.currentStep > 1) {
						this.goToStep(1);
					}
				});
			}
		}
	}

	/**
	 * Go to a specific step
	 *
	 * @since 1.0.0
	 * @param {number} step Step number
	 */
	goToStep(step) {
		if (step < 1 || step > this.totalSteps) {
			return;
		}

		this.currentStep = step;
		this.showStep(step);
		this.scrollToTop();
	}

	/**
	 * Show a specific step
	 *
	 * @since 1.0.0
	 * @param {number} step Step number
	 */
	showStep(step) {
		// Hide all steps.
		document.querySelectorAll('.splms-checkout-step').forEach(s => {
			s.style.display = 'none';
		});

		// Show target step.
		const targetStep = document.querySelector(`.splms-checkout-step[data-step="${step}"]`);
		if (targetStep) {
			targetStep.style.display = 'block';
			this.updateStepIndicators();
		}
	}

	/**
	 * Update step indicators/progress
	 *
	 * @since 1.0.0
	 */
	updateStepIndicators() {
		// Update pricing options header if it exists and we're past step 1.
		const pricingHeader = document.querySelector('.splms-pricing-options .splms-step-header');
		if (pricingHeader) {
			if (this.currentStep > 1) {
				pricingHeader.classList.add('completed');
				pricingHeader.classList.remove('active');
			} else {
				pricingHeader.classList.add('active');
				pricingHeader.classList.remove('completed');
			}
		}
	}

	/**
	 * Validate current step before proceeding
	 *
	 * @since 1.0.0
	 * @param {number} step Step number
	 * @returns {boolean} Whether step is valid
	 */
	validateStep(step) {
		// Step 1 with section pricing: Validate purchase option selection.
		if (step === 1 && this.showSectionPricing) {
			const purchaseType = document.querySelector('input[name="purchase_type"]:checked');

			if (!purchaseType) {
				alert('Please select a purchase option.');
				return false;
			}

			// If sections selected, ensure at least one is checked.
			if (purchaseType.value === 'sections') {
				const selectedSections = document.querySelectorAll('.splms-section-checkbox:checked:not(:disabled)');
				if (selectedSections.length === 0) {
					alert('Please select at least one section to purchase.');
					return false;
				}
			}

			return true;
		}

		return true;
	}

	/**
	 * Scroll to top of checkout container
	 *
	 * @since 1.0.0
	 */
	scrollToTop() {
		const container = document.querySelector('.splms-checkout-wrapper');
		if (container) {
			container.scrollIntoView({ behavior: 'smooth', block: 'start' });
		}
	}
}

// Export for use in checkout.js.
// NOTE: Do not auto-initialize here - it's instantiated by SPLMSCheckout class.
export default SPLMSCheckoutSteps;
