/**
 * Signup Review Modal - Admin Interface
 * 
 * Extends SPLMSBaseModal for reviewing pending signups in the admin area.
 * Provides a comprehensive interface for administrators to activate, delete, or resend
 * activation emails for pending user signups.
 *
 * @since 1.0.0
 */
import { SPLMSBaseModal } from '../core/base-modal.js';

export class SPLMSSignupReviewModal extends SPLMSBaseModal {
    
	constructor() {
		super({
			templateId: 'splms-admin-signup-review',
			modalId: 'splms-admin-signup-review-modal',
			defaultData: {
				signup_id: 0,
				user_login: '',
				user_email: '',
				display_name: '',
				registered: '',
				activation_key: '',
				meta: {},
				status: 'pending'
			},
			closeOnBackdrop: true,
			closeOnEscape: true
		});
        
		this.currentSignupData = null;
		this.isProcessing = false;
	}
    
	/**
     * Initialize the modal
     */
	onInit() {
		// Any additional initialization can go here
	}
    
	/**
     * Open modal with signup ID to load signup data
     */
	async openForSignup(signupId) {
		try {
			// Show loading modal first
			await this.open({ loading: true });
            
			// Load signup data
			const signupData = await this.loadSignupData(signupId);
            
			// Update modal with real data
			await this.updateModalContent(signupData);
            
		} catch (error) {
			this.handleError(error);
			this.close();
		}
	}
    
	/**
     * Load signup data via AJAX
     */
	async loadSignupData(signupId) {
		try {
			const response = await SPLMSCore.adminAjax.getSignupDetails(signupId);
			if (response.success) {
				return response.data;
			} else {
				throw new Error(response.data || 'Failed to load signup data');
			}
		} catch (error) {
			throw new Error('Network error loading signup');
		}
	}
    
	/**
     * Update modal content with signup data
     */
	async updateModalContent(data) {
		this.currentSignupData = data;
        
		// Prepare data with proper formatting
		const formattedData = {
			...this.config.defaultData,
			...data,
			registered_formatted: this.formatDate(data.registered),
			loading: false
		};
        
		// Regenerate modal with real data
		const modalHtml = this.template(formattedData);
        
		// Replace modal content
		this.modalElement.find('.modal-content').replaceWith(jQuery(modalHtml).find('.modal-content'));
        
		// Re-setup events for new content
		this.setupModalEvents();
	}
    
	/**
     * Setup modal-specific events
     */
	setupEvents() {
		if (!this.modalElement) return;
        
		// Modal activate button
		this.addEventHandler('click', async () => {
			await this.processSignupAction('activate');
		}, this.modalElement.find('.activate-signup-btn'));
        
		// Modal delete button
		this.addEventHandler('click', async () => {
			await this.processSignupAction('delete');
		}, this.modalElement.find('.delete-signup-btn'));
        
		// Modal resend activation button
		this.addEventHandler('click', async () => {
			await this.processSignupAction('resend');
		}, this.modalElement.find('.resend-activation-btn'));
	}
    
	/**
     * Process signup action (activate/delete/resend)
     */
	async processSignupAction(action) {
		if (this.isProcessing) return;
        
		let confirmMessage = '';
		if (action === 'activate') {
			confirmMessage = `Are you sure you want to activate the signup for ${this.currentSignupData.user_email}?`;
		} else if (action === 'delete') {
			confirmMessage = `Are you sure you want to delete the signup for ${this.currentSignupData.user_email}? This cannot be undone.`;
		} else if (action === 'resend') {
			confirmMessage = `Are you sure you want to resend the activation email to ${this.currentSignupData.user_email}?`;
		}
        
		// Use custom confirmation instead of browser confirm()
		if (!await this.showCustomConfirm(confirmMessage)) return;
        
		this.isProcessing = true;
		this.setLoading(true, 'Processing...');
        
		try {
			const result = await this.submitSignupAction(action);
            
			this.showNotification(result.message || 'Signup processed successfully', 'success');
            
			// Close modal and refresh page after short delay
			setTimeout(() => {
				this.close();
				setTimeout(() => {
					window.location.reload();
				}, 500);
			}, 1000);
            
		} catch (error) {
			this.showNotification(error.message || 'Error processing signup', 'error');
		} finally {
			this.isProcessing = false;
			this.setLoading(false);
		}
	}

	/**
     * Show custom confirmation dialog instead of browser confirm()
     */
	showCustomConfirm(message) {
		return new Promise((resolve) => {
			// Create confirmation modal HTML
			const confirmModal = jQuery(`
                <div class="splms-confirm-modal" style="
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: var(--splms-overlay-bg, rgba(0,0,0,0.5));
                    z-index: 999999;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                ">
                    <div style="
                        background: var(--splms-background, white);
                        padding: 30px;
                        border-radius: var(--splms-border-radius-lg, 8px);
                        max-width: 400px;
                        text-align: center;
                        box-shadow: var(--splms-shadow-xl, 0 10px 30px rgba(0,0,0,0.3));
                    ">
                        <h3 style="margin: 0 0 20px 0; color: var(--splms-text-color, #333);">Confirm Action</h3>
                        <p style="margin: 0 0 25px 0; color: var(--splms-text-secondary, #666); line-height: 1.5;">${message}</p>
                        <div style="display: flex; gap: 15px; justify-content: center;">
                            <button class="button button-secondary" style="min-width: 80px;">Cancel</button>
                            <button class="button button-primary" style="min-width: 80px;">Confirm</button>
                        </div>
                    </div>
                </div>
            `);

			// Add event listeners
			confirmModal.find('.button-secondary').on('click', () => {
				confirmModal.remove();
				resolve(false);
			});

			confirmModal.find('.button-primary').on('click', () => {
				confirmModal.remove();
				resolve(true);
			});

			// Add to body
			jQuery('body').append(confirmModal);
		});
	}
    
	/**
     * Validate modal data
     */
	validateData(data) {
		if (data.loading) {
			return { isValid: true }; // Loading state is always valid
		}
        
		if (!data.signup_id || !data.user_email) {
			return { 
				isValid: false, 
				message: 'Required signup data is missing' 
			};
		}
        
		return { isValid: true };
	}
    
	/**
     * Called when modal is closing
     */
	async onClose() {
		if (this.isProcessing) {
			return false; // Prevent closing while processing
		}
        
		this.currentSignupData = null;
		return true;
	}
    
	/**
     * Called when modal is destroyed
     */
	onDestroy() {
		this.currentSignupData = null;
		this.isProcessing = false;
	}
    
	/**
     * Process quick action without opening modal (called from table quick actions)
     */
	async processQuickAction(signupId, action, userEmail) {
		let confirmMessage = '';
        
		if (action === 'activate') {
			confirmMessage = `${SPLMSCore_Data.strings.quick_activate_confirm} ${userEmail}?`;
		} else if (action === 'delete') {
			confirmMessage = `${SPLMSCore_Data.strings.quick_delete_confirm} ${userEmail}?`;
		} else if (action === 'resend') {
			confirmMessage = `${SPLMSCore_Data.strings.quick_resend_confirm} ${userEmail}?`;
		}
        
		// Use custom confirmation instead of browser confirm()
		if (!await this.showCustomConfirm(confirmMessage)) return;
        
		try {
			const result = await this.submitSignupAction(action, signupId);
			this.showNotification(result.message || 'Signup processed successfully', 'success');
            
			// Refresh page to update table
			setTimeout(() => {
				window.location.reload();
			}, 1000);
            
		} catch (error) {
			this.showNotification(error.message || 'Error processing signup', 'error');
		}
	}
    
	/**
     * Submit signup action with custom signup ID (for quick actions)
     */
	async submitSignupAction(action, signupId = null) {
		const targetSignupId = signupId || this.currentSignupData.signup_id;
        
		try {
			const response = await SPLMSCore.adminAjax.processSignup(targetSignupId, action);
			if (response.success) {
				return response.data;
			} else {
				throw new Error(response.data || 'Failed to process signup');
			}
		} catch (error) {
			throw new Error('Network error processing signup');
		}
	}
    
	/**
     * Format date for display
     */
	formatDate(dateString) {
		if (!dateString) return '';
        
		try {
			const date = new Date(dateString);
			return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
		} catch (error) {
			return dateString;
		}
	}
    
	/**
     * Static method to initialize signup admin page functionality
     * This replaces any inline JavaScript in the PHP file
     */
	static initializeSignupAdminPage() {
		// Check if we're on the signup admin page
		if (typeof SPLMSCore_Data === 'undefined' || 
            !SPLMSCore_Data.is_signup_admin_page) {
			return; // Not on the signup admin page
		}
        
		let reviewModal = null;
        
		// Helper function to get or create modal instance
		const getModalInstance = () => {
			if (!reviewModal) {
				reviewModal = new SPLMSSignupReviewModal();
			}
			return reviewModal;
		};
        
		// Event listeners
		jQuery(document).ready(function($) {
			// Review signup links - opens the detailed modal
			$('.review-signup').on('click', function(e) {
				e.preventDefault();
				const signupId = $(this).data('signup-id');
				getModalInstance().openForSignup(signupId);
			});
            
			// Quick actions are handled by the modal system for consistency
			$('.quick-activate, .quick-delete, .quick-resend').on('click', function(e) {
				e.preventDefault();
				const signupId = $(this).data('signup-id');
				const userEmail = $(this).data('user-email');
				let action = '';
                
				if ($(this).hasClass('quick-activate')) {
					action = 'activate';
				} else if ($(this).hasClass('quick-delete')) {
					action = 'delete';
				} else if ($(this).hasClass('quick-resend')) {
					action = 'resend';
				}
                
				// Process quick action through modal system
				getModalInstance().processQuickAction(signupId, action, userEmail);
			});
		});
	}
}

// Auto-initialization removed - now called from admin/index.js
