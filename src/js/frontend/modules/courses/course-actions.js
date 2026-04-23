/**
 * SkillPulse LMS Course Actions
 * Handles enrollment, bookmarks, wishlist, and share functionality
 */

export class SPLMSCourseActions {
    constructor() {
        this.init();
    }

    init() {
        this.bindEvents();
        this.initUpNextCollapse();
    }

    bindEvents() {
        // Enrollment button
        jQuery(document).on('click', '.enroll-btn', this.handleEnrollment.bind(this));

        // Unenrollment button
        jQuery(document).on('click', '.splms-btn-unenroll', this.handleUnenrollment.bind(this));

        // Start learning button (for public access courses)
        jQuery(document).on('click', '.start-learning-btn', this.handleStartLearning.bind(this));

        // Buy now button (for paid courses)
        jQuery(document).on('click', '.buy-now-btn', this.handleBuyNow.bind(this));

        // Wishlist button
        jQuery(document).on('click', '.wishlist-btn', this.handleWishlist.bind(this));

        jQuery(document).on('click','.splms-filter-button,.splms-filter-close-btns', this.handleMobileFilter.bind(this));

        // Bookmark button - using delegated event binding for dynamically loaded content
        jQuery(document).on('click', '.bookmark-btn', this.handleBookmark.bind(this));

        // Copy link buttons - bind before share button to handle copy-link specifically
        jQuery(document).on('click', '.copy-link', this.handleCopyLink.bind(this));
        jQuery(document).on('click', '.splms-share-btn--copy', this.handleCopyLink.bind(this));

        // Share button (but not copy-link buttons)
        jQuery(document).on('click', '.share-btn:not(.copy-link)', this.handleShare.bind(this));

        // Course Info Modal
        jQuery(document).on('click', '.splms-course-info-btn', this.openCourseInfoModal.bind(this));
        jQuery(document).on('click', '.splms-modal-close, .splms-modal-overlay', this.closeCourseInfoModal.bind(this));

        // Close modal on Escape key
        jQuery(document).on('keydown', this.handleModalKeydown.bind(this));

        // Up Next collapse/expand toggle
        jQuery(document).on('click', '.splms-up-next__title', this.toggleUpNext.bind(this));
        jQuery(document).on('keydown', '.splms-up-next__title', this.handleUpNextKeydown.bind(this));
    }

    handleBookmark(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const $button = jQuery(e.currentTarget);
        // Bookmarks support both lessons and quizzes
        // jQuery data() automatically converts kebab-case to camelCase
        let itemId = $button.data('lesson-id') || $button.data('lessonId') || $button.attr('data-lesson-id');
        let itemType = 'lesson';
        
        // Check for quiz ID if lesson ID not found
        if (!itemId) {
            itemId = $button.data('quiz-id') || $button.data('quizId') || $button.attr('data-quiz-id');
            itemType = 'quiz';
        }
        
        // Parse as integer if it's a string
        if (itemId) {
            itemId = parseInt(itemId, 10);
        }
        
        
        if (!itemId || isNaN(itemId)) {
            console.error('Bookmark button missing valid lesson-id or quiz-id attribute. Button classes:', $button.attr('class'), 'Button HTML:', $button[0].outerHTML);
            return;
        }
        
        // Check if user is logged in
        if (!SPLMSCore.user.isUserLoggedIn()) {
            SPLMSCore.user.redirectToLogin();
            return;
        }

        // Check if bookmarks are enabled (if settings available)
        const frontend = window.splms_frontend || {};
        if (frontend.settings && frontend.settings.enable_bookmarks === false) {
            window.SPLMSCore.helper.showNotification('Bookmarks are disabled by site admin.', 'error');
            return;
        }
        
        $button.addClass('loading').prop('disabled', true);

        SPLMSCore.frontendAjax.toggleBookmark(itemId, itemType).then((response) => {
            if (response.success) {
                window.SPLMSCore.helper.showNotification(response.data.message || 'Bookmark updated successfully!', 'success');
                
                // Toggle button state
                $button.toggleClass('bookmarked active');
                
                // Update button text if exists
                const isBookmarked = $button.hasClass('bookmarked');
                const $text = $button.find('.bookmark-text');
                if ($text.length) {
                    $text.text(isBookmarked ? 'Bookmarked' : 'Bookmark');
                }

                // Sync accessibility state
                $button.attr('aria-pressed', isBookmarked ? 'true' : 'false');
                
                // Update SVG fill
                $button.find('svg path').attr('fill', isBookmarked ? 'currentColor' : 'none');
            } else {
                window.SPLMSCore.helper.showNotification(response.data.message || 'Failed to update bookmark.', 'error');
            }
        }).catch((error) => {
            console.error(error);
            window.SPLMSCore.helper.showNotification('Something went wrong. Please try again.', 'error');
        }).always(() => {
            $button.removeClass('loading').prop('disabled', false);
        });
    }

    handleEnrollment(e) {
        e.preventDefault();
        
        const $button = jQuery(e.currentTarget);
        const courseId = $button.data('course-id');
        
        if (!courseId) return;
        
        $button.addClass('loading').prop('disabled', true);

        SPLMSCore.frontendAjax.enrollCourse(courseId).then((response) => {
            if (response.success) {
                window.SPLMSCore.helper.showNotification('Successfully enrolled in course!', 'success');

                // Redirect or update UI
                if (response.data.redirect_url) {
                    window.location.href = response.data.redirect_url;
                } else {
                    location.reload();
                }
            } else {
                window.SPLMSCore.helper.showNotification(response.data.message || 'Enrollment failed.', 'error');
            }
        }).catch((error) => {
            console.error(error);
            window.SPLMSCore.helper.showNotification('Something went wrong. Please try again.', 'error');
            $button.removeClass('loading').prop('disabled', false);
        }).always(() => {
            $button.removeClass('loading').prop('disabled', false);
        });
    }

    handleUnenrollment(e) {
        e.preventDefault();
        
        const $button = jQuery(e.currentTarget);
        const courseId = $button.data('course-id');
        
        if (!courseId) return;
        
        // Show confirmation dialog
        if (!confirm('Are you sure you want to unenroll from this course? You will lose access to all course materials.')) {
            return;
        }
        
        $button.addClass('loading').prop('disabled', true);

        SPLMSCore.frontendAjax.unenrollCourse(courseId).then((response) => {
            if (response.success) {
                window.SPLMSCore.helper.showNotification(response.data.message || 'Successfully unenrolled from course.', 'success');

                // Reload page to update UI
                setTimeout(() => {
                    location.reload();
                }, 1000);
            } else {
                window.SPLMSCore.helper.showNotification(response.data.message || 'Failed to unenroll from course.', 'error');
                $button.removeClass('loading').prop('disabled', false);
            }
        }).catch((error) => {
            console.error(error);
            window.SPLMSCore.helper.showNotification('Something went wrong. Please try again.', 'error');
            $button.removeClass('loading').prop('disabled', false);
        });
    }

    /**
     * Handle start learning button for public access courses
     */
    handleStartLearning(e) {
        e.preventDefault();
        
        const $button = jQuery(e.currentTarget);
        const courseId = $button.data('course-id');
        
        if (!courseId) return;
        
        $button.addClass('loading').prop('disabled', true);

        // For public access courses, redirect to curriculum or first lesson
        // This could be enhanced to check if user is logged in and auto-enroll them
        if (SPLMSCore.user.isUserLoggedIn()) {
            // User is logged in, enroll them automatically
            SPLMSCore.frontendAjax.enrollCourse(courseId).then((response) => {
                if (response.success) {
                    window.SPLMSCore.helper.showNotification('Welcome to the course!', 'success');
                    
                    // Redirect to curriculum or first lesson
                    if (response.data.redirect_url) {
                        window.location.href = response.data.redirect_url;
                    } else {
                        // Redirect to curriculum tab
                        const currentUrl = new URL(window.location.href);
                        currentUrl.searchParams.set('tab', 'curriculum');
                        window.location.href = currentUrl.toString();
                    }
                } else {
                    window.SPLMSCore.helper.showNotification(response.data.message || 'Failed to start course.', 'error');
                }
            }).catch((error) => {
                console.error(error);
                window.SPLMSCore.helper.showNotification('Something went wrong. Please try again.', 'error');
            }).always(() => {
                $button.removeClass('loading').prop('disabled', false);
            });
        } else {
            // User is not logged in, redirect to login with return URL
            SPLMSCore.user.redirectToLogin();
        }
    }

    /**
     * Handle buy now button for paid courses
     */
    handleBuyNow(e) {
        e.preventDefault();
        
        const $button = jQuery(e.currentTarget);
        const courseId = $button.data('course-id');
        
        if (!courseId) return;
        
        // Check if user is logged in
        if (!SPLMSCore.user.isUserLoggedIn()) {
            // Redirect to login with return URL
            SPLMSCore.user.redirectToLogin();
            return;
        }
        
        $button.addClass('loading').prop('disabled', true);

        // Get the purchase URL from the href attribute
        const purchaseUrl = $button.attr('href');
        
        if (purchaseUrl) {
            // Redirect to purchase/checkout page
            window.location.href = purchaseUrl;
        } else {
            // Get purchase URL via AJAX
            SPLMSCore.frontendAjax.getCoursePurchaseUrl(courseId).then((response) => {
                if (response.success && response.data.purchase_url) {
                    window.location.href = response.data.purchase_url;
                } else {
                    window.SPLMSCore.helper.showNotification('Unable to process purchase. Please try again.', 'error');
                }
            }).catch((error) => {
                console.error(error);
                window.SPLMSCore.helper.showNotification('Something went wrong. Please try again.', 'error');
            }).always(() => {
                $button.removeClass('loading').prop('disabled', false);
            });
        }
    }

    handleWishlist(e) {
        e.preventDefault();
        
        const $button = jQuery(e.currentTarget);
        const courseId = $button.data('course-id');
        
        if (!courseId) return;
        
        // Check if user is logged in
        if (!SPLMSCore.user.isUserLoggedIn()) {
            SPLMSCore.user.redirectToLogin();
            return;
        }

        // Check if wishlist is enabled (if settings available)
        const frontend = window.splms_frontend || {};
        if (frontend.settings && frontend.settings.enable_course_wishlist === false) {
            window.SPLMSCore.helper.showNotification('Wishlist is disabled by site admin.', 'error');
            return;
        }
        
        $button.addClass('loading').prop('disabled', true);

        SPLMSCore.frontendAjax.toggleWishlist(courseId).then((response) => {
            if (response.success) {
                window.SPLMSCore.helper.showNotification(response.data.message || 'Wishlist updated successfully!', 'success');
                
                // Toggle button state
                $button.toggleClass('in-wishlist active');
                
                // Update button text and state
                const isWishlisted = $button.hasClass('in-wishlist');
                const $text = $button.find('.wishlist-text');
                if ($text.length) {
                    $text.text(isWishlisted ? 'Remove from Wishlist' : 'Add to Wishlist');
                }
                // If no text element exists, only update title attribute (preserve SVG icon)
                else {
                    $button.attr('title', isWishlisted ? 'Remove from Wishlist' : 'Add to Wishlist');
                }

                // Sync accessibility state
                $button.attr('aria-pressed', isWishlisted ? 'true' : 'false');

                // Toggle SVG heart fill to reflect state (filled when wishlisted)
                const $svg = $button.find('svg').first();
                if ($svg.length) {
                    // Update SVG fill attribute
                    $svg.attr('fill', isWishlisted ? 'currentColor' : 'none');
                    // Also update path fill if it exists
                    const $svgPath = $svg.find('path').first();
                    if ($svgPath.length) {
                        $svgPath.attr('fill', isWishlisted ? 'currentColor' : 'none');
                    }
                }
            } else {
                window.SPLMSCore.helper.showNotification(response.data.message || 'Failed to update wishlist.', 'error');
            }
        }).catch((error) => {
            console.error(error);
            window.SPLMSCore.helper.showNotification('Something went wrong. Please try again.', 'error');
        }).always(() => {
            $button.removeClass('loading').prop('disabled', false);
        });
    }

    handleMobileFilter(e){
        e.preventDefault();
        jQuery('body').toggleClass('splms-filter-open');
    }

    handleShare(e) {
        const $button = jQuery(e.currentTarget);
        const courseId = $button.data('course-id');

        // If button is a link with href (social media links), allow default behavior.
        if (!courseId && $button.is('a[href]')) {
            return;
        }

        e.preventDefault();

        if (!courseId) return;
        
        // Get course URL
        const courseUrl = window.location.href;
        const courseTitle = document.title;
        
        // Use unified share helper
        window.SPLMSCore.helper.shareContent({
            title: courseTitle,
            text: 'Check out this course!',
            url: courseUrl,
            onSuccess: () => {
            },
            onError: () => {
                // Share modal is already shown by helper
            }
        });
    }

    handleCopyLink(e) {
        e.preventDefault();

        const $button = jQuery(e.currentTarget);
        const copyText = $button.data('copy-url') || $button.data('copy-text') || window.location.href;
        const $message = $button.closest('.splms-course-share').find('.splms-course-share__message');
        
        // Use Clipboard API if available
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(copyText).then(() => {
                this.showCopySuccess($button, $message);
            }).catch(() => {
                // Fallback to older method
                this.fallbackCopyText(copyText, $button, $message);
            });
        } else {
            // Fallback for older browsers
            this.fallbackCopyText(copyText, $button, $message);
        }
    }

    fallbackCopyText(text, $button, $message) {
        const textArea = document.createElement('textarea');
        textArea.value = text;
        textArea.style.position = 'fixed';
        textArea.style.left = '-999999px';
        textArea.style.top = '-999999px';
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        
        try {
            const successful = document.execCommand('copy');
            if (successful) {
                this.showCopySuccess($button, $message);
            } else {
                window.SPLMSCore.helper.showNotification('Failed to copy link. Please copy manually.', 'error');
            }
        } catch (err) {
            window.SPLMSCore.helper.showNotification('Failed to copy link. Please copy manually.', 'error');
        } finally {
            document.body.removeChild(textArea);
        }
    }

    showCopySuccess($button, $message) {
        if ($message.length) {
            $message.fadeIn(200).delay(2000).fadeOut(200);
        } else {
            window.SPLMSCore.helper.showNotification('Link copied to clipboard!', 'success');
        }

        // Temporarily update button text
        const $span = $button.find('span');
        const originalText = $span.text();
        $span.text('Copied!');

        setTimeout(() => {
            $span.text(originalText);
        }, 2000);
    }

    /**
     * Open Course Information Modal
     */
    openCourseInfoModal(e) {
        e.preventDefault();

        const $modal = jQuery('#splms-course-info-modal');

        if (!$modal.length) {
            console.error('Course info modal not found');
            return;
        }

        // Show modal using class-based approach.
        $modal.addClass('modal-visible');
        $modal.attr('aria-hidden', 'false');

        // Prevent body scroll.
        jQuery('body').addClass('modal-open');

        // Focus on close button for accessibility.
        setTimeout(() => {
            $modal.find('.splms-modal-close').focus();
        }, 100);
    }

    /**
     * Close Course Information Modal
     */
    closeCourseInfoModal(e) {
        // Check if click is on overlay or close button.
        const $target = jQuery(e.target);
        const isOverlay = $target.hasClass('splms-modal-overlay');
        const isCloseButton = $target.closest('.splms-modal-close').length > 0;

        // Only close if clicking overlay or close button (or its children like SVG).
        // Don't close if clicking inside modal content (unless it's the close button).
        if (!isOverlay && !isCloseButton) {
            const isInsideModalContent = $target.closest('.splms-modal-content').length > 0;
            if (isInsideModalContent) {
                return;
            }
        }

        e?.preventDefault();

        const $modal = jQuery('#splms-course-info-modal');

        if (!$modal.length) return;

        // Hide modal using class-based approach.
        $modal.removeClass('modal-visible');
        $modal.attr('aria-hidden', 'true');

        // Restore body scroll.
        jQuery('body').removeClass('modal-open');
    }

    /**
     * Handle keyboard events for modal
     */
    handleModalKeydown(e) {
        const $modal = jQuery('#splms-course-info-modal');

        // Close modal on Escape key
        if (e.key === 'Escape' && $modal.is(':visible')) {
            this.closeCourseInfoModal(e);
        }
    }

    /**
     * Initialize Up Next section collapse/expand functionality
     */
    initUpNextCollapse() {
        const $upNextSection = jQuery('.splms-up-next-section');

        if ( ! $upNextSection.length ) {
            return;
        }

        // Load saved state from localStorage.
        try {
            const savedState = localStorage.getItem( 'splms_up_next_collapsed' );

            if ( 'true' === savedState ) {
                this.setUpNextCollapsed( $upNextSection, true );
            }
        } catch ( e ) {
            // LocalStorage not available, continue with default expanded state.
        }
    }

    /**
     * Toggle Up Next section collapse/expand
     */
    toggleUpNext(e) {
        e.preventDefault();

        const $title = jQuery( e.currentTarget );
        const $section = $title.closest( '.splms-up-next-section' );
        const isCollapsed = $section.hasClass( 'collapsed' );

        this.setUpNextCollapsed( $section, ! isCollapsed );

        // Save state to localStorage.
        try {
            localStorage.setItem( 'splms_up_next_collapsed', ! isCollapsed );
        } catch ( e ) {
            // LocalStorage not available, state will not persist.
        }
    }

    /**
     * Set Up Next section collapsed state
     */
    setUpNextCollapsed($section, collapsed) {
        const $title = $section.find( '.splms-up-next__title' );

        if ( collapsed ) {
            $section.addClass( 'collapsed' );
            $title.attr( 'aria-expanded', 'false' );
        } else {
            $section.removeClass( 'collapsed' );
            $title.attr( 'aria-expanded', 'true' );
        }
    }

    /**
     * Handle keyboard events for Up Next section
     */
    handleUpNextKeydown(e) {
        // Toggle on Enter or Space key.
        if ( 'Enter' === e.key || ' ' === e.key ) {
            e.preventDefault();
            this.toggleUpNext( e );
        }
    }
} 