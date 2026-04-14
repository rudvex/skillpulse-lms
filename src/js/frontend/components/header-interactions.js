/**
 * Header Interactions JavaScript
 * 
 * Handles mobile menu, user dropdown, notifications, and scroll effects
 * 
 * @package SkillPulse_LMS
 * @since 1.0.0
 */

(function($) {
	'use strict';

	/**
     * Header Interactions Class
     */
	class HeaderInteractions {
		constructor() {
			this.header = $('.splms-header');
			this.mobileToggle = $('.splms-mobile-menu-toggle');
			this.mobileMenu = $('.splms-mobile-menu');
			this.userMenuToggle = $('.splms-user-menu-toggle');
			this.userDropdown = $('.splms-user-dropdown');
			this.notificationBtn = $('.splms-notification-btn');
			this.notificationDropdown = $('.splms-notifications-dropdown');
			this.body = $('body');
            
			this.isScrolled = false;
			this.scrollThreshold = 50;
            
			this.init();
		}

		/**
         * Initialize all header interactions
         */
		init() {
			this.bindEvents();
			this.handleScrollEffect();
			this.setupAccessibility();
		}

		/**
         * Bind all event listeners
         */
		bindEvents() {
			// Mobile menu toggle
			this.mobileToggle.on('click', (e) => {
				e.preventDefault();
				this.toggleMobileMenu();
			});

			// User menu toggle
			this.userMenuToggle.on('click', (e) => {
				e.preventDefault();
				this.toggleUserMenu();
			});

			// Notification toggle - now handled by SPLMSNotifications class

			// Close dropdowns when clicking outside
			$(document).on('click', (e) => {
				this.handleOutsideClick(e);
			});

			// Handle escape key
			$(document).on('keydown', (e) => {
				if (e.key === 'Escape') {
					this.handleEscapeKey();
				}
			});

			// Handle scroll for header effects
			$(window).on('scroll', () => {
				this.handleScrollEffect();
			});

			// Handle window resize
			$(window).on('resize', () => {
				this.handleResize();
			});
		}

		/**
         * Toggle mobile menu
         */
		toggleMobileMenu() {
			const isOpen = this.mobileToggle.attr('aria-expanded') === 'true';
            
			this.mobileToggle.attr('aria-expanded', !isOpen);
			this.mobileMenu.toggleClass('is-open', !isOpen);
			this.body.toggleClass('mobile-menu-open', !isOpen);

			// Prevent body scroll when menu is open
			if (!isOpen) {
				this.body.css('overflow', 'hidden');
			} else {
				this.body.css('overflow', '');
			}

			// Focus management
			if (!isOpen) {
				setTimeout(() => {
					this.mobileMenu.find('a').first().focus();
				}, 100);
			}
		}

		/**
         * Toggle user dropdown menu
         */
		toggleUserMenu() {
			const isOpen = this.userMenuToggle.attr('aria-expanded') === 'true';
            
			// Close notifications if open
			this.closeNotifications();
            
			this.userMenuToggle.attr('aria-expanded', !isOpen);
			this.userDropdown.toggleClass('is-open', !isOpen);

			// Focus management
			if (!isOpen) {
				setTimeout(() => {
					this.userDropdown.find('a').first().focus();
				}, 100);
			}
		}

		/**
         * Notification functions moved to SPLMSNotifications class for unified handling
         */

		/**
         * Notification count functions moved to SPLMSNotifications class
         */

		/**
         * Close user menu
         */
		closeUserMenu() {
			this.userMenuToggle.attr('aria-expanded', 'false');
			this.userDropdown.removeClass('is-open');
		}

		/**
         * Close notifications - delegated to SPLMSNotifications class
         */
		closeNotifications() {
			if (window.splmsNotifications) {
				window.splmsNotifications.closeDropdown();
			}
		}

		/**
         * Close mobile menu
         */
		closeMobileMenu() {
			this.mobileToggle.attr('aria-expanded', 'false');
			this.mobileMenu.removeClass('is-open');
			this.body.removeClass('mobile-menu-open').css('overflow', '');
		}

		/**
         * Handle clicks outside dropdowns
         */
		handleOutsideClick(e) {
			const target = $(e.target);

			// Check if click is outside user menu
			if (!target.closest('.splms-user-menu').length) {
				this.closeUserMenu();
			}

			// Check if click is outside mobile menu
			if (!target.closest('.splms-mobile-menu, .splms-mobile-menu-toggle').length) {
				this.closeMobileMenu();
			}
            
			// Notifications are handled by SPLMSNotifications class
		}

		/**
         * Handle escape key press
         */
		handleEscapeKey() {
			this.closeUserMenu();
			this.closeNotifications();
			this.closeMobileMenu();
		}

		/**
         * Handle scroll effect on header
         */
		handleScrollEffect() {
			const scrollTop = $(window).scrollTop();
			const shouldAddClass = scrollTop > this.scrollThreshold;

			if (shouldAddClass !== this.isScrolled) {
				this.isScrolled = shouldAddClass;
				this.header.toggleClass('is-scrolled', this.isScrolled);
			}
		}

		/**
         * Handle window resize
         */
		handleResize() {
			const windowWidth = $(window).width();
            
			// Close mobile menu on larger screens
			if (windowWidth > 1024) {
				this.closeMobileMenu();
			}

			// Close dropdowns on mobile
			if (windowWidth <= 768) {
				this.closeUserMenu();
				this.closeNotifications();
			}
		}

		/**
         * Setup accessibility features
         */
		setupAccessibility() {
			// Add ARIA labels and roles
			this.userDropdown.attr({
				'role': 'menu',
				'aria-labelledby': this.userMenuToggle.attr('id') || 'user-menu-toggle'
			});

			this.notificationDropdown.attr({
				'role': 'menu',
				'aria-labelledby': this.notificationBtn.attr('id') || 'notification-btn'
			});

			// Add keyboard navigation for dropdowns
			this.setupKeyboardNavigation();
		}

		/**
         * Setup keyboard navigation
         */
		setupKeyboardNavigation() {
			// User dropdown navigation
			this.userDropdown.on('keydown', 'a', (e) => {
				this.handleDropdownKeyNavigation(e, this.userDropdown);
			});

			// Notification dropdown navigation
			this.notificationDropdown.on('keydown', 'a, button', (e) => {
				this.handleDropdownKeyNavigation(e, this.notificationDropdown);
			});

			// Mobile menu navigation
			this.mobileMenu.on('keydown', 'a', (e) => {
				if (e.key === 'Tab') {
					this.handleMobileMenuTabNavigation(e);
				}
			});
		}

		/**
         * Handle keyboard navigation in dropdowns
         */
		handleDropdownKeyNavigation(e, dropdown) {
			const focusableElements = dropdown.find('a, button').not(':disabled');
			const currentIndex = focusableElements.index(e.target);

			switch (e.key) {
			case 'ArrowDown': {
				e.preventDefault();
				const nextIndex = (currentIndex + 1) % focusableElements.length;
				focusableElements.eq(nextIndex).focus();
				break;
			}

			case 'ArrowUp': {
				e.preventDefault();
				const prevIndex = (currentIndex - 1 + focusableElements.length) % focusableElements.length;
				focusableElements.eq(prevIndex).focus();
				break;
			}

			case 'Home':
				e.preventDefault();
				focusableElements.first().focus();
				break;

			case 'End':
				e.preventDefault();
				focusableElements.last().focus();
				break;
			}
		}

		/**
         * Handle tab navigation in mobile menu
         */
		handleMobileMenuTabNavigation(e) {
			const focusableElements = this.mobileMenu.find('a, button').not(':disabled');
			const firstElement = focusableElements.first();
			const lastElement = focusableElements.last();

			if (e.shiftKey && e.target === firstElement[0]) {
				e.preventDefault();
				lastElement.focus();
			} else if (!e.shiftKey && e.target === lastElement[0]) {
				e.preventDefault();
				firstElement.focus();
			}
		}

		/**
         * Trigger modal (for footer links and dashboard)
         */
		// triggerModal removed
	}

	/**
     * Initialize header interactions when DOM is ready
     */
	$(document).ready(function() {
		new HeaderInteractions();
	});

})(jQuery);
