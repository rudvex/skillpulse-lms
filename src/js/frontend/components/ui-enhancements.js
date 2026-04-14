/**
 * SkillPulse LMS UI Enhancements
 * Handles lazy loading, smooth scroll, responsive features, and other UI improvements
 */

export class SPLMSUIEnhancements {
	constructor() {
		this.init();
	}

	init() {
		this.bindEvents();
		this.initLazyLoading();
		this.initSmoothScroll();
		this.initLoadingStates();
		this.initResponsiveNav();
		this.initSidebarEnhancements();
		this.initCollapsibleSections();
		this.addNotificationStyles();
	}

	bindEvents() {
		// Form submissions
		// jQuery('form').on('submit', this.handleFormSubmit.bind(this));
        
		// AJAX buttons
		// jQuery(document).on('click', '[data-ajax="true"]', this.handleAjaxButton.bind(this));
        
		// Sidebar enhancements
		jQuery('.course-info-widget .info-item').on('mouseenter', this.handleInfoItemHover.bind(this));
		jQuery('.course-info-widget .info-item').on('mouseleave', this.handleInfoItemLeave.bind(this));
		jQuery('.course-categories-widget .category-item').on('click', this.handleCategoryClick.bind(this));
		jQuery('.course-tags-widget .tag-link').on('click', this.handleTagClick.bind(this));
	}

	initLazyLoading() {
		if ('IntersectionObserver' in window) {
			const imageObserver = new IntersectionObserver((entries, observer) => {
				entries.forEach(entry => {
					if (entry.isIntersecting) {
						const img = entry.target;
						const src = img.dataset.src;
                        
						if (src) {
							img.src = src;
							img.classList.remove('lazy');
							img.classList.add('loaded');
							observer.unobserve(img);
						}
					}
				});
			});
            
			document.querySelectorAll('img[data-src]').forEach(img => {
				imageObserver.observe(img);
			});
		}
	}

	initSmoothScroll() {
		jQuery('a[href^="#"]').on('click', function(e) {
			e.preventDefault();
            
			const target = jQuery(this.getAttribute('href'));
			if (target.length) {
				jQuery('html, body').animate({
					scrollTop: target.offset().top - 100
				}, 800);
			}
		});
	}

	initLoadingStates() {
		// Loading states are handled in bindEvents
	}

	initResponsiveNav() {
		const navTabs = jQuery('.nav-tabs');
        
		if (navTabs.length && window.innerWidth <= 768) {
			navTabs.wrap('<div class="nav-tabs-wrapper"></div>');
            
			// Add swipe functionality for mobile
			let startX = 0;
			let scrollLeft = 0;
            
			navTabs.on('touchstart', function(e) {
				startX = e.touches[0].pageX - navTabs.offset().left;
				scrollLeft = navTabs.scrollLeft();
			});
            
			navTabs.on('touchmove', function(e) {
				e.preventDefault();
				const x = e.touches[0].pageX - navTabs.offset().left;
				const walk = (x - startX) * 2;
				navTabs.scrollLeft(scrollLeft - walk);
			});
		}
	}

	initSidebarEnhancements() {
		// Enhanced sidebar functionality is handled in bindEvents
	}

	initCollapsibleSections() {
		// Handle collapsible lesson attachments
		jQuery(document).on('click', '.splms-lesson-attachments__toggle', function(e) {
			e.preventDefault();
			const $toggle = jQuery(this);
			const $content = $toggle.siblings('.splms-lesson-attachments__content');
			const isExpanded = $toggle.attr('aria-expanded') === 'true';
            
			if (isExpanded) {
				$toggle.attr('aria-expanded', 'false');
				$content.removeClass('is-expanded');
			} else {
				$toggle.attr('aria-expanded', 'true');
				$content.addClass('is-expanded');
			}
		});
	}

	// handleFormSubmit(e) {
	//     jQuery(e.currentTarget).addClass('loading');
	// }

	// handleAjaxButton(e) {
	//     jQuery(e.currentTarget).addClass('loading');
	// }

	handleInfoItemHover(e) {
		jQuery(e.currentTarget).find('.info-icon').addClass('pulse');
	}

	handleInfoItemLeave(e) {
		jQuery(e.currentTarget).find('.info-icon').removeClass('pulse');
	}

	handleCategoryClick(e) {
		e.preventDefault();
		const $item = jQuery(e.currentTarget);
		const href = $item.find('.category-link').attr('href');
        
		// Add loading state
		$item.addClass('loading');
        
		// Navigate after animation
		setTimeout(() => {
			window.location.href = href;
		}, 200);
	}

	handleTagClick(e) {
		const $tag = jQuery(e.currentTarget);
        
		// Add click animation
		$tag.addClass('clicked');
		setTimeout(() => {
			$tag.removeClass('clicked');
		}, 200);
	}

	addNotificationStyles() {
		if (!document.querySelector('#splms-notifications-css')) {
			const style = document.createElement('style');
			style.id = 'splms-notifications-css';
			style.textContent = `
                .splms-notification {
                    position: fixed;
                    top: 45px;
                    right: 24px;
                    z-index: 10000;
                    min-width: 320px;
                    max-width: 420px;
                    padding: 16px 20px;
                    border-radius: 12px;
                    color: white;
                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                    font-size: 14px;
                    font-weight: 500;
                    line-height: 1.4;
                    display: flex;
                    align-items: flex-start;
                    gap: 12px;
                    transform: translateX(450px) scale(0.95);
                    transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
                    box-shadow: var(--splms-shadow-xl, 0 8px 32px rgba(0, 0, 0, 0.12)), var(--splms-shadow-sm, 0 2px 8px rgba(0, 0, 0, 0.08));
                    backdrop-filter: blur(8px);
                    border: 1px solid var(--splms-border-alpha, rgba(255, 255, 255, 0.1));
                }
                
                .splms-notification::before {
                    content: '';
                    position: absolute;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    border-radius: 12px;                    
                    pointer-events: none;
                }
                
                .splms-notification.show {
                    transform: translateX(0) scale(1);
                    animation: slideInBounce 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
                }
                
                .splms-notification.success {
                    background: linear-gradient(135deg, var(--splms-success, #10b981), var(--splms-success-dark, #059669));
                    border-color: var(--splms-success-alpha-3, rgba(16, 185, 129, 0.3));
                }
                
                .splms-notification.success::before {
                    content: '✓';
                    position: absolute;
                    left: 16px;
                    top: 50%;
                    transform: translateY(-50%);
                    font-size: 16px;
                    font-weight: bold;
                    color: white;
                    z-index: 1;
                }
                
                .splms-notification.success .notification-message {
                    margin-left: 24px;
                }
                
                .splms-notification.error {
                    background: linear-gradient(135deg, var(--splms-danger, #ef4444), var(--splms-danger-dark, #dc2626));
                    border-color: var(--splms-danger-alpha-3, rgba(239, 68, 68, 0.3));
                }
                
                .splms-notification.error::before {
                    content: '✕';
                    position: absolute;
                    left: 16px;
                    top: 50%;
                    transform: translateY(-50%);
                    font-size: 16px;
                    font-weight: bold;
                    color: white;
                    z-index: 1;
                }
                
                .splms-notification.error .notification-message {
                    margin-left: 24px;
                }
                
                .splms-notification.info {
                    background: linear-gradient(135deg, var(--splms-info, #3b82f6), var(--splms-info-dark, #2563eb));
                    border-color: var(--splms-info-alpha-3, rgba(59, 130, 246, 0.3));
                }
                
                .splms-notification.info::before {
                    content: 'ⓘ';
                    position: absolute;
                    left: 16px;
                    top: 50%;
                    transform: translateY(-50%);
                    font-size: 16px;
                    font-weight: bold;
                    color: white;
                    z-index: 1;
                }
                
                .splms-notification.info .notification-message {
                    margin-left: 24px;
                }
                
                .splms-notification.warning {
                    background: linear-gradient(135deg, var(--splms-warning, #f59e0b), var(--splms-warning-dark, #d97706));
                    border-color: var(--splms-warning-alpha-3, rgba(245, 158, 11, 0.3));
                }
                
                .splms-notification.warning::before {
                    content: '⚠';
                    position: absolute;
                    left: 16px;
                    top: 50%;
                    transform: translateY(-50%);
                    font-size: 16px;
                    font-weight: bold;
                    color: white;
                    z-index: 1;
                }
                
                .splms-notification.warning .notification-message {
                    margin-left: 24px;
                }
                
                .notification-message {
                    flex: 1;
                    position: relative;
                    z-index: 1;
                    text-shadow: var(--splms-text-shadow, 0 1px 2px rgba(0, 0, 0, 0.1));
                }
                
                .notification-close {
                    background: var(--splms-white-alpha-2, rgba(255, 255, 255, 0.2));
                    border: none;
                    color: white;
                    font-size: 16px;
                    cursor: pointer;
                    padding: 4px;
                    width: 24px;
                    height: 24px;
                    border-radius: 6px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    transition: all 0.2s ease;
                    position: relative;
                    z-index: 10;
                    flex-shrink: 0;
                    margin-top: -2px;
                    pointer-events: auto;
                }
                
                .notification-close:hover {
                    background: var(--splms-white-alpha-3, rgba(255, 255, 255, 0.3));
                    transform: scale(1.1);
                }
                
                .notification-close:active {
                    transform: scale(0.95);
                }
                
                @keyframes slideInBounce {
                    0% {
                        transform: translateX(450px) scale(0.95);
                        opacity: 0;
                    }
                    60% {
                        transform: translateX(-8px) scale(1.02);
                        opacity: 1;
                    }
                    100% {
                        transform: translateX(0) scale(1);
                        opacity: 1;
                    }
                }
                
                .loading {
                    opacity: 0.7;
                    pointer-events: none;
                }
                
                /* Progress bar for auto-dismiss */
                .splms-notification::after {
                    content: '';
                    position: absolute;
                    bottom: 0;
                    left: 0;
                    height: 3px;
                    background: var(--splms-white-alpha-3, rgba(255, 255, 255, 0.3));
                    border-radius: 0 0 12px 12px;
                    animation: progressBar 5s linear;
                }
                
                @keyframes progressBar {
                    0% { width: 100%; }
                    100% { width: 0%; }
                }
                
                @media (max-width: 768px) {
                    .splms-notification {
                        right: 16px;
                        left: 16px;
                        min-width: auto;
                        max-width: none;
                        top: 20px;
                        transform: translateY(-100px) scale(0.95);
                        font-size: 13px;
                        padding: 14px 16px;
                    }
                    
                    .splms-notification.show {
                        transform: translateY(0) scale(1);
                    }
                    
                    .splms-notification::before {
                        left: 14px;
                        font-size: 14px;
                    }
                    
                    .splms-notification.success .notification-message,
                    .splms-notification.error .notification-message,
                    .splms-notification.info .notification-message,
                    .splms-notification.warning .notification-message {
                        margin-left: 20px;
                    }
                }
                
                @media (max-width: 480px) {
                    .splms-notification {
                        right: 12px;
                        left: 12px;
                        top: 16px;
                        padding: 12px 14px;
                        font-size: 12px;
                    }
                    
                    .notification-close {
                        width: 20px;
                        height: 20px;
                        font-size: 14px;
                    }
                }
            `;
			document.head.appendChild(style);
		}
	}
} 