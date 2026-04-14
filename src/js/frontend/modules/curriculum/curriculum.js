/**
 * SkillPulse LMS Curriculum Handler
 * Handles curriculum interactions, enrollment, and progress tracking
 */
class SPLMSCurriculum {
    constructor() {
        this.init();
    }

    init() {
        this.bindEvents();
        this.initSectionToggles();
        this.initProgressBars();
    }

    bindEvents() {
        // Prevent section toggle when clicking link icons.
        jQuery(document).on('click', '.section-link-icon, .section-price-link', function(e) {
            e.stopPropagation();
        });

        // Section toggle functionality - bind to main header area and toggle button.
        jQuery(document).on('click', '.section-header-main, .section-header-minimal, .section-toggle, .section-toggle-minimal', this.handleSectionToggle.bind(this));

        // Note: Lesson completion is now handled by LessonCompletion.js in lesson-viewer.
        // Listen for completion events to update curriculum UI.
        jQuery(document).on('lesson_completed', this.handleLessonCompleted.bind(this));
    }

    initSectionToggles() {
        // Initialize all sections as collapsed by default
        jQuery('.curriculum-section, .curriculum-section-minimal').each(function() {
            const $section = jQuery(this);
            const $toggle = $section.find('.section-toggle, .section-toggle-minimal');
            const $content = $section.find('.section-content');

            // Set initial state to collapsed
            $toggle.attr('aria-expanded', 'false');
            $content.addClass('collapsed').hide();
        });
    }

    initProgressBars() {
        // Animate progress bars on load
        setTimeout(() => {
            jQuery('.progress-bar-fill').each(function() {
                const $bar = jQuery(this);
                const width = $bar.data('width') || $bar.css('width');
                $bar.css('width', '0').animate({ width: width }, 1000);
            });
        }, 500);
    }

    handleSectionToggle(e) {
        e.preventDefault();
        e.stopPropagation();

        const $clicked = jQuery(e.currentTarget);
        const $section = $clicked.closest('.curriculum-section, .curriculum-section-minimal');
        const $toggleBtn = $section.find('.section-toggle, .section-toggle-minimal');
        const $content = $section.find('.section-content');
        const isExpanded = $toggleBtn.attr('aria-expanded') === 'true';

        if (isExpanded) {
            // Collapse section
            $toggleBtn.attr('aria-expanded', 'false').removeClass('expanded');
            $content.slideUp(300, function() {
                $content.addClass('collapsed');
            });
        } else {
            // Expand section
            $toggleBtn.attr('aria-expanded', 'true').addClass('expanded');
            $content.removeClass('collapsed').slideDown(300);
        }
    }

    /**
     * Handle lesson completion event (triggered by LessonCompletion.js)
     */
    handleLessonCompleted(e, data) {
        const { lessonId, progressData } = data;
        
        // Update sidebar curriculum item
        this.markItemAsComplete(lessonId);
        
        // Update progress if provided
        if (progressData) {
            this.updateProgress(progressData);
        }
        
        // Update lesson meta to show completion (for lesson pages)
        this.updateLessonMeta(true);
    }

    markItemAsComplete(itemId) {
        // Find curriculum item by data attribute
        let $item = jQuery(`[data-item-id="${itemId}"]`);
        
        // Find by lesson ID data attribute if not found
        if (!$item.length) {
            $item = jQuery(`[data-lesson-id="${itemId}"]`);
        }
        
        // Find by URL if data attributes not found
        if (!$item.length) {
            $item = jQuery('.curriculum-item').filter(function() {
                const href = jQuery(this).attr('href');
                return href && (href.includes(`p=${itemId}`) || href.includes(`/${itemId}/`));
            });
        }
        
        if ($item.length) {
            $item.addClass('completed').removeClass('current');
            
            // Update status icon to completed state
            const $statusIcon = $item.find('.item-status .status-icon');
            if ($statusIcon.length) {
                $statusIcon.removeClass('incomplete locked').addClass('completed');
                $statusIcon.html(`
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M9 12L11 14L15 10M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                `);
            }
            
            // Update simple status text (for lesson template)
            const $status = $item.find('.item-status');
            if ($status.length && !$statusIcon.length) {
                $status.html('✓');
            }
            
            // Update action button to show completion badge
            const $actionBtn = $item.find('.item-actions .start-btn');
            if ($actionBtn.length) {
                $actionBtn.removeClass('start-btn').addClass('completion-badge').text('Completed');
            }
            
            // Add completion animation
            $item.addClass('completion-animation');
            setTimeout(() => {
                $item.removeClass('completion-animation');
            }, 1000);
        }
    }

    updateLessonButton($button, isCompleted) {
        if (isCompleted) {
            $button.removeClass('mark-lesson-complete loading')
                   .addClass('lesson-completed')
                   .prop('disabled', true)
                   .html('Completed');
        }
    }

    updateLessonMeta(isCompleted) {
        if (isCompleted) {
            const $lessonMeta = jQuery('.splms-lesson-meta');
            if ($lessonMeta.length && !$lessonMeta.find('.completion-indicator').length) {
                $lessonMeta.append('<div class="splms-lesson-meta-item completion-indicator">Completed</div>');
            }
        }
    }

    updateProgress(progressData) {
        if (!progressData) return;
        
        // Update progress bar (multiple selectors)
        const $progressBar = jQuery('.progress-bar-fill, .progress-fill');
        if ($progressBar.length && progressData.percentage !== undefined) {
            $progressBar.animate({
                width: progressData.percentage + '%'
            }, 500);
        }
        
        // Update progress text (multiple selectors)
        const $progressText = jQuery('.progress-text, .course-progress');
        if ($progressText.length && progressData.completed_lessons !== undefined && progressData.total_lessons !== undefined) {
            $progressText.text(`${progressData.completed_lessons} of ${progressData.total_lessons} items completed`);
        }
        
        // Update progress stat
        const $progressStat = jQuery('.progress-stat .stat-number');
        if ($progressStat.length) {
            $progressStat.text(Math.round(progressData.percentage) + '%');
        }
    }

    // Utility method to check enrollment status
    isEnrolled() {
        return jQuery('.enrollment-cta').length === 0;
    }

    // Utility method to get course progress
    getCourseProgress() {
        const $progressBar = jQuery('.progress-bar-fill');
        if ($progressBar.length) {
            const width = $progressBar.css('width');
            const containerWidth = $progressBar.parent().width();
            return (parseFloat(width) / containerWidth) * 100;
        }
        return 0;
    }
}

// Initialize when document is ready
jQuery(document).ready(function() {
    if (jQuery('.course-curriculum, .splms-course-curriculum, .curriculum-overview').length) {
        window.SPLMSCurriculum = new SPLMSCurriculum();
    }
});

// Additional CSS for notifications (inject into head)
jQuery(document).ready(function() {
    // Add curriculum-specific animations only
    if (!jQuery('#splms-curriculum-styles').length) {
        jQuery('head').append(`
            <style id="splms-curriculum-styles">
                .curriculum-item.completion-animation {
                    animation: completionPulse 1s ease;
                }
                
                @keyframes completionPulse {
                    0% { background-color: transparent; }
                    50% { background-color: var(--splms-success-alpha-1, rgba(16, 185, 129, 0.1)); }
                    100% { background-color: transparent; }
                }
            </style>
        `);
    }
}); 