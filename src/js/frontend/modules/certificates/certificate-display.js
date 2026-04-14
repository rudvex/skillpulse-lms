/**
 * Certificate Display Frontend Module
 * Handles certificate display functionality on frontend
 */

import { SPLMSHelper } from '../../core/helper';

class SPLMSCertificateDisplay {
    constructor() {
        this.init();
    }

    init() {
        this.bindEvents();
        this.checkCertificateStatus();
    }

    bindEvents() {
        // Handle certificate share button
        jQuery(document).on('click', '.certificate-share', this.handleShare.bind(this));

        // Handle certificate print button (download buttons now redirected to print)
        jQuery(document).on('click', '.certificate-download', this.handlePrint.bind(this));

        // Handle certificate print button
        jQuery(document).on('click', '.certificate-print', this.handlePrint.bind(this));


        // Handle certificate preview modal

        // Handle modal close
        jQuery(document).on('click', '.splms-modal-close, .splms-modal-overlay', this.closeModal.bind(this));

        // Handle modal actions
        jQuery(document).on('click', '.certificate-print-modal', this.handlePrint.bind(this));
        jQuery(document).on('click', '.certificate-share-modal', this.handleShareFromModal.bind(this));


        // Handle keyboard events for modal
        jQuery(document).on('keydown', this.handleKeydown.bind(this));
    }

    handleShare(e) {
        e.preventDefault();
        
        const $button = jQuery(e.currentTarget);
        const url = $button.data('url');
        
        if (!url) {
            console.warn('No certificate URL available');
            return;
        }

        // Use unified share helper
        window.SPLMSCore.helper.shareContent({
            title: 'My Certificate',
            text: 'Check out my certificate of completion!',
            url: url,
            onSuccess: () => {
                console.log('Certificate shared successfully!');
            },
            onError: () => {
                // Share modal is already shown by helper
            }
        });
    }


    handlePrint(e) {
        e.preventDefault();

        const $button = jQuery(e.currentTarget);

        // Skip if this is within a dashboard context (dashboard has its own handler)
        if ($button.closest('.splms-dashboard').length) {
            return;
        }

        const courseId = $button.data('course-id');
        const userId = $button.data('user-id');

        if (!courseId || !userId) {
            this.showError('Invalid certificate data');
            return;
        }

        // Generate certificate URL for printing
        const frontend = window.splms_frontend;
        const certificateUrl = `${frontend.site_url}/certificate-preview/?course_id=${courseId}&user_id=${userId}&print=1`;

        // Open certificate in new window optimized for printing
        const printWindow = window.open(
            certificateUrl,
            'certificate-print',
            'width=800,height=600,scrollbars=yes,resizable=yes'
        );

        if (printWindow) {
            // Wait for content to load then trigger print
            printWindow.onload = function() {
                setTimeout(() => {
                    printWindow.print();
                }, 500);
            };
        } else {
            this.showError('Pop-up blocked. Please allow pop-ups for printing certificates.');
        }
    }

    setButtonLoading($button, loading) {
        if (loading) {
            $button.prop('disabled', true);
            $button.find('.dashicons').removeClass('dashicons-download').addClass('dashicons-update');
            $button.find('.dashicons').css('animation', 'spin 1s linear infinite');
        } else {
            $button.prop('disabled', false);
            $button.find('.dashicons').removeClass('dashicons-update').addClass('dashicons-download');
            $button.find('.dashicons').css('animation', '');
        }
    }

    showError(message) {
        window.SPLMSCore.helper.showNotification('Error: ' + message, 'error');
    }

    showSuccess(message) {
        window.SPLMSCore.helper.showNotification('Success: ' + message, 'success');
    }

    trackDownload(url) {
        // Track certificate download for analytics
        // Extract certificate ID from URL if possible
        const urlParams = new URLSearchParams(url.split('?')[1]);
        const certificateId = urlParams.get('certificate_id') || 'unknown';
        
        // Send tracking event
        this.sendTrackingEvent('certificate_download', {
            certificate_id: certificateId,
            timestamp: new Date().toISOString()
        });
    }

    cleanupCertificateFile(url) {
        // Clean up certificate file after download
        const frontend = window.splms_frontend;
        jQuery.ajax({
            url: frontend.ajax_url,
            type: 'POST',
            data: {
                action: 'splms_cleanup_certificate_file',
                file_url: url,
                nonce: frontend.nonces.splms_certificate_nonce
            },
            success: (response) => {
                if (response.success) {
                    console.log('Certificate file cleaned up successfully');
                } else {
                    console.warn('Certificate cleanup failed:', response.data);
                }
            },
            error: (xhr, status, error) => {
                console.warn('Certificate cleanup error:', error);
            }
        });
    }



    checkCertificateStatus() {
        // Check if there's a generating message and periodically check status
        const $generating = jQuery('.certificate-generating');
        
        if ($generating.length) {
            this.startStatusChecker();
        }
    }

    startStatusChecker() {
        let attempts = 0;
        const maxAttempts = 10;
        
        const checkInterval = setInterval(() => {
            attempts++;
            
            if (attempts >= maxAttempts) {
                clearInterval(checkInterval);
                return;
            }

            this.checkUserCertificateStatus().then((hasNewCertificate) => {
                if (hasNewCertificate) {
                    clearInterval(checkInterval);
                    location.reload(); // Reload to show the new certificate
                }
            }).catch(() => {
                // Continue checking on error
            });
        }, 3000); // Check every 3 seconds
    }

    checkUserCertificateStatus() {
        return new Promise((resolve, reject) => {
            const userId = this.getCurrentUserId();
            const courseId = this.getCurrentCourseId();
            
            if (!userId || !courseId) {
                resolve(false);
                return;
            }

            const frontend = window.splms_frontend;
            jQuery.ajax({
                url: frontend.ajax_url,
                type: 'POST',
                data: {
                    action: 'splms_get_user_certificate',
                    user_id: userId,
                    course_id: courseId,
                    nonce: frontend.nonces.splms_certificate_nonce
                },
                success: (response) => {
                    resolve(response.success && response.data.has_certificate);
                },
                error: (xhr, status, error) => {
                    console.error('Certificate status check failed:', error);
                    reject(error);
                }
            });
        });
    }


    closeModal(e) {
        if (e.target === e.currentTarget) {
            const $modal = jQuery('#splms-certificate-modal');
            $modal.removeClass('splms-modal-open').hide();
            jQuery('body').removeClass('splms-modal-open');
        }
    }

    handleShareFromModal(e) {
        e.preventDefault();

        const $modal = jQuery('#splms-certificate-modal');
        const courseId = $modal.data('course-id');
        const userId = $modal.data('user-id');

        if (!courseId || !userId) {
            this.showError('Invalid certificate data');
            return;
        }

        // Generate shareable URL
        const shareUrl = this.generateShareUrl(courseId, userId);

        // Use unified share helper
        window.SPLMSCore.helper.shareContent({
            title: 'My Certificate of Completion',
            text: 'Check out my certificate of completion!',
            url: shareUrl,
            onSuccess: () => {
                this.showSuccess('Certificate shared successfully!');
                this.trackShare(courseId, userId);
            },
            onError: () => {
                // Share modal is already shown by helper
            }
        });
    }

    handleKeydown(e) {
        // Close modal on Escape key
        if (e.keyCode === 27) {
            const $modal = jQuery('#splms-certificate-modal');
            if ($modal.hasClass('splms-modal-open')) {
                this.closeModal(e);
            }
        }
    }

    generateShareUrl(courseId, userId) {
        const frontend = window.splms_frontend;
        return `${frontend.site_url}/certificate-verify/?course_id=${courseId}&user_id=${userId}`;
    }

    showDownloadProgress($button) {
        // Create a simple progress indicator
        const originalText = $button.text();
        $button.data('original-text', originalText);

        let progress = 0;
        const interval = setInterval(() => {
            progress += Math.random() * 15;
            if (progress > 90) progress = 90;

            $button.text(`Preparing... ${Math.floor(progress)}%`);
        }, 200);

        $button.data('progress-interval', interval);
    }

    completeDownloadProgress($button, success = true) {
        const interval = $button.data('progress-interval');
        if (interval) {
            clearInterval(interval);
        }

        const originalText = $button.data('original-text') || $button.text();

        if (success) {
            $button.text('Download Complete!');
            setTimeout(() => {
                $button.text(originalText);
            }, 2000);
        } else {
            $button.text(originalText);
        }
    }

    getCurrentUserId() {
        // Try to get user ID from various sources
        const userIdElement = jQuery('[data-user-id]').first();
        if (userIdElement.length) {
            return userIdElement.data('user-id');
        }

        // Try to get from global variables if available
        if (typeof splms_user_id !== 'undefined') {
            return splms_user_id;
        }

        return null;
    }

    getCurrentCourseId() {
        // Try to get course ID from various sources
        const courseIdElement = jQuery('[data-course-id]').first();
        if (courseIdElement.length) {
            return courseIdElement.data('course-id');
        }
        
        // Try to get from body class
        const bodyClass = jQuery('body').attr('class');
        const matches = bodyClass.match(/postid-(\d+)/);
        if (matches) {
            return parseInt(matches[1]);
        }
        
        // Try to get from global variables if available
        if (typeof splms_course_id !== 'undefined') {
            return splms_course_id;
        }
        
        return null;
    }


    setLoadingState($button, loading) {
        if (loading) {
            $button.prop('disabled', true);
            $button.data('original-text', $button.text());
            $button.html('<span class="spinner"></span> Loading...');
        } else {
            $button.prop('disabled', false);
            $button.text($button.data('original-text') || 'Submit');
        }
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

// CSS for loading animation (notifications handled by unified system)
const style = document.createElement('style');
style.textContent = `
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
`;
document.head.appendChild(style);

export { SPLMSCertificateDisplay };
