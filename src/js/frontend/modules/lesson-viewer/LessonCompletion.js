/**
 * Lesson Completion Handler
 *
 * Handles marking lessons as complete via AJAX.
 * Validates video completion requirements before allowing completion.
 *
 * @package SkillPulse_LMS
 * @version 1.0.0
 */

import { SPLMSFrontendAjax } from '../../core/ajax.js';
import { CompletionModal } from './CompletionModal.js';

class SPLMSLessonCompletion {
	constructor(lessonId, courseId, videoTracker = null) {
		this.lessonId = lessonId;
		this.courseId = courseId;
		this.videoTracker = videoTracker;
		this.completeButton = document.getElementById('splms-mark-complete-btn');
		this.isProcessing = false;
		this.courseCompletionTriggered = false;
		this.ajax = new SPLMSFrontendAjax();
		this.completionModal = new CompletionModal();

		this.init();
	}

	init() {
		if (!this.completeButton) {
			return;
		}

		// Check if lesson is locked.
		if (this.isLessonLocked()) {
			this.disableForLockedLesson();
			return;
		}

		// Check if already completed.
		const isCompleted = this.completeButton.classList.contains('is-completed');
		if (isCompleted) {
			this.completeButton.disabled = true;
			return;
		}

		// Listen for video completion if video tracker exists.
		if (this.videoTracker) {
			document.addEventListener('splms:videoCompleted', () => {
				this.enableCompleteButton();
			});
		}

		// Handle complete button click.
		this.completeButton.addEventListener('click', (e) => {
			e.preventDefault();
			this.markComplete();
		});
	}

	/**
	 * Check if the current lesson is locked
	 */
	isLessonLocked() {
		// Check for locked content indicator in the page
		const lockedContent = document.querySelector('.splms-locked-content');
		const lockedMessage = document.querySelector('[data-locked="true"]');

		// Check if the locked content section exists and contains the "This lesson is locked" message
		if (lockedContent) {
			const lockText = lockedContent.textContent || '';
			return lockText.includes('This lesson is locked') || lockText.includes('You need to purchase');
		}

		// Alternative check for locked indicators
		if (lockedMessage) {
			return true;
		}

		// Check if main content area shows purchase button (using standard DOM methods)
		const purchaseButtons = document.querySelectorAll('a[href*="course"]');
		for (let button of purchaseButtons) {
			const buttonText = button.textContent || '';
			if (buttonText.includes('View Course') && buttonText.includes('Purchase')) {
				return true;
			}
		}

		// Check for other locked lesson indicators
		const lockTexts = [
			'This lesson is locked',
			'You need to purchase',
			'Access denied',
			'Complete previous lessons'
		];

		for (let text of lockTexts) {
			if (document.body.textContent.includes(text)) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Disable the completion button for locked lessons
	 */
	disableForLockedLesson() {
		if (!this.completeButton) {
			return;
		}

		this.completeButton.disabled = true;
		this.completeButton.classList.add('is-locked');

		// Update button text and styling for locked state
		this.completeButton.innerHTML = `
			<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<rect x="5" y="11" width="14" height="10" rx="2" stroke="currentColor" stroke-width="2"/>
				<path d="M7 11V7C7 5.67392 7.52678 4.40215 8.46447 3.46447C9.40215 2.52678 10.6739 2 12 2C13.3261 2 14.5979 2.52678 15.5355 3.46447C16.4732 4.40215 17 5.67392 17 7V11" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
				<circle cx="12" cy="16" r="1" fill="currentColor"/>
			</svg>
			<span>Lesson Locked</span>
		`;

		// Add tooltip explaining why it's locked
		this.completeButton.setAttribute('title', 'Complete previous lessons or purchase this section to unlock');
		this.completeButton.setAttribute('aria-label', 'This lesson is locked and cannot be completed');
	}

	enableCompleteButton() {
		if (this.completeButton && this.completeButton.classList.contains('is-disabled')) {
			this.completeButton.classList.remove('is-disabled');
			this.completeButton.disabled = false;
		}
	}

	async markComplete() {
		if (this.isProcessing) {
			return;
		}

		// Double-check if lesson is locked before making the request.
		if (this.isLessonLocked()) {
			this.showError('This lesson is locked. Please purchase the section or complete prerequisites to access it.');
			return;
		}

		// Validate video completion if required.
		if (this.videoTracker && !this.videoTracker.isVideoCompleted()) {
			const completionRequired = this.videoTracker.completionRequired;
			const watchPercentage = this.videoTracker.getWatchPercentage();

			this.showError(
				`Please watch at least ${completionRequired}% of the video to complete this lesson. You have watched ${Math.round(watchPercentage)}%.`
			);
			return;
		}

		this.isProcessing = true;
		this.setButtonLoading(true);

		try {
			const response = await this.ajax.request({
				data: {
					action: 'splms_mark_lesson_complete',
					lesson_id: this.lessonId,
					course_id: this.courseId,
					video_progress: this.videoTracker ? this.videoTracker.getWatchPercentage() : 100
				}
			});

			if (response.success) {
				this.handleSuccess(response.data);
			} else {
				// Handle specific access denied errors
				const errorMessage = response.data?.message || response.data || 'Failed to mark lesson as complete.';
				if (typeof errorMessage === 'string' && errorMessage.toLowerCase().includes('access denied')) {
					this.showError('Access denied. This lesson may be locked or you may not have the required permissions.');
				} else {
					this.showError(errorMessage);
				}
			}
		} catch (error) {
			console.error('Error marking lesson complete:', error);
			this.showError('An error occurred. Please try again.');
		} finally {
			this.isProcessing = false;
			this.setButtonLoading(false);
		}
	}

	handleSuccess(data) {
		console.log('🎯 Course Completion Debug: handleSuccess called', {
			lessonId: this.lessonId,
			courseId: this.courseId,
			data: data
		});

		// Update button state.
		this.completeButton.classList.add('is-completed');
		this.completeButton.disabled = true;
		this.completeButton.innerHTML = `
			<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M22 11.08V12C21.9988 14.1564 21.3005 16.2547 20.0093 17.9818C18.7182 19.709 16.9033 20.9725 14.8354 21.5839C12.7674 22.1953 10.5573 22.1219 8.53447 21.3746C6.51168 20.6273 4.78465 19.2461 3.61096 17.4371C2.43727 15.628 1.87979 13.4881 2.02168 11.3363C2.16356 9.18455 2.99721 7.13631 4.39828 5.49706C5.79935 3.85781 7.69279 2.71537 9.79619 2.24013C11.8996 1.7649 14.1003 1.98232 16.07 2.85999" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				<path d="M22 4L12 14.01L9 11.01" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
			</svg>
			<span>Completed</span>
		`;

		// Update status badge in header.
		const statusBadge = document.querySelector('.splms-status-badge');
		if (statusBadge) {
			statusBadge.classList.remove('splms-status-in-progress');
			statusBadge.classList.add('splms-status-completed');
			statusBadge.textContent = 'Completed';
		}

		// Update sidebar curriculum item first (marks current item as completed).
		this.updateSidebarItem();

		// Update progress bar and sidebar progress.
		if (data.progress_percentage !== undefined) {
			this.updateProgressBar(data.progress_percentage);
		} else {
			// Calculate progress if not provided by server
			const calculatedProgress = this.calculateProgressFromDOM();
			if (calculatedProgress > 0) {
				this.updateProgressBar(calculatedProgress);
			}
		}

		// Dispatch completion event.
		const event = new CustomEvent('splms:lessonCompleted', {
			detail: {
				lessonId: this.lessonId,
				courseId: this.courseId,
				data: data
			}
		});
		document.dispatchEvent(event);

		// Check if course is completed (100%) and this is the final item
		const progress = this.calculateCourseProgress(data);
		const isFinal = this.isFinalCourseItem();

		console.log('🎯 Course Completion Debug: Checking completion conditions', {
			progress: progress,
			isFinal: isFinal,
			progressFromData: data.progress_percentage,
			condition: progress >= 100 && isFinal
		});

		if (progress >= 100 && isFinal) {
			console.log('🎉 Course Completion Debug: TRIGGERING COURSE COMPLETION!');
			this.handleCourseCompletion(data);
		} else {
			console.log('📝 Course Completion Debug: Regular lesson completion');
			// Show regular lesson completion message.
			this.showSuccess('Lesson marked as complete!');
		}
	}

	/**
	 * Calculate course progress percentage
	 */
	calculateCourseProgress(data) {
		console.log('🎯 Course Completion Debug: calculateCourseProgress() called with data:', data);

		// Priority 1: Use progress from server response
		if (data.progress_percentage !== undefined && data.progress_percentage !== null) {
			console.log('🎯 Course Completion Debug: Using server progress:', data.progress_percentage);
			return parseFloat(data.progress_percentage);
		}

		// Priority 2: Calculate from DOM curriculum items
		return this.calculateProgressFromDOM();
	}

	/**
	 * Calculate progress from DOM curriculum items
	 */
	calculateProgressFromDOM() {
		const allItems = document.querySelectorAll('.splms-curriculum-item');
		if (allItems.length > 0) {
			const completedItems = document.querySelectorAll('.splms-curriculum-item.is-completed');
			const calculatedProgress = (completedItems.length / allItems.length) * 100;

			console.log('🎯 Course Completion Debug: Calculated from DOM', {
				totalItems: allItems.length,
				completedItems: completedItems.length,
				calculatedProgress: calculatedProgress
			});

			return calculatedProgress;
		}

		// Priority 3: Check progress bars in UI
		const progressElement = document.querySelector('.splms-progress-value, .splms-progress-percentage');
		if (progressElement) {
			const progressText = progressElement.textContent.replace('%', '');
			const progressFromUI = parseFloat(progressText);

			if (!isNaN(progressFromUI)) {
				console.log('🎯 Course Completion Debug: Using UI progress:', progressFromUI);
				return progressFromUI;
			}
		}

		console.log('🎯 Course Completion Debug: No progress found - defaulting to 0');
		return 0;
	}

	updateProgressBar(percentage) {
		// Update progress bar fills
		const progressFills = document.querySelectorAll('.splms-progress-fill');
		progressFills.forEach(fill => {
			fill.style.width = percentage + '%';
		});

		// Update progress percentage displays
		const progressValues = document.querySelectorAll('.splms-progress-value');
		progressValues.forEach(value => {
			value.textContent = Math.round(percentage) + '%';
		});

		const progressPercentages = document.querySelectorAll('.splms-progress-percentage');
		progressPercentages.forEach(el => {
			el.textContent = Math.round(percentage) + '%';
		});

		// Update sidebar progress summary
		this.updateSidebarProgress(percentage);
	}

	updateSidebarProgress(percentage) {
		// Calculate completed items count (current item should already be marked as completed at this point)
		const allItems = document.querySelectorAll('.splms-curriculum-item');
		const completedItems = document.querySelectorAll('.splms-curriculum-item.is-completed');

		const totalCompleted = completedItems.length;
		const totalItems = allItems.length;

		console.log('🔄 Progress Update Debug:', {
			totalCompleted: totalCompleted,
			totalItems: totalItems,
			percentage: percentage
		});

		// Update progress count displays (e.g., "1/3 items")
		const progressCounts = document.querySelectorAll('.splms-progress-count');
		progressCounts.forEach(count => {
			count.textContent = `${totalCompleted}/${totalItems} items`;
		});

		// Update sidebar summary completion status
		const summaryCards = document.querySelectorAll('.splms-progress-summary-card');
		summaryCards.forEach(card => {
			// Update percentage in summary card
			const summaryValue = card.querySelector('.splms-progress-value');
			if (summaryValue) {
				summaryValue.textContent = Math.round(percentage) + '%';
			}

			// Update item count in summary card
			const summaryCount = card.querySelector('.splms-progress-count');
			if (summaryCount) {
				summaryCount.textContent = `${totalCompleted}/${totalItems} items`;
			}

			// Update completion label
			const progressLabel = card.querySelector('.splms-progress-label');
			if (progressLabel) {
				if (percentage >= 100) {
					progressLabel.textContent = 'Complete';
				} else {
					progressLabel.textContent = 'Complete';
				}
			}
		});

		// Also update any standalone progress displays in sidebar
		const sidebarProgressValues = document.querySelectorAll('.splms-fullscreen-sidebar .splms-progress-value');
		sidebarProgressValues.forEach(value => {
			value.textContent = Math.round(percentage) + '%';
		});
	}

	updateSidebarItem() {
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

	setButtonLoading(loading) {
		if (loading) {
			this.completeButton.disabled = true;
			this.completeButton.classList.add('is-loading');
			this.completeButton.innerHTML = `
				<svg class="splms-spinner" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
					<circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" opacity="0.25"/>
					<path d="M12 2C6.47715 2 2 6.47715 2 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
				</svg>
				<span>Processing...</span>
			`;
		} else {
			// Only reset if not already completed or locked
			if (!this.completeButton.classList.contains('is-completed') &&
				!this.completeButton.classList.contains('is-locked')) {
				this.completeButton.disabled = false;
				this.completeButton.classList.remove('is-loading');
				this.completeButton.innerHTML = `
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M22 11.08V12C21.9988 14.1564 21.3005 16.2547 20.0093 17.9818C18.7182 19.709 16.9033 20.9725 14.8354 21.5839C12.7674 22.1953 10.5573 22.1219 8.53447 21.3746C6.51168 20.6273 4.78465 19.2461 3.61096 17.4371C2.43727 15.628 1.87979 13.4881 2.02168 11.3363C2.16356 9.18455 2.99721 7.13631 4.39828 5.49706C5.79935 3.85781 7.69279 2.71537 9.79619 2.24013C11.8996 1.7649 14.1003 1.98232 16.07 2.85999" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
						<path d="M22 4L12 14.01L9 11.01" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
					<span>Mark as Complete</span>
				`;
			} else {
				// Just remove loading state but preserve completed/locked state
				this.completeButton.classList.remove('is-loading');
			}
		}
	}

	showSuccess(message) {
		// Use existing notification system
		if (window.SPLMSCore && window.SPLMSCore.helper) {
			window.SPLMSCore.helper.showNotification(message, 'success');
		} else {
			console.log('Success:', message);
		}
	}

	showError(message) {
		// Use existing notification system
		if (window.SPLMSCore && window.SPLMSCore.helper) {
			window.SPLMSCore.helper.showNotification(message, 'error');
		} else {
			console.error('Error:', message);
		}
	}

	/**
	 * Check if this is the final lesson/quiz in the course
	 */
	isFinalCourseItem() {
		console.log('🎯 Course Completion Debug: isFinalCourseItem() called');

		// Check if we're on the last item in the curriculum
		const currentItem = document.querySelector('.splms-curriculum-item.is-current');
		console.log('🎯 Course Completion Debug: Current item found:', currentItem);

		if (!currentItem) {
			console.log('🎯 Course Completion Debug: No current item found - returning false');
			return false;
		}

		// Look for next items that are not completed
		const allItems = document.querySelectorAll('.splms-curriculum-item');
		const currentIndex = Array.from(allItems).indexOf(currentItem);

		console.log('🎯 Course Completion Debug: Curriculum analysis', {
			totalItems: allItems.length,
			currentIndex: currentIndex,
			isLastInList: currentIndex === allItems.length - 1
		});

		// If this is the last item in the list, it's the final item
		if (currentIndex === allItems.length - 1) {
			console.log('🎯 Course Completion Debug: This is the last item in curriculum - returning true');
			return true;
		}

		// Check if all remaining items are already completed
		const remainingItems = [];
		for (let i = currentIndex + 1; i < allItems.length; i++) {
			const item = allItems[i];
			const isCompleted = item.classList.contains('is-completed');
			remainingItems.push({
				index: i,
				isCompleted: isCompleted,
				text: item.textContent?.trim() || 'Unknown'
			});

			if (!isCompleted) {
				console.log('🎯 Course Completion Debug: Found incomplete item after current - returning false', {
					incompleteItem: item.textContent?.trim() || 'Unknown',
					index: i
				});
				return false;
			}
		}

		console.log('🎯 Course Completion Debug: All remaining items are completed', {
			remainingItems: remainingItems
		});

		return true;
	}

	/**
	 * Handle course completion with special celebration
	 */
	handleCourseCompletion(data) {
		// Prevent duplicate course completion events
		if (this.courseCompletionTriggered) {
			console.log('🔄 Course Completion Debug: Completion already triggered, skipping duplicate');
			return;
		}

		this.courseCompletionTriggered = true;

		// Dispatch course completion event
		const courseEvent = new CustomEvent('splms:courseCompleted', {
			detail: {
				lessonId: this.lessonId,
				courseId: this.courseId,
				data: data
			}
		});
		document.dispatchEvent(courseEvent);

		// Show course completion celebration
		this.showCourseCompletion(data);

		// Update course status indicators
		this.updateCourseCompletionStatus();
	}

	/**
	 * Show course completion modal with celebration
	 */
	async showCourseCompletion(data) {
		// Show the completion modal using the new modal system
		// The modal will handle its own celebration and confetti
		await this.completionModal.showCompletion(this.lessonId, this.courseId, data);
	}


	/**
	 * Update course completion status indicators
	 */
	updateCourseCompletionStatus() {
		// Update status badge in header
		const statusBadges = document.querySelectorAll('.splms-status-badge');
		statusBadges.forEach(badge => {
			badge.classList.remove('splms-status-in-progress');
			badge.classList.add('splms-status-completed');
			badge.textContent = 'Completed';
		});

		// Update course completion indicators
		const courseWrappers = document.querySelectorAll('.splms-course-wrapper');
		courseWrappers.forEach(wrapper => {
			wrapper.classList.add('course-completed');
		});

		// Hide next button for final lesson and show completion actions
		const nextButton = document.querySelector('.splms-btn-next');
		if (nextButton) {
			nextButton.style.display = 'none';
		}
	}


	destroy() {
		if (this.completeButton) {
			this.completeButton.removeEventListener('click', this.markComplete);
		}
	}
}

export default SPLMSLessonCompletion;
