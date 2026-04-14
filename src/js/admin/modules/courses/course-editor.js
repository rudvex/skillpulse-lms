let SPLMSCourseEditor = class {
    constructor() {
        this.init();
    }

    init() {

    }

    setHeaderHeight() {
        let h = jQuery( "#splms-course-header" ).outerHeight( true ) || 0;
        jQuery( ".edit-post-layout" ).css( "padding-top", h + "px" );
    }

    maybeHideHeader(sidebarIsOpen, isTabletOrSmaller) {
        if (sidebarIsOpen && isTabletOrSmaller) {
            jQuery("#splms-course-header-wrapper").hide();
        } else {
            jQuery("#splms-course-header-wrapper").show();
        }
    }

    maybeHideBlockInserter(tabIndex) {
        let blockInserterBtn = jQuery('.edit-post-header-toolbar__inserter-toggle');

        if(tabIndex === 'course') {
            blockInserterBtn.show();
        } else {
            blockInserterBtn.hide();
        }
    }

    updateFullscreenLogoLink() {
        jQuery(".edit-post-fullscreen-mode-close").attr("href", SPLMSCore_Data.coursesUrl);
    }

    addCSS(selector, rule) {
        jQuery(selector).css(rule);
    }

    toggleVisualEditor(visibility = "hide") {
        if (visibility === "show") {
            jQuery(".edit-post-visual-editor, .edit-post-text-editor").show();
        } else {
            jQuery(".edit-post-visual-editor, .edit-post-text-editor").hide();
        }
    }

    toggleMetaBoxes(action, elements = []) {
        const metaboxes = wp.data.select("core/edit-post").getAllMetaBoxes();
        Object.entries(metaboxes).map(([key, metabox]) => {
            const metaboxEl = jQuery("#" + metabox.id);

            if (action === "hide") {
                if (elements === "" || elements.includes(metabox.id)) {
                    metaboxEl.hide();
                    return false;
                }
                if (!metaboxEl.hasClass("is-hidden")) metaboxEl.show();
            } else {
                if (elements === "" || elements.includes(metabox.id)) {
                    if(metaboxEl.hasClass('closed')){
                        metaboxEl.removeClass('closed')
                    }
                    metaboxEl.show();
                    return false;
                }
                metaboxEl.hide();
            }
        });
    }

    togglePanels(action, element = "") {
        jQuery(".components-panel .components-panel__body").each(function () {
            if (action === "hide") {
                if (element === "" || this.className.includes(element)) {
                    jQuery(this).hide();
                } else {
                    jQuery(this).show();
                }
            } else {
                if (element === "" || this.className.includes(element)) {
                    jQuery(this).show();
                    jQuery(this).addClass("is-opened");
                } else {
                    jQuery(this).hide();
                }
            }
        });
    }

    showDefaultView() {
        this.toggleVisualEditor("show");

        this.toggleMetaBoxes("hide", [
            "splms-course-curriculum",
            "splms-course-settings",
        ]);

    }

    showCurriculumView() {
        this.toggleVisualEditor();
        this.toggleMetaBoxes("show", ["splms-course-curriculum"]); // Show selected
    };

    showSettingsView() {
        this.toggleVisualEditor();
        this.toggleMetaBoxes("show", ["splms-course-settings"]); // Show selected
        this.togglePanels("hide", "");
    };



}


export { SPLMSCourseEditor };