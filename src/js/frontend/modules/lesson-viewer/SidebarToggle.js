/**
 * Sidebar Toggle
 *
 * Handles sidebar collapse/expand functionality for distraction-free learning.
 *
 * @package SkillPulse_LMS
 * @version 1.0.0
 */

class SPLMSSidebarToggle {
	constructor() {
		this.sidebar = document.querySelector('.splms-fullscreen-sidebar');
		this.toggleButton = document.querySelector('.splms-sidebar-collapse-toggle');
		this.container = document.querySelector('.splms-fullscreen-layout');
		this.isCollapsed = false;

		// Check localStorage for saved state.
		const savedState = localStorage.getItem('splms_sidebar_collapsed');
		if (savedState === 'true') {
			this.isCollapsed = true;
		}

		this.init();
	}

	init() {
		if (!this.sidebar || !this.toggleButton) {
			return;
		}

		// Apply saved state.
		if (this.isCollapsed) {
			this.collapse(false);
		}

		// Handle toggle button click.
		this.toggleButton.addEventListener('click', () => {
			this.toggle();
		});

		// Handle keyboard shortcut (Ctrl + B or Cmd + B).
		document.addEventListener('keydown', (e) => {
			if ((e.ctrlKey || e.metaKey) && e.key === 'b') {
				e.preventDefault();
				this.toggle();
			}
		});
	}

	toggle() {
		if (this.isCollapsed) {
			this.expand();
		} else {
			this.collapse();
		}
	}

	collapse(animate = true) {
		if (!this.sidebar) return;

		this.isCollapsed = true;

		if (!animate) {
			this.sidebar.style.transition = 'none';
		}

		this.sidebar.classList.add('is-collapsed');
		this.container?.classList.add('sidebar-collapsed');
		this.toggleButton?.setAttribute('aria-expanded', 'false');

		// Update button icon direction.
		const icon = this.toggleButton?.querySelector('svg');
		if (icon) {
			icon.style.transform = 'rotate(0deg)';
		}

		// Save state to localStorage.
		localStorage.setItem('splms_sidebar_collapsed', 'true');

		if (!animate) {
			setTimeout(() => {
				if (this.sidebar) {
					this.sidebar.style.transition = '';
				}
			}, 0);
		}

		// Dispatch event.
		this.dispatchToggleEvent('collapsed');
	}

	expand(animate = true) {
		if (!this.sidebar) return;

		this.isCollapsed = false;

		if (!animate) {
			this.sidebar.style.transition = 'none';
		}

		this.sidebar.classList.remove('is-collapsed');
		this.container?.classList.remove('sidebar-collapsed');
		this.toggleButton?.setAttribute('aria-expanded', 'true');

		// Update button icon direction.
		const icon = this.toggleButton?.querySelector('svg');
		if (icon) {
			icon.style.transform = 'rotate(180deg)';
		}

		// Save state to localStorage.
		localStorage.setItem('splms_sidebar_collapsed', 'false');

		if (!animate) {
			setTimeout(() => {
				if (this.sidebar) {
					this.sidebar.style.transition = '';
				}
			}, 0);
		}

		// Dispatch event.
		this.dispatchToggleEvent('expanded');
	}

	dispatchToggleEvent(state) {
		const event = new CustomEvent('splms:sidebarToggle', {
			detail: { state: state }
		});
		document.dispatchEvent(event);
	}

	destroy() {
		// Remove event listeners if needed (button will be destroyed with DOM).
	}
}

export default SPLMSSidebarToggle;
