/**
 * Lesson Viewer - Main Controller
 *
 * Initializes and coordinates all fullscreen lesson viewer components.
 *
 * @package SkillPulse_LMS
 * @version 1.0.0
 */

import SPLMSVideoTracker from './VideoTracker.js';
import SPLMSLessonCompletion from './LessonCompletion.js';
import SPLMSSidebarToggle from './SidebarToggle.js';

class SPLMSLessonViewer {
	constructor() {
		this.container = document.getElementById('splms-lesson-fullscreen-container');

		if (!this.container) {
			return;
		}

		this.lessonId = this.container.dataset.lessonId;
		this.courseId = this.container.dataset.courseId;

		// Initialize components.
		this.videoTracker = null;
		this.lessonCompletion = null;
		this.sidebarToggle = null;

		this.init();
	}

	init() {
		// Initialize video tracker if video player exists.
		const videoContainer = document.querySelector('.splms-video-player-container');
		if (videoContainer) {
			this.videoTracker = new SPLMSVideoTracker(videoContainer, this.lessonId);
		}

		// Initialize lesson completion handler.
		this.lessonCompletion = new SPLMSLessonCompletion(this.lessonId, this.courseId, this.videoTracker);

		// Initialize sidebar toggle.
		this.sidebarToggle = new SPLMSSidebarToggle();

		// Handle mobile menu toggle.
		this.initMobileMenu();

		// Handle section collapse/expand.
		this.initSectionToggle();

		// Update next button state based on completion.
		this.updateNextButtonState();
	}

	initMobileMenu() {
		const menuToggle = document.querySelector('.splms-mobile-menu-toggle');
		const sidebar = document.querySelector('.splms-fullscreen-sidebar');
		const menuIcon = menuToggle?.querySelector('.splms-menu-icon');
		const closeIcon = menuToggle?.querySelector('.splms-close-icon');

		if (!menuToggle || !sidebar) {
			return;
		}

		menuToggle.addEventListener('click', () => {
			const isOpen = sidebar.classList.contains('is-mobile-open');

			if (isOpen) {
				sidebar.classList.remove('is-mobile-open');
				menuToggle.setAttribute('aria-expanded', 'false');
				if (menuIcon) menuIcon.style.display = '';
				if (closeIcon) closeIcon.style.display = 'none';
			} else {
				sidebar.classList.add('is-mobile-open');
				menuToggle.setAttribute('aria-expanded', 'true');
				if (menuIcon) menuIcon.style.display = 'none';
				if (closeIcon) closeIcon.style.display = '';
			}
		});

		// Close sidebar when clicking outside on mobile.
		document.addEventListener('click', (e) => {
			if (window.innerWidth <= 768 &&
				sidebar.classList.contains('is-mobile-open') &&
				!sidebar.contains(e.target) &&
				!menuToggle.contains(e.target)) {
				sidebar.classList.remove('is-mobile-open');
				menuToggle.setAttribute('aria-expanded', 'false');
				if (menuIcon) menuIcon.style.display = '';
				if (closeIcon) closeIcon.style.display = 'none';
			}
		});
	}

	initSectionToggle() {
		const sectionHeaders = document.querySelectorAll('.splms-section-header');

		sectionHeaders.forEach(header => {
			header.addEventListener('click', (e) => {
				e.preventDefault();

				const section = header.closest('.splms-curriculum-section');
				const items = section.querySelector('.splms-section-items');
				const isExpanded = header.getAttribute('aria-expanded') === 'true';

				if (isExpanded) {
					header.setAttribute('aria-expanded', 'false');
					items.style.display = 'none';
					section.classList.remove('is-expanded');
				} else {
					header.setAttribute('aria-expanded', 'true');
					items.style.display = 'flex';
					section.classList.add('is-expanded');
				}
			});
		});
	}

	updateNextButtonState() {
		// Listen for lesson completion events.
		document.addEventListener('splms:lessonCompleted', () => {
			const nextButton = document.querySelector('.splms-btn-next.is-disabled');
			if (nextButton) {
				const url = nextButton.dataset.url;
				if (url) {
					nextButton.classList.remove('is-disabled');
					nextButton.removeAttribute('disabled');
					nextButton.setAttribute('href', url);

					// Remove locked badge.
					const lockedBadge = nextButton.querySelector('.splms-locked-badge');
					if (lockedBadge) {
						lockedBadge.remove();
					}
				}
			}
		});

		// Listen for course completion events.
		document.addEventListener('splms:courseCompleted', (event) => {
			this.handleCourseCompletion(event.detail);
		});
	}

	/**
	 * Handle course completion navigation and UI updates
	 */
	handleCourseCompletion(data) {
		// Hide next button since course is complete
		const nextButton = document.querySelector('.splms-btn-next');
		if (nextButton) {
			nextButton.style.display = 'none';
		}

		// Add course completion actions
		this.addCourseCompletionActions(data);

		// Update sidebar to show course as completed
		const courseSidebar = document.querySelector('.splms-course-sidebar');
		if (courseSidebar) {
			courseSidebar.classList.add('course-completed');
		}
	}

	/**
	 * Add completion actions like certificate download, back to dashboard
	 */
	addCourseCompletionActions(data) {
		const buttonContainer = document.querySelector('.lesson-navigation, .splms-lesson-footer');
		if (!buttonContainer) return;

		// Create completion actions container
		const actionsContainer = document.createElement('div');
		actionsContainer.className = 'course-completion-actions';
		actionsContainer.style.cssText = `
			display: flex;
			gap: 12px;
			align-items: center;
			margin-top: 20px;
			padding: 20px;
			background: var(--splms-background-secondary, #f8f9fa);
			border-radius: var(--splms-border-radius-lg, 8px);
			border: 1px solid var(--splms-border-color, #e9ecef);
		`;

		// Back to course overview button
		const courseButton = document.createElement('a');
		courseButton.href = window.location.pathname.replace(/\/[^\/]+\/?$/, '/');
		courseButton.className = 'splms-btn splms-btn-secondary';
		courseButton.textContent = '← Back to Course';
		courseButton.style.cssText = `
			padding: 12px 20px;
			text-decoration: none;
			border-radius: 6px;
			font-weight: 500;
		`;

		// Dashboard button
		const dashboardButton = document.createElement('a');
		dashboardButton.href = '/dashboard/';
		dashboardButton.className = 'splms-btn splms-btn-primary';
		dashboardButton.textContent = '🏠 Dashboard';
		dashboardButton.style.cssText = `
			padding: 12px 20px;
			text-decoration: none;
			border-radius: 6px;
			font-weight: 500;
		`;

		actionsContainer.appendChild(courseButton);

		// Add certificate button if certificate was generated
		if (data.data && (data.data.certificate_generated || data.data.certificate_id)) {
			const certButton = document.createElement('a');
			certButton.href = this.getCertificateUrl(data);
			certButton.target = '_blank';
			certButton.className = 'splms-btn splms-btn-success';
			certButton.textContent = '🏆 View Certificate';
			certButton.style.cssText = `
				padding: 12px 20px;
				text-decoration: none;
				border-radius: 6px;
				font-weight: 500;
				background-color: var(--splms-success, #10b981);
				color: white;
			`;
			actionsContainer.appendChild(certButton);
		}

		actionsContainer.appendChild(dashboardButton);

		// Add completion text
		const completionText = document.createElement('div');
		completionText.style.cssText = `
			flex: 1;
			text-align: center;
			color: var(--splms-text-muted, #6b7280);
			font-style: italic;
		`;
		completionText.textContent = 'Course completed! Choose your next step.';

		// Insert completion text in the middle
		actionsContainer.insertBefore(completionText, dashboardButton);

		// Insert into page
		buttonContainer.appendChild(actionsContainer);
	}

	/**
	 * Get certificate URL for current course
	 */
	getCertificateUrl(data) {
		// Try to build certificate URL from course data
		if (data.courseId) {
			return `/certificate/?course=${data.courseId}`;
		}

		// Fallback to dashboard certificates
		return '/dashboard/?tab=certificates';
	}

	destroy() {
		if (this.videoTracker) {
			this.videoTracker.destroy();
		}
		if (this.lessonCompletion) {
			this.lessonCompletion.destroy();
		}
		if (this.sidebarToggle) {
			this.sidebarToggle.destroy();
		}
	}
}

// Initialize when DOM is ready.
if (document.readyState === 'loading') {
	document.addEventListener('DOMContentLoaded', () => {
		window.splmsLessonViewer = new SPLMSLessonViewer();
	});
} else {
	window.splmsLessonViewer = new SPLMSLessonViewer();
}

export default SPLMSLessonViewer;
