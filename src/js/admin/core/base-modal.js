/**
 * Base Modal Class for Admin - Reusable admin modal system
 * 
 * This is the admin version of SPLMSBaseModal, optimized for WordPress admin interface.
 * It provides the same functionality as the frontend version but with admin-specific styling
 * and integration with WordPress admin UI patterns.
 *
 * @since 1.0.0
 * @abstract
 */
export class SPLMSBaseModal {
    
	/**
     * Constructor
     * 
     * @param {Object} config Modal configuration
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
		this.initTemplate();
		this.setupGlobalEvents();
        
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
        
		// Check if the template exists
		const templateElement = document.getElementById('tmpl-' + this.config.templateId);
		if (!templateElement) {
			throw new Error('Template not found: tmpl-' + this.config.templateId);
		}
        
		this.template = wp.template(this.config.templateId);
	}
    
	/**
     * Setup global event listeners
     * @protected
     */
	setupGlobalEvents() {
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
     */
	async open(data = {}) {
		try {
			const modalData = { ...this.config.defaultData, ...data };
            
			if (typeof this.validateData === 'function') {
				const validation = this.validateData(modalData);
				if (!validation.isValid) {
					throw new Error(validation.message || 'Invalid modal data');
				}
			}
            
			const modalHtml = this.template(modalData);
            
			this.destroy();
			this.createModal(modalHtml);
			this.setupModalEvents();
			this.show();
            
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
		this.modalElement = jQuery(html);
		this.modalElement.addClass('splms-admin-modal');
        
		if (this.config.modalId) {
			this.modalElement.addClass(this.config.modalId);
		}
        
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
                    jQuery(e.target).hasClass('splms-admin-modal')) {
					this.close();
				}
			}, this.modalElement);
		}
        
		// Form submission handler
		this.addEventHandler('submit', (e) => {
			this.handleFormSubmit(e);
		}, this.modalElement.find('form'));
        
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
		jQuery('body').addClass('splms-admin-modal-open');
		this.isOpen = true;
        
		requestAnimationFrame(() => {
			this.modalElement.addClass('splms-modal-visible is-open');
		});
	}
    
	/**
     * Close the modal
     */
	async close() {
		if (!this.isOpen || !this.modalElement) return;
        
		try {
			if (typeof this.onClose === 'function') {
				const shouldClose = await this.onClose();
				if (shouldClose === false) return;
			}
            
			this.modalElement.removeClass('splms-modal-visible');
            
			setTimeout(() => {
				this.hide();
			}, 300);
            
		} catch (error) {
			window.console?.error('Error closing modal:', error);
			this.hide();
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
		jQuery('body').removeClass('splms-admin-modal-open');
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
        
		if (!this.eventHandlers.has($element[0])) {
			this.eventHandlers.set($element[0], []);
		}
		this.eventHandlers.get($element[0]).push({ event, handler });
	}
    
	/**
     * Show admin notification
     * @protected
     */
	showNotification(message, type = 'info') {
		// Use WordPress admin notices style
		const noticeClass = type === 'error' ? 'notice-error' : 
			type === 'success' ? 'notice-success' : 
				type === 'warning' ? 'notice-warning' : 'notice-info';
        
		const notification = jQuery(`
            <div class="notice ${noticeClass} is-dismissible splms-admin-notification">
                <p>${message}</p>
                <button type="button" class="notice-dismiss">
                    <span class="screen-reader-text">Dismiss this notice.</span>
                </button>
            </div>
        `);
        
		// Position at top of admin content
		const adminNotices = jQuery('.wp-header-end');
		if (adminNotices.length) {
			adminNotices.after(notification);
		} else {
			jQuery('#wpbody-content').prepend(notification);
		}
        
		// Auto-hide after 5 seconds
		setTimeout(() => {
			notification.fadeOut(300, function() {
				jQuery(this).remove();
			});
		}, 5000);
        
		// Manual dismiss handler
		notification.find('.notice-dismiss').on('click', () => {
			notification.fadeOut(300, function() {
				jQuery(this).remove();
			});
		});
	}
    
	/**
     * Handle errors
     * @protected
     */
	handleError(error) {
		const message = error.message || 'An unexpected error occurred';
		this.showNotification(message, 'error');
	}
    
	/**
     * Update modal loading state
     * @protected
     */
	setLoading(isLoading, message = 'Loading...') {
		if (!this.modalElement) return;
        
		const submitBtns = this.modalElement.find('button[type="submit"], .button-primary, .button-secondary');
        
		if (isLoading) {
			submitBtns.each(function() {
				const $btn = jQuery(this);
				$btn.prop('disabled', true);
				if (!$btn.data('original-text')) {
					$btn.data('original-text', $btn.text());
				}
				$btn.text(message);
			});
		} else {
			submitBtns.each(function() {
				const $btn = jQuery(this);
				$btn.prop('disabled', false);
				const originalText = $btn.data('original-text');
				if (originalText) {
					$btn.text(originalText);
				}
			});
		}
	}
    
	/**
     * Destroy the modal and cleanup
     */
	destroy() {
		this.eventHandlers.forEach((handlers, element) => {
			const $element = jQuery(element);
			handlers.forEach(({ event, handler }) => {
				$element.off(event, handler);
			});
		});
		this.eventHandlers.clear();
        
		if (this.modalElement) {
			this.modalElement.remove();
			this.modalElement = null;
		}
        
		this.isOpen = false;
		jQuery('body').removeClass('splms-admin-modal-open');
        
		if (typeof this.onDestroy === 'function') {
			this.onDestroy();
		}
	}
    
	// ==========================================
	// ABSTRACT METHODS - Override in child classes
	// ==========================================
    
	onInit() {
		// Override in child classes
	}
    
	async onOpen() {
		// Override in child classes
	}
    
	async onClose() {
		// Override in child classes
		return true;
	}
    
	onDestroy() {
		// Override in child classes
	}
    
	async onFormSubmit() {
		// Override in child classes
	}
    
	setupEvents() {
		// Override in child classes
	}
    
	validateData() {
		// Override in child classes
		return { isValid: true };
	}
}
