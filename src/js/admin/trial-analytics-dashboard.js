/**
 * Trial Analytics Dashboard JavaScript
 *
 * Handles basic interactivity for the trial analytics admin page.
 * Provides data refresh, export functionality, and simple animations.
 *
 * @since [SPLMS_VERSION]
 */

(function($) {
    'use strict';

    /**
     * Analytics Dashboard Controller
     */
    const AnalyticsDashboard = {

        /**
         * Initialize dashboard functionality
         */
        init: function() {
            this.bindEvents();
            this.initializeAnimations();
            this.setupProgressBars();
        },

        /**
         * Bind event handlers
         */
        bindEvents: function() {
            // Export analytics data button
            $('.splms-export-analytics').on('click', this.exportAnalyticsData);

            // Refresh data form enhancement
            $('form button[name="refresh_analytics_data"]').on('click', this.showRefreshLoader);

            // Add click handlers for metric cards
            $('.splms-metric-card').on('click', this.animateMetricCard);
        },

        /**
         * Initialize entry animations
         */
        initializeAnimations: function() {
            // Animate metric cards on load
            $('.splms-metric-card').each(function(index) {
                $(this).css({
                    'opacity': '0',
                    'transform': 'translateY(20px)'
                }).delay(index * 100).animate({
                    'opacity': '1'
                }, 300, function() {
                    $(this).css('transform', 'translateY(0)');
                });
            });

            // Animate funnel steps
            $('.splms-funnel-step').each(function(index) {
                $(this).css({
                    'opacity': '0',
                    'transform': 'translateX(-20px)'
                }).delay(index * 150).animate({
                    'opacity': '1'
                }, 400, function() {
                    $(this).css('transform', 'translateX(0)');
                });
            });
        },

        /**
         * Setup animated progress bars
         */
        setupProgressBars: function() {
            // Animate funnel progress bars
            $('.splms-funnel-progress').each(function() {
                const $bar = $(this);
                const targetWidth = $bar.css('width');

                $bar.css('width', '0%').animate({
                    width: targetWidth
                }, 1000, 'easeOutCubic');
            });

            // Animate popularity bars
            $('.splms-popularity-fill').each(function() {
                const $fill = $(this);
                const targetWidth = $fill.css('width');

                $fill.css('width', '0%').animate({
                    width: targetWidth
                }, 800, 'easeOutCubic');
            });
        },

        /**
         * Export analytics data
         */
        exportAnalyticsData: function(e) {
            e.preventDefault();

            const $button = $(this);
            const originalText = $button.text();

            // Show loading state
            $button.text('Exporting...').prop('disabled', true);

            // Prepare export data from DOM
            const exportData = {
                timestamp: new Date().toISOString(),
                analytics: AnalyticsDashboard.collectAnalyticsData()
            };

            // Create and download file
            const blob = new Blob([JSON.stringify(exportData, null, 2)], {
                type: 'application/json'
            });

            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'skillpulse-trial-analytics-' +
                        new Date().toISOString().split('T')[0] + '.json';

            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);

            // Reset button
            setTimeout(function() {
                $button.text(originalText).prop('disabled', false);
                AnalyticsDashboard.showExportSuccess();
            }, 1000);
        },

        /**
         * Collect analytics data from DOM
         */
        collectAnalyticsData: function() {
            const data = {
                overview: {},
                funnel: {},
                features: [],
                performance: {},
                insights: []
            };

            // Extract overview metrics
            $('.splms-metric-card').each(function() {
                const title = $(this).find('h3').text().trim();
                const value = $(this).find('.splms-metric-value').text().trim();
                data.overview[title] = value;
            });

            // Extract funnel data
            $('.splms-funnel-step').each(function() {
                const title = $(this).find('h4').text().trim();
                const count = $(this).find('.splms-funnel-count').text().trim();
                const rate = $(this).find('.splms-funnel-rate').text().trim();

                data.funnel[title] = {
                    count: count,
                    rate: rate
                };
            });

            // Extract feature adoption data
            $('.splms-feature-table-wrapper tbody tr').each(function() {
                const feature = $(this).find('td').eq(0).text().trim();
                const usage = $(this).find('td').eq(1).text().trim();
                const firstUsed = $(this).find('td').eq(2).text().trim();

                data.features.push({
                    name: feature,
                    usage: usage,
                    firstUsed: firstUsed
                });
            });

            // Extract performance data
            $('.splms-performance-card').each(function() {
                const title = $(this).find('h4').text().trim();
                const value = $(this).find('.splms-performance-value, .splms-performance-status').text().trim();
                data.performance[title] = value;
            });

            // Extract insights
            $('.splms-insight-item .splms-insight-text').each(function() {
                data.insights.push($(this).text().trim());
            });

            return data;
        },

        /**
         * Show refresh loader
         */
        showRefreshLoader: function() {
            const $button = $(this);
            const originalText = $button.text();

            $button.text('Refreshing...').prop('disabled', true);

            // Re-enable after form submission
            setTimeout(function() {
                $button.text(originalText).prop('disabled', false);
            }, 2000);
        },

        /**
         * Animate metric card on click
         */
        animateMetricCard: function() {
            $(this).addClass('splms-metric-card-pulse');

            setTimeout(() => {
                $(this).removeClass('splms-metric-card-pulse');
            }, 300);
        },

        /**
         * Show export success message
         */
        showExportSuccess: function() {
            const $notice = $('<div class="notice notice-success is-dismissible splms-export-notice">' +
                '<p>Analytics data exported successfully!</p>' +
                '</div>');

            $('.wrap').prepend($notice);

            // Auto-dismiss after 3 seconds
            setTimeout(function() {
                $notice.fadeOut(function() {
                    $(this).remove();
                });
            }, 3000);
        }
    };

    /**
     * Initialize when document is ready
     */
    $(document).ready(function() {
        // Only initialize on analytics dashboard page
        if ($('.splms-analytics-dashboard').length > 0) {
            AnalyticsDashboard.init();
        }
    });

    /**
     * Add easing function for smooth animations
     */
    $.easing.easeOutCubic = function(x, t, b, c, d) {
        return c*((t=t/d-1)*t*t + 1) + b;
    };

})(jQuery);