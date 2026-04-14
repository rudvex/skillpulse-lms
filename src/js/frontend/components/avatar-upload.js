/**
 * Avatar Upload Component
 * Handles avatar upload functionality for  profiles
 */

export class SPLMSAvatarUpload {
	constructor() {
		this.init();
	}

	init() {
		this.bindEvents();
	}

	bindEvents() {
		// Handle file input change
		jQuery(document).on('change', '#avatar-upload', (e) => {
			this.handleFileSelect(e);
		});

		// Handle click on avatar container (support both class names)
		jQuery(document).on('click', '.avatar-upload-container, .splms-avatar-upload-container', (e) => {
			// Only trigger if clicking on the container itself, not the file input
			if (e.target === e.currentTarget || e.target.classList.contains('camera-icon') || e.target.closest('.camera-icon')) {
				jQuery('#avatar-upload').click();
			}
		});

		// Handle remove button click (support both class names)
		jQuery(document).on('click', '.avatar-remove-btn, .splms-avatar-remove-btn', (e) => {
			e.preventDefault();
			e.stopPropagation();
			this.showRemoveConfirmation();
		});

		// Handle preview confirm/cancel
		jQuery(document).on('click', '.avatar-preview-confirm', (e) => {
			e.preventDefault();
			this.confirmUpload();
		});

		jQuery(document).on('click', '.avatar-preview-cancel', (e) => {
			e.preventDefault();
			this.cancelPreview();
		});

		// Handle remove confirmation
		jQuery(document).on('click', '.avatar-remove-confirm', (e) => {
			e.preventDefault();
			this.confirmRemove();
		});

		jQuery(document).on('click', '.avatar-remove-cancel', (e) => {
			e.preventDefault();
			this.cancelRemove();
		});
	}

	handleFileSelect(e) {
		const file = e.target.files[0];
		if (!file) return;

		// Validate file type
		const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
		if (!allowedTypes.includes(file.type)) {
			this.showNotification('Please select a valid image file (JPG, PNG, or GIF).', 'error');
			return;
		}

		// Validate file size (5MB max)
		const maxSize = 5 * 1024 * 1024; // 5MB
		if (file.size > maxSize) {
			this.showNotification('File size too large. Please select an image smaller than 5MB.', 'error');
			return;
		}

		// Show preview
		this.showPreview(file);
	}

	showPreview(file) {
		const reader = new FileReader();
		reader.onload = (e) => {
			const previewUrl = e.target.result;
			this.createPreviewModal(previewUrl, file);
		};
		reader.readAsDataURL(file);
	}

	createPreviewModal(previewUrl, file) {
		// Remove existing preview modal
		jQuery('.avatar-preview-modal').remove();

		const modal = `
            <div class="avatar-preview-modal">
                <div class="avatar-preview-overlay"></div>
                <div class="avatar-preview-content">
                    <div class="avatar-preview-header">
                        <h3>Preview Your New Avatar</h3>
                        <button class="avatar-preview-close">&times;</button>
                    </div>
                    <div class="avatar-preview-body">
                        <div class="avatar-preview-image">
                            <img src="${previewUrl}" alt="Avatar Preview">
                        </div>
                        <div class="avatar-preview-info">
                            <p><strong>File:</strong> ${file.name}</p>
                            <p><strong>Size:</strong> ${this.formatFileSize(file.size)}</p>
                            <p><strong>Type:</strong> ${file.type}</p>
                        </div>
                    </div>
                    <div class="avatar-preview-actions">
                        <button class="avatar-preview-cancel btn btn-secondary">Cancel</button>
                        <button class="avatar-preview-confirm btn btn-primary">Upload Avatar</button>
                    </div>
                </div>
            </div>
        `;

		jQuery('body').append(modal);

		// Store file for upload
		this.pendingFile = file;

		// Handle close button
		jQuery('.avatar-preview-close').on('click', () => {
			this.cancelPreview();
		});

		// Handle overlay click
		jQuery('.avatar-preview-overlay').on('click', () => {
			this.cancelPreview();
		});

		// Handle escape key
		jQuery(document).on('keydown.avatarPreview', (e) => {
			if (e.key === 'Escape') {
				this.cancelPreview();
			}
		});
	}

	confirmUpload() {
		if (!this.pendingFile) return;

		// Show loading state
		this.showLoadingState();

		// Upload the file
		this.uploadAvatar(this.pendingFile);
	}

	cancelPreview() {
		// Remove preview modal
		jQuery('.avatar-preview-modal').remove();
        
		// Clear file input
		jQuery('#avatar-upload').val('');
        
		// Remove escape key handler
		jQuery(document).off('keydown.avatarPreview');
        
		// Clear pending file
		this.pendingFile = null;
	}

	uploadAvatar(file) {
		const frontend = window.splms_frontend;
		const formData = new FormData();
		formData.append('action', 'splms_upload_avatar');
		formData.append('avatar', file);
		formData.append('nonce', frontend.nonces.splms_nonce);

		jQuery.ajax({
			url: frontend.ajax_url,
			type: 'POST',
			data: formData,
			processData: false,
			contentType: false,
			success: (response) => {
				this.handleUploadSuccess(response);
			},
			error: (xhr, status, error) => {
				this.handleUploadError(xhr, status, error);
			},
			complete: () => {
				this.hideLoadingState();
			}
		});
	}

	handleUploadSuccess(response) {
		if (response.success) {
			// Remove preview modal
			jQuery('.avatar-preview-modal').remove();
            
			// Update the avatar image
			this.updateAvatarImage(response.data.avatar_url);
            
			// Show success message
			this.showNotification(response.data.message, 'success');
            
			// Clear the file input
			jQuery('#avatar-upload').val('');
            
			// Clear pending file
			this.pendingFile = null;
            
			// Remove escape key handler
			jQuery(document).off('keydown.avatarPreview');
            
			// Trigger a page reload to update all avatar instances
			setTimeout(() => {
				window.location.reload();
			}, 1500);
		} else {
			this.showNotification(response.data || 'Upload failed. Please try again.', 'error');
		}
	}

	handleUploadError() {
		this.showNotification('Network error. Please try again.', 'error');
	}

	updateAvatarImage(avatarUrl) {
		// Update the avatar image in the profile (support both class names)
		const $avatarContainer = jQuery('.avatar-upload-container img, .splms-avatar-upload-container img, .splms-avatar-image');
		if ($avatarContainer.length) {
			$avatarContainer.attr('src', avatarUrl);
		}

		// Update any other avatar instances on the page
		jQuery('img[src*="avatar"], .splms-avatar-image').each(function() {
			const currentSrc = jQuery(this).attr('src');
			if (currentSrc && (currentSrc.includes('avatar') || currentSrc.includes('profile-picture'))) {
				if (!currentSrc.includes('gravatar') && !currentSrc.includes('mystery')) {
					jQuery(this).attr('src', avatarUrl);
				}
			}
		});
	}

	showLoadingState() {
		const $container = jQuery('.avatar-upload-container, .splms-avatar-upload-container');
		$container.addClass('uploading');
		jQuery('.avatar-preview-confirm').prop('disabled', true);
		jQuery('.avatar-remove-confirm').prop('disabled', true);
        
		// Add loading overlay
		if (!$container.find('.upload-loading').length) {
			$container.append(`
                <div class="upload-loading">
                    <div class="loading-spinner"></div>
                    <span>Uploading...</span>
                </div>
            `);
		}
	}

	hideLoadingState() {
		const $container = jQuery('.avatar-upload-container, .splms-avatar-upload-container');
		$container.removeClass('uploading');
		$container.find('.upload-loading').remove();
		jQuery('.avatar-preview-confirm').prop('disabled', false);
		jQuery('.avatar-remove-confirm').prop('disabled', false);
	}

	formatFileSize(bytes) {
		if (bytes === 0) return '0 Bytes';
		const k = 1024;
		const sizes = ['Bytes', 'KB', 'MB', 'GB'];
		const i = Math.floor(Math.log(bytes) / Math.log(k));
		return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
	}

	showNotification(message, type = 'info') {
		window.SPLMSCore.helper.showNotification(message, type);
	}

	showRemoveConfirmation() {
		// Remove existing modals
		jQuery('.avatar-preview-modal, .avatar-remove-modal').remove();
		const modal = `
            <div class="avatar-remove-modal">
                <div class="avatar-preview-overlay"></div>
                <div class="avatar-preview-content">
                    <div class="avatar-preview-header">
                        <h3>Remove Avatar</h3>
                        <button class="avatar-preview-close">&times;</button>
                    </div>
                    <div class="avatar-preview-body">
                        <div class="avatar-preview-image">
                            <svg width="80" height="80" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M20 21V19C20 17.9391 19.5786 16.9217 18.8284 16.1716C18.0783 15.4214 17.0609 15 16 15H8C6.93913 15 5.92172 15.4214 5.17157 16.1716C4.42143 16.9217 4 17.9391 4 19V21" stroke="var(--splms-danger, #ef4444)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <circle cx="12" cy="7" r="4" stroke="var(--splms-danger, #ef4444)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <div class="avatar-preview-info">
                            <p><strong>Are you sure?</strong></p>
                            <p>This will remove your current profile picture and revert to the default avatar. This action cannot be undone.</p>
                        </div>
                    </div>
                    <div class="avatar-preview-actions">
                        <button class="avatar-remove-cancel btn btn-secondary">Cancel</button>
                        <button class="avatar-remove-confirm btn btn-danger">Remove Avatar</button>
                    </div>
                </div>
            </div>
        `;

		jQuery('body').append(modal);

		// Handle close button
		jQuery('.avatar-preview-close').on('click', () => {
			this.cancelRemove();
		});

		// Handle overlay click
		jQuery('.avatar-preview-overlay').on('click', () => {
			this.cancelRemove();
		});

		// Handle escape key
		jQuery(document).on('keydown.avatarRemove', (e) => {
			if (e.key === 'Escape') {
				this.cancelRemove();
			}
		});
	}

	confirmRemove() {
		// Show loading state
		this.showLoadingState();

		// Send remove request
		const frontend = window.splms_frontend;
		jQuery.ajax({
			url: frontend.ajax_url,
			type: 'POST',
			data: {
				action: 'splms_remove_avatar',
				nonce: frontend.nonces.splms_nonce
			},
			success: (response) => {
				this.handleRemoveSuccess(response);
			},
			error: (xhr, status, error) => {
				this.handleRemoveError(xhr, status, error);
			},
			complete: () => {
				this.hideLoadingState();
			}
		});
	}

	cancelRemove() {
		// Remove remove modal
		jQuery('.avatar-remove-modal').remove();
        
		// Remove escape key handler
		jQuery(document).off('keydown.avatarRemove');
	}

	handleRemoveSuccess(response) {
		if (response.success) {
			// Remove remove modal
			jQuery('.avatar-remove-modal').remove();
            
			// Update the avatar image to default
			this.updateAvatarImage(response.data.avatar_url || '');
            
			// Show success message
			this.showNotification(response.data.message || 'Avatar removed successfully.', 'success');
            
			// Remove escape key handler
			jQuery(document).off('keydown.avatarRemove');
            
			// Trigger a page reload to update all avatar instances
			setTimeout(() => {
				window.location.reload();
			}, 1500);
		} else {
			this.showNotification(response.data || 'Failed to remove avatar. Please try again.', 'error');
		}
	}

	handleRemoveError() {
		this.showNotification('Network error. Please try again.', 'error');
	}
} 