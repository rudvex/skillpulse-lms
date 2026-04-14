/**
 * Quiz Timer - Header Timer Display
 *
 * Displays and updates the quiz timer in the header.
 * Works with the main SPLMSQuiz class for timer countdown logic.
 *
 * @package SkillPulse_LMS
 * @version 1.0.0
 */

class SPLMSQuizTimer {
	constructor(timerElement) {
		this.timerElement = timerElement;
		this.valueElement = timerElement?.querySelector('.splms-timer-value');
		this.timeLimit = parseInt(timerElement?.dataset.timeLimit) || 0;
		this.timeRemaining = 0;
		this.isWarning = false;
		this.isCritical = false;

		this.init();
	}

	init() {
		if (!this.timerElement || !this.valueElement) {
			return;
		}

		// Timer is hidden by default, shown when quiz starts.
		if (this.timeLimit > 0) {
			this.timeRemaining = this.timeLimit * 60; // Convert minutes to seconds.
			this.updateDisplay();
		}
	}

	show() {
		if (this.timerElement && this.timeLimit > 0) {
			this.timerElement.style.display = '';
			this.timeRemaining = this.timeLimit * 60;
			this.updateDisplay();
		}
	}

	hide() {
		if (this.timerElement) {
			this.timerElement.style.display = 'none';
		}
	}

	updateTime(seconds) {
		this.timeRemaining = seconds;
		this.updateDisplay();
		this.checkWarnings();
	}

	updateDisplay() {
		if (!this.valueElement) {
			return;
		}

		const minutes = Math.floor(this.timeRemaining / 60);
		const seconds = this.timeRemaining % 60;

		// Format time as MM:SS.
		const timeString = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
		this.valueElement.textContent = timeString;
	}

	checkWarnings() {
		if (!this.timerElement) {
			return;
		}

		// Critical warning: last 1 minute.
		if (this.timeRemaining <= 60 && !this.isCritical) {
			this.isCritical = true;
			this.timerElement.classList.add('is-critical');
			this.timerElement.classList.remove('is-warning');

			// Dispatch event for other components.
			const event = new CustomEvent('splms:quiz:timerCritical', {
				detail: { timeRemaining: this.timeRemaining }
			});
			document.dispatchEvent(event);
		}
		// Warning: last 5 minutes.
		else if (this.timeRemaining <= 300 && this.timeRemaining > 60 && !this.isWarning) {
			this.isWarning = true;
			this.timerElement.classList.add('is-warning');

			// Dispatch event for other components.
			const event = new CustomEvent('splms:quiz:timerWarning', {
				detail: { timeRemaining: this.timeRemaining }
			});
			document.dispatchEvent(event);
		}
		// Reset warnings if time increases (shouldn't happen but defensive).
		else if (this.timeRemaining > 300) {
			this.isWarning = false;
			this.isCritical = false;
			this.timerElement.classList.remove('is-warning', 'is-critical');
		}
	}

	destroy() {
		// Cleanup if needed.
	}
}

export default SPLMSQuizTimer;
