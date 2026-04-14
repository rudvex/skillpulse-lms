let SPLMSSectionEditor = class {
	constructor() {
		this.init();
	}

	/**
	 * Initialize section editor
	 */
	init() {
		// Initialize section editor functionality.
	}

	/**
	 * Set header height
	 */
	setHeaderHeight() {
		let h = jQuery( "#splms-section-header" ).outerHeight( true ) || 0;
		jQuery( ".edit-post-layout" ).css( "padding-top", h + "px" );
	}

	maybeHideHeader(sidebarIsOpen, isTabletOrSmaller) {
		if (sidebarIsOpen && isTabletOrSmaller) {
			jQuery("#splms-section-header-wrapper").hide();
		} else {
			jQuery("#splms-section-header-wrapper").show();
		}
	}

	maybeHideBlockInserter(tabIndex) {
		let blockInserterBtn = jQuery('.edit-post-header-toolbar__inserter-toggle');

		if(tabIndex === 'section') {
			blockInserterBtn.show();
		} else {
			blockInserterBtn.hide();
		}
	}

	/**
	 * Add CSS to element
	 */
	addCSS(selector, rule) {
		if (typeof rule === 'object') {
			jQuery(selector).css(rule);
		}
	}

	/**
	 * Toggle visual editor visibility
	 */
	toggleVisualEditor(visibility = "hide") {
		if (visibility === "show") {
			jQuery(".edit-post-visual-editor, .edit-post-text-editor").show();
		} else {
			jQuery(".edit-post-visual-editor, .edit-post-text-editor").hide();
		}
	}

	/**
	 * Toggle metaboxes visibility
	 */
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

	/**
	 * Toggle panels visibility
	 */
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

	/**
	 * Show default view (section page editing)
	 */
	showDefaultView() {
		this.toggleVisualEditor("show");
		this.toggleMetaBoxes("hide", [
			"splms-section-settings",
			"splms-section-pricing",
		]);
	}

	/**
	 * Show settings view
	 */
	showSettingsView() {
		this.toggleVisualEditor();
		this.toggleMetaBoxes("show", ["splms-section-settings"]);
		this.togglePanels("hide", "");
	}

	/**
	 * Show pricing view
	 */
	showPricingView() {
		this.toggleVisualEditor();
		this.toggleMetaBoxes("show", ["splms-section-pricing"]);
		this.togglePanels("hide", "");
	}
};

export { SPLMSSectionEditor };
