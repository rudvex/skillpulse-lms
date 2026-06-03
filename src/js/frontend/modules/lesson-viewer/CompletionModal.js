/**
 * Course Completion Modal
 *
 * Extends SPLMSBaseModal to show course completion celebration
 * with proper integration into the existing modal system.
 *
 * @package SkillPulse_LMS
 * @version 1.0.0
 */

import { SPLMSBaseModal } from '../../core/base-modal.js';
import { getSiteUrl } from '../../../react-core/utility/url';

export class CompletionModal extends SPLMSBaseModal {
    constructor() {
        super({
            templateId: 'splms-completion-modal',
            modalId: 'splms-completion-modal',
            closeOnBackdrop: true,
            closeOnEscape: true,
            defaultData: {
                courseTitle: 'Your Course',
                totalLessons: 0,
                completedLessons: 0,
                completionDate: '',
                hasCertificate: false,
                certificateUrl: ''
            }
        });

        this.lessonId = null;
        this.courseId = null;
        this.certificateData = null;
    }

    /**
     * Show completion modal with course data
     */
    async showCompletion(lessonId, courseId, data, options = {}) {
        this.lessonId = lessonId;
        this.courseId = courseId;
        this.certificateData = data;

        // Get course information
        const courseTitle = this.getCourseTitle();
        const completionStats = this.getCompletionStats();
        const hasCertificate = data.certificate_generated || data.certificate_id;

        // Prepare modal data
        const modalData = {
            courseTitle: courseTitle,
            totalLessons: completionStats.totalLessons,
            completedLessons: completionStats.completedLessons,
            completionDate: completionStats.completionDate,
            hasCertificate: hasCertificate,
            certificateUrl: this.getCertificateUrl(),
            showActions: options.showActions !== false // Default to true, can be overridden
        };

        // Open modal with data
        await this.open(modalData);
    }

    /**
     * Get course title from page context
     */
    getCourseTitle() {
        // Try to find course title from various sources
        const titleElement = document.querySelector('h1.splms-course-title, .splms-lesson-header h1, .course-title');
        if (titleElement) {
            return titleElement.textContent.trim();
        }

        // Fallback to breadcrumb or page title
        const breadcrumb = document.querySelector('.splms-breadcrumb a');
        if (breadcrumb) {
            return breadcrumb.textContent.trim();
        }

        return 'Your Course'; // Final fallback
    }

    /**
     * Get completion statistics
     */
    getCompletionStats() {
        const allItems = document.querySelectorAll('.splms-curriculum-item');
        const completedItems = document.querySelectorAll('.splms-curriculum-item.is-completed');

        return {
            totalLessons: allItems.length,
            completedLessons: completedItems.length,
            completionDate: new Date().toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            })
        };
    }

    /**
     * Get certificate URL if available
     */
    getCertificateUrl() {
        // Use backend-provided certificate URL (new hash format)
        if (this.certificateData && this.certificateData.certificate_url) {
            return this.certificateData.certificate_url;
        }

        // No fallback - rely on backend generation
        return null;
    }

    /**
     * Get course URL for navigation
     */
    getCourseUrl() {
        // First, try to get course URL from frontend data if available
        if (typeof splms_frontend !== 'undefined' && splms_frontend.course_url) {
            return splms_frontend.course_url;
        }

        // Get course ID from lesson container data attribute
        const lessonContainer = document.getElementById('splms-lesson-fullscreen-container');
        if (lessonContainer) {
            const courseId = lessonContainer.dataset.courseId;
            if (courseId) {
                // Construct course URL using WordPress structure
                const baseUrl = getSiteUrl();
                return `${baseUrl}/?p=${courseId}`;
            }
        }

        // Fallback: try to find course link in existing navigation
        const backButton = document.querySelector('.splms-back-button[href]');
        if (backButton && backButton.href && !backButton.href.includes(getSiteUrl() + '/' + '?')) {
            return backButton.href;
        }

        return null;
    }

    /**
     * Setup additional event handlers
     */
    setupEvents() {
        if (!this.modalElement) return;

        // Handle action button clicks
        this.addEventHandler('click', (e) => {
            const action = e.target.closest('[data-action]');
            if (!action) return;

            e.preventDefault();

            switch (action.dataset.action) {
                case 'download-certificate':
                    this.handleCertificateDownload();
                    break;
                case 'back-to-course':
                    this.handleBackToCourse();
                    break;
                case 'goto-dashboard':
                    this.handleGotoDashboard();
                    break;
            }
        });
    }

    /**
     * Handle certificate download
     */
    handleCertificateDownload() {
        const certificateUrl = this.getCertificateUrl();

        // Try to open certificate in new tab
        if (certificateUrl) {
            // Check if this is a proper certificate URL (has nonce)
            if (certificateUrl.includes('cert-nonce=') || certificateUrl.includes('/dashboard/')) {
                window.open(certificateUrl, '_blank');
                window.SPLMSCore.helper.showNotification('Opening certificate...', 'success');
            } else {
                // Fallback URL without proper authentication
                window.SPLMSCore.helper.showNotification('Redirecting to dashboard for certificate access...', 'info');
                window.open('/dashboard/?tab=certificates', '_blank');
            }
        } else {
            window.SPLMSCore.helper.showNotification('Certificate is being generated. Please check your dashboard in a few moments.', 'info');
            // Open dashboard as fallback
            setTimeout(() => {
                window.open('/dashboard/?tab=certificates', '_blank');
            }, 1000);
        }
    }

    /**
     * Handle back to course navigation
     */
    handleBackToCourse() {
        const courseUrl = this.getCourseUrl();

        if (courseUrl) {
            window.location.href = courseUrl;
        } else {
            // Fallback: go up one level in URL
            const currentUrl = window.location.pathname;
            const courseUrl = currentUrl.replace(/\/[^\/]+\/?$/, '/');
            window.location.href = courseUrl;
        }
    }

    /**
     * Handle dashboard navigation
     */
    handleGotoDashboard() {
        window.location.href = '/dashboard/';
    }

    /**
     * Called when modal is opened - trigger celebration
     */
    async onOpen(data) {
        // Trigger celebration animation
        this.triggerCelebration();

        // Add bounce animation to trophy after modal is visible
        setTimeout(() => {
            const trophy = this.modalElement.find('.celebration-icon');
            if (trophy.length) {
                trophy.addClass('celebration-bounce');
            }
        }, 300);
    }

    /**
     * Trigger celebration confetti animation
     */
    triggerCelebration() {
        // Create celebration overlay
        const celebration = document.createElement('div');
        celebration.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 10002;
        `;

        // Add floating emojis
        const emojis = ['🎉', '🎊', '🏆', '⭐', '🥳'];
        for (let i = 0; i < 15; i++) {
            const emoji = document.createElement('div');
            emoji.textContent = emojis[Math.floor(Math.random() * emojis.length)];
            emoji.style.cssText = `
                position: absolute;
                font-size: 24px;
                top: -50px;
                left: ${Math.random() * 100}%;
                animation: fallDown 3s ease-in-out forwards;
                animation-delay: ${Math.random() * 2}s;
            `;
            celebration.appendChild(emoji);
        }

        // Add CSS animation for falling emojis
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fallDown {
                to {
                    transform: translateY(100vh) rotate(360deg);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);

        document.body.appendChild(celebration);

        // Remove celebration after animation
        setTimeout(() => {
            if (celebration.parentNode) {
                celebration.remove();
            }
            if (style.parentNode) {
                style.remove();
            }
        }, 5000);
    }

    /**
     * Validate modal data
     */
    validateData(data) {
        if (!data.courseTitle || data.courseTitle.trim() === '') {
            return { isValid: false, message: 'Course title is required' };
        }

        if (data.totalLessons <= 0) {
            return { isValid: false, message: 'Invalid lesson count' };
        }

        return { isValid: true };
    }
}