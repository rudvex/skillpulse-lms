(function($) {
    'use strict';

    /**
     * SkillPulse LMS Trial Upgrade Prompts
     *
     * Handles upgrade prompt interactions, dismissals, and conversion tracking.
     *
     * @since [SPLMS_VERSION]
     */
    const SkillPulseTrialPrompts = {

        /**
         * Initialize upgrade prompt system.
         *
         * @since [SPLMS_VERSION]
         */
        init: function() {
            this.bindEvents();
            this.checkPromptTriggers();
            this.setupKeyboardHandling();
        },

        /**
         * Bind event handlers for prompt interactions.
         *
         * @since [SPLMS_VERSION]
         */
        bindEvents: function() {
            $(document).on('click', '.splms-maybe-later', this.handleMaybeLater);
            $(document).on('click', '.splms-upgrade-btn', this.trackUpgradeClick);
            $(document).on('click', '.splms-upgrade-overlay', this.handleOverlayClick);
            $(document).on('splms_limit_reached', this.showLimitPrompt);
            $(document).on('keydown', this.handleKeyboardShortcuts);
        },

        /**
         * Check for existing prompt triggers in the DOM.
         *
         * @since [SPLMS_VERSION]
         */
        checkPromptTriggers: function() {
            // Check if there are any existing modals to show
            $('.splms-upgrade-modal').each(function() {
                const modal = $(this);
                if (!modal.is(':visible')) {
                    SkillPulseTrialPrompts.showModal(modal);
                }
            });

            // Check for trigger elements that should show prompts
            $('.splms-trigger-upgrade-prompt').on('click', function(e) {
                e.preventDefault();
                const context = $(this).data('context') || 'general_trial_limit';
                SkillPulseTrialPrompts.showPromptForContext(context);
            });
        },

        /**
         * Setup keyboard handling for accessibility.
         *
         * @since [SPLMS_VERSION]
         */
        setupKeyboardHandling: function() {
            $(document).on('keydown', function(e) {
                const modal = $('.splms-upgrade-modal:visible');

                if (modal.length > 0) {
                    // ESC key closes modal
                    if (e.key === 'Escape' || e.keyCode === 27) {
                        SkillPulseTrialPrompts.dismissModal(modal);
                    }

                    // Tab trapping within modal
                    if (e.key === 'Tab' || e.keyCode === 9) {
                        SkillPulseTrialPrompts.handleTabTrapping(e, modal);
                    }
                }
            });
        },

        /**
         * Handle tab key trapping within modal for accessibility.
         *
         * @since [SPLMS_VERSION]
         *
         * @param {Event} e     Keyboard event.
         * @param {jQuery} modal Modal element.
         */
        handleTabTrapping: function(e, modal) {
            const focusableElements = modal.find('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
            const firstFocusable = focusableElements.first();
            const lastFocusable = focusableElements.last();

            if (e.shiftKey) {
                // Shift + Tab
                if ($(document.activeElement).is(firstFocusable)) {
                    e.preventDefault();
                    lastFocusable.focus();
                }
            } else {
                // Tab
                if ($(document.activeElement).is(lastFocusable)) {
                    e.preventDefault();
                    firstFocusable.focus();
                }
            }
        },

        /**
         * Show limit prompt for specific context.
         *
         * @since [SPLMS_VERSION]
         *
         * @param {Event} event   Event object.
         * @param {string} context Limit context.
         * @param {Object} data    Additional data.
         */
        showLimitPrompt: function(event, context, data) {
            const promptConfig = SkillPulseTrialPrompts.getPromptConfig(context);

            if (promptConfig) {
                SkillPulseTrialPrompts.displayModal(promptConfig);
            }
        },

        /**
         * Get prompt configuration for context.
         *
         * @since [SPLMS_VERSION]
         *
         * @param {string} context Prompt context.
         * @return {Object|null} Prompt configuration.
         */
        getPromptConfig: function(context) {
            const configs = {
                'course_limit': {
                    title: 'Course Creation Limit Reached',
                    message: 'You\'ve created 3 courses during your trial. Upgrade to create unlimited courses.',
                    icon: 'splms-icon-course'
                },
                'enrollment_limit': {
                    title: 'Student Enrollment Limit Reached',
                    message: 'You\'ve enrolled 25 students during your trial. Upgrade for unlimited enrollments.',
                    icon: 'splms-icon-users'
                },
                'email_limit': {
                    title: 'Email Limit Reached',
                    message: 'You\'ve sent 100 emails during your trial. Upgrade for unlimited email notifications.',
                    icon: 'splms-icon-email'
                },
                'api_limit': {
                    title: 'API Limit Reached',
                    message: 'You\'ve made 5000 API requests during your trial. Upgrade for unlimited API access.',
                    icon: 'splms-icon-api'
                }
            };

            return configs[context] || null;
        },

        /**
         * Display upgrade modal.
         *
         * @since [SPLMS_VERSION]
         *
         * @param {Object} promptData Prompt configuration.
         */
        displayModal: function(promptData) {
            // Create modal HTML if it doesn't exist
            if ($('.splms-upgrade-modal').length === 0) {
                this.createModalHTML(promptData);
            }

            const modal = $('.splms-upgrade-modal');
            this.showModal(modal);
        },

        /**
         * Create modal HTML structure.
         *
         * @since [SPLMS_VERSION]
         *
         * @param {Object} promptData Prompt configuration.
         */
        createModalHTML: function(promptData) {
            const modalHTML = `
                <div class="splms-upgrade-modal" data-context="${promptData.context || 'general'}">
                    <div class="splms-upgrade-overlay"></div>
                    <div class="splms-upgrade-content">
                        <div class="splms-upgrade-icon">
                            <i class="${promptData.icon || 'splms-icon-lock'}"></i>
                        </div>
                        <h3 class="splms-upgrade-title">${promptData.title}</h3>
                        <p class="splms-upgrade-message">${promptData.message}</p>
                        <div class="splms-upgrade-actions">
                            <a href="#" class="button button-primary button-large splms-upgrade-btn">
                                ${promptData.cta || 'Upgrade Now'}
                            </a>
                            <button class="button button-secondary splms-maybe-later">
                                Maybe Later
                            </button>
                        </div>
                    </div>
                </div>
            `;

            $('body').append(modalHTML);
        },

        /**
         * Show modal with animation.
         *
         * @since [SPLMS_VERSION]
         *
         * @param {jQuery} modal Modal element.
         */
        showModal: function(modal) {
            modal.fadeIn(300, function() {
                // Focus on the first button for accessibility
                modal.find('.splms-upgrade-btn').focus();

                // Add body class to prevent scrolling
                $('body').addClass('splms-modal-open');
            });
        },

        /**
         * Handle "Maybe Later" button clicks.
         *
         * @since [SPLMS_VERSION]
         *
         * @param {Event} e Click event.
         */
        handleMaybeLater: function(e) {
            e.preventDefault();

            const modal = $(this).closest('.splms-upgrade-modal');
            const context = modal.data('context');

            SkillPulseTrialPrompts.dismissModal(modal);
            SkillPulseTrialPrompts.setPromptDismissed(context);
        },

        /**
         * Handle overlay clicks to close modal.
         *
         * @since [SPLMS_VERSION]
         *
         * @param {Event} e Click event.
         */
        handleOverlayClick: function(e) {
            if ($(e.target).hasClass('splms-upgrade-overlay')) {
                const modal = $(e.target).closest('.splms-upgrade-modal');
                SkillPulseTrialPrompts.dismissModal(modal);
            }
        },

        /**
         * Dismiss modal with animation.
         *
         * @since [SPLMS_VERSION]
         *
         * @param {jQuery} modal Modal element.
         */
        dismissModal: function(modal) {
            modal.fadeOut(300, function() {
                $('body').removeClass('splms-modal-open');
                modal.remove();
            });
        },

        /**
         * Track upgrade button clicks.
         *
         * @since [SPLMS_VERSION]
         *
         * @param {Event} e Click event.
         */
        trackUpgradeClick: function(e) {
            const modal = $(this).closest('.splms-upgrade-modal');
            const context = modal.data('context');

            // Track conversion attempt
            SkillPulseTrialPrompts.trackEvent('upgrade_click', context);

            // Get the actual upgrade URL and redirect
            const upgradeUrl = SkillPulseTrialPrompts.getUpgradeUrl(context);

            if (upgradeUrl) {
                // Update the href and let the click proceed
                $(this).attr('href', upgradeUrl);
            }
        },

        /**
         * Get upgrade URL for context.
         *
         * @since [SPLMS_VERSION]
         *
         * @param {string} context Prompt context.
         * @return {string} Upgrade URL.
         */
        getUpgradeUrl: function(context) {
            const baseUrl = window.splmsUpgradePrompts?.upgradeUrl || '/wp-admin/admin.php?page=splms-license';

            // Add context parameter for tracking
            const url = new URL(baseUrl, window.location.origin);
            url.searchParams.set('focus', 'upgrade');
            url.searchParams.set('context', context);

            return url.toString();
        },

        /**
         * Set prompt as dismissed.
         *
         * @since [SPLMS_VERSION]
         *
         * @param {string} context Prompt context.
         */
        setPromptDismissed: function(context) {
            if (typeof splmsUpgradePrompts === 'undefined') {
                return;
            }

            const data = {
                action: 'splms_dismiss_upgrade_prompt',
                nonce: splmsUpgradePrompts.nonce,
                context: context
            };

            $.post(splmsUpgradePrompts.ajaxUrl, data, function(response) {
                if (response.success) {
                    console.log('Prompt dismissed successfully');
                }
            }).fail(function() {
                console.warn('Failed to dismiss prompt');
            });
        },

        /**
         * Track user events for analytics.
         *
         * @since [SPLMS_VERSION]
         *
         * @param {string} eventType Event type.
         * @param {string} context   Event context.
         */
        trackEvent: function(eventType, context) {
            if (typeof splmsUpgradePrompts === 'undefined') {
                return;
            }

            const data = {
                action: 'splms_track_upgrade_click',
                nonce: splmsUpgradePrompts.nonce,
                context: context,
                event_type: eventType
            };

            $.post(splmsUpgradePrompts.ajaxUrl, data, function(response) {
                if (response.success) {
                    console.log('Event tracked:', eventType, context);
                }
            }).fail(function() {
                console.warn('Failed to track event');
            });
        },

        /**
         * Show prompt for specific context programmatically.
         *
         * @since [SPLMS_VERSION]
         *
         * @param {string} context Prompt context.
         */
        showPromptForContext: function(context) {
            const promptConfig = this.getPromptConfig(context);

            if (promptConfig) {
                promptConfig.context = context;
                this.displayModal(promptConfig);
            }
        }
    };

    /**
     * Initialize upgrade prompts when document is ready.
     */
    $(document).ready(function() {
        SkillPulseTrialPrompts.init();
    });

    /**
     * Expose to global scope for external triggering.
     */
    window.SkillPulseTrialPrompts = SkillPulseTrialPrompts;

})(jQuery);