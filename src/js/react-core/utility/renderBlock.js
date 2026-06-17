import { createRoot } from '@wordpress/element';
import domReady from '@wordpress/dom-ready';

/**
 * render Block
 **/
export function renderBlock(containerId, element) {
    domReady(() => {
        const mount = () => {
            const container = document.getElementById(containerId);
            if (container) {
                // Prevent multiple roots if MutationObserver fires twice
                if (!container.hasAttribute('data-react-mounted')) {
                    container.setAttribute('data-react-mounted', 'true');
                    const root = createRoot(container);
                    root.render(element);
                }

                    // Gutenberg prematurely hides empty metaboxes before React can render.
                    // Forcefully reveal the metabox container and liner once React mounts.
                    const postbox = container.closest('.postbox');
                    if (postbox) {
                        postbox.classList.remove('is-hidden');
                        postbox.style.display = 'block';
                        
                        // On fresh installs, metaboxes might default to closed.
                        // Force open it by clicking the toggle button so the preference is saved.
                        if (postbox.classList.contains('closed')) {
                            const toggleBtn = postbox.querySelector('.handlediv');
                            if (toggleBtn) {
                                toggleBtn.click();
                            }
                        }
                    }

                    // FIX FOR GLOBAL METABOX VISIBILITY PREFERENCE
                    // Forcefully reveal the master metabox region if the user's preference was set to false
                    const mainRegion = document.querySelector('.edit-post-meta-boxes-main');
                    if (mainRegion) {
                        mainRegion.style.height = 'auto';
                    }

                    const liner = document.querySelector('.edit-post-meta-boxes-main__liner');
                    if (liner) {
                        liner.removeAttribute('hidden');
                    }

                    // Attempt to permanently save the preference via WordPress Redux API
                    if (window.wp && window.wp.data && window.wp.data.dispatch) {
                        try {
                            const preferencesStore = window.wp.data.dispatch('core/preferences');
                            if (preferencesStore && preferencesStore.set) {
                                preferencesStore.set('core/edit-post', 'metaBoxesMainIsOpen', true);
                            } else {
                                // Fallback for older WordPress versions
                                const editPostStore = window.wp.data.dispatch('core/edit-post');
                                if (editPostStore && editPostStore.updatePreference) {
                                    editPostStore.updatePreference('metaBoxesMainIsOpen', true);
                                }
                            }
                        } catch (e) {
                            // Ignore errors if stores aren't available
                        }
                    }

                return true;
            }
            return false;
        };

        if (!mount()) {
            const observer = new MutationObserver((mutations, obs) => {
                if (mount()) {
                    obs.disconnect();
                }
            });
            observer.observe(document.body, { childList: true, subtree: true });
            
            // Fallback timeout to prevent memory leaks if metabox never renders
            setTimeout(() => observer.disconnect(), 10000);
        }
    });
}
