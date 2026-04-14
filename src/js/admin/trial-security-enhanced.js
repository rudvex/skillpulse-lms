/**
 * Enhanced Trial Security System
 *
 * Provides client-side security validation with server-side verification
 * to prevent trial manipulation while maintaining user experience.
 */

(function($, window, document) {
    'use strict';

    /**
     * Enhanced Trial Security Class
     */
    class TrialSecurityEnhanced {
        constructor() {
            this.config = window.splmsTrialRestrictions || {};
            this.validationInterval = null;
            this.lastValidationTime = 0;
            this.validationCache = {};
            this.securityLevel = 'enhanced'; // enhanced, basic, disabled

            this.init();
        }

        /**
         * Initialize security system
         */
        init() {
            this.bindEvents();
            this.startPeriodicValidation();
            this.setupFormInterception();
            this.setupAjaxInterception();
            this.initializeSecurityChecks();
            this.setupDebugMode();
        }

        /**
         * Bind event handlers
         */
        bindEvents() {
            // Form submission security
            $(document).on('submit', 'form', this.validateFormSubmission.bind(this));

            // Button click security
            $(document).on('click', '.button, .btn, input[type="submit"]', this.validateButtonClick.bind(this));

            // Page load validation
            $(window).on('load', this.performPageLoadValidation.bind(this));

            // Browser tab focus validation
            $(window).on('focus', this.performFocusValidation.bind(this));

            // Storage change detection
            $(window).on('storage', this.handleStorageChange.bind(this));

            // Admin menu restrictions
            $(document).on('click', '.splms-restricted-feature', this.showFeatureRestrictedModal.bind(this));
        }

        /**
         * Start periodic validation
         */
        startPeriodicValidation() {
            // Validate every 5 minutes
            this.validationInterval = setInterval(() => {
                this.performServerValidation();
            }, 5 * 60 * 1000);

            // Immediate validation
            this.performServerValidation();
        }

        /**
         * Setup form interception
         */
        setupFormInterception() {
            // Intercept form submissions before they're sent
            const originalSubmit = HTMLFormElement.prototype.submit;
            const self = this;

            HTMLFormElement.prototype.submit = function() {
                if (self.shouldBlockFormSubmission(this)) {
                    self.showTrialRestrictedNotice();
                    return false;
                }
                return originalSubmit.apply(this, arguments);
            };
        }

        /**
         * Setup AJAX interception
         */
        setupAjaxInterception() {
            const self = this;

            // jQuery AJAX interception
            $(document).ajaxSend(function(event, jqXHR, ajaxOptions) {
                if (self.shouldBlockAjaxRequest(ajaxOptions)) {
                    jqXHR.abort();
                    self.showTrialRestrictedNotice();
                    return false;
                }

                // Add security headers
                jqXHR.setRequestHeader('X-SPLMS-Trial-Token', self.getSecurityToken());
                jqXHR.setRequestHeader('X-SPLMS-Validation-Time', Date.now());
            });

            // Native fetch interception
            if (window.fetch) {
                const originalFetch = window.fetch;
                window.fetch = function(input, init = {}) {
                    if (self.shouldBlockFetchRequest(input, init)) {
                        self.showTrialRestrictedNotice();
                        return Promise.reject(new Error('Trial restriction'));
                    }

                    // Add security headers
                    init.headers = init.headers || {};
                    init.headers['X-SPLMS-Trial-Token'] = self.getSecurityToken();
                    init.headers['X-SPLMS-Validation-Time'] = Date.now();

                    return originalFetch.apply(this, arguments);
                };
            }
        }

        /**
         * Initialize security checks
         */
        initializeSecurityChecks() {
            this.checkClientIntegrity();
            this.validateEnvironment();
            this.setupTamperDetection();
        }

        /**
         * Setup debug mode
         */
        setupDebugMode() {
            if (this.config.debugMode) {
                window.SkillPulseTrial = {
                    getStatus: () => this.getTrialStatus(),
                    validate: () => this.performServerValidation(),
                    getRestrictions: () => this.getActiveRestrictions(),
                    bypass: (feature) => {
                        console.warn('Debug mode: Bypassing restriction for', feature);
                        return true;
                    }
                };
            }
        }

        /**
         * Perform server validation
         */
        async performServerValidation() {
            if (Date.now() - this.lastValidationTime < 60000) {
                return this.validationCache.isValid || false;
            }

            try {
                const response = await $.ajax({
                    url: this.config.ajaxUrl,
                    method: 'POST',
                    data: {
                        action: 'splms_validate_trial_integrity',
                        nonce: this.config.nonce,
                        client_time: Date.now(),
                        security_level: this.securityLevel
                    },
                    timeout: 10000
                });

                if (response.success) {
                    this.validationCache = response.data;
                    this.lastValidationTime = Date.now();
                    this.updateUIBasedOnValidation(response.data);
                    return response.data.is_valid;
                } else {
                    console.warn('Trial validation failed:', response);
                    return false;
                }
            } catch (error) {
                console.warn('Trial validation error:', error);
                // Fail gracefully - don't block user on network errors
                return this.validationCache.isValid !== false;
            }
        }

        /**
         * Validate form submission
         */
        validateFormSubmission(event) {
            const form = event.target;

            if (this.shouldBlockFormSubmission(form)) {
                event.preventDefault();
                event.stopPropagation();
                this.showTrialRestrictedNotice();
                return false;
            }

            return true;
        }

        /**
         * Validate button click
         */
        validateButtonClick(event) {
            const button = event.target;

            if (this.shouldBlockButtonClick(button)) {
                event.preventDefault();
                event.stopPropagation();
                this.showTrialRestrictedNotice();
                return false;
            }

            return true;
        }

        /**
         * Perform page load validation
         */
        performPageLoadValidation() {
            this.performServerValidation();
            this.updateRestrictedElements();
        }

        /**
         * Perform focus validation
         */
        performFocusValidation() {
            // Validate when user returns to tab
            if (document.hidden === false) {
                this.performServerValidation();
            }
        }

        /**
         * Handle storage change
         */
        handleStorageChange(event) {
            if (event.key && event.key.includes('splms_trial')) {
                this.performServerValidation();
            }
        }

        /**
         * Check if form submission should be blocked
         */
        shouldBlockFormSubmission(form) {
            if (!form) return false;

            // Check if trial is expired
            if (this.isTrialExpired()) {
                return true;
            }

            // Check form-specific restrictions
            const restrictedForms = [
                'bulk-import-form',
                'mass-enroll-form',
                'advanced-settings-form',
                'export-data-form'
            ];

            const formId = form.id || '';
            const formClass = form.className || '';

            return restrictedForms.some(restriction =>
                formId.includes(restriction) || formClass.includes(restriction)
            );
        }

        /**
         * Check if button click should be blocked
         */
        shouldBlockButtonClick(button) {
            if (!button) return false;

            if (this.isTrialExpired()) {
                return true;
            }

            // Check button-specific restrictions
            const restrictedSelectors = [
                '.splms-bulk-action',
                '.splms-advanced-feature',
                '.splms-premium-only',
                '[data-feature="advanced"]'
            ];

            return restrictedSelectors.some(selector =>
                $(button).is(selector) || $(button).closest(selector).length > 0
            );
        }

        /**
         * Check if AJAX request should be blocked
         */
        shouldBlockAjaxRequest(ajaxOptions) {
            if (!ajaxOptions.data) return false;

            if (this.isTrialExpired()) {
                return true;
            }

            const restrictedActions = [
                'splms_bulk_import',
                'splms_mass_enroll',
                'splms_export_data',
                'splms_advanced_analytics'
            ];

            const action = ajaxOptions.data.action || '';
            return restrictedActions.includes(action);
        }

        /**
         * Check if fetch request should be blocked
         */
        shouldBlockFetchRequest(input, init) {
            const url = typeof input === 'string' ? input : input.url;

            if (this.isTrialExpired()) {
                return url.includes('/wp-admin/') || url.includes('/wp-json/skillpulse-lms/');
            }

            const restrictedEndpoints = [
                '/wp-json/skillpulse-lms/v1/bulk',
                '/wp-json/skillpulse-lms/v1/import',
                '/wp-json/skillpulse-lms/v1/export'
            ];

            return restrictedEndpoints.some(endpoint => url.includes(endpoint));
        }

        /**
         * Show trial restricted notice
         */
        showTrialRestrictedNotice() {
            const modal = this.createRestrictedModal();
            $('body').append(modal);
            modal.fadeIn();

            // Auto-close after 5 seconds
            setTimeout(() => {
                modal.fadeOut(() => modal.remove());
            }, 5000);
        }

        /**
         * Create restricted feature modal
         */
        createRestrictedModal() {
            const daysRemaining = this.config.daysRemaining || 0;
            const isExpired = daysRemaining <= 0;

            return $(`
                <div class="splms-trial-modal-overlay" style="
                    position: fixed; top: 0; left: 0; width: 100%; height: 100%;
                    background: rgba(0,0,0,0.5); z-index: 999999; display: none;
                ">
                    <div class="splms-trial-modal" style="
                        position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);
                        background: white; padding: 20px; border-radius: 8px; max-width: 500px;
                        box-shadow: 0 4px 20px rgba(0,0,0,0.3);
                    ">
                        <div class="splms-trial-modal-header" style="margin-bottom: 15px;">
                            <h3 style="margin: 0; color: #d63638;">
                                ${isExpired ? 'Trial Expired' : 'Trial Restriction'}
                            </h3>
                        </div>
                        <div class="splms-trial-modal-content" style="margin-bottom: 20px;">
                            <p>${isExpired ?
                                'Your 14-day trial has ended. Please activate a license to continue using SkillPulse LMS features.' :
                                `This feature is limited in the trial version. ${daysRemaining} days remaining in your trial.`
                            }</p>
                        </div>
                        <div class="splms-trial-modal-actions">
                            <a href="${this.config.licenseUrl}" class="button button-primary" style="margin-right: 10px;">
                                Activate License
                            </a>
                            <button class="button splms-modal-close">Close</button>
                        </div>
                    </div>
                </div>
            `).on('click', '.splms-modal-close, .splms-trial-modal-overlay', function(e) {
                if (e.target === this || $(e.target).hasClass('splms-modal-close')) {
                    $(this).fadeOut(() => $(this).remove());
                }
            });
        }

        /**
         * Show feature restricted modal
         */
        showFeatureRestrictedModal(event) {
            event.preventDefault();
            this.showTrialRestrictedNotice();
        }

        /**
         * Update UI based on validation
         */
        updateUIBasedOnValidation(validationData) {
            if (!validationData.is_valid || validationData.is_expired) {
                this.applyRestrictions();
            } else {
                this.removeRestrictions();
            }

            // Update dashboard indicators
            this.updateTrialStatusIndicators(validationData);
        }

        /**
         * Apply restrictions to UI elements
         */
        applyRestrictions() {
            // Disable restricted buttons
            $('.splms-premium-feature').prop('disabled', true).addClass('splms-trial-disabled');

            // Add restricted class to elements
            $('.splms-advanced-feature').addClass('splms-restricted-feature');

            // Hide premium-only elements
            $('.splms-premium-only').hide();

            // Add trial watermarks
            this.addTrialWatermarks();
        }

        /**
         * Remove restrictions from UI elements
         */
        removeRestrictions() {
            $('.splms-trial-disabled').prop('disabled', false).removeClass('splms-trial-disabled');
            $('.splms-restricted-feature').removeClass('splms-restricted-feature');
            $('.splms-premium-only').show();
            $('.splms-trial-watermark').remove();
        }

        /**
         * Update trial status indicators
         */
        updateTrialStatusIndicators(validationData) {
            const statusEl = $('.splms-trial-status');
            if (statusEl.length) {
                statusEl.html(this.generateStatusHTML(validationData));
            }
        }

        /**
         * Generate status HTML
         */
        generateStatusHTML(data) {
            const { is_expired, days_remaining, usage_stats = {} } = data;

            if (is_expired) {
                return '<span class="splms-status-expired">Trial Expired</span>';
            }

            return `
                <div class="splms-trial-status-info">
                    <span class="splms-status-active">Trial Active</span>
                    <span class="splms-days-remaining">${days_remaining} days remaining</span>
                    <div class="splms-usage-summary">
                        ${Object.entries(usage_stats).map(([key, value]) =>
                            `<span class="splms-usage-item">${key}: ${value}</span>`
                        ).join('')}
                    </div>
                </div>
            `;
        }

        /**
         * Add trial watermarks
         */
        addTrialWatermarks() {
            if ($('.splms-trial-watermark').length) return;

            $('body').append(`
                <div class="splms-trial-watermark" style="
                    position: fixed; bottom: 10px; right: 10px; z-index: 999998;
                    background: rgba(0,0,0,0.8); color: white; padding: 8px 12px;
                    border-radius: 4px; font-size: 12px; pointer-events: none;
                ">
                    SkillPulse LMS Trial
                </div>
            `);
        }

        /**
         * Check client integrity
         */
        checkClientIntegrity() {
            // Check for common debugging tools
            const devtools = {
                open: false,
                orientation: null
            };

            const threshold = 160;

            if (window.outerHeight - window.innerHeight > threshold ||
                window.outerWidth - window.innerWidth > threshold) {
                devtools.open = true;
                devtools.orientation = 'vertical';
            }

            if (devtools.open && !this.config.debugMode) {
                console.warn('Development tools detected');
            }
        }

        /**
         * Validate environment
         */
        validateEnvironment() {
            // Check for console manipulation
            if (window.console && console.clear) {
                const originalClear = console.clear;
                console.clear = function() {
                    console.warn('Console cleared - Trial validation may be affected');
                    return originalClear.apply(this, arguments);
                };
            }
        }

        /**
         * Setup tamper detection
         */
        setupTamperDetection() {
            // Detect script injection
            const originalAppendChild = Element.prototype.appendChild;
            Element.prototype.appendChild = function(child) {
                if (child.tagName === 'SCRIPT' && !child.src.includes('skillpulse-lms')) {
                    console.warn('Unauthorized script injection detected');
                }
                return originalAppendChild.apply(this, arguments);
            };
        }

        /**
         * Get security token
         */
        getSecurityToken() {
            return btoa(JSON.stringify({
                timestamp: Date.now(),
                nonce: this.config.nonce,
                validation: this.validationCache.validation_timestamp || 0
            }));
        }

        /**
         * Update restricted elements
         */
        updateRestrictedElements() {
            // Add restrictions to elements that weren't there on page load
            $('[data-splms-restriction]').each((index, element) => {
                const restriction = $(element).data('splms-restriction');
                if (this.isFeatureRestricted(restriction)) {
                    $(element).addClass('splms-restricted-feature');
                }
            });
        }

        /**
         * Check if feature is restricted
         */
        isFeatureRestricted(feature) {
            if (this.isTrialExpired()) {
                return true;
            }

            const restrictions = this.getActiveRestrictions();
            return restrictions.includes(feature);
        }

        /**
         * Get active restrictions
         */
        getActiveRestrictions() {
            const baseRestrictions = [
                'bulk_import',
                'advanced_analytics',
                'mass_enrollment',
                'premium_themes'
            ];

            if (this.isTrialExpired()) {
                return [...baseRestrictions, 'all_features'];
            }

            return baseRestrictions;
        }

        /**
         * Check if trial is expired
         */
        isTrialExpired() {
            return this.config.isTrialExpired || false;
        }

        /**
         * Get trial status
         */
        getTrialStatus() {
            return {
                isExpired: this.isTrialExpired(),
                daysRemaining: this.config.daysRemaining || 0,
                restrictions: this.getActiveRestrictions(),
                lastValidation: this.lastValidationTime,
                validationCache: this.validationCache
            };
        }

        /**
         * Cleanup
         */
        destroy() {
            if (this.validationInterval) {
                clearInterval(this.validationInterval);
            }

            // Remove event listeners
            $(document).off('.trialSecurity');
            $(window).off('.trialSecurity');
        }
    }

    // Initialize when DOM is ready
    $(document).ready(function() {
        if (window.splmsTrialRestrictions) {
            window.splmsTrialSecurity = new TrialSecurityEnhanced();
        }
    });

})(jQuery, window, document);