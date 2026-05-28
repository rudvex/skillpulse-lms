/**
 * SkillPulse LMS Frontend Helper
 * Common utility functions for frontend functionality
 */

export class SPLMSHelper {
	constructor() {
		// Helper class doesn't need initialization
	}

	/**
     * Get dynamic REST API URL with fallback logic
     * @param {string} endpoint - Optional endpoint path to append
     * @returns {string} Full REST API URL
     */
	getRestApiUrl(endpoint = '') {
		const apiRoot = window?.wpApiSettings?.root || window?.splms_frontend?.rest_url || window?.SPLMSCore_Data?.rest_url || '/wp-json/';
		const base = apiRoot.endsWith('/') ? apiRoot : `${apiRoot}/`;
		const path = endpoint.startsWith('/') ? endpoint.substring(1) : endpoint;
		return `${base}${path}`;
	}

	/**
     * Show notification message
     * @param {string} message - The message to display
     * @param {string} type - The type of notification (success, error, info)
     */
	showNotification(message, type = 'success') {
		// Remove existing notifications
		jQuery('.splms-notification').remove();
        
		const notification = jQuery(`
            <div class="splms-notification ${type}">
                <span class="notification-message">${message}</span>
                <button class="notification-close">&times;</button>
            </div>
        `);
        
		jQuery('body').append(notification);
        
		setTimeout(() => {
			notification.addClass('show');
		}, 100);
        
		// Auto-hide after 5 seconds
		setTimeout(() => {
			this.hideNotification(notification);
		}, 5000);
        
		// Close button
		notification.find('.notification-close').on('click', () => {
			this.hideNotification(notification);
		});
	}
    
	/**
     * Hide notification
     * @param {jQuery} $notification - The notification element to hide
     */
	hideNotification($notification) {
		$notification.removeClass('show');
		setTimeout(() => {
			$notification.remove();
		}, 300);
	}

	/**
     * Copy text to clipboard
     * @param {string} text - Text to copy
     * @returns {Promise} Promise that resolves when copy is complete
     */
	copyToClipboard(text) {
		if (navigator.clipboard) {
			return navigator.clipboard.writeText(text);
		} else {
			// For older browsers
			return new Promise((resolve, reject) => {
				const textArea = document.createElement('textarea');
				textArea.value = text;
				textArea.style.position = 'fixed';
				textArea.style.opacity = '0';
				document.body.appendChild(textArea);
				textArea.select();
				try {
					document.execCommand('copy');
					document.body.removeChild(textArea);
					resolve();
				} catch (err) {
					document.body.removeChild(textArea);
					reject(err);
				}
			});
		}
	}

	/**
     * Share content using Web Share API
     * @param {Object} options - Share options
     * @param {string} options.title - Share title
     * @param {string} options.text - Share text
     * @param {string} options.url - URL to share
     * @param {Function} options.onSuccess - Callback when share succeeds
     * @param {Function} options.onError - Callback when share fails
     */
	shareContent(options) {
		const { title, text, url, onSuccess, onError } = options;

		// Try Web Share API first
		if (navigator.share) {
			navigator.share({
				title: title || '',
				text: text || '',
				url: url || ''
			}).then(() => {
				if (onSuccess) onSuccess();
			}).catch(() => {
				// User cancelled or error occurred, use clipboard
				this.shareViaClipboard(url, title, onSuccess, onError);
			});
		} else {
			// Use clipboard
			this.shareViaClipboard(url, title, onSuccess, onError);
		}
	}

	/**
     * Share content via clipboard with modal
     * @param {string} url - URL to share
     * @param {string} title - Optional title
     * @param {Function} onSuccess - Success callback
     * @param {Function} onError - Error callback
     */
	shareViaClipboard(url, title = '', onSuccess = null, onError = null) {
		this.copyToClipboard(url).then(() => {
			this.showNotification('Link copied to clipboard!', 'success');
			if (onSuccess) onSuccess();
		}).catch(() => {
			// Clipboard failed, show share modal
			this.showShareModal(url, title);
			if (onError) onError();
		});
	}

	/**
     * Show share modal with copy and social share options
     * @param {string} url - URL to share
     * @param {string} title - Optional title for sharing
     */
	showShareModal(url, title = '') {
		// Remove existing modals
		jQuery('.splms-share-modal').remove();

		const modal = jQuery(`
            <div class="splms-share-modal" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: var(--splms-overlay-bg, rgba(0,0,0,0.5)); z-index: 9999; display: flex; align-items: center; justify-content: center;">
                <div style="background: var(--splms-background, white); padding: 20px; border-radius: var(--splms-border-radius-lg, 8px); max-width: 400px; width: 90%;">
                    <h3 style="margin-top: 0;">Share ${title ? title : 'Link'}</h3>
                    <p>Share this link with others:</p>
                    <input type="text" class="splms-share-url-input" value="${url}" readonly style="width: 100%; padding: 8px; margin: 10px 0; border: 1px solid var(--splms-border-color-light, #ddd); border-radius: var(--splms-border-radius, 4px); box-sizing: border-box;">
                    <div style="display: flex; gap: 10px; margin-top: 15px;">
                        <button class="splms-share-copy-btn" style="flex: 1; padding: 8px; background: var(--splms-primary, #007cba); color: white; border: none; border-radius: var(--splms-border-radius, 4px); cursor: pointer;">Copy Link</button>
                        <button class="splms-share-close-btn" style="flex: 1; padding: 8px; background: var(--splms-gray-500, #6c757d); color: white; border: none; border-radius: var(--splms-border-radius, 4px); cursor: pointer;">Close</button>
                    </div>
                    <div class="splms-social-share" style="margin-top: 15px; display: flex; gap: 10px;">
                        <a href="https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(url)}" target="_blank" style="flex: 1; padding: 8px; background: var(--splms-social-linkedin, #0077b5); color: white; text-align: center; border-radius: var(--splms-border-radius, 4px); text-decoration: none;">LinkedIn</a>
                        <a href="https://twitter.com/intent/tweet?url=${encodeURIComponent(url)}&text=${encodeURIComponent(title || 'Check this out!')}" target="_blank" style="flex: 1; padding: 8px; background: var(--splms-social-twitter, #1da1f2); color: white; text-align: center; border-radius: var(--splms-border-radius, 4px); text-decoration: none;">Twitter</a>
                    </div>
                </div>
            </div>
        `);

		jQuery('body').append(modal);

		// Handle copy button
		modal.find('.splms-share-copy-btn').on('click', () => {
			const input = modal.find('.splms-share-url-input')[0];
			input.select();
			this.copyToClipboard(url).then(() => {
				this.showNotification('Link copied to clipboard!', 'success');
				modal.remove();
			}).catch(() => {
				// Use execCommand for older browsers
				try {
					document.execCommand('copy');
					this.showNotification('Link copied to clipboard!', 'success');
					modal.remove();
				} catch (err) {
					this.showNotification('Failed to copy link. Please select and copy manually.', 'error');
				}
			});
		});

		// Handle close button and overlay click
		modal.find('.splms-share-close-btn, .splms-share-modal').on('click', (e) => {
			if (e.target === e.currentTarget || jQuery(e.target).hasClass('splms-share-close-btn')) {
				modal.remove();
			}
		});

		// Auto-select URL input
		setTimeout(() => {
			modal.find('.splms-share-url-input').focus().select();
		}, 100);
	}

	/**
     * Debounce function
     * @param {Function} func - Function to debounce
     * @param {number} wait - Wait time in milliseconds
     * @param {boolean} immediate - Execute immediately
     */
	debounce(func, wait, immediate) {
		let timeout;
		return function executedFunction(...args) {
			const later = () => {
				timeout = null;
				if (!immediate) func.apply(this, args);
			};
			const callNow = immediate && !timeout;
			clearTimeout(timeout);
			timeout = setTimeout(later, wait);
			if (callNow) func.apply(this, args);
		};
	}

	/**
     * Throttle function
     * @param {Function} func - Function to throttle
     * @param {number} limit - Limit in milliseconds
     */
	throttle(func, limit) {
		let inThrottle;
		return function(...args) {
			if (!inThrottle) {
				func.apply(this, args);
				inThrottle = true;
				setTimeout(() => inThrottle = false, limit);
			}
		};
	}

	/**
     * Check if element is in viewport
     * @param {HTMLElement} element - Element to check
     * @returns {boolean}
     */
	isInViewport(element) {
		const rect = element.getBoundingClientRect();
		return (
			rect.top >= 0 &&
            rect.left >= 0 &&
            rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
            rect.right <= (window.innerWidth || document.documentElement.clientWidth)
		);
	}

	/**
     * Sanitize HTML string
     * @param {string} str - String to sanitize
     * @returns {string}
     */
	sanitizeHTML(str) {
		const temp = document.createElement('div');
		temp.textContent = str;
		return temp.innerHTML;
	}

	/**
     * Format currency
     * @param {number} amount - Amount to format
     * @param {string} currency - Currency code
     * @returns {string}
     */
	formatCurrency(amount, currency = 'USD') {
		return new Intl.NumberFormat('en-US', {
			style: 'currency',
			currency: currency
		}).format(amount);
	}

	/**
     * Format date
     * @param {Date|string} date - Date to format
     * @param {string} format - Format string
     * @returns {string}
     */
	formatDate(date, format = 'short') {
		const dateObj = new Date(date);
		return dateObj.toLocaleDateString('en-US', {
			year: 'numeric',
			month: format === 'long' ? 'long' : 'short',
			day: 'numeric'
		});
	}

	/**
     * Get URL parameter
     * @param {string} name - Parameter name
     * @returns {string|null}
     */
	getUrlParameter(name) {
		const urlParams = new URLSearchParams(window.location.search);
		return urlParams.get(name);
	}

	/**
     * Set URL parameter
     * @param {string} name - Parameter name
     * @param {string} value - Parameter value
     */
	setUrlParameter(name, value) {
		const url = new URL(window.location);
		url.searchParams.set(name, value);
		window.history.pushState({}, '', url);
	}

	/**
     * Remove URL parameter
     * @param {string} name - Parameter name
     */
	removeUrlParameter(name) {
		const url = new URL(window.location);
		url.searchParams.delete(name);
		window.history.pushState({}, '', url);
	}

	/**
     * Validate email address
     * @param {string} email - Email to validate
     * @returns {boolean}
     */
	isValidEmail(email) {
		const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
		return emailRegex.test(email);
	}

	/**
     * Generate random string
     * @param {number} length - Length of string
     * @returns {string}
     */
	generateRandomString(length = 10) {
		const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
		let result = '';
		for (let i = 0; i < length; i++) {
			result += chars.charAt(Math.floor(Math.random() * chars.length));
		}
		return result;
	}

	/**
     * Storage helper methods
     */
	get storage() {
		return {
			set: (key, value) => {
				try {
					localStorage.setItem(key, JSON.stringify(value));
				} catch (e) {
					window.console?.warn('Failed to save to localStorage:', e);
				}
			},
        
			get: (key, defaultValue = null) => {
				try {
					const item = localStorage.getItem(key);
					return item ? JSON.parse(item) : defaultValue;
				} catch (e) {
					window.console?.warn('Failed to read from localStorage:', e);
					return defaultValue;
				}
			},
        
			remove: (key) => {
				try {
					localStorage.removeItem(key);
				} catch (e) {
					window.console?.warn('Failed to remove from localStorage:', e);
				}
			},
        
			clear: () => {
				try {
					localStorage.clear();
				} catch (e) {
					window.console?.warn('Failed to clear localStorage:', e);
				}
			}
		};
	}
} 