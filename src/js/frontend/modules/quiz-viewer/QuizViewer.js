/**
 * Quiz Viewer - Fullscreen Quiz Controller
 *
 * Lightweight wrapper that integrates the existing quiz system with the fullscreen UI.
 * The main quiz logic is handled by SPLMSQuiz class.
 *
 * @package SkillPulse_LMS
 * @version 1.0.0
 */

import SPLMSQuizTimer from './QuizTimer.js';
import SPLMSSidebarToggle from '../lesson-viewer/SidebarToggle.js';

class SPLMSQuizViewer {
	constructor() {
		this.container = document.getElementById('splms-quiz-fullscreen-container');

		if (!this.container) {
			return;
		}

		this.quizId = this.container.dataset.quizId;
		this.courseId = this.container.dataset.courseId;

		// Initialize components.
		this.quizTimer = null;
		this.sidebarToggle = null;

		this.init();
	}

	init() {
		// Initialize quiz timer in header.
		const timerDisplay = document.querySelector('.splms-quiz-timer-display');
		if (timerDisplay) {
			this.quizTimer = new SPLMSQuizTimer(timerDisplay);
		}

		// Initialize sidebar toggle (reuse from lesson viewer).
		this.sidebarToggle = new SPLMSSidebarToggle();

		// Handle mobile menu toggle.
		this.initMobileMenu();

		// Handle section collapse/expand in sidebar.
		this.initSectionToggle();

		// Listen for quiz events to update timer.
		this.bindQuizEvents();
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

	bindQuizEvents() {
		// Listen for quiz start event to show timer.
		document.addEventListener('splms:quiz:started', (e) => {
			if (this.quizTimer) {
				this.quizTimer.show();
			}
		});

		// Listen for quiz submitted event to hide timer.
		document.addEventListener('splms:quiz:submitted', (e) => {
			if (this.quizTimer) {
				this.quizTimer.hide();
			}

			// Update sidebar if quiz was passed.
			if (e.detail && e.detail.results && e.detail.results.passed) {
				this.updateSidebarQuizStatus();
			}
		});

		// Listen for timer updates from SPLMSQuiz.
		document.addEventListener('splms:quiz:timerUpdate', (e) => {
			if (this.quizTimer && e.detail && e.detail.timeRemaining !== undefined) {
				this.quizTimer.updateTime(e.detail.timeRemaining);
			}
		});
	}

	updateSidebarQuizStatus() {
		const currentItem = document.querySelector('.splms-curriculum-item.is-current');
		if (currentItem) {
			currentItem.classList.add('is-completed');

			// Add checkmark if not exists.
			if (!currentItem.querySelector('.splms-check-icon')) {
				const checkmark = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
				checkmark.classList.add('splms-check-icon');
				checkmark.setAttribute('width', '20');
				checkmark.setAttribute('height', '20');
				checkmark.setAttribute('viewBox', '0 0 24 24');
				checkmark.setAttribute('fill', 'none');
				checkmark.innerHTML = '<path d="M20 6L9 17L4 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>';
				currentItem.appendChild(checkmark);
			}
		}
	}

	destroy() {
		if (this.quizTimer) {
			this.quizTimer.destroy();
		}
		if (this.sidebarToggle) {
			this.sidebarToggle.destroy();
		}
	}
}

// Initialize when DOM is ready.
if (document.readyState === 'loading') {
	document.addEventListener('DOMContentLoaded', () => {
		window.splmsQuizViewer = new SPLMSQuizViewer();
	});
} else {
	window.splmsQuizViewer = new SPLMSQuizViewer();
}

export default SPLMSQuizViewer;
