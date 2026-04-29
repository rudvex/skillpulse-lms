/**
 * SkillPulse LMS Course Filters
 * Handles course archive filters, search, and load more functionality
 */

export class SPLMSCourseFilters {
    constructor() {
        this.init();
    }

    init() {
        this.bindEvents();
        this.initializeLoadMore();
        this.setupSearch();
        this.initializeLayoutManagement();
        this.initializeLayoutSwitcher();
        this.initializeSearchToggle();
    }

    bindEvents() {
        // Auto-submit on filter change
        jQuery(document).on('change', '.sidebar-filters-form input[type="checkbox"], .sidebar-filters-form input[type="radio"]', this.handleFilterChange.bind(this));
        
        // Auto-submit on header filter change
        jQuery(document).on('change', '.course-filters-form select', this.handleHeaderFilterChange.bind(this));
        
        // Load more buttons
        jQuery(document).on('click', '.load-more-btn', this.handleLoadMore.bind(this));
        
        // Accordion toggle for filter sections
        jQuery(document).on('click', '.filter-accordion .filter-section-header', this.toggleAccordion.bind(this));
        
        // Keyboard support for accordions
        jQuery(document).on('keydown', '.filter-accordion .filter-section-header', this.handleAccordionKeydown.bind(this));
    }

    /**
     * Handle filter change with auto-submit
     */
    handleFilterChange(e) {
        const $form = jQuery(e.currentTarget).closest('.sidebar-filters-form');
        if ($form.length) {
            // Small delay to allow for multiple selections
            setTimeout(() => {
                $form[0].submit();
            }, 300);
        }
    }

    /**
     * Handle header filter change with auto-submit
     */
    handleHeaderFilterChange(e) {
        const $form = jQuery(e.currentTarget).closest('.course-filters-form');
        const $select = jQuery(e.currentTarget);

        if ($form.length) {
            // Add loading state.
            $select.prop('disabled', true);
            $form.find('.filter-select').addClass('loading');

            // Submit after brief delay to prevent rapid submissions.
            setTimeout(() => {
                $form[0].submit();
            }, 300);
        }
    }

    /**
     * Toggle accordion section
     */
    toggleAccordion(e) {
        e.preventDefault();
        const $header = jQuery(e.currentTarget);
        const $section = $header.closest('.filter-accordion');
        const isExpanded = $header.attr('aria-expanded') === 'true';
        
        // Update aria-expanded
        $header.attr('aria-expanded', !isExpanded);
        
        // Toggle collapsed state
        if (isExpanded) {
            $section.attr('data-collapsed', 'true');
        } else {
            $section.removeAttr('data-collapsed');
        }
    }

    /**
     * Handle keyboard navigation for accordions
     */
    handleAccordionKeydown(e) {
        // Enter or Space to toggle
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            this.toggleAccordion(e);
        }
    }

    /**
     * Initialize search toggle functionality
     */
    initializeSearchToggle() {
        // Auto-expand if search query exists on page load
        jQuery('.splms-search-form').each((index, form) => {
            const $form = jQuery(form);
            const $input = $form.find('.splms-search-input');
            
            if ($input.val().trim()) {
                const $wrapper = $form.closest('.splms-search-toggle-wrapper');
                const $toggleBtn = $wrapper.find('.splms-search-toggle');
                const $panel = $wrapper.find('.splms-search-panel');
                
                // Expand after a short delay to allow layout to settle
                setTimeout(() => {
                    $form.attr('data-expanded', 'true');
                    $toggleBtn.attr('aria-expanded', 'true');
                    $panel.attr('aria-hidden', 'false');
                    $form.addClass('is-expanded');
                    $panel.addClass('is-expanded');
                }, 100);
            }
        });
    }

    /**
     * Initialize load more functionality
     */
    initializeLoadMore() {
        const $containers = jQuery('.filter-scrollable-container');
        
        $containers.each((index, container) => {
            const $container = jQuery(container);
            const itemsPerLoad = parseInt($container.data('items-per-load')) || 8;
            const $allItems = $container.find('.filter-checkbox-label, .filter-tag-label');
            
            // Hide items beyond initial load
            $allItems.each((itemIndex, item) => {
                const $item = jQuery(item);
                if (itemIndex >= itemsPerLoad) {
                    $item.addClass('hidden-item');
                } else {
                    $item.removeClass('hidden-item');
                }
            });
            
            // Update load more button
            this.updateLoadMoreButton($container, itemsPerLoad);
        });
    }

    /**
     * Update load more button text and visibility
     */
    updateLoadMoreButton($container, itemsPerLoad) {
        const $loadMoreBtn = $container.find('.load-more-btn');
        const $loadMoreCount = $container.find('.load-more-count');
        const $hiddenItems = $container.find('.hidden-item');
        
        if (!$loadMoreBtn.length) return;
        
        const remainingCount = $hiddenItems.length;
        
        if (remainingCount <= 0) {
            $loadMoreBtn.hide();
        } else {
            $loadMoreBtn.show();
            if ($loadMoreCount.length) {
                $loadMoreCount.text(`(${remainingCount} more)`);
            }
        }
    }

    /**
     * Handle load more button click
     */
    handleLoadMore(e) {
        e.preventDefault();
        
        const $button = jQuery(e.currentTarget);
        const targetId = $button.data('target');
        const $container = jQuery(`#${targetId}`);
        const itemsPerLoad = parseInt($container.data('items-per-load')) || 8;
        const $hiddenItems = $container.find('.hidden-item');
        
        // Add loading state
        $button.addClass('loading');
        $container.addClass('loading');
        
        // Simulate loading delay for better UX
        setTimeout(() => {
            // Show next batch of items with animation
            const itemsToShow = Math.min(itemsPerLoad, $hiddenItems.length);
            
            $hiddenItems.slice(0, itemsToShow).each((index, item) => {
                const $item = jQuery(item);
                $item.removeClass('hidden-item');
                
                // Animate in with stagger
                setTimeout(() => {
                    $item.css({
                        'transition': 'all 0.3s ease',
                        'opacity': '1',
                        'transform': 'translateY(0)'
                    });
                }, index * 50);
            });
            
            // Remove loading state
            setTimeout(() => {
                $button.removeClass('loading');
                $container.removeClass('loading');
                
                // Update button
                this.updateLoadMoreButton($container, itemsPerLoad);
            }, itemsToShow * 50 + 200);
        }, 300);
    }

    /**
     * Setup search functionality
     */
    setupSearch() {
        // Search functionality is handled in bindEvents
    }

    /**
     * Initialize layout management for archive pages
     */
    initializeLayoutManagement() {
        const $archive = jQuery('.splms-archive');
        const $archiveControls = jQuery('.splms-archive-controls');
        const $sidebar = jQuery('.splms-sidebar-filters');
        const $coursesMain = jQuery('.splms-courses-main');
        
        if ($archive.length) {
            // Check for empty controls
            if ($archiveControls.length && $archiveControls.children().length === 0) {
                $archive.addClass('no-controls');
            }
            
            // Check for empty sidebar
            if (!$sidebar.length || $sidebar.children().length === 0) {
                $archive.addClass('no-sidebar');
                if ($coursesMain.length) {
                    $coursesMain.addClass('no-sidebar');
                }
            } else {
                // Check if sidebar has minimal content
                const filterSections = $sidebar.find('.filter-section');
                if (filterSections.length <= 2) {
                    $archive.addClass('minimal-sidebar');
                    if ($coursesMain.length) {
                        $coursesMain.addClass('minimal-sidebar');
                    }
                }
            }
            
            // Handle dynamic control count
            let controlCount = 0;
            if ($archiveControls.length) {
                const $searchForm = $archiveControls.find('.splms-search-form');
                const $courseFilters = $archiveControls.find('.splms-course-filters');
                const $layoutSwitcher = $archiveControls.find('.splms-layout-switcher');
                
                if ($searchForm.length) controlCount++;
                if ($courseFilters.length) controlCount++;
                if ($layoutSwitcher.length) controlCount++;
                
                if (controlCount <= 1) {
                    $archive.addClass('minimal-controls');
                }
            }
        }
    }

    /**
     * Initialize layout switcher functionality
     */
    initializeLayoutSwitcher() {
        const $layoutSwitcher = jQuery('.splms-layout-switcher');
        const $coursesGrid = jQuery('.splms-courses-grid');
        
        if ($layoutSwitcher.length && $coursesGrid.length) {
            const $buttons = $layoutSwitcher.find('.layout-switch-btn');
            
            // Set initial layout
            const currentLayout = $layoutSwitcher.data('current-layout') || 'grid';
            $coursesGrid.attr('data-layout', currentLayout);

            // Bind click events
            $buttons.on('click', (e) => {
                e.preventDefault();
                const $button = jQuery(e.currentTarget);
                const layout = $button.data('layout');
                
                // Update active button
                $buttons.removeClass('active');
                $button.addClass('active');
                
                // Update corresponding radio button
                const $radioButton = jQuery(`#layout-${layout}`);
                if ($radioButton.length) {
                    $radioButton.prop('checked', true);
                }
                
                // Update grid layout
                $coursesGrid.attr('data-layout', layout);
                
                // Save to localStorage for persistence
                try {
                    localStorage.setItem('splms_layout', layout);
                } catch (e) {
                    // Use cookie if localStorage is not available
                    document.cookie = `splms_layout=${layout}; path=/; max-age=31536000`;
                }
                
                // Update switcher data
                $layoutSwitcher.attr('data-current-layout', layout);
            });
            
            // Load saved layout preference on page load
            try {
                const savedLayout = localStorage.getItem('splms_layout');
                if (savedLayout && savedLayout !== currentLayout) {
                    const $savedButton = $layoutSwitcher.find(`[data-layout="${savedLayout}"]`);
                    if ($savedButton.length) {
                        $savedButton.trigger('click');
                    }
                }
            } catch (e) {
                // localStorage not available, continue with default
            }
        }
    }

    /**
     * Re-initialize filters (useful for AJAX content updates)
     */
    reinitialize() {
        this.initializeLoadMore();
        this.initializeLayoutManagement();
        this.initializeLayoutSwitcher();
    }
}
