/**
 * Trial Admin Restrictions
 *
 * Client-side trial restriction handling for admin interface.
 * Blocks form submissions and provides upgrade prompts when trial expires.
 *
 * @since [SPLMS_VERSION]
 */

class SPLMSTrialRestrictions {
	constructor() {
		this.config = window.splmsTrialRestrictions || {};
		this.isTrialExpired = this.config.isTrialExpired || false;
		this.daysRemaining = this.config.daysRemaining || 0;
		this.messages = this.config.messages || {};

		this.init();
	}

	/**
	 * Initialize trial restrictions.
	 */
	init() {
		if (!this.config.ajaxUrl || !this.config.nonce) {
			return;
		}

		this.setupFormRestrictions();
		this.setupPageOverlays();
		this.setupPeriodicCheck();
		this.setupUpgradePrompts();
	}

	/**
	 * Setup form submission restrictions.
	 */
	setupFormRestrictions() {
		if (!this.isTrialExpired) {
			return;
		}

		// Block form submissions for critical admin forms.
		jQuery(document).on('submit', 'form', (e) => {
			const $form = jQuery(e.target);

			// Allow forms that should not be restricted.
			if (this.isFormAllowed($form)) {
				return;
			}

			e.preventDefault();
			this.showUpgradePrompt('form_submission');
			return false;
		});

		// Show visual indicators on restricted forms.
		this.addFormRestrictionNotices();
	}

	/**
	 * Check if a form should be allowed during trial expiration.
	 *
	 * @param {jQuery} $form Form element.
	 * @return {boolean} True if form is allowed.
	 */
	isFormAllowed($form) {
		// Always allow these forms.
		const allowedForms = [
			'form[action*="admin-ajax.php"]', // AJAX forms
			'#adminmenu form', // Admin menu search
			'form[action*="update.php"]', // WordPress updates
			'form[action*="options-general.php"]', // General settings
			'form[action*="splms-license"]', // License page
		];

		for (const selector of allowedForms) {
			if ($form.is(selector) || $form.closest(selector).length) {
				return true;
			}
		}

		// Allow forms in allowed pages.
		if (this.isPageAllowed(window.location.href)) {
			return true;
		}

		return false;
	}

	/**
	 * Check if a page should be allowed during trial expiration.
	 *
	 * @param {string} url Page URL.
	 * @return {boolean} True if page is allowed.
	 */
	isPageAllowed(url) {
		const allowedPages = [
			'admin.php?page=splms-license',
			'admin.php?page=splms-settings',
			'profile.php',
			'users.php',
		];

		return allowedPages.some(page => url.includes(page));
	}

	/**
	 * Add visual restriction notices to forms.
	 */
	addFormRestrictionNotices() {
		jQuery('form').each((index, form) => {
			const $form = jQuery(form);

			if (this.isFormAllowed($form)) {
				return;
			}

			// Add restriction notice.
			const notice = jQuery(`
				<div class="splms-trial-restriction-notice">
					<p><strong>${this.messages.expired || 'Trial expired'}</strong></p>
					<p>${this.messages.formBlocked || 'Form submission blocked'}</p>
					<button type="button" class="button-primary splms-upgrade-btn">
						${this.config.upgradeText || 'Upgrade Now'}
					</button>
				</div>
			`);

			$form.prepend(notice);
			$form.addClass('splms-restricted-form');
		});

		// Handle upgrade button clicks.
		jQuery(document).on('click', '.splms-upgrade-btn', (e) => {
			e.preventDefault();
			this.navigateToLicense();
		});
	}

	/**
	 * Setup page-level overlays for restricted pages.
	 */
	setupPageOverlays() {
		if (!this.isTrialExpired) {
			return;
		}

		// Check if current page should be restricted.
		if (this.shouldShowPageOverlay()) {
			this.showPageOverlay();
		}
	}

	/**
	 * Check if page overlay should be shown.
	 *
	 * @return {boolean} True if overlay should be shown.
	 */
	shouldShowPageOverlay() {
		const restrictedPages = [
			'admin.php?page=splms-courses',
			'admin.php?page=splms-lessons',
			'admin.php?page=splms-quizzes',
			'admin.php?page=splms-certificates',
			'post-new.php?post_type=sp-course',
			'post-new.php?post_type=sp-lesson',
		];

		const currentUrl = window.location.href;
		return restrictedPages.some(page => currentUrl.includes(page));
	}

	/**
	 * Show page-level restriction overlay.
	 */
	showPageOverlay() {
		const overlay = jQuery(`
			<div id="splms-page-restriction-overlay" class="splms-restriction-overlay">
				<div class="splms-restriction-content">
					<div class="splms-restriction-icon">
						<span class="dashicons dashicons-lock"></span>
					</div>
					<h2>${this.messages.expired || 'Trial Expired'}</h2>
					<p>Your 14-day trial has ended. Upgrade to continue using SkillPulse LMS features.</p>
					<div class="splms-restriction-actions">
						<button type="button" class="button-primary splms-upgrade-btn">
							Upgrade Now
						</button>
						<button type="button" class="button-secondary splms-dismiss-overlay">
							Go Back
						</button>
					</div>
				</div>
			</div>
		`);

		jQuery('body').append(overlay);

		// Handle overlay actions.
		overlay.find('.splms-upgrade-btn').on('click', () => {
			this.navigateToLicense();
		});

		overlay.find('.splms-dismiss-overlay').on('click', () => {
			window.history.back();
		});
	}

	/**
	 * Setup periodic trial status check.
	 */
	setupPeriodicCheck() {
		// Check trial status every 5 minutes.
		setInterval(() => {
			this.checkTrialStatus();
		}, 5 * 60 * 1000);

		// Check when page becomes visible.
		document.addEventListener('visibilitychange', () => {
			if (!document.hidden) {
				this.checkTrialStatus();
			}
		});
	}

	/**
	 * Check trial status via AJAX.
	 */
	checkTrialStatus() {
		jQuery.ajax({
			url: this.config.ajaxUrl,
			method: 'POST',
			data: {
				action: 'splms_check_trial_status',
				nonce: this.config.nonce
			},
			success: (response) => {
				if (response.success && response.data) {
					this.updateTrialStatus(response.data);
				}
			},
			error: () => {
				// Silently handle errors to avoid disrupting user experience.
			}
		});
	}

	/**
	 * Update trial status based on server response.
	 *
	 * @param {Object} data Trial status data.
	 */
	updateTrialStatus(data) {
		const wasExpired = this.isTrialExpired;
		this.isTrialExpired = data.isTrialExpired || false;
		this.daysRemaining = data.daysRemaining || 0;

		// If trial just expired, reload page to show restrictions.
		if (!wasExpired && this.isTrialExpired) {
			window.location.reload();
		}

		// Update any existing trial status displays.
		this.updateTrialDisplays();
	}

	/**
	 * Update trial status displays on page.
	 */
	updateTrialDisplays() {
		// Update any trial countdown elements.
		jQuery('.splms-trial-days-remaining').text(this.daysRemaining);

		// Update trial status indicators.
		if (this.isTrialExpired) {
			jQuery('.splms-trial-status').addClass('expired').removeClass('active');
		} else {
			jQuery('.splms-trial-status').addClass('active').removeClass('expired');
		}
	}

	/**
	 * Setup contextual upgrade prompts.
	 */
	setupUpgradePrompts() {
		// Show upgrade prompts on feature usage attempts.
		this.addFeatureUsagePrompts();

		// Add upgrade prompts to navigation items.
		this.addNavigationPrompts();
	}

	/**
	 * Add feature usage prompts.
	 */
	addFeatureUsagePrompts() {
		// Add prompts to buttons that create new content.
		jQuery('.page-title-action, .add-new-h2').each((index, button) => {
			const $button = jQuery(button);

			if (this.isTrialExpired) {
				$button.addClass('splms-restricted-button');
				$button.on('click', (e) => {
					e.preventDefault();
					this.showUpgradePrompt('feature_usage');
				});
			}
		});
	}

	/**
	 * Add navigation prompts.
	 */
	addNavigationPrompts() {
		if (!this.isTrialExpired && this.daysRemaining <= 3) {
			// Add urgency indicators to navigation.
			jQuery('#adminmenu .splms-menu-item').each((index, item) => {
				const $item = jQuery(item);
				$item.append(`
					<span class="splms-trial-urgency" title="${this.daysRemaining} days remaining">
						<span class="dashicons dashicons-clock"></span>
					</span>
				`);
			});
		}
	}

	/**
	 * Show upgrade prompt modal.
	 *
	 * @param {string} context Context for the prompt.
	 */
	showUpgradePrompt(context = 'general') {
		const messages = {
			form_submission: 'You need an active license to save changes.',
			feature_usage: 'This feature requires an active license.',
			general: 'Your trial has expired. Upgrade to continue.'
		};

		const modal = jQuery(`
			<div id="splms-upgrade-modal" class="splms-modal-overlay">
				<div class="splms-modal-content">
					<div class="splms-modal-header">
						<h2>Upgrade Required</h2>
						<button type="button" class="splms-modal-close">
							<span class="dashicons dashicons-no"></span>
						</button>
					</div>
					<div class="splms-modal-body">
						<p><strong>${messages[context] || messages.general}</strong></p>
						<p>Unlock all SkillPulse LMS features with a license:</p>
						<ul>
							<li>Unlimited courses and lessons</li>
							<li>Advanced quiz features</li>
							<li>Certificate generation</li>
							<li>Student analytics</li>
							<li>Priority support</li>
						</ul>
					</div>
					<div class="splms-modal-footer">
						<button type="button" class="button-primary splms-upgrade-btn">
							Upgrade Now
						</button>
						<button type="button" class="button-secondary splms-modal-close">
							Cancel
						</button>
					</div>
				</div>
			</div>
		`);

		jQuery('body').append(modal);

		// Handle modal actions.
		modal.find('.splms-upgrade-btn').on('click', () => {
			this.navigateToLicense();
		});

		modal.find('.splms-modal-close').on('click', () => {
			modal.remove();
		});

		// Close on overlay click.
		modal.on('click', (e) => {
			if (e.target === modal[0]) {
				modal.remove();
			}
		});

		// Track upgrade prompt display.
		if (window.gtag) {
			window.gtag('event', 'trial_upgrade_prompt_shown', {
				context: context,
				days_remaining: this.daysRemaining
			});
		}
	}

	/**
	 * Navigate to license page.
	 */
	navigateToLicense() {
		const licenseUrl = this.config.licenseUrl || 'admin.php?page=splms-license';

		// Track upgrade click.
		if (window.gtag) {
			window.gtag('event', 'trial_upgrade_click', {
				source: 'admin_restrictions',
				days_remaining: this.daysRemaining
			});
		}

		window.location.href = licenseUrl;
	}
}

// Initialize when DOM is ready.
jQuery(document).ready(() => {
	if (window.splmsTrialRestrictions) {
		new SPLMSTrialRestrictions();
	}
});

// Add basic styles.
jQuery(() => {
	const styles = `
		<style>
		.splms-restriction-overlay {
			position: fixed;
			top: 0;
			left: 0;
			width: 100%;
			height: 100%;
			background: rgba(0, 0, 0, 0.8);
			z-index: 999999;
			display: flex;
			align-items: center;
			justify-content: center;
		}

		.splms-restriction-content {
			background: white;
			padding: 40px;
			border-radius: 8px;
			max-width: 500px;
			text-align: center;
			box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
		}

		.splms-restriction-icon {
			margin-bottom: 20px;
		}

		.splms-restriction-icon .dashicons {
			font-size: 48px;
			color: #dc3232;
		}

		.splms-restriction-actions {
			margin-top: 30px;
		}

		.splms-restriction-actions .button {
			margin: 0 10px;
		}

		.splms-trial-restriction-notice {
			background: #fff3cd;
			border: 1px solid #ffeaa7;
			padding: 15px;
			margin-bottom: 20px;
			border-radius: 4px;
		}

		.splms-restricted-form {
			opacity: 0.7;
			pointer-events: none;
		}

		.splms-restricted-form .splms-trial-restriction-notice {
			opacity: 1;
			pointer-events: all;
		}

		.splms-restricted-button {
			position: relative;
		}

		.splms-restricted-button::after {
			content: '🔒';
			position: absolute;
			top: -5px;
			right: -5px;
			background: #dc3232;
			color: white;
			border-radius: 50%;
			width: 20px;
			height: 20px;
			display: flex;
			align-items: center;
			justify-content: center;
			font-size: 10px;
		}

		.splms-trial-urgency {
			color: #d63638;
			margin-left: 5px;
		}

		.splms-modal-overlay {
			position: fixed;
			top: 0;
			left: 0;
			width: 100%;
			height: 100%;
			background: rgba(0, 0, 0, 0.6);
			z-index: 999999;
			display: flex;
			align-items: center;
			justify-content: center;
		}

		.splms-modal-content {
			background: white;
			max-width: 600px;
			width: 90%;
			border-radius: 6px;
			box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
		}

		.splms-modal-header {
			padding: 20px 30px;
			border-bottom: 1px solid #ddd;
			display: flex;
			justify-content: space-between;
			align-items: center;
		}

		.splms-modal-header h2 {
			margin: 0;
		}

		.splms-modal-close {
			background: none;
			border: none;
			cursor: pointer;
			padding: 5px;
		}

		.splms-modal-body {
			padding: 30px;
		}

		.splms-modal-body ul {
			margin: 15px 0;
			padding-left: 20px;
		}

		.splms-modal-footer {
			padding: 20px 30px;
			border-top: 1px solid #ddd;
			text-align: right;
		}

		.splms-modal-footer .button {
			margin-left: 10px;
		}
		</style>
	`;

	jQuery('head').append(styles);
});