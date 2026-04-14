/**
 * Base Modal Class - Abstract class for reusable modals
 * 
 * This abstract class provides common modal functionality that can be extended
 * by specific modal implementations. It uses WordPress wp.template() system
 * for rendering and provides a standardized API for modal management.
 *
 * @since 1.0.0
 * @abstract
 */
export class SPLMSBaseModal {
    
	/**
     * Constructor
     * 
     * @param {Object} config Modal configuration
     * @param {string} config.templateId - The ID for wp.template() (without 'tmpl-' prefix)
     * @param {string} config.modalId - Unique modal identifier for CSS classes
     * @param {Object} config.defaultData - Default data structure for the modal
     */
	constructor(config = {}) {
		if (this.constructor === SPLMSBaseModal) {
			throw new Error('SPLMSBaseModal is abstract and cannot be instantiated directly');
		}
        
		this.config = {
			templateId: '',
			modalId: '',
			defaultData: {},
			closeOnBackdrop: true,
			closeOnEscape: true,
			...config
		};
        
		this.template = null;
		this.modalElement = null;
		this.isOpen = false;
		this.eventHandlers = new Map();
        
		this.init();
	}
    
	/**
     * Initialize the modal
     * @protected
     */
	init() {
		// Initialize wp.template
		this.initTemplate();
        
		// Setup global event listeners
		this.setupGlobalEvents();
        
		// Call child class initialization
		if (typeof this.onInit === 'function') {
			this.onInit();
		}
	}
    
	/**
     * Initialize WordPress template
     * @protected
     */
	initTemplate() {
		if (!this.config.templateId) {
			throw new Error('templateId is required for modal');
		}
        
		if (typeof wp === 'undefined' || typeof wp.template === 'undefined') {
			throw new Error('WordPress wp.template is not available');
		}
        
		this.template = wp.template(this.config.templateId);
	}
    
	/**
     * Setup global event listeners
     * @protected
     */
	setupGlobalEvents() {
		// Escape key handler
		if (this.config.closeOnEscape) {
			this.addEventHandler('keydown', (e) => {
				if (e.key === 'Escape' && this.isOpen) {
					this.close();
				}
			}, document);
		}
	}
    
	/**
     * Open the modal
     * 
     * @param {Object} data Data to pass to the template
     * @returns {Promise<void>}
     */
	async open(data = {}) {
		try {
			// Merge with default data
			const modalData = { ...this.config.defaultData, ...data };
            
			// Validate data if child class provides validation
			if (typeof this.validateData === 'function') {
				const validation = this.validateData(modalData);
				if (!validation.isValid) {
					throw new Error(validation.message || 'Invalid modal data');
				}
			}
            
			// Generate modal HTML
			const modalHtml = this.template(modalData);
            
			// Remove existing modal if any
			this.destroy();
            
			// Create and append modal
			this.createModal(modalHtml);
            
			// Setup modal-specific events
			this.setupModalEvents();
            
			// Show modal
			this.show();
            
			// Call child class hook
			if (typeof this.onOpen === 'function') {
				await this.onOpen(modalData);
			}
            
		} catch (error) {
			window.console?.error('Error opening modal:', error);
			this.handleError(error);
		}
	}
    
	/**
     * Create modal element and append to DOM
     * @protected
     */
	createModal(html) {
		// Remove any existing modals first
		jQuery('.' + this.config.modalId).remove();
		jQuery('.splms-modal').remove(); // Remove all old modals
        
		// Create modal container
		this.modalElement = jQuery(html);
        
		// Add base modal classes
		this.modalElement.addClass('splms-modal');
		if (this.config.modalId) {
			this.modalElement.addClass(this.config.modalId);
		}
        
		// Append to body
		jQuery('body').append(this.modalElement);
	}
    
	/**
     * Setup modal-specific event handlers
     * @protected
     */
	setupModalEvents() {
		if (!this.modalElement) return;
        
		// Close button handler
		this.addEventHandler('click', () => {
			this.close();
		}, this.modalElement.find('.splms-modal-close, .modal-close'));
        
		// Backdrop click handler
		if (this.config.closeOnBackdrop) {
			this.addEventHandler('click', (e) => {
				if (jQuery(e.target).hasClass('modal-overlay') || 
                    jQuery(e.target).hasClass('splms-modal')) {
					this.close();
				}
			}, this.modalElement);
		}
        
		// Form submission handler (if modal contains forms)
		this.addEventHandler('submit', (e) => {
			this.handleFormSubmit(e);
		}, this.modalElement.find('form'));
        
		// Call child class event setup
		if (typeof this.setupEvents === 'function') {
			this.setupEvents();
		}
	}
    
	/**
     * Show the modal with animation
     * @protected
     */
	show() {
		if (!this.modalElement) return;
        
		this.modalElement.css('display', 'block');
		jQuery('body').addClass('splms-modal-open');
		this.isOpen = true;
        
		// Add animation class after display
		requestAnimationFrame(() => {
			this.modalElement.addClass('splms-modal-visible is-open');
		});
	}
    
	/**
     * Close the modal
     * 
     * @returns {Promise<void>}
     */
	async close() {
		if (!this.isOpen || !this.modalElement) return;
        
		try {
			// Call child class hook
			if (typeof this.onClose === 'function') {
				const shouldClose = await this.onClose();
				if (shouldClose === false) return; // Allow child to prevent closing
			}
            
			// Hide modal with animation
			this.modalElement.fadeOut(300, () => {
				this.hide();
			});
            
		} catch (error) {
			window.console?.error('Error closing modal:', error);
			this.hide(); // Force close on error
		}
	}
    
	/**
     * Hide the modal and cleanup
     * @protected
     */
	hide() {
		if (this.modalElement) {
			this.modalElement.css('display', 'none').removeClass('splms-modal-visible is-open');
		}
		jQuery('body').removeClass('splms-modal-open');
		this.isOpen = false;
	}
    
	/**
     * Handle form submissions in the modal
     * @protected
     */
	async handleFormSubmit(e) {
		e.preventDefault();
        
		if (typeof this.onFormSubmit === 'function') {
			await this.onFormSubmit(e);
		}
	}
    
	/**
     * Add event handler and track it for cleanup
     * @protected
     */
	addEventHandler(event, handler, element = this.modalElement) {
		if (!element || !element.length) return;
        
		const $element = jQuery(element);
		$element.on(event, handler);
        
		// Track for cleanup
		if (!this.eventHandlers.has($element[0])) {
			this.eventHandlers.set($element[0], []);
		}
		this.eventHandlers.get($element[0]).push({ event, handler });
	}
	/**
     * Handle errors
     * @protected
     */
	handleError(error) {
		const message = error.message || 'An unexpected error occurred';
		window.SPLMSCore.helper.showNotification(message, 'error');
	}
    
	/**
     * Destroy the modal and cleanup
     */
	destroy() {
		// Remove event handlers
		this.eventHandlers.forEach((handlers, element) => {
			const $element = jQuery(element);
			handlers.forEach(({ event, handler }) => {
				$element.off(event, handler);
			});
		});
		this.eventHandlers.clear();
        
		// Remove modal element
		if (this.modalElement) {
			this.modalElement.remove();
			this.modalElement = null;
		}
        
		// Reset state
		this.isOpen = false;
		jQuery('body').removeClass('splms-modal-open');
        
		// Call child class cleanup
		if (typeof this.onDestroy === 'function') {
			this.onDestroy();
		}
	}
    
	/**
     * Get current modal data from form inputs
     * @protected
     */
	getFormData() {
		if (!this.modalElement) return {};
        
		const form = this.modalElement.find('form').first();
		if (!form.length) return {};
        
		const formData = new FormData(form[0]);
		const data = {};
        
		for (let [key, value] of formData.entries()) {
			data[key] = value;
		}
        
		return data;
	}
    
	/**
     * Update modal loading state
     * @protected
     */
	setLoading(isLoading, message = 'Loading...') {
		if (!this.modalElement) return;
        
		const submitBtn = this.modalElement.find('button[type="submit"]');
        
		if (isLoading) {
			submitBtn.prop('disabled', true);
			if (!submitBtn.data('original-text')) {
				submitBtn.data('original-text', submitBtn.text());
			}
			submitBtn.text(message);
		} else {
			submitBtn.prop('disabled', false);
			const originalText = submitBtn.data('original-text');
			if (originalText) {
				submitBtn.text(originalText);
			}
		}
	}
    
	// ==========================================
	// ABSTRACT METHODS - Override in child classes
	// ==========================================
    
	/**
     * Called after modal initialization
     * Override in child classes for specific setup
     * @abstract
     */
	onInit() {
		// Override in child classes
	}
    
	/**
     * Called when modal is opened
     * Override in child classes for specific logic
     * @abstract
     * @param {Object} data Modal data
     */
	async onOpen() {
		// Override in child classes
	}
    
	/**
     * Called when modal is closing
     * Override in child classes for validation
     * Return false to prevent closing
     * @abstract
     * @returns {boolean|Promise<boolean>}
     */
	async onClose() {
		// Override in child classes
		return true;
	}
    
	/**
     * Called when modal is destroyed
     * Override in child classes for cleanup
     * @abstract
     */
	onDestroy() {
		// Override in child classes
	}
    
	/**
     * Handle form submission
     * Override in child classes for specific form handling
     * @abstract
     * @param {Event} e Form submit event
     */
	async onFormSubmit() {
		// Override in child classes
	}
    
	/**
     * Setup additional event handlers
     * Override in child classes for specific events
     * @abstract
     */
	setupEvents() {
		// Override in child classes
	}
    
	/**
     * Validate modal data
     * Override in child classes for data validation
     * @abstract
     * @param {Object} data Data to validate
     * @returns {Object} {isValid: boolean, message?: string}
     */
	validateData() {
		// Override in child classes
		return { isValid: true };
	}
}
