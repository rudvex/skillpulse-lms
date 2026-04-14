/**
 * SkillPulse LMS Search & Filter
 * Handles course search, filtering, and layout switching
 */

export class SPLMSSearchFilter {
    constructor() {
        this.searchTimeout = null;
        this.init();
    }

    init() {
        this.bindEvents();
        this.initLayoutSwitcher();
        this.loadSavedLayout();
    }

    bindEvents() {
        // Search toggle functionality
        jQuery(document).on('click', '.splms-search-toggle', this.handleSearchToggle.bind(this));
        jQuery(document).on('click', '.splms-search-close', this.handleSearchClose.bind(this));
        
        // Keyboard support
        jQuery(document).on('keydown', this.handleKeyboard.bind(this));
        
        // Close search when clicking outside
        jQuery(document).on('click', this.handleClickOutsideSearch.bind(this));
        
        // Search functionality
        jQuery('.splms-search-input').on('focus', this.handleSearchFocus.bind(this));
        jQuery('.splms-search-input').on('blur', this.handleSearchBlur.bind(this));
        jQuery('.splms-search-input').on('input', this.handleSearchInput.bind(this));
        
        // Clear search
        jQuery(document).on('click', '.search-clear-btn', this.handleSearchClear.bind(this));
        
        // Filter changes
        jQuery('.sidebar-filters-form').on('change', 'input', this.handleFilterChange.bind(this));
        
        // Clear all filters
        jQuery('.clear-all-filters').on('click', this.handleClearAllFilters.bind(this));
        
        // Layout switcher
        jQuery('.layout-switcher').on('change', 'input[type="radio"]', this.handleLayoutChange.bind(this));
        
        // Auto-expand if search query exists
        this.checkAutoExpand();
    }


    handleSearchToggle(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const $toggleBtn = jQuery(e.currentTarget);
        const $wrapper = $toggleBtn.closest('.splms-search-toggle-wrapper');
        const $panel = $wrapper.find('.splms-search-panel');
        const $form = $panel.find('.splms-search-form');
        const $input = $form.find('.splms-search-input');
        
        // Toggle expanded state
        const isExpanded = $form.attr('data-expanded') === 'true';
        
        if (isExpanded) {
            this.collapseSearch($form, $toggleBtn, $panel);
        } else {
            this.expandSearch($form, $toggleBtn, $panel, $input);
        }
    }

    expandSearch($form, $toggleBtn, $panel, $input) {
        $form.attr('data-expanded', 'true');
        $toggleBtn.attr('aria-expanded', 'true');
        $panel.attr('aria-hidden', 'false');
        $form.addClass('is-expanded');
        $panel.addClass('is-expanded');
        
        // Focus input after animation
        setTimeout(() => {
            $input.focus();
            $input.select(); // Select existing text if any
        }, 150);
    }

    collapseSearch($form, $toggleBtn, $panel) {
        $form.attr('data-expanded', 'false');
        $toggleBtn.attr('aria-expanded', 'false');
        $panel.attr('aria-hidden', 'true');
        $form.removeClass('is-expanded');
        $panel.removeClass('is-expanded');
        
        // Return focus to toggle button
        $toggleBtn.focus();
    }

    handleSearchClose(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const $closeBtn = jQuery(e.currentTarget);
        const $form = $closeBtn.closest('.splms-search-form');
        const $wrapper = $form.closest('.splms-search-toggle-wrapper');
        const $toggleBtn = $wrapper.find('.splms-search-toggle');
        const $panel = $wrapper.find('.splms-search-panel');
        
        this.collapseSearch($form, $toggleBtn, $panel);
    }

    handleKeyboard(e) {
        // ESC key to close search
        if (e.key === 'Escape' || e.keyCode === 27) {
            const $expandedForms = jQuery('.splms-search-form[data-expanded="true"]');
            
            if ($expandedForms.length > 0) {
                e.preventDefault();
                e.stopPropagation();
                
                $expandedForms.each((index, form) => {
                    const $form = jQuery(form);
                    const $wrapper = $form.closest('.splms-search-toggle-wrapper');
                    const $toggleBtn = $wrapper.find('.splms-search-toggle');
                    const $panel = $wrapper.find('.splms-search-panel');
                    
                    this.collapseSearch($form, $toggleBtn, $panel);
                });
            }
        }
    }

    handleClickOutsideSearch(e) {
        const $target = jQuery(e.target);
        const $wrapper = $target.closest('.splms-search-toggle-wrapper');
        
        // Don't close if clicking inside the search wrapper
        if ($wrapper.length > 0) {
            return;
        }
        
        // Close all expanded search forms
        jQuery('.splms-search-form[data-expanded="true"]').each((index, form) => {
            const $form = jQuery(form);
            const $wrapper = $form.closest('.splms-search-toggle-wrapper');
            const $toggleBtn = $wrapper.find('.splms-search-toggle');
            const $panel = $wrapper.find('.splms-search-panel');
            
            // Only close if not submitting
            if (!$target.closest('.splms-search-submit').length) {
                this.collapseSearch($form, $toggleBtn, $panel);
            }
        });
    }

    checkAutoExpand() {
        // Auto-expand if search query exists
        jQuery('.splms-search-form').each((index, form) => {
            const $form = jQuery(form);
            const $input = $form.find('.splms-search-input');
            
            if ($input.val().trim()) {
                const $wrapper = $form.closest('.splms-search-toggle-wrapper');
                const $toggleBtn = $wrapper.find('.splms-search-toggle');
                const $panel = $wrapper.find('.splms-search-panel');
                this.expandSearch($form, $toggleBtn, $panel, $input);
            }
        });
    }

    handleSearchInput(e) {
        const $input = jQuery(e.currentTarget);
        const query = $input.val().trim();
        
        // Toggle clear button (if implemented)
        // this.toggleClearButton($input, query);
    }

    handleSearchFocus(e) {
        jQuery(e.currentTarget).closest('.splms-search-input-wrapper').addClass('focused');
    }

    handleSearchBlur(e) {
        jQuery(e.currentTarget).closest('.splms-search-input-wrapper').removeClass('focused');
    }

    handleSearchClear(e) {
        const $clearBtn = jQuery(e.currentTarget);
        const $input = $clearBtn.siblings('.splms-search-input');
        const searchForm = $input.closest('.splms-search-form');
        
        $input.val('').focus();
        
        // Auto-submit to show all courses
        setTimeout(() => {
            searchForm.submit();
        }, 100);
    }

    handleFilterChange(e) {
        const $form = jQuery(e.currentTarget).closest('form');
        const $content = jQuery('.splms-courses-content');
        
        // Add loading state
        $content.addClass('loading');
        
        // Small delay for better UX
        setTimeout(() => {
            $form.submit();
        }, 300);
    }

    handleClearAllFilters(e) {
        e.preventDefault();
        
        const $form = jQuery('.sidebar-filters-form');
        $form.find('input[type="checkbox"], input[type="radio"]').prop('checked', false);
        $form.submit();
    }

    initLayoutSwitcher() {
        const layoutSwitcher = jQuery('.layout-switcher');
        const coursesGrid = jQuery('.splms-courses-grid');
        
        if (layoutSwitcher.length && coursesGrid.length) {
            // Set initial layout from saved preference
            const savedLayout = localStorage.getItem('splms_layout');
            if (savedLayout) {
                layoutSwitcher.find(`input[value="${savedLayout}"]`).prop('checked', true);
                coursesGrid.attr('data-layout', savedLayout);
            }
        }
    }

    handleLayoutChange(e) {
        const layout = jQuery(e.currentTarget).val();
        const coursesGrid = jQuery('.splms-courses-grid');
        
        coursesGrid.attr('data-layout', layout);
        
        // Save preference to localStorage
        localStorage.setItem('splms_layout', layout);
    }

    loadSavedLayout() {
        const savedLayout = localStorage.getItem('splms_layout');
        if (savedLayout) {
            const layoutSwitcher = jQuery('.layout-switcher');
            const coursesGrid = jQuery('.splms-courses-grid');
            
            layoutSwitcher.find(`input[value="${savedLayout}"]`).prop('checked', true);
            coursesGrid.attr('data-layout', savedLayout);
        }
    }
} 